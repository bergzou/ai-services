<?php

namespace App\Providers;

use App\Logging\SqlLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * 注册应用的事件监听器
     * @return void
     * @throws BindingResolutionException
     */
    public function boot()
    {
        // 检查SQL日志开关配置（对应 .env 中的 LOG_SQL_ENABLED 或 config/logging.php 中的 sql_enabled）
        if (config('logging.sql_enabled')) {
            // 注册SQL查询事件监听器：当查询执行时触发日志记录
            $this->app->make('events')->listen(
                QueryExecuted::class,  // 要监听的事件类（Laravel数据库查询执行后触发）
                [SqlLogger::class, 'handle']  // 事件处理器（SqlLogger类的handle方法负责记录日志）
            );
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
