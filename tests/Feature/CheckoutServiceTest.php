<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Address;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cart;

    private CheckoutService $checkout;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-11 10:00:00');

        $this->seed(CatalogSeeder::class);

        $this->cart = app(CartService::class);
        $this->checkout = app(CheckoutService::class);
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

    public function test_checkout_creates_order_items_and_reservation(): void
    {
        $stockBefore = $this->variant->stock;
        $reservedBefore = $this->variant->stock_reserved;

        $this->cart->add($this->buyer, $this->variant, 2);

        $order = $this->checkout->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::ManualTransfer,
            15000
        );

        $this->assertSame(OrderStatus::MenungguPembayaran, $order->status);
        $this->assertSame(OrderChannel::Web, $order->channel);
        $this->assertSame(PaymentMethod::ManualTransfer, $order->payment_method);
        $this->assertSame($this->variant->effective_price * 2, $order->subtotal);
        $this->assertSame(15000, $order->shipping_cost);
        $this->assertSame($order->subtotal + 15000, $order->total);
        $this->assertMatchesRegularExpression('/^TTC-\d{8}-[A-Z0-9]{6}$/', $order->order_code);

        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertSame($this->variant->sku, $item->sku);
        $this->assertSame($this->variant->size, $item->size);
        $this->assertSame($this->variant->color->name, $item->color);
        $this->assertSame(2, $item->qty);
        $this->assertSame($this->variant->effective_price * 2, $item->subtotal);

        $reservation = $order->reservations->first();
        $this->assertSame(ReservationStatus::Active, $reservation->status);
        $this->assertSame(2, $reservation->qty);
        $this->assertTrue($reservation->expires_at->equalTo(now()->addMinutes(60)));

        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
        $this->assertSame($reservedBefore + 2, $this->variant->fresh()->stock_reserved);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_qris_reservation_expires_in_30_seconds(): void
    {
        config(['payment.reservations.qris_seconds' => 30]);

        $this->cart->add($this->buyer, $this->variant, 1);

        $order = $this->checkout->placeOrder($this->buyer, $this->address, PaymentMethod::Qris);

        $this->assertTrue(
            $order->reservations->first()->expires_at->equalTo(now()->addSeconds(30))
        );
    }

    public function test_empty_cart_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->checkout->placeOrder($this->buyer, $this->address, PaymentMethod::ManualTransfer);
    }

    public function test_insufficient_stock_rolls_back_checkout(): void
    {
        $this->cart->add($this->buyer, $this->variant, 1);

        $this->variant->forceFill(['stock_reserved' => $this->variant->stock])->save();

        try {
            $this->checkout->placeOrder($this->buyer, $this->address, PaymentMethod::ManualTransfer);
            $this->fail('Checkout seharusnya gagal.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('stock_reservations', 0);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame($this->variant->stock, $this->variant->fresh()->stock_reserved);
    }

    public function test_address_must_belong_to_the_buyer(): void
    {
        $otherUser = User::factory()->create();
        $otherAddress = Address::create([
            'user_id' => $otherUser->id,
            'label' => 'Rumah',
            'recipient' => 'Orang Lain',
            'phone' => '0899999999',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Lain No. 2',
            'is_default' => true,
        ]);

        $this->cart->add($this->buyer, $this->variant, 1);

        $this->expectException(NotFoundHttpException::class);

        $this->checkout->placeOrder($this->buyer, $otherAddress, PaymentMethod::ManualTransfer);
    }

    public function test_expired_reservations_release_stock_and_expire_order(): void
    {
        $reservedBefore = $this->variant->stock_reserved;

        $this->cart->add($this->buyer, $this->variant, 2);
        $order = $this->checkout->placeOrder($this->buyer, $this->address, PaymentMethod::ManualTransfer);

        $this->assertSame($reservedBefore + 2, $this->variant->fresh()->stock_reserved);

        Carbon::setTestNow(now()->addMinutes(61));

        $this->artisan('reservations:release-expired')->assertSuccessful();

        $this->assertSame(ReservationStatus::Expired, $order->reservations()->first()->status);
        $this->assertSame(OrderStatus::Kedaluwarsa, $order->fresh()->status);
        $this->assertSame($reservedBefore, $this->variant->fresh()->stock_reserved);
    }
}
