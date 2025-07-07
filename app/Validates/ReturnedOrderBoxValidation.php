<?php

namespace App\Validates;



use App\Validates\ValidationService;

class ReturnedOrderBoxValidation  extends ValidationService
{
    /**
     * 验证规则
     * @return array
     */
    public  function rules()
    {
        return [
            'returned_box_id' => 'required',
            'box_code' => 'required',
            'tracking_number' => 'required',
            'outer_box_length' => 'required|numeric|min:0',
            'outer_box_width' => 'required|numeric|min:0',
            'outer_box_height' => 'required|numeric|min:0',
            'outer_box_weight' => 'required|numeric|min:0',
            'sku_types' => 'required|integer|min:0',
            'sku_pieces' => 'required|integer|min:0',
            'actual_outer_box_length' => 'required|numeric|min:0',
            'actual_outer_box_width' => 'required|numeric|min:0',
            'actual_outer_box_height' => 'required|numeric|min:0',
            'actual_outer_box_weight' => 'required|numeric|min:0',
            'returned_order_code' => 'required|max:64',
            'remarks' => 'nullable|max:500',
            'box_detail' => 'required|array',
            'box_detail.*.sku' =>'required',
            'box_detail.*.customer_sku' =>'required',
            'box_detail.*.sku_weight' =>'required|numeric',
            'box_detail.*.sku_length' =>'required|numeric',
            'box_detail.*.sku_width' =>'required|numeric',
            'box_detail.*.sku_height' =>'required|numeric',
            'box_detail.*.returned_quantity' =>'required|integer|min:1',
        ];
    }

    /**
     * 自定义消息
     * @return array
     */
    public  function messages()
    {
        return [
           
        ];
    }

    /**
     * 自定义属性名称
     * @return array
     */
    public  function customAttributes()
    {
        return [
            'returned_box_id' => '雪花ID',
            'box_code' => '箱唛单号',
            'tracking_number' => '跟踪号',
            'outer_box_length' => '预报外箱长',
            'outer_box_width' => '预报外箱宽',
            'outer_box_height' => '预报外箱高',
            'outer_box_weight' => '预报外箱重量',
            'sku_types' => 'SKU种类数',
            'sku_pieces' => 'SKU数量',
            'actual_outer_box_length' => '实收外箱长',
            'actual_outer_box_width' => '实收外箱宽',
            'actual_outer_box_height' => '实收外箱高',
            'actual_outer_box_weight' => '实收外箱重量',
            'returned_order_code' => '退件单号',
            'remarks' => '备注',
            'box_detail' => '明细',
            'box_detail.*.sku' => 'SKU',
            'box_detail.*.customer_sku' => '客户SKU',
            'box_detail.*.sku_weight' => '重量',
            'box_detail.*.sku_length' => '长度',
            'box_detail.*.sku_width' => '宽度',
            'box_detail.*.sku_height' => '高度',
            'box_detail.*.packing_quantity' => '装箱数量',
        ];
    }

    public function addParams(){
        return [
            'box_code','outer_box_length','outer_box_width',
            'outer_box_height','outer_box_weight','remarks',
            'box_detail','box_detail.*.sku','box_detail.*.customer_sku',
            'box_detail.*.sku_weight','box_detail.*.sku_length','box_detail.*.sku_width',
            'box_detail.*.sku_height','box_detail.*.returned_quantity'

        ]; 
    }
}