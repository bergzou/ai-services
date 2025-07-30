<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemDeptModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_dept';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 部门id
        'snowflake_id' => 'string', # 雪花Id
        'name' => 'string', # 部门名称
        'parent_id' => 'integer', # 父部门id
        'sort' => 'integer', # 显示顺序
        'leader_user_id' => 'integer', # 负责人
        'phone' => 'string', # 联系电话
        'email' => 'string', # 邮箱
        'status' => 'boolean', # 部门状态：1=启用， 2=停用
        'tenant_id' => 'integer', # 租户编号
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
