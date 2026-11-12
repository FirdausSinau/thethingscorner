<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas waktu reservasi pembayaran
    |--------------------------------------------------------------------------
    |
    | Satuannya detik. Default produksi:
    | QRIS, VA, dan e-wallet 30 menit; transfer manual 60 menit.
    |
    | Sementara QRIS dipakai 30 detik untuk pengujian. Kembalikan ke
    | 1800 detik (30 menit) sebelum produksi lewat .env.
    |
    */

    'reservations' => [
        'qris_seconds' => (int) env('QRIS_RESERVATION_SECONDS', 30),
        'va_seconds' => (int) env('VA_RESERVATION_SECONDS', 1800),
        'ewallet_seconds' => (int) env('EWALLET_RESERVATION_SECONDS', 1800),
        'manual_transfer_seconds' => (int) env('MANUAL_TRANSFER_RESERVATION_SECONDS', 3600),
    ],

];
