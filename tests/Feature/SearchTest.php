<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\LibraryEntry;
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
        $localGame = Game::create(['name' => 'Local Catan', 'pub_year' => 1995]);
        $source = Source::where('slug', 'bgg')->first();
        $localGame->gameSources()->create([
            'source_id' => $source->id,
            'external_id' => '13',
            'raw_data' => [],
            'last_sync_at' => now(),
        ]);

        $xml = file_get_contents(base_path('tests/fixtures/bgg/search_catan.xml'));
        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response($xml, 200)
        ]);

        $response = $this->getJson('/api/search?q=Catan&src=bgg&limit=50');

        $response->assertStatus(200);
        $results = $response->json();

        $this->assertNotEmpty($results);

        $catan = collect($results)->firstWhere('external_id', '13');
        $this->assertNotNull($catan, "Catan (ID 13) not found in results");
        $this->assertTrue($catan['is_local']);
        $this->assertEquals($localGame->id, $catan['id']);

        $hasExternal = collect($results)->contains(fn ($r) => ! $r['is_local']);
        $this->assertTrue($hasExternal, 'Expected at least one external-only result');
    }

    public function test_search_ranks_results_by_relevance()
    {
        Game::create(['name' => 'ExactMatch']);
        Game::create(['name' => 'ExactMatch Extended']);
        
        $tk = TranslationKey::create(['hash' => 'test_hash']);
        Translation::create([
            'translation_id' => $tk->id,
            'language_code' => 'en',
            'text' => 'This game mentions ExactMatch in description'
        ]);
        Game::create(['name' => 'Other Title', 'translation_id' => $tk->id]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->getJson('/api/search?q=ExactMatch&src=bgg');

        $results = $response->json();
        
        $this->assertEquals('ExactMatch', $results[0]['name']);
        $this->assertEquals('ExactMatch Extended', $results[1]['name']);
        $this->assertEquals('Other Title', $results[2]['name']);
    }

    public function test_search_handles_description_matches()
    {
        $tk = TranslationKey::create(['hash' => 'desc_only_hash']);
        Translation::create([
            'translation_id' => $tk->id,
            'language_code' => 'en',
            'text' => 'This game is about space exploration'
        ]);
        Game::create(['name' => 'Galactic Journey', 'translation_id' => $tk->id]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->getJson('/api/search?q=space&src=bgg');

        $response->assertStatus(200);
        $results = $response->json();
        
        $match = collect($results)->firstWhere('name', 'Galactic Journey');
        $this->assertNotNull($match);
        $this->assertEquals(40, $match['score']);
    }

    public function test_search_validates_query_length_and_limit()
    {
        $longQuery = str_repeat('a', 201);

        $this->getJson('/api/search?q='.$longQuery)
            ->assertStatus(422);

        $this->getJson('/api/search?q=test&limit=100')
            ->assertStatus(422);
    }

    public function test_search_filters_by_player_count()
    {
        Game::create(['name' => 'Small Game', 'min_players' => 2, 'max_players' => 4]);
        Game::create(['name' => 'Broad Game', 'min_players' => 1, 'max_players' => 5]);
        Game::create(['name' => 'Big Game', 'min_players' => 6, 'max_players' => 10]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->getJson('/api/search?q=Game&min_players=2&max_players=4');

        $response->assertOk();
        $names = collect($response->json())->pluck('name')->all();

        $this->assertContains('Small Game', $names);
        $this->assertNotContains('Broad Game', $names);
        $this->assertNotContains('Big Game', $names);
    }

    public function test_search_loose_player_filter_accepts_overlapping_ranges()
    {
        Game::create(['name' => 'Small Game', 'min_players' => 2, 'max_players' => 4]);
        Game::create(['name' => 'Broad Game', 'min_players' => 1, 'max_players' => 5]);
        Game::create(['name' => 'Big Game', 'min_players' => 6, 'max_players' => 10]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->getJson('/api/search?q=Game&min_players=2&max_players=4&filter_mode=loose');

        $response->assertOk();
        $names = collect($response->json())->pluck('name')->all();

        $this->assertContains('Small Game', $names);
        $this->assertContains('Broad Game', $names);
        $this->assertNotContains('Big Game', $names);
    }

    public function test_search_rejects_empty_player_filter_ranges()
    {
        $this->getJson('/api/search?q=Game&min_players=5&max_players=4')
            ->assertStatus(422);
    }

    public function test_search_filters_by_strict_time_range()
    {
        Game::create(['name' => 'Short Game', 'min_time' => 20, 'max_time' => 40]);
        Game::create(['name' => 'Broad Time Game', 'min_time' => 10, 'max_time' => 90]);
        Game::create(['name' => 'Long Game', 'min_time' => 80, 'max_time' => 120]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->getJson('/api/search?q=Game&min_time=15&max_time=60');

        $response->assertOk();
        $names = collect($response->json())->pluck('name')->all();

        $this->assertContains('Short Game', $names);
        $this->assertNotContains('Broad Time Game', $names);
        $this->assertNotContains('Long Game', $names);
    }

    public function test_search_loose_time_filter_accepts_overlapping_ranges()
    {
        Game::create(['name' => 'Short Game', 'min_time' => 20, 'max_time' => 40]);
        Game::create(['name' => 'Broad Time Game', 'min_time' => 10, 'max_time' => 90]);
        Game::create(['name' => 'Long Game', 'min_time' => 80, 'max_time' => 120]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->getJson('/api/search?q=Game&min_time=15&max_time=60&filter_mode=loose');

        $response->assertOk();
        $names = collect($response->json())->pluck('name')->all();

        $this->assertContains('Short Game', $names);
        $this->assertContains('Broad Time Game', $names);
        $this->assertNotContains('Long Game', $names);
    }

    public function test_search_rejects_empty_time_filter_ranges()
    {
        $this->getJson('/api/search?q=Game&min_time=90&max_time=60')
            ->assertStatus(422);
    }

    public function test_search_includes_library_state_for_authenticated_user()
    {
        $game = Game::create(['name' => 'Owned Game']);
        $user = User::factory()->create();

        LibraryEntry::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'list_type' => 'owned',
        ]);

        Http::fake([
            'boardgamegeek.com/xmlapi2/search*' => Http::response('<?xml version="1.0" encoding="utf-8"?><items total="0"></items>', 200)
        ]);

        $response = $this->actingAs($user)->getJson('/api/search?q=Owned');

        $response->assertOk();
        $result = collect($response->json())->firstWhere('name', 'Owned Game');
        $this->assertTrue($result['in_user_library']);
        $this->assertContains('owned', $result['list_types']);
    }
}
