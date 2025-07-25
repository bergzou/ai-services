<?php

return [

    'default' => env('SMS_DRIVER', 'yunpian'),

    'enabled' => env('SMS_ENABLED', false),

    'drivers' => [
        'aliyun' => [
            'driver' => \App\Services\Common\Sms\Drivers\AliyunSmsDriver::class,
            'access_key_id' => env('ALIYUN_SMS_ACCESS_KEY_ID'),
            'access_key_secret' => env('ALIYUN_SMS_ACCESS_KEY_SECRET'),
            'sign_name' => env('ALIYUN_SMS_SIGN_NAME'),
            'region_id' => env('ALIYUN_SMS_REGION_ID', 'cn-hangzhou'),
        ],

        'tencentcloud' => [
            'driver' => \App\Services\Common\Sms\Drivers\TencentSmsDriver::class,
            'secret_id' => env('TENCENTCLOUD_SMS_SECRET_ID'),
            'secret_key' => env('TENCENTCLOUD_SMS_SECRET_KEY'),
            'sdk_app_id' => env('TENCENTCLOUD_SMS_SDK_APP_ID'),
            'sign_name' => env('TENCENTCLOUD_SMS_SIGN_NAME'),
            'region' => env('TENCENTCLOUD_SMS_REGION', 'ap-guangzhou'),
        ],

        'jiguang' => [
            'driver' => \App\Services\Common\Sms\Drivers\JiGuangSmsDriver::class,
            'app_key' => env('JIGUANG_SMS_APP_KEY'),
            'master_secret' => env('JIGUANG_SMS_MASTER_SECRET'),
            'sign_id' => env('JIGUANG_SMS_SIGN_ID'),
        ],

        'yunpian' => [
            'driver' => \App\Services\Common\Sms\Drivers\YunPianSmsDriver::class,
            'domain' => env('YUNPIAN_SMS_DOMAIN', 'sms.yunpian.com'),
            'api_key' => env('YUNPIAN_SMS_API_KEY'),
            'sign_name' => env('YUNPIAN_SMS_SIGN_NAME'),
            'templates' => [
                'common' => env('YUNPIAN_SMS_TEMPLATE_COMMON_CODE','123456789') // 通用短信模板
            ],
        ],
    ],
];
