@extends('layouts.admin')

@section('title', 'Kelola ' . $product->name . ' · Admin')

@section('content')
    @php
        $input = 'mt-1 rounded-lg border border-border bg-bg px-3 py-1.5 text-sm text-ink focus:border-primary focus:outline-none';
        $btn = 'rounded-full bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90';
        $btnGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
    @endphp

    <a href="{{ route('admin.products.index') }}" class="{{ $btnGhost }} justify-between">← Semua produk</a>

    <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
        <h1 class="font-display text-3xl text-primary-dark">Kelola Produk : {{ $product->name }}</h1>
        <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="{{ $btnGhost }}">Lihat di katalog ↗</a>
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Info produk --}}
    <section class="mt-6 rounded-2xl border border-border bg-surface p-6">
        <h2 class="font-display text-xl text-primary-dark">Info produk</h2>
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="mt-4">
            @csrf
            @method('PUT')
            @include('admin.products._form', ['product' => $product])

            <button type="submit"
                    class="{{ $btn }} mt-6 w-full rounded-full bg-primary-dark px-6 py-4 text-base text-white font-semibold shadow-sm transition hover:opacity-90">
                Simpan perubahan
            </button>
        </form>
    </section>

    <section class="mt-8 rounded-2xl border border-border bg-surface p-6">
        <h2 class="font-display text-xl text-primary-dark">Foto produk</h2>
        <p class="mt-1 text-sm text-muted">
            Tampil di kartu katalog dan di halaman detail sebelum pembeli memilih warna. Foto pertama jadi sampul.
        </p>

        <div class="mt-4 flex flex-wrap gap-3">
            @forelse ($product->images as $image)
            <div class="relative">  
                <img src="{{ $image->url }}" alt="" loading="lazy"
                    class="h-64 w-64 rounded-xl border border-border object-cover">
                <form method="POST" action="{{ route('admin.images.destroy', $image) }}"
                    class="absolute -right-2 -top-2" onsubmit="return confirm('Hapus foto ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Hapus foto"
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs text-white">×</button>
                </form>
            </div>
            @empty
                <p class="text-sm text-muted">Belum ada foto produk.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.products.images.store', $product) }}"
            enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
            @csrf
            <input type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp"
                class="text-sm text-muted file:mr-3 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-dark">
            <button type="submit" class="{{ $btnGhost }}">Unggah foto</button>
        </form>
    </section>

    {{-- Warna --}}
    <section class="mt-8">
        <h2 class="font-display text-xl text-primary-dark">Warna, ukuran &amp; foto</h2>

        @forelse ($product->colors as $color)
            <div class="mt-4 rounded-2xl border border-border bg-surface p-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="h-5 w-5 rounded-full border border-border"
                              style="background: {{ $color->hex_code ?? '#ffffff' }}"></span>
                        <h3 class="font-display text-lg text-primary-dark">{{ $color->name }}</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.colors.destroy', $color) }}"
                          onsubmit="return confirm(@js('Hapus warna ' . $color->name . ' beserta semua ukuran dan fotonya?'))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Hapus warna</button>
                    </form>
                </div>

                {{-- Foto --}}
                <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-muted">Foto (yang pertama jadi sampul)</p>
                <div class="mt-2 flex flex-wrap gap-3">
                    @forelse ($color->images as $image)
                        <div class="relative">
                            <img src="{{ $image->url }}" alt="" loading="lazy"
                                 class="h-64 w-64 rounded-xl border border-border object-cover">
                            <form method="POST" action="{{ route('admin.images.destroy', $image) }}"
                                  class="absolute -right-2 -top-2" onsubmit="return confirm('Hapus foto ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Hapus foto"
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs text-white">×</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-muted">Belum ada foto.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.colors.images.store', $color) }}"
                      enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp"
                           class="text-sm text-muted file:mr-3 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-dark">
                    <button type="submit" class="{{ $btnGhost }}">Unggah foto</button>
                </form>

                {{-- Ukuran & stok --}}
                <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-muted">Ukuran &amp; stok</p>

                @if ($color->variants->isNotEmpty())
                    <form method="POST" action="{{ route('admin.colors.variants.update', $color) }}" class="mt-2">
                        @csrf
                        @method('PUT')

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs uppercase tracking-wider text-muted">
                                    <tr>
                                        <th class="py-2 pr-4">Ukuran</th>
                                        <th class="py-2 pr-4">SKU</th>
                                        <th class="py-2 pr-4">Stok</th>
                                        <th class="py-2 pr-4">Harga khusus</th>
                                        <th class="py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($color->variants->sortBy('size', SORT_NATURAL) as $v)
                                        <tr class="border-t border-border">
                                            <td class="py-2 pr-4 font-medium text-ink">{{ $v->size }}</td>
                                            <td class="py-2 pr-4 text-xs text-muted">{{ $v->sku }}</td>
                                            <td class="py-2 pr-4">
                                                <input type="number" min="0" required
                                                       name="variants[{{ $v->id }}][stock]"
                                                       value="{{ old("variants.{$v->id}.stock", $v->stock) }}"
                                                       class="w-24 {{ $input }}">
                                            </td>
                                            <td class="py-2 pr-4">
                                                <input type="number" min="0"
                                                       name="variants[{{ $v->id }}][price]"
                                                       value="{{ old("variants.{$v->id}.price", $v->price) }}"
                                                       placeholder="{{ $product->base_price }}"
                                                       class="w-32 {{ $input }}">
                                            </td>
                                            <td class="py-2 text-right">
                                                <button type="submit" form="del-variant-{{ $v->id }}"
                                                        class="text-xs font-medium text-red-600 hover:underline">Hapus</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="mt-2 text-xs text-muted">Kosongkan harga khusus untuk memakai harga dasar produk.</p>
                        <button type="submit" class="mt-3 {{ $btn }}">Simpan stok</button>
                    </form>

                
                    @foreach ($color->variants as $v)
                        <form id="del-variant-{{ $v->id }}" method="POST" class="hidden"
                              action="{{ route('admin.variants.destroy', $v) }}"
                              onsubmit="return confirm(@js('Hapus ukuran ' . $v->size . '?'))">
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
                        <input type="text" name="size" required maxlength="10" placeholder="42" class="block w-24 {{ $input }}">
                    </label>
                    <label class="text-xs text-muted">Stok
                        <input type="number" name="stock" min="0" value="0" required class="block w-24 {{ $input }}">
                    </label>
                    <label class="text-xs text-muted">Harga khusus (opsional)
                        <input type="number" name="price" min="0" class="block w-32 {{ $input }}">
                    </label>
                    <button type="submit" class="{{ $btnGhost }}">+ Tambah ukuran</button>
                </form>
            </div>
        @empty
            <p class="mt-4 rounded-2xl border border-dashed border-border bg-surface px-6 py-8 text-center text-sm text-muted">
                Belum ada warna. Tambahkan di bawah.
            </p>
        @endforelse

        {{-- Tambah warna --}}
        <form method="POST" action="{{ route('admin.products.colors.store', $product) }}"
              class="mt-4 flex flex-wrap items-end gap-3 rounded-2xl border border-dashed border-border bg-surface p-6">
            @csrf
            <label class="text-xs text-muted">Nama warna
                <input type="text" name="name" required maxlength="50" placeholder="Hitam" class="block w-48 {{ $input }}">
            </label>
            <label class="text-xs text-muted">Kode warna
                <input type="color" name="hex_code" value="#111111" class="block h-9 w-14 rounded-lg border border-border bg-bg">
            </label>
            <button type="submit" class="{{ $btnGhost }}"">+ Tambah warna</button>
        </form>
    </section>

    {{-- Hapus produk --}}
    <section class="mt-8 rounded-2xl border border-red-200 bg-red-50/50 p-6">
        <h2 class="font-display text-lg text-red-700">Hapus produk</h2>
        <p class="mt-1 text-sm text-muted">Semua warna, ukuran, dan foto produk ini ikut terhapus. Tidak bisa dibatalkan.</p>
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="mt-3"
              onsubmit="return confirm(@js('Hapus produk ' . $product->name . ' secara permanen?'))">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-full border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                Hapus produk
            </button>
        </form>
    </section>
@endsection