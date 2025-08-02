<?php
// routes/api.php

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
Route::get('/orders/{orderCode}/status', [OrderController::class, 'getOrderStatus'])->name('api.orders.status');

// Other API routes can go here, e.g. for kitchen app to update item status
// Route::post('/order-items/{orderItem}/toggle-done', [KitchenController::class, 'toggleDone']);
