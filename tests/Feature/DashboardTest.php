<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs($user = User::factory()->create());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_authenticated_users_can_fetch_dashboard_data()
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('api.dashboard.show'))
            ->assertOk()
            ->assertJson([
                'stats' => [
                    'comments_count' => 0,
                    'votes_count' => 0,
                    'library_count' => 0,
                ],
                'recentLibraryGames' => [],
            ]);
    }
}
