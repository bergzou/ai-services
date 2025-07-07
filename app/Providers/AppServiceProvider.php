<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 所有SQL执行记录
        DB::listen(function ($query) {
            $tmp = str_replace('?', '"'.'%s'.'"', $query->sql);
            $qBindings = [];
            foreach ($query->bindings as $key => $value) {
                if (is_numeric($key)) {
                    $qBindings[] = $value;
                } else {
                    $tmp = str_replace(':'.$key, '"'.$value.'"', $tmp);
                }
            }
            $tmp = vsprintf($tmp, $qBindings);
            $sql = str_replace("\\", "", $tmp);
            $executionTime  =  '执行时间 :'.$query->time . 'ms';
            Log::channel('sql')->info($executionTime,[$sql]);
        });
    }
}
