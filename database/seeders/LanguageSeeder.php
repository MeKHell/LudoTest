<?php

namespace Database\Seeders;

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
        DB::table('languages')->insert(['code' => 'FR',
                'bgg_index' => '2187',
                'name' => 'Français']);
        DB::table('languages')->insert(['code' => 'EN',
                'bgg_index' => '2184',
                'name' => 'English',]);
        DB::table('languages')->insert(['code' => 'DE',
                'bgg_index' => '2188',
                'name' => 'Deutsch']);
        DB::table('languages')->insert(['code' => 'IT',
                'bgg_index' => '2193',
                'name' => 'Italiano']);
    }
}
