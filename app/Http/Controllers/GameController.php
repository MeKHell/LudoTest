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
    private function addGame(array $game, string $parent = null) {
        $description = $game['en_description'];
        $artists = $game['artists'];
        $publishers = $game['publishers'];
        $designers = $game['designers'];

        unset($game['en_description']);
        unset($game['artists']);
        unset($game['publishers']);
        unset($game['designers']);
        if ($parent) {
            $game['version_of'] = $parent;
        }

        $db_game = Game::updateOrCreate(['bgge_id' => $game['bgge_id']], $game);



    }
    private function getGame(string $id){
        $game = Game::where('updated_at', '>', now()->subMinute())->find($id);
        if ($game){
            Log::debug("Game found in database.");
            return $game;
        }
        $bgg_res = bgg_query('thing', ['id' => $id, 'versions' => 1]);

        Log::debug("Game not found in database.");

        $jmes = new CompilerRuntime('storage/jmespath');
        $game_formatter = "items.item.{
            bgge_id: xml_attr.id,
            name: name[?xml_attr.type=='primary']|[0].xml_attr.value,
            thumb_url: thumbnail.value,
            image_url: image.value,
            en_description: description.value,
            pub_year: yearpublished.xml_attr.value,
            min_age: minage.xml_attr.value,
            box_time: playingtime.xml_attr.value,
            min_time: minplaytime.xml_attr.value,
            max_time: maxplaytime.xml_attr.value,
            artists: link[?xml_attr.type=='boardgameartist'].{
                bgge_id: xml_attr.id,
                name: xml_attr.value },
            publishers: link[?xml_attr.type=='boardgamepublisher'].{
                bgge_id:xml_attr.id,
                name: xml_attr.value }
            designers: link[?xml_attr.type=='boardgamedesigner'].{
                bgge_id:xml_attr.id,
                name: xml_attr.value }
            }";
        $versions_formatter = "items.item.versions.
            item[?contains(map(&contains(['French','English','German', 'Italian'], @),link[?xml_attr.type == 'language'].xml_attr.value),`true`)].{
            bgge_id: xml_attr.id,
            thumbnail: thumbnail.value,
            image: image.value,
            languages: link[?xml_attr.type == 'language'].xml_attr.value,
            name: canonicalname.xml_attr.value,
            pub_year: yearpublished.xml_attr.value,
            artist: link[?xml_attr.type=='boardgameartist'].{
                bgge_id: xml_attr.id,
                name: xml_attr.value },
            publisher: link[?xml_attr.type=='boardgamepublisher'].{
                bgge_id:xml_attr.id,
                name: xml_attr.value },
            designer: link[?xml_attr.type=='boardgamedesigner'].{
                id:xml_attr.id,
                name: xml_attr.value }
            }";
        $game = $jmes($game_formatter, $bgg_res);
        $versions = $jmes($versions_formatter, $bgg_res);
        return ["game" => $game, "versions" => $versions];
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

        $key = "search_{$query}";
        $result = Cache::get($key);
        if ($result){
            Log::debug("Search found in database");
            return $result;
        }
        Log::debug("Search not found in database");
        $bgg_res = bgg_query('search', ['query' => $query]);
        Cache::put("search_{$query}", $bgg_res);


        $jmes = new CompilerRuntime('storage/jmespath');
        $formatter = "items.item[].{bgge_id: xml_attr.id, name:name.xml_attr.value, pub_year: yearpublished.xml_attr.value}";
        $result = $jmes($formatter, $bgg_res);
        Cache::put($key, $result);
        return $result;
    }

    public function get(string $id): JsonResponse
    {
        return response()->json($this->getGame($id));
    }
}
