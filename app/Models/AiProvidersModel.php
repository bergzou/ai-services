<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiProvidersModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'ai_providers';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer',
        'name' => 'string', # 产品名称
        'slug' => 'string', # 唯一标识
        'base_url' => 'string', # API基础地址
        'api_key' => 'string', # APi key
        'created_by' => 'string', # 创建人名称
        'created_at' => 'datetime', # 创建时间
        'updated_by' => 'string', # 更新人名称
        'updated_at' => 'datetime', # 更新时间
    ];

}
