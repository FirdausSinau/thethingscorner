@php
    $variants = $product->colors->flatMap->variants;
    $totalStock = (int) $variants->sum(fn ($variant): int => $available($variant));
    $lowCount = $variants->filter(fn ($variant): bool => $available($variant) <= 3)->count();
@endphp

<tbody class="border-t border-border first:border-t-0">
    <tr>
        <td rowspan="2" class="border-r border-border px-4 py-4 align-top">
            <span class="font-medium text-ink">{{ $product->name }}</span>
            @if ($product->is_preorder)
                <span class="ml-1 rounded-full bg-primary-dark px-2 py-0.5 text-[10px] font-semibold uppercase text-white">PO</span>
            @endif
        </td>
        <td class="px-4 py-4 text-muted">{{ $product->category?->name ?? '-' }}</td>
        <td class="px-4 py-4">Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
        <td class="px-4 py-4">
            <span class="font-semibold {{ $totalStock <= 0 ? 'text-red-600' : 'text-ink' }}">{{ $totalStock }}</span>
            @if ($lowCount > 0)
                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">
                    {{ $lowCount }} ukuran kritis
                </span>
            @endif
        </td>
        <td class="px-4 py-4">
            <span class="{{ $product->is_active ? 'text-success' : 'text-muted' }}">
                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </td>
        <td class="px-4 py-4 text-right">
            <a href="{{ route('admin.products.edit', $product) }}" class="{{ $buttonGhost }}">Kelola</a>
        </td>
    </tr>

    <tr>
        <td colspan="5" class="border-t border-border px-4 pb-4 pt-3">
            @include('admin.products.partials.stock-matrix', [
                'product' => $product,
                'available' => $available,
                'stockClass' => $stockClass,
            ])
        </td>
    </tr>
</tbody>
