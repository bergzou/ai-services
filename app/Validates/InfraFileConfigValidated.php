<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraFileConfigValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'name' => 'required|string|max:63', # 配置名
            'storage' => 'required|boolean', # 存储器
            'remark' => 'nullable|string|max:255', # 备注
            'master' => 'required|boolean', # 是否为主配置
            'config' => 'required|string|max:4096', # 存储配置
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
            'name' => __('validated.300083'), # 配置名
            'storage' => __('validated.300084'), # 存储器
            'remark' => __('validated.300054'), # 备注
            'master' => __('validated.300085'), # 是否为主配置
            'config' => __('validated.300086'), # 存储配置
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
        ];
    }
}