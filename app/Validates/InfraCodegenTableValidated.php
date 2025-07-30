<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraCodegenTableValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'data_source_config_id' => 'required|integer', # 数据源配置的编号
            'scene' => 'nullable|boolean', # 生成场景
            'table_name' => 'nullable|string|max:200', # 表名称
            'table_comment' => 'nullable|string|max:500', # 表描述
            'remark' => 'nullable|string|max:500', # 备注
            'module_name' => 'required|string|max:30', # 模块名
            'business_name' => 'required|string|max:30', # 业务名
            'class_name' => 'nullable|string|max:100', # 类名称
            'class_comment' => 'required|string|max:50', # 类描述
            'author' => 'required|string|max:50', # 作者
            'template_type' => 'nullable|boolean', # 模板类型
            'front_type' => 'required|boolean', # 前端类型
            'parent_menu_id' => 'nullable|integer', # 父菜单编号
            'master_table_id' => 'nullable|integer', # 主表的编号
            'sub_join_column_id' => 'nullable|integer', # 子表关联主表的字段编号
            'sub_join_many' => 'nullable|boolean', # 主表与子表是否一对多
            'tree_parent_column_id' => 'nullable|integer', # 树表的父字段编号
            'tree_name_column_id' => 'nullable|integer', # 树表的名字字段编号
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
            'data_source_config_id' => __('validated.300050'), # 数据源配置的编号
            'scene' => __('validated.300051'), # 生成场景
            'table_name' => __('validated.300052'), # 表名称
            'table_comment' => __('validated.300053'), # 表描述
            'remark' => __('validated.300054'), # 备注
            'module_name' => __('validated.300055'), # 模块名
            'business_name' => __('validated.300056'), # 业务名
            'class_name' => __('validated.300057'), # 类名称
            'class_comment' => __('validated.300058'), # 类描述
            'author' => __('validated.300059'), # 作者
            'template_type' => __('validated.300060'), # 模板类型
            'front_type' => __('validated.300061'), # 前端类型
            'parent_menu_id' => __('validated.300062'), # 父菜单编号
            'master_table_id' => __('validated.300063'), # 主表的编号
            'sub_join_column_id' => __('validated.300064'), # 子表关联主表的字段编号
            'sub_join_many' => __('validated.300065'), # 主表与子表是否一对多
            'tree_parent_column_id' => __('validated.300066'), # 树表的父字段编号
            'tree_name_column_id' => __('validated.300067'), # 树表的名字字段编号
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
        ];
    }
}