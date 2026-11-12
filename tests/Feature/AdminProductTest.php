<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => UserRole::Admin, 'is_active' => true])->save();

        $this->product = Product::query()->firstOrFail();
        $this->variant = ProductVariant::query()->where('stock', '>', 2)->firstOrFail();
    }

    private function placeOrderForVariant(): void
    {
        $buyer = User::factory()->create();

        app(CartService::class)->add($buyer, $this->variant, 1);

        $address = Address::create([
            'user_id' => $buyer->id,
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

        app(CheckoutService::class)->placeOrder($buyer, $address, PaymentMethod::ManualTransfer);
    }

    public function test_admin_product_index_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee($this->product->name);
    }

    public function test_admin_product_edit_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $this->product))
            ->assertOk()
            ->assertSee($this->product->name)
            ->assertSee('Nonaktifkan produk');
    }

    public function test_deactivate_product_sets_inactive_without_deleting(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.products.deactivate', $this->product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertFalse($this->product->fresh()->is_active);
        $this->assertDatabaseHas('products', ['id' => $this->product->id]);
    }

    public function test_variant_with_order_history_cannot_be_deleted(): void
    {
        $this->placeOrderForVariant();

        $this->actingAs($this->admin)
            ->from(route('admin.products.edit', $this->product))
            ->delete(route('admin.variants.destroy', $this->variant))
            ->assertRedirect(route('admin.products.edit', $this->product))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('product_variants', ['id' => $this->variant->id]);
    }

    public function test_variant_without_history_can_be_deleted(): void
    {
        $variant = $this->variant->color->variants()->create([
            'size' => '99',
            'sku' => 'TEST-VARIANT-99',
            'stock' => 1,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.products.edit', $this->product))
            ->delete(route('admin.variants.destroy', $variant))
            ->assertRedirect(route('admin.products.edit', $this->product));

        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    public function test_stock_below_reserved_is_rejected(): void
    {
        $stockBefore = $this->variant->fresh()->stock;

        $this->variant->forceFill(['stock_reserved' => 2])->save();

        $response = $this->actingAs($this->admin)
            ->from(route('admin.products.edit', $this->product))
            ->put(route('admin.colors.variants.update', $this->variant->color), [
                'variants' => [
                    $this->variant->id => [
                        'stock' => 1,
                        'seen' => $this->variant->stock,
                        'price' => null,
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.products.edit', $this->product));
        $response->assertSessionHasErrors("variants.{$this->variant->id}.stock");

        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }

    public function test_stock_edit_with_stale_seen_value_is_rejected(): void
    {
        $stockBefore = $this->variant->fresh()->stock;

        $response = $this->actingAs($this->admin)
            ->from(route('admin.products.edit', $this->product))
            ->put(route('admin.colors.variants.update', $this->variant->color), [
                'variants' => [
                    $this->variant->id => [
                        'stock' => 9,
                        'seen' => $this->variant->stock + 1,
                        'price' => null,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors("variants.{$this->variant->id}.stock");
        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }

    public function test_color_with_order_history_cannot_be_deleted(): void
    {
        $this->placeOrderForVariant();

        $this->actingAs($this->admin)
            ->from(route('admin.products.edit', $this->product))
            ->delete(route('admin.colors.destroy', $this->variant->color))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('product_colors', ['id' => $this->variant->product_color_id]);
    }
}
