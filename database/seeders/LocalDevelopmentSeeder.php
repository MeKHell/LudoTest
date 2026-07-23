<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class LocalDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

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
    }
}
