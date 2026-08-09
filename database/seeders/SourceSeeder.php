<?php

namespace Database\Seeders;

use App\Models\LanguageMapping;
use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * BGG language link ids → app language codes (from local catalog).
     * Mappings belong to the source and must exist before games are imported.
     *
     * @var list<array{language_code: string, external_id: string, external_name: string}>
     */
    private array $bggLanguageMappings = [
        ['language_code' => 'en', 'external_id' => '2184', 'external_name' => 'English'],
        ['language_code' => 'fr', 'external_id' => '2187', 'external_name' => 'French'],
        ['language_code' => 'de', 'external_id' => '2188', 'external_name' => 'German'],
        ['language_code' => 'it', 'external_id' => '2193', 'external_name' => 'Italian'],
    ];

    public function run(): void
    {
        $bgg = Source::updateOrCreate(
            ['slug' => 'bgg'],
            ['name' => 'BoardGameGeek', 'base_url' => 'https://boardgamegeek.com/xmlapi2']
        );

        $bggv = Source::updateOrCreate(
            ['slug' => 'bggv'],
            ['name' => 'BoardGameGeek Versions', 'base_url' => 'https://boardgamegeek.com/xmlapi2']
        );

        $this->seedLanguageMappings($bgg);
        $this->seedLanguageMappings($bggv);
    }

    private function seedLanguageMappings(Source $source): void
    {
        foreach ($this->bggLanguageMappings as $mapping) {
            LanguageMapping::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'language_code' => $mapping['language_code'],
                ],
                [
                    'external_id' => $mapping['external_id'],
                    'external_name' => $mapping['external_name'],
                ]
            );
        }
    }
}
