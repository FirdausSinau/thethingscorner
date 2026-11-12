@extends('layouts.storefront')

@section('title', 'Checkout · Thethingscorner')

@section('content')
    <section class="mx-auto w-full max-w-4xl px-5 py-10 md:px-8">
        <h1 class="font-display text-3xl text-primary-dark">Checkout</h1>

        @error('cart')
            <div class="mt-6 rounded-xl border border-danger/20 bg-danger/5 px-4 py-3 text-sm text-danger">
                <p>{{ $message }}</p>
                <a href="{{ route('cart.index') }}" class="font-semibold underline">Kembali ke keranjang</a>
            </div>
        @enderror

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 space-y-8">
            @csrf

            @include('storefront.checkout.partials.address-form', ['defaultAddress' => $defaultAddress])
            @include('storefront.checkout.partials.payment-methods', ['paymentMethods' => $paymentMethods])
            @include('storefront.checkout.partials.summary', ['items' => $items, 'subtotal' => $subtotal])

            <button type="submit"
                    class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                Buat pesanan
            </button>
        </form>
    </section>
@endsection
