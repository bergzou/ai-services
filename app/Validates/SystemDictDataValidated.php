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
            'id' => 'required', # 字典编码
            'snowflake_id' => 'required', # 雪花Id
            'sort' => 'required', # 字典排序
            'label' => 'required', # 字典标签
            'value' => 'required', # 字典键值
            'dict_type' => 'required', # 字典类型
            'status' => 'required', # 状态：1=启用， 2=停用
            'color_type' => 'nullable', # 颜色类型
            'css_class' => 'nullable', # css 样式
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
            'id' => __('validated.300286'), # 字典编码
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