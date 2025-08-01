<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraFileValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'config_id' => 'nullable|integer', # 配置编号
            'name' => 'nullable|string|max:256', # 文件名
            'path' => 'required|string|max:512', # 文件路径
            'url' => 'required|string|max:1024', # 文件 URL
            'type' => 'nullable|string|max:128', # 文件类型
            'size' => 'required|integer', # 文件大小
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'required|integer', # 是否删除
            'deleted_by' => 'nullable|string|max:255', # 删除人名称
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
            'config_id' => __('validated.300077'), # 配置编号
            'name' => __('validated.300078'), # 文件名
            'path' => __('validated.300079'), # 文件路径
            'url' => __('validated.300080'), # 文件 URL
            'type' => __('validated.300081'), # 文件类型
            'size' => __('validated.300082'), # 文件大小
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}