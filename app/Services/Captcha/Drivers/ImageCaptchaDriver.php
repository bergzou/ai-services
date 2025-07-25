<?php

namespace App\Services\Captcha\Drivers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageCaptchaDriver extends CaptchaDriver
{
    public function generate(): array
    {
        $key = $this->generateKey();
        $code = $this->generateCode(6);

        $this->store($key, $code);

        $image = $this->createImage($code);

        return [
            'key' => $key,
            'image' => $image->encode('data-url')->encoded,
            'type' => 'image'
        ];
    }

    protected function generateCode(int $length): string
    {
        return substr(str_shuffle('123456789ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, $length);
    }

    protected function createImage(string $code)
    {
        return Image::canvas(120, 40, '#f5f5f5')
            ->text($code, 60, 20, function($font) {
                $font->file(resource_path('fonts/Roboto-Bold.ttf'));
                $font->size(24);
                $font->color('#333');
                $font->align('center');
                $font->valign('middle');
            })
            ->rectangle(5, 5, 115, 35, function ($draw) {
                $draw->border(1, '#ddd');
            });
    }

    public function validate(string $key, string $value): bool
    {
        $stored = $this->retrieve($key);
        $this->forget($key);

        return $stored && strtolower($stored) === strtolower($value);
    }
}