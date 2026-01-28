<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::create(['code' => 'fr',
                    'bgg_index' => '2187',
                    'name' => 'Français']);
        Language::create(['code' => 'en',
                        'bgg_index' => '2184',
                        'name' => 'English']);
        Language::create(['code' => 'de',
                        'bgg_index' => '2188',
                        'name' => 'Deutsch']);
        Language::create(['code' => 'it',
                        'bgg_index' => '2193',
                        'name' => 'Italiano']);
    }
}
