<?php declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\Tmdb\CastMemberData;
use App\DTO\Tmdb\CreditsData;
use App\DTO\Tmdb\CrewMemberData;
use App\Models\Movie;
use App\Services\Api\ImageDownloader;
use App\Services\Movie\MovieProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieProcessorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_attach_same_person_as_director_and_actor(): void
    {
        $imageDownloader = $this->createMock(ImageDownloader::class);
        $processor = new MovieProcessor($imageDownloader);

        $movie = Movie::create([
            'title' => 'Test Movie',
            'imdb_id' => 'tt1234567',
            'year' => 2024,
            'imdb_score' => 8.5,
        ]);

        $creditsData = new CreditsData(
            id: 1,
            cast: collect([
                new CastMemberData(
                    adult: false,
                    gender: 2,
                    id: 100,
                    knownForDepartment: 'Acting',
                    name: 'John Doe',
                    originalName: 'John Doe',
                    popularity: 10.0,
                    profilePath: null,
                    castId: 1,
                    character: 'Main Hero',
                    creditId: 'c1',
                    order: 0
                )
            ]),
            crew: collect([
                new CrewMemberData(
                    adult: false,
                    gender: 2,
                    id: 100,
                    knownForDepartment: 'Directing',
                    name: 'John Doe',
                    originalName: 'John Doe',
                    popularity: 10.0,
                    profilePath: null,
                    creditId: 'c2',
                    department: 'Directing',
                    job: 'Director'
                )
            ])
        );

        $processor->attachDirectorsToMovie($creditsData, $movie);
        $processor->attachActorsToMovie($creditsData, $movie);

        $movie->load('credits');

        $this->assertCount(2, $movie->credits, 'Should have 2 credit entries (one for Director, one for Actor)');
        $this->assertTrue($movie->credits->contains(fn ($credit) => $credit->pivot->job === 'Director'), 'Missing Director role');
        $this->assertTrue($movie->credits->contains(fn ($credit) => $credit->pivot->job === 'Actor'), 'Missing Actor role');
    }
}
