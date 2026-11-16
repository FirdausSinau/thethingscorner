<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use App\Http\Controllers\Webhook\ShippingWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/webhooks/payment/{provider}', [PaymentWebhookController::class, 'handle'])
    ->name('webhooks.payment');
Route::post('/webhooks/shipping/{provider}', [ShippingWebhookController::class, 'handle'])
    ->name('webhooks.shipping');

require __DIR__.'/auth.php';
require __DIR__.'/storefront.php';
require __DIR__.'/admin.php';
