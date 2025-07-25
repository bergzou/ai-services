<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemDictDataModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_dict_data';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 字典编码
        'sort' => 'integer', # 字典排序
        'label' => 'string', # 字典标签
        'value' => 'string', # 字典键值
        'dict_type' => 'string', # 字典类型
        'status' => 'boolean', # 状态（0正常 1停用）
        'color_type' => 'string', # 颜色类型
        'css_class' => 'string', # css 样式
        'remark' => 'string', # 备注
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
