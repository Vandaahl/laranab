<?php

namespace Tests\Unit;

use App\Services\Api\NzbDataManipulator;
use PHPUnit\Framework\TestCase;

class NzbDataManipulatorTest extends TestCase
{
    public function test_it_keeps_only_items_with_required_attributes()
    {
        $items = [
            [
                'title' => 'Item 1',
                'attr' => [
                    ['@attributes' => ['name' => 'imdb', 'value' => '123']],
                    ['@attributes' => ['name' => 'category', 'value' => '2000']],
                ]
            ],
            [
                'title' => 'Item 2',
                'attr' => [
                    ['@attributes' => ['name' => 'imdb', 'value' => '456']],
                ]
            ],
            [
                'title' => 'Item 3',
                'attr' => [
                    ['@attributes' => ['name' => 'category', 'value' => '2000']],
                ]
            ]
        ];

        // Should only keep items that HAVE BOTH 'imdb' AND 'category' attributes
        $filtered = NzbDataManipulator::keepItemsWithAttributes(['imdb', 'category'], $items);

        $this->assertCount(1, $filtered);
        $this->assertEquals('Item 1', $filtered[0]['title']);
    }
}
