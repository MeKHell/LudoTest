<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $games = Cache::remember('recent_games', 60, function () {
            return Game::query()
                ->select('games.id', 'games.name', 'games.thumb_url')
                ->selectRaw('(
                    SELECT MAX(activity_at) FROM (
                        SELECT MAX(created_at) AS activity_at FROM comments WHERE game_id = games.id
                        UNION ALL
                        SELECT MAX(created_at) AS activity_at FROM answers WHERE game_id = games.id
                    )
                ) AS last_activity_at')
                ->where(function ($query) {
                    $query->whereHas('comments')
                        ->orWhereHas('answers');
                })
                ->orderByDesc('last_activity_at')
                ->limit(10)
                ->get()
                ->map(fn (Game $game) => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'thumb_url' => $game->thumb_url,
                ])
                ->values();
        });

        return response()->json($games);
    }

    public function getRandom(Request $request): JsonResponse
    {
        // Cache the random selection (IDs only) for everyone for 1 hour so the
        // list stays stable. Library badges are still resolved per-user below.
        $ids = Cache::remember('random_games', now()->addHour(), function () {
            return Game::query()
                ->whereNull('version_of')
                ->inRandomOrder()
                ->limit(20)
                ->pluck('id')
                ->all();
        });

        if ($ids === []) {
            return response()->json([]);
        }

        $games = Game::query()
            ->with('gameSources', 'languages')
            ->whereIn('id', $ids)
            ->get();

        $results = $games->map(fn (Game $game) => [
            'game' => $game,
            'external' => null,
            'score' => 0,
        ]);

        return response()->json(\App\Http\Resources\SearchResultResource::collection($results));
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:200'],
            'src' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'min_players' => ['nullable', 'integer', 'min:1'],
            'max_players' => ['nullable', 'integer', 'min:1', 'gte:min_players'],
            'min_time' => ['nullable', 'integer', 'min:1'],
            'max_time' => ['nullable', 'integer', 'min:1', 'gte:min_time'],
            'min_age' => ['nullable', 'integer', 'min:1'],
            'filter_mode' => ['nullable', 'string', 'in:strict,loose'],
        ]);

        $query = $validated['q'];
        $src = $validated['src'] ?? null;
        $limit = min(50, max(1, (int) ($validated['limit'] ?? 50)));
        $page = max(1, (int) ($validated['page'] ?? 1));

        $filters = array_filter([
            'min_players' => $validated['min_players'] ?? null,
            'max_players' => $validated['max_players'] ?? null,
            'min_time' => $validated['min_time'] ?? null,
            'max_time' => $validated['max_time'] ?? null,
            'min_age' => $validated['min_age'] ?? null,
        ], fn ($value) => $value !== null);
        $filterMode = $validated['filter_mode'] ?? 'strict';

        $filterKey = $filters === [] ? '' : '_'.md5(json_encode([$filters, $filterMode]));
        $key = "search_game_{$query}_{$src}{$filterKey}";
        $allResults = Cache::remember($key, now()->addDay(), function () use ($query, $src, $filters, $filterMode) {
            return $this->gameService->search($query, $src, $filters, $filterMode);
        });

        $results = $allResults->slice(($page - 1) * $limit, $limit)->values();

        return response()->json(\App\Http\Resources\SearchResultResource::collection($results));
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
