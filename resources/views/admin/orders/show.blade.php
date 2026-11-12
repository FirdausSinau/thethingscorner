@extends('layouts.admin')

@section('title', 'Pesanan ' . $order->order_code . ' · Admin')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-muted hover:text-primary-dark">&larr; Semua pesanan</a>

            <h1 class="mt-2 font-display text-3xl text-primary-dark">{{ $order->order_code }}</h1>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-muted">
                <x-order-status-badge :status="$order->status" />
                <span>{{ $order->payment_method->label() }}</span>
                <span>·</span>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                <a href="{{ route('orders.show', ['order' => $order->order_code]) }}"
                   class="font-medium text-primary hover:text-primary-dark">Lihat halaman pembeli &rarr;</a>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-surface px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-muted">Ubah status</p>
            <div class="mt-2">
                @include('admin.orders.partials.status-form', ['order' => $order])
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @include('admin.orders.partials.summary', ['order' => $order])
        @include('admin.orders.partials.items', ['order' => $order])
    </div>

    @include('admin.orders.partials.status-history', ['order' => $order])
@endsection
