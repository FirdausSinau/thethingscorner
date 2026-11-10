<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;          // sesuaikan namespace
use App\Models\Product\ProductImage;     // sesuaikan namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $next = (int) ProductImage::where('product_id', $product->id)->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $product->images()->create([
                'path'       => $file->store("products/{$product->id}/gallery", 's3'),
                'sort_order' => $next++,
            ]);
        }

        return back()->with('status', 'Foto produk diunggah. Foto pertama jadi sampul di katalog.');
    }

    public function destroy(ProductImage $productImage)
    {
        Storage::disk('s3')->delete($productImage->path);
        $productImage->delete();

        return back()->with('status', 'Foto produk dihapus.');
    }
}