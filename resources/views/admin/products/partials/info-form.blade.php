@php
    $button = 'rounded-full bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90';
@endphp

<section class="mt-6 rounded-2xl border border-border bg-surface p-6">
    <h2 class="font-display text-xl text-primary-dark">Info produk</h2>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="mt-4">
        @csrf
        @method('PUT')

        @include('admin.products.partials.info-fields', [
            'product' => $product,
            'categories' => $categories,
        ])

        <button type="submit" class="{{ $button }} mt-6 w-full py-4 text-base shadow-sm">
            Simpan perubahan
        </button>
    </form>
</section>
