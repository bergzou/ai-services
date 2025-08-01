<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraApiAccessLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_api_access_log';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['trace_id','user_id','user_type','application_name','request_method','request_url','request_params','response_body','user_ip','user_agent','operate_module','operate_name','operate_type','begin_time','end_time','duration','result_code','result_msg','tenant_id','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 日志主键
        'snowflake_id' => 'string', # 雪花Id
        'trace_id' => 'string', # 链路追踪编号
        'user_id' => 'integer', # 用户编号
        'user_type' => 'integer', # 用户类型：10=会员， 20=管理员
        'application_name' => 'string', # 应用名
        'request_method' => 'string', # 请求方法名
        'request_url' => 'string', # 请求地址
        'request_params' => 'string', # 请求参数
        'response_body' => 'string', # 响应结果
        'user_ip' => 'string', # 用户 IP
        'user_agent' => 'string', # 浏览器 UA
        'operate_module' => 'string', # 操作模块
        'operate_name' => 'string', # 操作名
        'operate_type' => 'integer', # 操作分类：10=查询， 20=新增， 30=修改， 40=删除， 50=导出， 60=导入， 70=其它
        'begin_time' => 'time', # 开始请求时间
        'end_time' => 'time', # 结束请求时间
        'duration' => 'integer', # 执行时长
        'result_code' => 'integer', # 结果码
        'result_msg' => 'string', # 结果提示
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
