@extends('layouts.storefront')

@section('title', 'Thethingscorner · Sepatu handmade custom Bandung')

@section('content')
    <section class="mx-auto grid w-full max-w-6xl gap-12 px-5 py-16 md:grid-cols-[1.15fr_0.85fr] md:px-8 md:py-24">
        <div class="flex flex-col justify-center">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-accent">
                Handmade custom shoes · Bandung
            </p>

            <h1 class="mt-5 max-w-2xl font-display text-5xl leading-[1.04] tracking-tight text-primary-dark md:text-7xl">
                Sepatu yang dibuat perlahan, dipakai setiap hari.
            </h1>

            <p class="mt-6 max-w-xl text-base leading-7 text-muted md:text-lg">
                Lihat model, warna, dan ukuran yang tersedia. Masuk untuk memesan,
                menyimpan alamat, dan memantau pesanan.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                       class="inline-flex min-h-11 items-center justify-center rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
                        Buka dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex min-h-11 items-center justify-center rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
                        Buat akun
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex min-h-11 items-center justify-center rounded-full border border-border bg-surface px-6 py-3 text-sm font-semibold text-primary-dark transition hover:border-primary/30">
                        Masuk
                    </a>
                @endauth
            </div>

            <p class="mt-4">
                <a href="{{ route('catalog.index') }}"
                   class="text-sm font-semibold text-primary transition hover:text-primary-dark">
                    Lihat katalog sepatu &rarr;
                </a>
            </p>

            <p class="mt-8 text-xs text-muted">
                Pesanan web dan Shopee akan tercatat dalam satu alur stok.
            </p>
        </div>

        <div class="hidden md:block">
            <div class="relative h-full min-h-[28rem] overflow-hidden rounded-[2rem] border border-border bg-surface p-8 shadow-sm">
                <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-accent/15"></div>
                <div class="absolute -bottom-20 -left-16 h-64 w-64 rounded-full bg-primary/5"></div>

                <div class="relative flex h-full flex-col justify-between">
                    <div class="flex items-center justify-between text-[10px] uppercase tracking-[0.22em] text-muted">
                        <span>Thethingscorner</span>
                        <span>Since 2015</span>
                    </div>

                    <div class="py-12">
                        <p class="font-display text-4xl leading-tight text-primary-dark">
                            Custom your shoes.
                        </p>
                        <p class="mt-4 max-w-xs text-sm leading-6 text-muted">
                            Pilih model, bahan, dan ukuran. Admin akan menghubungi
                            lewat WhatsApp untuk harga dan estimasi produksi.
                        </p>
                    </div>

                    <div class="border-t border-border pt-5 text-xs text-muted">
                        Handmade · Bandung · Custom order
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
