<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ApiService
{
    /**
     * @param string $url
     * @return array
     * @throws ConnectionException
     */
    public function fetchNzbs(string $url): array
    {
        $response = Http::timeout(20)->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch NZBs');
        }

        $data = $response->json();
        /** @var array $itemData */
        $itemData = $data['channel']['item'];

        // Filter out items with imdb=0000000 so only known releases are returned.
        return ApiDataManipulator::filterItems('imdb', '0000000', $itemData);
    }
}
