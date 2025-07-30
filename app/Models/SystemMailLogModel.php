<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemMailLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_mail_log';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'user_id' => 'integer', # 用户编号
        'user_type' => 'boolean', # 用户类型
        'to_mail' => 'string', # 接收邮箱地址
        'account_id' => 'integer', # 邮箱账号编号
        'from_mail' => 'string', # 发送邮箱地址
        'template_id' => 'integer', # 模板编号
        'template_code' => 'string', # 模板编码
        'template_nickname' => 'string', # 模版发送人名称
        'template_title' => 'string', # 邮件标题
        'template_content' => 'string', # 邮件内容
        'template_params' => 'string', # 邮件参数
        'send_status' => 'boolean', # 发送状态
        'send_time' => 'time', # 发送时间
        'send_message_id' => 'string', # 发送返回的消息 ID
        'send_exception' => 'string', # 发送异常
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
