<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ReturnedOrderController;






Route::namespace('App\Http\Controllers\Web')->prefix('/middle')->group( function (){

    // 退件单管理
    Route::post('/order/list', [ReturnedOrderController::class, 'list']);

});

















