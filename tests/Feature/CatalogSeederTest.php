<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_catalog_seeds_categories_products_and_variants(): void
    {
        $this->seed(DemoCatalogSeeder::class);

        $this->assertDatabaseCount('categories', 7);
        $this->assertDatabaseCount('products', 10);
        $this->assertDatabaseCount('product_variants', 30);

        $product = Product::query()
            ->with(['category', 'variants'])
            ->where('slug', 'mules-ayu')
            ->firstOrFail();

        $this->assertSame('Mules', $product->category->name);
        $this->assertSame(ProductStatus::Active, $product->status);
        $this->assertCount(3, $product->variants);
        $this->assertSame(219000.0, (float) $product->variants->first()->price);
    }

    public function test_catalog_seeder_is_idempotent(): void
    {
        $this->seed(DemoCatalogSeeder::class);
        $this->seed(DemoCatalogSeeder::class);

        $this->assertDatabaseCount('categories', 7);
        $this->assertDatabaseCount('products', 10);
        $this->assertDatabaseCount('product_variants', 30);
    }

    public function test_product_scopes_filter_active_products_by_search_and_category(): void
    {
        $this->seed(DemoCatalogSeeder::class);

        $this->assertSame(2, Product::query()->active()->search('mules')->count());
        $this->assertSame(2, Product::query()->inCategory('heels')->count());
    }

    public function test_variant_stock_available_subtracts_reserved_stock(): void
    {
        $this->seed(DemoCatalogSeeder::class);

        $variant = ProductVariant::query()->firstOrFail();
        $variant->forceFill(['stock_reserved' => 2])->save();

        $this->assertSame(
            $variant->stock_on_hand - 2,
            $variant->fresh()->stock_available
        );
    }
}
