<?php

namespace App\Validates;
use App\Validates\ValidationService;

class ReturnedDetailValidation extends ValidationService
{
    public function rules(): array
    {

        return [
            'sku' => 'required',
           'customer_sku' => 'required',
           'sku_weight' =>'required|integer',
          
          'sku_length' =>'required|integer',
          'sku_width' =>'required|integer',
          'sku_height' =>'required|integer',
          'actual_received_quantity' =>'required|integer|min:1',
          'returned_quantity' =>'required|integer|min:1',
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
            'sku' => 'SKU',
            'customer_sku' => '客户SKU',
            'sku_weight' => '重量',
            'sku_length' => '长度',
            'sku_width' => '宽度',
            'sku_height' => '高度',
            'actual_received_quantity' => '实际收货数量',
            'returned_quantity' => '退件数量'
        ];
    }


    public function addParams(){
        return [
            'sku','customer_sku','actual_received_quantity','returned_quantity'
        ];
    }

}