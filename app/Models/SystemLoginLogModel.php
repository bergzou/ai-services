<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemLoginLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_login_log';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 访问ID
        'log_type' => 'integer', # 日志类型
        'trace_id' => 'string', # 链路追踪编号
        'user_id' => 'integer', # 用户编号
        'user_type' => 'boolean', # 用户类型
        'username' => 'string', # 用户账号
        'result' => 'boolean', # 登陆结果
        'user_ip' => 'string', # 用户 IP
        'user_agent' => 'string', # 浏览器 UA
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
        'tenant_id' => 'integer', # 租户编号
    ];

}
