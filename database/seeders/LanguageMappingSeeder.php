<?php

namespace Database\Seeders;

use App\Models\LanguageMapping;
use App\Models\Source;
use Illuminate\Database\Seeder;

class LanguageMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bgg = Source::where('slug', 'bgg')->first();
        if (!$bgg) return;

        $mappings = [
            ['language_code' => 'en', 'external_id' => '2184', 'external_name' => 'English'],
            ['language_code' => 'fr', 'external_id' => '2187', 'external_name' => 'French'],
            ['language_code' => 'de', 'external_id' => '2188', 'external_name' => 'German'],
            ['language_code' => 'it', 'external_id' => '2193', 'external_name' => 'Italian'],
        ];

        foreach ($mappings as $mapping) {
            LanguageMapping::updateOrCreate(
                ['source_id' => $bgg->id, 'language_code' => $mapping['language_code']],
                ['external_id' => $mapping['external_id'], 'external_name' => $mapping['external_name']]
            );
        }
        
        // Also map for bggv if it exists
        $bggv = Source::where('slug', 'bggv')->first();
        if ($bggv) {
            foreach ($mappings as $mapping) {
                LanguageMapping::updateOrCreate(
                    ['source_id' => $bggv->id, 'language_code' => $mapping['language_code']],
                    ['external_id' => $mapping['external_id'], 'external_name' => $mapping['external_name']]
                );
            }
        }
    }
}
