<?php

namespace App\Providers;

use App\Guards\JwtGuard;
use App\Services\JwtService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        // 注册自定义守卫
        Auth::extend('custom_jwt', function ($app, $name, array $config) {
            return new JwtGuard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app->make(JwtService::class)
            );
        });
    }

    public function register()
    {
        $this->app->singleton(JwtService::class, function ($app) {
            return new JwtService();
        });
    }
}