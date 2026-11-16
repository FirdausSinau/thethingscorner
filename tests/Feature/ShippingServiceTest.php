<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\Shipping\TrackingEvent;
use App\Services\ShippingService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShippingService $shipping;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->shipping = app(ShippingService::class);
        $this->buyer = User::factory()->create();
        $this->variant = ProductVariant::query()->where('stock', '>', 3)->firstOrFail();

        $this->address = Address::create([
            'user_id' => $this->buyer->id,
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

        config(['shipping.fake.couriers' => [
            [
                'courier' => 'JNE',
                'service' => 'REG',
                'description' => 'Reguler',
                'base_cost' => 9000,
                'per_kg' => 2500,
                'etd' => '2-3 hari',
            ],
        ]]);
    }

    private function makeOrder(OrderStatus $status = OrderStatus::Dikemas): Order
    {
        return Order::create([
            'order_code' => 'TTC-TEST-'.Str::upper(Str::random(8)),
            'user_id' => $this->buyer->id,
            'channel' => OrderChannel::Web,
            'status' => $status,
            'payment_method' => PaymentMethod::ManualTransfer,
            'subtotal' => 250000,
            'shipping_cost' => 15000,
            'total' => 265000,
            'recipient' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'city' => 'Bandung',
            'postal_code' => '40132',
        ]);
    }

    private function placePaidOrder(int $qty = 1): Order
    {
        app(CartService::class)->add($this->buyer, $this->variant, $qty);

        return app(CheckoutService::class)->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::ManualTransfer,
            15000,
        );
    }

    public function test_rates_are_calculated_from_weight(): void
    {
        $rates = $this->shipping->rates(1001, 'Bandung', '40132', 'JNE');

        $this->assertCount(1, $rates);
        $this->assertSame(11500, $rates[0]->cost);
        $this->assertFalse($rates[0]->isFallback);
    }

    public function test_rates_fall_back_to_flat_when_provider_fails(): void
    {
        config([
            'shipping.fake.couriers' => [],
            'shipping.flat' => [
                'courier' => 'Flat',
                'service' => 'REG',
                'description' => 'Tarif flat',
                'cost' => 15000,
                'etd' => '2-4 hari',
            ],
        ]);

        $rates = $this->shipping->rates(1000, 'Bandung', '40132', 'JNE');

        $this->assertCount(1, $rates);
        $this->assertSame('Flat', $rates[0]->courier);
        $this->assertSame(15000, $rates[0]->cost);
        $this->assertTrue($rates[0]->isFallback);
        $this->assertDatabaseHas('integration_logs', [
            'type' => 'shipping',
            'event' => 'shipping.rate_fallback',
            'status' => 'failed',
        ]);
    }

    public function test_weight_for_order_uses_product_weight_and_default(): void
    {
        $this->variant->load('color.product');
        $this->variant->color->product->update(['weight_grams' => 700]);

        $order = $this->placePaidOrder(2);

        $this->assertSame(1400, $this->shipping->weightFor($order));

        $this->variant->color->product->update(['weight_grams' => 0]);

        $this->assertSame(2000, $this->shipping->weightFor($order->fresh()));
    }

    public function test_rate_for_uses_order_destination_and_weight(): void
    {
        $this->variant->load('color.product');
        $this->variant->color->product->update(['weight_grams' => 700]);

        $order = $this->placePaidOrder(2);
        $rates = $this->shipping->rateFor($order, 'JNE');

        $this->assertCount(1, $rates);
        $this->assertSame(11500, $rates[0]->cost);
    }

    public function test_create_shipment_uses_fake_provider_and_is_idempotent(): void
    {
        $order = $this->makeOrder();

        $shipment = $this->shipping->createForOrder($order, 'JNE', 'REG', 1200, 11500);

        $this->assertSame('fake', $shipment->provider);
        $this->assertSame('FAKE-SHIP-'.$order->order_code, $shipment->provider_reference);
        $this->assertSame('FAKE-JNE-'.$order->order_code, $shipment->tracking_number);
        $this->assertSame(ShipmentStatus::Pending, $shipment->status);
        $this->assertSame(11500, $shipment->cost);
        $this->assertSame(1200, $shipment->weight_grams);

        $again = $this->shipping->createForOrder($order->fresh(), 'JNE', 'REG', 1200, 11500);

        $this->assertSame($shipment->id, $again->id);
        $this->assertDatabaseCount('shipments', 1);
    }

    public function test_admin_can_create_manual_shipment_with_tracking_number(): void
    {
        $order = $this->makeOrder();

        $shipment = $this->shipping->createForOrder(
            $order,
            'JNE',
            'REG',
            1200,
            11500,
            'JNE123456',
            'MANUAL-REF',
        );

        $this->assertSame('manual', $shipment->provider);
        $this->assertSame('JNE123456', $shipment->tracking_number);
        $this->assertSame('MANUAL-REF', $shipment->provider_reference);
        $this->assertSame(ShipmentStatus::Pending, $shipment->status);
    }

    public function test_shipment_requires_order_dikemas(): void
    {
        $order = $this->makeOrder(OrderStatus::Dibayar);

        $this->expectException(ValidationException::class);

        $this->shipping->createForOrder($order, 'JNE', 'REG', 1200, 11500);
    }

    public function test_tracking_moves_order_to_dikirim_then_selesai(): void
    {
        $order = $this->makeOrder();
        $shipment = $this->shipping->createForOrder($order, 'JNE', 'REG', 1200, 11500);
        $tracking = $shipment->tracking_number;

        $this->shipping->updateTracking($shipment, new TrackingEvent(
            trackingNumber: $tracking,
            status: ShipmentStatus::Pickup,
            description: 'Paket dijemput kurir',
            occurredAt: now(),
        ));

        $this->assertSame(ShipmentStatus::Pickup, $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->shipped_at);
        $this->assertSame(OrderStatus::Dikirim, $order->fresh()->status);

        $this->shipping->updateTracking($shipment->fresh(), new TrackingEvent(
            trackingNumber: $tracking,
            status: ShipmentStatus::InTransit,
            description: 'Paket dalam perjalanan',
            occurredAt: now(),
        ));

        $this->assertSame(OrderStatus::Dikirim, $order->fresh()->status);

        $this->shipping->updateTracking($shipment->fresh(), new TrackingEvent(
            trackingNumber: $tracking,
            status: ShipmentStatus::Delivered,
            description: 'Paket diterima',
            occurredAt: now(),
        ));

        $this->assertSame(ShipmentStatus::Delivered, $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->delivered_at);
        $this->assertSame(OrderStatus::Selesai, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->completed_at);
        $this->assertSame(1, $order->statusLogs()->where('to_status', OrderStatus::Dikirim->value)->count());
        $this->assertSame(1, $order->statusLogs()->where('to_status', OrderStatus::Selesai->value)->count());
    }

    public function test_duplicate_tracking_event_is_ignored(): void
    {
        $order = $this->makeOrder();
        $shipment = $this->shipping->createForOrder($order, 'JNE', 'REG', 1200, 11500);
        $event = new TrackingEvent(
            trackingNumber: $shipment->tracking_number,
            status: ShipmentStatus::Pickup,
            description: 'Paket dijemput kurir',
            occurredAt: now(),
        );

        $this->shipping->updateTracking($shipment, $event);
        $this->shipping->updateTracking($shipment->fresh(), $event);

        $this->assertSame(1, $order->statusLogs()->where('to_status', OrderStatus::Dikirim->value)->count());
        $this->assertDatabaseHas('integration_logs', [
            'event' => 'shipping.tracking_ignored',
            'status' => 'ignored',
        ]);
    }

    public function test_invalid_tracking_transition_is_rejected(): void
    {
        $order = $this->makeOrder();
        $shipment = $this->shipping->createForOrder($order, 'JNE', 'REG', 1200, 11500);

        try {
            $this->shipping->updateTracking($shipment, new TrackingEvent(
                trackingNumber: $shipment->tracking_number,
                status: ShipmentStatus::Delivered,
                description: 'Paket diterima',
                occurredAt: now(),
            ));

            $this->fail('Transisi PENDING ke DELIVERED seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('shipping', $e->errors());
        }

        $this->assertSame(ShipmentStatus::Pending, $shipment->fresh()->status);
        $this->assertSame(OrderStatus::Dikemas, $order->fresh()->status);
    }

    public function test_tracking_number_mismatch_is_rejected(): void
    {
        $order = $this->makeOrder();
        $shipment = $this->shipping->createForOrder($order, 'JNE', 'REG', 1200, 11500);

        $this->expectException(ValidationException::class);

        $this->shipping->updateTracking($shipment, new TrackingEvent(
            trackingNumber: 'RESI-LAIN',
            status: ShipmentStatus::Pickup,
            description: 'Paket dijemput kurir',
            occurredAt: now(),
        ));
    }

    public function test_returned_shipment_does_not_cancel_order(): void
    {
        $order = $this->makeOrder();
        $shipment = $this->shipping->createForOrder($order, 'JNE', 'REG', 1200, 11500);
        $tracking = $shipment->tracking_number;

        $this->shipping->updateTracking($shipment, new TrackingEvent(
            trackingNumber: $tracking,
            status: ShipmentStatus::Pickup,
            description: 'Paket dijemput kurir',
            occurredAt: now(),
        ));

        $this->shipping->updateTracking($shipment->fresh(), new TrackingEvent(
            trackingNumber: $tracking,
            status: ShipmentStatus::Returned,
            description: 'Paket dikembalikan ke toko',
            occurredAt: now(),
        ));

        $this->assertSame(ShipmentStatus::Returned, $shipment->fresh()->status);
        $this->assertSame(OrderStatus::Dikirim, $order->fresh()->status);
    }
}
