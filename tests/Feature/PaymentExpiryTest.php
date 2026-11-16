<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaymentExpiryTest extends TestCase
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

    private function placeOrder(int $qty = 1): Order
    {
        app(CartService::class)->add($this->buyer, $this->variant, $qty);

        return app(CheckoutService::class)->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::Qris,
        );
    }

    public function test_expired_pending_payment_releases_reservation(): void
    {
        $stockBefore = $this->variant->fresh()->stock;
        $reservedBefore = $this->variant->fresh()->stock_reserved;

        $order = $this->placeOrder();
        $payment = app(PaymentService::class)->createForOrder($order, PaymentMethod::Qris);

        Carbon::setTestNow(now()->addSeconds(31));

        $this->artisan('payments:expire')
            ->expectsOutput('1 pembayaran kedaluwarsa.')
            ->assertSuccessful();

        $this->assertSame(PaymentStatus::Expired, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Kedaluwarsa, $order->fresh()->status);
        $this->assertSame(ReservationStatus::Expired, $order->reservations()->first()->status);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
        $this->assertSame($reservedBefore, $this->variant->fresh()->stock_reserved);
    }

    public function test_payment_with_future_expiry_is_untouched(): void
    {
        $order = $this->placeOrder();
        $payment = app(PaymentService::class)->createForOrder($order, PaymentMethod::Qris);

        $this->artisan('payments:expire')
            ->expectsOutput('0 pembayaran kedaluwarsa.')
            ->assertSuccessful();

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
    }

    public function test_paid_payment_is_not_expired(): void
    {
        $order = $this->placeOrder();
        $payments = app(PaymentService::class);
        $payment = $payments->createForOrder($order, PaymentMethod::Qris);
        $payments->markPaid($payment);

        Carbon::setTestNow(now()->addHour());

        $this->artisan('payments:expire')
            ->expectsOutput('0 pembayaran kedaluwarsa.')
            ->assertSuccessful();

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Dibayar, $order->fresh()->status);
    }

    public function test_failed_payment_expires_at_deadline(): void
    {
        $order = $this->placeOrder();
        $payments = app(PaymentService::class);
        $payment = $payments->createForOrder($order, PaymentMethod::Qris);
        $payments->markFailed($payment, 'Saldo tidak cukup');

        Carbon::setTestNow(now()->addSeconds(31));

        $this->artisan('payments:expire')
            ->expectsOutput('1 pembayaran kedaluwarsa.')
            ->assertSuccessful();

        $this->assertSame(PaymentStatus::Expired, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Kedaluwarsa, $order->fresh()->status);
    }
}
