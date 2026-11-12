@extends('layouts.admin')

@section('title', 'Produk & stok · Admin')

@section('content')
    @php
        $buttonGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
        $available = fn ($variant): int => max(0, (int) $variant->stock - (int) $variant->stock_reserved);
        $stockClass = fn (int $stock): string => match (true) {
            $stock <= 0 => 'bg-red-50 text-red-700 font-semibold',
            $stock <= 3 => 'bg-amber-50 text-amber-800 font-semibold',
            default => 'text-ink',
        };
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Katalog</p>
            <h1 class="mt-2 font-display text-3xl text-primary-dark">Produk &amp; stok</h1>
        </div>
        <a href="{{ route('admin.products.create') }}" class="{{ $buttonGhost }}">+ Tambah produk</a>
    </div>

    @include('admin.products.partials.legend')

    <div class="mt-3 overflow-x-auto rounded-2xl border border-border bg-surface">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead class="border-b border-border text-xs uppercase tracking-wider text-muted">
                <tr>
                    <th class="w-48 px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Total stok</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>

            @forelse ($products as $product)
                @include('admin.products.partials.row', [
                    'product' => $product,
                    'available' => $available,
                    'stockClass' => $stockClass,
                    'buttonGhost' => $buttonGhost,
                ])
            @empty
                <tbody>
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-muted">Belum ada produk.</td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection
