<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use Illuminate\Http\Request;   // <- tambahkan ini

class ProductController extends Controller
{
    public function show(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'images', 'colors.images', 'colors.variants']);

        $activeColor = $product->colors->firstWhere('id', (int) $request->query('warna'))
            ?? $product->colors->first();

        $colorChosen = $request->has('warna');

        return view('product.product-show', compact('product', 'activeColor', 'colorChosen'));
    }
}