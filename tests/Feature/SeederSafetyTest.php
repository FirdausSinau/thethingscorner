<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_keeps_stock_and_product_edits(): void
    {
        $this->seed(DatabaseSeeder::class);

        $variant = ProductVariant::query()->where('sku', 'THEON-DOCMART-HITAM-39')->firstOrFail();
        $variant->update(['stock' => 99]);

        $product = Product::query()->where('slug', 'theon-docmart')->firstOrFail();
        $product->update(['base_price' => 300000, 'is_active' => false]);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(99, $variant->fresh()->stock);
        $this->assertSame(300000, $product->fresh()->base_price);
        $this->assertFalse($product->fresh()->is_active);
        $this->assertSame(1, Product::query()->count());
    }

    public function test_reseeding_does_not_reset_demo_password_or_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@demo.local')->firstOrFail();
        $admin->forceFill([
            'password' => 'kata-sandi-baru',
            'role' => UserRole::Admin,
        ])->save();

        $this->seed(DatabaseSeeder::class);

        $admin->refresh();

        $this->assertTrue(Hash::check('kata-sandi-baru', $admin->password));
        $this->assertSame(UserRole::Admin, $admin->role);
    }

    public function test_demo_seed_is_skipped_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Product::query()->count());
    }
}
