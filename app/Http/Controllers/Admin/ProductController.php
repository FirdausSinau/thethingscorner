<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Support\Str;

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
        $product->load('colors.images', 'colors.variants');

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

    public function deactivate(Product $product)
    {
        $product->update(['is_active' => false]);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk dinonaktifkan. Riwayat pesanan tetap aman.');
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
