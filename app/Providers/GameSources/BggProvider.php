<?php

namespace App\Providers\GameSources;

use App\Contracts\GameProviderInterface;
use App\DTOs\ExternalGameData;
use App\DTOs\ExternalGameRoleData;
use Illuminate\Support\Collection;
use JmesPath\CompilerRuntime;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class BggProvider implements GameProviderInterface
{
    private CompilerRuntime $jmes;

    public function __construct(
        private string $sourceSlug = 'bgg'
    ) {
        $this->jmes = new CompilerRuntime('storage/jmespath');
    }

    private string $gameFormatter = "items.item.{
            src_id: xml_attr.id,
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
                name: xml_attr.value },
            designers: ([link][])[?xml_attr.type=='boardgamedesigner'].{
                src_id:xml_attr.id,
                name: xml_attr.value },
            languages: ([link][])[?xml_attr.type == 'language'].xml_attr.id
            }";

    private function getVersionsFormatter(): string
    {
        $source = \App\Models\Source::where('slug', $this->sourceSlug)->first();
        if (! $source) {
            return 'items.item.versions | []';
        }

        $langs = \App\Models\LanguageMapping::where('source_id', $source->id)
            ->pluck('external_name')
            ->filter() // Removes empty/null items to not have ''
            ->values()
            ->all();

        // Without mappings the JMES filter matches nothing useful — fail closed.
        if ($langs === []) {
            return 'items.item.versions | []';
        }

        $langList = "'".implode("','", $langs)."'";

        return "items.item.versions |
            ([item][])[?contains(map(&contains([$langList], @),
                ([link][])[?xml_attr.type == 'language'].xml_attr.value),`true`)].{
            src_id: xml_attr.id,
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
            }";
    }

    public function fetchById(string $externalId): ExternalGameData
    {
        $rawData = $this->query('thing', ['id' => $externalId, 'versions' => 1]);

        $gameData = ($this->jmes)($this->gameFormatter, $rawData);
        $versionsData = ($this->jmes)($this->getVersionsFormatter(), $rawData) ?: [];

        return $this->mapToDto($gameData, $rawData, $versionsData);
    }

    public function search(string $query): Collection
    {
        $rawData = $this->query('search', ['query' => $query, 'type' => 'boardgame']);

        $formatter = "items.item[].{src_id: xml_attr.id, name: name.xml_attr.value, pub_year: yearpublished.xml_attr.value}";
        $results = ($this->jmes)($formatter, $rawData) ?: [];

        return collect($results)->map(fn($item) => new ExternalGameData(
            externalId: $item['src_id'],
            sourceSlug: $this->sourceSlug,
            name: $item['name'],
            pubYear: (int) ($item['pub_year'] ?? null),
            rawData: $item
        ));
    }

    private function mapToDto(array $data, array $rawData, array $versionsData = []): ExternalGameData
    {
        $roles = [];
        foreach (['artists' => 'Artist', 'publishers' => 'Publisher', 'designers' => 'Designer'] as $key => $roleName) {
            foreach ($data[$key] ?? [] as $role) {
                $roles[] = new ExternalGameRoleData(
                    name: $role['name'],
                    role: $roleName,
                    sourceSlug: $this->sourceSlug,
                    externalId: $role['src_id']
                );
            }
        }

        $versions = array_map(function($v) use ($rawData) {
             $vRoles = [];
             foreach (['artists' => 'Artist', 'publishers' => 'Publisher', 'designers' => 'Designer'] as $key => $roleName) {
                foreach ($v[$key] ?? [] as $role) {
                    $vRoles[] = new ExternalGameRoleData(
                        name: $role['name'],
                        role: $roleName,
                        sourceSlug: $this->sourceSlug,
                        externalId: $role['src_id']
                    );
                }
            }
            return new ExternalGameData(
                externalId: $v['src_id'],
                sourceSlug: $this->sourceSlug,
                name: $v['name'],
                thumbUrl: $v['thumb_url'] ?? null,
                imageUrl: $v['image_url'] ?? null,
                pubYear: (int) ($v['pub_year'] ?? null),
                roles: $vRoles,
                languages: $v['languages'] ?? [],
                rawData: $v
            );
        }, $versionsData);

        return new ExternalGameData(
            externalId: $data['src_id'],
            sourceSlug: $this->sourceSlug,
            name: $data['name'],
            description: $data['description'] ?? null,
            thumbUrl: $data['thumb_url'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            pubYear: (int) ($data['pub_year'] ?? null),
            minAge: (int) ($data['min_age'] ?? null),
            minPlayers: (int) ($data['min_players'] ?? null),
            maxPlayers: (int) ($data['max_players'] ?? null),
            boxTime: (int) ($data['box_time'] ?? null),
            minTime: (int) ($data['min_time'] ?? null),
            maxTime: (int) ($data['max_time'] ?? null),
            roles: $roles,
            languages: $data['languages'] ?? [],
            versions: $versions,
            rawData: $rawData
        );
    }

    private function query(string $path, array $params): array
    {
        $config = config("app.src_list.{$this->sourceSlug}");
        $apiKey = $config['api_key'] ?? null;
        if (! is_string($apiKey) || $apiKey === '') {
            throw new \RuntimeException(
                'BGG_API_KEY is not configured. Set it in .env (local) or LUDOTEST_BGG_API_KEY (Docker).'
            );
        }

        $url = $config['url'] . '/' . $path;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url, $params);

        if ($response->status() === 401) {
            throw new \RuntimeException(
                'BGG API returned 401 Unauthorized. Check BGG_API_KEY / LUDOTEST_BGG_API_KEY.'
            );
        }

        if ($response->failed()) {
            throw new \Exception("BGG API Error: " . $response->reason());
        }

        $xml = simplexml_load_string($response->body());
        return $this->xmlToArray($xml);
    }

    private function xmlToArray(SimpleXMLElement $xml): array
    {
        $parser = function (SimpleXMLElement $xml, array $collection = []) use (&$parser) {
            $nodes = $xml->children();
            $attributes = $xml->attributes();

            if (0 !== count($attributes)) {
                foreach ($attributes as $attrName => $attrValue) {
                    $collection['xml_attr'][$attrName] = strval($attrValue);
                }
            }

            if (0 === $nodes->count()) {
                $collection['value'] = strval($xml);
                return $collection;
            }

            foreach ($nodes as $nodeName => $nodeValue) {
                if (count($nodeValue->xpath('../' . $nodeName)) < 2) {
                    $collection[$nodeName] = $parser($nodeValue);
                    continue;
                }
                $collection[$nodeName][] = $parser($nodeValue);
            }

            return $collection;
        };

        return [$xml->getName() => $parser($xml)];
    }
}
