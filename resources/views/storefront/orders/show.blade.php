@extends('layouts.storefront')
    @php
        $btnGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
    @endphp

@section('title', 'Pesanan ' . $order->order_code . ' · Thethingscorner')



@section('content')
    <section class="mx-auto w-full max-w-3xl px-5 py-10 md:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="font-display text-3xl text-primary-dark">
                    {{ $order->order_code }}
                </h1>
                <p class="mt-2 text-sm text-muted">
                    {{ $order->status->label() }} · {{ $order->payment_method->label() }}
                </p>
            </div>

            @can('manage-orders')
                <div>
                    <a href="{{ route('admin.orders.index') }}" class="{{ $btnGhost }} justify-between">← Kembali Ke Pesanan</a>
                </div>
            @else
                <div>
                    <a href="{{ route('catalog.index') }}" class="{{ $btnGhost }} justify-between">← Kembali Halaman Utama</a>
                </div>
            @endcan
        </div>

        @if ($order->reservation_expires_at)
            <p class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
                Bayar sebelum <strong>{{ $order->reservation_expires_at->format('d/m/Y H:i') }}</strong>.
                Setelah itu pesanan kedaluwarsa.
            </p>
        @endif

        <div class="mt-6 rounded-2xl border border-border bg-surface p-5">
            <ul class="space-y-3 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-3">
                        <span>
                            {{ $item->product_name }} ({{ $item->color }}, ukuran {{ $item->size }})
                            · {{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                        </span>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4 flex justify-between border-t border-border pt-3 text-sm font-semibold">
                <span>Total</span>
                <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </p>
        </div>

        <div class="mt-6 rounded-2xl border border-border bg-surface p-5 text-sm">
            <p class="font-semibold">Dikirim ke</p>
            <p class="mt-2">{{ $order->recipient }} · {{ $order->phone }}</p>
            <p class="text-muted">{{ $order->address }}</p>
        </div>

        <p class="mt-6 text-xs text-muted">Instruksi pembayaran akan tersedia di tahap berikutnya.</p>
    </section>
@endsection