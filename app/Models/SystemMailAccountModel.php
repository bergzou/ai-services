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
        'mail' => 'string', # 邮箱
        'username' => 'string', # 用户名
        'password' => 'string', # 密码
        'host' => 'string', # SMTP 服务器域名
        'port' => 'integer', # SMTP 服务器端口
        'ssl_enable' => 'boolean', # 是否开启 SSL
        'starttls_enable' => 'boolean', # 是否开启 STARTTLS
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
