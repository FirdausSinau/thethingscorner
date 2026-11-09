<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Integrations
    |--------------------------------------------------------------------------
    |
    | Fase 1 memakai fake adapter secara default. Ganti driver ke provider
    | asli setelah akun sandbox tersedia.
    |
    */

    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'fake'),
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        ],
        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY'),
            'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
        ],
    ],

    'shipping' => [
        'driver' => env('SHIPPING_DRIVER', 'fake'),
        'biteship' => [
            'api_key' => env('BITESHIP_API_KEY'),
            'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID'),
        ],
    ],

    'whatsapp' => [
        'admin_number' => env('WHATSAPP_ADMIN_NUMBER'),
    ],

];
