@extends('layouts.admin')

@section('title', 'Dashboard Admin · Thethingscorner')

@section('content')
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Dashboard</p>
            <h1 class="mt-2 font-display text-3xl text-primary-dark md:text-4xl">
                Halo, {{ auth()->user()->name }}.
            </h1>
            <p class="mt-2 text-sm text-muted">
                {{ auth()->user()->role->label() }} · ringkasan operasional toko.
            </p>
        </div>

        @if (auth()->user()->isAdminMaster())
            <span class="inline-flex w-fit items-center rounded-full bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary">
                Akses penuh
            </span>
        @endif
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-border bg-surface p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">{{ $card['label'] }}</p>
                <p class="mt-3 font-display text-3xl text-primary-dark">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-muted">{{ $card['value'] > 0 ? $card['note'] : 'Tidak ada antrean' }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-2xl border border-dashed border-border bg-surface px-6 py-12 text-center">
            <p class="font-display text-xl text-primary-dark">Modul admin akan muncul bertahap.</p>
            <p class="mt-2 text-sm text-muted">
                Produk, stok, pesanan, PO, review, dan laporan menyusul di slice berikutnya.
            </p>
        </div>

        <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Status sistem</p>
            <ul class="mt-4 space-y-3 text-sm text-muted">
                <li class="flex items-center justify-between gap-4">
                    <span>Login dan role</span>
                    <span class="font-medium text-success">Aktif</span>
                </li>
                <li class="flex items-center justify-between gap-4">
                    <span>Katalog</span>
                    <span class="font-medium text-success">Aktif</span>
                </li>
                <li class="flex items-center justify-between gap-4">
                    <span>Pembayaran</span>
                    <span class="font-medium text-muted">Belum aktif</span>
                </li>
                <li class="flex items-center justify-between gap-4">
                    <span>Pengiriman</span>
                    <span class="font-medium text-muted">Belum aktif</span>
                </li>
            </ul>
        </div>
    </div>

    @if (auth()->user()->isAdminMaster())
        <div class="mt-6 rounded-2xl border border-primary/15 bg-primary/5 px-5 py-4 text-sm text-primary-dark">
            Sebagai Admin Master, kamu akan mendapat akses ke pengelolaan akun admin, laporan, dan rekonsiliasi Shopee.
        </div>
    @endif
@endsection
