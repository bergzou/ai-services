<?php

/**
 * 基础数据站点
 */
namespace App\Client;

use App\Exceptions\BusinessException;
use App\Libraries\Curl;
use Illuminate\Support\Facades\Lang;

class BaseClient
{
    public string $host = '';

    protected string $prefix = '';
    // 发送请求
    public  function sendClient($url,$method,$data = [],$isDealResult = true, $headers = array("Content-Type: application/json;charset=UTF-8"), $timeout = 30, $maxRetries = 0, $retryDelay = 0,$https=true): mixed
    {

        $result =  Curl::sendRequest($url,$method,$data,$headers,$timeout,$maxRetries,$retryDelay,$https);
        if($isDealResult){
            // 服务请求为空
            if (empty($result))  throw new BusinessException("no data");
            $result = json_decode($result,true);
        }
        return $result;
    }

    public function sendFile($url, $filePath, $fileName)
    {
        $result = Curl::sendFile($url, $filePath, $fileName);
        if (empty($result)) throw new BusinessException("no data");
        return json_decode($result, true);
    }

}
