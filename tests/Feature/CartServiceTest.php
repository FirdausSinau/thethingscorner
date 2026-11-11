<?php

namespace Tests\Feature;

use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cart;

    private User $buyer;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->cart = app(CartService::class);
        $this->buyer = User::factory()->create();
        $this->variant = ProductVariant::query()->where('stock', '>', 3)->firstOrFail();
    }

    public function test_item_can_be_added_without_changing_stock(): void
    {
        $stockBefore = $this->variant->stock;
        $reservedBefore = $this->variant->stock_reserved;

        $item = $this->cart->add($this->buyer, $this->variant, 2);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->buyer->id,
            'variant_id' => $this->variant->id,
            'qty' => 2,
        ]);
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
        $this->assertSame($reservedBefore, $this->variant->fresh()->stock_reserved);
        $this->assertSame(2, $item->qty);
    }

    public function test_adding_same_variant_increments_quantity(): void
    {
        $this->cart->add($this->buyer, $this->variant, 1);
        $item = $this->cart->add($this->buyer, $this->variant, 2);

        $this->assertSame(3, $item->qty);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_quantity_cannot_exceed_available_stock(): void
    {
        $this->expectException(ValidationException::class);

        $this->cart->add($this->buyer, $this->variant, $this->variant->stock_available + 1);
    }

    public function test_quantity_update_above_stock_is_rejected(): void
    {
        $item = $this->cart->add($this->buyer, $this->variant, 1);

        $this->expectException(ValidationException::class);

        $this->cart->update($this->buyer, $item, $this->variant->stock_available + 1);
    }

    public function test_item_can_be_removed(): void
    {
        $item = $this->cart->add($this->buyer, $this->variant, 1);

        $this->cart->remove($this->buyer, $item);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $this->variant->color->product->update(['is_active' => false]);

        $this->expectException(ValidationException::class);

        $this->cart->add($this->buyer, $this->variant, 1);
    }

    public function test_summary_uses_effective_price_and_counts_items(): void
    {
        $item = $this->cart->add($this->buyer, $this->variant, 2);
        $summary = $this->cart->summary($this->buyer);

        $expectedUnitPrice = (int) ($this->variant->price ?? $this->variant->color->product->base_price);

        $this->assertSame(1, $summary['items']->count());
        $this->assertSame(2, $summary['count']);
        $this->assertSame($expectedUnitPrice * 2, $summary['subtotal']);
        $this->assertSame($expectedUnitPrice * 2, $item->line_total);
    }

    public function test_reserved_stock_reduces_available_quantity(): void
    {
        $this->variant->forceFill([
            'stock_reserved' => $this->variant->stock - 1,
        ])->save();

        $this->expectException(ValidationException::class);

        $this->cart->add($this->buyer, $this->variant->fresh(), 2);
    }

    public function test_user_cannot_update_another_users_cart_item(): void
    {
        $item = $this->cart->add($this->buyer, $this->variant, 1);
        $otherUser = User::factory()->create();

        $this->expectException(NotFoundHttpException::class);

        $this->cart->update($otherUser, $item, 2);
    }
}
