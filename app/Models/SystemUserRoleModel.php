<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemUserRoleModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_user_role';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['user_id','role_id','created_at','create_time','updated_at','update_time','deleted','tenant_id','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
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
