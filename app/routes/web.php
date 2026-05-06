<?php

use App\DTO\ApiResponseItemCollection;
use App\Models\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function ()
{
    return view('welcome');
});

Route::get('/api/nzbs/test/fetch', function ()
{
    dd(Artisan::call('app:fetch-nzbs'));
});

Route::get('/api/nzbs/test/read', function ()
{
    $apiResponse = ApiResponse::latest()->first();
    $collection = ApiResponseItemCollection::fromArray($apiResponse->payload);

    foreach ($collection->items as $item) {
        dump($item);
    }
    dd('read');
});
