<?php

namespace App\Http\Middleware;

;

use App\Libraries\Logger;
use App\Libraries\Response;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class OmsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = apache_request_headers();
        //判断请求是否合法
        if((!isset($headers['Req-User-Id']) || !isset($headers['Req-User-Name']) || !isset($headers['Req-User-Mobile']) || !isset($headers['Req-From']))
            && getenv('APP_ENV') != 'local'){
            return Response::fail(Lang::get('web.50002'));
        }
        //设置语言
//        $set_locale = $headers['Req-Local-Language'] ?? 'zh-CN';
//        App::setLocale($set_locale);
        unset($headers['Req-Local-Language']);


        // 过滤所有输入参数
        $input = $request->all();
        array_walk_recursive($input, function (&$item, $key) {
            $item = trim($item);
        });

        $input['seller_code'] = $headers['Req-Seller-Code'] ?? '';//门户卖家隔离
        if (empty($input['seller_code'])) return Response::fail("卖家编码不允许为空");

        // 子代理卖家
        $input['seller_code_child'] = $headers['Req-Seller-Code-Child'] ?? [];
        if (!empty( $input['seller_code_child'])) $input['seller_code_child'] = explode(',', $input['seller_code_child']);



        $request->merge($input);
        $response = $next($request);
        // 记录请求日志
        if ($response instanceof JsonResponse){
            Logger::httpRequest($request,$response);
        }
        return $response;
    }





}
