<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemTenantModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_tenant';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['name','contact_user_id','contact_name','contact_mobile','status','website','package_id','expire_time','account_count','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 租户编号
        'snowflake_id' => 'string', # 雪花Id
        'name' => 'string', # 租户名
        'contact_user_id' => 'integer', # 联系人的用户编号
        'contact_name' => 'string', # 联系人
        'contact_mobile' => 'string', # 联系手机
        'status' => 'integer', # 租户状态：1=正常， 2=停用
        'website' => 'string', # 绑定域名
        'package_id' => 'integer', # 租户套餐编号
        'expire_time' => 'time', # 过期时间
        'account_count' => 'integer', # 账号数量
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
