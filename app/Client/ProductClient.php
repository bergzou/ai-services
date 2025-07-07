<?php

/**
 * 调用内部系统-商品系统
 */
namespace App\Client;

use App\Exceptions\BusinessException;
use App\Libraries\Curl;
use Illuminate\Support\Js;

class ProductClient extends BaseClient
{
    protected string $prefix = '/product';

    public function __construct()
    {
        $this->host = getenv('INTRANET_URL') . $this->prefix;
    }


    //根据sku获取商品信息(多个)
    public function getProductsBySkus($skus,$fields = [])
    {
        $params['sku']    = $skus;
        if(!empty($fields)){
            $params['fields'] = $fields;
        }
        return $this->sendClient($this->host."/internal/productSku/getProductSkuList",'POST',json_encode($params));
    }


    //根据sku获取商品信息(多个)
    public function getProductsBySkusNew($params)
    {
        return $this->sendClient($this->host."/internal/productSku/getProductSkuList",'POST',json_encode($params));
    }

    public function getProductAndMapInfoV2($params)
    {
        return $this->sendClient($this->host."/internal/productSku/getProductAndMapInfoV2",'POST',json_encode($params));
    }
}
