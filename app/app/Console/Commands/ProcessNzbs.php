<?php

namespace App\Console\Commands;

use App\DTO\NzbData;
use App\DTO\NzbCollection;
use App\Models\ApiResponse;
use App\Models\Category;
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
                    return;
                }

                if ($nzb->imdb === '0000000' || $nzb->imdb === null || $nzb->imdbTitle === null || $nzb->imdbYear === null) {
                    Log::channel('laranab')->warning("Skipping NZB {$nzb->title} because it is missing one or more imdb attributes.");
                    return;
                }

                if (Nzb::where('guid', $nzb->guid)->exists()) {
                    Log::channel('laranab')->warning("Skipping NZB {$nzb->title} because it already exists in the database.");
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

                $lastNzb++;

                $categoryIds = $this->nzbProcessor->getCategoryIds($nzb);
                $nzbRecord->categories()->sync($categoryIds);

                // todo: update ApiResponse with processed_at, failed_at, attempts, error
                // todo: after failed, retry from the failed nzb/movie
            });

            $apiResponse->attempts++;
            $apiResponse->last_successful = $lastNzb;
            if (!$failed) $apiResponse->processed_at = now();
            $apiResponse->save();
            Log::channel('laranab')->info("Done processing ApiResponse {$apiResponse->id}");
        });
    }

    /**
     * Handles executing a callback function while enforcing rate limits using a RateLimiter.
     *
     * Continuously checks the remaining allowance for the 'tmdb' key in the RateLimiter. If the
     * allowance has been depleted, the method waits until the RateLimiter allows for further
     * operations by sleeping for the reported number of seconds or waiting briefly to
     * accommodate edge cases. After the rate limit is incremented, the provided callback function
     * is executed and its result is returned.
     *
     * @param callable $callback A callback function to execute once the rate limit has been handled.
     * @return mixed The result of the executed callback function.
     */
    private function withRateLimit(callable $callback): mixed
    {
        $limiter = RateLimiter::limiter('tmdb');
        $maxAttempts = $limiter ? $limiter()->maxAttempts : 40;

        while (RateLimiter::remaining('tmdb', $maxAttempts) === 0) {
            $seconds = RateLimiter::availableIn('tmdb');
            if ($seconds > 0) {
                sleep($seconds);
            } else {
                // If availableIn is 0 but remaining is 0, we might be at the edge of a second.
                // Wait a tiny bit to avoid a busy loop.
                usleep(100000); // 100ms
            }
        }

        RateLimiter::increment('tmdb');

        return $callback();
    }
}
