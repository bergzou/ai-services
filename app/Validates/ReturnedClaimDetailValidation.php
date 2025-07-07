<?php

namespace App\Validates;
use App\Validates\ValidationService;

class ReturnedClaimDetailValidation extends ValidationService
{
    public function rules(): array
    {
        return [
            'sku' => 'required|max:64',
            'customer_sku' => 'required|max:64',
            'receive_quantity' => 'required|integer',
            'receive_defective_quantity' => 'nullable|integer',
            'claim_order_code' => 'required|max:64',
            'identification_mark' => 'required|integer',
            'new_sku' => 'required|max:64',
            'new_customer_sku' => 'required|max:64',
            'seller_sku' => 'required|max:64'
        ];
    }

    public function messages()
    {
        return [
            'sku' => '系统SKU',
            'customer_sku' => '卖家SKU',
            'receive_quantity' => '退件数量',
            'receive_defective_quantity' => '实收不良品数量',
            'claim_order_code' => '认领单号',
            'identification_mark' => '无法识别SKU标识',
            'new_sku' => '转换后SKU',
            'new_customer_sku' => '转换后SKU编码',
            'seller_sku' => '销售SKU'
        ];
    }

    public function customAttributes()
    {
        return [
            'sku' => '系统SKU',
            'customer_sku' => '卖家SKU',
            'receive_quantity' => '退件数量',
            'receive_defective_quantity' => '实收不良品数量',
            'claim_order_code' => '认领单号',
            'identification_mark' => '无法识别SKU标识',
            'new_sku' => '转换后SKU',
            'new_customer_sku' => '转换后SKU编码',
            'seller_sku' => '销售SKU'
        ];
    }
}