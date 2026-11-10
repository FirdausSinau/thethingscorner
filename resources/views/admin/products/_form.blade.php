@php
    $input = 'mt-1 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink focus:border-primary focus:outline-none';
    $label = 'block text-xs font-semibold uppercase tracking-[0.14em] text-muted';
    $p = $product ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="{{ $label }}">Nama produk
            <input type="text" name="name" value="{{ old('name', $p?->name) }}" required maxlength="150" class="{{ $input }}">
        </label>
    </div>

    <div>
        <label class="{{ $label }}">Kategori
            <select name="category_id" required class="{{ $input }}">
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $p?->category_id) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>
    </div>

    <div>
        <label class="{{ $label }}">Harga dasar (Rp)
            <input type="number" name="base_price" min="0" value="{{ old('base_price', $p?->base_price) }}" required class="{{ $input }}">
        </label>
    </div>

    <div class="md:col-span-2">
        <label class="{{ $label }}">Deskripsi
            <textarea name="description" rows="4" maxlength="2000" class="{{ $input }}">{{ old('description', $p?->description) }}</textarea>
        </label>
    </div>


    <div class="flex flex-wrap gap-6 md:col-span-2">
        <label class="flex items-center gap-2 text-sm text-ink">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $p?->is_active ?? true))>
            Tampilkan di katalog
        </label>
    </div>
</div>