<form method="POST" action="#" class="mt-6">
    @csrf
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">
        Ukuran {{ $activeColor ? '· ' . $activeColor->name : '' }}
    </p>

    @if ($variants->isEmpty())
        <p class="mt-3 text-sm text-muted">Ukuran untuk warna ini belum tersedia.</p>
    @else
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($variants as $v)
                <label>
                    <input type="radio" name="variant_id" value="{{ $v->id }}"
                           class="peer sr-only" required @disabled($v->stock <= 0)>
                    <span class="block min-w-12 cursor-pointer rounded-lg border border-border px-3 py-2 text-center text-sm
                                 peer-checked:border-primary-dark peer-checked:bg-primary-dark peer-checked:text-white
                                 peer-disabled:cursor-not-allowed peer-disabled:line-through peer-disabled:opacity-40">
                        {{ $v->size }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif 

    <!-- //kerjain woi anand -->
    <button type="submit" @disabled($variants->isEmpty())
            class="mt-8 w-full rounded-full bg-primary-dark px-6 py-3 text-sm font-semibold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">
        Tambah ke keranjang
    </button>
</form>