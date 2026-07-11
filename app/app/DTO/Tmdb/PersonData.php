<?php declare(strict_types=1);

namespace App\DTO\Tmdb;

abstract readonly class PersonData
{
    public function __construct(
        public bool $adult,
        public int $gender,
        public int $id,
        public ?string $knownForDepartment,
        public string $name,
        public string $originalName,
        public float $popularity,
        public ?string $profilePath,
    ) {}
}
