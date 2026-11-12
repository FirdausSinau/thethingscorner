@php
    $fields = [
        'recipient' => 'Nama penerima',
        'phone' => 'Nomor telepon',
        'province' => 'Provinsi',
        'city' => 'Kota/kabupaten',
        'district' => 'Kecamatan',
        'postal_code' => 'Kode pos',
        'detail' => 'Alamat lengkap',
    ];
@endphp

<div>
    <h2 class="font-display text-xl text-primary-dark">Alamat pengiriman</h2>

    <div class="mt-4 space-y-4">
        @foreach ($fields as $name => $label)
            <div>
                <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
                <input id="{{ $name }}" name="{{ $name }}" required
                       value="{{ old($name, $defaultAddress?->$name) }}"
                       class="mt-1 w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm">

                @error($name)
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>
</div>
