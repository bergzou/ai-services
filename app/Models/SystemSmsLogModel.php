<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSmsLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_sms_log';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['channel_id','channel_code','template_id','template_code','template_type','template_content','template_params','api_template_id','mobile','user_id','user_type','send_status','send_time','api_send_code','api_send_msg','api_request_id','api_serial_no','receive_status','receive_time','api_receive_code','api_receive_msg','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'channel_id' => 'integer', # 短信渠道编号
        'channel_code' => 'string', # 短信渠道编码
        'template_id' => 'integer', # 模板编号
        'template_code' => 'string', # 模板编码
        'template_type' => 'integer', # 短信类型
        'template_content' => 'string', # 短信内容
        'template_params' => 'string', # 短信参数
        'api_template_id' => 'string', # 短信 API 的模板编号
        'mobile' => 'string', # 手机号
        'user_id' => 'integer', # 用户编号
        'user_type' => 'integer', # 用户类型
        'send_status' => 'integer', # 发送状态
        'send_time' => 'time', # 发送时间
        'api_send_code' => 'string', # 短信 API 发送结果的编码
        'api_send_msg' => 'string', # 短信 API 发送失败的提示
        'api_request_id' => 'string', # 短信 API 发送返回的唯一请求 ID
        'api_serial_no' => 'string', # 短信 API 发送返回的序号
        'receive_status' => 'integer', # 接收状态
        'receive_time' => 'time', # 接收时间
        'api_receive_code' => 'string', # API 接收结果的编码
        'api_receive_msg' => 'string', # API 接收结果的说明
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
