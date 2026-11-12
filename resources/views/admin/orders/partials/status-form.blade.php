@php
    $next = $order->status->manualNext();
    $input = 'rounded-lg border border-border bg-white px-3 py-2 text-sm';
@endphp

@if ($next)
    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex items-center gap-2">
        @csrf
        @method('PATCH')

        <select name="status" required class="{{ $input }}">
            <option value="" disabled selected>Ubah ke...</option>
            @foreach ($next as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>

        <button type="submit" class="rounded-lg border border-border px-3 py-2 text-sm hover:border-primary-dark">
            Ubah
        </button>
    </form>
@else
    <span class="text-xs text-muted">Status akhir</span>
@endif
