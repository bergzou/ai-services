<?php

namespace App\Services\Captcha\Drivers;

use App\Interfaces\CaptchaInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class CaptchaDriver implements CaptchaInterface
{
    protected string $cacheKeyPrefix = 'captcha_';
    protected int $ttl = 300; // 5分钟有效期

    protected function store(string $key, $value): void
    {
        Cache::put($this->cacheKeyPrefix . $key, $value, $this->ttl);
    }

    protected function retrieve(string $key)
    {
        return Cache::get($this->cacheKeyPrefix . $key);
    }

    protected function forget(string $key): void
    {
        Cache::forget($this->cacheKeyPrefix . $key);
    }

    protected function generateKey(): string
    {
        return Str::random(40);
    }
}