<?php

use App\Http\Controllers\ApiDataCenterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::controller(ApiDataCenterController::class)->group(function () {
        Route::get('users','getUsers');
    });
});