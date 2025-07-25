<?php

namespace App\Services\Captcha\Drivers;

class TextCaptchaDriver extends CaptchaDriver
{
    public function generate(): array
    {
        $key = $this->generateKey();
        $code = rand(1000, 9999);

        $this->store($key, $code);

        return [
            'key' => $key,
            'code' => $code,
            'type' => 'text'
        ];
    }

    public function validate(string $key, string $value): bool
    {
        $stored = $this->retrieve($key);
        $this->forget($key);

        return $stored && $stored == $value;
    }
}