<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductColor;
use App\Support\ProductImageDisk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductColorController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:50',
                Rule::unique('product_colors', 'name')->where('product_id', $product->id)],
            'hex_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $product->colors()->create($data);

        return back()->with('status', 'Warna ditambahkan. Lanjut isi ukuran dan foto.');
    }

    public function destroy(ProductColor $color)
    {
        ProductImageDisk::disk()->delete($color->images()->pluck('path')->all());
        $color->delete();

        return back()->with('status', 'Warna dihapus.');
    }
}
