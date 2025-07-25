<?php

namespace App\Services\Captcha\Drivers;

class ClickCaptchaDriver extends CaptchaDriver
{
    public function generate(): array
    {
        $key = $this->generateKey();
        $points = $this->generatePoints();

        $this->store($key, json_encode($points));

        return [
            'key' => $key,
            'image' => 'base64_encoded_image_data', // 实际实现需要生成图片
            'points' => $points,
            'type' => 'click'
        ];
    }

    protected function generatePoints(): array
    {
        $points = [];
        $targetCount = rand(3, 5);

        for ($i = 0; $i < $targetCount; $i++) {
            $points[] = [
                'x' => rand(10, 90),
                'y' => rand(10, 90),
                'text' => chr(rand(65, 90)) // A-Z
            ];
        }

        return $points;
    }

    public function validate(string $key, string $value): bool
    {
        $stored = json_decode($this->retrieve($key), true);
        $this->forget($key);

        if (!$stored) return false;

        $submitted = json_decode($value, true);
        if (count($stored) !== count($submitted)) return false;

        foreach ($stored as $point) {
            $found = false;
            foreach ($submitted as $sub) {
                if (abs($point['x'] - $sub['x']) <= 5 &&
                    abs($point['y'] - $sub['y']) <= 5 &&
                    $point['text'] === $sub['text']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) return false;
        }

        return true;
    }
}