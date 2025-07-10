<?php

namespace App\Http\Controllers;

use App\Services\Queue\QueueDetailConfigService;
use App\Services\Queue\QueueDetailService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;

class Controller extends BaseController
{
    public function index()
    {
        Redis::set('name','name');
        var_dump( Redis::get('name') . 'redis');
        die;
    }

}
