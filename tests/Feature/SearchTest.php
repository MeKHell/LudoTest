<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Source;
use App\Models\User;
use App\Models\TranslationKey;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Source::updateOrCreate(
            ['slug' => 'bgg'],
            ['name' => 'BoardGameGeek', 'base_url' => 'https://boardgamegeek.com/xmlapi2']
        );

        $this->actingAs(User::factory()->create());
    }

    public function test_unified_search_merges_local_and_external_results()
    {
        // 1. Setup local game (Catan ID 13)
        $localGame = Game::create(['name' => 'Local Catan', 'pub_year' => 1995]);
        $source = Source::where('slug', 'bgg')->first();
        $localGame->gameSources()->create([
            'source_id' => $source->id,
            'external_id' => '13',
            'raw_data' => [],
            'last_sync_at' => now(),
        ]);

        // 2. Mock BGG Search API with fixture
        $xml = file_get_contents(base_path('tests/fixtures/bgg/search_catan.xml'));
        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response($xml, 200)
        ]);

        // 3. Perform search with a higher limit to capture merged results
        $response = $this->getJson('/api/search?q=Catan&src=bgg&limit=150');

        $response->assertStatus(200);
        $results = $response->json();

        // Should have many results (Catan search has many matches)
        // Catan (ID 13) should be marked as local
        $this->assertNotEmpty($results);
        
        $catan = collect($results)->firstWhere('external_id', '13');
        $this->assertNotNull($catan, "Catan (ID 13) not found in results");
        $this->assertTrue($catan['is_local']);
        $this->assertEquals($localGame->id, $catan['id']);
        
        // Find another game that isn't local (e.g., ID 110308 - 7 Wonders: Catan)
        $newGame = collect($results)->firstWhere('external_id', '110308');
        $this->assertNotNull($newGame, "7 Wonders: Catan (ID 110308) not found in results");
        $this->assertFalse($newGame['is_local']);
    }

    public function test_search_ranks_results_by_relevance()
    {
        // Setup local games
        Game::create(['name' => 'ExactMatch']);
        Game::create(['name' => 'ExactMatch Extended']);
        
        $tk = TranslationKey::create(['hash' => 'test_hash']);
        Translation::create([
            'translation_id' => $tk->id,
            'language_code' => 'en',
            'text' => 'This game mentions ExactMatch in description'
        ]);
        $descMatch = Game::create(['name' => 'Other Title', 'translation_id' => $tk->id]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->getJson('/api/search?q=ExactMatch&src=bgg');

        $results = $response->json();
        
        // Expected order:
        // 1. ExactMatch (Score 100)
        // 2. ExactMatch Extended (Score 80 - starts with)
        // 3. Other Title (Score 40 - description match)
        
        $this->assertEquals('ExactMatch', $results[0]['name']);
        $this->assertEquals('ExactMatch Extended', $results[1]['name']);
        $this->assertEquals('Other Title', $results[2]['name']);
    }

    public function test_search_handles_description_matches()
    {
        // 1. Setup local game with description match only
        $tk = TranslationKey::create(['hash' => 'desc_only_hash']);
        Translation::create([
            'translation_id' => $tk->id,
            'language_code' => 'en',
            'text' => 'This game is about space exploration'
        ]);
        $game = Game::create(['name' => 'Galactic Journey', 'translation_id' => $tk->id]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        // 2. Perform search for "space"
        $response = $this->getJson('/api/search?q=space&src=bgg');

        $response->assertStatus(200);
        $results = $response->json();
        
        $match = collect($results)->firstWhere('name', 'Galactic Journey');
        $this->assertNotNull($match);
        $this->assertEquals(40, $match['score']);
    }
}
