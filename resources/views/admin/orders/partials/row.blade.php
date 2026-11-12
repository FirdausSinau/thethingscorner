@php($first = $order->items->first())

<tr class="border-t border-border align-top">
    <td class="px-2 py-3">
        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" form="bulkForm" class="row-check">
    </td>

    <td class="px-2 py-3">
        <p class="break-all font-medium text-ink">{{ $order->order_code }}</p>
        <p class="text-xs text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p class="text-xs text-muted">{{ $order->payment_method->label() }}</p>

        <a href="{{ route('admin.orders.show', $order) }}"
           class="mt-2 inline-block text-xs font-medium text-primary hover:text-primary-dark">
            Detail pesanan &rarr;
        </a>
    </td>

    <td class="px-2 py-3">
        <p class="break-words">{{ $order->recipient }}</p>
        <p class="break-words text-xs text-muted">{{ $order->user?->name }} · {{ $order->phone }}</p>
        <p class="text-xs text-muted">{{ $order->city }}</p>
    </td>

    <td class="px-2 py-3">
        @if ($first)
            <p class="break-words font-medium leading-snug text-ink">{{ $first->product_name }}</p>
            <p class="mt-0.5 break-words text-xs text-muted">{{ $first->color }} · {{ $first->size }} · x{{ $first->qty }}</p>

            @if ($order->items->count() > 1)
                <details class="mt-2">
                    <summary class="inline-flex cursor-pointer list-none items-center gap-1 rounded-full bg-primary/10 px-2 py-1 text-[11px] font-medium text-primary-dark hover:bg-primary/15 [&::-webkit-details-marker]:hidden">
                        + {{ $order->items->count() - 1 }} item <span aria-hidden="true">&#9662;</span>
                    </summary>

                    <ul class="mt-3 space-y-3 border-l-2 border-border pl-3">
                        @foreach ($order->items->skip(1) as $item)
                            <li>
                                <p class="break-words leading-snug text-ink">{{ $item->product_name }}</p>
                                <p class="mt-0.5 break-words text-xs text-muted">{{ $item->color }} · {{ $item->size }} · x{{ $item->qty }}</p>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
        @endif
    </td>

    <td class="whitespace-nowrap px-2 py-3 font-medium">Rp {{ number_format($order->total, 0, ',', '.') }}</td>

    <td class="px-2 py-3">
        <x-order-status-badge :status="$order->status" />

        @if ($order->paid_at)
            <p class="mt-1 text-xs text-muted">Dibayar {{ $order->paid_at->format('d/m H:i') }}</p>
        @endif
    </td>

    <td class="px-2 py-3">
        @include('admin.orders.partials.status-form', ['order' => $order])
    </td>
</tr>
