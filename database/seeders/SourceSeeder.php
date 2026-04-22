<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Source::updateOrCreate(
            ['slug' => 'bgg'],
            ['name' => 'BoardGameGeek', 'base_url' => 'https://boardgamegeek.com/xmlapi2']
        );

        Source::updateOrCreate(
            ['slug' => 'bggv'],
            ['name' => 'BoardGameGeek Versions', 'base_url' => 'https://boardgamegeek.com/xmlapi2']
        );
    }
}
