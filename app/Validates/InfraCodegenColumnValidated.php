<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraCodegenColumnValidated extends BaseValidated implements ValidatesInterface
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
            'table_id' => 'required', # 表编号
            'column_name' => 'required', # 字段名
            'data_type' => 'required', # 字段类型
            'column_comment' => 'required', # 字段描述
            'nullable' => 'required', # 是否允许为空
            'primary_key' => 'required', # 是否主键
            'ordinal_position' => 'required', # 排序
            'java_type' => 'required', # Java 属性类型
            'java_field' => 'required', # Java 属性名
            'dict_type' => 'nullable', # 字典类型
            'example' => 'nullable', # 数据示例
            'create_operation' => 'required', # 是否为 Create 创建操作的字段
            'update_operation' => 'required', # 是否为 Update 更新操作的字段
            'list_operation' => 'required', # 是否为 List 查询操作的字段
            'list_operation_condition' => 'required', # List 查询操作的条件类型
            'list_operation_result' => 'required', # 是否为 List 查询操作的返回字段
            'html_type' => 'required', # 显示类型
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
            'table_id' => __('validated.300033'), # 表编号
            'column_name' => __('validated.300034'), # 字段名
            'data_type' => __('validated.300035'), # 字段类型
            'column_comment' => __('validated.300036'), # 字段描述
            'nullable' => __('validated.300037'), # 是否允许为空
            'primary_key' => __('validated.300038'), # 是否主键
            'ordinal_position' => __('validated.300039'), # 排序
            'java_type' => __('validated.300040'), # Java 属性类型
            'java_field' => __('validated.300041'), # Java 属性名
            'dict_type' => __('validated.300042'), # 字典类型
            'example' => __('validated.300043'), # 数据示例
            'create_operation' => __('validated.300044'), # 是否为 Create 创建操作的字段
            'update_operation' => __('validated.300045'), # 是否为 Update 更新操作的字段
            'list_operation' => __('validated.300046'), # 是否为 List 查询操作的字段
            'list_operation_condition' => __('validated.300047'), # List 查询操作的条件类型
            'list_operation_result' => __('validated.300048'), # 是否为 List 查询操作的返回字段
            'html_type' => __('validated.300049'), # 显示类型
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