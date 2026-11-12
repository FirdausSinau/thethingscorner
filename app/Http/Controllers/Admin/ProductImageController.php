<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Product\Product;
use App\Models\Product\ProductImage;
use App\Support\ProductImageDisk;
=======
use App\Models\Product\Product;          
use App\Models\Product\ProductImage;     
>>>>>>> abbe662 (fix : image upload to database Neon.tech)
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $next = (int) ProductImage::where(
            'product_id',
            $product->id
        )->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $path = $file->store(
                "products/{$product->id}/gallery",
                'neon'
            );

            $product->images()->create([
<<<<<<< HEAD
                'path'       => $file->store("products/{$product->id}/gallery", ProductImageDisk::name()),
=======
                'path'       => $path,
>>>>>>> abbe662 (fix : image upload to database Neon.tech)
                'sort_order' => $next++,
            ]);
        }

        return back()->with(
            'status',
            'Foto produk berhasil diunggah ke Neon.'
        );
    }

    public function destroy(ProductImage $image)
    {
<<<<<<< HEAD
        ProductImageDisk::disk()->delete($productImage->path);
        $productImage->delete();
=======
        if ($image->path) {
            Storage::disk('neon')->delete($image->path);
        }
>>>>>>> abbe662 (fix : image upload to database Neon.tech)

        $image->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    
}

