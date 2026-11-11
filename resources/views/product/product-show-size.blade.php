<div class="mt-6">
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">
        Ukuran {{ $activeColor ? '· ' . $activeColor->name : '' }}
    </p>

    @if ($variants->isEmpty())
        <p class="mt-3 text-sm text-muted">Ukuran untuk warna ini belum tersedia.</p>
    @else
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($variants as $v)
                <span class="block min-w-12 rounded-lg border border-border px-3 py-2 text-center text-sm
                             {{ $v->stock <= 0 ? 'text-muted line-through opacity-40' : 'text-ink' }}">
                    {{ $v->size }}
                </span>
            @endforeach
        </div>
    @endif

    <p class="mt-8 rounded-xl border border-dashed border-border bg-surface px-4 py-3 text-xs leading-5 text-muted">
        Keranjang dan checkout akan tersedia di tahap berikutnya.
    </p>
</div>
