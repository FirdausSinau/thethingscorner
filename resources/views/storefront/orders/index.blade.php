@extends('layouts.storefront')

@section('title', 'Pesanan saya · Thethingscorner')

@section('content')
    <section class="mx-auto w-full max-w-3xl px-5 py-10 md:px-8">
        <h1 class="font-display text-3xl text-primary-dark">Pesanan saya</h1>

        @if ($orders->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-border bg-surface px-6 py-12 text-center">
                <p class="font-display text-xl text-primary-dark">Belum ada pesanan</p>
                <a href="{{ route('catalog.index') }}"
                   class="mt-5 inline-flex rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                    Mulai belanja
                </a>
            </div>
        @else
            <ul class="mt-8 space-y-4">
                @foreach ($orders as $order)
                    <li>
                        <a href="{{ route('orders.show', ['order' => $order->order_code]) }}"
                           class="block rounded-2xl border border-border bg-surface p-5 hover:border-primary/30">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="font-semibold text-primary-dark">{{ $order->order_code }}</p>
                                <x-order-status-badge :status="$order->status" />
                            </div>

                            <p class="mt-1 text-xs text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</p>

                            <p class="mt-3 text-sm font-semibold">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection