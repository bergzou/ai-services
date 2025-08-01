<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSocialUserBindModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_social_user_bind';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['user_id','user_type','social_type','social_user_id','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by','tenant_id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 主键(自增策略)
        'snowflake_id' => 'string', # 雪花Id
        'user_id' => 'integer', # 用户编号
        'user_type' => 'integer', # 用户类型：10：会员， 20：管理员
        'social_type' => 'integer', # 社交平台的类型
        'social_user_id' => 'integer', # 社交用户的编号
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
        'tenant_id' => 'integer', # 租户编号
    ];

}
