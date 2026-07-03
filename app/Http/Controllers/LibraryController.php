<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Game;
use App\Models\LibraryEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $entries = LibraryEntry::query()
            ->where('user_id', $request->user()->id)
            ->with('game')
            ->latest()
            ->get();

        return response()->json($entries->map(fn (LibraryEntry $entry) => [
            'id' => $entry->id,
            'list_type' => $entry->list_type,
            'game' => [
                'id' => $entry->game->id,
                'name' => $entry->game->name,
                'thumb_url' => $entry->game->thumb_url,
                'pub_year' => $entry->game->pub_year,
            ],
            'created_at' => $entry->created_at,
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'list_type' => ['required', Rule::in(['owned', 'wishlist'])],
        ]);

        $entry = LibraryEntry::firstOrCreate([
            'user_id' => $request->user()->id,
            'game_id' => $validated['game_id'],
            'list_type' => $validated['list_type'],
        ]);

        return response()->json([
            'id' => $entry->id,
            'list_type' => $entry->list_type,
            'game_id' => $entry->game_id,
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, LibraryEntry $libraryEntry): JsonResponse
    {
        if ($libraryEntry->user_id !== $request->user()->id) {
            abort(403);
        }

        $libraryEntry->delete();

        return response()->json(['ok' => true]);
    }
}
