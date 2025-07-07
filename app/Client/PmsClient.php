<?php

/**
 * 采购
 */
namespace App\Client;

use App\Exceptions\BusinessException;

class PmsClient extends  BaseClient
{

    public function __construct()
    {
        $this->host = env('INTRANET_URL','http://inner-test.tomatocross.cn:18888').'/purchase';
    }


    //获取FBA仓库数据
    public function getFbaWarehouse($data): array
    {
        return $this->sendClient($this->host.'/Internal/Warehouse/fbaWarehouse','POST',json_encode($data));
    }

}
