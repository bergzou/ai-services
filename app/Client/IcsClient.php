<?php

/**
 * 调用内部系统-站点
 */
namespace App\Client;

use App\Exceptions\BusinessException;
use App\Libraries\Curl;

class IcsClient extends  BaseClient
{

    public string $proxyUrl = '';//代理url

    public function __construct()
    {
        $this->proxyUrl = env('INTRANET_URL').'/ics';
    }


    /**
     * erp创建出库单提交出库单信息
     * @param array $data
     * @return bool|string|array
     * @throws BusinessException
     */

    public function submitOutboundOperation(array $data = []): bool|string|array
    {
        return $this->sendClient($this->proxyUrl."/internal/inventory/submitOutboundOperation",'POST',json_encode($data));
    }


    //出库单拦截取消商品库存
    public function cancelOutboundOperation(array $data = []): bool|string|array
    {
        return $this->sendClient($this->proxyUrl."/internal/inventory/cancelOutboundOperation",'POST',json_encode($data));
    }

    //根据仓库编码获取仓库信息和区域仓信息
    public function getWarehouseCode(array $data = []): bool|string|array
    {
        return $this->sendClient($this->proxyUrl."/internal/RegionWarehouse/getWarehouseCode",'POST',json_encode($data));
    }

    // 获取自有海外仓库
    public function getOwnWarehouse(array $data = []) {
        return $this->sendClient($this->proxyUrl."/internal/RegionWarehouse/getwarehouseTenantCode",'POST',json_encode($data));
    }








}
