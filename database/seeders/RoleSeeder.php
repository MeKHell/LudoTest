<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);
        Role::updateOrCreate(['slug' => 'user'], ['name' => 'User']);
    }
}
