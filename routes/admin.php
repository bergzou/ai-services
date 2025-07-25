<?php

use App\Http\Controllers\Admin\SystemController;
use Illuminate\Support\Facades\Route;

///admin-api/system/tenant/get-by-website
Route::namespace('App\Http\Controllers\Admin')->prefix('admin-api')->group( function (){

    Route::get('/system/tenant/get-by-website', [SystemController::class, 'tenantGetByWebsite']);


});












