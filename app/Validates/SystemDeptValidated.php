<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemDeptValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'name' => 'nullable|string|max:30', # 部门名称
            'parent_id' => 'nullable|integer', # 父部门id
            'sort' => 'nullable|integer', # 显示顺序
            'leader_user_id' => 'nullable|integer', # 负责人
            'phone' => 'nullable|string|max:11', # 联系电话
            'email' => 'nullable|string|max:50', # 邮箱
            'status' => 'required|boolean', # 部门状态：1=启用， 2=停用
            'tenant_id' => 'nullable|integer', # 租户编号
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
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
            'name' => __('validated.300101'), # 部门名称
            'parent_id' => __('validated.300102'), # 父部门id
            'sort' => __('validated.300103'), # 显示顺序
            'leader_user_id' => __('validated.300104'), # 负责人
            'phone' => __('validated.300105'), # 联系电话
            'email' => __('validated.300106'), # 邮箱
            'status' => __('validated.300107'), # 部门状态
            'tenant_id' => __('validated.300018'), # 租户编号
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
        ];
    }
}