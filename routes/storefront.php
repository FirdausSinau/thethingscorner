<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::get('/produk', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/produk/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show');
