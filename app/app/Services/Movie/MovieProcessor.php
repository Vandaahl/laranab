<?php declare(strict_types=1);

namespace App\Services\Movie;

use App\DTO\NzbData;
use App\DTO\Tmdb\CreditsData;
use App\DTO\Tmdb\MovieData;
use App\Models\Country;
use App\Models\Credit;
use App\Models\Genre;
use App\Models\Movie;
use App\Services\Api\ImageDownloader;

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
     * @return Movie The created movie instance.
     * @thows ImageDownloadException If the poster image download fails.
     */
    public function createMovie(NzbData $nzb): Movie
    {
        $posterUrl = $nzb->coverUrl;
        if ($posterUrl) {
            $name = $nzb->imdb . '-' . $nzb->imdbTitle;
            $filename = $this->imageDownloader->processUrl($posterUrl, $name, 'posters');
        }

        return Movie::firstOrCreate([
            'title' => $nzb->imdbTitle,
            'imdb_id' => $nzb->imdb,
            'year' => $nzb->imdbYear,
            'poster' => $filename ?? null,
            'imdb_score' => $nzb->imdbScore,
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
            $movie->credits()->syncWithoutDetaching([
                $director->id => ['job' => 'Director']
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
            $movie->credits()->syncWithoutDetaching([
                $actor->id => ['job' => 'Actor']
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

        foreach ($movieData->production_countries as $productionCountry) {
            $country = Country::updateOrCreate([
                'iso_3166_1' => $productionCountry['iso_3166_1'],
            ], [
                'name' => $productionCountry['name'],
            ]);
            $movie->countries()->syncWithoutDetaching($country->id);
        }
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

        foreach ($movieData->genres as $genre) {
            $genre = Genre::updateOrCreate([
                'tmdb_id' => $genre['id'],
            ], [
                'name' => $genre['name'],
            ]);
            $movie->genres()->syncWithoutDetaching($genre->id);
        }
    }
}
