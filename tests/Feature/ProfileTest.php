<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Informasi akun')
            ->assertSee($user->email);
    }

    public function test_profile_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'baru@example.test',
                'phone' => '081200000000',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.test',
            'phone' => '081200000000',
        ]);
    }

    public function test_password_can_be_changed(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'password123',
                'password' => 'rahasia12345',
                'password_confirmation' => 'rahasia12345',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('rahasia12345', $user->fresh()->password));
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password'), [
                'current_password' => 'salah-password',
                'password' => 'rahasia12345',
                'password_confirmation' => 'rahasia12345',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');
    }

    public function test_api_token_can_be_regenerated_and_is_shown_once(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('profile.token'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $plainToken = session('api_token');

        $this->assertIsString($plainToken);
        $this->assertTrue(Str::startsWith($plainToken, 'ta_'));
        $this->assertSame(hash('sha256', $plainToken), $user->fresh()->getRawOriginal('api_token'));

        $response->assertSessionHas('api_token', $plainToken);
    }
}
