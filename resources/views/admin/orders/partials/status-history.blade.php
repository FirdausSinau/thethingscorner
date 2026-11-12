@use('App\Enums\OrderStatus')

<section class="mt-4 rounded-2xl border border-border bg-surface p-5">
    <h2 class="font-display text-xl text-primary-dark">Riwayat status</h2>

    @forelse ($order->statusLogs->sortByDesc('created_at') as $log)
        @php
            $from = $log->from_status
                ? (OrderStatus::tryFrom($log->from_status)?->label() ?? $log->from_status)
                : 'Awal';
            $to = OrderStatus::tryFrom($log->to_status)?->label() ?? $log->to_status;
        @endphp

        <div class="mt-3 border-t border-border pt-3 text-sm">
            <p class="font-medium text-ink">{{ $from }} &rarr; {{ $to }}</p>
            <p class="mt-1 text-xs text-muted">
                {{ $log->created_at?->format('d/m/Y H:i') }} · {{ $log->actor?->name ?? 'Sistem' }}
                @if ($log->note)
                    · {{ $log->note }}
                @endif
            </p>
        </div>
    @empty
        <p class="mt-3 text-sm text-muted">Belum ada riwayat status.</p>
    @endforelse
</section>
