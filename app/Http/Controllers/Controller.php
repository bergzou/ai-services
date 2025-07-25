<?php

namespace App\Http\Controllers;

use App\Libraries\Response;
use App\Services\Captcha\CaptchaManager;
use App\Services\Excel\ExcelManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;


class Controller extends BaseController
{

    public function test(Request $request){
        $params = $request->all();

        /** @var CaptchaManager $captcha */
        $captcha = App::make('captcha');
//        $res = $captcha->generate();
//        var_dump($res);die;

        $params['captcha_key'] = 'captcha_27551983535093461';
        $params['captcha_value'] = '6AF4';
        $res = $captcha->validate($params);
        var_dump($res);die;

        return Response::success([]);
    }

}
