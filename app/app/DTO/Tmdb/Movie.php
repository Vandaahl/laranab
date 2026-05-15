<?php declare(strict_types=1);

namespace App\DTO\Tmdb;

final readonly class Movie
{
    public function __construct(
        public array $genres,
        public int $tmdb_id,
        public string $imdb_id,
        public string $original_language,
        public string $original_title,
        public string $title,
        public string $overview,
        public int $runtime,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            genres: $data['genres'],
            tmdb_id: $data['id'],
            imdb_id: $data['imdb_id'],
            original_language: $data['original_language'],
            original_title: $data['original_title'],
            title: $data['title'],
            overview: $data['overview'],
            runtime: $data['runtime'],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'imdb_id' => $this->imdb_id,
            'tmdb_id' => $this->tmdb_id,
            'original_title' => $this->original_title,
            'original_language' => $this->original_language,
            'overview' => $this->overview,
            'runtime' => $this->runtime,
            'genres' => $this->genres
        ];
    }
}
