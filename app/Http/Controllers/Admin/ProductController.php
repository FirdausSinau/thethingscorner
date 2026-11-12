<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductColorImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\OrderItem;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'colors.variants'])
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $this->payload($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        $product = Product::create($data);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Produk dibuat. Sekarang tambahkan warna, ukuran, dan foto.');
    }

    public function edit(Product $product)
    {
        $product->load([
            'images',
            'colors.images',
            'colors.variants' => fn ($q) => $q->withCount('orderItems'),
        ]);

        return view('admin.products.edit', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        // slug sengaja tidak diubah supaya URL produk stabil
        $product->update($this->payload($request));

        return back()->with('status', 'Info produk disimpan.');
    }

    public function destroy(Product $product)
    {
        $product->load(['images', 'colors.images', 'colors.variants']);

        $variantIds = $product->colors->flatMap->variants->pluck('id');

        if (OrderItem::whereIn('variant_id', $variantIds)->exists()
            || StockReservation::whereIn('variant_id', $variantIds)->exists()) {
            return back()->withErrors([
                'delete' => 'Produk ini sudah pernah dipesan, jadi tidak bisa dihapus. Nonaktifkan lewat "Tampilkan di katalog".',
            ]);
        }

        $paths = $product->images->pluck('path')
            ->merge($product->colors->flatMap->images->pluck('path'))
            ->all();

        DB::transaction(fn () => $product->delete());

        // file dihapus SETELAH data berhasil dihapus
        Storage::disk('s3')->delete($paths);

        return redirect()->route('admin.products.index')->with('status', 'Produk dihapus.');
    }

    private function payload(ProductRequest $request): array
    {
        $data = $request->validated();
        $data['weight_grams'] = (int) ($data['weight_grams'] ?? 0);
        $data['is_active']    = $request->boolean('is_active');
        $data['is_preorder']  = $request->boolean('is_preorder');

        if (! $data['is_preorder']) {
            $data['preorder_close_date'] = null;
            $data['estimated_ready_date'] = null;
        }

        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = $slug = Str::slug($name);
        $i = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
