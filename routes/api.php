<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['prefix' => 'v1'], function () {

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'register'], function () {
    Route::post('/', [\App\Http\Controllers\RegistrationController::class, 'store']);
    Route::post('resend/{email}', [\App\Http\Controllers\Auth\EmailController::class, 'resend']);

});
Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'product'], function () {
            Route::post('/', [\App\Http\Controllers\ProductController::class, 'storeProduct'])->middleware('checkProduct');
            Route::get('/all-product', [\App\Http\Controllers\ProductController::class, 'getAllProduct']);
            Route::get('/all-user-product', [\App\Http\Controllers\ProductController::class, 'getAllUserProduct']);
            Route::post('/update-product/{product}', [\App\Http\Controllers\ProductController::class, 'updateProduct']);
            Route::get('/get-my-product', [\App\Http\Controllers\ProductController::class, 'getMyProduct']);
            Route::get('/product-details/{product}', [\App\Http\Controllers\ProductController::class, 'productDetails']);


        });

        Route::group(['prefix' => 'email'], function () {
            Route::post('verify', [\App\Http\Controllers\Auth\EmailController::class, 'verify']);
            Route::post('change-email', [\App\Http\Controllers\Auth\EmailController::class, 'changeEmail']);
        });

    });

    
});