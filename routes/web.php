<?php


use App\Http\Controllers\AuthController;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Controller;

Route::post('test', [Controller::class, 'test']);



Route::prefix('auth')->group(function () {

    // 基础登录
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login/mobile', [AuthController::class, 'loginWithMobile']);

    // 第三方登录
    Route::get('social/{provider}/redirect', [AuthController::class, 'socialRedirect']);
    Route::get('social/{provider}/callback', [AuthController::class, 'socialCallback']);

    // 令牌管理
    Route::post('validate', [AuthController::class, 'validateToken']);
    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth:api');
});


// 受保护的路由示例
Route::middleware('auth:api')->group(function () {
    Route::get('protected', function () {
        return response()->json(['message' => '访问受保护资源成功']);
    });
});












