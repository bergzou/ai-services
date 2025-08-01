<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemOauth2ClientModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_oauth2_client';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'client_id' => 'string', # 客户端编号
        'secret' => 'string', # 客户端密钥
        'name' => 'string', # 应用名
        'logo' => 'string', # 应用图标
        'description' => 'string', # 应用描述
        'status' => 'integer', # 状态
        'access_token_validity_seconds' => 'integer', # 访问令牌的有效期
        'refresh_token_validity_seconds' => 'integer', # 刷新令牌的有效期
        'redirect_uris' => 'string', # 可重定向的 URI 地址
        'authorized_grant_types' => 'string', # 授权类型
        'scopes' => 'string', # 授权范围
        'auto_approve_scopes' => 'string', # 自动通过的授权范围
        'authorities' => 'string', # 权限
        'resource_ids' => 'string', # 资源
        'additional_information' => 'string', # 附加信息
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
