<?php

namespace App\Services\Captcha\Drivers;

class SmsCaptchaDriver extends CaptchaDriver
{
    public function generate(): array
    {
        $key = $this->generateKey();
        $code = rand(100000, 999999);

        $this->store($key, $code);

        // 实际应用中这里应该调用短信服务发送验证码
        // $this->sendSms($phone, $code);

        return [
            'key' => $key,
            'expires' => $this->ttl,
            'type' => 'sms'
        ];
    }

    public function validate(string $key, string $value): bool
    {
        $stored = $this->retrieve($key);
        $this->forget($key);

        return $stored && $stored == $value;
    }
}