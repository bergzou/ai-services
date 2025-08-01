<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class YudaoDemo03GradeModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'yudao_demo03_grade';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'student_id' => 'integer', # 学生编号
        'name' => 'string', # 名字
        'teacher' => 'string', # 班主任
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
        'tenant_id' => 'integer', # 租户编号
    ];

}
