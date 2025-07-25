<?php

return [
    'default' => env('CAPTCHA_DRIVER', 'text'),
    'drivers' => [
        'text' => [
            'driver' => \App\Services\Captcha\Drivers\TextCaptchaDriver::class,
            'width' => 160,
            'height' => 50,
            'characters' => 4,
            'expire' => 60,
        ],
        'image' => [
            'driver' => \App\Services\Captcha\Drivers\ImageCaptchaDriver::class,
        ],
        'slide' => [
            'driver' => \App\Services\Captcha\Drivers\SlideCaptchaDriver::class,
        ],
        'click' => [
            'driver' => \App\Services\Captcha\Drivers\ClickCaptchaDriver::class,
        ],
        'sms' => [
            'driver' => \App\Services\Captcha\Drivers\SmsCaptchaDriver::class,
        ],
    ],
];