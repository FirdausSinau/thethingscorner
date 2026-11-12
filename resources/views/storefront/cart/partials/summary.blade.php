<div class="mt-6 rounded-2xl border border-border bg-surface p-5">
    <p class="flex justify-between text-sm">
        <span class="text-muted">Subtotal ({{ $count }} barang)</span>
        <span class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
    </p>

    <p class="mt-3 text-xs text-muted">Stok belum dikunci sampai checkout.</p>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('checkout.index') }}"
           class="inline-flex rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
            Checkout
        </a>

        <form method="POST" action="{{ route('cart.clear') }}"
              onsubmit="return confirm('Kosongkan seluruh keranjang?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="rounded-full border border-border px-4 py-2 text-sm font-medium text-muted hover:border-danger/30 hover:text-danger">
                Kosongkan keranjang
            </button>
        </form>
    </div>
</div>
