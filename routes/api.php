<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\EmailAuthController;
use App\Http\Controllers\Api\V1\Auth\PhoneAuthController;
use App\Http\Controllers\Api\V1\CartController as ApiCartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\Api\V1\ProductInventoryController;
use App\Http\Controllers\Api\V1\ProductSearchController;
use App\Http\Controllers\Api\V1\ConversationController as ApiConversationController;
use App\Http\Controllers\Api\V1\ConversationMessageController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::withoutMiddleware(['auth'])->group(function () {
        Route::post('signin/{role}/phone', [PhoneAuthController::class, 'signIn'])
            ->whereIn('role', ['admin', 'user', 'vendor']);
        Route::post('verify/phone', [PhoneAuthController::class, 'verify']);

        Route::post('signin/{role}/email', [EmailAuthController::class, 'signIn'])
            ->whereIn('role', ['admin', 'user', 'vendor']);
        Route::post('resend/email', [EmailAuthController::class, 'resend']);
        Route::post('verify/email', [EmailAuthController::class, 'verify']);


        
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('search/products', ProductSearchController::class);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', AuthenticatedUserController::class);

        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);

        Route::post('categories', [CategoryController::class, 'store']);

        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);

        Route::get('products/{product}/inventory', [ProductInventoryController::class, 'show']);
        Route::put('products/{product}/inventory', [ProductInventoryController::class, 'update']);

        Route::post('products/{product}/images', [ProductImageController::class, 'store']);

        Route::get('conversations', [ApiConversationController::class, 'index']);
        Route::post('conversations', [ApiConversationController::class, 'store']);
        Route::get('conversations/{conversation}/messages', [ConversationMessageController::class, 'index']);
        Route::post('conversations/{conversation}/messages', [ConversationMessageController::class, 'store']);

        Route::get('carts/{buyer}', [ApiCartController::class, 'index'])
            ->whereNumber('buyer');
        Route::post('carts/{buyer}/items', [ApiCartController::class, 'store'])
            ->whereNumber('buyer');
        Route::put('carts/{buyer}/items/{item}', [ApiCartController::class, 'update'])
            ->whereNumber('buyer');
        Route::delete('carts/{buyer}/items/{item}', [ApiCartController::class, 'destroy'])
            ->whereNumber('buyer');

    });
});
