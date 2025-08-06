<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemRoleModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_role';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['name','code','sort','data_scope','data_scope_dept_ids','status','type','remark','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by','tenant_id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 角色ID
        'snowflake_id' => 'string', # 雪花Id
        'name' => 'string', # 角色名称
        'code' => 'string', # 角色权限字符串
        'sort' => 'integer', # 显示顺序
        'data_scope' => 'integer', # 数据范围：1=全部数据权限， 2=自定数据权限， 3=本部门数据权限， 4=本部门及以下数据权限
        'data_scope_dept_ids' => 'string', # 数据范围(指定部门数组)
        'status' => 'integer', # 角色状态： 1=正常， 2=停用
        'type' => 'integer', # 角色类型
        'remark' => 'string', # 备注
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
