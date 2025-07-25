<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function send($mobile)
    {
        $code = rand(1000, 9999);
        Cache::put('sms_'.$mobile, $code, now()->addMinutes(5));

        // 实际项目中使用短信网关
        Log::info("发送短信验证码到 {$mobile}: {$code}");

        return $code;
    }

    public static function verify($mobile, $code)
    {
        $storedCode = Cache::pull('sms_'.$mobile);
        return $storedCode && $storedCode == $code;
    }
}