<li class="rounded-2xl border border-border bg-surface p-4">
    <p class="font-semibold text-primary-dark">{{ $item->variant->color->product->name }}</p>
    <p class="text-xs text-muted">
        {{ $item->variant->color->name }} · Ukuran {{ $item->variant->size }} · SKU {{ $item->variant->sku }}
    </p>
    <p class="mt-1 text-sm">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>

    <div class="mt-3 flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')

            <label for="qty-{{ $item->id }}" class="sr-only">Jumlah</label>
            <input id="qty-{{ $item->id }}" type="number" name="qty" value="{{ $item->qty }}" min="1"
                   max="{{ $item->variant->stock_available }}"
                   class="w-16 rounded-lg border border-border px-2 py-2 text-center text-sm">

            <button type="submit" class="rounded-lg border border-border px-3 py-2 text-sm">Ubah</button>
        </form>

        <p class="text-sm font-semibold text-primary-dark">
            Rp {{ number_format($item->line_total, 0, ',', '.') }}
        </p>

        <form method="POST" action="{{ route('cart.destroy', $item) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-danger hover:underline">Hapus</button>
        </form>
    </div>
</li>
