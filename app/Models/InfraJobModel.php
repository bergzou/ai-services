<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraJobModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_job';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 任务编号
        'snowflake_id' => 'string', # 雪花Id
        'name' => 'string', # 任务名称
        'status' => 'boolean', # 任务状态
        'handler_name' => 'string', # 处理器的名字
        'handler_param' => 'string', # 处理器的参数
        'cron_expression' => 'string', # CRON 表达式
        'retry_count' => 'integer', # 重试次数
        'retry_interval' => 'integer', # 重试间隔
        'monitor_timeout' => 'integer', # 监控超时时间
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
