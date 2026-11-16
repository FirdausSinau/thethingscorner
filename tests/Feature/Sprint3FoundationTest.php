<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\PaymentGateway;
use App\Services\Shipping\FakeShippingProvider;
use App\Services\Shipping\ShippingProvider;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint3FoundationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $buyer): Order
    {
        return Order::create([
            'order_code' => 'TTC-'.Str::upper(Str::random(8)),
            'user_id' => $buyer->id,
            'channel' => OrderChannel::Web,
            'status' => OrderStatus::MenungguPembayaran,
            'payment_method' => PaymentMethod::Qris,
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

    public function test_payment_model_casts_and_relations(): void
    {
        $buyer = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => UserRole::AdminMaster])->save();

        $order = $this->makeOrder($buyer);

        $pending = Payment::create([
            'order_id' => $order->id,
            'provider' => 'fake',
            'method' => PaymentMethod::Qris,
            'external_id' => 'FAKE-'.$order->order_code,
            'amount' => 265000,
            'expires_at' => now()->addMinutes(30),
        ]);

        $paid = Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'method' => PaymentMethod::ManualTransfer,
            'external_id' => 'MANUAL-'.$order->order_code,
            'amount' => 265000,
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'verified_by' => $admin->id,
        ]);

        $this->assertSame(PaymentStatus::Pending, $pending->status);
        $this->assertSame(PaymentStatus::Paid, $paid->status);
        $this->assertSame(PaymentMethod::ManualTransfer, $paid->method);
        $this->assertSame(265000, $paid->amount);
        $this->assertNotNull($paid->paid_at);
        $this->assertTrue($paid->isPaid());
        $this->assertSame($order->id, $paid->order->id);
        $this->assertSame($admin->id, $paid->verifier->id);
        $this->assertSame(2, $order->payments()->count());
        $this->assertSame($paid->id, $order->latestPayment->id);
    }

    public function test_shipment_model_casts_and_relations(): void
    {
        $buyer = User::factory()->create();
        $order = $this->makeOrder($buyer);

        $pending = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'fake',
            'courier' => 'JNE',
            'service' => 'REG',
            'provider_reference' => 'FAKE-SHIP-'.$order->order_code,
            'tracking_number' => 'FAKE-JNE-'.$order->order_code,
            'cost' => 11500,
            'weight_grams' => 1200,
        ]);

        $delivered = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'courier' => 'SiCepat',
            'service' => 'REG',
            'tracking_number' => 'MANUAL-'.$order->order_code,
            'cost' => 11000,
            'weight_grams' => 1500,
            'status' => ShipmentStatus::Delivered,
            'shipped_at' => now()->subDay(),
            'delivered_at' => now(),
        ]);

        $this->assertSame(ShipmentStatus::Pending, $pending->status);
        $this->assertSame(ShipmentStatus::Delivered, $delivered->status);
        $this->assertSame(11500, $pending->cost);
        $this->assertSame(1200, $pending->weight_grams);
        $this->assertTrue($delivered->isFinal());
        $this->assertSame($order->id, $delivered->order->id);
        $this->assertSame($delivered->id, $order->latestShipment->id);
    }

    public function test_integration_log_stores_payload_and_has_no_updated_at(): void
    {
        $log = IntegrationLog::create([
            'provider' => 'fake',
            'type' => 'payment',
            'event' => 'callback.received',
            'external_id' => 'FAKE-TTC-001',
            'payload' => [
                'status' => 'PAID',
                'amount' => 265000,
            ],
        ]);

        $this->assertSame(IntegrationLog::STATUS_RECEIVED, $log->status);
        $this->assertSame('PAID', $log->payload['status']);
        $this->assertSame(265000, $log->payload['amount']);
        $this->assertNull($log->updated_at);
    }

    public function test_external_id_is_unique(): void
    {
        $buyer = User::factory()->create();
        $order = $this->makeOrder($buyer);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'fake',
            'method' => PaymentMethod::Qris,
            'external_id' => 'FAKE-DUPLICATE',
            'amount' => 265000,
        ]);

        $this->expectException(QueryException::class);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'fake',
            'method' => PaymentMethod::Qris,
            'external_id' => 'FAKE-DUPLICATE',
            'amount' => 265000,
        ]);
    }

    public function test_container_binds_fake_payment_and_shipping_adapters(): void
    {
        $this->assertInstanceOf(FakePaymentGateway::class, app(PaymentGateway::class));
        $this->assertInstanceOf(FakeShippingProvider::class, app(ShippingProvider::class));
    }
}
