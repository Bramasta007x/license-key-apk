<?php

return [

    'paths' => [
        'api/*',
        'storage/*',
        'public/storage/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://127.0.0.1:3000',
        'http://localhost:3000',
        'https://fe.eticket-jayapura.com',
         env('FRONTEND_URL', 'https://jayapuramusicfest.com'),
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => true,

];
