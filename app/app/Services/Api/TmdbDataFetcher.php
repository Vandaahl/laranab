<?php declare(strict_types=1);

namespace App\Services\Api;

use App\DTO\NzbData;
use App\DTO\Tmdb\CreditsData;
use App\DTO\Tmdb\MovieData;
use App\Services\Api\Exceptions\TmdbConnectionException;
use App\Services\Api\Exceptions\TmdbDataNotFoundException;
use App\Services\Api\Exceptions\TmdbWrongTypeException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

final class TmdbDataFetcher
{
    public const string TMDB_MOVIE_URL = 'https://api.themoviedb.org/3/movie/%s';
    public const string TMDB_CREDITS_URL = 'https://api.themoviedb.org/3/movie/%s/credits';
    private string $apiKey;

    public function __construct(
    )
    {
        $this->apiKey = config('laranab.tmdb_api_key');
    }

    /**
     * Fetches movie data from TMDB using the provided IMDb ID with a rate limiter.
     *
     * @param NzbData $nzb Should contain the IMDb ID.
     * @return MovieData
     * @throws TmdbWrongTypeException|TmdbDataNotFoundException|TmdbConnectionException|ConnectionException
     */
    public function getMovie(NzbData $nzb): MovieData
    {
        $movieResponse = $this->withRateLimit(function () use ($nzb) {
            $url = sprintf(self::TMDB_MOVIE_URL, $nzb->imdb);
            return Http::withToken($this->apiKey)->get($url);
        });

        if ($movieResponse && $movieResponse->successful()) {
            $data = $movieResponse->json();
            $movieData = MovieData::fromArray($data);
        } else {
            throw new TmdbConnectionException("Failed to fetch TMDB movie data for IMDB ID {$nzb->imdb}");
        }

        return $movieData;
    }

    /**
     * Fetches credits data from TMDB using the provided IMDb ID with a rate limiter.
     *
     * @param NzbData $nzb Should contain the IMDb ID.
     * @return CreditsData
     * @throws TmdbConnectionException|ConnectionException
     */
    public function getCredits(NzbData $nzb): CreditsData
    {
        $creditsResponse = $this->withRateLimit(function () use ($nzb) {
            $url = sprintf(self::TMDB_CREDITS_URL, $nzb->imdb);
            return Http::withToken($this->apiKey)->get($url);
        });

        if ($creditsResponse && $creditsResponse->successful()) {
            $data = $creditsResponse->json();
            // Limit cast to 10 members.
            if (count($data['cast']) > 10) {
                $data['cast'] = array_slice($data['cast'], 0, 10);
            }
            $creditsData = CreditsData::fromArray($data);
        } else {
            throw new TmdbConnectionException("Failed to fetch TMDB credits data for IMDB ID {$nzb->imdb}.");
        }

        return $creditsData;
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
