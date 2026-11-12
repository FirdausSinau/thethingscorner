@extends('layouts.storefront')

@section('title', $product->name . ' · Thethingscorner')

@section('content')
    @php
        $colorImages = $activeColor?->images ?? collect();

        $images = ($colorChosen && $colorImages->isNotEmpty())
            ? $colorImages
            : ($product->images->isNotEmpty() ? $product->images : $colorImages);

        $mainImage = $images->firstWhere('id', (int) request('foto')) ?? $images->first();
        $variants = ($activeColor?->variants ?? collect())->sortBy(fn ($variant) => (int) $variant->size);
    @endphp

    <section class="mx-auto w-full max-w-6xl px-5 py-10 md:px-8 md:py-14">
        <a href="{{ route('catalog.index') }}"
           class="inline-flex rounded-full px-3 py-2 font-medium text-primary-dark transition hover:bg-primary/5">
            &larr; Kembali ke katalog
        </a>

        <div class="mt-6 grid gap-8 md:grid-cols-2 md:gap-12">
            @include('product.partials.gallery', [
                'product' => $product,
                'images' => $images,
                'mainImage' => $mainImage,
                'activeColor' => $activeColor,
            ])

            @include('product.partials.info', [
                'product' => $product,
                'activeColor' => $activeColor,
                'colorChosen' => $colorChosen,
                'variants' => $variants,
            ])
        </div>
    </section>
@endsection
