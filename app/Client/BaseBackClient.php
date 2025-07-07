<?php

/**
 * 统一单据-站点
 */
namespace App\Client;

use App\Exceptions\BusinessException;
use App\Libraries\Curl;
use App\Libraries\LibLang;

class BaseBackClient
{

    /**
     * Notes: 发送请求并处理
     * Date: 2024/3/26 11:03
     * @param $url
     * @param $method
     * @param $data
     * @param $isDealResult
     * @param $headers
     * @param $timeout
     * @param $maxRetries
     * @param $retryDelay
     * @param $https
     * @return array|mixed|null
     */
    public  function sendClient($url,$method,$data = [],$isDealResult = true, $headers = array("Content-Type: application/json;charset=UTF-8"), $timeout = 30, $maxRetries = 0, $retryDelay = 0,$https=true){

        $result =  Curl::sendRequest($url,$method,$data,$headers,$timeout,$maxRetries,$retryDelay,$https);
        if($isDealResult){
            // 服务请求为空
            if (empty($result))  throw new BusinessException(LibLang::web(50200));
            $result = json_decode($result,true);
        }
        return $result;
    }
}
