<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemMenuModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_menu';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 菜单ID
        'name' => 'string', # 菜单名称
        'permission' => 'string', # 权限标识
        'type' => 'boolean', # 菜单类型
        'sort' => 'integer', # 显示顺序
        'parent_id' => 'integer', # 父菜单ID
        'path' => 'string', # 路由地址
        'icon' => 'string', # 菜单图标
        'component' => 'string', # 组件路径
        'component_name' => 'string', # 组件名
        'status' => 'boolean', # 菜单状态
        'visible' => 'boolean', # 是否可见
        'keep_alive' => 'boolean', # 是否缓存
        'always_show' => 'boolean', # 是否总是显示
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
