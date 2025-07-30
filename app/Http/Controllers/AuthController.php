<?php

namespace App\Http\Controllers;


use App\Helpers\AopProxy;
use App\Libraries\Response;
use App\Services\Common\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $params = $request->all();

        $services = AopProxy::make(AuthService::class);

        $result = $services->register($params);

        return Response::success($result);

    }


    public function login(Request $request)
    {
        $params = $request->all();

        $services = new AuthService();

        $result = $services->login($params);

        return Response::success($result);

    }


    public function loginWithMobile(Request $request)
    {
        $params = $request->all();

        $services = new AuthService();

        $result = $services->loginWithMobile($params);

        return Response::success($result);
    }


    public function socialRedirect($provider, Request $request)
    {
        $params = $request->all();

        $services = new AuthService();

        $result = $services->login($params);

        return Response::success($result);
    }

    public function socialCallback($provider, Request $request)
    {
        $params = $request->all();

        $services = new AuthService();

        $result = $services->login($params);

        return Response::success($result);
    }


    public function logout(Request $request)
    {
        $params = $request->all();

        $services = new AuthService();

        $result = $services->login($params);

        return Response::success($result);
    }

}