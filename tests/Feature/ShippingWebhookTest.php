<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShippingWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        config(['shipping.fake.enabled' => true]);

        $this->buyer = User::factory()->create();
    }

    private function makeShipment(): array
    {
        $order = Order::create([
            'order_code' => 'TTC-TEST-'.Str::upper(Str::random(8)),
            'user_id' => $this->buyer->id,
            'channel' => OrderChannel::Web,
            'status' => OrderStatus::Dikemas,
            'payment_method' => PaymentMethod::ManualTransfer,
            'subtotal' => 250000,
            'shipping_cost' => 9000,
            'total' => 259000,
            'recipient' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'city' => 'Bandung',
            'postal_code' => '40132',
        ]);

        $shipment = app(ShippingService::class)->createForOrder(
            $order,
            'JNE',
            'REG',
            1000,
            9000,
        );

        return [$order, $shipment];
    }

    public function test_fake_shipping_webhook_updates_tracking_and_order(): void
    {
        [$order, $shipment] = $this->makeShipment();

        $this->postJson(route('webhooks.shipping', ['provider' => 'fake']), [
            'tracking_number' => $shipment->tracking_number,
            'status' => 'IN_TRANSIT',
            'description' => 'Paket dalam perjalanan',
        ])
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'shipment_status' => 'IN_TRANSIT',
                'order_status' => 'DIKIRIM',
            ]);

        $this->assertSame(ShipmentStatus::InTransit, $shipment->fresh()->status);
        $this->assertSame(OrderStatus::Dikirim, $order->fresh()->status);
    }

    public function test_duplicate_shipping_webhook_is_ignored(): void
    {
        [$order, $shipment] = $this->makeShipment();
        $payload = [
            'tracking_number' => $shipment->tracking_number,
            'status' => 'PICKUP',
            'description' => 'Paket dijemput',
        ];

        $this->postJson(route('webhooks.shipping', ['provider' => 'fake']), $payload)->assertOk();
        $this->postJson(route('webhooks.shipping', ['provider' => 'fake']), $payload)->assertOk();

        $this->assertSame(1, $order->statusLogs()->where('to_status', OrderStatus::Dikirim->value)->count());
        $this->assertDatabaseHas('integration_logs', [
            'event' => 'shipping.tracking_ignored',
            'status' => 'ignored',
        ]);
    }

    public function test_shipping_webhook_with_unknown_tracking_is_rejected(): void
    {
        $this->postJson(route('webhooks.shipping', ['provider' => 'fake']), [
            'tracking_number' => 'TIDAK-ADA',
            'status' => 'IN_TRANSIT',
        ])->assertNotFound();
    }

    public function test_shipping_webhook_with_invalid_status_is_logged(): void
    {
        $this->postJson(route('webhooks.shipping', ['provider' => 'fake']), [
            'tracking_number' => 'FAKE-JNE-001',
            'status' => 'LOST',
        ])
            ->assertStatus(422)
            ->assertJson(['status' => 'error']);

        $this->assertDatabaseHas('integration_logs', [
            'event' => 'callback.invalid',
            'type' => 'shipping',
            'status' => 'failed',
        ]);
    }

    public function test_unknown_shipping_provider_is_rejected(): void
    {
        $this->postJson(route('webhooks.shipping', ['provider' => 'biteship']), [
            'tracking_number' => 'FAKE-001',
            'status' => 'IN_TRANSIT',
        ])->assertNotFound();
    }

    public function test_fake_shipping_webhook_can_be_disabled(): void
    {
        config(['shipping.fake.enabled' => false]);

        $this->postJson(route('webhooks.shipping', ['provider' => 'fake']), [
            'tracking_number' => 'FAKE-001',
            'status' => 'IN_TRANSIT',
        ])->assertNotFound();
    }
}
