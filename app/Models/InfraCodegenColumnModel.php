<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraCodegenColumnModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_codegen_column';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'table_id' => 'integer', # 表编号
        'column_name' => 'string', # 字段名
        'data_type' => 'string', # 字段类型
        'column_comment' => 'string', # 字段描述
        'nullable' => 'boolean', # 是否允许为空
        'primary_key' => 'boolean', # 是否主键
        'ordinal_position' => 'integer', # 排序
        'java_type' => 'string', # Java 属性类型
        'java_field' => 'string', # Java 属性名
        'dict_type' => 'string', # 字典类型
        'example' => 'string', # 数据示例
        'create_operation' => 'boolean', # 是否为 Create 创建操作的字段
        'update_operation' => 'boolean', # 是否为 Update 更新操作的字段
        'list_operation' => 'boolean', # 是否为 List 查询操作的字段
        'list_operation_condition' => 'string', # List 查询操作的条件类型
        'list_operation_result' => 'boolean', # 是否为 List 查询操作的返回字段
        'html_type' => 'string', # 显示类型
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
