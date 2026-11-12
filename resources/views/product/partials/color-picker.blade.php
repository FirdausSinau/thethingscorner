<div class="mt-8">
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Pilihan warna</p>

    <div class="mt-3 flex gap-3">
        @foreach ($product->colors as $color)
            <a href="{{ route('products.show', [$product->slug, 'warna' => $color->id]) }}"
               title="{{ $color->name }}"
               aria-label="Pilih warna {{ $color->name }}"
               class="h-8 w-8 rounded-full shadow-sm transition hover:scale-110
                      {{ $colorChosen && $color->id === $activeColor?->id
                          ? 'border-2 border-primary-dark ring-2 ring-offset-2'
                          : 'border-0' }}"
               style="background-color: {{ $color->hex_code }}"></a>
        @endforeach
    </div>
</div>
