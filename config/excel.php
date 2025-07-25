<?php

return [
    'default' => 'phpspreadsheet',

    'drivers' => [
        'phpspreadsheet' => [
            'driver' => \App\Services\Common\Excel\Drivers\PhpSpreadsheetDriver::class,
        ],

        'spout' => [
            'driver' => \App\Services\Common\Excel\Drivers\SpoutDriver::class,
        ],

        'vtiful' => [
            'driver' => \App\Services\Common\Excel\Drivers\VtifulDriver::class,
            'path' => storage_path('exports'), // Vtiful专用配置
        ],
    ],
];
