<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductColor;
use App\Models\Product\ProductColorImage;
use App\Support\ProductImageDisk;
use Illuminate\Http\Request;

class ProductColorImageController extends Controller
{
    public function store(Request $request, ProductColor $color)
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $next = (int) ProductColorImage::where(
            'product_color_id',
            $color->id
        )->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $path = $file->store("products/{$color->product_id}", ProductImageDisk::name());

            $color->images()->create([
                'path'       => $path,
                'sort_order' => $next++,
            ]);
        }

        return back()->with('status', 'Foto berhasil diunggah ke Neon.');
    }


    public function destroy(ProductColorImage $image)
    {
        ProductImageDisk::disk()->delete($image->path);
        $image->delete();

        return back()->with('status', 'Foto berhasil dihapus.');
    }
}

