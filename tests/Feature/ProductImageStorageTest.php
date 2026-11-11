<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageStorageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => UserRole::Admin, 'is_active' => true])->save();
        $this->product = Product::query()->firstOrFail();
    }

    public function test_product_image_can_be_uploaded_to_public_disk(): void
    {
        Storage::fake('public');
        config(['filesystems.product_images_disk' => 'public']);

        $response = $this->actingAs($this->admin)->post(
            route('admin.products.images.store', $this->product),
            ['images' => [UploadedFile::fake()->image('produk.jpg')->size(500)]]
        );

        $response->assertRedirect();

        $image = $this->product->images()->firstOrFail();

        $this->assertSame('public', config('filesystems.product_images_disk'));
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_product_image_can_be_uploaded_to_s3_disk_when_configured(): void
    {
        Storage::fake('s3');
        config(['filesystems.product_images_disk' => 's3']);

        $response = $this->actingAs($this->admin)->post(
            route('admin.products.images.store', $this->product),
            ['images' => [UploadedFile::fake()->image('produk.jpg')->size(500)]]
        );

        $response->assertRedirect();

        $image = $this->product->images()->firstOrFail();

        Storage::disk('s3')->assertExists($image->path);
    }
}
