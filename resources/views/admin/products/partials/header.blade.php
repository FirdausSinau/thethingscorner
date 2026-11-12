@php
    $buttonGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
@endphp

<a href="{{ route('admin.products.index') }}" class="{{ $buttonGhost }} inline-flex">&larr; Semua produk</a>

<div class="mt-3 flex flex-wrap items-end justify-between gap-3">
    <h1 class="font-display text-3xl text-primary-dark">Kelola produk: {{ $product->name }}</h1>
    <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="{{ $buttonGhost }}">
        Lihat di katalog &nearr;
    </a>
</div>
