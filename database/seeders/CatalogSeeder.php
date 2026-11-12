<?php

namespace Database\Seeders;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Support\ProductImageDisk;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Hanya membuat data yang belum ada. Stok dan perubahan dari admin tidak ditimpa
     * saat seeder dijalankan ulang di database bersama.
     */
    public function run(): void
    {
        $boots = Category::firstOrCreate(['slug' => 'boots'], ['name' => 'Boots']);

        $product = Product::firstOrCreate(
            ['slug' => Str::slug('Theon Docmart')],
            [
                'category_id' => $boots->id,
                'name' => 'Theon Docmart',
                'description' => 'Boots kanvas ringan untuk harian.',
                'base_price' => 250000,
                'is_active' => true,
                'is_preorder' => false,
                'estimated_ready_date' => null,
            ]
        );

        $colors = [
            'Hitam' => ['#111111', [35 => 10, 37 => 10, 38 => 10, 39 => 10, 40 => 10]],
            'Cream' => ['#e0ded3', [35 => 10, 37 => 10, 38 => 10, 39 => 10, 40 => 10]],
        ];

        foreach ($colors as $colorName => [$hex, $sizes]) {
            $color = $product->colors()->firstOrCreate(
                ['name' => $colorName],
                ['hex_code' => $hex]
            );

            foreach ($sizes as $size => $stock) {
                $color->variants()->firstOrCreate(
                    ['size' => (string) $size],
                    [
                        'sku' => strtoupper(Str::slug("Theon Docmart-{$colorName}-{$size}")),
                        'stock' => $stock,
                    ]
                );
            }
        }

        if ($product->wasRecentlyCreated) {
            $this->attachCover($product, 'Theon Dockmart.png');
        }
    }

    private function attachCover(Product $product, string $fileName): void
    {
        $local = Storage::disk('local');

        if (! $local->exists($fileName)) {
            $this->command?->warn("Foto '{$fileName}' tidak ditemukan di storage/app/private, dilewati.");

            return;
        }

        $path = ProductImageDisk::disk()->putFileAs(
            "products/{$product->id}/gallery",
            new File($local->path($fileName)),
            'theon-dockmart.png'
        );

        $product->images()->firstOrCreate(['path' => $path], ['sort_order' => 1]);
    }
}
