<?php

namespace App\Models;
use Tymon\JWTAuth\Contracts\JWTSubject;


class UsersModel extends BaseModel implements JWTSubject
{

    # 对应的数据库表名
    protected $table = 'users';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 用户ID
        'uuid' => 'string', # UUID
        'name' => 'string', # 用户名
        'password' => 'string', # 加密密码
        'email' => 'string', # 邮箱
        'mobile' => 'string', # 手机号
        'points' => 'integer', # 会员积分
        'level' => 'integer', # 会员等级：10=普通会员,20=黄金会员,30=铂金会员,40=钻石会员,50=终身会员
        'status' => 'integer', # 状态：0=禁用,1=启用,2=未激活
        'avatar' => 'string', # 头像路径
        'created_by' => 'string', # 创建人名称
        'created_at' => 'datetime', # 创建时间
        'updated_by' => 'string', # 更新人名称
        'updated_at' => 'datetime', # 更新时间
    ];


    protected $fillable = ['name', 'mobile', 'password'];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
