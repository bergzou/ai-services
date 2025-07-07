<?php

namespace App\Models;

class ReturnedClaimOrderModel extends BaseModel
{
    protected $table = 'returned_claim_order';


    public function getTable()
    {
        return $this->table;
    }

    public function transWhere($requestData){


        if (isset($requestData['sku']) && $requestData['sku'] != ''){
            $detail = ReturnedClaimDetailModel::query()->select(['claim_order_code'])->where('sku',$requestData['sku'])->get()->toArray();
            $requestData['claim_order_code'] = ['无'];
            if (!empty($detail))  $requestData['claim_order_code'] = array_column($detail,'claim_order_code');
        }

        if (isset($requestData['customer_sku']) && $requestData['customer_sku'] != ''){
            $detail = ReturnedClaimDetailModel::query()->select(['claim_order_code'])->where('customer_sku',$requestData['customer_sku'])->get()->toArray();
            $requestData['claim_order_code'] = ['无'];
            if (!empty($detail))  $requestData['claim_order_code'] = array_column($detail,'claim_order_code');
        }

        if (isset($requestData['seller_sku']) && $requestData['seller_sku'] != ''){
            $detail = ReturnedClaimDetailModel::query()->select(['claim_order_code'])->where('seller_sku',$requestData['seller_sku'])->get()->toArray();
            $requestData['claim_order_code'] = ['无'];
            if (!empty($detail))  $requestData['claim_order_code'] = array_column($detail,'claim_order_code');
        }

        return  $requestData;
    }


}