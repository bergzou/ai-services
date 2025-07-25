<?php

use App\Http\Controllers\OpenApi\AuthController;
use Illuminate\Support\Facades\Route;


Route::namespace('App\Http\Controllers\OpenApi')->prefix('open/api')->group( function (){

    Route::get('captcha', [AuthController::class, 'captcha']);
    Route::post('send/sms', [AuthController::class, 'sendBySMS']);
    Route::post('register/username', [AuthController::class, 'registerByUsername']);
    Route::post('register/mobile', [AuthController::class, 'registerByMobile']);
    Route::post('login/username', [AuthController::class, 'loginByUsername']);
    Route::post('login/mobile', [AuthController::class, 'loginByMobile']);

});












