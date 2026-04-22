<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed sources
        Source::updateOrCreate(
            ['slug' => 'bgg'],
            ['name' => 'BoardGameGeek', 'base_url' => 'https://boardgamegeek.com/xmlapi2']
        );
        Source::updateOrCreate(
            ['slug' => 'bggv'],
            ['name' => 'BoardGameGeek Versions', 'base_url' => 'https://boardgamegeek.com/xmlapi2']
        );

        $this->actingAs(User::factory()->create());
    }

    public function test_can_get_game_by_src_and_id_external()
    {
        $xml = file_get_contents(base_path('tests/fixtures/bgg/id13.xml'));

        // Mock BGG API
        Http::fake([
            'boardgamegeek.com/xmlapi2/thing*' => Http::response($xml, 200)
        ]);

        // Access via /api/game/bgg/13
        $response = $this->get(route('api.game.get', ['src' => 'bgg', 'id' => 13]));

        $response->assertStatus(200);
        $response->assertJsonPath('game.name', 'Catan');
    }

    public function test_frontend_redirects_to_internal_id()
    {
        $xml = file_get_contents(base_path('tests/fixtures/bgg/id13.xml'));
        Http::fake(['boardgamegeek.com/xmlapi2/thing*' => Http::response($xml, 200)]);

        // Access via /game/bgg/13
        $response = $this->get(route('game', ['src' => 'bgg', 'id' => 13]));

        $game = Game::where('name', 'Catan')->first();
        $response->assertRedirect(route('game.default', ['id' => $game->id]));
    }

    public function test_can_get_game_by_bggv_slug()
    {
        $xml = file_get_contents(base_path('tests/fixtures/bgg/id13.xml'));
        Http::fake(['boardgamegeek.com/xmlapi2/thing*' => Http::response($xml, 200)]);

        // Access via /api/game/bggv/13
        $response = $this->get(route('api.game.get', ['src' => 'bggv', 'id' => 13]));

        $response->assertStatus(200);
        $response->assertJsonPath('game.name', 'Catan');
        $this->assertDatabaseHas('game_sources', ['external_id' => '13', 'source_id' => Source::where('slug', 'bggv')->first()->id]);
    }

    public function test_can_get_game_by_internal_id()
    {
        // First, ensure the game is in the DB
        $xml = file_get_contents(base_path('tests/fixtures/bgg/id13.xml'));
        Http::fake(['boardgamegeek.com/xmlapi2/thing*' => Http::response($xml, 200)]);
        $this->get(route('api.game.get', ['src' => 'bgg', 'id' => 13]));

        $game = Game::where('name', 'Catan')->first();

        // Access via /api/game/1 (assuming ID is 1)
        $response = $this->get(route('api.game.get.default', ['id' => $game->id]));

        $response->assertStatus(200);
        $response->assertJsonPath('game.name', 'Catan');
        $this->assertEquals($game->id, $response->json('game.id'));
    }

    public function test_fails_on_invalid_internal_id()
    {
        $response = $this->get(route('api.game.get.default', ['id' => 99999]));
        $response->assertStatus(404);
    }
}
