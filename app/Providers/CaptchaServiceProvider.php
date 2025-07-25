<?php

namespace App\Providers;

use App\Services\Captcha\CaptchaManager;
use Illuminate\Support\ServiceProvider;

class CaptchaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('captcha', function ($app) {
            $driver = $parameters['driver'] ?? null;
            return new CaptchaManager($driver);
        });
    }

    public function boot()
    {

    }
}