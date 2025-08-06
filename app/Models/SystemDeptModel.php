<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemDeptModel extends BaseModel
{
    # 使用Eloquent工厂模式
    use HasFactory;



    # 对应的数据库表名
    protected $table = 'system_dept';

    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）
    public $guarded = ['id','snowflake_id'];

    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了$fillable和$guarded，则只有$fillable生效）
    public $fillable = ['name','parent_id','sort','leader_user_id','phone','email','status','tenant_id','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

    # 属性类型转换（自动映射数据库类型到PHP类型）
    public $casts = [
        'id' => 'integer', # 部门id
        'snowflake_id' => 'string', # 雪花Id
        'name' => 'string', # 部门名称
        'parent_id' => 'integer', # 父部门id
        'sort' => 'integer', # 显示顺序
        'leader_user_id' => 'integer', # 负责人
        'phone' => 'string', # 联系电话
        'email' => 'string', # 邮箱
        'status' => 'integer', # 部门状态：1=启用， 2=停用
        'tenant_id' => 'integer', # 租户编号
        'created_at' => 'datetime', # 创建时间
        'created_by' => 'string', # 创建人名称
        'updated_at' => 'datetime', # 更新时间
        'updated_by' => 'string', # 更新人名称
        'is_deleted' => 'integer', # 是否删除
        'deleted_at' => 'datetime', # 删除时间
        'deleted_by' => 'string', # 删除人名称
    ];

}
