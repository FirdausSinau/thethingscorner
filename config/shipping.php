<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asal pengiriman
    |--------------------------------------------------------------------------
    |
    | Masih placeholder sampai client memberi alamat gudang resmi. Nilai ini
    | boleh dipakai untuk tarif dan label, tetapi jangan di-hardcode di Blade.
    |
    */

    'origin' => [
        'city' => env('SHIPPING_ORIGIN_CITY', 'Bandung'),
        'postal_code' => env('SHIPPING_ORIGIN_POSTAL_CODE', '40132'),
        'area_id' => env('BITESHIP_ORIGIN_AREA_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tarif flat fallback
    |--------------------------------------------------------------------------
    |
    | Dipakai saat provider gagal atau tidak mengembalikan layanan. Checkout
    | tetap bisa berjalan dengan tarif ini.
    |
    */

    'flat' => [
        'courier' => env('SHIPPING_FLAT_COURIER', 'Flat'),
        'service' => env('SHIPPING_FLAT_SERVICE', 'REG'),
        'description' => env('SHIPPING_FLAT_DESCRIPTION', 'Tarif flat'),
        'cost' => (int) env('SHIPPING_FLAT_COST', 15000),
        'etd' => env('SHIPPING_FLAT_ETD', '2-4 hari'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fake provider
    |--------------------------------------------------------------------------
    |
    | Tarif deterministik untuk lokal, demo, dan test. Satu kg pertama
    | memakai base_cost, kg berikutnya ditambah per_kg.
    |
    */

    'fake' => [
        'couriers' => [
            [
                'courier' => 'JNE',
                'service' => 'REG',
                'description' => 'Reguler',
                'base_cost' => 9000,
                'per_kg' => 2500,
                'etd' => '2-3 hari',
            ],
            [
                'courier' => 'JNE',
                'service' => 'YES',
                'description' => 'Next day',
                'base_cost' => 18000,
                'per_kg' => 4000,
                'etd' => '1 hari',
            ],
            [
                'courier' => 'SiCepat',
                'service' => 'REG',
                'description' => 'Reguler',
                'base_cost' => 8500,
                'per_kg' => 2500,
                'etd' => '2-3 hari',
            ],
        ],
    ],

];
