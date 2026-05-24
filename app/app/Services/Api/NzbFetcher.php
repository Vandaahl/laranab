<?php declare(strict_types=1);

namespace App\Services\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class NzbFetcher
{
    /**
     * @param string $url
     * @return array
     * @throws ConnectionException
     */
    public function fetch(string $url): array
    {
        $response = Http::timeout(20)->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch NZBs');
        }

        $data = $response->json();
        /** @var array $itemData */
        $itemData = $data['channel']['item'];

        // Filter out items with imdb=0000000 so only known releases are returned.
        $items = NzbDataManipulator::removeItemsByAttributeValue('imdb', '0000000', $itemData);
        // Filter out items that are missing required IMDb attributes.
        return NzbDataManipulator::keepItemsWithAttributes(['imdb', 'imdbtitle', 'imdbyear'], $items);
    }
}
