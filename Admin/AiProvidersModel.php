<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiProvidersModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'ai_providers';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['name','code','base_uri','api_key','status','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 自增ID
        'snowflake_id' => 'string', # 雪花ID
        'name' => 'string', # 服务商名称
        'code' => 'string', # 服务商编码（唯一）
        'base_uri' => 'string', # 基础 API 地址
        'api_key' => 'string', # 默认 API Key
        'status' => 'integer', # 状态：1=启用，0=禁用
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
