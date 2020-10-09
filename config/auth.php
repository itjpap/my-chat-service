<?php declare(strict_types=1);
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => '123456789',
    ],
    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ]
    ]
];
