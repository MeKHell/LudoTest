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
        Language::create(['code' => 'FR',
                    'bgg_index' => '2187',
                    'name' => 'Français']);
        Language::create(['code' => 'EN',
                        'bgg_index' => '2184',
                        'name' => 'English']);
        Language::create(['code' => 'DE',
                        'bgg_index' => '2188',
                        'name' => 'Deutsch']);
        Language::create(['code' => 'IT',
                        'bgg_index' => '2193',
                        'name' => 'Italiano']);
    }
}
