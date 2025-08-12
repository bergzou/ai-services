<?php

namespace App\Providers;

use App\Services\Common\AiModel\AiModelManager;
use Illuminate\Support\ServiceProvider;


class AiModelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AiModelManager::class, function ($app) {
            $config = config('ai_model');
            return new AiModelManager($config);
        });
    }
}
