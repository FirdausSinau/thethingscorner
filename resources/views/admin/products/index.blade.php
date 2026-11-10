@extends('layouts.admin')

@section('title', 'Produk & stok · Admin')

@section('content')
    @php
        $btnGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';

        // Warna sel stok: habis = merah, menipis = kuning, aman = netral
        $stockCls = fn (int $stock) => match (true) {
            $stock <= 0 => 'bg-red-50 text-red-700 font-semibold',
            $stock <= 3 => 'bg-amber-50 text-amber-800 font-semibold',
            default     => 'text-ink',
        };
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Katalog</p>
            <h1 class="mt-2 font-display text-3xl text-primary-dark">Produk &amp; stok</h1>
        </div>
        <a href="{{ route('admin.products.create') }}" class="{{ $btnGhost }}">+ Tambah produk</a>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-success/20 bg-success/5 px-4 py-3 text-sm text-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Keterangan warna stok --}}
    <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-muted">
        <span class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded border border-border bg-white"></span> Aman
        </span>
        <span class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded border border-amber-200 bg-amber-50"></span> Menipis (≤ 3)
        </span>
        <span class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded border border-red-200 bg-red-50"></span> Habis
        </span>
    </div>

    <div class="mt-3 overflow-x-auto rounded-2xl border border-border bg-surface">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead class="border-b border-border text-xs uppercase tracking-wider text-muted">
                <tr>
                    <th class="w-48 px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Total stok</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>

            @forelse ($products as $product)
                @php
                    $variants   = $product->colors->flatMap->variants;
                    $totalStock = (int) $variants->sum('stock');
                    $lowCount   = $variants->where('stock', '<=', 3)->count();
                    $sizes      = $variants->pluck('size')->unique()->sortBy(fn ($s) => (int) $s)->values();
                @endphp

                <tbody class="border-t border-border first:border-t-0">
                    <tr>
                        <td rowspan="2" class="border-r border-border px-4 py-4 align-top">
                            <span class="font-medium text-ink">{{ $product->name }}</span>
                            @if ($product->is_preorder)
                                <span class="ml-1 rounded-full bg-primary-dark px-2 py-0.5 text-[10px] font-semibold uppercase text-white">PO</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-muted">{{ $product->category?->name ?? '-' }}</td>
                        <td class="px-4 py-4">Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-4">
                            <span class="font-semibold {{ $totalStock <= 0 ? 'text-red-600' : 'text-ink' }}">{{ $totalStock }}</span>
                            @if ($lowCount > 0)
                                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">
                                    {{ $lowCount }} ukuran kritis
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="{{ $product->is_active ? 'text-success' : 'text-muted' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="{{$btnGhost}}">Kelola</a>
                        </td>
                    </tr>

                    {{-- Baris 2: matriks warna x ukuran, masuk ke dalam area produk (di kanan kolom Produk) --}}
                    <tr>
                        <td colspan="5" class="border-t border-border px-4 pb-4 pt-3">
                            @if ($product->colors->isEmpty())
                                <p class="text-xs text-muted">Belum ada warna untuk produk ini.</p>
                            @else
                                <table class="w-full text-sm">
                                    <thead class="text-xs uppercase tracking-wider text-muted">
                                        <tr>
                                            <th class="w-40 pb-1 text-left font-medium">Warna</th>
                                            @foreach ($sizes as $size)
                                                <th class="pb-1 text-center font-medium">{{ $size }}</th>
                                            @endforeach
                                            <th class="w-20 pb-1 text-right font-medium">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($product->colors as $color)
                                            <tr class="border-t border-border">
                                                <td class="py-1.5">
                                                    <span class="flex items-center gap-2">
                                                        <span class="h-4 w-4 rounded-full border border-border"
                                                              style="background-color: {{ $color->hex_code }}"></span>
                                                        <span class="text-ink">{{ $color->name }}</span>
                                                    </span>
                                                </td>
                                                @foreach ($sizes as $size)
                                                    @php $v = $color->variants->firstWhere('size', $size); @endphp
                                                    <td class="p-0.5 text-center">
                                                        @if ($v)
                                                            <span class="mx-auto block w-16 rounded py-1 {{ $stockCls((int) $v->stock) }}"
                                                                  title="{{ $v->sku }}">{{ $v->stock }}</span>
                                                        @else
                                                            <span class="block py-1 text-muted">&ndash;</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="text-right font-semibold text-ink">{{ (int) $color->variants->sum('stock') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-muted">Belum ada produk.</td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection