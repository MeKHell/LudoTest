<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\LibraryEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_and_remove_library_entry(): void
    {
        $user = User::factory()->create();
        $game = Game::create(['name' => 'Library Game']);

        $this->actingAs($user)
            ->postJson('/api/library', ['game_id' => $game->id, 'list_type' => 'owned'])
            ->assertCreated();

        $this->assertDatabaseHas('library_entries', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'list_type' => 'owned',
        ]);

        $entry = LibraryEntry::first();

        $this->actingAs($user)
            ->deleteJson('/api/library/'.$entry->id)
            ->assertOk();

        $this->assertDatabaseMissing('library_entries', ['id' => $entry->id]);
    }

    public function test_user_can_remove_game_from_category(): void
    {
        $user = User::factory()->create();
        $game = Game::create(['name' => 'Category Game']);

        LibraryEntry::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'list_type' => 'wishlist',
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/library/'.$game->id.'/wishlist')
            ->assertOk()
            ->assertJson([
                'removed' => true,
                'game_id' => $game->id,
                'list_type' => 'wishlist',
            ]);

        $this->assertDatabaseMissing('library_entries', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'list_type' => 'wishlist',
        ]);
    }

    public function test_posting_an_existing_entry_is_idempotent(): void
    {
        $user = User::factory()->create();
        $game = Game::create(['name' => 'Owned Game']);

        $this->actingAs($user)
            ->postJson('/api/library', ['game_id' => $game->id, 'list_type' => 'owned'])
            ->assertCreated();

        $this->actingAs($user)
            ->postJson('/api/library', ['game_id' => $game->id, 'list_type' => 'owned'])
            ->assertOk();

        $this->assertSame(1, LibraryEntry::where([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'list_type' => 'owned',
        ])->count());
    }

    public function test_user_cannot_delete_another_users_library_entry(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $game = Game::create(['name' => 'Protected Game']);

        $entry = LibraryEntry::create([
            'user_id' => $owner->id,
            'game_id' => $game->id,
            'list_type' => 'wishlist',
        ]);

        $this->actingAs($other)
            ->deleteJson('/api/library/'.$entry->id)
            ->assertForbidden();
    }
}
