<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemNotifyTemplateValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'name' => 'required|string|max:63', # 模板名称
            'code' => 'required|string|max:64', # 模版编码
            'nickname' => 'required|string|max:255', # 发送人名称
            'content' => 'required|string|max:1024', # 模版内容
            'type' => 'required|boolean', # 类型
            'params' => 'nullable|string|max:255', # 参数数组
            'status' => 'required|boolean', # 状态
            'remark' => 'nullable|string|max:255', # 备注
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'tenant_id' => 'nullable|integer', # 租户编号
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
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'name' => __('validated.300135'), # 模板名称
            'code' => __('validated.300164'), # 模版编码
            'nickname' => __('validated.300137'), # 发送人名称
            'content' => __('validated.300159'), # 模版内容
            'type' => __('validated.300165'), # 类型
            'params' => __('validated.300140'), # 参数数组
            'status' => __('validated.300111'), # 状态
            'remark' => __('validated.300054'), # 备注
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}