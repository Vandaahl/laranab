<?php

use App\DTO\NzbCollection;
use App\DTO\NzbData;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NzbController;
use App\Http\Controllers\SearchController;
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

Route::get('credits/{credit}', [CreditController::class, 'show'])
    ->name('credits.show');

Route::get('/search', SearchController::class)
    ->name('search');

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

Route::get('/api/nzbs/test/process', function ()
{
    dd(Artisan::call('app:process-nzbs'));
});

Route::get('/api/nzbs/test/list', function()
{
    $queue = ApiResponse::where('processed_at', '=', null)->where('attempts', '<', 3)->get();
    dd($queue);
});
