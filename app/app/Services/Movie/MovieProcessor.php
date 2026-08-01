<?php declare(strict_types=1);

namespace App\Services\Movie;

use App\DTO\NzbData;
use App\DTO\Tmdb\CreditsData;
use App\DTO\Tmdb\MovieData;
use App\Models\Country;
use App\Models\Credit;
use App\Models\Genre;
use App\Models\Movie;
use App\Services\Api\Exceptions\ImageDownloadException;
use App\Services\Api\ImageDownloader;
use Illuminate\Support\Facades\DB;

final readonly class MovieProcessor
{
    public function __construct(
        private ImageDownloader $imageDownloader)
    {
    }

    /**
     * Creates a new movie record in the database using the provided NZB data.
     *
     * @param NzbData $nzb The NZB data containing movie details.
     * @param MovieData $movieData Movie data from TMDB.
     * @return Movie The created movie instance.
     * @throws ImageDownloadException If the poster image download fails.
     */
    public function createMovie(NzbData $nzb, MovieData $movieData): Movie
    {
        $posterUrl = $nzb->coverUrl;
        if ($posterUrl) {
            $name = $nzb->imdb . '-' . $movieData->title;
            $filename = $this->imageDownloader->processUrl($posterUrl, $name, 'posters');
        }

        return Movie::firstOrCreate([
            'imdb_id' => $nzb->imdb,
        ], [
            'title' => $movieData->title,
            'year' => $movieData->year,
            'poster' => $filename ?? null,
            'imdb_score' => $nzb->imdbScore,
            'tmdb_score' => $movieData->vote_average,
        ]);
    }

    /**
     * Attaches directors to the specified movie based on the provided credits data.
     * Saves new credits to the database if they don't exist.
     *
     * @param CreditsData $creditsData The credits data containing crew information.
     * @param Movie $movie The movie to which the directors will be attached.
     * @return void
     */
    public function attachDirectorsToMovie(CreditsData $creditsData, Movie $movie): void
    {
        $directors = $creditsData->crew->filter(function ($crew) {
            return $crew->job === 'Director';
        });
        foreach ($directors as $director) {
            $director = Credit::updateOrCreate([
                'tmdb_id' => $director->id,
            ], [
                'name' => $director->name,
            ]);
            DB::table('credit_movie')->updateOrInsert([
                'movie_id' => $movie->id,
                'credit_id' => $director->id,
                'job' => 'Director',
            ]);
        }
    }

    /**
     * Attaches actors from the provided credits data to the specified movie.
     * Saves new credits to the database if they don't exist.
     *
     * @param CreditsData $creditsData The credits data containing the list of cast members.
     * @param Movie $movie The movie to which actors will be attached.
     * @return void
     */
    public function attachActorsToMovie(CreditsData $creditsData, Movie $movie): void
    {
        foreach ($creditsData->cast as $castMember) {
            $actor = Credit::updateOrCreate([
                'tmdb_id' => $castMember->id,
            ], [
                'name' => $castMember->name,
            ]);
            DB::table('credit_movie')->updateOrInsert([
                'movie_id' => $movie->id,
                'credit_id' => $actor->id,
                'job' => 'Actor',
            ]);
        }
    }

    /**
     * Attaches production countries from the provided movie data to the specified movie.
     * Creates new country records in the database if they don't already exist.
     *
     * @param MovieData $movieData The movie data containing the list of production countries.
     * @param Movie $movie The movie to which production countries will be attached.
     * @return void
     */
    public function attachProductionCountriesToMovie(MovieData $movieData, Movie $movie): void
    {
        if (!count($movieData->production_countries)) {
            return;
        }

        $countryIds = [];
        foreach ($movieData->production_countries as $productionCountry) {
            $country = Country::updateOrCreate([
                'iso_3166_1' => $productionCountry['iso_3166_1'],
            ], [
                'name' => $productionCountry['name'],
            ]);
            $countryIds[] = $country->id;
        }
        $movie->countries()->syncWithoutDetaching(array_unique($countryIds));
    }

    /**
     * Attaches genres from the provided movie data to the specified movie.
     * Creates new genres in the database if they don't already exist.
     *
     * @param MovieData $movieData The movie data containing the list of genres.
     * @param Movie $movie The movie to which genres will be attached.
     * @return void
     */
    public function attachGenresToMovie(MovieData $movieData, Movie $movie): void
    {
        if (!count($movieData->genres)) {
            return;
        }

        $genreIds = [];
        foreach ($movieData->genres as $genre) {
            $genre = Genre::updateOrCreate([
                'tmdb_id' => $genre['id'],
            ], [
                'name' => $genre['name'],
            ]);
            $genreIds[] = $genre->id;
        }
        $movie->genres()->syncWithoutDetaching(array_unique($genreIds));
    }
}
