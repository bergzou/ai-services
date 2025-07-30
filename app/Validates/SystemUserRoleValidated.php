<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemUserRoleValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'user_id' => 'required|integer', # 用户ID
            'role_id' => 'required|integer', # 角色ID
            'create_time' => 'nullable|date_format:Y-m-d H:i:s', # 创建时间
            'update_time' => 'nullable|date_format:Y-m-d H:i:s', # 更新时间
            'deleted' => 'nullable|boolean', # 是否删除
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
            'user_id' => __('validated.300253'), # 用户ID
            'role_id' => __('validated.300201'), # 角色ID
            'create_time' => __('validated.300255'), # 创建时间
            'update_time' => __('validated.300256'), # 更新时间
            'deleted' => __('validated.300184'), # 是否删除
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}