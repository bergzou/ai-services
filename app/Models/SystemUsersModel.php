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
        'snowflake_id' => 'string', # 雪花Id
        'username' => 'string', # 用户账号
        'password' => 'string', # 密码
        'nickname' => 'string', # 用户昵称
        'remark' => 'string', # 备注
        'dept_id' => 'integer', # 部门ID
        'post_ids' => 'string', # 岗位编号数组
        'email' => 'string', # 用户邮箱
        'mobile' => 'string', # 手机号码
        'sex' => 'integer', # 用户性别
        'avatar' => 'string', # 头像地址
        'status' => 'integer', # 帐号状态： 1=正常， 2=停用
        'login_ip' => 'string', # 最后登录IP
        'login_date' => 'date', # 最后登录时间
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
        'tenant_id' => 'integer', # 租户编号
        'level' => 'integer', # 会员等级：10=普通会员， 20=黄金会员， 30=铂金会员， 40=砖石会员， 50=终生会员
    ];

}
