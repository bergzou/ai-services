<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraCodegenTableModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_codegen_table';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'data_source_config_id' => 'integer', # 数据源配置的编号
        'scene' => 'boolean', # 生成场景
        'table_name' => 'string', # 表名称
        'table_comment' => 'string', # 表描述
        'remark' => 'string', # 备注
        'module_name' => 'string', # 模块名
        'business_name' => 'string', # 业务名
        'class_name' => 'string', # 类名称
        'class_comment' => 'string', # 类描述
        'author' => 'string', # 作者
        'template_type' => 'boolean', # 模板类型
        'front_type' => 'boolean', # 前端类型
        'parent_menu_id' => 'integer', # 父菜单编号
        'master_table_id' => 'integer', # 主表的编号
        'sub_join_column_id' => 'integer', # 子表关联主表的字段编号
        'sub_join_many' => 'boolean', # 主表与子表是否一对多
        'tree_parent_column_id' => 'integer', # 树表的父字段编号
        'tree_name_column_id' => 'integer', # 树表的名字字段编号
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
