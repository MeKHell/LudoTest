<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
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

    public function test_admin_middleware_allows_admin()
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $response = $this->actingAs($user)->get('/admin-test');

        $response->assertStatus(200);
        $response->assertSee('Admin Only');
    }

    public function test_admin_middleware_denies_regular_user()
    {
        $userRole = Role::where('slug', 'user')->first();
        $user = User::factory()->create();
        $user->roles()->attach($userRole);

        $response = $this->actingAs($user)->get('/admin-test');

        $response->assertStatus(403);
    }

    public function test_admin_middleware_denies_guest()
    {
        $response = $this->get('/admin-test');

        // Redirects to login because of 'auth' middleware on the group
        $response->assertRedirect(route('login'));
    }
}
