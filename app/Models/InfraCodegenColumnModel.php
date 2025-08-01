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
        'snowflake_id' => 'string', # 雪花Id
        'table_id' => 'integer', # 表编号
        'column_name' => 'string', # 字段名
        'data_type' => 'string', # 字段类型
        'column_comment' => 'string', # 字段描述
        'nullable' => 'integer', # 是否允许为空
        'primary_key' => 'integer', # 是否主键
        'ordinal_position' => 'integer', # 排序
        'java_type' => 'string', # Java 属性类型
        'java_field' => 'string', # Java 属性名
        'dict_type' => 'string', # 字典类型
        'example' => 'string', # 数据示例
        'create_operation' => 'integer', # 是否为 Create 创建操作的字段
        'update_operation' => 'integer', # 是否为 Update 更新操作的字段
        'list_operation' => 'integer', # 是否为 List 查询操作的字段
        'list_operation_condition' => 'string', # List 查询操作的条件类型
        'list_operation_result' => 'integer', # 是否为 List 查询操作的返回字段
        'html_type' => 'string', # 显示类型
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
