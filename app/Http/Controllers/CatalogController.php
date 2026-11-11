<?php

namespace App\Http\Controllers;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $products = Product::listed()
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->whereLike('name', "%{$term}%")
                        ->orWhereLike('description', "%{$term}%");
                });
            })
            ->when($filters['category'] ?? null, function ($query, string $category): void {
                $query->whereHas('category', fn ($query) => $query->where('slug', $category));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('product.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}
