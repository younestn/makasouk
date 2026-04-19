<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Tailor\OrderController as TailorOrderController;
use App\Http\Controllers\Tailor\ProfileController as TailorProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('customer')->group(function () {
        Route::post('orders', [CustomerOrderController::class, 'store']);
        Route::get('orders/{order}', [CustomerOrderController::class, 'show']);
        Route::patch('orders/{order}/cancel', [CustomerOrderController::class, 'cancel']);
        Route::get('orders-history', [CustomerOrderController::class, 'history']);
        Route::post('orders/{order}/reviews', [CustomerReviewController::class, 'store']);
    });

    Route::prefix('tailor')->group(function () {
        Route::post('orders/{order}/accept', [TailorOrderController::class, 'acceptOrder']);
        Route::patch('orders/{order}/status', [TailorOrderController::class, 'updateStatus']);
        Route::patch('orders/{order}/cancel', [TailorOrderController::class, 'cancel']);
        Route::get('orders-history', [TailorOrderController::class, 'history']);
        Route::patch('availability/toggle', [TailorProfileController::class, 'toggleAvailability']);
    });

    Route::prefix('admin')->group(function () {
        Route::get('categories', [AdminCategoryController::class, 'index']);
        Route::post('categories', [AdminCategoryController::class, 'store']);

        Route::get('products', [AdminProductController::class, 'index']);
        Route::post('products', [AdminProductController::class, 'store']);

        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{order}/track', [AdminOrderController::class, 'trackOrder']);
        Route::get('orders/statistics', [AdminOrderController::class, 'statistics']);

        Route::get('users', [AdminUserController::class, 'index']);
        Route::patch('users/{user}/suspend', [AdminUserController::class, 'suspend']);
        Route::get('users/pending-tailors', [AdminUserController::class, 'pendingTailors']);
    });
});
