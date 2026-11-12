@php
    $input = 'mt-1 rounded-lg border border-border bg-bg px-3 py-1.5 text-sm text-ink focus:border-primary focus:outline-none';
    $buttonGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
@endphp

<section class="mt-8">
    <h2 class="font-display text-xl text-primary-dark">Warna, ukuran &amp; foto</h2>

    @forelse ($product->colors as $color)
        @include('admin.products.partials.color-card', ['color' => $color, 'product' => $product])
    @empty
        <p class="mt-4 rounded-2xl border border-dashed border-border bg-surface px-6 py-8 text-center text-sm text-muted">
            Belum ada warna. Tambahkan di bawah.
        </p>
    @endforelse

    <form method="POST" action="{{ route('admin.products.colors.store', $product) }}"
          class="mt-4 flex flex-wrap items-end gap-3 rounded-2xl border border-dashed border-border bg-surface p-6">
        @csrf
        <label class="text-xs text-muted">Nama warna
            <input type="text" name="name" required maxlength="50" placeholder="ex: Hitam" class="block w-48 {{ $input }}">
        </label>
        <label class="text-xs text-muted">Kode warna
            <input type="color" name="hex_code" value="#111111" class="block h-9 w-14 rounded-lg border border-border bg-bg">
        </label>
        <button type="submit" class="{{ $buttonGhost }}">+ Tambah warna</button>
    </form>
</section>
