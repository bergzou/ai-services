<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'system_users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'system_users',
        ],

        'api' => [
            'driver' => 'custom_jwt',
            'provider' => 'system_users',
        ],
    ],

    'providers' => [
        'system_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\SystemUser::class,
        ],
    ],

    'passwords' => [
        'system_users' => [
            'provider' => 'system_users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
];