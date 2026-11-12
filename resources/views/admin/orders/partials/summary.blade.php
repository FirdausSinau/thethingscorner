<section class="rounded-2xl border border-border bg-surface p-5">
    <h2 class="font-display text-xl text-primary-dark">Ringkasan pesanan</h2>

    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-xs text-muted">Pembeli</dt>
            <dd>{{ $order->user?->name ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted">Penerima</dt>
            <dd>{{ $order->recipient }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted">Telepon</dt>
            <dd>{{ $order->phone }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted">Metode bayar</dt>
            <dd>{{ $order->payment_method->label() }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs text-muted">Alamat</dt>
            <dd>{{ $order->address }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted">Kota</dt>
            <dd>{{ $order->city }} {{ $order->postal_code }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted">Dibuat</dt>
            <dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted">Dibayar</dt>
            <dd>{{ $order->paid_at?->format('d/m/Y H:i') ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-muted">Selesai</dt>
            <dd>{{ $order->completed_at?->format('d/m/Y H:i') ?? '-' }}</dd>
        </div>

        @if ($order->reservation_expires_at)
            <div>
                <dt class="text-xs text-muted">Batas pembayaran</dt>
                <dd>{{ $order->reservation_expires_at->format('d/m/Y H:i:s') }}</dd>
            </div>
        @endif
    </dl>

    @if ($order->reservations->isNotEmpty())
        <h3 class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-muted">Reservasi stok</h3>
        <ul class="mt-2 space-y-2 text-sm">
            @foreach ($order->reservations as $reservation)
                <li class="flex items-center justify-between gap-3">
                    <span>
                        {{ $reservation->variant?->sku ?? 'Varian tidak ditemukan' }}
                        · {{ $reservation->qty }} pasang
                    </span>
                    <span class="text-xs text-muted">
                        {{ $reservation->status->label() }}
                        @if ($reservation->expires_at)
                            · {{ $reservation->expires_at->format('d/m H:i') }}
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-5 space-y-1 border-t border-border pt-3 text-sm">
        <p class="flex justify-between"><span class="text-muted">Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></p>
        <p class="flex justify-between"><span class="text-muted">Ongkir</span><span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span></p>
        <p class="flex justify-between font-semibold"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></p>
    </div>
</section>
