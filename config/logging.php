<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\JsonFormatter;
return [



    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    'sql_enabled' => env('LOG_SQL_ENABLED', false), // 是否启用 SQL 日志记录

    'exception_enabled' => env('LOG_EXCEPTION_ENABLED', false), // 是否启用异常日志记录

    'aop_enabled' => env('AOP_LOG_ENABLED', false), // 是否启用 AOP 日志记录

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'permission' => 0777,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'permission' => 0777,
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
            'permission' => 0777,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'permission' => 0777,
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'permission' => 0777,
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'permission' => 0777
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'permission' => 0777
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
            'permission' => 0777,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
            'permission' => 0777,
        ],

        // 业务日志
        'aop' => [
            'driver' => 'daily',
            'path' => storage_path('logs/aop.log'),
            'level' => 'info',
            'days' => 7, // 保留 7 天日志,
        ],
        // SQL日志
        'sql' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sql.log'),
            'level' => 'debug',
            'days' => 7, // 保留 7 天日志
        ],

        // 异常日志
        'exception' => [
            'driver' => 'daily',
            'path' => storage_path('logs/exception.log'),
            'level' => 'debug',
            'days' => 7, // 保留 7 天日志
        ],

    ],

];
