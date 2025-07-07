<?php

/**
 * oms出库单
 */
namespace App\Client;

use App\Exceptions\BusinessException;

class OmsOutboundClient extends  BaseClient
{

    public function __construct()
    {
        $this->host = env('INTRANET_URL','http://inner-test.tomatocross.cn:18888').'/omsoutbound';
    }

    /**
     * @throws BusinessException
     */
    public function getOutboundOrder($params) {
        $url = $this->host.'/internal/outbound/getOutboundOrder';
        return $this->sendClient($url,'POST',json_encode($params));
    }
}
