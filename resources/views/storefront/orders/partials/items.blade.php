<div class="mt-6 rounded-2xl border border-border bg-surface p-5">
    <ul class="space-y-3 text-sm">
        @foreach ($order->items as $item)
            <li class="flex justify-between gap-3">
                <span>
                    {{ $item->product_name }} ({{ $item->color }}, ukuran {{ $item->size }})
                    · {{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                </span>
                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </li>
        @endforeach
    </ul>

    <p class="mt-4 flex justify-between border-t border-border pt-3 text-sm font-semibold">
        <span>Total</span>
        <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
    </p>
</div>
