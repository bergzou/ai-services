<?php

namespace App\Models;

use App\Client\RoleClient;
use App\Exceptions\BusinessException;
use App\Models\BaseModel;
use App\Models\ReturnedDetailModel;

class ReturnedOrderModel extends BaseModel
{
    
    protected $table = 'returned_order';


    public function getTable()
    {
        return $this->table;
    }


    /**
     * @throws BusinessException
     */
    public function transWhere($requestData){


        if (isset($requestData['sku']) && $requestData['sku'] != ''){
            $detail = ReturnedDetailModel::query()->select(['returned_order_code'])->where('sku',$requestData['sku'])->get()->toArray();
            $requestData['returned_order_code'] = ['无'];
            if (!empty($detail))  $requestData['returned_order_code'] = array_column($detail,'returned_order_code'); 
        }

        if (isset($requestData['customer_sku']) && $requestData['customer_sku'] != ''){
            $detail = ReturnedDetailModel::query()->select(['returned_order_code'])->where('customer_sku',$requestData['customer_sku'])->get()->toArray();
            $requestData['returned_order_code'] = ['无'];
            if (!empty($detail))  $requestData['returned_order_code'] = array_column($detail,'returned_order_code'); 
        }

        if (isset($requestData['seller_sku']) && $requestData['seller_sku'] != ''){
            $detail = ReturnedDetailModel::query()->select(['returned_order_code'])->where('new_seller_sku',$requestData['new_seller_sku'])->get()->toArray();
            $requestData['returned_order_code'] = ['无'];
            if (!empty($detail))  $requestData['returned_order_code'] = array_column($detail,'returned_order_code'); 
        }

        if (isset($requestData['seller_code_agent']) && $requestData['seller_code_agent'] != ''){
            $requestData['seller_code'] = ['无'];
            $rolData = (new  RoleClient())->getAgentSellerCode(['seller_code' => $requestData['seller_code_agent']]);
            $sellerCodeArr = $rolData['data'] ?? [];
            if (!empty($sellerCodeArr))  $requestData['seller_code'] = $sellerCodeArr;
        }

        return  $requestData;
    }


}