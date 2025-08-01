<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSmsTemplateModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_sms_template';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['type','status','code','name','content','params','remark','api_template_id','channel_id','channel_code','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'type' => 'integer', # 模板类型
        'status' => 'integer', # 开启状态
        'code' => 'string', # 模板编码
        'name' => 'string', # 模板名称
        'content' => 'string', # 模板内容
        'params' => 'string', # 参数数组
        'remark' => 'string', # 备注
        'api_template_id' => 'string', # 短信 API 的模板编号
        'channel_id' => 'integer', # 短信渠道编号
        'channel_code' => 'string', # 短信渠道编码
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
