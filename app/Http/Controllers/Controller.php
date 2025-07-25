<?php

namespace App\Http\Controllers;

use App\Libraries\Response;
use App\Services\Common\Captcha\CaptchaManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;


class Controller extends BaseController
{

    public function test(Request $request){
        $params = $request->all();


        /** @var \App\Services\Common\Sms\SmsManager $sms */
        $sms = App::make('sms');
        $res  = $sms->templateSingleSend('13800000000', 'sms_template', ['code' => '1234']);
        var_dump($res);
        die;

        return Response::success([]);
    }

}
