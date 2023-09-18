<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductStatusController;
use App\Http\Controllers\Webhooks\PaystackWebhookController;

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
            Route::post('/update-product/{product}', [\App\Http\Controllers\ProductController::class, 'updateProduct'])->middleware('checkProduct');
            Route::get('/get-my-product', [\App\Http\Controllers\ProductController::class, 'getMyProduct']);
            Route::get('/product-details/{product}', [\App\Http\Controllers\ProductController::class, 'productDetails']);
        });

        Route::group(['prefix' => 'email'], function () {
            Route::post('verify', [\App\Http\Controllers\Auth\EmailController::class, 'verify']);
            Route::post('change-email', [\App\Http\Controllers\Auth\EmailController::class, 'changeEmail']);
        });

        Route::group(['prefix' => 'profile'], function () {
            Route::post('update', [\App\Http\Controllers\UserController::class, 'updateProfile'])->middleware('checkProduct');
            Route::post('/logout', [\App\Http\Controllers\UserController::class, 'logout']);

        });

        Route::group(['prefix' => 'transaction'], function () {
            Route::post('/', [\App\Http\Controllers\TransactionController::class, 'myTransactions']); 
            Route::get('/single-transaction/{transaction}', [\App\Http\Controllers\TransactionController::class, 'singleTransaction']); 
            Route::post('product/{product}/payment', [\App\Http\Controllers\PaymentController::class, 'purchaseProduct']); 
            Route::get('/user-purchase', [\App\Http\Controllers\TransactionController::class, 'userPurchasehistory']); 
        });

        Route::group(['prefix' => 'status'], function () {
            Route::get('/', [ProductStatusController::class, 'getProductStatus']);
            Route::get('/single-product-status/{status}', [ProductStatusController::class, 'getSingleProductStatus']);
            Route::post('/add-status', [ProductStatusController::class, 'addStatus']);
            Route::post('/update-product-status/{status}', [ProductStatusController::class, 'updateStatus']);
        });

        Route::group(['prefix' => 'status'], function () {
            Route::get('/', [ProductStatusController::class, 'getProductStatus']);
            Route::get('/single-product-status/{status}', [ProductStatusController::class, 'getSingleProductStatus']);
            Route::post('/add-status', [ProductStatusController::class, 'addStatus']);
            Route::post('/update-product-status/{status}', [ProductStatusController::class, 'updateStatus']);
        });

    });

    Route::group(['prefix' => 'webhook'], function () {
        Route::post('paystack', [PaystackWebhookController::class, 'handle'])->middleware('paystack');
    });
    
});