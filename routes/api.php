<?php
// routes/api.php

use App\Http\Controllers\Admin\KitchenController;
use App\Http\Controllers\OrderController;
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

// API route to get order status/details
Route::get('/orders/{orderCode}/status', [OrderController::class, 'getOrderStatus'])
    ->middleware('throttle:user-order-status')
    ->name('api.orders.status');

Route::prefix('kitchen')->name('api.kitchen.')->group(function () {
    Route::get('orders', [KitchenController::class, 'getOrdersApi'])
        ->middleware('throttle:kitchen-refresh')
        ->name('orders');
    Route::patch('order-items/{orderItem}/toggle-done', [KitchenController::class, 'toggleOrderItemDone'])->name('order-items.toggle-done');
    Route::patch('orders/{order}/status', [KitchenController::class, 'updateOrderStatus'])->name('orders.update-status');
});
