<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Game;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\QuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LatestGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_latest_endpoint_returns_games_with_recent_votes(): void
    {
        $this->seed(QuestionSeeder::class);

        $user = User::factory()->create();
        $olderGame = Game::create(['name' => 'Older Game', 'thumb_url' => 'https://example.com/older.jpg']);
        $recentGame = Game::create(['name' => 'Recent Game', 'thumb_url' => 'https://example.com/recent.jpg']);
        $question = Question::where('is_enabled', true)->first();

        Answer::create([
            'user_id' => $user->id,
            'game_id' => $olderGame->id,
            'question_id' => $question->id,
            'value' => 3,
        ])->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();
        Answer::create([
            'user_id' => $user->id,
            'game_id' => $recentGame->id,
            'question_id' => $question->id,
            'value' => 5,
        ]);

        Cache::flush();

        $response = $this->actingAs($user)->getJson('/api/latest');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $recentGame->id)
            ->assertJsonPath('0.name', 'Recent Game')
            ->assertJsonPath('1.id', $olderGame->id);
    }
}
