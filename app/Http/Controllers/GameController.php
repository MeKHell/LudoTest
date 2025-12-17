<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Game;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JmesPath\CompilerRuntime;
use function App\bgg_query;

class GameController extends Controller
{

    private function getGame(string $id){
        $game = Game::where('updated_at', '>', now()->subMinute())->find($id);
        if ($game){
            Log::debug("Game found in database");
            return $game;
        }
        $bgg_res = bgg_query('thing', ['id' => $id, 'versions' => 1]);

        Log::debug($bgg_res);

        $jmes = new CompilerRuntime('storage/jmespath');
        $formatter = "items.item.{
            id: xml_attr.id,
            thumbnail: thumbnail.value,
            image: image.value,
            en_description: description.value,
            year: yearpublished.xml_attr.value,
            age: minage.xml_attr.value,
            time: {
                box: playingtime.xml_attr.value,
                min: minplaytime.xml_attr.value,
                max: maxplaytime.xml_attr.value },
            versions: versions.item[?contains(map(&contains(['French','English','German', 'Italian'], @),link[?xml_attr.type == 'language'].xml_attr.value),`true`)].{
                id: xml_attr.id,
                thumbnail: thumbnail.value,
                image: image.value,
                languages: link[?xml_attr.type == 'language'].xml_attr.value,
                name: canonicalname.xml_attr.value,
                year: yearpublished.xml_attr.value,
                artist: link[?xml_attr.type=='boardgameartist'].{
                    id: xml_attr.id,
                    name: xml_attr.value },
                publisher: link[?xml_attr.type=='boardgamepublisher'].{
                    id:xml_attr.id,
                    name: xml_attr.value }
                }
            }";
        Log::debug("Game not found in database");

        return $jmes($formatter, $bgg_res);
    }
    public function getTrending(): JsonResponse
    {
        $topGames = Cache::remember('trending_games', 60, Game::withCount('comments')
            ->orderBy('comments_count', 'desc')  // sort by the count descending. :contentReference[oaicite:1]{index=1}
            ->take(10)  // limit to top 10
            ->get());
        return response()->json($topGames);
    }

    public function getLatest(): JsonResponse

    {
        $games = Cache::remember('recent_games', 60, Game::query()
            ->whereHas('comments')
            ->orderByDesc(
            // Subquery: pick the latest comment created_at per game
                Comment::select('created_at')
                    ->whereColumn('game_id', 'games.id')
                    ->latest()
                    ->limit(1)
            )
            ->take(10)
            ->get());
        return response()->json($games);

    }
    public function search()
    {
        $query = request()->input('q');
        $bgg_res = bgg_query('search', ['query' => $query]);


        $jmes = new CompilerRuntime('storage/jmespath');
        $formatter = "items.item[].{id: xml_attr.id, name:name.xml_attr.value, year: yearpublished.xml_attr.value}";
        return $jmes($formatter, $bgg_res);
    }

    public function get(string $id): JsonResponse
    {
        return response()->json($this->getGame($id));
    }
}
