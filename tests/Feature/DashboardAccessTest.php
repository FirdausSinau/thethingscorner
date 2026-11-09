<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_buyer_can_access_buyer_dashboard(): void
    {
        $buyer = $this->userWithRole(UserRole::Buyer);

        $this->actingAs($buyer)->get(route('dashboard'))->assertOk();
    }

    public function test_buyer_cannot_access_admin_dashboard(): void
    {
        $buyer = $this->userWithRole(UserRole::Buyer);

        $this->actingAs($buyer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_master_can_access_admin_dashboard(): void
    {
        $adminMaster = $this->userWithRole(UserRole::AdminMaster);

        $this->actingAs($adminMaster)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_inactive_admin_cannot_access_admin_dashboard(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);
        $admin->forceFill(['is_active' => false])->save();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_is_redirected_from_buyer_dashboard_to_admin_dashboard(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }
}
