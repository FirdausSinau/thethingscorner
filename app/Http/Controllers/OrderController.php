<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        
        Gate::authorize('view', $order);

        $order->load(['items', 'reservations']);

        return view('storefront.orders.show', ['order' => $order]);
    }
}
