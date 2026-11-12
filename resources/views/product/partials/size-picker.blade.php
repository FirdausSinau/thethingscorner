<form method="POST" action="{{ route('cart.store') }}" class="mt-6">
    @csrf

    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">
        Ukuran {{ $activeColor ? '· '.$activeColor->name : '' }}
    </p>

    @if (! $colorChosen)
        <p class="mt-3 text-sm text-muted">Pilih warna yang tersedia terlebih dahulu.</p>
    @elseif ($variants->isEmpty())
        <p class="mt-3 text-sm text-muted">Ukuran untuk warna ini belum tersedia.</p>
    @else
        @php($hasStock = $variants->contains(fn ($variant) => $variant->stock_available > 0))

        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($variants as $variant)
                <label>
                    <input type="radio" name="variant_id" value="{{ $variant->id }}" class="peer sr-only" required
                           @disabled($variant->stock_available < 1)>
                    <span class="block min-w-12 cursor-pointer rounded-lg border border-border px-3 py-2 text-center text-sm peer-checked:border-primary-dark peer-checked:bg-primary-dark peer-checked:text-white peer-disabled:cursor-not-allowed peer-disabled:line-through peer-disabled:opacity-40">
                        {{ $variant->size }}
                    </span>
                </label>
            @endforeach
        </div>

        <input type="hidden" name="qty" value="1">

        @error('variant_id')
            <p class="mt-3 text-sm text-danger">{{ $message }}</p>
        @enderror
        @error('qty')
            <p class="mt-3 text-sm text-danger">{{ $message }}</p>
        @enderror

        @if ($product->is_preorder)
            <p class="mt-6 text-sm text-muted">
                Produk ini dipesan lewat pengajuan PO. Form PO tersedia di tahap berikutnya.
            </p>
        @elseif ($hasStock)
            @auth
                <button type="submit"
                        class="mt-6 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                    Tambah ke keranjang
                </button>
            @else
                <a href="{{ route('login') }}"
                   class="mt-6 inline-flex rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                    Masuk untuk menambah ke keranjang
                </a>
            @endauth
        @else
            <p class="mt-6 text-sm text-muted">Stok warna ini sedang habis.</p>
        @endif
    @endif
</form>
