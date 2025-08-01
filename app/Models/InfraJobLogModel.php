<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraJobLogModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_job_log';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['job_id','handler_name','handler_param','execute_index','begin_time','end_time','duration','status','result','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 日志编号
        'snowflake_id' => 'string', # 雪花Id
        'job_id' => 'integer', # 任务编号
        'handler_name' => 'string', # 处理器的名字
        'handler_param' => 'string', # 处理器的参数
        'execute_index' => 'integer', # 第几次执行
        'begin_time' => 'time', # 开始执行时间
        'end_time' => 'time', # 结束执行时间
        'duration' => 'integer', # 执行时长
        'status' => 'integer', # 任务状态
        'result' => 'string', # 结果数据
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
