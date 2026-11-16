<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('storefront.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['items', 'reservations', 'latestPayment', 'latestShipment', 'statusLogs.actor']);

        return view('storefront.orders.show', ['order' => $order]);
    }
}
