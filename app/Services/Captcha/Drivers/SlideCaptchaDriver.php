<?php

namespace App\Services\Captcha\Drivers;

class SlideCaptchaDriver extends CaptchaDriver
{
    public function generate(): array
    {
        $key = $this->generateKey();
        $position = rand(10, 90); // 滑块位置百分比

        $this->store($key, $position);

        return [
            'key' => $key,
            'position' => $position,
            'type' => 'slide'
        ];
    }

    public function validate(string $key, string $value): bool
    {
        $stored = $this->retrieve($key);
        $this->forget($key);

        return $stored && abs($stored - (int)$value) <= 3; // 允许3%误差
    }
}