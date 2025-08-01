<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemNotifyMessageModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_notify_message';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['user_id','user_type','template_id','template_code','template_nickname','template_content','template_type','template_params','read_status','read_time','created_at','created_by','updated_at','updated_by','tenant_id','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 用户ID
        'snowflake_id' => 'string', # 雪花Id
        'user_id' => 'integer', # 用户id
        'user_type' => 'integer', # 用户类型：10=会员， 20=管理员
        'template_id' => 'integer', # 模版编号
        'template_code' => 'string', # 模板编码
        'template_nickname' => 'string', # 模版发送人名称
        'template_content' => 'string', # 模版内容
        'template_type' => 'integer', # 模版类型
        'template_params' => 'string', # 模版参数
        'read_status' => 'integer', # 是否已读
        'read_time' => 'time', # 阅读时间
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'tenant_id' => 'integer', # 租户编号
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
