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
        'job_id' => 'integer', # 任务编号
        'handler_name' => 'string', # 处理器的名字
        'handler_param' => 'string', # 处理器的参数
        'execute_index' => 'boolean', # 第几次执行
        'begin_time' => 'time', # 开始执行时间
        'end_time' => 'time', # 结束执行时间
        'duration' => 'integer', # 执行时长
        'status' => 'boolean', # 任务状态
        'result' => 'string', # 结果数据
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
