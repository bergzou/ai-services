<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemDictDataValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'sort' => 'nullable|integer', # 字典排序
            'label' => 'nullable|string|max:100', # 字典标签
            'value' => 'nullable|string|max:100', # 字典键值
            'dict_type' => 'nullable|string|max:100', # 字典类型
            'status' => 'nullable|boolean', # 状态：1=启用， 2=停用
            'color_type' => 'nullable|string|max:100', # 颜色类型
            'css_class' => 'nullable|string|max:100', # css 样式
            'remark' => 'nullable|string|max:500', # 备注
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
            'sort' => __('validated.300108'), # 字典排序
            'label' => __('validated.300109'), # 字典标签
            'value' => __('validated.300110'), # 字典键值
            'dict_type' => __('validated.300042'), # 字典类型
            'status' => __('validated.300111'), # 状态
            'color_type' => __('validated.300112'), # 颜色类型
            'css_class' => __('validated.300113'), # css 样式
            'remark' => __('validated.300054'), # 备注
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
        ];
    }
}