<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\EmailAuthController;
use App\Http\Controllers\Api\V1\Auth\PhoneAuthController;
use App\Http\Controllers\Api\V1\CartController as ApiCartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController as ApiCheckoutController;
use App\Http\Controllers\Api\V1\ConversationController as ApiConversationController;
use App\Http\Controllers\Api\V1\ConversationMessageController;
use App\Http\Controllers\Api\V1\DisputeController;
use App\Http\Controllers\Api\V1\LmsIntegrationController;
use App\Http\Controllers\Api\V1\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\V1\NotificationTemplateController;
use App\Http\Controllers\Api\V1\OrderController as ApiOrderController;
use App\Http\Controllers\Api\V1\PaymentProofController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\Api\V1\ProductInventoryController;
use App\Http\Controllers\Api\V1\ProductSearchController;
use App\Http\Controllers\Api\V1\ReturnController;
use App\Http\Controllers\Api\V1\ShipmentController;
use App\Http\Controllers\Api\V1\ShippingController;
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
        Route::post('integrations/lms/webhooks', [LmsIntegrationController::class, 'webhook']);
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

        Route::post('checkout/estimate', [ApiCheckoutController::class, 'estimate']);
        Route::post('checkout/create-order', [ApiCheckoutController::class, 'create']);

        Route::get('orders', [ApiOrderController::class, 'index']);
        Route::get('orders/{orderNumber}', [ApiOrderController::class, 'show']);
        Route::put('orders/{orderNumber}/cancel', [ApiOrderController::class, 'cancel']);
        Route::put('orders/{orderNumber}/status', [ApiOrderController::class, 'updateStatus']);
        Route::put('orders/{orderNumber}/confirm-delivery', [ApiOrderController::class, 'confirmDelivery']);
        Route::get('orders/{orderNumber}/timeline', [ApiOrderController::class, 'timeline']);

        Route::post('payments/proofs', [PaymentProofController::class, 'store']);
        Route::get('payments/proofs/{paymentProof}', [PaymentProofController::class, 'show']);
        Route::put('payments/proofs/{paymentProof}/verify', [PaymentProofController::class, 'verify']);
        Route::get('notifications', [ApiNotificationController::class, 'index']);
        Route::post('notifications/send', [ApiNotificationController::class, 'send']);

        Route::get('notification-templates', [NotificationTemplateController::class, 'index']);
        Route::post('notification-templates', [NotificationTemplateController::class, 'store']);
        Route::put('notification-templates/{template}', [NotificationTemplateController::class, 'update']);

        Route::get('shipping/services', [ShippingController::class, 'services']);
        Route::get('shipping/hubs', [ShippingController::class, 'hubs']);
        Route::post('shipping/quotes', [ShippingController::class, 'quote']);

        Route::post('shipments', [ShipmentController::class, 'store']);
        Route::get('shipments/{shipment}', [ShipmentController::class, 'show']);
        Route::get('shipments/{shipment}/events', [ShipmentController::class, 'events']);
        Route::post('shipments/{shipment}/cancel', [ShipmentController::class, 'cancel']);
        Route::post('pod/{shipment}', [ShipmentController::class, 'storeProofOfDelivery']);
        Route::get('pod/{shipment}', [ShipmentController::class, 'showProofOfDelivery']);

        Route::post('integrations/lms/shipments/sync', [LmsIntegrationController::class, 'sync']);
        Route::get('integrations/lms/health', [LmsIntegrationController::class, 'health']);

        Route::get('disputes', [DisputeController::class, 'index']);
        Route::post('disputes', [DisputeController::class, 'store']);
        Route::get('disputes/{dispute}', [DisputeController::class, 'show']);
        Route::put('disputes/{dispute}', [DisputeController::class, 'update']);
        Route::post('disputes/{dispute}/messages', [DisputeController::class, 'storeMessage']);

        Route::post('returns', [ReturnController::class, 'store']);
        Route::get('returns/{returnRequest}', [ReturnController::class, 'show']);
        Route::put('returns/{returnRequest}', [ReturnController::class, 'update']);

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
