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
        'http://localhost:5173',
        'https://dev.efisienin.com',
        'https://defisienin.com',
        'https://efisienin.com',
         env('FRONTEND_URL', 'https://jayapuramusicfest.com'),
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => true,

];
