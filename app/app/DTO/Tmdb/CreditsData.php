<?php declare(strict_types=1);

namespace App\DTO\Tmdb;

use Illuminate\Support\Collection;

final readonly class CreditsData
{
    public function __construct(
        public int $id,
        /** @var Collection<int, CastMemberData> */
        public Collection $cast,
        /** @var Collection<int, CrewMemberData> */
        public Collection $crew,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            cast: collect($data['cast'])
                ->map(fn (array $cast) => CastMemberData::fromArray($cast)),
            crew: collect($data['crew'])
                ->map(fn (array $crew) => CrewMemberData::fromArray($crew)),
        );
    }
}
