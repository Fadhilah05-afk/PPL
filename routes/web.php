<?php

use App\Http\Controllers\CashierDashboardController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Customer Self-Ordering
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Cashier & Admin Dashboard
Route::get('/kasir', [CashierDashboardController::class, 'index'])->name('kasir.dashboard');
Route::get('/dashboard', [CashierDashboardController::class, 'index']);
Route::get('/admin', [CashierDashboardController::class, 'index']);

// Order API Endpoints
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/live', [OrderController::class, 'liveUpdates'])->name('orders.live');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePayment'])->name('orders.payment');
Route::get('/orders-analytics', [OrderController::class, 'analytics'])->name('orders.analytics');
Route::get('/orders-export', [OrderController::class, 'exportCsv'])->name('orders.export');
