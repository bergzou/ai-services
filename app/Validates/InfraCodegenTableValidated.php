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
            'id' => 'required', # 编号
            'snowflake_id' => 'required', # 雪花Id
            'data_source_config_id' => 'required', # 数据源配置的编号
            'scene' => 'required', # 生成场景
            'table_name' => 'required', # 表名称
            'table_comment' => 'required', # 表描述
            'remark' => 'nullable', # 备注
            'module_name' => 'required', # 模块名
            'business_name' => 'required', # 业务名
            'class_name' => 'required', # 类名称
            'class_comment' => 'required', # 类描述
            'author' => 'required', # 作者
            'template_type' => 'required', # 模板类型
            'front_type' => 'required', # 前端类型
            'parent_menu_id' => 'nullable', # 父菜单编号
            'master_table_id' => 'nullable', # 主表的编号
            'sub_join_column_id' => 'nullable', # 子表关联主表的字段编号
            'sub_join_many' => 'nullable', # 主表与子表是否一对多
            'tree_parent_column_id' => 'nullable', # 树表的父字段编号
            'tree_name_column_id' => 'nullable', # 树表的名字字段编号
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