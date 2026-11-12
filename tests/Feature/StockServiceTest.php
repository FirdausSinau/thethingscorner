<?php

namespace Tests\Feature;

use App\Models\Product\ProductVariant;
use App\Services\StockService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->variant = ProductVariant::query()->where('stock', '>', 2)->firstOrFail();
        $this->variant->forceFill(['stock_reserved' => 3])->save();
    }

    public function test_physical_stock_can_be_set_to_the_reserved_amount(): void
    {
        $updated = app(StockService::class)->setPhysicalStock($this->variant, 3);

        $this->assertSame(3, $updated->stock);
        $this->assertSame(3, $this->variant->fresh()->stock);
    }

    public function test_physical_stock_below_reserved_is_rejected(): void
    {
        $stockBefore = $this->variant->stock;

        try {
            app(StockService::class)->setPhysicalStock($this->variant, 2);

            $this->fail('Stok di bawah jumlah yang ditahan seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stock', $e->errors());
        }

        $this->assertSame($stockBefore, $this->variant->fresh()->stock);
    }

    public function test_negative_stock_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(StockService::class)->setPhysicalStock($this->variant, -1);
    }
}
