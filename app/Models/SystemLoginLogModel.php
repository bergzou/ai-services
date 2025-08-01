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
        'snowflake_id' => 'string', # 雪花Id
        'log_type' => 'integer', # 日志类型
        'trace_id' => 'string', # 链路追踪编号
        'user_id' => 'integer', # 用户编号
        'user_type' => 'integer', # 用户类型：10=会员， 20=管理员
        'username' => 'string', # 用户账号
        'result' => 'integer', # 登陆结果
        'user_ip' => 'string', # 用户 IP
        'user_agent' => 'string', # 浏览器 UA
        'tenant_id' => 'integer', # 租户编号
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
