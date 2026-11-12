@extends('layouts.admin')

@use('App\Enums\OrderStatus')

@section('title', 'Pesanan · Admin')

@section('content')
    @php
        $badge = fn ($s) => match ($s) {
            OrderStatus::MenungguPembayaran => 'bg-amber-100 text-amber-800',
            OrderStatus::Dibayar            => 'bg-sky-100 text-sky-800',
            OrderStatus::Dikemas            => 'bg-indigo-100 text-indigo-800',
            OrderStatus::Dipending          => 'bg-orange-100 text-orange-800',
            OrderStatus::Dikirim            => 'bg-violet-100 text-violet-800',
            OrderStatus::Selesai            => 'bg-green-100 text-green-800',
            OrderStatus::Dibatalkan,
            OrderStatus::Kedaluwarsa        => 'bg-red-100 text-red-700',
        };

        $input = 'rounded-lg border border-border bg-white px-3 py-2 text-sm';
        $btn   = 'rounded-full bg-primary-dark px-4 py-2 text-sm font-semibold text-white hover:opacity-90';
        $current = request('status');

        $filters = array_filter([
            'status' => request('status'),
            'q'      => request('q'),
            'dari'   => request('dari'),
            'sampai' => request('sampai'),
        ]);
        $hasFilter = request()->filled('q') || request()->filled('dari') || request()->filled('sampai');

        // Status tujuan untuk aksi massal (Menunggu pembayaran dan Kedaluwarsa hanya oleh sistem)
        $bulkStatuses = collect($statuses)->reject(
            fn ($s) => in_array($s, [OrderStatus::MenungguPembayaran, OrderStatus::Kedaluwarsa], true)
        );
    @endphp

    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Operasional</p>
        <h1 class="mt-2 font-display text-3xl text-primary-dark">Pesanan</h1>
    </div>

    {{-- Filter status --}}
    <div class="mt-6 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('admin.orders.index', array_diff_key($filters, ['status' => 1])) }}"
           class="rounded-full border px-3 py-1.5 {{ ! $current ? 'border-primary-dark bg-primary-dark text-white' : 'border-border text-muted hover:text-primary-dark' }}">
            Semua <span class="opacity-70">({{ $counts->sum() }})</span>
        </a>
        @foreach ($statuses as $s)
            <a href="{{ route('admin.orders.index', array_merge($filters, ['status' => $s->value])) }}"
               class="rounded-full border px-3 py-1.5 {{ $current === $s->value ? 'border-primary-dark bg-primary-dark text-white' : 'border-border text-muted hover:text-primary-dark' }}">
                {{ $s->label() }} <span class="opacity-70">({{ $counts[$s->value] ?? 0 }})</span>
            </a>
        @endforeach
    </div>

    {{-- Pencarian dan filter tanggal dibayar --}}
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

        <button type="submit" class="{{ $btn }}">Terapkan</button>

        @if ($hasFilter)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $current])) }}"
               class="px-2 py-2 text-sm text-muted hover:text-primary-dark">Reset</a>
        @endif
    </form>

    @if (request('dari') || request('sampai'))
        <p class="mt-3 text-sm text-muted">
            {{ $orders->total() }} pesanan dibayar
            {{ request('dari') ? 'dari ' . \Illuminate\Support\Carbon::parse(request('dari'))->format('d/m/Y') : '' }}
            {{ request('sampai') ? 'sampai ' . \Illuminate\Support\Carbon::parse(request('sampai'))->format('d/m/Y') : '' }}
            · total <strong class="text-ink">Rp {{ number_format($sumTotal, 0, ',', '.') }}</strong>
        </p>
    @endif

    {{-- Ubah status massal (checkbox di tabel terhubung lewat atribut form="bulkForm") --}}
    <form id="bulkForm" method="POST" action="{{ route('admin.orders.bulk-status') }}"
          class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-border bg-surface px-4 py-3">
        @csrf
        @method('PATCH')
        <span class="text-sm text-muted"><span id="selectedCount">0</span> dipilih</span>

        <select name="status" required class="{{ $input }}">
            <option value="">Ubah status menjadi...</option>
            @foreach ($bulkStatuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>

        <button type="submit" id="bulkBtn" disabled
                class="{{ $btn }} disabled:cursor-not-allowed disabled:opacity-40">
            Terapkan ke yang dipilih
        </button>
    </form>

    <div class="mt-4 overflow-x-auto rounded-2xl border border-border bg-surface">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-border text-xs uppercase tracking-wider text-muted">
                <tr>
                    <th class="px-4 py-3"><input type="checkbox" id="selectAll" aria-label="Pilih semua"></th>
                    <th class="px-4 py-3">Pesanan</th>
                    <th class="px-4 py-3">Pembeli</th>
                    <th class="px-4 py-3">Barang</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Ubah status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $first = $order->items->first();
                        $next  = $order->status->manualNext();
                    @endphp
                    <tr class="border-t border-border align-top">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" form="bulkForm" class="row-check">
                        </td>

                        <td class="px-4 py-3">
                            <p class="font-medium text-ink">{{ $order->order_code }}</p>
                            <p class="text-xs text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-muted">{{ $order->payment_method->label() }}</p>

                            @unless ($order->status->isAwaitingPayment())
                                <a href="{{ route('orders.show', ['order' => $order->order_code]) }}"
                                   class="mt-2 inline-block text-xs font-medium text-primary hover:text-primary-dark">
                                    Detail pesanan &rarr;
                                </a>
                            @endunless
                        </td>

                        <td class="px-4 py-3">
                            <p>{{ $order->recipient }}</p>
                            <p class="text-xs text-muted">{{ $order->user?->name }} · {{ $order->phone }}</p>
                            <p class="text-xs text-muted">{{ $order->city }}</p>
                        </td>

                        <td class="min-w-[12rem] px-4 py-3">
                            @if ($first)
                                <p class="font-medium leading-snug text-ink">{{ $first->product_name }}</p>
                                <p class="mt-0.5 text-xs text-muted">{{ $first->color }} · {{ $first->size }} · x{{ $first->qty }}</p>

                                @if ($order->items->count() > 1)
                                    <details class="mt-2">
                                        <summary class="inline-flex cursor-pointer list-none items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary-dark hover:bg-primary/15 [&::-webkit-details-marker]:hidden">
                                            + {{ $order->items->count() - 1 }} item <span aria-hidden="true">&#9662;</span>
                                        </summary>

                                        <ul class="mt-3 space-y-3 border-l-2 border-border pl-3">
                                            @foreach ($order->items->skip(1) as $item)
                                                <li>
                                                    <p class="leading-snug text-ink">{{ $item->product_name }}</p>
                                                    <p class="mt-0.5 text-xs text-muted">{{ $item->color }} · {{ $item->size }} · x{{ $item->qty }}</p>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            @endif
                        </td>

                        <td class="px-4 py-3 font-medium">Rp {{ number_format($order->total, 0, ',', '.') }}</td>

                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $badge($order->status) }}">
                                {{ $order->status->label() }}
                            </span>
                            @if ($order->paid_at)
                                <p class="mt-1 text-xs text-muted">Dibayar {{ $order->paid_at->format('d/m H:i') }}</p>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if ($next)
                                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" required class="{{ $input }}">
                                        <option value="" disabled selected>Ubah ke...</option>
                                        @foreach ($next as $s)
                                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-lg border border-border px-3 py-2 text-sm hover:border-primary-dark">
                                        Ubah
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-muted">Status akhir</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-muted">Belum ada pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>

    <script>
        (function () {
            const selectAll = document.getElementById('selectAll');
            const rows      = Array.from(document.querySelectorAll('.row-check'));
            const counter   = document.getElementById('selectedCount');
            const bulkBtn   = document.getElementById('bulkBtn');
            const bulkForm  = document.getElementById('bulkForm');

            const checkedCount = () => rows.filter((r) => r.checked).length;

            function refresh() {
                const n = checkedCount();
                counter.textContent = n;
                bulkBtn.disabled = n === 0;
                selectAll.checked = rows.length > 0 && n === rows.length;
                selectAll.indeterminate = n > 0 && n < rows.length;
            }

            selectAll.addEventListener('change', () => {
                rows.forEach((r) => (r.checked = selectAll.checked));
                refresh();
            });

            rows.forEach((r) => r.addEventListener('change', refresh));

            bulkForm.addEventListener('submit', (e) => {
                const n = checkedCount();

                if (n === 0 || ! confirm('Ubah status ' + n + ' pesanan yang dipilih?')) {
                    e.preventDefault();
                }
            });

            refresh();
        })();
    </script>
@endsection