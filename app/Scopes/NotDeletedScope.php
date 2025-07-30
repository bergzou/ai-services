<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * 未删除记录过滤作用域（实现模型软删除功能）
 *
 * 用于自动过滤已标记为删除的数据库记录，配合BaseModel的全局作用域注册使用
 * 所有继承自BaseModel的模型（如SystemMenuModel）会自动应用此作用域
 * 生效时会在查询条件中附加 "is_deleted = 0"，仅返回未删除的记录
 */
class NotDeletedScope implements Scope
{
    /**
     * 应用全局查询作用域（Laravel作用域核心方法）
     *
     * 当模型执行查询时（如get()/first()等），此方法会被自动调用
     * 向查询构建器添加 "is_deleted = 0" 条件，过滤已删除的记录
     *
     * @param Builder $builder Eloquent查询构建器实例
     * @param Model $model 当前操作的模型实例
     */
    public function apply(Builder $builder, Model $model)
    {
        // 附加未删除条件：is_deleted字段值为0表示未删除
        $builder->where('is_deleted', 0);
    }

}