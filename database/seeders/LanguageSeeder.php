<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::updateOrCreate(['code' => 'fr'], ['name' => 'Français']);
        Language::updateOrCreate(['code' => 'en'], ['name' => 'English']);
        Language::updateOrCreate(['code' => 'de'], ['name' => 'Deutsch']);
        Language::updateOrCreate(['code' => 'it'], ['name' => 'Italiano']);
    }
}
