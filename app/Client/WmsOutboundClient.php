<?php

/**
 * 调用内部系统-站点
 */
namespace App\Client;

use App\Exceptions\BusinessException;

class WmsOutboundClient extends  BaseClient
{

    public function __construct($regionCode)
    {
        $innerUrl = env('REGION_INNER_URL_'.strtoupper($regionCode),'');
        if (empty($innerUrl)) throw new BusinessException("该区域仓无配置链接");

        $innerUrl = $innerUrl.'/outbound';
        $this->host = $innerUrl;
    }



}