<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSocialUserModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_social_user';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 主键(自增策略)
        'type' => 'boolean', # 社交平台的类型
        'openid' => 'string', # 社交 openid
        'token' => 'string', # 社交 token
        'raw_token_info' => 'string', # 原始 Token 数据，一般是 JSON 格式
        'nickname' => 'string', # 用户昵称
        'avatar' => 'string', # 用户头像
        'raw_user_info' => 'string', # 原始用户数据，一般是 JSON 格式
        'code' => 'string', # 最后一次的认证 code
        'state' => 'string', # 最后一次的认证 state
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
        'tenant_id' => 'integer', # 租户编号
    ];

}
