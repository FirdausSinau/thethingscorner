<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);
    }

    public function test_catalog_index_is_public_and_lists_products(): void
    {
        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Theon Docmart');
    }

    public function test_catalog_search_filters_products(): void
    {
        $this->get(route('catalog.index', ['q' => 'Theon']))
            ->assertOk()
            ->assertSee('Theon Docmart');

        $this->get(route('catalog.index', ['q' => 'tidak-ada-produk']))
            ->assertOk()
            ->assertDontSee('Theon Docmart');
    }

    public function test_product_detail_is_public(): void
    {
        $this->get(route('products.show', ['product' => 'theon-docmart']))
            ->assertOk()
            ->assertSee('Theon Docmart')
            ->assertSee('Hitam');
    }

    public function test_buyer_cannot_access_admin_products(): void
    {
        $buyer = User::factory()->create();
        $buyer->forceFill(['role' => UserRole::Buyer, 'is_active' => true])->save();

        $this->actingAs($buyer)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_products(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => UserRole::Admin, 'is_active' => true])->save();

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Theon Docmart');
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => UserRole::Admin, 'is_active' => true])->save();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Produk Baru',
            'category_id' => Category::firstOrFail()->id,
            'description' => 'Produk untuk test.',
            'base_price' => 150000,
            'weight_grams' => 700,
            'is_active' => 1,
            'is_preorder' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Produk Baru',
            'base_price' => 150000,
            'weight_grams' => 700,
        ]);
        $this->assertSame(1, Product::where('slug', 'produk-baru')->count());
    }

    public function test_variant_stock_available_subtracts_reserved_stock(): void
    {
        $variant = ProductVariant::query()->firstOrFail();
        $variant->forceFill(['stock_reserved' => 2])->save();

        $this->assertSame(
            $variant->stock - 2,
            $variant->fresh()->stock_available
        );
    }
}
