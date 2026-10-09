<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default payment driver
    |--------------------------------------------------------------------------
    | Nilai: 'manual_transfer' | 'midtrans' | 'xendit'
    | Dipakai oleh PaymentGatewayInterface untuk memilih implementasi.
    */
    'driver' => env('PAYMENT_DRIVER', 'manual_transfer'),

    /*
    |--------------------------------------------------------------------------
    | Manual Transfer — data rekening tujuan
    |--------------------------------------------------------------------------
    */
    'manual' => [
        'bank'           => env('PAYMENT_MANUAL_BANK', 'BCA'),
        'account_number' => env('PAYMENT_MANUAL_ACCOUNT', '1234567890'),
        'account_name'   => env('PAYMENT_MANUAL_NAME', 'Boboin Villa'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans (isi saat Fase 5)
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
        'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

];
