<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class ReturnedClaimOrderValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'claim_order_code' => 'nullable|string|max:64', # 认领单号 
            'tracking_number' => 'required|string|max:64', # 跟踪单号
            'returned_desc' => 'nullable|string|max:200', # 退件描述
            'seller_code' => 'required|string|max:64', # 卖家代码
            'claim_status' => 'required|boolean', # 认领状态：10=待认领,20=已认领,30=已弃货
            'claim_type' => 'required|boolean', # 认领类型：10：我的退件,20：未知退件,30：已弃货
            'warehouse_code' => 'nullable|string|max:64', # 仓库编码
            'region_code' => 'nullable|string|max:64', # 区域编码
            'handling_method' => 'nullable|boolean', # 处理方式：1：重新上架 2：销毁
            'receiving_at' => 'nullable|date_format:Y-m-d H:i:s', # 收货时间
            'claim_at' => 'nullable|date_format:Y-m-d H:i:s', # 认领时间
            'manage_code' => 'nullable|string|max:64', # 项目编码
            'manage_name' => 'nullable|string|max:64', # 项目名称
            'returned_order_code' => 'nullable|string|max:64', # 关联退件单号
            'tenant_code' => 'nullable|string|max:64', # 租户编码
            'claim_order_id' => 'required|string|max:64', # 雪花id
            'abandoned_at' => 'nullable|date_format:Y-m-d H:i:s', # 弃货时间
        ];
    }

    /**
     * 定义验证错误消息数组
     * @return array 键为'字段名.规则名'（如 'name.required'），值为自定义错误提示信息
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * 定义字段自定义别名数组（用于错误消息中显示友好名称）
     * @return array 键为字段名，值为业务友好的字段显示名称（如 'name' => '用户姓名'）
     * */
    public function customAttributes(): array
    {
        return [
            'claim_order_code' => __('validated.100000'), # 认领单号
            'tracking_number' => __('validated.100001'), # 跟踪单号
            'returned_desc' => __('validated.100002'), # 退件描述
            'seller_code' => __('validated.100003'), # 卖家代码
            'claim_status' => __('validated.100004'), # 认领状态
            'claim_type' => __('validated.100005'), # 认领类型
            'warehouse_code' => __('validated.100006'), # 仓库编码
            'region_code' => __('validated.100007'), # 区域编码
            'handling_method' => __('validated.100008'), # 处理方式
            'receiving_at' => __('validated.100009'), # 收货时间
            'claim_at' => __('validated.100010'), # 认领时间
            'manage_code' => __('validated.100011'), # 项目编码
            'manage_name' => __('validated.100012'), # 项目名称
            'returned_order_code' => __('validated.100013'), # 关联退件单号
            'tenant_code' => __('validated.100014'), # 租户编码
            'claim_order_id' => __('validated.100015'), # 雪花id
            'abandoned_at' => __('validated.100016'), # 弃货时间
        ];
    }

    /**
     * 定义验证场景参数数组
     * @return array 键为场景名，值为场景参数数组
     */
    public function addParams(){
        return ['abandoned_at'];
    }

}