<div>
    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">
        {{ $product->category?->name ?? '-' }}
    </p>

    <h1 class="mt-2 font-display text-3xl text-primary-dark md:text-4xl">{{ $product->name }}</h1>

    <p class="mt-3 font-display text-2xl text-primary-dark">
        Rp {{ number_format($product->base_price, 0, ',', '.') }}
    </p>

    @if ($product->is_preorder)
        <span class="mt-3 inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
            Pre Order
        </span>
    @endif

    @if ($product->description)
        <p class="mt-5 text-sm leading-7 text-muted">{{ $product->description }}</p>
    @endif

    @include('product.partials.color-picker', [
        'product' => $product,
        'activeColor' => $activeColor,
        'colorChosen' => $colorChosen,
    ])

    @include('product.partials.size-picker', [
        'product' => $product,
        'variants' => $variants,
        'activeColor' => $activeColor,
        'colorChosen' => $colorChosen,
    ])
</div>
