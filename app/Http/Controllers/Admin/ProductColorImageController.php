<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductColor;
use App\Models\Product\ProductColorImage;
use App\Support\ProductImageDisk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductColorImageController extends Controller
{
    public function store(Request $request, ProductColor $color): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $next = (int) ProductColorImage::query()
            ->where('product_color_id', $color->id)
            ->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $color->images()->create([
                'path' => $file->store(
                    "products/{$color->product_id}/gallery/product-color",
                    ProductImageDisk::name()
                ),
                'sort_order' => $next++,
            ]);
        }

        return back()->with('status', 'Foto warna berhasil diunggah.');
    }

    public function destroy(ProductColorImage $image): RedirectResponse
    {
        if ($image->path) {
            ProductImageDisk::disk()->delete($image->path);
        }

        $image->delete();

        return back()->with('status', 'Foto berhasil dihapus.');
    }
}
