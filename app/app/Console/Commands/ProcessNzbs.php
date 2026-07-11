<?php

namespace App\Console\Commands;

use App\DTO\NzbCollection;
use App\Models\ApiResponse;
use App\Models\Nzb;
use App\Services\Api\Exceptions\ImageDownloadException;
use App\Services\Api\NzbDataManipulator;
use App\Services\Api\TmdbDataFetcher;
use App\Services\Movie\MovieProcessor;
use App\Services\Nzb\NzbProcessor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Movie;

#[Signature('app:process-nzbs')]
#[Description('Create NZBs and movies enriched with metadata from TMDB')]
class ProcessNzbs extends Command
{
    public const array NZB_CATEGORIES = [
        '2000' => 'movies',
        '2010' => 'foreign',
        '2040' => 'hd',
        '2045' => 'uhd',
        '2020' => 'other',
        '2030' => 'sd'
    ];

    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly MovieProcessor $movieProcessor,
        private readonly TmdbDataFetcher $tmdbDataFetcher,
        private readonly NzbProcessor $nzbProcessor,
    )
    {
        parent::__construct();
    }

    /**
     * Processes a queue of API responses containing NZBs and performs the following operations:
     *
     * - Removes NZBs that are missing required attributes.
     * - Ensures NZBs are not duplicates based on their GUIDs or associated IMDB IDs.
     * - Attempts to create a movie record for each valid NZB, attaching additional metadata (e.g., genres, production countries, directors, actors).
     * - Downloads metadata using TMDB APIs for valid NZBs.
     * - Handles errors related to external API connections and logs relevant messages.
     * - Updates the processing status of the current API response, including attempts and failed items.
     */
    public function handle(): void
    {
        set_time_limit(0);
        $queue = ApiResponse::where('processed_at', '=', null)->where('attempts', '<', self::MAX_ATTEMPTS)->limit(2)->get();

        $queue->each(function (ApiResponse $apiResponse) {
            // Keep track of NZB items that were not successfully processed, so they can be processed again in the future.
            $failedItems = $apiResponse->failed_items ?? [];
            $apiResponseItems = NzbDataManipulator::keepItemsWithAttributes(['imdb'], $apiResponse->payload);
            $collection = NzbCollection::fromArray($apiResponseItems);

            foreach ($collection as $key => $nzb) {
                // This NZB has already been processed.
                if ($apiResponse->attempts !== null && $apiResponse->attempts >= 1 && !in_array($key, $failedItems)) {
                    continue;
                }

                if ($nzb->imdb === '0000000' || $nzb->imdb === null || $nzb->imdbTitle === null || $nzb->imdbYear === null) {
                    Log::channel('laranab')->warning("Skipping NZB {$nzb->title} because it is missing one or more imdb attributes.");
                    continue;
                }

                if (Nzb::where('guid', $nzb->guid)->exists()) {
                    Log::channel('laranab')->warning("Skipping NZB {'$nzb->title}' because it already exists in the database.");
                    continue;
                }

                $preExistingMovie = Movie::where('imdb_id', $nzb->imdb)->where('tmdb_id', '!=', null)->first();
                if ($preExistingMovie) {
                    Log::channel('laranab')->warning("Skipping creating a movie for NZB {$nzb->title} because it already exists in the database.");
                    $movie = $preExistingMovie;
                } else {
                    try {
                        $movie = $this->movieProcessor->createMovie($nzb);
                        Log::channel('laranab')->info("Movie {$nzb->imdbTitle} ({$nzb->imdb}) created.");
                    } catch (ImageDownloadException $e) {
                        Log::channel('laranab')->error("Failed to download image for movie {$nzb->imdbTitle}. Error: {$e->getMessage()}");
                        // Continue even if image download fails, as it's not critical.
                    }
                }

                try {
                    $movieData = $this->tmdbDataFetcher->getMovie($nzb);
                } catch (\Exception $e) {
                    Log::channel('laranab')->warning($e->getMessage());
                    if (!in_array($key, $failedItems)) $failedItems[] = $key;
                    continue;
                }

                if (isset($movie)) {
                    // Check if another movie record already has this tmdb_id. If so, use that movie record instead to avoid a
                    // duplicate tmdb_id key error when updating the movie record.
                    $preExistingMovieWithTmdbId = Movie::where('tmdb_id', $movieData->tmdb_id)
                        ->where('id', '!=', $movie->id)
                        ->first();

                    if ($preExistingMovieWithTmdbId) {
                        Log::channel('laranab')->info("Merging movie {$movie->imdb_id} into existing movie with tmdb_id {$movieData->tmdb_id}.");

                        // If the current movie record was just created and doesn't have a tmdb_id yet, and it's different from
                        // the existing one, we should delete it to avoid orphaned records.
                        if ($movie->tmdb_id === null && $movie->id !== $preExistingMovieWithTmdbId->id) {
                            $movie->delete();
                        }

                        $movie = $preExistingMovieWithTmdbId;
                    }

                    $movie->update([
                        'tmdb_id' => $movieData->tmdb_id,
                        'original_title' => $movieData->original_title,
                        'original_language' => $movieData->original_language,
                        'overview' => $movieData->overview,
                        'runtime' => $movieData->runtime,
                    ]);

                    $this->movieProcessor->attachProductionCountriesToMovie($movieData, $movie);
                    $this->movieProcessor->attachGenresToMovie($movieData, $movie);
                }

                try {
                    $creditsData = $this->tmdbDataFetcher->getCredits($nzb);
                } catch (\Exception $e) {
                    Log::channel('laranab')->warning($e->getMessage());
                    if (!in_array($key, $failedItems)) $failedItems[] = $key;
                    continue;
                }

                if (isset($movie)) {
                    $this->movieProcessor->attachDirectorsToMovie($creditsData, $movie);
                    $this->movieProcessor->attachActorsToMovie($creditsData, $movie);
                }

                $nzbRecord = Nzb::create([
                    'title' => $nzb->title,
                    'movie_id' => $movie->id,
                    'guid' => $nzb->guid,
                    'group' => $nzb->group,
                    'size' => $nzb->size,
                    'nzb' => $nzb->nzb,
                    'nfo' => $nzb->nfo,
                    'published_at' => new \DateTime($nzb->pubDate),
                ]);

                Log::channel('laranab')->info("Nzb '{$nzb->title}' for IMDB ID {$nzb->imdb} created.");

                $categoryIds = $this->nzbProcessor->getCategoryIds($nzb);
                $nzbRecord->categories()->sync($categoryIds);

                // If we made it this far, the NZB was successfully processed, so remove it from the failed items array.
                if (count($failedItems) > 0 && in_array($key, $failedItems)) {
                    unset($failedItems[array_search($key, $failedItems)]);
                }
            }

            Log::channel('laranab')->info("Done processing ApiResponse {$apiResponse->id}");
            $apiResponse->attempts++;
            if (count($failedItems)) {
                $apiResponse->failed_items = $failedItems;
            }
            if ($apiResponse->attempts == self::MAX_ATTEMPTS || count($failedItems) == 0) {
                $apiResponse->processed_at = now();
            }
            $apiResponse->save();
        });
    }
}
