@extends('layouts.storefront')

@section('title', 'Pesanan ' . $order->order_code . ' · Thethingscorner')

@section('content')
    @php
        $buttonGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
    @endphp

    <section class="mx-auto w-full max-w-3xl px-5 py-10 md:px-8">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="font-display text-3xl text-primary-dark">{{ $order->order_code }}</h1>

                @include('storefront.orders.partials.status', ['order' => $order])
            </div>

            @can('manage-orders')
                <a href="{{ route('admin.orders.show', $order) }}" class="{{ $buttonGhost }}">
                    &larr; Kembali ke pesanan admin
                </a>
            @else
                <a href="{{ route('catalog.index') }}" class="{{ $buttonGhost }}">
                    &larr; Kembali ke katalog
                </a>
            @endcan
        </div>

        @include('storefront.orders.partials.items', ['order' => $order])
        @include('storefront.orders.partials.shipping', ['order' => $order])

        <p class="mt-6 text-xs text-muted">Instruksi pembayaran akan tersedia di tahap berikutnya.</p>
    </section>
@endsection
