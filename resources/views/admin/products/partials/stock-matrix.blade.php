@php
    $sizes = $product->colors
        ->flatMap->variants
        ->pluck('size')
        ->unique()
        ->sortBy(fn ($size): int => (int) $size)
        ->values();
@endphp

@if ($product->colors->isEmpty())
    <p class="text-xs text-muted">Belum ada warna untuk produk ini.</p>
@else
    <table class="w-full text-sm">
        <thead class="text-xs uppercase tracking-wider text-muted">
            <tr>
                <th class="w-40 pb-1 text-left font-medium">Warna</th>
                @foreach ($sizes as $size)
                    <th class="pb-1 text-center font-medium">{{ $size }}</th>
                @endforeach
                <th class="w-20 pb-1 text-right font-medium">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($product->colors as $color)
                <tr class="border-t border-border">
                    <td class="py-1.5">
                        <span class="flex items-center gap-2">
                            <span class="h-4 w-4 rounded-full border border-border"
                                  style="background-color: {{ $color->hex_code }}"></span>
                            <span class="text-ink">{{ $color->name }}</span>
                        </span>
                    </td>

                    @foreach ($sizes as $size)
                        @php
                            $variant = $color->variants->firstWhere('size', $size);
                            $stock = $variant ? $available($variant) : 0;
                        @endphp

                        <td class="p-0.5 text-center">
                            @if ($variant)
                                <span class="mx-auto block w-16 rounded py-1 {{ $stockClass($stock) }}"
                                      title="{{ $variant->sku }} · fisik {{ $variant->stock }} · dipesan {{ $variant->stock_reserved }}">
                                    {{ $stock }}
                                    @if ((int) $variant->stock_reserved > 0)
                                        <span class="block text-[10px] font-normal leading-none opacity-70">
                                            +{{ $variant->stock_reserved }} dipesan
                                        </span>
                                    @endif
                                </span>
                            @else
                                <span class="block py-1 text-muted">&ndash;</span>
                            @endif
                        </td>
                    @endforeach

                    <td class="text-right font-semibold text-ink">
                        {{ (int) $color->variants->sum(fn ($variant): int => $available($variant)) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
