<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ReturnedOrderController;
use App\Http\Controllers\Web\ReturnedClaimOrderController;

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



Route::namespace('App\Http\Controllers\Web')->prefix('/middle')->group( function (){

    // 退件单管理
    Route::post('/order/list', [ReturnedOrderController::class, 'list']);
    Route::post('/order/listCount', [ReturnedOrderController::class, 'listCount']);
    Route::post('/order/detail', [ReturnedOrderController::class, 'detail']);
    Route::post('/order/log', [ReturnedOrderController::class, 'log']);
    Route::post('/order/operateList', [ReturnedOrderController::class, 'operateList']);
    Route::post('/order/add', [ReturnedOrderController::class, 'add']);
    Route::post('/order/edit', [ReturnedOrderController::class, 'edit']);
    Route::post('/order/export', [ReturnedOrderController::class, 'export']);
    Route::post('/order/cancel', [ReturnedOrderController::class, 'cancel']);

    Route::post('/order/selectData', [ReturnedOrderController::class, 'selectData']);

    // 认领单管理
    Route::post('/claimOrder/list', [ReturnedClaimOrderController::class, 'list']);
    Route::post('/claimOrder/listCount', [ReturnedClaimOrderController::class, 'listCount']);
    Route::post('/claimOrder/detail', [ReturnedClaimOrderController::class, 'detail']);
    Route::post('/claimOrder/productList', [ReturnedClaimOrderController::class, 'productList']);
    Route::post('/claimOrder/log', [ReturnedClaimOrderController::class, 'log']);
    Route::post('/claimOrder/claim', [ReturnedClaimOrderController::class, 'claim']);


});

















