<?php

declare(strict_types=1);

return [
    'secret_cipher' => [
        'key' => env('PLATFORM_APP_SECRET_KEY', env('JWT_SECRET')),
        'cipher' => env('PLATFORM_APP_SECRET_CIPHER', 'aes-256-gcm'),
    ],
];
