<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('rooms.index'))->assertRedirect(route('login'));
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@example.test',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'operator@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create(['email' => 'admin@example.test']);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'admin@example.test',
                'password' => 'salah-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->inactive()->create([
            'email' => 'nonaktif@example.test',
            'password' => 'password123',
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'nonaktif@example.test',
                'password' => 'password123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_viewer_can_read_but_cannot_manage_module(): void
    {
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->get(route('rooms.index'))->assertOk();
        $this->actingAs($viewer)->get(route('rooms.create'))->assertForbidden();
        $this->actingAs($viewer)->get(route('schedules.create'))->assertForbidden();
    }

    public function test_operator_can_open_management_forms(): void
    {
        $operator = User::factory()->create();

        $this->actingAs($operator)->get(route('buildings.create'))->assertOk();
        $this->actingAs($operator)->get(route('rooms.create'))->assertOk();
        $this->actingAs($operator)->get(route('facilities.create'))->assertOk();
        $this->actingAs($operator)->get(route('schedules.create'))->assertOk();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
