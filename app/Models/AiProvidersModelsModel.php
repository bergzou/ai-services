<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiProvidersModelsModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'ai_providers_models';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer',
        'name' => 'string', # 产品名称
        'slug' => 'string', # 唯一标识
        'endpoint' => 'string', # API端点
        'api_key' => 'string', # APi key
        'status' => 'integer', # 状态：0：禁用 1：启用
        'created_by' => 'string', # 创建人名称
        'created_at' => 'datetime', # 创建时间
        'updated_by' => 'string', # 更新人名称
        'updated_at' => 'datetime', # 更新时间
    ];

}
