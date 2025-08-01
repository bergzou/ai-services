<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemMailTemplateValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 编号
            'snowflake_id' => 'required', # 雪花Id
            'name' => 'required', # 模板名称
            'code' => 'required', # 模板编码
            'account_id' => 'required', # 发送的邮箱账号编号
            'nickname' => 'nullable', # 发送人名称
            'title' => 'required', # 模板标题
            'content' => 'required', # 模板内容
            'params' => 'required', # 参数数组
            'status' => 'required', # 开启状态：1=启用， 2=停用
            'remark' => 'nullable', # 备注
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
            'id' => __('validated.300280'), # 编号
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'name' => __('validated.300135'), # 模板名称
            'code' => __('validated.300126'), # 模板编码
            'account_id' => __('validated.300136'), # 发送的邮箱账号编号
            'nickname' => __('validated.300137'), # 发送人名称
            'title' => __('validated.300138'), # 模板标题
            'content' => __('validated.300139'), # 模板内容
            'params' => __('validated.300140'), # 参数数组
            'status' => __('validated.300141'), # 开启状态
            'remark' => __('validated.300054'), # 备注
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