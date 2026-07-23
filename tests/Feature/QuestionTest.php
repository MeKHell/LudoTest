<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Game;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\QuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_questions_endpoint_returns_top_five_games_per_question(): void
    {
        $this->seed(QuestionSeeder::class);

        $user = User::factory()->create();
        $question = Question::where('is_enabled', true)->orderBy('id')->first();

        $topGame = Game::create(['name' => 'Top Game', 'thumb_url' => 'https://example.com/top.jpg']);
        $secondGame = Game::create(['name' => 'Second Game', 'thumb_url' => 'https://example.com/second.jpg']);
        $thirdGame = Game::create(['name' => 'Third Game']);

        Answer::create([
            'user_id' => $user->id,
            'game_id' => $topGame->id,
            'question_id' => $question->id,
            'value' => 5,
        ]);
        Answer::create([
            'user_id' => $user->id,
            'game_id' => $secondGame->id,
            'question_id' => $question->id,
            'value' => 4,
        ]);
        Answer::create([
            'user_id' => $user->id,
            'game_id' => $thirdGame->id,
            'question_id' => $question->id,
            'value' => 3,
        ]);

        $response = $this->actingAs($user)->getJson('/api/questions');

        $response->assertOk()
            ->assertJsonCount(5, 'questions')
            ->assertJsonPath('questions.0.top_games.0.id', $topGame->id)
            ->assertJsonPath('questions.0.top_games.0.name', 'Top Game')
            ->assertJsonPath('questions.0.top_games.0.thumb_url', 'https://example.com/top.jpg')
            ->assertJsonPath('questions.0.top_games.0.average_score', 5)
            ->assertJsonPath('questions.0.top_games.1.id', $secondGame->id)
            ->assertJsonPath('questions.0.top_games.2.id', $thirdGame->id);
    }
}
