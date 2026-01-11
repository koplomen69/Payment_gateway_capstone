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
    ->name('transactions.midtrans-notification')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::get('/transactions/{transaction}/check-status', [TransactionController::class, 'checkPaymentStatus'])
    ->name('transactions.check-status');
Route::get('/transactions/{transaction}/snap', [TransactionController::class, 'snapPayment'])
    ->name('transactions.snap');
Route::post('/transactions/{transaction}/resend-whatsapp', [TransactionController::class, 'resendWhatsapp'])
     ->name('transactions.resend-whatsapp');

// Service Routes
Route::resource('services', ServiceController::class);

// Customer Routes
Route::resource('customers', CustomerController::class);

// Report Routes
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/data', [ReportController::class, 'getReportData'])->name('reports.data');
});

// Debug route for Midtrans testing
Route::get('/debug/midtrans', function() {
    $transaction = \App\Models\Transaction::where('payment_method', 'midtrans')
        ->whereNotNull('midtrans_snap_token')
        ->latest()
        ->first();

    if (!$transaction) {
        return 'No midtrans transaction found';
    }

    return view('transactions.snap', compact('transaction'));
})->name('debug.midtrans');



