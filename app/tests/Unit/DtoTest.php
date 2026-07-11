<?php declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\Tmdb\CastMemberData;
use App\DTO\Tmdb\CrewMemberData;
use Tests\TestCase;

class DtoTest extends TestCase
{
    public function test_cast_member_data_instantiation(): void
    {
        $data = [
            'adult' => false,
            'gender' => 1,
            'id' => 123,
            'known_for_department' => 'Acting',
            'name' => 'John Doe',
            'original_name' => 'John Doe',
            'popularity' => 10.5,
            'profile_path' => '/path.jpg',
            'cast_id' => 1,
            'character' => 'Hero',
            'credit_id' => '502c471419c29508ed000013',
            'order' => 0,
        ];

        $dto = CastMemberData::fromArray($data);

        $this->assertInstanceOf(CastMemberData::class, $dto);
        $this->assertFalse($dto->adult);
        $this->assertEquals(123, $dto->id);
        $this->assertEquals('Hero', $dto->character);
    }

    public function test_crew_member_data_instantiation(): void
    {
        $data = [
            'adult' => false,
            'gender' => 2,
            'id' => 456,
            'known_for_department' => 'Directing',
            'name' => 'Jane Smith',
            'original_name' => 'Jane Smith',
            'popularity' => 15.2,
            'profile_path' => '/path2.jpg',
            'credit_id' => '502c471419c29508ed000014',
            'department' => 'Directing',
            'job' => 'Director',
        ];

        $dto = CrewMemberData::fromArray($data);

        $this->assertInstanceOf(CrewMemberData::class, $dto);
        $this->assertFalse($dto->adult);
        $this->assertEquals(456, $dto->id);
        $this->assertEquals('Director', $dto->job);
    }
}
