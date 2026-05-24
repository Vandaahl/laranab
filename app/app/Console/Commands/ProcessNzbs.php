<?php

namespace App\Console\Commands;

use App\DTO\NzbData;
use App\DTO\NzbCollection;
use App\Models\ApiResponse;
use App\Models\Nzb;
use App\Services\Api\Exceptions\ImageDownloadException;
use App\Services\Api\Exceptions\TmdbConnectionException;
use App\Services\Api\NzbDataManipulator;
use App\Services\Api\TmdbDataFetcher;
use App\Services\Movie\MovieProcessor;
use App\Services\Nzb\NzbProcessor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Movie;
use Illuminate\Support\Facades\RateLimiter;

#[Signature('app:process-nzbs')]
#[Description('Get movie data from TMDB')]
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

    public function __construct(
        private readonly MovieProcessor $movieProcessor,
        private readonly TmdbDataFetcher $tmdbDataFetcher,
        private readonly NzbProcessor $nzbProcessor,
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        set_time_limit(0);
        $queue = ApiResponse::where('processed_at', '=', null)->where('attempts', '<', 3)->limit(2)->get();

        $queue->each(function (ApiResponse $apiResponse) {
            $failed = false;
            // Keep track of the last NZB that was processed.
            $lastNzb = 0;
            $apiResponseItems = NzbDataManipulator::removeItemsByMissingAttribute(['imdb'], $apiResponse->payload);
            $collection = NzbCollection::fromArray($apiResponseItems);
            $collection->each(function (NzbData $nzb, $key) use ($apiResponse, &$failed, &$lastNzb) {
                // This NZB has already been processed.
                if ($apiResponse->last_successful !== null && $key <= $apiResponse->last_successful) {
                    $lastNzb++;
                    return;
                }

                if ($nzb->imdb === '0000000' || $nzb->imdb === null || $nzb->imdbTitle === null || $nzb->imdbYear === null) {
                    $lastNzb++;
                    Log::channel('laranab')->warning("Skipping NZB {$nzb->title} because it is missing one or more imdb attributes.");
                    return;
                }

                if (Nzb::where('guid', $nzb->guid)->exists()) {
                    $lastNzb++;
                    Log::channel('laranab')->warning("Skipping NZB {'$nzb->title}' because it already exists in the database.");
                    return;
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
                    }
                }

                try {
                    $movieData = $this->tmdbDataFetcher->getMovie($nzb);
                } catch (\Exception $e) {
                    Log::channel('laranab')->warning($e->getMessage());
                    if ($e instanceof TmdbConnectionException) {
                        $apiResponse->failed_at = now();
                        $apiResponse->error = $e->getMessage();
                        $failed = true;
                    }
                    return;
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

                try {
                    $creditsData = $this->tmdbDataFetcher->getCredits($nzb);
                } catch (\Exception $e) {
                    Log::channel('laranab')->warning($e->getMessage());
                    if ($e instanceof TmdbConnectionException) {
                        $apiResponse->failed_at = now();
                        $apiResponse->error = $e->getMessage();
                        $failed = true;
                    }
                    return;
                }

                $this->movieProcessor->attachDirectorsToMovie($creditsData, $movie);
                $this->movieProcessor->attachActorsToMovie($creditsData, $movie);

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

                $lastNzb++;
            });

            $apiResponse->attempts++;
            $apiResponse->last_successful = $lastNzb;
            if (!$failed) $apiResponse->processed_at = now();
            $apiResponse->save();
            Log::channel('laranab')->info("Done processing ApiResponse {$apiResponse->id}");
        });
    }
}
