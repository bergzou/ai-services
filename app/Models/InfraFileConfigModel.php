<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InfraFileConfigModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'infra_file_config';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'name' => 'string', # 配置名
        'storage' => 'boolean', # 存储器
        'remark' => 'string', # 备注
        'master' => 'boolean', # 是否为主配置
        'config' => 'string', # 存储配置
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
