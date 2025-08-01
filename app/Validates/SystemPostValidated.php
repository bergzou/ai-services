<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemPostValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'code' => 'required|string|max:64', # 岗位编码
            'name' => 'required|string|max:50', # 岗位名称
            'sort' => 'required|integer', # 显示顺序
            'status' => 'required|integer', # 状态（0正常 1停用）
            'remark' => 'nullable|string|max:500', # 备注
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'required|integer', # 是否删除
            'deleted_by' => 'nullable|string|max:255', # 删除人名称
            'tenant_id' => 'required|integer', # 租户编号
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
            'code' => __('validated.300192'), # 岗位编码
            'name' => __('validated.300193'), # 岗位名称
            'sort' => __('validated.300103'), # 显示顺序
            'status' => __('validated.300194'), # 状态（0正常 1停用）
            'remark' => __('validated.300054'), # 备注
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}