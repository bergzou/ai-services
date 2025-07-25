<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsersPointsLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'users_points_log';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'string', # 用户ID
        'points_change' => 'integer', # 积分变动值
        'current_points' => 'integer', # 变动后积分
        'source_type' => 'integer', # 积分来源：10：充值会员
        'source_id' => 'string', # 积分来源相关记录ID
        'description' => 'string', # 变动描述
        'created_by' => 'string', # 操作人名称
        'created_at' => 'datetime', # 操作时间
    ];

}
