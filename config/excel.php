<?php

return [
    'default' => 'phpspreadsheet',

    'drivers' => [
        'phpspreadsheet' => [
            'driver' => \App\Services\Excel\Drivers\PhpSpreadsheetDriver::class,
        ],

        'spout' => [
            'driver' => \App\Services\Excel\Drivers\SpoutDriver::class,
        ],

        'vtiful' => [
            'driver' => \App\Services\Excel\Drivers\VtifulDriver::class,
            'path' => storage_path('exports'), // Vtiful专用配置
        ],
    ],
];
