@php
    $buttonGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
    $fileInput = 'text-sm text-muted file:mr-3 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-dark';
@endphp

<section class="mt-8 rounded-2xl border border-border bg-surface p-6">
    <h2 class="font-display text-xl text-primary-dark">Foto produk</h2>
    <p class="mt-1 text-sm text-muted">
        Tampil di kartu katalog dan di halaman detail sebelum pembeli memilih warna. Foto pertama jadi sampul.
    </p>

    <div class="mt-4 flex flex-wrap gap-3">
        @forelse ($product->images as $image)
            <div class="relative">
                @if ($image->url)
                    <img src="{{ $image->url }}" alt="" loading="lazy"
                         class="h-64 w-64 rounded-xl border border-border object-cover">
                @else
                    <div class="flex h-64 w-64 items-center justify-center rounded-xl border border-dashed border-border text-xs text-muted">
                        File foto tidak ditemukan
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.product-images.destroy', $image) }}"
                      class="absolute -right-2 -top-2" onsubmit="return confirm('Hapus foto ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Hapus foto"
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs text-white">&times;</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-muted">Belum ada foto produk.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.products.images.store', $product) }}"
          enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
        @csrf
        <input type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp" class="{{ $fileInput }}">
        <button type="submit" class="{{ $buttonGhost }}">Unggah foto</button>
    </form>
</section>
