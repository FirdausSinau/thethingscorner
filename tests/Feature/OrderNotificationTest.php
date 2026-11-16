<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderStatusService;
use App\Services\PaymentService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

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

    private function placeOrder(): Order
    {
        app(CartService::class)->add($this->buyer, $this->variant, 1);

        return app(CheckoutService::class)->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::ManualTransfer,
        );
    }

    public function test_status_change_sends_database_notification_to_buyer(): void
    {
        $order = $this->placeOrder();
        $payments = app(PaymentService::class);
        $payment = $payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $payments->markPaid($payment);

        $notification = $this->buyer->notifications()->latest('created_at')->first();

        $this->assertNotNull($notification);
        $this->assertSame('App\Notifications\OrderStatusChanged', $notification->type);
        $this->assertSame($order->order_code, $notification->data['order_code']);
        $this->assertSame(OrderStatus::Dibayar->value, $notification->data['to_status']);
        $this->assertNull($notification->read_at);
    }

    public function test_each_status_change_creates_one_notification(): void
    {
        $order = $this->placeOrder();
        $payments = app(PaymentService::class);
        $payment = $payments->createForOrder($order, PaymentMethod::ManualTransfer);
        $payments->markPaid($payment);

        app(OrderStatusService::class)->change(
            $order->fresh(),
            OrderStatus::MenungguKonfirmasi,
            User::factory()->create(),
            'Dikonfirmasi admin',
        );

        $this->assertSame(2, $this->buyer->notifications()->count());
    }
}
