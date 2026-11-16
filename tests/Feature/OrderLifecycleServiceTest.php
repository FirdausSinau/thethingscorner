<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderStatusService;
use App\Services\PaymentService;
use App\Services\Shipping\TrackingEvent;
use App\Services\ShippingService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderLifecycleServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-16 10:00:00');

        $this->seed(CatalogSeeder::class);

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
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    private function placeOrder(int $qty = 1): Order
    {
        app(CartService::class)->add($this->buyer, $this->variant, $qty);

        return app(CheckoutService::class)->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::ManualTransfer,
        );
    }

    private function pay(Order $order): void
    {
        $payments = app(PaymentService::class);
        $payment = $payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $payments->markPaid($payment);
    }

    public function test_buyer_cancel_policy_only_before_packing(): void
    {
        $unpaid = $this->placeOrder();
        $this->assertTrue(Gate::forUser($this->buyer)->allows('cancel', $unpaid));

        $dipending = $this->placeOrder();
        $this->pay($dipending);
        app(OrderStatusService::class)->change(
            $dipending->fresh(),
            OrderStatus::MenungguKonfirmasi,
            $this->userWithRole(UserRole::Admin),
        );
        $this->assertTrue(Gate::forUser($this->buyer)->allows('cancel', $dipending->fresh()));

        app(OrderStatusService::class)->change(
            $dipending->fresh(),
            OrderStatus::Dikemas,
            $this->userWithRole(UserRole::Admin),
        );
        $this->assertFalse(Gate::forUser($this->buyer)->allows('cancel', $dipending->fresh()));
    }

    public function test_buyer_confirm_policy_only_when_shipped(): void
    {
        $order = $this->placeOrder();

        $this->assertFalse(Gate::forUser($this->buyer)->allows('confirm', $order));

        $order->forceFill(['status' => OrderStatus::Dikirim])->save();

        $this->assertTrue(Gate::forUser($this->buyer)->allows('confirm', $order));
    }

    public function test_cancelling_dipending_order_restores_stock(): void
    {
        $stockBefore = $this->variant->fresh()->stock;
        $order = $this->placeOrder();
        $this->pay($order);

        $statuses = app(OrderStatusService::class);
        $admin = $this->userWithRole(UserRole::Admin);

        $statuses->change($order->fresh(), OrderStatus::MenungguKonfirmasi, $admin);
        $statuses->change($order->fresh(), OrderStatus::Dibatalkan, $this->buyer, 'Berubah pikiran');

        $this->assertSame(OrderStatus::Dibatalkan, $order->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
        $this->assertSame(PaymentStatus::Paid, $order->latestPayment()->first()->status);
    }

    public function test_regular_admin_cannot_force_cancel_paid_order(): void
    {
        $order = $this->placeOrder();
        $this->pay($order);

        $this->expectException(ValidationException::class);

        app(OrderStatusService::class)->change(
            $order->fresh(),
            OrderStatus::Dibatalkan,
            $this->userWithRole(UserRole::Admin),
            'Coba batalkan',
        );
    }

    public function test_admin_master_can_cancel_paid_order_and_restore_stock(): void
    {
        $stockBefore = $this->variant->fresh()->stock;
        $order = $this->placeOrder();
        $this->pay($order);

        app(OrderStatusService::class)->change(
            $order->fresh(),
            OrderStatus::Dibatalkan,
            $this->userWithRole(UserRole::AdminMaster),
            'Dibatalkan Admin Master',
        );

        $this->assertSame(OrderStatus::Dibatalkan, $order->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }

    public function test_refund_marks_payment_refunded_and_restores_stock(): void
    {
        $stockBefore = $this->variant->fresh()->stock;
        $order = $this->placeOrder();
        $this->pay($order);
        $payment = $order->latestPayment()->first();
        $master = $this->userWithRole(UserRole::AdminMaster);

        app(PaymentService::class)->refundManual($payment, $master, 'Pembeli membatalkan');

        $this->assertSame(PaymentStatus::Refunded, $payment->fresh()->status);
        $this->assertSame($master->id, $payment->fresh()->verified_by);
        $this->assertSame(OrderStatus::Dibatalkan, $order->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }

    public function test_regular_admin_cannot_refund(): void
    {
        $order = $this->placeOrder();
        $this->pay($order);

        $this->expectException(AuthorizationException::class);

        app(PaymentService::class)->refundManual(
            $order->latestPayment()->first(),
            $this->userWithRole(UserRole::Admin),
            'Coba refund',
        );
    }

    public function test_refund_after_shipped_requires_returned_shipment(): void
    {
        $stockBefore = $this->variant->fresh()->stock;
        $order = $this->placeOrder();
        $this->pay($order);

        $statuses = app(OrderStatusService::class);
        $admin = $this->userWithRole(UserRole::Admin);
        $master = $this->userWithRole(UserRole::AdminMaster);

        $statuses->change($order->fresh(), OrderStatus::MenungguKonfirmasi, $admin);
        $statuses->change($order->fresh(), OrderStatus::Dikemas, $admin);

        $shipping = app(ShippingService::class);
        $shipment = $shipping->createForOrder($order->fresh(), 'JNE', 'REG', 1000, 9000, 'JNE123');

        $shipping->updateTracking($shipment, new TrackingEvent(
            trackingNumber: 'JNE123',
            status: ShipmentStatus::Pickup,
            description: 'Paket dijemput',
            occurredAt: now(),
        ));

        $payment = $order->latestPayment()->first();

        try {
            app(PaymentService::class)->refundManual($payment, $master, 'Refund sebelum kembali');
            $this->fail('Refund sebelum paket kembali seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment', $e->errors());
        }

        $this->assertSame(OrderStatus::Dikirim, $order->fresh()->status);

        $shipping->updateTracking($shipment->fresh(), new TrackingEvent(
            trackingNumber: 'JNE123',
            status: ShipmentStatus::Returned,
            description: 'Paket kembali ke toko',
            occurredAt: now(),
        ));

        app(PaymentService::class)->refundManual($payment->fresh(), $master, 'Paket sudah kembali');

        $this->assertSame(PaymentStatus::Refunded, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Dibatalkan, $order->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }
}
