<p class="mt-2 text-sm text-muted">
    {{ $order->status->label() }} · {{ $order->payment_method->label() }}
</p>

@if ($order->reservation_expires_at)
    <p class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
        Bayar sebelum <strong>{{ $order->reservation_expires_at->format('d/m/Y H:i:s') }}</strong>.
        Setelah itu pesanan kedaluwarsa.
    </p>
@endif
