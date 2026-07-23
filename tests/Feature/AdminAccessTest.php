<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\LibraryEntry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_gate_allows_admin_user()
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $this->assertTrue(Gate::forUser($user)->allows('admin'));
    }

    public function test_admin_gate_denies_regular_user()
    {
        $userRole = Role::where('slug', 'user')->first();
        $user = User::factory()->create();
        $user->roles()->attach($userRole);

        $this->assertTrue(Gate::forUser($user)->denies('admin'));
    }
}
