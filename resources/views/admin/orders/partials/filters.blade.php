@php
    $input = 'rounded-lg border border-border bg-white px-3 py-2 text-sm';
    $button = 'rounded-full bg-primary-dark px-4 py-2 text-sm font-semibold text-white hover:opacity-90';
    $current = request('status');
    $filters = array_filter([
        'status' => request('status'),
        'q' => request('q'),
        'dari' => request('dari'),
        'sampai' => request('sampai'),
    ]);
    $hasFilter = request()->filled('q') || request()->filled('dari') || request()->filled('sampai');
@endphp

<div class="mt-6 flex flex-wrap gap-2 text-sm">
    <a href="{{ route('admin.orders.index', array_diff_key($filters, ['status' => 1])) }}"
       class="rounded-full border px-3 py-1.5 {{ ! $current ? 'border-primary-dark bg-primary-dark text-white' : 'border-border text-muted hover:text-primary-dark' }}">
        Semua <span class="opacity-70">({{ $counts->sum() }})</span>
    </a>

    @foreach ($statuses as $status)
        <a href="{{ route('admin.orders.index', array_merge($filters, ['status' => $status->value])) }}"
           class="rounded-full border px-3 py-1.5 {{ $current === $status->value ? 'border-primary-dark bg-primary-dark text-white' : 'border-border text-muted hover:text-primary-dark' }}">
            {{ $status->label() }} <span class="opacity-70">({{ $counts[$status->value] ?? 0 }})</span>
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.orders.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
    @if ($current)
        <input type="hidden" name="status" value="{{ $current }}">
    @endif

    <label class="min-w-[250px] flex-1 text-xs text-muted">Cari
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Kode pesanan atau penerima"
               class="{{ $input }} mt-1 block w-full sm:w-64">
    </label>

    <label class="text-xs text-muted">Dibayar dari
        <input type="date" name="dari" value="{{ request('dari') }}" class="{{ $input }} mt-1 block">
    </label>

    <label class="text-xs text-muted">Sampai
        <input type="date" name="sampai" value="{{ request('sampai') }}" class="{{ $input }} mt-1 block">
    </label>

    <button type="submit" class="{{ $button }}">Terapkan</button>

    @if ($hasFilter)
        <a href="{{ route('admin.orders.index', array_filter(['status' => $current])) }}"
           class="px-2 py-2 text-sm text-muted hover:text-primary-dark">Reset</a>
    @endif
</form>

@if (request('dari') || request('sampai'))
    <p class="mt-3 text-sm text-muted">
        {{ $orders->total() }} pesanan dibayar
        {{ request('dari') ? 'dari '.\Illuminate\Support\Carbon::parse(request('dari'))->format('d/m/Y') : '' }}
        {{ request('sampai') ? 'sampai '.\Illuminate\Support\Carbon::parse(request('sampai'))->format('d/m/Y') : '' }}
        · total <strong class="text-ink">Rp {{ number_format($sumTotal, 0, ',', '.') }}</strong>
    </p>
@endif
