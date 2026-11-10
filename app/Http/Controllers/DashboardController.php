<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Product\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::listed()
            ->with(['category', 'images', 'colors.images', 'colors.variants'])
            ->latest()
            ->paginate(12);

        return view('storefront.dashboard', compact('products'));
    }
}
