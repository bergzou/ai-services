<?php

namespace App\Http\Middleware;

use App\Libraries\Logger;
use App\Libraries\Response;
use App\Service\UserInfoService;
use Illuminate\Http\Request;
use Exception;
class Internal
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @param mixed ...$guards
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ...$guards)
    {
        try {
            //设置用户信息
            $this->getUserInfo($request);
            $response = $next($request);

            Logger::internalRequest($request->url(),$request->all(),$response->original);

        }catch (\Throwable $e){
            return Response::fail($e->getMessage(),$e->getCode());
        }
        return $response;
    }

    /**
     * 设置登录用户信息
     * @param Request $request
     */
    private function getUserInfo(Request $request): void
    {
        $params = $request->toArray();

        $userInfo = [
            'user_id'            => $params['reqUserId']?? '',
            'user_name'          => $params['reqUserName']?? '',
            'user_mobile'        => $params['reqUserMobile']?? '',
            'tenant_code'        => $params['reqTenantCode']?? '',
            'seller_code'	     => $params['reqSellerCode']?? '',
            'manage_code'	     => $params['reqMangeCode']?? '',
            'region_code'        => $params['regionCode']?? '',
            'warehouse_code'     => $params['warehouseCode']?? '',
            'warehouse_code_arr' => $params['warehouseCodeArr']?? '',
            'city_name'          => $params['reqCityName']?? '',
        ];

        UserInfoService::setUserInfo($userInfo);
    }
}
