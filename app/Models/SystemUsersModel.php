<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemUsersModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_users';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 用户ID
        'username' => 'string', # 用户账号
        'password' => 'string', # 密码
        'nickname' => 'string', # 用户昵称
        'remark' => 'string', # 备注
        'dept_id' => 'integer', # 部门ID
        'post_ids' => 'string', # 岗位编号数组
        'email' => 'string', # 用户邮箱
        'mobile' => 'string', # 手机号码
        'sex' => 'boolean', # 用户性别
        'avatar' => 'string', # 头像地址
        'status' => 'boolean', # 帐号状态（0正常 1停用）
        'login_ip' => 'string', # 最后登录IP
        'login_date' => 'date', # 最后登录时间
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
        'tenant_id' => 'integer', # 租户编号
    ];

}
