<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Dashboard Route
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Transaction Routes
Route::resource('transactions', TransactionController::class);
Route::post('/transactions/midtrans-notification', [TransactionController::class, 'handleMidtransNotification'])
    ->name('transactions.midtrans-notification');
Route::get('/transactions/{transaction}/check-status', [TransactionController::class, 'checkPaymentStatus'])
    ->name('transactions.check-status');

// Service Routes
Route::resource('services', ServiceController::class);

// Customer Routes
Route::resource('customers', CustomerController::class);

// Report Routes
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
