<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Game;

use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JmesPath\CompilerRuntime;
use function App\bgg_query;
use function App\extract_subarray;

class GameController extends Controller
{
    private array $game_formatter =["bgg" => "items.item.{
            src_id: xml_attr.id,
            src: 'bgg',
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
                src_id: xml_attr.id,
                name: xml_attr.value },
            publishers: ([link][])[?xml_attr.type=='boardgamepublisher'].{
                src_id:xml_attr.id,
                name: xml_attr.value }
            designers: ([link][])[?xml_attr.type=='boardgamedesigner'].{
                src_id:xml_attr.id,
                name: xml_attr.value }
            }"];
    private array $versions_formatter = ["bgg" => "items.item.versions |
            ([item][])[?contains(map(&contains(['French','English','German', 'Italian'], @),
                ([link][])[?xml_attr.type == 'language'].xml_attr.value),`true`)].{
            src_id: xml_attr.id,
            src_id: 'bggv',
            thumb_url: thumbnail.value,
            image_url: image.value,
            languages: ([link][])[?xml_attr.type == 'language'].xml_attr.id,
            name: canonicalname.xml_attr.value,
            pub_year: yearpublished.xml_attr.value,
            artists: ([link][])[?xml_attr.type=='boardgameartist'].{
                src_id: xml_attr.id,
                name: xml_attr.value },
            publishers: ([link][])[?xml_attr.type=='boardgamepublisher'].{
                src_id:xml_attr.id,
                name: xml_attr.value },
            designers: ([link][])[?xml_attr.type=='boardgamedesigner'].{
                src_id:xml_attr.id,
                name: xml_attr.value }
            }"];

    private array $src_get = [];


    public function __construct(){
        $this->src_get =  [
            "bgg" => fn($id) =>
                bgg_query('thing', ['id' => $id, 'versions' => 1])
        ];
    }
    private function addGame(array $game_data, Collection $avail_languages, ?string $version_of) {
        // Extracting data for fast reuse
        $artists = extract_subarray('artists', $game_data);
        $publishers = extract_subarray('publishers', $game_data);
        $designers = extract_subarray('designers', $game_data);
        $languages = extract_subarray('languages', $game_data);
        $description = $game_data['description'] ?? null;

        // Adding the game parent if needed
        $game_data['version_of'] = $version_of;
        $game_data['last_sync_at'] = now();

        //Adding the game
        $db_game = Game::updateOrCreate(["src_id" => $game_data['src_id'], "src" => $game_data['src']], $game_data);

        // Addding the roles and ids to different roles
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
            $db_game->worked_on()->upsert( $roles, ['game_id', 'src_id', 'role'], ['game_id', 'src_id', 'name', 'role']);
        }

        // Binds the description to the game
        if ($description) {
            $tk = TranslationKeyController::ADD($description, "en", "game_description");
            $db_game->translationKey()->associate($tk);
            $db_game->save();
        } else if ($db_game->parent()->select(['translation_id'])->first()){
            $db_game = $db_game->translationKey()->associate($db_game->parent()->first()->translation_id);
            $db_game->save();
        }

        // Binds the languages to the game
        if ($languages){
            $languages_to_add = array_intersect($languages, $avail_languages->keys()->toArray());
            $codes_to_add = array_map(fn($lang) => $avail_languages[$lang]['code'], $languages_to_add);
            $db_game->languages()->sync($codes_to_add);
        }
    }

    private function getGameFromDB(string $id, ?string $src)
    {
        if ($src){
            $game = Game::with(['worked_on', 'translationKey', 'comments', 'parent', 'answers'])
                    ->where(["games.src_id" => $id, "games.src" => $src])->first();
        } else {
            $game = Game::with(['worked_on', 'translationKey', 'comments', 'parent', 'answers'])->find($id);
        }
        $versions = $game->versions()->get(['id', 'name', 'pub_year', 'thumb_url']);
        return ['game' => $game->toResource(), 'versions' => $versions];
    }

    private function ensureGameInDB(string $id, ?string $src){
        if ($src){
            $game = Game::where(["games.src_id" => $id, "games.src" => $src])->first();
        } else {
            $game = Game::find($id);
            $src = $game->src;
            $src = ($src == "bggv") ? $src : "bgg";
        }
        if ($game?->last_sync_at->isAfter(now()->minus(minutes: 1))) {
            Log::debug("Game " . $id . " found in database.");
            return null;
            }
        Log::debug("Game " . $id . " not found in database.");

        // Querying required data
        $languages = Language::get()->keyBy('bgg_index');
        Log::Debug($game->parent()->get('src_id')->src_id);
        // We first need to check the data from the game since we might query it without any $src
        $query_res = $this->src_get[$src ?? $game->src]($game->parent()->get('src_id')->src_id ?? $game->src_id ?? $id);
        // Extracting from qurey response
        $jmes = new CompilerRuntime('storage/jmespath');
        $game_data = $jmes($this->game_formatter[$game->src ?? $src], $query_res);
        if (!$game_data){
            return $query_res;
        }
        $versions = $jmes($this->versions_formatter[$game->src ?? $src], $query_res);

        // Add the game and its versions
        $this->addGame($game_data, $languages, null);
        foreach ($versions as $version){
            $this->addGame($version, $languages, $game['id']);
        }
        return null;
    }

    public function getTrending(): JsonResponse
    {
        $topGames = Cache::remember('trending_games', 60, Game::withCount('comments')
            ->orderBy('comments_count', 'desc')
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
        /*if ($result){
            Log::debug("Search found in database");
            return $result;
        }*/
        Log::debug("Search not found in database");
        $bgg_res = bgg_query('search', ['query' => $query]);
        Cache::put("search_{$query}", $bgg_res);
        Log::debug($bgg_res);
        $jmes = new CompilerRuntime('storage/jmespath');
        $formatter = "items.item[].{id: xml_attr.id, name:name.xml_attr.value, pub_year: yearpublished.xml_attr.value}";
        //$result = $jmes($formatter, $bgg_res);
        Cache::put($key, $result);
        return $bgg_res;
    }

    public function get(string $id, ?string $src = null): JsonResponse
    {

        if ($res = $this->ensureGameInDB($id, $src)){
            return response()->json($res);
        }

        return response()->json($this->getGameFromDB($id, $src));
    }
}
