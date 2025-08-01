<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSocialClientModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_social_client';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['name','social_type','user_type','client_id','client_secret','agent_id','status','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by','tenant_id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'name' => 'string', # 应用名
        'social_type' => 'integer', # 社交平台的类型：10=后台， 20=微信， 21=微信公众平台， 22=微信小程序， 30=支付宝， 31=钉钉， 50=Gitee
        'user_type' => 'integer', # 用户类型：10：会员， 20：管理员
        'client_id' => 'string', # 客户端编号
        'client_secret' => 'string', # 客户端密钥
        'agent_id' => 'string', # 代理编号
        'status' => 'integer', # 状态
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
