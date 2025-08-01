<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemLoginLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_login_log';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['log_type','trace_id','user_id','user_type','username','result','user_ip','user_agent','tenant_id','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
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
