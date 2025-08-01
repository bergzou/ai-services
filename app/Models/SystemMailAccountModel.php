<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemMailAccountModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_mail_account';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['mail','username','password','host','port','ssl_enable','starttls_enable','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 主键
        'snowflake_id' => 'string', # 雪花Id
        'mail' => 'string', # 邮箱
        'username' => 'string', # 用户名
        'password' => 'string', # 密码
        'host' => 'string', # SMTP 服务器域名
        'port' => 'integer', # SMTP 服务器端口
        'ssl_enable' => 'integer', # 是否开启 SSL
        'starttls_enable' => 'integer', # 是否开启 STARTTLS
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
