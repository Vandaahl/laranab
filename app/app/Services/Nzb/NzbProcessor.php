<?php declare(strict_types=1);

namespace App\Services\Nzb;

use App\DTO\NzbData;
use App\DTO\Tmdb\CreditsData;
use App\Models\Category;
use App\Models\Credit;
use App\Models\Movie;
use App\Services\Api\ImageDownloader;

final readonly class NzbProcessor
{
    public const array NZB_CATEGORIES = [
        '2000' => 'movies',
        '2010' => 'foreign',
        '2040' => 'hd',
        '2045' => 'uhd',
        '2020' => 'other',
        '2030' => 'sd'
    ];

    public function getCategoryIds(NzbData $nzb): array
    {
        // A movie NZB has two categories: 2000 for movies and a child for HD/Foreign/etc.
        $categoryRecordIds = [];
        foreach ($nzb->categories as $key => $category) {
            $previousCat = $key > 0 ? $categoryRecordIds[$key - 1] : null;
            $name = self::NZB_CATEGORIES[$category] ?? 'unknown';
            $cat = Category::firstOrCreate([
                'external_id' => $category,
                'parent_id' => $previousCat,
                'name' => $name
            ]);
            $categoryRecordIds[] = $cat->id;
        }

        return $categoryRecordIds;
    }
}
