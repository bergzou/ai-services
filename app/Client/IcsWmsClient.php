<?php

/**
 * 调用ICS仓储相关
 */
namespace App\Client;

use App\Exceptions\BusinessException;
use App\Libraries\Curl;

class IcsWmsClient extends  BaseClient
{
    public function __construct()
    {
        $this->host = getenv('INTRANET_URL').'/ics';
    }


    //获取仓库信息
    public function getWarehouse(array $params): array
    {
        $url = $this->host.'/internal/WarehouseApi/Warehouse/getWarehouse';
        return $this->sendClient($url,'POST',json_encode($params));
    }

    //获取多个仓库信息
    public function getWarehouses(array $params): array
    {
        $url = $this->host . '/internal/WarehouseApi/Warehouse/getWarehouses';
        return $this->sendClient($url, 'POST', json_encode($params));
    }

    //获取区域仓信息
    public function getRegions(array $params): array
    {
        $url = $this->host.'/internal/RegionWarehouse/getRegions';
        return $this->sendClient($url,'POST',json_encode($params));
    }
}
