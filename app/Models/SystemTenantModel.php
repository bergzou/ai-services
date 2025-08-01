<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemTenantModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_tenant';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
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
        'creator' => 'string', # 创建者
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
