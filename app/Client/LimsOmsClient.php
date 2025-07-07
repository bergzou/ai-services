<?php

/**
 * 物流代理系统
 */
namespace App\Client;

use App\Exceptions\BusinessException;

class LimsOmsClient extends  BaseClient
{

    public function __construct()
    {
        $this->host = env('INTRANET_URL','http://inner-test.tomatocross.cn:18888').'/limsoms';
//        $this->host = "127.0.0.1:8011";
    }

    //预报更新
    public function shippingForecastUpdate($params): array
    {
        $data = $params;
        $url = $this->host.'/internal/shipping/shippingForecastUpdate';
        return $this->sendClient($url,'POST',json_encode($data));
    }


    //操作节点更新
    public function shippingOperateUpdate($params){
        $data = $params;
        $url = $this->host.'/internal/shipping/shippingOperateUpdate';
        return $this->sendClient($url,'POST',json_encode($data));
    }

    //保存配送订单保存
    public function shippingUpdate($params): array
    {
        $data = $params;
        $url = $this->host.'/internal/shipping/shippingUpdate';
        return $this->sendClient($url,'POST',json_encode($data));
    }

    public function shippingInfo($params): array
    {
        $data = $params;
        $url = $this->host.'/internal/shipping/shippingInfo';
        return $this->sendClient($url,'POST',json_encode($data));
    }





}
