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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    | Google Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google APIs used in the application.
    | This is used by the AttendanceController for Google Sheets integration.
    |
    */

    'google' => [
        'credentials_path' => storage_path('app/server-absensi-credentials.json'), // <--- UBAH MENJADI INI
        'timeout' => env('GOOGLE_API_TIMEOUT', 10),
        'retry_attempts' => env('GOOGLE_API_RETRY_ATTEMPTS', 2),
        'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID', '1JaQaEjtOUOJTO1I0jsGItnqMrrnso-v2S_vzQ4nqqcs'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp notification gateway (Fonnte, WaBlas, etc.)
    | Used for sending service report notifications.
    |
    */

    'wa' => [
        'gateway_url' => env('WA_GATEWAY_URL', 'https://api.fonnte.com/send'),
        'gateway_token' => env('WA_GATEWAY_TOKEN'),
        'service_admin_phone' => env('WA_SERVICE_ADMIN_PHONE'),
        'customer_default_phone' => env('WA_CUSTOMER_DEFAULT_PHONE'),
    ],

    'maintenance_url_token' => env('MAINTENANCE_URL_TOKEN'),
];
