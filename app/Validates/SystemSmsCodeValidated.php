<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemSmsCodeValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'mobile' => 'required|string|max:11', # 手机号
            'code' => 'required|string|max:6', # 验证码
            'create_ip' => 'required|string|max:15', # 创建 IP
            'scene' => 'required|boolean', # 发送场景
            'today_index' => 'required|boolean', # 今日发送的第几条
            'used' => 'required|boolean', # 是否使用
            'used_time' => 'nullable|date_format:Y-m-d H:i:s', # 使用时间
            'used_ip' => 'nullable|string|max:255', # 使用 IP
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'nullable|boolean', # 是否删除
            'deleted_by' => 'nullable|string|max:255', # 删除人名称
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
            'mobile' => __('validated.300208'), # 手机号
            'code' => __('validated.300209'), # 验证码
            'create_ip' => __('validated.300210'), # 创建 IP
            'scene' => __('validated.300211'), # 发送场景
            'today_index' => __('validated.300212'), # 今日发送的第几条
            'used' => __('validated.300213'), # 是否使用
            'used_time' => __('validated.300214'), # 使用时间
            'used_ip' => __('validated.300215'), # 使用 IP
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}