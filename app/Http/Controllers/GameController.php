<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Comment;
use App\Models\Game;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JmesPath\CompilerRuntime;
use function App\bgg_query;
use function App\extract_subarray;
use function App\lt_translate;

class GameController extends Controller
{
    private string $game_formatter = "items.item.{
            bgge_id: xml_attr.id,
            name: ([name][])[?xml_attr.type=='primary']|[0].xml_attr.value,
            thumb_url: thumbnail.value,
            image_url: image.value,
            description: description.value,
            pub_year: yearpublished.xml_attr.value,
            min_age: minage.xml_attr.value,
            min_players: minplayers.xml_attr.value,
            max_players: maxplayers.xml_attr.value,
            box_time: playingtime.xml_attr.value,
            min_time: minplaytime.xml_attr.value,
            max_time: maxplaytime.xml_attr.value,
            artists: ([link][])[?xml_attr.type=='boardgameartist'].{
                bgge_id: xml_attr.id,
                name: xml_attr.value },
            publishers: ([link][])[?xml_attr.type=='boardgamepublisher'].{
                bgge_id:xml_attr.id,
                name: xml_attr.value }
            designers: ([link][])[?xml_attr.type=='boardgamedesigner'].{
                bgge_id:xml_attr.id,
                name: xml_attr.value }
            }";

    private string $versions_formatter = "items.item.versions |
            ([item][])[?contains(map(&contains(['French','English','German', 'Italian'], @),
                ([link][])[?xml_attr.type == 'language'].xml_attr.value),`true`)].{
            bgge_id: xml_attr.id,
            thumb_url: thumbnail.value,
            image_url: image.value,
            languages: ([link][])[?xml_attr.type == 'language'].xml_attr.id,
            name: canonicalname.xml_attr.value,
            pub_year: yearpublished.xml_attr.value,
            artists: ([link][])[?xml_attr.type=='boardgameartist'].{
                bgge_id: xml_attr.id,
                name: xml_attr.value },
            publishers: ([link][])[?xml_attr.type=='boardgamepublisher'].{
                bgge_id:xml_attr.id,
                name: xml_attr.value },
            designers: ([link][])[?xml_attr.type=='boardgamedesigner'].{
                id:xml_attr.id,
                name: xml_attr.value }
            }";

    private function addGame(array $game, Collection $avail_languages, ?string  $version_of) {
        // Extracting data for fast reuse
        $id = $game['bgge_id'];
        $artists = extract_subarray('artists', $game);
        $publishers = extract_subarray('publishers', $game);
        $designers = extract_subarray('designers', $game);
        $languages = extract_subarray('languages', $game);
        $description = $game['description'] ?? null;

        // Adding the game parent if needed
        $game['version_of'] = $version_of;
        $game['last_sync_at'] = now();

        //Adding the game
        $db_game = Game::updateOrCreate(['bgge_id' => $game['bgge_id']], $game);

        // Addding the roles and bgge_ids to different roles
        if($artists){
            $artists = array_map(fn($array): array =>
                array_merge($array, ['role' => 'Artist', 'updated_at' => now(), 'created_at' => now()]), $artists);
        }
        if ($publishers) {
            $publishers = array_map(fn($array): array =>
                array_merge($array, ['role' => 'Publisher', 'updated_at' => now(), 'created_at' => now()]), $publishers);
        }
        if ($designers) {
            $designers = array_map(fn($array): array =>
                array_merge($array, ['role' => 'Designer', 'updated_at' => now(), 'created_at' => now()]), $designers);
        }

            // Binds the roles to the game
        $roles = $designers + $artists + $publishers ;
        if ($roles && count($roles) > 0){
            $db_game->worked_on()->upsert( $roles, ['bgge_id', 'name', 'role'], ['bgge_id', 'name', 'role']);
        }

        // Binds the description to the game
        if ($description) {
            $desc_hash = hash('sha256', $description);
            $game['description_hash'] = $desc_hash;
            $description = lt_translate($description);
            $descriptions = array_map((fn($lang,  $text): array =>
            ['en_hash' => $desc_hash, 'lang' => $lang, 'description' => $text]),array_keys($description), $description);
            $db_game->descriptions()->upsert($descriptions, ['lang', 'game_id']);
        }

        // Binds the languages to the game
        if ($languages){
            $languages_to_add = array_intersect($languages, $avail_languages->keys()->toArray());
            $codes_to_add = array_map(fn($lang) => $avail_languages[$lang]['code'], $languages_to_add);
            $db_game->languages()->sync($codes_to_add);
        }
    }

    private function getGameFromDB(string $id)
    {
        Log::debug("Getting game ". $id . " from database");
        $game = Game::with(['worked_on', 'descriptions', 'comments', 'parent'])->find($id);
        $versions = $game->versions()->get(['bgge_id', 'name', 'pub_year', 'thumb_url']);
        return ['game' => $game->toResource(), 'versions' => $versions];
    }

    private function ensureGameInDB(string $id){
        $game = Game::whereKey($id)
                ->first();
        if ($game?->last_sync_at->isLastWeek()) {
            Log::debug("Game " . $id . " found in database.");
            return null;
            }
        Log::debug("Game " . $id . " not found in database.");

        // Querying required data
        $languages = Language::get()->keyBy('bgg_index');
        $bgg_res = bgg_query('thing', ['id' => $game?->parent()->getParentKey() ?? $id, 'versions' => 1]);
        Log::debug(json_encode($bgg_res));
        // Extracting from BGG response
        $jmes = new CompilerRuntime('storage/jmespath');
        $game = $jmes($this->game_formatter, $bgg_res);
        if (!$game){
            return $bgg_res;
        }
        $versions = $jmes($this->versions_formatter, $bgg_res);

        // Add the game and its versions
        $this->addGame($game, $languages, null);
        foreach ($versions as $version){
            $this->addGame($version, $languages, $game['bgge_id']);
        }
        Log::debug("Game " . $id . " added to database.");
        return null;
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

        if ($res = $this->ensureGameInDB($id)){
            return response()->json($res);
        }

        return response()->json($this->getGameFromDB($id));
    }
}
