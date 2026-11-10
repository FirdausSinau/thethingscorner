<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductColor;
use App\Models\Product\ProductColorImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductColorImageController extends Controller
{
    public function store(Request $request, ProductColor $color)
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],   // maks 2 MB per foto
        ]);

        // Query langsung ke model (bukan lewat relasi yang sudah di-orderBy):
        // di PostgreSQL, max() dengan ORDER BY menimbulkan error GROUP BY.
        $next = (int) ProductColorImage::where('product_color_id', $color->id)->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $path = $file->store("products/{$color->product_id}", 's3');

            $color->images()->create([
                'path'       => $path,
                'sort_order' => $next++,
            ]);
        }

        return back()->with('status', 'Foto diunggah. Foto pertama jadi sampul.');
    }

    public function destroy(ProductColorImage $image)
    {
        Storage::disk('s3')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Foto dihapus.');
    }
}