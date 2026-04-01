<?php

return [
    'is_production' => env('MIDTRANS_PRODUCTION', false),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    // Rekening bank untuk manual transfer -> NEED CEK!!
    'bank_accounts' => [
        [
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'PT Eficon Indonesia',
        ],
        [
            'bank_name' => 'Mandiri',
            'account_number' => '0987654321',
            'account_name' => 'PT Eficon Indonesia',
        ],
        [
            'bank_name' => 'BNI',
            'account_number' => '1122334455',
            'account_name' => 'PT Eficon Indonesia',
        ],
    ],
];
