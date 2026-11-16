<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AutoCompleteOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-16 10:00:00');

        $this->buyer = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makeOrder(OrderStatus $status = OrderStatus::Dikirim): Order
    {
        return Order::create([
            'order_code' => 'TTC-TEST-'.Str::upper(Str::random(8)),
            'user_id' => $this->buyer->id,
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

    private function makeShipment(Order $order, Carbon $shippedAt, ShipmentStatus $status = ShipmentStatus::InTransit): Shipment
    {
        return Shipment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'courier' => 'JNE',
            'service' => 'REG',
            'tracking_number' => 'JNE-'.$order->id,
            'cost' => 9000,
            'weight_grams' => 1000,
            'status' => $status,
            'shipped_at' => $shippedAt,
        ]);
    }

    public function test_order_shipped_more_than_seven_days_is_completed(): void
    {
        $order = $this->makeOrder();
        $this->makeShipment($order, now()->subDays(8));

        $this->artisan('orders:auto-complete')
            ->expectsOutput('1 pesanan diselesaikan otomatis.')
            ->assertSuccessful();

        $this->assertSame(OrderStatus::Selesai, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->completed_at);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'to_status' => OrderStatus::Selesai->value,
            'note' => 'Auto-selesai setelah 7 hari',
        ]);
    }

    public function test_recent_shipment_is_not_completed(): void
    {
        $order = $this->makeOrder();
        $this->makeShipment($order, now()->subDays(6));

        $this->artisan('orders:auto-complete')
            ->expectsOutput('0 pesanan diselesaikan otomatis.')
            ->assertSuccessful();

        $this->assertSame(OrderStatus::Dikirim, $order->fresh()->status);
    }

    public function test_returned_shipment_is_not_completed(): void
    {
        $order = $this->makeOrder();
        $this->makeShipment($order, now()->subDays(8), ShipmentStatus::Returned);

        $this->artisan('orders:auto-complete')
            ->expectsOutput('0 pesanan diselesaikan otomatis.')
            ->assertSuccessful();

        $this->assertSame(OrderStatus::Dikirim, $order->fresh()->status);
    }

    public function test_order_without_shipment_uses_status_log_date(): void
    {
        $order = $this->makeOrder();

        $log = $order->statusLogs()->create([
            'from_status' => OrderStatus::Dikemas->value,
            'to_status' => OrderStatus::Dikirim->value,
            'note' => 'Dikirim manual',
        ]);

        $log->forceFill(['created_at' => now()->subDays(8)])->save();

        $this->artisan('orders:auto-complete')
            ->expectsOutput('1 pesanan diselesaikan otomatis.')
            ->assertSuccessful();

        $this->assertSame(OrderStatus::Selesai, $order->fresh()->status);
    }
}
