<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;

class SystemRoleValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 角色ID
            'snowflake_id' => 'required', # 雪花Id
            'name' => 'required', # 角色名称
            'code' => 'required', # 角色权限字符串
            'sort' => 'required', # 显示顺序
            'data_scope' => 'required', # 数据范围（1：全部数据权限 2：自定数据权限 3：本部门数据权限 4：本部门及以下数据权限）
            'data_scope_dept_ids' => 'required', # 数据范围(指定部门数组)
            'status' => 'required', # 角色状态（0正常 1停用）
            'type' => 'required', # 角色类型
            'remark' => 'nullable', # 备注
            'created_by' => 'required', # 创建人名称
            'updated_by' => 'required', # 更新人名称
            'is_deleted' => 'required', # 是否删除
            'deleted_by' => 'nullable', # 删除人名称
            'tenant_id' => 'required', # 租户编号
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
            'id' => __('validated.300201'), # 角色ID
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'name' => __('validated.300195'), # 角色名称
            'code' => __('validated.300196'), # 角色权限字符串
            'sort' => __('validated.300103'), # 显示顺序
            'data_scope' => __('validated.300197'), # 数据范围（1
            'data_scope_dept_ids' => __('validated.300198'), # 数据范围(指定部门数组)
            'status' => __('validated.300199'), # 角色状态（0正常 1停用）
            'type' => __('validated.300200'), # 角色类型
            'remark' => __('validated.300054'), # 备注
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }

    /**
     * 新增参数
     * @return array
     */
    public function addParams(): array
    {
        return ['name','code','sort','status','remark'];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return ['snowflake_id','name','code','sort','status','remark'];
    }

    /**
     * 删除参数
     * @return array
     */
    public function deleteParams(): array
    {
        return ['snowflake_id'];
    }

    /**
     * 详情参数
     * @return array
     */
    public function detailParams(): array
    {
        return ['snowflake_id'];
    }
}