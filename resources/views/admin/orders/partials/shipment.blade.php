@php
    $shipment = $order->latestShipment;
@endphp

@if ($shipment)
    <section class="mt-6 rounded-2xl border border-border bg-surface p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="font-display text-xl text-primary-dark">Pengiriman</h2>
                <p class="mt-1 text-sm text-muted">{{ $shipment->courier }} {{ $shipment->service }}</p>
            </div>

            <span class="rounded-full border border-border bg-bg px-3 py-1 text-xs font-semibold text-muted">
                {{ $shipment->status->label() }}
            </span>
        </div>

        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs text-muted">Nomor resi</dt>
                <dd class="break-all font-mono text-xs">{{ $shipment->tracking_number ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-muted">Ongkir</dt>
                <dd>Rp {{ number_format($shipment->cost, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-muted">Berat</dt>
                <dd>{{ number_format($shipment->weight_grams, 0, ',', '.') }} gram</dd>
            </div>
            <div>
                <dt class="text-xs text-muted">Dikirim</dt>
                <dd>{{ $shipment->shipped_at?->format('d/m/Y H:i') ?? '-' }}</dd>
            </div>
        </dl>

        @if ($shipment->tracking_number)
            <a href="{{ route('admin.shipments.label', $shipment) }}"
               class="mt-4 inline-flex rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark">
                Cetak resi PDF
            </a>
        @else
            <p class="mt-4 text-sm text-muted">Nomor resi belum tersedia.</p>
        @endif
    </section>
@endif
