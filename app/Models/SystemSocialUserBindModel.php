<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSocialUserBindModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_social_user_bind';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 主键(自增策略)
        'user_id' => 'integer', # 用户编号
        'user_type' => 'boolean', # 用户类型
        'social_type' => 'boolean', # 社交平台的类型
        'social_user_id' => 'integer', # 社交用户的编号
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
        'tenant_id' => 'integer', # 租户编号
    ];

}
