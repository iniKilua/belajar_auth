<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
//use App\Http\Controllers\Api\ProductController;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    
    Route::middleware('jwt')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::put('updateUser', [AuthController::class, 'updateUser'])->name('updateUser');
        route::post('updateUser', [AuthController::class, 'updateUser'])->name('updateUser.options');
        route::get('itemList', [ItemController::class, 'itemList'])->name('itemList');
        route::post('addItem', [ItemController::class, 'addItem'])->name('addItem');
        route::put('updateItem/{id}', [ItemController::class, 'updateItem'])->name('updateItem');
        route::delete('deleteItem/{id}', [ItemController::class, 'deleteItem'])->name('deleteItem');
       
    });
});


//Route::middleware('jwt')->group(function () {
//    Route::apiResource('products', ProductController::class);
//});

