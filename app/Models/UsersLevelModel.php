<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsersLevelModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'users_level';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'boolean', # 等级ID
        'level_name' => 'string', # 等级名称
        'min_points' => 'integer', # 所需最低积分
        'discount_rate' => 'decimal:2', # 折扣率
        'icon' => 'string', # 等级图标
        'created_by' => 'string', # 创建人名称
        'created_at' => 'datetime', # 创建时间
        'updated_by' => 'string', # 更新人名称
        'updated_at' => 'datetime', # 更新时间
    ];

}
