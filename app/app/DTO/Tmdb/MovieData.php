<?php declare(strict_types=1);

namespace App\DTO\Tmdb;

use DateTime;

final readonly class MovieData
{
    public function __construct(
        public array $genres,
        public int $tmdb_id,
        public string $imdb_id,
        public string $original_language,
        public string $title,
        public string $original_title,
        public string $overview,
        public int $runtime,
        public array $origin_country,
        public array $production_countries,
        public int $year,
        public ?float $vote_average,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            genres: $data['genres'],
            tmdb_id: $data['id'],
            imdb_id: $data['imdb_id'],
            original_language: $data['original_language'],
            title: $data['title'],
            original_title: $data['original_title'],
            overview: $data['overview'],
            runtime: $data['runtime'],
            origin_country: $data['origin_country'],
            production_countries: $data['production_countries'],
            year: (isset($data['release_date']) && $data['release_date'] !== '')
                ? (int) (new DateTime($data['release_date']))->format('Y')
                : 1,
            vote_average: isset($data['vote_average']) ? (float) $data['vote_average'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'imdb_id' => $this->imdb_id,
            'tmdb_id' => $this->tmdb_id,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'original_language' => $this->original_language,
            'overview' => $this->overview,
            'genres' => $this->genres,
            'runtime' => $this->runtime,
            'origin_country' => $this->origin_country,
            'production_countries' => $this->production_countries,
            'year' => $this->year,
            'vote_average' => $this->vote_average,
        ];
    }
}
