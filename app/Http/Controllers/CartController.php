<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request, CartService $cart)
    {
        $summary = $cart->summary($request->user());

        return view('storefront.cart.index', [
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'count' => $summary['count'],
        ]);
    }

    public function store(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($data['variant_id']);
        $cart->add($request->user(), $variant, (int) $data['qty']);

        return back()->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartService $cart, CartItem $cartItem)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $cart->update($request->user(), $cartItem, (int) $data['qty']);

        return back()->with('status', 'Jumlah diperbarui.');
    }

    public function destroy(Request $request, CartService $cart, CartItem $cartItem)
    {
        $cart->remove($request->user(), $cartItem);

        return back()->with('status', 'Item dihapus.');
    }
}