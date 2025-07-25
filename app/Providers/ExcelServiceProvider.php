<?php

namespace App\Providers;

use App\Services\Excel\ExcelManager;
use Illuminate\Support\ServiceProvider;

class ExcelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('excel', function ($app) {
            $driver = $parameters['driver'] ?? null;
            return new ExcelManager($driver);
        });
    }

    public function boot()
    {

    }
}