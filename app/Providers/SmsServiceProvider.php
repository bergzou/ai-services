<?php

namespace App\Providers;

use App\Services\Common\Sms\SmsManager;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('sms', function ($app) {
            $driver = $parameters['driver'] ?? null;
            return new SmsManager($driver);
        });
    }

    public function boot()
    {

    }
}