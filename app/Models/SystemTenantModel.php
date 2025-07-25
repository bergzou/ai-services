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
        'name' => 'string', # 租户名
        'contact_user_id' => 'integer', # 联系人的用户编号
        'contact_name' => 'string', # 联系人
        'contact_mobile' => 'string', # 联系手机
        'status' => 'boolean', # 租户状态（0正常 1停用）
        'website' => 'string', # 绑定域名
        'package_id' => 'integer', # 租户套餐编号
        'expire_time' => 'time', # 过期时间
        'account_count' => 'integer', # 账号数量
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
