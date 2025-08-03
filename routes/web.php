<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\KitchenController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('user.home');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/order/{orderCode}/success', [OrderController::class, 'success'])->name('user.order.success');
Route::get('/order/{orderCode}', [OrderController::class, 'show'])->name('user.order.show');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::get('kitchen', [KitchenController::class, 'index'])
            ->name('kitchen.index')
            ->middleware('role:chef,admin,owner');
    });
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
