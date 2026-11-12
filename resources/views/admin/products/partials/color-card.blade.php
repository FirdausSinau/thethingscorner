@php
    $input = 'mt-1 rounded-lg border border-border bg-bg px-3 py-1.5 text-sm text-ink focus:border-primary focus:outline-none';
    $button = 'rounded-full bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90';
    $buttonGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
    $fileInput = 'text-sm text-muted file:mr-3 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-dark';
    $colorInUse = $color->variants->contains(
        fn ($variant): bool => ($variant->order_items_count ?? 0) > 0 || (int) $variant->stock_reserved > 0
    );
@endphp

<div class="mt-4 rounded-2xl border border-border bg-surface p-6">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="h-5 w-5 rounded-full border border-border"
                  style="background: {{ $color->hex_code ?? '#ffffff' }}"></span>
            <h3 class="font-display text-lg text-primary-dark">{{ $color->name }}</h3>
        </div>

        @if ($colorInUse)
            <span class="text-xs text-muted" title="Warna sudah dipakai di pesanan atau stoknya sedang ditahan">
                Sudah dipakai
            </span>
        @else
            <form method="POST" action="{{ route('admin.colors.destroy', $color) }}"
                  onsubmit="return confirm(@js('Hapus warna ' . $color->name . ' beserta semua ukuran dan fotonya?'))">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Hapus warna</button>
            </form>
        @endif
    </div>

    <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-muted">Foto (yang pertama jadi sampul)</p>
    <div class="mt-2 flex flex-wrap gap-3">
        @forelse ($color->images as $image)
            <div class="relative">
                @if ($image->url)
                    <img src="{{ $image->url }}" alt="" loading="lazy"
                         class="h-64 w-64 rounded-xl border border-border object-cover">
                @else
                    <div class="flex h-64 w-64 items-center justify-center rounded-xl border border-dashed border-border text-xs text-muted">
                        File foto tidak ditemukan
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.images.destroy', $image) }}"
                      class="absolute -right-2 -top-2" onsubmit="return confirm('Hapus foto ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Hapus foto"
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs text-white">&times;</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-muted">Belum ada foto.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.colors.images.store', $color) }}"
          enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
        @csrf
        <input type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp" class="{{ $fileInput }}">
        <button type="submit" class="{{ $buttonGhost }}">Unggah foto</button>
    </form>

    <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-muted">Ukuran &amp; stok</p>

    @if ($color->variants->isNotEmpty())
        <form method="POST" action="{{ route('admin.colors.variants.update', $color) }}" class="mt-2">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-muted">
                        <tr>
                            <th class="py-2 pr-4">Ukuran</th>
                            <th class="py-2 pr-4">SKU</th>
                            <th class="py-2 pr-4">Stok fisik</th>
                            <th class="py-2 pr-4">Ditahan</th>
                            <th class="py-2 pr-4">Tersedia</th>
                            <th class="py-2 pr-4">Harga khusus</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($color->variants->sortBy('size', SORT_NATURAL) as $variant)
                            @php
                                $reserved = (int) $variant->stock_reserved;
                                $available = max(0, (int) $variant->stock - $reserved);
                                $inUse = ($variant->order_items_count ?? 0) > 0 || $reserved > 0;
                            @endphp

                            <tr class="border-t border-border">
                                <td class="py-2 pr-4 font-medium text-ink">{{ $variant->size }}</td>
                                <td class="py-2 pr-4 text-xs text-muted">{{ $variant->sku }}</td>
                                <td class="py-2 pr-4">
                                    <input type="number" required min="{{ $reserved }}"
                                           name="variants[{{ $variant->id }}][stock]"
                                           value="{{ old("variants.{$variant->id}.stock", $variant->stock) }}"
                                           class="w-24 {{ $input }}">
                                    <input type="hidden" name="variants[{{ $variant->id }}][seen]" value="{{ $variant->stock }}">
                                </td>
                                <td class="py-2 pr-4 {{ $reserved > 0 ? 'font-medium text-amber-700' : 'text-muted' }}">
                                    {{ $reserved }}
                                </td>
                                <td class="py-2 pr-4 font-semibold {{ $available <= 0 ? 'text-red-600' : ($available <= 3 ? 'text-amber-700' : 'text-ink') }}">
                                    {{ $available }}
                                </td>
                                <td class="py-2 pr-4">
                                    <input type="number" min="0"
                                           name="variants[{{ $variant->id }}][price]"
                                           value="{{ old("variants.{$variant->id}.price", $variant->price) }}"
                                           placeholder="{{ $product->base_price }}"
                                           class="w-32 {{ $input }}">
                                </td>
                                <td class="py-2 text-right">
                                    @if ($inUse)
                                        <span class="text-xs text-muted" title="Sudah dipakai, tidak bisa dihapus">Dipakai</span>
                                    @else
                                        <button type="submit" form="del-variant-{{ $variant->id }}"
                                                class="text-xs font-medium text-red-600 hover:underline">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-2 text-xs text-muted">
                Stok fisik tidak bisa diisi di bawah jumlah yang sedang ditahan pesanan. Kosongkan harga khusus untuk memakai harga dasar produk.
            </p>
            <button type="submit" class="mt-3 {{ $button }}">Simpan stok</button>
        </form>

        @foreach ($color->variants as $variant)
            @continue(($variant->order_items_count ?? 0) > 0 || (int) $variant->stock_reserved > 0)
            <form id="del-variant-{{ $variant->id }}" method="POST" class="hidden"
                  action="{{ route('admin.variants.destroy', $variant) }}"
                  onsubmit="return confirm(@js('Hapus ukuran ' . $variant->size . '?'))">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @else
        <p class="mt-2 text-sm text-muted">Belum ada ukuran.</p>
    @endif

    <form method="POST" action="{{ route('admin.colors.variants.store', $color) }}"
          class="mt-4 flex flex-wrap items-end gap-3">
        @csrf
        <label class="text-xs text-muted">Ukuran
            <input type="text" name="size" required maxlength="10" placeholder="ex: 40" class="block w-24 {{ $input }}">
        </label>
        <label class="text-xs text-muted">Stok
            <input type="number" name="stock" min="0" value="0" required class="block w-24 {{ $input }}">
        </label>
        <label class="text-xs text-muted">Harga khusus (opsional)
            <input type="number" name="price" min="0" placeholder="ex: 200000" class="block w-32 {{ $input }}">
        </label>
        <button type="submit" class="{{ $buttonGhost }}">+ Tambah ukuran</button>
    </form>
</div>
