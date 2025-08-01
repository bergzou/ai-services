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
            'id' => 'required', # 部门id
            'snowflake_id' => 'required', # 雪花Id
            'name' => 'required', # 部门名称
            'parent_id' => 'required', # 父部门id
            'sort' => 'required', # 显示顺序
            'leader_user_id' => 'nullable', # 负责人
            'phone' => 'nullable', # 联系电话
            'email' => 'nullable', # 邮箱
            'status' => 'required', # 部门状态：1=启用， 2=停用
            'tenant_id' => 'required', # 租户编号
            'created_by' => 'required', # 创建人名称
            'updated_by' => 'required', # 更新人名称
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
            'id' => __('validated.300285'), # 部门id
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
        return ['name','parent_id','sort','leader_user_id','phone','email','status'];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return ['snowflake_id','name','parent_id','sort','leader_user_id','phone','email','status'];
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