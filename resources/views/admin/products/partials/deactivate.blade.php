@if ($product->is_active)
    <section class="mt-8 rounded-2xl border border-red-200 bg-red-50/50 p-6">
        <h2 class="font-display text-lg text-red-700">Nonaktifkan produk</h2>
        <p class="mt-1 text-sm text-muted">
            Produk tidak tampil lagi di katalog dan tidak bisa dibeli. Warna, ukuran, foto, dan riwayat pesanan tetap tersimpan.
            Produk bisa diaktifkan kembali lewat centang "Tampilkan di katalog" di info produk.
        </p>

        <form method="POST" action="{{ route('admin.products.deactivate', $product) }}" class="mt-3"
              onsubmit="return confirm(@js('Nonaktifkan produk ' . $product->name . '?'))">
            @csrf
            @method('PATCH')
            <button type="submit" class="rounded-full border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                Nonaktifkan produk
            </button>
        </form>
    </section>
@endif
