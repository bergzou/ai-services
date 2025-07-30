<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemMailAccountModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_mail_account';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 主键
        'snowflake_id' => 'string', # 雪花Id
        'mail' => 'string', # 邮箱
        'username' => 'string', # 用户名
        'password' => 'string', # 密码
        'host' => 'string', # SMTP 服务器域名
        'port' => 'integer', # SMTP 服务器端口
        'ssl_enable' => 'boolean', # 是否开启 SSL
        'starttls_enable' => 'boolean', # 是否开启 STARTTLS
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
