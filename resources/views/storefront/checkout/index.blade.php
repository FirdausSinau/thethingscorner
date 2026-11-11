@extends('layouts.storefront')

@section('title', 'Checkout · thethingscorner')

@section('content')
    @php
        $fields = [
            'recipient' => 'Nama penerima',
            'phone' => 'Nomor telepon',
            'province' => 'Provinsi',
            'city' => 'Kota/kabupaten',
            'district' => 'Kecamatan',
            'postal_code' => 'Kode pos',
            'detail' => 'Alamat lengkap',
        ];
    @endphp

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

            <div>
                <h2 class="font-display text-xl text-primary-dark">Alamat pengiriman</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($fields as $name => $label)
                        <div>
                            <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" required
                                   value="{{ old($name, $defaultAddress?->$name) }}"
                                   class="mt-1 w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm">
                            @error($name)
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="font-display text-xl text-primary-dark">Metode pembayaran</h2>
                <div class="mt-4 space-y-2">
                    @foreach ($paymentMethods as $method)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="payment_method" value="{{ $method->value }}" required
                                   @checked(old('payment_method', 'manual_transfer') === $method->value)>
                            {{ $method->label() }} (batas bayar {{ $method->reservationMinutes() }} menit)
                        </label>
                    @endforeach
                </div>
                @error('payment_method')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-border bg-surface p-5">
                <h2 class="font-display text-xl text-primary-dark">Ringkasan pesanan</h2>

                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($items as $item)
                        <li class="flex justify-between gap-3">
                            <span>
                                {{ $item->variant->color->product->name }}
                                ({{ $item->variant->color->name }}, ukuran {{ $item->variant->size }}) x{{ $item->qty }}
                            </span>
                            <span>Rp {{ number_format($item->line_total, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-4 flex justify-between border-t border-border pt-3 text-sm font-semibold">
                    <span>Total (belum termasuk ongkir)</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </p>
            </div>

            <button type="submit"
                    class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                Buat pesanan
            </button>
        </form>
    </section>
@endsection