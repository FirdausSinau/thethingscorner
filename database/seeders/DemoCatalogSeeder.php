<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Seed categories, products, and variants from the demo CSV files.
     */
    public function run(): void
    {
        $products = $this->readCsv('products.csv');
        $variants = $this->readCsv('product_variants.csv');

        foreach ($products as $row) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($row['category'])],
                ['name' => $row['category']]
            );

            Product::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'status' => ProductStatus::tryFrom($row['status']) ?? ProductStatus::Active,
                    'weight_grams' => (int) $row['weight_grams'],
                ]
            );
        }

        $productsBySlug = Product::query()
            ->whereIn('slug', collect($variants)->pluck('product_slug')->unique())
            ->get()
            ->keyBy('slug');

        foreach ($variants as $row) {
            $product = $productsBySlug->get($row['product_slug']);

            if (! $product) {
                throw new RuntimeException("Produk {$row['product_slug']} tidak ditemukan untuk SKU {$row['sku']}.");
            }

            ProductVariant::updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'product_id' => $product->id,
                    'size' => $row['size'],
                    'color' => $row['color'],
                    'price' => (float) $row['price'],
                    'stock_on_hand' => (int) $row['stock_on_hand'],
                    'stock_reserved' => (int) $row['stock_reserved'],
                ]
            );
        }
    }

    /**
     * Read a CSV file from docs/data.
     *
     * @return array<int, array<string, string>>
     */
    private function readCsv(string $file): array
    {
        $path = base_path('docs/data/'.$file);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("File CSV tidak bisa dibuka: {$path}");
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return [];
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if ($values === [null] || count($values) !== count($header)) {
                continue;
            }

            $rows[] = array_combine($header, array_map('trim', $values));
        }

        fclose($handle);

        return $rows;
    }
}
