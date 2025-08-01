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
            'id' => 'required', # 自增编号
            'snowflake_id' => 'required', # 雪花Id
            'user_id' => 'required', # 用户ID
            'role_id' => 'required', # 角色ID
            'create_time' => 'nullable', # 创建时间
            'update_time' => 'nullable', # 更新时间
            'deleted' => 'nullable', # 是否删除
            'tenant_id' => 'required', # 租户编号
            'is_deleted' => 'required', # 是否删除
            'deleted_by' => 'nullable', # 删除人名称
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
            'id' => __('validated.300291'), # 自增编号
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'user_id' => __('validated.300253'), # 用户ID
            'role_id' => __('validated.300201'), # 角色ID
            'create_time' => __('validated.300255'), # 创建时间
            'update_time' => __('validated.300256'), # 更新时间
            'deleted' => __('validated.300184'), # 是否删除
            'tenant_id' => __('validated.300018'), # 租户编号
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }

    /**
     * 新增参数
     * @return array
     */
    public function addParams(): array
    {
        return [];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return [];
    }

    /**
     * 删除参数
     * @return array
     */
    public function deleteParams(): array
    {
        return [];
    }

    /**
     * 详情参数
     * @return array
     */
    public function detailParams(): array
    {
        return [];
    }
}