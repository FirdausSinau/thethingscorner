<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductColor;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    public function store(Request $request, ProductColor $color)
    {
        $data = $request->validate([
            'size'  => ['required', 'string', 'max:10',
                Rule::unique('product_variants', 'size')->where('product_color_id', $color->id)],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sku'] = $this->makeSku($color, $data['size']);
        $color->variants()->create($data);

        return back()->with('status', 'Ukuran ' . $data['size'] . ' ditambahkan.');
    }

    public function updateMany(Request $request, ProductColor $color)
    {
        $request->validate([
            'variants'         => ['required', 'array'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $color) {
            // Loop dari varian milik warna ini, jadi ID varian milik produk lain
            // yang disisipkan di form tidak akan ikut berubah.
            foreach ($color->variants as $variant) {
                $row = $request->input("variants.{$variant->id}");

                if ($row === null) {
                    continue;
                }

                $variant->update([
                    'stock' => $row['stock'],
                    'price' => $row['price'] ?? null,
                ]);
            }
        });

        return back()->with('status', 'Stok warna ' . $color->name . ' disimpan.');
    }

    public function destroy(ProductVariant $variant)
    {
        $variant->delete();

        return back()->with('status', 'Ukuran dihapus.');
    }

    private function makeSku(ProductColor $color, string $size): string
    {
        $base = $sku = strtoupper(Str::slug($color->product->slug . '-' . $color->name . '-' . $size));
        $i = 2;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $base . '-' . $i++;
        }

        return $sku;
    }
}