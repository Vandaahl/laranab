<?php declare(strict_types=1);

namespace App\DTO\Tmdb;

final readonly class CastMember extends Person
{
    public function __construct(
        bool $adult,
        int $gender,
        int $id,
        string $knownForDepartment,
        string $name,
        string $originalName,
        float $popularity,
        ?string $profilePath,

        public int $castId,
        public string $character,
        public string $creditId,
        public int $order,
    ) {
        parent::__construct(
            adult: $adult,
            gender: $gender,
            id: $id,
            knownForDepartment: $knownForDepartment,
            name: $name,
            originalName: $originalName,
            popularity: $popularity,
            profilePath: $profilePath,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            adult: $data['adult'],
            gender: $data['gender'],
            id: $data['id'],
            knownForDepartment: $data['known_for_department'],
            name: $data['name'],
            originalName: $data['original_name'],
            popularity: $data['popularity'],
            profilePath: $data['profile_path'],

            castId: $data['cast_id'],
            character: $data['character'],
            creditId: $data['credit_id'],
            order: $data['order'],
        );
    }
}
