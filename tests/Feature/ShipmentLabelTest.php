<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShipmentLabelTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    private function makeOrder(): Order
    {
        return Order::create([
            'order_code' => 'TTC-TEST-'.Str::upper(Str::random(8)),
            'user_id' => $this->buyer->id,
            'channel' => OrderChannel::Web,
            'status' => OrderStatus::Dikemas,
            'payment_method' => PaymentMethod::ManualTransfer,
            'subtotal' => 250000,
            'shipping_cost' => 9000,
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
            'total' => 259000,
            'recipient' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'city' => 'Bandung',
            'postal_code' => '40132',
        ]);
    }

    public function test_admin_can_download_shipment_label_pdf(): void
    {
        $order = $this->makeOrder();
        $shipment = app(ShippingService::class)->createForOrder(
            $order,
            'JNE',
            'REG',
            1200,
            9000,
            'JNE123456',
        );

        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)
            ->get(route('admin.shipments.label', $shipment))
            ->assertOk()
            ->assertDownload('resi-JNE123456.pdf');
    }

    public function test_admin_master_can_download_shipment_label_pdf(): void
    {
        $order = $this->makeOrder();
        $shipment = app(ShippingService::class)->createForOrder(
            $order,
            'JNE',
            'REG',
            1200,
            9000,
            'JNE123456',
        );

        $admin = $this->userWithRole(UserRole::AdminMaster);

        $this->actingAs($admin)
            ->get(route('admin.shipments.label', $shipment))
            ->assertOk()
            ->assertDownload('resi-JNE123456.pdf');
    }

    public function test_buyer_cannot_download_shipment_label(): void
    {
        $order = $this->makeOrder();
        $shipment = app(ShippingService::class)->createForOrder(
            $order,
            'JNE',
            'REG',
            1200,
            9000,
            'JNE123456',
        );

        $this->actingAs($this->buyer)
            ->get(route('admin.shipments.label', $shipment))
            ->assertForbidden();
    }

    public function test_label_requires_tracking_number(): void
    {
        $order = $this->makeOrder();
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'courier' => 'JNE',
            'service' => 'REG',
            'cost' => 9000,
            'weight_grams' => 1200,
        ]);

        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->get(route('admin.shipments.label', $shipment))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHasErrors('shipping');
    }

    public function test_admin_order_page_shows_label_button(): void
    {
        $order = $this->makeOrder();
        app(ShippingService::class)->createForOrder(
            $order,
            'JNE',
            'REG',
            1200,
            9000,
            'JNE123456',
        );

        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Cetak resi')
            ->assertSee('JNE123456');
    }
}
