<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductColorController;
use App\Http\Controllers\Admin\ProductColorImageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,admin_master'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except(['show', 'destroy']);
        Route::patch('products/{product}/deactivate', [ProductController::class, 'deactivate'])->name('products.deactivate');

        Route::post('products/{product}/images', [ProductImageController::class, 'store'])
            ->name('products.images.store');
        Route::delete('product-images/{image}', [ProductImageController::class, 'destroy'])
            ->name('product-images.destroy');

        Route::post('products/{product}/colors', [ProductColorController::class, 'store'])
            ->name('products.colors.store');
        Route::delete('colors/{color}', [ProductColorController::class, 'destroy'])
            ->name('colors.destroy');

        Route::post('colors/{color}/images', [ProductColorImageController::class, 'store'])
            ->name('colors.images.store');
        Route::delete('images/{image}', [ProductColorImageController::class, 'destroy'])
            ->name('images.destroy');

        Route::post('colors/{color}/variants', [ProductVariantController::class, 'store'])
            ->name('colors.variants.store');
        Route::put('colors/{color}/variants', [ProductVariantController::class, 'updateMany'])
            ->name('colors.variants.update');
        Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy'])
            ->name('variants.destroy');


        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::patch('orders/bulk-status', [AdminOrderController::class, 'bulkStatus'])->name('orders.bulk-status');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        Route::middleware('role:admin_master')->group(function (): void {
            Route::get('/admins', [UserController::class, 'index'])->name('admins.index');
            Route::get('/admins/create', [UserController::class, 'create'])->name('admins.create');
            Route::post('/admins', [UserController::class, 'store'])->name('admins.store');
            Route::patch('/admins/{user}/deactivate', [UserController::class, 'deactivate'])->name('admins.deactivate');
            Route::patch('/admins/{user}/activate', [UserController::class, 'activate'])->name('admins.activate');
        });
    });