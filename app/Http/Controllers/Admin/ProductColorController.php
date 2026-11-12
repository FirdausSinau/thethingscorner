<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductColor;
use App\Support\ProductImageDisk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductColorController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('product_colors', 'name')->where('product_id', $product->id)],
            'hex_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $product->colors()->create($data);

        return back()->with('status', 'Warna ditambahkan. Lanjut isi ukuran dan foto.');
    }

    public function destroy(ProductColor $color): RedirectResponse
    {
        $color->load(['variants', 'images']);

        $inUse = $color->variants->contains(
            fn ($variant): bool => $variant->orderItems()->exists() || $variant->stock_reserved > 0
        );

        if ($inUse) {
            return back()->withErrors([
                'delete' => 'Warna ini sudah dipakai di pesanan atau stoknya sedang ditahan, jadi tidak bisa dihapus.',
            ]);
        }

        $paths = $color->images->pluck('path')->all();

        DB::transaction(fn () => $color->delete());

        if ($paths !== []) {
            ProductImageDisk::disk()->delete($paths);
        }

        return back()->with('status', 'Warna dihapus.');
    }
}
