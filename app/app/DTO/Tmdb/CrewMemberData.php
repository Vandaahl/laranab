<?php declare(strict_types=1);

namespace App\DTO\Tmdb;

final readonly class CrewMemberData extends PersonData
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

        public string $creditId,
        public string $department,
        public string $job,
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

            creditId: $data['credit_id'],
            department: $data['department'],
            job: $data['job'],
        );
    }
}
