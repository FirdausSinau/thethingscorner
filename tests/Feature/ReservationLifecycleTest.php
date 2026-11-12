<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ReservationService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReservationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-11 10:00:00');

        $this->seed(CatalogSeeder::class);

        $this->buyer = User::factory()->create();
        $this->variant = ProductVariant::query()->where('stock', '>', 2)->firstOrFail();

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

    private function placeOrder(): Order
    {
        app(CartService::class)->add($this->buyer, $this->variant, 2);

        return app(CheckoutService::class)->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::ManualTransfer
        );
    }

    public function test_expired_reservation_is_released_only_once(): void
    {
        $reservedBefore = $this->variant->stock_reserved;
        $order = $this->placeOrder();
        $reservation = $order->reservations->first();

        Carbon::setTestNow(now()->addMinutes(61));

        $service = app(ReservationService::class);

        $this->assertTrue($service->releaseIfExpired($reservation));
        $this->assertFalse($service->releaseIfExpired($reservation));
        $this->assertSame($reservedBefore, $this->variant->fresh()->stock_reserved);
        $this->assertSame(OrderStatus::Kedaluwarsa, $order->fresh()->status);
    }

    public function test_qris_reservation_expires_after_30_seconds(): void
    {
        config(['payment.reservations.qris_seconds' => 30]);

        $reservedBefore = $this->variant->stock_reserved;

        app(CartService::class)->add($this->buyer, $this->variant, 1);

        $order = app(CheckoutService::class)->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::Qris
        );

        Carbon::setTestNow(now()->addSeconds(31));

        $this->artisan('reservations:release-expired')->assertSuccessful();

        $this->assertSame(ReservationStatus::Expired, $order->reservations()->first()->status);
        $this->assertSame(OrderStatus::Kedaluwarsa, $order->fresh()->status);
        $this->assertSame($reservedBefore, $this->variant->fresh()->stock_reserved);
    }

    public function test_reservation_marked_consumed_elsewhere_is_not_released(): void
    {
        $reservedBefore = $this->variant->stock_reserved;
        $order = $this->placeOrder();
        $reservation = $order->reservations->first();

        StockReservation::query()
            ->whereKey($reservation->id)
            ->update(['status' => ReservationStatus::Consumed->value]);

        Carbon::setTestNow(now()->addMinutes(61));

        $this->assertFalse(app(ReservationService::class)->releaseIfExpired($reservation));
        $this->assertSame($reservedBefore + 2, $this->variant->fresh()->stock_reserved);
        $this->assertSame(OrderStatus::MenungguPembayaran, $order->fresh()->status);
    }

    public function test_reservation_that_has_not_expired_is_kept(): void
    {
        $order = $this->placeOrder();
        $reservation = $order->reservations->first();

        $this->assertFalse(app(ReservationService::class)->releaseIfExpired($reservation));
        $this->assertSame(ReservationStatus::Active, $reservation->fresh()->status);
    }

    public function test_expiry_time_is_shown_only_while_waiting_for_payment(): void
    {
        $order = $this->placeOrder();

        $this->assertNotNull($order->reservation_expires_at);

        $order->update(['status' => OrderStatus::Dibayar]);

        $this->assertNull($order->fresh()->reservation_expires_at);
    }
}
