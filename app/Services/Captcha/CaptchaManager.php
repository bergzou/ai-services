<?php

namespace App\Services\Captcha;

use App\Interfaces\CaptchaInterface;
use InvalidArgumentException;

class CaptchaManager implements CaptchaInterface
{
    protected $driver;
    protected array $drivers = [];


    public function __construct(string $driver = null)
    {
        $driver = $driver ?: config('captcha.default');

        $this->driver($driver);
    }


    public function driver(string $driver): self
    {

        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }


        $this->driver = $this->drivers[$driver];
        return $this;
    }

    protected function createDriver(string $driver)
    {
        $config = config("captcha.drivers.{$driver}");


        if (empty($config)) {
            throw new InvalidArgumentException("验证码驱动 [{$driver}] 未在配置中定义");
        }


        $driverClass = $config['driver'];


        if (!class_exists($driverClass)) {
            throw new InvalidArgumentException("验证码驱动类 [{$driverClass}] 不存在");
        }

        return app($driverClass);
    }

    public function generate(): array
    {
        return $this->driver->generate();
    }

    public function validate(string $key, string $value): bool
    {
        return $this->driver->validate($key, $value);
    }
}