<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->variant = ProductVariant::query()->where('stock', '>', 2)->firstOrFail();
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    private function admin(): User
    {
        return $this->userWithRole(UserRole::Admin);
    }

    private function buyer(): User
    {
        return $this->userWithRole(UserRole::Buyer);
    }

    private function makeOrder(OrderStatus $status): Order
    {
        return Order::create([
            'order_code' => 'TTC-TEST-'.Str::upper(Str::random(8)),
            'user_id' => $this->buyer()->id,
            'channel' => OrderChannel::Web,
            'status' => $status,
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

    private function placeOrder(User $buyer, int $qty = 1): Order
    {
        app(CartService::class)->add($buyer, $this->variant, $qty);

        $address = Address::create([
            'user_id' => $buyer->id,
            'label' => 'Rumah',
            'recipient' => 'Budi Santoso',
            'phone' => '081234567890',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Contoh No. 1',
            'is_default' => true,
        ]);

        return app(CheckoutService::class)->placeOrder($buyer, $address, PaymentMethod::ManualTransfer);
    }

    public function test_admin_can_view_order_index(): void
    {
        $order = $this->makeOrder(OrderStatus::MenungguPembayaran);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($order->order_code);
    }

    public function test_admin_order_index_renders_for_every_status(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->makeOrder($status);
        }

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Dipending');
    }

    public function test_buyer_cannot_view_admin_orders(): void
    {
        $this->actingAs($this->buyer())
            ->get(route('admin.orders.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_admin_orders(): void
    {
        $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_order_detail(): void
    {
        $order = $this->makeOrder(OrderStatus::Dibayar);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Riwayat status');
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        $order = $this->makeOrder(OrderStatus::MenungguPembayaran);

        $this->actingAs($this->buyer())
            ->get(route('orders.show', ['order' => $order->order_code]))
            ->assertNotFound();
    }

    public function test_admin_can_view_buyer_order_page(): void
    {
        $order = $this->makeOrder(OrderStatus::Dibayar);

        $this->actingAs($this->admin())
            ->get(route('orders.show', ['order' => $order->order_code]))
            ->assertOk();
    }

    public function test_confirming_payment_consumes_reservation_and_decrements_stock(): void
    {
        $buyer = $this->buyer();
        $admin = $this->admin();
        $order = $this->placeOrder($buyer, 2);

        $stockBefore = $this->variant->fresh()->stock;
        $reservedBefore = $this->variant->fresh()->stock_reserved;

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Dibayar->value])
            ->assertRedirect(route('admin.orders.index'));

        $order->refresh();

        $this->assertSame(OrderStatus::Dibayar, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame($stockBefore - 2, $this->variant->fresh()->stock);
        $this->assertSame($reservedBefore - 2, $this->variant->fresh()->stock_reserved);
        $this->assertDatabaseHas('stock_reservations', [
            'order_id' => $order->id,
            'status' => 'consumed',
        ]);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'from_status' => OrderStatus::MenungguPembayaran->value,
            'to_status' => OrderStatus::Dibayar->value,
            'actor_id' => $admin->id,
        ]);
    }

    public function test_cancelling_unpaid_order_releases_reservation(): void
    {
        $order = $this->placeOrder($this->buyer(), 2);

        $stockBefore = $this->variant->fresh()->stock;
        $reservedBefore = $this->variant->fresh()->stock_reserved;

        $this->actingAs($this->admin())
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Dibatalkan->value])
            ->assertRedirect(route('admin.orders.index'));

        $order->refresh();

        $this->assertSame(OrderStatus::Dibatalkan, $order->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
        $this->assertSame($reservedBefore - 2, $this->variant->fresh()->stock_reserved);
        $this->assertDatabaseHas('stock_reservations', [
            'order_id' => $order->id,
            'status' => 'released',
        ]);
    }

    public function test_paid_order_can_move_to_confirmation_without_stock_change(): void
    {
        $admin = $this->admin();
        $order = $this->placeOrder($this->buyer());

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Dibayar->value]);

        $stockAfterPayment = $this->variant->fresh()->stock;

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::MenungguKonfirmasi->value])
            ->assertRedirect(route('admin.orders.index'));

        $order->refresh();

        $this->assertSame(OrderStatus::MenungguKonfirmasi, $order->status);
        $this->assertSame($stockAfterPayment, $this->variant->fresh()->stock);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'from_status' => OrderStatus::Dibayar->value,
            'to_status' => OrderStatus::MenungguKonfirmasi->value,
        ]);
    }

    public function test_cancelling_confirmed_order_restores_stock(): void
    {
        $admin = $this->admin();
        $order = $this->placeOrder($this->buyer());
        $stockBefore = $this->variant->fresh()->stock;

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Dibayar->value]);

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::MenungguKonfirmasi->value]);

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Dibatalkan->value]);

        $this->assertSame(OrderStatus::Dibatalkan, $order->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $order = $this->makeOrder(OrderStatus::MenungguPembayaran);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Dikemas->value]);

        $response->assertRedirect(route('admin.orders.index'));
        $response->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
    }

    public function test_manual_expiry_is_rejected(): void
    {
        $order = $this->makeOrder(OrderStatus::MenungguPembayaran);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.status', $order), ['status' => OrderStatus::Kedaluwarsa->value]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
    }

    public function test_search_by_order_code_works(): void
    {
        $first = $this->makeOrder(OrderStatus::MenungguPembayaran);
        $second = $this->makeOrder(OrderStatus::MenungguPembayaran);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['q' => $first->order_code]))
            ->assertOk()
            ->assertSee($first->order_code)
            ->assertDontSee($second->order_code);
    }

    public function test_bulk_status_updates_selected_orders(): void
    {
        $first = $this->placeOrder($this->buyer());
        $second = $this->placeOrder($this->buyer());

        $this->actingAs($this->admin())
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.bulk-status'), [
                'order_ids' => [$first->id, $second->id],
                'status' => OrderStatus::Dibatalkan->value,
            ])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame(OrderStatus::Dibatalkan, $first->fresh()->status);
        $this->assertSame(OrderStatus::Dibatalkan, $second->fresh()->status);
    }
}
