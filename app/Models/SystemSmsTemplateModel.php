<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSmsTemplateModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_sms_template';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'snowflake_id' => 'string', # 雪花Id
        'type' => 'boolean', # 模板类型
        'status' => 'boolean', # 开启状态
        'code' => 'string', # 模板编码
        'name' => 'string', # 模板名称
        'content' => 'string', # 模板内容
        'params' => 'string', # 参数数组
        'remark' => 'string', # 备注
        'api_template_id' => 'string', # 短信 API 的模板编号
        'channel_id' => 'integer', # 短信渠道编号
        'channel_code' => 'string', # 短信渠道编码
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'boolean', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
