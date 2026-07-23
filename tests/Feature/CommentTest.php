<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Game;
use App\Models\Language;
use App\Models\Role;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $other;
    protected Game $game;
    protected Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, LanguageSeeder::class]);

        $lang = Language::first();
        $this->owner = User::factory()->create(['lang' => $lang->code]);
        $this->other = User::factory()->create(['lang' => $lang->code]);

        $this->game = Game::create(['name' => 'Test Game']);

        $tk = TranslationKey::create(['hash' => 'comment_hash', 'context' => 'game_comment']);
        Translation::create([
            'translation_id' => $tk->id,
            'language_code' => $lang->code,
            'text' => 'Original comment',
        ]);

        $this->comment = Comment::create([
            'translation_id' => $tk->id,
            'game_id' => $this->game->id,
            'writer' => $this->owner->id,
            'lang' => $lang->code,
        ]);
    }

    public function test_owner_can_update_comment(): void
    {
        $response = $this->actingAs($this->owner)->putJson(
            route('api.comment.update', $this->comment),
            ['comment' => 'Updated comment text']
        );

        $response->assertOk();
        $this->assertDatabaseHas('translation_keys', ['context' => 'game_comment']);
    }

    public function test_other_user_cannot_update_comment(): void
    {
        $response = $this->actingAs($this->other)->putJson(
            route('api.comment.update', $this->comment),
            ['comment' => 'Hacked comment']
        );

        $response->assertForbidden();
    }

    public function test_owner_can_delete_comment(): void
    {
        $response = $this->actingAs($this->owner)->deleteJson(
            route('api.comment.delete', $this->comment)
        );

        $response->assertOk();
        $this->assertDatabaseMissing('comments', ['id' => $this->comment->id]);
    }

    public function test_other_user_cannot_delete_comment(): void
    {
        $response = $this->actingAs($this->other)->deleteJson(
            route('api.comment.delete', $this->comment)
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $this->comment->id]);
    }
}
