<?php

namespace App\Services\Api;

class ApiDataManipulator
{
    /**
     * Turn:
     *
     * [
     *      [
     *          "@attributes" => [
     *              "name" => "category",
     *              "value" => "2000"
     *          ]
     *      ],
     *      [
     *          "@attributes" => [
     *              "name" => "category",
     *              "value" => "2040"
     *          ]
     *      ],
     *      [
     *          "@attributes" => [
     *              "name" => "imdb",
     *              "value" => "34906817"
     *          ]
     *      ]
     * ]
     *
     * Into:
     *
     * [
     *      "categories" => ["2000", "2040"],
     *      "imdb" => "34906817"
     * ]
     *
     * @param array $attributes
     * @return array
     */
    public static function flattenAttributes(array $attributes): array
    {
        /** @var array $attr */
        $attr =  collect($attributes)
            ->reduce(function ($carry, $item) {
                $name = $item['@attributes']['name'];
                $value = $item['@attributes']['value'];

                if ($name === 'category') {
                    $carry['categories'][] = $value;
                } else {
                    $carry[$name] = $value;
                }

                return $carry;
            }, ['categories' => []]);

        return $attr;
    }

    /**
     * Filters out items from the given array where an attribute matches the provided name and value.
     *
     * Each item in the array is expected to contain an "attr" key with a list of attributes, each
     * in this format: ["@attributes" => ["name" => "category", "value" => "2000"]].
     * The filtering is based on the condition that, within the "attr" list, an attribute with the
     * specified name and value exists.
     *
     * @param string $name The name of the attribute to filter by.
     * @param string $value The value of the attribute to filter by.
     * @param array $items The array of items to filter.
     * @return array The filtered array of items.
     */
    public static function removeItemsByAttributeValue(string $name, string $value, array $items): array
    {
        return collect($items)
            ->reject(function ($item) use ($name, $value) {
                return collect($item['attr'] ?? [])
                    ->contains(function ($attr) use ($name, $value) {
                        return $attr['@attributes']['name'] === $name
                            && $attr['@attributes']['value'] === $value;
                    });
            })
        ->values()
        ->all();
    }

    /**
     * Remove items from an array that are missing a specific attribute.
     *
     * Each item in the array is expected to contain an "attr" key with a list of attributes, each
     * in this format: ["@attributes" => ["name" => "category", "value" => "2000"]].
     * The filtering is based on the condition that, within the "attr" list, an attribute with the
     * specified name is missing.
     *
     * @param string $name The name of the missing attribute to filter by.
     * @param array $items The array of items to filter.
     * @return array The filtered array of items.
     */
    public static function removeItemsByMissingAttribute(string $name, array $items): array
    {
        return collect($items)
            ->filter(function ($item) use ($name) {
                return collect($item['attr'] ?? [])
                    ->contains(function ($attr) use ($name) {
                        return ($attr['@attributes']['name'] ?? null) === $name;
                     });
            })
        ->values()
        ->all();
    }
}
