@extends('layouts.storefront')

@section('title', 'Katalog · thethingscorner')

@section('content')
    <section class="mx-auto w-full max-w-6xl px-5 py-10 md:px-8 md:py-14">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-accent">Katalog</p>
                <h1 class="mt-3 font-display text-4xl text-primary-dark md:text-5xl">Sepatu pilihan</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-muted">
                    Cari model, warna, dan ukuran yang tersedia. Produk pre-order tetap bisa dilihat.
                </p>
            </div>

            <form method="GET" action="{{ route('catalog.index') }}" class="flex w-full max-w-md gap-2">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Cari sepatu..."
                       aria-label="Cari produk"
                       class="min-h-11 w-full rounded-xl border border-border bg-surface px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                @if (! empty($filters['category']))
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                <button type="submit"
                        class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    Cari
                </button>
            </form>
        </div>

        <div class="mt-8 flex flex-wrap gap-2">
            <a href="{{ route('catalog.index', array_filter(['q' => $filters['q'] ?? null])) }}"
               class="rounded-full border px-3 py-1.5 text-sm font-medium transition {{ empty($filters['category']) ? 'border-primary-dark bg-primary-dark text-white' : 'border-border bg-surface text-muted hover:border-primary/30 hover:text-primary-dark' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('catalog.index', array_filter(['q' => $filters['q'] ?? null, 'category' => $category->slug])) }}"
                   class="rounded-full border px-3 py-1.5 text-sm font-medium transition {{ ($filters['category'] ?? null) === $category->slug ? 'border-primary-dark bg-primary-dark text-white' : 'border-border bg-surface text-muted hover:border-primary/30 hover:text-primary-dark' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
            @forelse ($products as $product)
                @include('product.partials.product-card', ['product' => $product])
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-border bg-surface px-6 py-12 text-center text-sm text-muted">
                    Tidak ada produk yang cocok dengan pencarian.
                </p>
            @endforelse
        </div>

        <div class="mt-10">{{ $products->links() }}</div>
    </section>
@endsection

