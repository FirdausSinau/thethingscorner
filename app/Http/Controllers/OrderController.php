<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
<<<<<<< Updated upstream
    public function show(Request $request, Order $order)
=======
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->latest()->paginate(10);

        return view('storefront.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order)
>>>>>>> Stashed changes
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['items', 'reservations']);

        return view('storefront.orders.show', ['order' => $order]);
    }
}