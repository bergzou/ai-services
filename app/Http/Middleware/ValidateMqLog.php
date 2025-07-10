<?php

namespace App\Http\Middleware;

use App\Services\Queue\MqLogService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ValidateMqLog
{
    public function handle(Request $request, \Closure $next, ...$guards)
    {
        $params = $request->all();
        $service = new MqLogService();
        $service->validateMqLog($params);

        $response = $next($request);
        if($response instanceof JsonResponse){
            $responseData = $response->getData(true);
            if($responseData['code'] == 200){
                $service->saveValidateMqLog($params);//保存日志
            }
        }

        return $response;
    }
}
