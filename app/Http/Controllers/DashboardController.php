<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $products = Product::listed()
            ->latest()
            ->paginate(12);

        return view('storefront.dashboard', compact('products'));
    }
}
