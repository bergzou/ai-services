<?php

namespace App\Validates;
use App\Validates\ValidationService;

class ReturnedClaimLogValidation extends ValidationService
{
    public function rules(): array
    {
        return [
            'claim_order_code' => 'required|max:64',
            'content' => 'required|max:200',
            'opeator_name' => 'required|max:200',
            'opeator_uid' => 'required|max:64',
            'claim_log_id' => 'required|max:64',
            'log_type' => 'required',
            'seller_code' => 'required|max:64'
        ];
    }

    public function messages()
    {
        return [
            'claim_order_code' => '认领单号',
            'content' => '操作内容',
            'opeator_name' => '操作人',
            'opeator_uid' => '操作人名称',
            'claim_log_id' => '退件日志雪花ID',
            'log_type' => '日志类型',
            'seller_code' => '卖家代码'
        ];
    }

    public function customAttributes()
    {
        return [
            'claim_order_code' => '认领单号',
            'content' => '操作内容',
            'opeator_name' => '操作人',
            'opeator_uid' => '操作人名称',
            'claim_log_id' => '退件日志雪花ID',
            'log_type' => '日志类型',
            'seller_code' => '卖家代码'
        ];
    }
}