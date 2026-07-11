<?php

use App\DTO\NzbCollection;
use App\DTO\NzbData;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NzbController;
use App\Models\ApiResponse;
use App\Models\Credit;
use App\Models\Movie;
use App\Services\Api\Exceptions\ImageDownloadException;
use App\Services\Api\ImageDownloader;
use App\Services\Api\NzbDataManipulator;
use Illuminate\Support\Facades\Route;

Route::get('/', [MovieController::class, 'index'])
    ->name('home');

Route::get('/categories/{category}', [CategoryController::class, 'show'])
    ->name('categories.show');

Route::get('/nzbs/{nzb}/nfo', [NzbController::class, 'nfo'])
    ->name('nzb.nfo');

Route::get('/api/nzbs/test/fetch', function ()
{
    dd(Artisan::call('app:fetch-nzbs'));
});

Route::get('/api/nzbs/test/read', function ()
{
    $apiResponse = ApiResponse::latest()->first();
    $collection = NzbCollection::fromArray($apiResponse->payload);
    $collection->each(function (NzbData $nzb, $key) {
        dump($nzb);
    });
    dd('read');
});

Route::get('/api/nzbs/test/filter', function () {
    $apiResponse = ApiResponse::latest()->first()->payload;
    dump(count($apiResponse) . ' unfiltered items');
    $items = NzbDataManipulator::keepItemsWithAttributes(['imdb'], $apiResponse);
    dd(count($items) . ' items after filtering out items that are missing imdb attribute');
});

Route::get('/create-movie', function (ImageDownloader $imageDownloader)
{
    $apiResponse = ApiResponse::latest()->first();
    $collection = NzbCollection::fromArray($apiResponse->payload);
    $movie = $collection->random();

    $posterUrl = $movie->coverUrl;
    if ($posterUrl) {
        $name = $movie->imdb . '-' . $movie->imdbTitle;
        try {
            $filename = $imageDownloader->processUrl($posterUrl, $name, 'posters');
        } catch (ImageDownloadException $e) {
            Log::error("Failed to download image for movie {$movie->title}. Error: {$e->getMessage()}");
        }
    }

    $newMovie = Movie::updateOrCreate([
        'title' => $movie->imdbTitle,
        'imdb_id' => $movie->imdb,
        'year' => $movie->imdbYear,
        'poster' => $filename ?? null,
    ]);

    Log::info("Movie {$movie->imdbTitle} created or updated.");

    dd($newMovie);
});

Route::get('/update-movie', function ()
{
    $movie = Movie::where('imdb_id', '=', 'tt0069735')->first();
    $director = Credit::updateOrCreate([
        'name' => 'Jesús García de Dueñas',
        'tmdb_id' => '1031910',
    ]);
    $actor = Credit::updateOrCreate([
        'name' => 'Lola Flores',
        'tmdb_id' => '107271',
    ]);
    $movie->credits()->syncWithoutDetaching([
        $director->id => ['job' => 'Director'],
        $actor->id => ['job' => 'Actor'],
    ]);
    dd($movie->toArray());
});

Route::get('/api/tmdb/test/process', function ()
{
    dd(Artisan::call('app:process-nzbs'));
});

Route::get('/api/nzbs/test/process', function()
{
    $queue = ApiResponse::where('processed_at', '=', null)->where('attempts', '<', 3)->get();
    dd($queue);
});
