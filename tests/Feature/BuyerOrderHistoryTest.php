<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BuyerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    private function makeOrder(User $buyer): Order
    {
        return Order::create([
            'order_code' => 'TTC-TEST-'.Str::upper(Str::random(8)),
            'user_id' => $buyer->id,
            'channel' => OrderChannel::Web,
            'status' => OrderStatus::MenungguPembayaran,
            'payment_method' => PaymentMethod::ManualTransfer,
            'subtotal' => 250000,
            'shipping_cost' => 0,
            'total' => 250000,
            'recipient' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'city' => 'Bandung',
            'postal_code' => '40132',
        ]);
    }

    public function test_buyer_sees_only_own_orders(): void
    {
        $buyer = $this->userWithRole(UserRole::Buyer);
        $otherBuyer = $this->userWithRole(UserRole::Buyer);

        $ownOrder = $this->makeOrder($buyer);
        $otherOrder = $this->makeOrder($otherBuyer);

        $this->actingAs($buyer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee($ownOrder->order_code)
            ->assertDontSee($otherOrder->order_code);
    }

    public function test_buyer_order_detail_shows_order_summary(): void
    {
        $buyer = $this->userWithRole(UserRole::Buyer);
        $order = $this->makeOrder($buyer);
        $order->update([
            'subtotal' => 250000,
            'shipping_cost' => 15000,
            'total' => 265000,
        ]);

        $this->actingAs($buyer)
            ->get(route('orders.show', ['order' => $order->order_code]))
            ->assertOk()
            ->assertSee('Ringkasan pesanan')
            ->assertSee('Budi Santoso')
            ->assertSee('081234567890')
            ->assertSee('Jl. Contoh No. 1')
            ->assertSee('Bandung 40132')
            ->assertSee('Rp 250.000')
            ->assertSee('Rp 15.000')
            ->assertSee('Rp 265.000');
    }

    public function test_buyer_without_orders_sees_empty_state(): void
    {
        $buyer = $this->userWithRole(UserRole::Buyer);

        $this->actingAs($buyer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Belum ada pesanan');
    }

    public function test_admin_cannot_open_buyer_order_list(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)
            ->get(route('orders.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_order_list(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }
}
