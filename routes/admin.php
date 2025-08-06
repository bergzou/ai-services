<?php


use App\Http\Controllers\Admin\SystemDeptController;
use App\Http\Controllers\Admin\SystemMenuController;
use App\Http\Controllers\Admin\SystemPostController;
use App\Http\Controllers\Admin\SystemRoleController;
use App\Http\Controllers\Admin\SystemTenantController;
use App\Http\Controllers\Admin\SystemTenantPackageController;
use App\Http\Controllers\Admin\SystemUsersController;
use Illuminate\Support\Facades\Route;


//Route::get('/system/tenant/get-by-website', [SystemController::class, 'tenantGetByWebsite']);

Route::namespace('App\Http\Controllers\Admin')->prefix('admin')->group( function (){


    // 用户管理
    Route::post('/system/users/list', [SystemUsersController::class, 'getList']); // 用户管理-用户列表
    Route::post('/system/users/add', [SystemUsersController::class, 'add']); // 用户管理-添加用户
    Route::post('/system/users/update', [SystemUsersController::class, 'update']); // 用户管理-更新用户
    Route::post('/system/users/delete', [SystemUsersController::class, 'delete']); // 用户管理-删除用户
    Route::post('/system/users/detail', [SystemUsersController::class, 'getDetail']); // 用户管理-用户详情





    // 菜单管理
    Route::post('/system/menu/list', [SystemMenuController::class, 'getList']); // 菜单管理-菜单列表
    Route::post('/system/menu/add', [SystemMenuController::class, 'add']); // 菜单管理-添加菜单
    Route::post('/system/menu/update', [SystemMenuController::class, 'update']); // 菜单管理-更新菜单
    Route::post('/system/menu/delete', [SystemMenuController::class, 'delete']); // 菜单管理-删除菜单
    Route::post('/system/menu/detail', [SystemMenuController::class, 'getDetail']); // 菜单管理-菜单详情

    // 岗位管理
    Route::post('/system/post/list', [SystemPostController::class,'getList']); // 岗位管理-岗位列表
    Route::post('/system/post/add', [SystemPostController::class,'add']); // 岗位管理-添加岗位
    Route::post('/system/post/update', [SystemPostController::class,'update']); // 岗位管理-更新岗位
    Route::post('/system/post/delete', [SystemPostController::class,'delete']); // 岗位管理-删除岗位
    Route::post('/system/post/detail', [SystemPostController::class,'getDetail']); // 岗位管理-岗位详情


    // 部门管理
    Route::post('/system/dept/list', [SystemDeptController::class,'getList']); // 部门管理-部门列表
    Route::post('/system/dept/add', [SystemDeptController::class,'add']); // 部门管理-添加部门
    Route::post('/system/dept/update', [SystemDeptController::class,'update']); // 部门管理-更新部门
    Route::post('/system/dept/delete', [SystemDeptController::class,'delete']); // 部门管理-删除部门
    Route::post('/system/dept/detail', [SystemDeptController::class,'getDetail']); // 部门管理-部门详情


    // 租户管理
    Route::post('/system/tenant/list', [SystemTenantController::class, 'getList']); // 租户管理-租户列表
    Route::post('/system/tenant/add', [SystemTenantController::class, 'add']); // 租户管理-添加租户
    Route::post('/system/tenant/update', [SystemTenantController::class, 'update']); // 租户管理-更新租户
    Route::post('/system/tenant/delete', [SystemTenantController::class, 'delete']); // 租户管理-删除租户
    Route::post('/system/tenant/detail', [SystemTenantController::class, 'getDetail']); // 租户管理-租户详情

    // 租户套餐管理
    Route::post('/system/tenant/package/list', [SystemTenantPackageController::class, 'getList']); // 租户套餐管理-套餐列表
    Route::post('/system/tenant/package/add', [SystemTenantPackageController::class, 'add']); // 租户套餐管理-添加套餐
    Route::post('/system/tenant/package/update', [SystemTenantPackageController::class, 'update']); // 租户套餐管理-更新套餐
    Route::post('/system/tenant/package/delete', [SystemTenantPackageController::class, 'delete']); // 租户套餐管理-删除套餐
    Route::post('/system/tenant/package/detail', [SystemTenantPackageController::class, 'getDetail']); // 租户套餐管理-套餐详情




});












