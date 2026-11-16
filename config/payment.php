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

    /*
    |--------------------------------------------------------------------------
    | Transfer manual
    |--------------------------------------------------------------------------
    |
    | Data rekening masih placeholder. Ganti lewat .env setelah client
    | memberikan rekening resmi. Jangan menulis rekening di Blade.
    |
    */

    'manual' => [
        'bank_name' => env('PAYMENT_MANUAL_BANK_NAME', 'Bank Contoh'),
        'account_number' => env('PAYMENT_MANUAL_ACCOUNT_NUMBER', '0000000000'),
        'account_holder' => env('PAYMENT_MANUAL_ACCOUNT_HOLDER', 'Thethingscorner'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bukti transfer
    |--------------------------------------------------------------------------
    |
    | Disk privat, bukan public. File hanya boleh diakses lewat controller
    | yang memeriksa pemilik order atau admin.
    |
    */

    'proof' => [
        'disk' => env('PAYMENT_PROOF_DISK', 'local'),
        'max_kb' => (int) env('PAYMENT_PROOF_MAX_KB', 2048),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fake gateway
    |--------------------------------------------------------------------------
    |
    | Hanya untuk lokal dan demo. Matikan di produksi. Tombol simulasi
    | pembayaran tidak boleh muncul saat nilainya false.
    |
    */

    'fake' => [
        'enabled' => filter_var(env('PAYMENT_FAKE_ENABLED', false), FILTER_VALIDATE_BOOL),
    ],

];
