<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Internal\OmsReturnedOrderController;
use App\Http\Controllers\Internal\OmsReturnedOrderClaimController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/





Route::namespace('App\Http\Controllers\Internal')->prefix('internal')->group( function (){

    Route::post('/order/cancel', [OmsReturnedOrderController::class, 'cancel']);
    Route::post('/order/detail', [OmsReturnedOrderController::class, 'detail']);
    Route::post('/order/save', [OmsReturnedOrderController::class, 'save']);


});









