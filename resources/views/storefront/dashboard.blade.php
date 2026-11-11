@extends('layouts.storefront')

@section('title', 'Dashboard · Thethingscorner')

@section('content')
    <section class="mx-auto w-full max-w-6xl px-5 py-12 md:px-8">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Dashboard pembeli</p>

        <div class="mt-3 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="font-display text-3xl text-primary-dark md:text-4xl">
                    Halo, {{ auth()->user()->name }}.
                </h1>
                <p class="mt-2 text-sm text-muted">
                    Pantau pesanan, custom PO, dan alamat pengiriman dari satu tempat.
                </p>
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Pesanan</p>
                <p class="mt-4 font-display text-2xl text-primary-dark">Belum ada pesanan</p>
                <p class="mt-2 text-sm leading-6 text-muted">
                    Pesanan siap stok akan muncul di sini setelah katalog aktif.
                </p>
            </div>

            <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Custom PO</p>
                <p class="mt-4 font-display text-2xl text-primary-dark">Belum ada PO</p>
                <p class="mt-2 text-sm leading-6 text-muted">
                    Pengajuan custom model dan status produksi akan tampil di sini.
                </p>
            </div>

            <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Alamat</p>
                <p class="mt-4 font-display text-2xl text-primary-dark">Belum diisi</p>
                <p class="mt-2 text-sm leading-6 text-muted">
                    Alamat pengiriman akan dipakai saat checkout.
                </p>
            </div>
        </div>

        <div class="border-t border-border" style="margin-top: 4rem; padding-top: 3rem;">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Katalog</p>
                <h2 class="mt-3 font-display text-2xl text-primary-dark md:text-3xl">
                    Temukan sepatu favoritmu
                </h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                    Pilih model, warna, dan ukuran yang paling pas untuk langkahmu.
                </p>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
                @forelse ($products as $product)
                    @include('product.partials.product-card', ['product' => $product])
                @empty
                    <p class="col-span-full rounded-2xl border border-dashed border-border bg-surface px-6 py-10 text-center text-sm text-muted">
                        Belum ada produk.
                    </p>
                @endforelse
            </div>

            <div class="mt-10">{{ $products->links() }}</div>
        </div>
    </section>
@endsection
