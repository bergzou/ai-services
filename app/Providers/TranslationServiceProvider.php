<?php

namespace App\Providers;

use App\Services\Translation\TranslatorManager;
use Illuminate\Support\ServiceProvider;

/**
 * 翻译服务提供者：负责注册翻译服务的全局实例
 * 遵循 Laravel 服务提供者规范，用于管理翻译服务的依赖注入
 */
class TranslationServiceProvider extends ServiceProvider
{
    /**
     * 注册服务到容器（Laravel 服务注册阶段调用）
     * 在此绑定翻译服务的单例实例，确保全局唯一
     */
    public function register()
    {
        // 注册名为 'translator' 的单例服务（全局仅创建一次实例）
        // 当其他类通过依赖注入或 app('translator') 获取时，返回 TranslatorManager 实例
        $this->app->singleton('translation', function ($app) {
            return new TranslatorManager();
        });
    }

    /**
     * 引导服务（Laravel 服务引导阶段调用）
     * 可在此添加翻译服务的初始化逻辑（如加载配置、注册事件监听等）
     * 当前无具体实现，保留扩展空间
     */
    public function boot()
    {
        //
    }
}