<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraApiErrorLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_api_error_log';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['trace_id','user_id','user_type','application_name','request_method','request_url','request_params','user_ip','user_agent','exception_time','exception_name','exception_message','exception_root_cause_message','exception_stack_trace','exception_class_name','exception_file_name','exception_method_name','exception_line_number','process_status','process_time','process_user_id','tenant_id','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'trace_id' => 'string', # 链路追踪编号
        'user_id' => 'integer', # 用户编号
        'user_type' => 'integer', # 用户类型：10=会员， 20=管理员
        'application_name' => 'string', # 应用名
        'request_method' => 'string', # 请求方法名
        'request_url' => 'string', # 请求地址
        'request_params' => 'string', # 请求参数
        'user_ip' => 'string', # 用户 IP
        'user_agent' => 'string', # 浏览器 UA
        'exception_time' => 'time', # 异常发生时间
        'exception_name' => 'string', # 异常名
        'exception_message' => 'string', # 异常导致的消息
        'exception_root_cause_message' => 'string', # 异常导致的根消息
        'exception_stack_trace' => 'string', # 异常的栈轨迹
        'exception_class_name' => 'string', # 异常发生的类全名
        'exception_file_name' => 'string', # 异常发生的类文件
        'exception_method_name' => 'string', # 异常发生的方法名
        'exception_line_number' => 'integer', # 异常发生的方法所在行
        'process_status' => 'integer', # 处理状态：10：未处理，10：已处理，10：已忽略
        'process_time' => 'time', # 处理时间
        'process_user_id' => 'integer', # 处理用户编号
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
