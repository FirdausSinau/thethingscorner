<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'images', 'colors.images', 'colors.variants']);

        $colorChosen = $request->filled('warna');

        $activeColor = $colorChosen
            ? $product->colors->firstWhere('id', (int) $request->query('warna'))
            : null;

        return view('product.product-show', compact('product', 'activeColor', 'colorChosen'));
    }
}