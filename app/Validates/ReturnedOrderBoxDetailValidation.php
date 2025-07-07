<?php

namespace App\Validates;


use App\Validates\ValidationService;

class ReturnedOrderBoxDetailValidation extends ValidationService
{
    public  function rules()
    {
        return [
            'returned_order_code' => 'required|string|max:64',
            'returned_order_box_detail_id' => 'required|string|max:64',
            'sku' => 'required|string|max:64',
            'customer_sku' => 'required|string|max:64',
            'sku_weight' => 'required|numeric|min:0',
            'sku_length' => 'required|numeric|min:0',
            'sku_width' => 'required|numeric|min:0',
            'sku_height' => 'required|numeric|min:0',
            'shipment_quantity' => 'required|integer|min:0',
            'box_code' => 'required|string|max:100',
            'returned_box_id' => 'required|string|max:64',
            'seller_code' => 'required|string|max:64',
            'packing_quantity' => 'required|integer|min:0',
        ];
    }

    public  function messages()
    {
        return [
            'returned_order_code.required' => '退件单号不能为空',
            'returned_order_box_detail_id.required' => '箱明细ID不能为空',
            'returned_order_box_detail_id.unique' => '箱明细ID已存在',
            'sku.required' => 'SKU编码不能为空',
            'customer_sku.required' => '卖家SKU不能为空',
            'sku_weight.required' => '重量不能为空',
            'sku_weight.numeric' => '重量必须是数字',
            'sku_weight.min' => '重量不能小于0',
            'sku_length.required' => '长度不能为空',
            'sku_width.required' => '宽度不能为空',
            'sku_height.required' => '高度不能为空',
            'shipment_quantity.required' => '数量不能为空',
            'box_code.required' => '箱唛号不能为空',
            'returned_box_id.required' => '箱ID不能为空',
            'seller_code.required' => '卖家代码不能为空',
            'packing_quantity.required' => '装箱数量不能为空',
        ];
    }

    public  function customAttributes()
    {
        return [
            'returned_order_code' => '退件单号',
            'returned_order_box_detail_id' => '箱明细ID',
            'sku' => 'SKU编码',
            'customer_sku' => '卖家SKU',
            'sku_weight' => '重量',
            'sku_length' => '长',
            'sku_width' => '宽',
            'sku_height' => '高',
            'shipment_quantity' => '数量',
            'box_code' => '箱唛号',
            'returned_box_id' => '箱ID',
            'seller_code' => '卖家代码',
            'packing_quantity' => '装箱数量',
        ];
    }
}