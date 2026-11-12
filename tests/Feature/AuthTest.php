<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_buyer_can_login_and_is_redirected_to_catalog(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
        ]);
        $user->forceFill(['role' => UserRole::Buyer, 'is_active' => true])->save();

        $response = $this->post(route('login.store'), [
            'email' => 'buyer@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $user->forceFill(['role' => UserRole::Admin, 'is_active' => true])->save();

        $response = $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
        ]);
        $user->forceFill(['role' => UserRole::Buyer, 'is_active' => false])->save();

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_new_user_can_register_as_buyer(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Pembeli Baru',
            'email' => 'pembeli@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'pembeli@example.com',
            'role' => 'buyer',
        ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
