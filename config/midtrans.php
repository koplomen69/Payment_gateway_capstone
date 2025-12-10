<?php

return [
    // Kredensial Midtrans (dari .env untuk keamanan)
    'server_key' => env('MIDTRANS_SERVER_KEY', 'default-server-key-jika-tidak-ada'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'default-client-key-jika-tidak-ada'),

    // Setting mode (sandbox/production)
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false), // false untuk sandbox

    // Setting tambahan untuk keamanan Midtrans
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    // Setting lain jika diperlukan, misalnya webhook URL
    'webhook_url' => env('MIDTRANS_WEBHOOK_URL', '/transactions/midtrans-notification'),
];
