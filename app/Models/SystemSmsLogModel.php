<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSmsLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_sms_log';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'channel_id' => 'integer', # 短信渠道编号
        'channel_code' => 'string', # 短信渠道编码
        'template_id' => 'integer', # 模板编号
        'template_code' => 'string', # 模板编码
        'template_type' => 'boolean', # 短信类型
        'template_content' => 'string', # 短信内容
        'template_params' => 'string', # 短信参数
        'api_template_id' => 'string', # 短信 API 的模板编号
        'mobile' => 'string', # 手机号
        'user_id' => 'integer', # 用户编号
        'user_type' => 'boolean', # 用户类型
        'send_status' => 'boolean', # 发送状态
        'send_time' => 'time', # 发送时间
        'api_send_code' => 'string', # 短信 API 发送结果的编码
        'api_send_msg' => 'string', # 短信 API 发送失败的提示
        'api_request_id' => 'string', # 短信 API 发送返回的唯一请求 ID
        'api_serial_no' => 'string', # 短信 API 发送返回的序号
        'receive_status' => 'boolean', # 接收状态
        'receive_time' => 'time', # 接收时间
        'api_receive_code' => 'string', # API 接收结果的编码
        'api_receive_msg' => 'string', # API 接收结果的说明
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
