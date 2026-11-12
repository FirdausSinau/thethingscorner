@php
    $totalStock = $product->colors->flatMap->variants->sum(
        fn ($variant) => $variant->stock_available
    );
@endphp

<article class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-surface shadow-sm transition hover:shadow-md">
    {{-- Foto (bisa diklik) --}}
    <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden">
       @if ($product->thumbnail_url)
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                class="aspect-square w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex aspect-square items-center justify-center bg-gray-100 text-xs text-muted">Foto belum ada</div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs uppercase tracking-[0.18em] text-muted">{{ $product->category->name ?? '-' }}</p>
        <h3 class="mt-1 font-display text-base text-primary-dark">{{ $product->name }}</h3>
        <p class="mt-2 text-sm font-semibold text-primary-dark">
            Rp {{ number_format($product->base_price, 0, ',', '.') }}
        </p>

        <div class="mt-2 flex flex-wrap gap-1.5">
            @if ($product->is_preorder)
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Pre Order</span>
            @elseif ($totalStock > 0)
                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">Ready stock</span>
            @else
                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">Habis</span>
            @endif
        </div>

        <div class="mt-3 flex gap-1.5">
            @foreach ($product->colors as $color)
                <span class="h-4 w-4 rounded-full border border-border"
                      style="background-color: {{ $color->hex_code }}"
                      title="{{ $color->name }}"></span>
            @endforeach
        </div>

        {{-- Tombol detail --}}
        <a href="{{ route('products.show', $product->slug) }}"
        class="mt-5 flex items-center justify-between rounded-full border border-primary-dark/20 px-4 py-2.5
                text-sm font-semibold text-primary-dark transition duration-300
                hover:border-primary-dark hover:bg-primary-dark hover:text-white
                group-hover:border-primary-dark">
            <span>Lihat detail</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                class="h-4 w-4 transition duration-300 group-hover:translate-x-1">
                <path fill-rule="evenodd"
                    d="M3 10a.75.75 0 0 1 .75-.75h10.69l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z"
                    clip-rule="evenodd" />
            </svg>
        </a>
    </div>
</article>