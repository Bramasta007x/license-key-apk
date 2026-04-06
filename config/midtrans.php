<?php

return [
    'is_production' => env('MIDTRANS_PRODUCTION', false),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    // Rekening bank untuk manual transfer -> NEED CEK!!
    'bank_accounts' => [
        [
            'bank_name' => 'BCA',
            'account_number' => '788-0873398',
            'account_name' => 'JOZZ ABADI SENTOSA CV',
        ]
    ],
];
