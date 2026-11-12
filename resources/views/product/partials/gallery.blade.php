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
            @foreach ($images as $image)
                @if ($image->url)
                    <a href="{{ route('products.show', [$product->slug, 'warna' => $activeColor?->id, 'foto' => $image->id]) }}">
                        <img src="{{ $image->url }}" alt="{{ $product->name }}"
                             class="h-16 w-16 rounded-lg border border-border object-cover">
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
