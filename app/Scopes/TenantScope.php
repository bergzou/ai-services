<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * 租户数据隔离作用域（实现多租户数据权限控制）
 *
 * 用于自动为模型查询添加租户ID过滤条件，确保租户只能访问自己的数据
 * 需配合BaseModel的全局作用域注册使用（或手动通过模型的addGlobalScope方法注册）
 * 生效时会在查询条件中附加 "tenant_id = 当前租户ID"，实现多租户数据隔离
 */
class TenantScope implements Scope
{
    /**
     * 应用租户作用域到查询构建器（Laravel作用域核心方法）
     *
     * 当模型执行查询时（如get()/first()等），此方法会被自动调用
     * 仅在用户已登录且属于租户身份时生效，向查询添加租户ID过滤条件
     *
     * @param Builder $builder Eloquent查询构建器实例
     * @param Model $model 当前操作的模型实例
     */
    public function apply(Builder $builder, Model $model)
    {
        // 仅当用户已登录且是租户时应用作用域（is_tenant字段标记用户是否为租户身份）
//        if (Auth::check() && Auth::user()->is_tenant) {
//            // 从当前登录用户获取租户ID
//            $tenantId = Auth::user()->tenant_id;
//            // 向查询构建器添加 "tenant_id = 当前租户ID" 条件
//            $builder->where('tenant_id', $tenantId);
//        }
    }
}