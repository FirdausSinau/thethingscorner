@extends('layouts.storefront')

@section('title', 'Keranjang · Thethingscorner')

@section('content')
    <section class="mx-auto w-full max-w-4xl px-5 py-10 md:px-8">
        <h1 class="font-display text-3xl text-primary-dark">Keranjang</h1>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-danger/20 bg-danger/5 px-4 py-3 text-sm text-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-border bg-surface px-6 py-12 text-center">
                <p class="font-display text-xl text-primary-dark">Keranjang masih kosong</p>
                <a href="{{ route('catalog.index') }}"
                   class="mt-5 inline-flex rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                    Lihat katalog
                </a>
            </div>
        @else
            <ul class="mt-8 space-y-4">
                @foreach ($items as $item)
                    @include('storefront.cart.partials.item', ['item' => $item])
                @endforeach
            </ul>

            @include('storefront.cart.partials.summary', ['subtotal' => $subtotal, 'count' => $count])
        @endif
    </section>
@endsection
