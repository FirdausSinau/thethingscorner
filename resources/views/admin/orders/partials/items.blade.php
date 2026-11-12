<section class="rounded-2xl border border-border bg-surface p-5">
    <h2 class="font-display text-xl text-primary-dark">Item pesanan</h2>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[34rem] text-left text-sm">
            <thead class="text-xs uppercase tracking-wider text-muted">
                <tr>
                    <th class="py-2 pr-4">Produk</th>
                    <th class="py-2 pr-4">SKU</th>
                    <th class="py-2 pr-4">Warna / ukuran</th>
                    <th class="py-2 pr-4 text-right">Harga</th>
                    <th class="py-2 pr-4 text-right">Qty</th>
                    <th class="py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-t border-border">
                        <td class="py-2 pr-4 font-medium text-ink">{{ $item->product_name }}</td>
                        <td class="py-2 pr-4 text-xs text-muted">{{ $item->sku }}</td>
                        <td class="py-2 pr-4">{{ $item->color }} / {{ $item->size }}</td>
                        <td class="py-2 pr-4 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="py-2 pr-4 text-right">{{ $item->qty }}</td>
                        <td class="py-2 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="mt-4 flex justify-between border-t border-border pt-3 text-sm font-semibold">
        <span>Total</span>
        <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
    </p>
</section>
