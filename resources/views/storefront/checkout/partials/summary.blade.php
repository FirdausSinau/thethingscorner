<div class="rounded-2xl border border-border bg-surface p-5">
    <h2 class="font-display text-xl text-primary-dark">Ringkasan pesanan</h2>

    <ul class="mt-3 space-y-2 text-sm">
        @foreach ($items as $item)
            <li class="flex justify-between gap-3">
                <span>
                    {{ $item->variant->color->product->name }}
                    ({{ $item->variant->color->name }}, ukuran {{ $item->variant->size }}) x{{ $item->qty }}
                </span>
                <span>Rp {{ number_format($item->line_total, 0, ',', '.') }}</span>
            </li>
        @endforeach
    </ul>

    <p class="mt-4 flex justify-between border-t border-border pt-3 text-sm font-semibold">
        <span>Total (belum termasuk ongkir)</span>
        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
    </p>
</div>
