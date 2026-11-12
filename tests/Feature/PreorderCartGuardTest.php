<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PreorderCartGuardTest extends TestCase
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

    private function markPreorder(): void
    {
        Product::query()
            ->whereKey($this->variant->color->product_id)
            ->update(['is_preorder' => true]);
    }

    public function test_preorder_product_cannot_be_added_to_cart(): void
    {
        $this->markPreorder();

        try {
            app(CartService::class)->add($this->buyer, $this->variant->fresh(), 1);

            $this->fail('Produk preorder seharusnya ditolak dari keranjang.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('variant_id', $e->errors());
        }

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_preorder_item_left_in_cart_blocks_checkout(): void
    {
        app(CartService::class)->add($this->buyer, $this->variant, 1);

        $reservedBefore = $this->variant->fresh()->stock_reserved;

        $this->markPreorder();

        try {
            app(CheckoutService::class)->placeOrder($this->buyer, $this->address, PaymentMethod::ManualTransfer);

            $this->fail('Checkout produk preorder seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cart', $e->errors());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame($reservedBefore, $this->variant->fresh()->stock_reserved);
    }
}
