<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSmsCodeModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_sms_code';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'mobile' => 'string', # 手机号
        'code' => 'string', # 验证码
        'create_ip' => 'string', # 创建 IP
        'scene' => 'boolean', # 发送场景
        'today_index' => 'boolean', # 今日发送的第几条
        'used' => 'boolean', # 是否使用
        'used_time' => 'time', # 使用时间
        'used_ip' => 'string', # 使用 IP
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
        'tenant_id' => 'integer', # 租户编号
    ];

}
