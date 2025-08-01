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
            'id' => 'required', # 岗位ID
            'snowflake_id' => 'required', # 雪花Id
            'code' => 'required', # 岗位编码
            'name' => 'required', # 岗位名称
            'sort' => 'required', # 显示顺序
            'status' => 'required', # 岗位状态： 1=正常， 2=停用
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
            'id' => __('validated.300254'), # 岗位ID
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'code' => __('validated.300192'), # 岗位编码
            'name' => __('validated.300193'), # 岗位名称
            'sort' => __('validated.300103'), # 显示顺序
            'status' => __('validated.300278'), # 岗位状态
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
        return ['code','name','sort','status','remark'];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return  ['snowflake_id','code','name','sort','status','remark'];
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