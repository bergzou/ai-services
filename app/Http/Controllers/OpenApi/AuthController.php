<?php

namespace App\Http\Controllers\OpenApi;

use App\Http\Controllers\Controller;
use App\Libraries\Response;
use App\Services\AuthService;
use App\Services\Captcha\CaptchaService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // 生成验证码（对应路由：POST open/api/captcha）
    public function captcha(Request $request)
    {
        $params = $request->all();

        $service = new CaptchaService();

        $result = $service->generate();


        return Response::success([$result]);
    }

    // 发送短信验证码（对应路由：POST open/api/send/sms）
    public function sendBySMS(Request $request)
    {
        $params = $request->all();

        $service = new AuthService();

        $result = $service->sendSMS($params); // 假设服务方法名为 sendSMS

        return Response::success($result);

    }

    // 用户名注册（对应路由：POST open/api/register/username）
    public function registerByUsername(Request $request)
    {

        $params = $request->all();

        $service = new AuthService();

        $result = $service->registerByUsername($params); // 假设服务方法名为 registerByUsername

        return Response::success($result);

    }

    // 手机号注册（对应路由：POST open/api/register/mobile）
    public function registerByMobile(Request $request)
    {

        $params = $request->all();

        $service = new AuthService();

        $result = $service->registerByMobile($params); // 假设服务方法名为 registerByMobile

        return Response::success($result);

    }

    // 用户名登录（对应路由：POST open/api/login/username）
    public function loginByUsername(Request $request)
    {

        $params = $request->all();

        $service = new AuthService();

        $result = $service->loginByUsername($params); // 假设服务方法名为 loginByUsername

        return Response::success($result);
    }

    // 手机号登录（对应路由：POST open/api/login/mobile）
    public function loginByMobile(Request $request)
    {
        $params = $request->all();

        $service = new AuthService();

        $result = $service->loginByMobile($params); // 假设服务方法名为 loginByMobile

        return Response::success($result);
    }
}