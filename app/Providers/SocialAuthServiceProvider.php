<?php

namespace App\Providers;



use App\Services\Common\Auth\SocialAuthManager;
use Illuminate\Support\ServiceProvider;

class SocialAuthServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton('social_auth', function ($app) {
            return new SocialAuthManager($app);
        });
    }


    public function boot()
    {
        // 配置第三方服务
        $this->app->bind('social_auth.wechat', function ($app) {
            return $app['social_auth']->driver('wechat');
        });

        $this->app->bind('social_auth.alipay', function ($app) {
            return $app['social_auth']->driver('alipay');
        });

        $this->app->bind('social_auth.xiaohongshu', function ($app) {
            return $app['social_auth']->driver('xiaohongshu');
        });
    }
}
