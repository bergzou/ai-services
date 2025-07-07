<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\JsonFormatter;
return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

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
        # HTTP POST 请求日志
        'request' => [
            'driver' => 'daily',
            'path' => storage_path('logs/request.log'),
            'level' => 'info',
            'days' => 3,
            'handler' => StreamHandler::class,
            'formatter' => JsonFormatter::class,
            'value_max_length' => env('REQUEST_LOG_VALUE_MAX_LENGTH', 300),
            'permission' => 0777,
        ],
        # 队列消费日志
        'queue' => [
            'driver' => 'daily',
            'path' => storage_path('logs/queue.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        # cron 定时任务日志 apollo
        'apollo' => [
            'driver' => 'daily',
            'path' => storage_path('logs/apollo.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        # SQL 执行日志
        'sql' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sql.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        # businessException 自定义异常
        'business' => [
            'driver' => 'daily',
            'path' => storage_path('logs/business.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        # throwable 自定义异常
        'throwable' => [
            'driver' => 'daily',
            'path' => storage_path('logs/throwable.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        # 上传 curl_upload
        'curl_upload' => [
            'driver' => 'daily',
            'path' => storage_path('logs/curl_upload.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        # 内部服务调用
        'client_request' => [
            'driver' => 'daily',
            'path' => storage_path('logs/client_request.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        'internal_request' => [
            'driver' => 'daily',
            'path' => storage_path('logs/internal_request.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],
        'business_log' => [
            'driver' => 'daily',
            'path' => storage_path('logs/business_log.log'),
            'level' => 'info',
            'days' => 3,
            'permission' => 0777,
        ],

        // 添加新通道
        'cost_forecast_log' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cost_forecast_log.log'),
            'formatter' => App\Logging\CostForecastLogFormatter::class,
            'formatter_with' => [
                'lineWidth' => 80 // 可配置宽度
            ],
            'level' => 'debug',
            'days' => 30,
        ],

    ],

];
