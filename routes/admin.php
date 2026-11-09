<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,admin_master'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware('role:admin_master')->group(function (): void {
            Route::get('/admins', [UserController::class, 'index'])->name('admins.index');
            Route::get('/admins/create', [UserController::class, 'create'])->name('admins.create');
            Route::post('/admins', [UserController::class, 'store'])->name('admins.store');
            Route::patch('/admins/{user}/deactivate', [UserController::class, 'deactivate'])->name('admins.deactivate');
            Route::patch('/admins/{user}/activate', [UserController::class, 'activate'])->name('admins.activate');
        });
    });
