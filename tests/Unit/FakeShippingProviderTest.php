<?php

namespace Tests\Unit;

use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Services\Shipping\FakeShippingProvider;
use App\Services\Shipping\ShipmentRequest;
use App\Services\Shipping\ShippingRateRequest;
use InvalidArgumentException;
use Tests\TestCase;

class FakeShippingProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['shipping.fake.couriers' => [
            [
                'courier' => 'JNE',
                'service' => 'REG',
                'description' => 'Reguler',
                'base_cost' => 9000,
                'per_kg' => 2500,
                'etd' => '2-3 hari',
            ],
            [
                'courier' => 'SiCepat',
                'service' => 'REG',
                'description' => 'Reguler',
                'base_cost' => 8500,
                'per_kg' => 2500,
                'etd' => '2-3 hari',
            ],
        ]]);
    }

    public function test_rates_are_deterministic_and_round_weight_up(): void
    {
        $provider = new FakeShippingProvider;

        $oneGram = $provider->rates(new ShippingRateRequest('Bandung', '40132', 1, 'JNE'));
        $oneKg = $provider->rates(new ShippingRateRequest('Bandung', '40132', 1000, 'JNE'));
        $overOneKg = $provider->rates(new ShippingRateRequest('Bandung', '40132', 1001, 'JNE'));

        $this->assertCount(1, $oneGram);
        $this->assertSame(9000, $oneGram[0]->cost);
        $this->assertSame(9000, $oneKg[0]->cost);
        $this->assertSame(11500, $overOneKg[0]->cost);
        $this->assertSame('JNE', $overOneKg[0]->courier);
        $this->assertSame('REG', $overOneKg[0]->service);
        $this->assertSame('2-3 hari', $overOneKg[0]->estimatedDays);
    }

    public function test_rates_without_courier_filter_returns_all_services(): void
    {
        $rates = (new FakeShippingProvider)->rates(
            new ShippingRateRequest('Bandung', '40132', 2000)
        );

        $this->assertCount(2, $rates);
        $this->assertSame([9000 + 2500, 8500 + 2500], array_column($rates, 'cost'));
    }

    public function test_rates_throw_when_courier_has_no_service(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new FakeShippingProvider)->rates(
            new ShippingRateRequest('Bandung', '40132', 1000, 'POS')
        );
    }

    public function test_create_shipment_returns_fake_tracking_number(): void
    {
        $order = new Order([
            'order_code' => 'TTC-001',
        ]);

        $result = (new FakeShippingProvider)->createShipment(
            new ShipmentRequest($order, 'JNE', 'REG', 1200, 11500)
        );

        $this->assertSame('fake', $result->provider);
        $this->assertSame('FAKE-SHIP-TTC-001', $result->reference);
        $this->assertSame('FAKE-JNE-TTC-001', $result->trackingNumber);
        $this->assertSame(ShipmentStatus::Pending, $result->status);
    }

    public function test_parse_tracking_callback_reads_event(): void
    {
        $event = (new FakeShippingProvider)->parseTrackingCallback([
            'tracking_number' => 'FAKE-JNE-TTC-001',
            'status' => 'in_transit',
            'description' => 'Paket tiba di hub Bandung',
            'occurred_at' => '2026-11-16 09:00:00',
        ]);

        $this->assertSame('FAKE-JNE-TTC-001', $event->trackingNumber);
        $this->assertSame(ShipmentStatus::InTransit, $event->status);
        $this->assertSame('Paket tiba di hub Bandung', $event->description);
        $this->assertSame('2026-11-16 09:00:00', $event->occurredAt->format('Y-m-d H:i:s'));
    }

    public function test_parse_tracking_callback_rejects_unknown_status(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new FakeShippingProvider)->parseTrackingCallback([
            'tracking_number' => 'FAKE-JNE-TTC-001',
            'status' => 'lost',
        ]);
    }
}
