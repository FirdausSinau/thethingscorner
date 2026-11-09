@extends('layouts.storefront')

@section('title', 'Dashboard · The Things Corner')

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

            <a href="{{ route('home') }}"
               class="text-sm font-medium text-primary transition hover:text-primary-dark">
                Kembali ke toko
            </a>
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

        <div class="mt-8 rounded-2xl border border-dashed border-border bg-surface px-6 py-10 text-center">
            <p class="font-display text-xl text-primary-dark">Fitur pembeli sedang disiapkan.</p>
            <p class="mt-2 text-sm text-muted">
                Katalog, keranjang, dan custom PO akan tersedia bertahap.
            </p>
        </div>
    </section>
@endsection
