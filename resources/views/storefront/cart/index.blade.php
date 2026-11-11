@extends('layouts.storefront')

@section('title', 'Keranjang · thethingscorner')

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
                    <li class="rounded-2xl border border-border bg-surface p-4">
                        <p class="font-semibold text-primary-dark">{{ $item->variant->color->product->name }}</p>
                        <p class="text-xs text-muted">
                            {{ $item->variant->color->name }} · Ukuran {{ $item->variant->size }} · SKU {{ $item->variant->sku }}
                        </p>
                        <p class="mt-1 text-sm">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="qty" value="{{ $item->qty }}" min="1"
                                       class="w-16 rounded-lg border border-border px-2 py-2 text-center text-sm">
                                <button type="submit" class="rounded-lg border border-border px-3 py-2 text-sm">Ubah</button>
                            </form>

                            <p class="text-sm font-semibold text-primary-dark">
                                Rp {{ number_format($item->line_total, 0, ',', '.') }}
                            </p>

                            <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-danger hover:underline">Hapus</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6 rounded-2xl border border-border bg-surface p-5">
                <p class="flex justify-between text-sm">
                    <span class="text-muted">Subtotal ({{ $count }} barang)</span>
                    <span class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </p>
                <p class="mt-3 text-xs text-muted">Stok belum dikunci sampai checkout.</p>
                                <a href="{{ route('checkout.index') }}"
                   class="mt-4 inline-flex rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
                    Checkout
                </a>
            </div>
        @endif
    </section>
@endsection