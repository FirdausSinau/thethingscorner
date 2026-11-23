@php
    $shipment = $order->latestShipment;
@endphp

<section class="rounded-2xl border border-border bg-surface p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <h2 class="font-display text-xl text-primary-dark">Ringkasan pesanan</h2>

        @if ($shipment?->tracking_number)
            <a href="{{ route('admin.shipments.label', $shipment) }}"
               title="Cetak resi PDF"
               class="inline-flex items-center gap-2 rounded-lg border border-border bg-bg px-3 py-2 text-xs font-semibold text-primary-dark transition hover:border-primary/30 hover:bg-primary/5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Cetak resi
            </a>
        @endif
    </div>

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
