<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\EmailAuthController;
use App\Http\Controllers\Api\V1\Auth\PhoneAuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\Api\V1\ProductInventoryController;
use App\Http\Controllers\Api\V1\ProductSearchController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::withoutMiddleware(['auth'])->group(function () {
        Route::post('signin/phone', [PhoneAuthController::class, 'signIn']);
        Route::post('verify/phone', [PhoneAuthController::class, 'verify']);

        Route::post('signin/email', [EmailAuthController::class, 'signIn']);
        Route::post('resend/email', [EmailAuthController::class, 'resend']);
        Route::post('verify/email', [EmailAuthController::class, 'verify']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', AuthenticatedUserController::class);

        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);

        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);

        Route::get('products', [ProductController::class, 'index']);
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);

        Route::get('products/{product}/inventory', [ProductInventoryController::class, 'show']);
        Route::put('products/{product}/inventory', [ProductInventoryController::class, 'update']);

        Route::post('products/{product}/images', [ProductImageController::class, 'store']);

        Route::get('search/products', ProductSearchController::class);
    });
});
