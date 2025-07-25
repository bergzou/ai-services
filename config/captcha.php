<?php

return [
    'default' => env('CAPTCHA_DRIVER', 'text'),

    'drivers' => [
        'text' => \App\Services\Captcha\Drivers\TextCaptchaDriver::class,
        'image' => \App\Services\Captcha\Drivers\ImageCaptchaDriver::class,
        'slide' => \App\Services\Captcha\Drivers\SlideCaptchaDriver::class,
        'click' => \App\Services\Captcha\Drivers\ClickCaptchaDriver::class,
        'sms' => \App\Services\Captcha\Drivers\SmsCaptchaDriver::class,
    ],

    'ttl' => 300, // 验证码有效期（秒）

    // 短信验证码配置
    'sms' => [
        'template' => '您的验证码是：{code}，有效期5分钟',
    ],
];