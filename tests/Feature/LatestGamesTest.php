<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\Game;
use App\Models\Language;
use App\Models\Question;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
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

    public function test_latest_endpoint_orders_by_most_recent_comment_or_answer(): void
    {
        $this->seed([QuestionSeeder::class, LanguageSeeder::class]);

        $lang = Language::first();
        $user = User::factory()->create(['lang' => $lang->code]);
        $commentGame = Game::create(['name' => 'Comment Activity', 'thumb_url' => 'https://example.com/comment.jpg']);
        $answerGame = Game::create(['name' => 'Answer Activity', 'thumb_url' => 'https://example.com/answer.jpg']);
        $question = Question::where('is_enabled', true)->first();

        Answer::create([
            'user_id' => $user->id,
            'game_id' => $answerGame->id,
            'question_id' => $question->id,
            'value' => 4,
        ])->forceFill([
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ])->save();

        $tk = TranslationKey::create(['hash' => 'latest_comment_hash', 'context' => 'game_comment']);
        Translation::create([
            'translation_id' => $tk->id,
            'language_code' => $lang->code,
            'text' => 'Newest comment activity',
        ]);
        Comment::create([
            'translation_id' => $tk->id,
            'game_id' => $commentGame->id,
            'writer' => $user->id,
            'lang' => $lang->code,
        ]);

        Cache::flush();

        $response = $this->actingAs($user)->getJson('/api/latest');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $commentGame->id)
            ->assertJsonPath('1.id', $answerGame->id)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'thumb_url'],
            ]);
    }
}
