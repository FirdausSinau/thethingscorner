<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index(Request $request, CartService $cart)
    {
        $user = $request->user();
        $summary = $cart->summary($user);

        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Keranjang masih kosong.');
        }

        return view('storefront.checkout.index', [
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'count' => $summary['count'],
            'defaultAddress' => $user->addresses()->where('is_default', true)->first(),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function store(Request $request, CheckoutService $checkout, CartService $cart)
    {
        $user = $request->user();

        if ($cart->summary($user)['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Keranjang masih kosong.');
        }

        $data = $request->validate([
            'recipient' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'detail' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);

        $order = DB::transaction(function () use ($user, $data, $checkout) {
            $fields = collect($data)->except('payment_method')->all();
            $address = $user->addresses()->where('is_default', true)->first();

            if ($address) {
                $address->update($fields);
            } else {
                $address = $user->addresses()->create($fields + ['is_default' => true]);
            }

            return $checkout->placeOrder($user, $address, PaymentMethod::from($data['payment_method']));
        });

        return redirect()
            ->route('orders.show', ['order' => $order->order_code])
            ->with('status', 'Pesanan berhasil dibuat.');
    }
}