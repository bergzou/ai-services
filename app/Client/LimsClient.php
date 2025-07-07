<?php

/**
 * 物流
 */
namespace App\Client;

use App\Exceptions\BusinessException;

class LimsClient extends  BaseClient
{

    public function __construct()
    {
        $this->host = env('INTRANET_URL','http://inner-test.tomatocross.cn:18888').'/lims';
    }

    //尾程预报
    public function shippingForecast($data): array
    {
        $url = $this->host.'/inner/base/shipping/forecast';
        $result = $this->sendClient($url,'POST',json_encode($data));
        return $result;
    }

    public function shippingCancel($data): array
    {
        $url = $this->host.'/inner/base/shipping/cancel';
        $result = $this->sendClient($url,'POST',json_encode($data));
        return $result;
    }

    protected function formatResponse($result)
    {
        $result = is_string($result) ? json_decode($result, true) : $result;
        if (!isset($result['isSuccess'])) {
            throw new BusinessException('响应失败:' . json_encode($result, JSON_UNESCAPED_UNICODE));
        }
        if (!$result['isSuccess']) {
            throw new BusinessException($result['msg']);
        }
        return $result['data'];
    }

}
