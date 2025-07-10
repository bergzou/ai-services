<?php

namespace App\Services\Queue;

use App\Libraries\Common;
use App\Libraries\LibSnowflake;

class QueueDetailValidateService
{

    public static function validateMqLog($request){

        $mqLogService = new MqLogService();
        $mqValidate = $mqLogService->validateMqLog($request);
        if($mqValidate['code'] !=200){
            throw new \Exception($mqValidate['msg'],$mqValidate['code']);
        }

        return $mqValidate;
    }

    //保存消费日志
    public static function saveValidateMqLog($request){
        $LibSnowflake  = new LibSnowflake(Common::getWorkerId());
        $mqInfo = [
            'msg_id' => $request['msgId'],
            'mq_log_id' => $LibSnowflake->next(),
        ];
        $mqService = new MqLogService();
        return $mqService->addMqLog($mqInfo);
    }

}