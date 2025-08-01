<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemMenuModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_menu';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['name','permission','type','sort','parent_id','path','icon','component','component_name','status','visible','keep_alive','always_show','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 菜单ID
        'snowflake_id' => 'string', # 雪花Id
        'name' => 'string', # 菜单名称
        'permission' => 'string', # 权限标识
        'type' => 'integer', # 菜单类型：1=目录， 2=菜单， 3=按钮
        'sort' => 'integer', # 显示顺序
        'parent_id' => 'integer', # 父菜单ID
        'path' => 'string', # 路由地址
        'icon' => 'string', # 菜单图标
        'component' => 'string', # 组件路径
        'component_name' => 'string', # 组件名
        'status' => 'integer', # 菜单状态：1=启用， 2=停用
        'visible' => 'integer', # 是否可见：1=显示， 2=隐藏
        'keep_alive' => 'integer', # 是否缓存：1=缓存， 2=不缓存
        'always_show' => 'integer', # 是否总是显示：1=总是， 2=不是
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
