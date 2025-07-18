<?php

return [
    'default' => 'phpspreadsheet',

    'drivers' => [
        'phpspreadsheet' => [
            'class' => \App\Services\Excel\Drivers\PhpSpreadsheetDriver::class,
        ],

        'spout' => [
            'class' => \App\Services\Excel\Drivers\SpoutDriver::class,
        ],

        'vtiful' => [
            'class' => \App\Services\Excel\Drivers\VtifulDriver::class,
            'path' => storage_path('exports'), // Vtiful专用配置
        ],
    ],
];