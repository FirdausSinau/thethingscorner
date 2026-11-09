<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(UserRole $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    public function test_admin_master_can_view_admin_accounts_page(): void
    {
        $master = $this->userWithRole(UserRole::AdminMaster);

        $this->actingAs($master)
            ->get(route('admin.admins.index'))
            ->assertOk()
            ->assertSee('Akun admin');
    }

    public function test_admin_biasa_cannot_view_admin_accounts_page(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_buyer_cannot_view_admin_accounts_page(): void
    {
        $buyer = $this->userWithRole(UserRole::Buyer);

        $this->actingAs($buyer)
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_admin_master_can_create_admin_biasa(): void
    {
        $master = $this->userWithRole(UserRole::AdminMaster);

        $response = $this->actingAs($master)->post(route('admin.admins.store'), [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.admins.index'));

        $admin = User::where('email', 'adminbaru@example.com')->firstOrFail();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('rahasia123', $admin->password));
    }

    public function test_role_cannot_be_escalated_through_the_request(): void
    {
        $master = $this->userWithRole(UserRole::AdminMaster);

        $this->actingAs($master)->post(route('admin.admins.store'), [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin_master',
            'is_active' => false,
        ]);

        $admin = User::where('email', 'adminbaru@example.com')->firstOrFail();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_email_must_be_unique(): void
    {
        $master = $this->userWithRole(UserRole::AdminMaster);
        User::factory()->create(['email' => 'sudahada@example.com']);

        $response = $this->actingAs($master)->post(route('admin.admins.store'), [
            'name' => 'Admin Baru',
            'email' => 'sudahada@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_master_can_deactivate_and_activate_an_admin(): void
    {
        $master = $this->userWithRole(UserRole::AdminMaster);
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($master)
            ->patch(route('admin.admins.deactivate', $admin))
            ->assertRedirect(route('admin.admins.index'));

        $this->assertFalse($admin->fresh()->is_active);

        $this->actingAs($master)
            ->patch(route('admin.admins.activate', $admin))
            ->assertRedirect(route('admin.admins.index'));

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_master_cannot_deactivate_another_admin_master(): void
    {
        $master = $this->userWithRole(UserRole::AdminMaster);
        $otherMaster = $this->userWithRole(UserRole::AdminMaster, [
            'email' => 'masterlain@example.com',
        ]);

        $this->actingAs($master)
            ->patch(route('admin.admins.deactivate', $otherMaster))
            ->assertForbidden();

        $this->assertTrue($otherMaster->fresh()->is_active);
    }

    public function test_admin_biasa_cannot_deactivate_another_admin(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);
        $target = $this->userWithRole(UserRole::Admin, [
            'email' => 'adminlain@example.com',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.admins.deactivate', $target))
            ->assertForbidden();

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_master_cannot_toggle_a_buyer_account(): void
    {
        $master = $this->userWithRole(UserRole::AdminMaster);
        $buyer = $this->userWithRole(UserRole::Buyer);

        $this->actingAs($master)
            ->patch(route('admin.admins.deactivate', $buyer))
            ->assertForbidden();

        $this->assertTrue($buyer->fresh()->is_active);
    }
}
