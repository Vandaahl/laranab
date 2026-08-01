<?php declare(strict_types=1);

namespace App\Services\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

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

        $xml = simplexml_load_string($response->body());
        return $this->parseXml($xml, $url);
    }

    private function parseXml(false|SimpleXMLElement $xml, string $url): array
    {
        if ($xml === false) {
            throw new \Exception('Failed to parse XML from ' . parse_url($url, PHP_URL_HOST));
        }

        $namespaces = $xml->getNamespaces(true);
        // E.g. http://www.newznab.com/DTD/2010/feeds/attributes/
        $nsUri = $namespaces['newznab'] ?? null;

        if (!$nsUri) {
            throw new \Exception('Failed to parse namespace from ' . parse_url($url, PHP_URL_HOST));
        }

        $itemData = $this->convertXmlToArray($xml, $nsUri);

        // Filter out items with imdb=0000000 so only known releases are returned.
        $items = NzbDataManipulator::removeItemsByAttributeValue('imdb', '0000000', $itemData, 'xml');
        // Filter out items that are missing required IMDb attributes.
        return NzbDataManipulator::keepItemsWithAttributes(['imdb'], $items, 'xml');
    }

    private function convertXmlToArray(SimpleXMLElement $xml, string $nsUri): array
    {
        $items = [];

        foreach ($xml->channel->item as $item) {
            $attrs = [];
            // Turn xml into simple array.
            $flattenedItem = json_decode(json_encode($item), true);

            $item->registerXPathNamespace('nn', $nsUri);

            foreach ($item->xpath('./nn:attr') as $attr) {
                $name = (string) $attr['name'];
                $value = (string) $attr['value'];

                $attrs[] = ['name' => $name, 'value' => $value];
            }

            $newItem = [];

            foreach ($flattenedItem as $property => $value) {
                $newItem[$property] = $value;
            }

            $newItem['attr'] = $attrs;

            $items[] = $newItem;
        }

        return $items;
    }
}
