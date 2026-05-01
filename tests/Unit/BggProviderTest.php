<?php

namespace Tests\Unit;

use App\Providers\GameSources\BggProvider;
use App\DTOs\ExternalGameData;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BggProviderTest extends TestCase
{
    use RefreshDatabase;

    private BggProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed();

        $this->provider = new BggProvider('bgg');
    }

    public function test_fetch_by_id_returns_correct_dto()
    {
        $xml = file_get_contents(base_path('tests/fixtures/bgg/id13.xml'));

        Http::fake([
            'boardgamegeek.com/xmlapi2/thing*' => Http::response($xml, 200),
        ]);

        $dto = $this->provider->fetchById('13');

        $this->assertInstanceOf(ExternalGameData::class, $dto);
        $this->assertEquals('13', $dto->externalId);
        $this->assertEquals('Catan', $dto->name);
        $this->assertEquals(1995, $dto->pubYear);
        
        // Catan has many artists, publishers, designers in real world data
        $this->assertGreaterThan(50, count($dto->roles)); 
        
        // Check for specific known roles if needed, e.g. Klaus Teuber
        $designers = array_values(array_filter($dto->roles, fn($r) => $r->role === 'Designer'));
        $this->assertNotEmpty($designers);
        $this->assertEquals('Klaus Teuber', $designers[0]->name);

        $this->assertGreaterThan(0, count($dto->versions));
    }

    public function test_search_returns_collection_of_dtos()
    {
        $xml = file_get_contents(base_path('tests/fixtures/bgg/search_catan.xml'));

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response($xml, 200),
        ]);

        $results = $this->provider->search('Catan');

        $this->assertGreaterThan(100, $results->count());
        $this->assertInstanceOf(ExternalGameData::class, $results->first());
        
        // Find 'Catan' with id 13 in results
        $catan = $results->first(fn($r) => $r->externalId === '13');
        $this->assertNotNull($catan);
        $this->assertEquals('Catan', $catan->name);
    }
}
