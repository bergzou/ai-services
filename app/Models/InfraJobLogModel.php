<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraJobLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_job_log';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 日志编号
        'snowflake_id' => 'string', # 雪花Id
        'job_id' => 'integer', # 任务编号
        'handler_name' => 'string', # 处理器的名字
        'handler_param' => 'string', # 处理器的参数
        'execute_index' => 'boolean', # 第几次执行
        'begin_time' => 'time', # 开始执行时间
        'end_time' => 'time', # 结束执行时间
        'duration' => 'integer', # 执行时长
        'status' => 'boolean', # 任务状态
        'result' => 'string', # 结果数据
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
