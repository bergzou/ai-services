<?php

namespace App\Providers;

use App\Services\UserInfoService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     * 注册应用服务
     * @return void
     */
    public function register()
    {
        $this->app->bind('user', function ($app) {
            return new UserInfoService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }


    /**
     * DeferrableProvider 延迟加载，需指定别名
     * @return string[]
     */
    public function provides(): array
    {
        return [UserInfoService::class => 'user'];
    }
}
