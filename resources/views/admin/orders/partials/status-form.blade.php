@php
    $next = $order->status->manualNext();
    $input = 'w-full rounded-lg border border-border bg-white px-2 py-2 text-xs';
@endphp

@if ($next)
    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex flex-col gap-2">
        @csrf
        @method('PATCH')

        <select name="status" required class="{{ $input }}">
            <option value="" disabled selected>Ubah ke...</option>
            @foreach ($next as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>

        <button type="submit" class="w-full rounded-lg border border-border px-2 py-2 text-xs hover:border-primary-dark">
            Ubah
        </button>
    </form>
@else
    <span class="text-xs text-muted">Status akhir</span>
@endif
