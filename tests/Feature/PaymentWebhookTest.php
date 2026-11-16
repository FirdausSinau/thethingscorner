<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        config(['payment.fake.enabled' => true]);

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

    private function makePayment(): array
    {
        app(CartService::class)->add($this->buyer, $this->variant, 1);

        $order = app(CheckoutService::class)->placeOrder(
            $this->buyer,
            $this->address,
            PaymentMethod::Qris,
        );

        $payment = app(PaymentService::class)->createForOrder($order, PaymentMethod::Qris);

        return [$order, $payment];
    }

    public function test_fake_webhook_marks_payment_paid(): void
    {
        [$order, $payment] = $this->makePayment();
        $stockBefore = $this->variant->fresh()->stock;

        $this->postJson(route('webhooks.payment', ['provider' => 'fake']), [
            'external_id' => $payment->external_id,
            'status' => 'PAID',
            'amount' => $payment->amount,
        ])
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'payment_status' => 'PAID',
                'order_status' => 'DIBAYAR',
            ]);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Dibayar, $order->fresh()->status);
        $this->assertSame($stockBefore - 1, $this->variant->fresh()->stock);
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        [$order, $payment] = $this->makePayment();
        $stockBefore = $this->variant->fresh()->stock;
        $payload = [
            'external_id' => $payment->external_id,
            'status' => 'PAID',
            'amount' => $payment->amount,
        ];

        $this->postJson(route('webhooks.payment', ['provider' => 'fake']), $payload)->assertOk();
        $this->postJson(route('webhooks.payment', ['provider' => 'fake']), $payload)->assertOk();

        $this->assertSame($stockBefore - 1, $this->variant->fresh()->stock);
        $this->assertDatabaseHas('integration_logs', [
            'event' => 'callback.ignored',
            'status' => 'ignored',
        ]);
    }

    public function test_webhook_with_wrong_amount_is_rejected(): void
    {
        [, $payment] = $this->makePayment();

        $this->postJson(route('webhooks.payment', ['provider' => 'fake']), [
            'external_id' => $payment->external_id,
            'status' => 'PAID',
            'amount' => $payment->amount - 1,
        ])
            ->assertStatus(422)
            ->assertJson(['status' => 'error']);

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
    }

    public function test_webhook_with_invalid_payload_is_logged(): void
    {
        $this->postJson(route('webhooks.payment', ['provider' => 'fake']), [
            'external_id' => 'FAKE-UNKNOWN',
            'status' => 'SETTLED',
            'amount' => 1000,
        ])
            ->assertStatus(422)
            ->assertJson(['status' => 'error']);

        $this->assertDatabaseHas('integration_logs', [
            'event' => 'callback.invalid',
            'status' => 'failed',
        ]);
    }

    public function test_unknown_provider_is_rejected(): void
    {
        $this->postJson(route('webhooks.payment', ['provider' => 'midtrans']), [
            'external_id' => 'FAKE-001',
            'status' => 'PAID',
            'amount' => 1000,
        ])->assertNotFound();
    }

    public function test_fake_webhook_can_be_disabled(): void
    {
        config(['payment.fake.enabled' => false]);

        $this->postJson(route('webhooks.payment', ['provider' => 'fake']), [
            'external_id' => 'FAKE-001',
            'status' => 'PAID',
            'amount' => 1000,
        ])->assertNotFound();
    }
}
