<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductColor;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'variants.*.seen'  => ['required', 'integer'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $color) {
            // Kunci semua varian warna ini (urut id agar tidak deadlock),
            // supaya tidak bersilangan dengan perubahan status pesanan.
            $variants = $color->variants()->orderBy('id')->lockForUpdate()->get();

            foreach ($variants as $variant) {
                $row = $request->input("variants.{$variant->id}");

                if ($row === null) {
                    continue;
                }

                if ((int) $row['seen'] !== (int) $variant->stock) {
                    throw ValidationException::withMessages([
                        "variants.{$variant->id}.stock" => "Ukuran {$variant->size}: stok berubah saat kamu mengedit (sekarang {$variant->stock}). Muat ulang halaman, lalu coba lagi.",
                    ]);
                }

                if ((int) $row['stock'] < (int) $variant->stock_reserved) {
                    throw ValidationException::withMessages([
                        "variants.{$variant->id}.stock" => "Ukuran {$variant->size}: stok tidak boleh di bawah {$variant->stock_reserved} (sedang ditahan pesanan).",
                    ]);
                }

                $variant->update([
                    'stock' => (int) $row['stock'],
                    'price' => isset($row['price']) && $row['price'] !== '' ? (int) $row['price'] : null,
                ]);
            }
        });

        return back()->with('status', 'Stok warna ' . $color->name . ' disimpan.');
    }

    public function destroy(ProductVariant $variant)
    {
        if ($variant->orderItems()->exists() || $variant->stock_reserved > 0) {
            return back()->withErrors([
                'delete' => "Ukuran {$variant->size} sudah pernah dipesan atau sedang ditahan pesanan, jadi tidak bisa dihapus. Set stoknya ke 0 saja.",
            ]);
        }

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