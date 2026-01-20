<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\TranslationKey;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        for ($i = 0; $i < 10; ++$i) {
            User::firstOrCreate(
                    ['email' => 'test'.$i.'@example.com'],
                    [
                            'name' => 'Test User'.$i,
                            'password' => 'password',
                            'email_verified_at' => now(),
                    ]
            );
        }
        $this->call(LanguageSeeder::class);
        $this->call(QuestionSeeder::class);
    }
}
