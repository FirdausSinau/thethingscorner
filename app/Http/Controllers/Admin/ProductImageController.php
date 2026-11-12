<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductImage;
use App\Support\ProductImageDisk;
use Illuminate\Http\Request;

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
            $path = $file->store(
                "products/{$product->id}/gallery",
                'neon'
            );
            
            $product->images()->create([
                'path'       => $path,
                'sort_order' => $next++,
            ]);
        }

        return back()->with('status', 'Foto produk berhasil diunggah.');
    }

    public function destroy(ProductImage $image)
    {
        if ($image->path) {
            ProductImageDisk::disk()->delete($image->path);
        }

        $image->delete();

        return back()->with('status', 'Foto berhasil dihapus.');
    }
}