<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemRoleModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_role';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 角色ID
        'name' => 'string', # 角色名称
        'code' => 'string', # 角色权限字符串
        'sort' => 'integer', # 显示顺序
        'data_scope' => 'boolean', # 数据范围（1：全部数据权限 2：自定数据权限 3：本部门数据权限 4：本部门及以下数据权限）
        'data_scope_dept_ids' => 'string', # 数据范围(指定部门数组)
        'status' => 'boolean', # 角色状态（0正常 1停用）
        'type' => 'boolean', # 角色类型
        'remark' => 'string', # 备注
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
        'tenant_id' => 'integer', # 租户编号
    ];

}
