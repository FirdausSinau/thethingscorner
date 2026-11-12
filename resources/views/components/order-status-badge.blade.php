@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\OrderStatus::MenungguPembayaran => 'bg-amber-100 text-amber-800',
        \App\Enums\OrderStatus::Dibayar => 'bg-sky-100 text-sky-800',
        \App\Enums\OrderStatus::MenungguKonfirmasi => 'bg-orange-100 text-orange-800',
        \App\Enums\OrderStatus::Dikemas => 'bg-rose-100 text-rose-800',
        \App\Enums\OrderStatus::Dikirim => 'bg-pink-100 text-pink-800',
        \App\Enums\OrderStatus::Selesai => 'bg-green-100 text-green-800',
        \App\Enums\OrderStatus::Dibatalkan,
        \App\Enums\OrderStatus::Kedaluwarsa => 'bg-red-100 text-red-700',
    };
@endphp

<span {{ $attributes->merge(['class' => 'rounded-full px-2.5 py-1 text-xs font-medium '.$classes]) }}>
    {{ $status->label() }}
</span>
