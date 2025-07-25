<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraApiAccessLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_api_access_log';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 日志主键
        'trace_id' => 'string', # 链路追踪编号
        'user_id' => 'integer', # 用户编号
        'user_type' => 'boolean', # 用户类型
        'application_name' => 'string', # 应用名
        'request_method' => 'string', # 请求方法名
        'request_url' => 'string', # 请求地址
        'request_params' => 'string', # 请求参数
        'response_body' => 'string', # 响应结果
        'user_ip' => 'string', # 用户 IP
        'user_agent' => 'string', # 浏览器 UA
        'operate_module' => 'string', # 操作模块
        'operate_name' => 'string', # 操作名
        'operate_type' => 'boolean', # 操作分类
        'begin_time' => 'time', # 开始请求时间
        'end_time' => 'time', # 结束请求时间
        'duration' => 'integer', # 执行时长
        'result_code' => 'integer', # 结果码
        'result_msg' => 'string', # 结果提示
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
        'tenant_id' => 'integer', # 租户编号
    ];

}
