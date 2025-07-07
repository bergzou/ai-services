<?php

namespace App\Validates;
use App\Validates\ValidationService;

class ReturnedClaimOrderValidation extends ValidationService
{
    public function rules(): array
    {
        return [
            'claim_order_code' => 'required|max:64',
            'tracking_number' => 'required|max:64',
            'returned_desc' => 'required|max:200',
            'seller_code' => 'required|max:64',
            'claim_status' => 'required',
            'claim_type' => 'required',
            'warehouse_code' => 'required|max:64',
            'region_code' => 'required|max:64',
            'handling_method' => 'required',
            'manage_code' => 'required|max:64',
            'manage_name' => 'required|max:64',
            'returned_order_code' => 'required|max:64',
            'claim_order_id' => 'required|max:64',
            'detail' => 'required|array',
            'detail.*.sku' => 'required',
            'detail.*.customer_sku' => 'required',
            'detail.*.new_sku' => 'required',
            'detail.*.new_customer_sku' => 'required',
            'detail.*.new_seller_sku' => 'required',
        ];
    }

    public function messages()
    {
        return [];
    }

    public function customAttributes()
    {
        return [
            'claim_order_code' => '认领单号',
            'tracking_number' => '跟踪单号',
            'returned_desc' => '退件描述',
            'seller_code' => '卖家代码',
            'claim_status' => '认领状态',
            'claim_type' => '认领类型',
            'warehouse_code' => '仓库编码',
            'region_code' => '区域编码',
            'handling_method' => '处理方式',
            'manage_code' => '项目编码',
            'manage_name' => '项目名称',
            'returned_order_code' => '关联退件单号',
            'claim_order_id' => '雪花id'
        ];
    }

    public function addParams(){
        return [
            'claim_order_code',  'handling_method',  'manage_code',  'manage_name', 'detail',

        ];
    }
}