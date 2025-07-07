<?php

namespace App\Http\Middleware;


use App\Libraries\Response;
use Illuminate\Http\Request;

class OpenApi
{

    public function handle(Request $request, \Closure $next, ...$guards)
    {
        $platform = $request->header('platform','');

        $allowPlatforms = ['xhs','temu'];
        if(empty($platform)){
            return Response::fail('平台参数不能为空');
        }
        $platform = strtolower($platform);
        if(!in_array($platform,$allowPlatforms)){
            return Response::fail('不支持当前平台');
        }

        $sellerCode = $request->header('sellercode','');
        if(empty($sellerCode)){
            return Response::fail('开放平台卖家代码不能为空');
        }
        $request->merge(['seller_code'=>$sellerCode]);

        
        return $next($request);
    }

}
