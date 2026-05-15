<?php declare(strict_types=1);

namespace App\DTO;

use App\Services\Api\NzbDataManipulator;

final readonly class Nzb
{
    public function __construct(
        public string  $title,
        public ?string $imdb,
        public ?string $imdbTitle,
        public ?string $size,
        public ?string $pubDate,
        public array $categories,
        public string $guid,
        public ?int $imdbYear,
        public ?float $imdbScore,
        public ?string $coverUrl,
        public ?string $group,
        public string $nzb,
        public ?string $nfo
    ) {}

    public static function fromArray(array $data): self
    {
        $attributes = NzbDataManipulator::flattenAttributes($data['attr']);

        return new self(
            title: $data['title'],
            imdb: $attributes['imdb'] ?? null,
            imdbTitle: $attributes['imdbtitle'] ?? null,
            size: $attributes['size'] ?? null,
            pubDate: $data['pubDate'] ?? null,
            categories: $attributes['categories'],
            guid: $attributes['guid'],
            imdbYear: isset($attributes['imdbyear']) ? (int) $attributes['imdbyear'] : null,
            imdbScore: isset($attributes['imdbscore']) ? (float) $attributes['imdbscore'] : null,
            coverUrl: $attributes['coverurl'] ?? null,
            group: $attributes['group'] ?? null,
            nzb: $data['link'],
            nfo: $attributes['info'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'imdb' => $this->imdb,
            'imdbTitle' => $this->imdbTitle,
            'size' => $this->size,
            'pubDate' => $this->pubDate,
            'categories' => $this->categories,
            'guid' => $this->guid,
            'imdbYear' => $this->imdbYear,
            'imdbScore' => $this->imdbScore,
            'coverUrl' => $this->coverUrl,
            'group' => $this->group,
            'nzb' => $this->nzb,
            'nfo' => $this->nfo,
        ];
    }
}
