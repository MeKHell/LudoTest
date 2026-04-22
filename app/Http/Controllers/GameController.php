<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GameController extends Controller
{
    public function __construct(
        protected GameService $gameService
    ) {}

    public function getTrending(): JsonResponse
    {
        $topGames = Cache::remember('trending_games', 60, function() {
             return Game::withCount('comments')
                ->orderBy('comments_count', 'desc')
                ->take(10)
                ->get();
        });
        return response()->json($topGames);
    }

    public function getLatest(): JsonResponse
    {
        $games = Cache::remember('recent_games', 60, function() {
            return Game::query()
                ->whereHas('comments')
                ->orderByDesc(
                    Comment::select('created_at')
                        ->whereColumn('game_id', 'games.id')
                        ->latest()
                        ->limit(1)
                )
                ->take(10)
                ->get();
        });
        return response()->json($games);
    }

    public function search(): JsonResponse
    {
        $query = request()->input('q');
        if (!$query) {
            return response()->json([]);
        }

        $key = "search_{$query}";
        $results = Cache::remember($key, now()->addDay(), function() use ($query) {
            return $this->gameService->search($query);
        });

        return response()->json($results);
    }

    public function get(string $id, ?string $src = null)
    {
        if ($src) {
            $game = $this->gameService->ensureGameInDb($id, $src);
        } else {
            $game = Game::findOrFail($id);
        }

        // Load relationships for the resource
        $game->load(['worked_on', 'translationKey', 'comments', 'parent', 'answers']);
        
        $versions = $game->versions()->with('languages')->get();

        return response()->json([
            'game' => new \App\Http\Resources\GameResource($game),
            'versions' => \App\Http\Resources\GameResource::collection($versions)
        ]);
    }
}
