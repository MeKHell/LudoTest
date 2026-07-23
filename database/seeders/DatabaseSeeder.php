<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(SourceSeeder::class);
        $this->call(LanguageMappingSeeder::class);
        $this->call(QuestionSeeder::class);

        if (app()->environment('local')) {
            $this->call(LocalDevelopmentSeeder::class);
        }
    }
}
