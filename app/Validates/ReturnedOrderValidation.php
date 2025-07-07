<?php

namespace App\Validates;

use App\Validates\ValidationService;

class ReturnedOrderValidation extends ValidationService
{
    public function rules(): array
    {
        return [
            'returned_order_id' => 'required|max:64',
            'returned_order_code' => 'required|max:64',
            'tracking_number' => 'required|max:64',
            'returned_reference_no' => 'required|max:64',
            'manage_name' => 'required|max:64',
            'manage_code' => 'required|max:64',
            'returned_sign' => 'required|integer',
            'warehouse_code' => 'required|max:64',
            'returned_type' => 'required|integer',
            'handling_method' => 'required|integer',
            'returned_illustrate' => 'required|max:200',
            'submit_at' => 'required|date',
            'receiving_at' => 'required|date',
            'completion_at' => 'required|date',
            'updator_uid' => 'required|max:64',
            'updator_name' => 'required|max:64',
            'outbound_order_code' => 'required|max:64',
            'seller_order_code' => 'required|max:64',
            'expected_delivery_time' => 'required|date',
            'region_code' => 'required|max:64',
            'seller_code' => 'required|max:64',
            'tenant_code' => 'required|max:64',
            'claim_order_code' => 'required|max:64',
            'returned_status' => 'required|integer',
            'outbound_warehouse_code' => 'required|max:64',
            'outbound_warehouse_name' => 'required|max:64',
            'warehouse_name' => 'required|max:64',
            'document_type' => 'required|integer',
            'create_type' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [

        ];
    }

    public function customAttributes()
    {
        return [
            'returned_order_id' => '雪花ID',
            'returned_order_code' => '退件单号',
            'tracking_number' => '跟踪号',
            'returned_reference_no' => '退件参考号',
            'manage_name' => '项目名称',
            'manage_code' => '项目编码',
            'returned_sign' => '退件标识',
            'warehouse_code' => '退件仓库',
            'returned_type' => '退件类型',
            'handling_method' => '处理方式',
            'returned_illustrate' => '退件说明',
            'submit_at' => '提交时间',
            'receiving_at' => '收货时间',
            'completion_at' => '完成时间',
            'updator_uid' => '更新人UID',
            'updator_name' => '更新人名称',
            'outbound_order_code' => '出库单号',
            'seller_order_code' => '卖家订单号',
            'expected_delivery_time' => '预计到货时间',
            'region_code' => '区域仓代码',
            'seller_code' => '卖家代码',
            'tenant_code' => '租户编码',
            'claim_order_code' => '退件认领单号',
            'returned_status' => '退件状态',
            'outbound_warehouse_code' => '发货仓库编码',
            'outbound_warehouse_name' => '发货仓库名称',
            'warehouse_name' => '仓库名称',
            'document_type' => '单据类型',
            'create_type' => '创建类型'
        ];
    }



    public function addParams()
    {
        return [
            'returned_status','manage_name','manage_code','returned_sign','warehouse_code','returned_type','handling_method', 'tracking_number',
        ];  
    }

    public function syncAddParams()
    {
        return [
            'returned_status','manage_name','manage_code','returned_sign','warehouse_code','returned_type','handling_method',
        ];
    }
}