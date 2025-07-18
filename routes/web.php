<?php

use App\Http\Controllers\Web\UsersController;
use Illuminate\Support\Facades\Route;


Route::namespace('App\Http\Controllers\Web')->prefix('admin')->group( function (){

    // 用户管理
    Route::post('users/list', [UsersController::class, 'getList']); // 获取用户列表
    Route::post('users/add', [UsersController::class, 'add']); // 添加用户
    Route::post('users/update', [UsersController::class, 'update']); // 更新用户
    Route::post('users/delete', [UsersController::class, 'delete']); // 删除用户
    Route::post('users/detail', [UsersController::class, 'getDetail']); // 获取用户详情



});

















