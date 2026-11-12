@extends('layouts.storefront')

@section('title', $product->name . ' · Thethingscorner')

@section('content')
    @php
        $colorImages = $activeColor?->images ?? collect();

        $images = ($colorChosen && $colorImages->isNotEmpty())
            ? $colorImages
            : ($product->images->isNotEmpty() ? $product->images : $colorImages);

        $mainImage = $images->firstWhere('id', (int) request('foto')) ?? $images->first();
        $variants = ($activeColor?->variants ?? collect())->sortBy(fn ($v) => (int) $v->size);
    @endphp

    <section class="mx-auto w-full max-w-6xl px-5 py-10 md:px-8 md:py-14">
        <a href="{{ route('catalog.index') }}" class="inline-flex rounded-full px-3 py-2 font-medium text-primary-dark transition hover:bg-primary/5">&larr; Kembali ke katalog</a>

        <div class="mt-6 grid gap-8 md:grid-cols-2 md:gap-12">
            {{-- Foto --}}
            <div>
                <div class="aspect-square overflow-hidden rounded-2xl border border-border bg-surface">
                    @if ($mainImage?->url)
                        <img src="{{ $mainImage->url }}" alt="{{ $product->name }}"
                             class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-sm text-muted">Foto belum ada</div>
                    @endif
                </div>

                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($images as $img)
                            @if ($img->url)
                                <a href="{{ route('products.show', [$product->slug, 'warna' => $activeColor?->id, 'foto' => $img->id]) }}">
                                    <img src="{{ $img->url }}"
                                         class="h-16 w-16 rounded-lg border border-border object-cover">
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">{{ $product->category->name ?? '-' }}</p>
                <h1 class="mt-2 font-display text-3xl text-primary-dark md:text-4xl">{{ $product->name }}</h1>
                <p class="mt-3 font-display text-2xl text-primary-dark">
                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                </p>

                @if ($product->is_preorder)
                    <span class="mt-3 inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">Pre Order</span>
                @endif

                @if ($product->description)
                    <p class="mt-5 text-sm leading-7 text-muted">{{ $product->description }}</p>
                @endif

                {{-- Warna --}}
                <div class="mt-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">
                        Pilihan Warna:
                    </p>

                    <div class="mt-3 flex gap-3">
                        @foreach ($product->colors as $color)
                            <a href="{{ route('products.show', [$product->slug, 'warna' => $color->id]) }}"
                            title="{{ $color->name }}"
                            class="h-8 w-8 rounded-full shadow-sm transition hover:scale-110
                                    {{ $colorChosen && $color->id === $activeColor?->id
                                        ? 'border-2 border-primary-dark ring-2 ring-offset-2'
                                        : 'border-0' }}"
                            style="background-color: {{ $color->hex_code }}">
                            </a>
                        @endforeach
                    </div>
                </div>
              

                {{-- Ukuran --}}
                @include('product.product-show-size', ['variants' => $variants, 'activeColor' => $activeColor])
            </div>
        </div>
    </section>
@endsection

