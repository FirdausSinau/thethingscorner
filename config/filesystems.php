<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Disk default dipakai kalau kode tidak memilih disk secara eksplisit.
    | Untuk kerja bersama dengan Neon, isi FILESYSTEM_DISK=neon di .env.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Product Images Disk
    |--------------------------------------------------------------------------
    |
    | "public" menyimpan foto di storage/app/public. "neon" menyimpan foto
    | di Neon Object Storage. Default "neon" mengikuti database bersama.
    | Untuk development tanpa Neon, isi PRODUCT_IMAGES_DISK=public.
    |
    */

    'product_images_disk' => env('PRODUCT_IMAGES_DISK', 'neon'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * Neon Object Storage.
         * Kunci NEON_STORAGE_* dipakai lebih dulu. Kalau tidak diisi, disk ini
         * memakai kunci AWS_* supaya kompatibel dengan konfigurasi lama.
         */
        'neon' => [
            'driver' => 's3',
            'key' => env('NEON_STORAGE_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('NEON_STORAGE_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('NEON_STORAGE_REGION', env('AWS_DEFAULT_REGION', 'ap-southeast-1')),
            'bucket' => env('NEON_STORAGE_BUCKET', env('AWS_BUCKET')),
            'endpoint' => env('NEON_STORAGE_ENDPOINT', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => true,
            'throw' => true,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID', env('NEON_STORAGE_ACCESS_KEY_ID')),
            'secret' => env('AWS_SECRET_ACCESS_KEY', env('NEON_STORAGE_SECRET_ACCESS_KEY')),
            'region' => env('AWS_DEFAULT_REGION', env('NEON_STORAGE_REGION', 'ap-southeast-1')),
            'bucket' => env('AWS_BUCKET', env('NEON_STORAGE_BUCKET')),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT_URL_S3', env('AWS_ENDPOINT', env('NEON_STORAGE_ENDPOINT'))),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'throw' => true,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
