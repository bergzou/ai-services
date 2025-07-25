<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemMailTemplateModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;

    # 对应的数据库表名
    protected $table = 'system_mail_template';

    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）
    protected $guarded = ['id'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    protected $casts = [
        'id' => 'integer', # 编号
        'name' => 'string', # 模板名称
        'code' => 'string', # 模板编码
        'account_id' => 'integer', # 发送的邮箱账号编号
        'nickname' => 'string', # 发送人名称
        'title' => 'string', # 模板标题
        'content' => 'string', # 模板内容
        'params' => 'string', # 参数数组
        'status' => 'boolean', # 开启状态
        'remark' => 'string', # 备注
        'creator' => 'string', # 创建者
        'create_time' => 'time', # 创建时间
        'updater' => 'string', # 更新者
        'update_time' => 'time', # 更新时间
        'deleted' => 'boolean', # 是否删除
    ];

}
