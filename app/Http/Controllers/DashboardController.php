<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\LibraryEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'stats' => [
                'comments_count' => Comment::where('writer', $user->id)->count(),
                'votes_count' => Answer::where('user_id', $user->id)->count(),
                'library_count' => LibraryEntry::where('user_id', $user->id)->count(),
            ],
            'recentLibraryGames' => LibraryEntry::query()
                ->where('user_id', $user->id)
                ->with('game')
                ->latest()
                ->take(6)
                ->get()
                ->map(fn (LibraryEntry $entry) => [
                    'id' => $entry->game->id,
                    'name' => $entry->game->name,
                    'thumb_url' => $entry->game->thumb_url,
                    'list_type' => $entry->list_type,
                ]),
        ]);
    }
}
