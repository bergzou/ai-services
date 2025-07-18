<?php

namespace App\Providers;

use App\Services\Excel\ExcelManager;
use Illuminate\Support\ServiceProvider;

class ExcelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('excel', function ($app) {
            // 注册名为 'excel' 的单例服务（全局仅创建一次实例）
            // 当其他类通过依赖注入或 app('excel') 获取时，返回 ExcelManager 实例
            return new ExcelManager();
        });
    }

    public function boot()
    {

    }
}