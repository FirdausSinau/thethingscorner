<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\Payment\PaymentEvent;
use App\Services\PaymentService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $payments;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-16 10:00:00');

        $this->seed(CatalogSeeder::class);

        $this->payments = app(PaymentService::class);
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

    private function placeOrder(PaymentMethod $method = PaymentMethod::Qris, int $qty = 1): Order
    {
        app(CartService::class)->add($this->buyer, $this->variant, $qty);

        return app(CheckoutService::class)->placeOrder($this->buyer, $this->address, $method);
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    public function test_create_for_order_creates_pending_fake_payment(): void
    {
        $order = $this->placeOrder();
        $reservedAfterCheckout = $this->variant->fresh()->stock_reserved;

        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);

        $this->assertSame('fake', $payment->provider);
        $this->assertSame(PaymentMethod::Qris, $payment->method);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame('FAKE-'.$order->order_code, $payment->external_id);
        $this->assertSame((int) $order->total, $payment->amount);
        $this->assertTrue($payment->expires_at->equalTo(now()->addSeconds(30)));
        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
        $this->assertSame($reservedAfterCheckout, $this->variant->fresh()->stock_reserved);

        $again = $this->payments->createForOrder($order->fresh(), PaymentMethod::Qris);

        $this->assertSame($payment->id, $again->id);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_manual_transfer_uses_manual_provider(): void
    {
        $order = $this->placeOrder(PaymentMethod::ManualTransfer);

        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $this->assertSame('manual', $payment->provider);
        $this->assertSame('MANUAL-'.$order->order_code, $payment->external_id);
        $this->assertSame((int) $order->total, $payment->amount);
        $this->assertTrue($payment->expires_at->equalTo(now()->addHour()));
    }

    public function test_mark_paid_consumes_reservation_and_stock_once(): void
    {
        $order = $this->placeOrder();
        $stockBefore = $this->variant->fresh()->stock;
        $reservedAfterCheckout = $this->variant->fresh()->stock_reserved;

        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);

        $this->payments->markPaid($payment);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame(OrderStatus::Dibayar, $order->fresh()->status);
        $this->assertSame(ReservationStatus::Consumed, $order->reservations()->first()->status);
        $this->assertSame($stockBefore - 1, $this->variant->fresh()->stock);
        $this->assertSame($reservedAfterCheckout - 1, $this->variant->fresh()->stock_reserved);

        $this->payments->markPaid($payment->fresh());

        $this->assertSame($stockBefore - 1, $this->variant->fresh()->stock);
        $this->assertSame(1, $order->statusLogs()->where('to_status', OrderStatus::Dibayar->value)->count());
    }

    public function test_mark_paid_rejects_amount_that_does_not_match_order_total(): void
    {
        $order = $this->placeOrder();
        $stockBefore = $this->variant->fresh()->stock;

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'method' => PaymentMethod::ManualTransfer,
            'external_id' => 'MANUAL-WRONG-AMOUNT',
            'amount' => (int) $order->total - 1,
        ]);

        try {
            $this->payments->markPaid($payment);

            $this->fail('Nominal yang tidak sama dengan total order seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment', $e->errors());
        }

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }

    public function test_duplicate_callback_does_not_consume_stock_twice(): void
    {
        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);
        $stockBefore = $this->variant->fresh()->stock;

        $event = new PaymentEvent(
            provider: 'fake',
            externalId: $payment->external_id,
            status: PaymentStatus::Paid,
            amount: $payment->amount,
        );

        $this->payments->handleCallback($event);
        $this->payments->handleCallback($event);

        $this->assertSame($stockBefore - 1, $this->variant->fresh()->stock);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertDatabaseHas('integration_logs', [
            'event' => 'callback.ignored',
            'status' => 'ignored',
        ]);
    }

    public function test_callback_log_redacts_signature_and_token(): void
    {
        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);

        $this->payments->handleCallback(new PaymentEvent(
            provider: 'fake',
            externalId: $payment->external_id,
            status: PaymentStatus::Paid,
            amount: $payment->amount,
            raw: [
                'signature' => 'secret-signature',
                'token' => 'secret-token',
                'status' => 'PAID',
            ],
        ));

        $log = IntegrationLog::query()
            ->where('event', 'callback.received')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('[redacted]', $log->payload['signature']);
        $this->assertSame('[redacted]', $log->payload['token']);
        $this->assertSame('PAID', $log->payload['status']);
    }

    public function test_callback_with_wrong_amount_is_rejected(): void
    {
        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);
        $stockBefore = $this->variant->fresh()->stock;

        try {
            $this->payments->handleCallback(new PaymentEvent(
                provider: 'fake',
                externalId: $payment->external_id,
                status: PaymentStatus::Paid,
                amount: $payment->amount - 1,
            ));

            $this->fail('Nominal yang tidak cocok seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment', $e->errors());
        }

        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
        $this->assertDatabaseHas('integration_logs', [
            'event' => 'callback.failed',
            'status' => 'failed',
        ]);
    }

    public function test_failed_payment_keeps_order_open_and_allows_new_attempt(): void
    {
        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);

        $this->payments->markFailed($payment, 'Saldo tidak cukup');

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
        $this->assertSame(ReservationStatus::Active, $order->reservations()->first()->status);

        $retry = $this->payments->createForOrder($order->fresh(), PaymentMethod::Qris);

        $this->assertNotSame($payment->id, $retry->id);
        $this->assertSame('FAKE-'.$order->order_code.'-2', $retry->external_id);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_expire_releases_reservation_without_decrementing_stock(): void
    {
        $stockBeforeCheckout = $this->variant->fresh()->stock;
        $reservedBeforeCheckout = $this->variant->fresh()->stock_reserved;

        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);

        $this->payments->expire($payment);

        $this->assertSame(PaymentStatus::Expired, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Kedaluwarsa, $order->fresh()->status);
        $this->assertSame(ReservationStatus::Expired, $order->reservations()->first()->status);
        $this->assertSame($stockBeforeCheckout, $this->variant->fresh()->stock);
        $this->assertSame($reservedBeforeCheckout, $this->variant->fresh()->stock_reserved);
    }

    public function test_expired_payment_cannot_be_paid_later(): void
    {
        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);

        $this->payments->expire($payment);

        $this->expectException(ValidationException::class);

        $this->payments->markPaid($payment->fresh());
    }

    public function test_manual_verification_requires_proof(): void
    {
        $order = $this->placeOrder(PaymentMethod::ManualTransfer);
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);
        $admin = $this->userWithRole(UserRole::Admin);

        $this->expectException(ValidationException::class);

        $this->payments->verifyManual($payment, $admin);
    }

    public function test_admin_can_verify_manual_payment_with_proof(): void
    {
        $order = $this->placeOrder(PaymentMethod::ManualTransfer);
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);
        $payment->update(['proof_path' => 'payment-proofs/bukti.jpg']);

        $stockBefore = $this->variant->fresh()->stock;
        $admin = $this->userWithRole(UserRole::Admin);

        $this->payments->verifyManual($payment->fresh(), $admin);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame($admin->id, $payment->fresh()->verified_by);
        $this->assertSame(OrderStatus::Dibayar, $order->fresh()->status);
        $this->assertSame($stockBefore - 1, $this->variant->fresh()->stock);
    }

    public function test_non_admin_cannot_verify_manual_payment(): void
    {
        $order = $this->placeOrder(PaymentMethod::ManualTransfer);
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);
        $payment->update(['proof_path' => 'payment-proofs/bukti.jpg']);

        $this->expectException(AuthorizationException::class);

        $this->payments->verifyManual($payment->fresh(), $this->buyer);
    }

    public function test_admin_can_reject_manual_proof_without_changing_status(): void
    {
        $order = $this->placeOrder(PaymentMethod::ManualTransfer);
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);
        $payment->update(['proof_path' => 'payment-proofs/bukti.jpg']);
        $admin = $this->userWithRole(UserRole::Admin);

        $this->payments->rejectManual($payment, $admin, 'Nominal tidak sesuai');

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
        $this->assertDatabaseHas('integration_logs', [
            'event' => 'payment.manual_rejected',
            'message' => 'Nominal tidak sesuai',
        ]);
    }

    public function test_reject_manual_requires_note(): void
    {
        $order = $this->placeOrder(PaymentMethod::ManualTransfer);
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);
        $admin = $this->userWithRole(UserRole::Admin);

        $this->expectException(ValidationException::class);

        $this->payments->rejectManual($payment, $admin, '   ');
    }

    public function test_payment_cannot_be_created_for_expired_order(): void
    {
        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);
        $this->payments->expire($payment);

        $this->expectException(ValidationException::class);

        $this->payments->createForOrder($order->fresh(), PaymentMethod::Qris);
    }

    public function test_callback_cannot_pay_another_providers_payment(): void
    {
        $order = $this->placeOrder(PaymentMethod::ManualTransfer);
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $this->expectException(ValidationException::class);

        $this->payments->handleCallback(new PaymentEvent(
            provider: 'fake',
            externalId: $payment->external_id,
            status: PaymentStatus::Paid,
            amount: $payment->amount,
        ));
    }
}
