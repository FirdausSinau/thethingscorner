@extends('layouts.admin')

@section('title', 'Pesanan · Admin')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Operasional</p>
        <h1 class="mt-2 font-display text-3xl text-primary-dark">Pesanan</h1>
    </div>

    @include('admin.orders.partials.filters', [
        'statuses' => $statuses,
        'counts' => $counts,
        'orders' => $orders,
        'sumTotal' => $sumTotal,
    ])

    @include('admin.orders.partials.bulk-actions', ['statuses' => $statuses])

    @include('admin.orders.partials.table', ['orders' => $orders])

    <div class="mt-6">{{ $orders->links() }}</div>

    @include('admin.orders.partials.scripts')
@endsection
