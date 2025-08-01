<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemDictDataModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_dict_data';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['sort','label','value','dict_type','status','color_type','css_class','remark','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 字典编码
        'snowflake_id' => 'string', # 雪花Id
        'sort' => 'integer', # 字典排序
        'label' => 'string', # 字典标签
        'value' => 'string', # 字典键值
        'dict_type' => 'string', # 字典类型
        'status' => 'integer', # 状态：1=启用， 2=停用
        'color_type' => 'string', # 颜色类型
        'css_class' => 'string', # css 样式
        'remark' => 'string', # 备注
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
