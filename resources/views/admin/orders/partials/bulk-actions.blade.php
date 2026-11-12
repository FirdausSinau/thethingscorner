@use('App\Enums\OrderStatus')

@php
    $input = 'rounded-lg border border-border bg-white px-3 py-2 text-sm';
    $button = 'rounded-full bg-primary-dark px-4 py-2 text-sm font-semibold text-white hover:opacity-90';
    $bulkStatuses = collect($statuses)->reject(
        fn (OrderStatus $status): bool => in_array($status, [OrderStatus::MenungguPembayaran, OrderStatus::Kedaluwarsa], true)
    );
@endphp

<form id="bulkForm" method="POST" action="{{ route('admin.orders.bulk-status') }}"
      class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-border bg-surface px-4 py-3">
    @csrf
    @method('PATCH')

    <span class="text-sm text-muted"><span id="selectedCount">0</span> dipilih</span>

    <select name="status" required class="{{ $input }}">
        <option value="">Ubah status menjadi...</option>
        @foreach ($bulkStatuses as $status)
            <option value="{{ $status->value }}">{{ $status->label() }}</option>
        @endforeach
    </select>

    <button type="submit" id="bulkBtn" disabled
            class="{{ $button }} disabled:cursor-not-allowed disabled:opacity-40">
        Terapkan ke yang dipilih
    </button>
</form>
