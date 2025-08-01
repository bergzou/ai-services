<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemUserRoleModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_user_role';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 自增编号
        'snowflake_id' => 'string', # 雪花Id
        'user_id' => 'integer', # 用户ID
        'role_id' => 'integer', # 角色ID
        'created_at' => 'datetime', # 创建时间
        'create_time' => 'time', # 创建时间
        'updated_at' => 'datetime', # 更新时间
        'update_time' => 'time', # 更新时间
        'deleted' => 'integer', # 是否删除
        'tenant_id' => 'integer', # 租户编号
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
