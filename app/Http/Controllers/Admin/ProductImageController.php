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
            $product->images()->create([
                'path'       => $file->store("products/{$product->id}/gallery", ProductImageDisk::name()),
                'sort_order' => $next++,
            ]);
        }

        return back()->with('status', 'Foto produk diunggah. Foto pertama jadi sampul di katalog.');
    }

    public function destroy(ProductImage $productImage)
    {
        ProductImageDisk::disk()->delete($productImage->path);
        $productImage->delete();

        return back()->with('status', 'Foto produk dihapus.');
    }
}

