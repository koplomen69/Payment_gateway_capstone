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
// Tambahkan route untuk snap payment
Route::get('/transactions/{transaction}/snap', [TransactionController::class, 'snapPayment'])->name('transactions.snap');
// Service Routes
Route::resource('services', ServiceController::class);

// Customer Routes
Route::resource('customers', CustomerController::class);

// Di dalam file routes/web.php, tambahkan baris ini (bisa di bawah route resource)
Route::post('/transactions/{transaction}/resend-whatsapp', [TransactionController::class, 'resendWhatsapp'])
     ->name('transactions.resend-whatsapp');

// Debug route - tambahkan di web.php
Route::get('/debug/midtrans', function() {
    $transaction = \App\Models\Transaction::where('payment_method', 'midtrans')
        ->whereNotNull('midtrans_snap_token')
        ->latest()
        ->first();

    if (!$transaction) {
        return 'No midtrans transaction found';
    }

    return view('transactions.snap', compact('transaction'));
});


Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/api/reports/data', [ReportController::class, 'getReportData']);
// Reports Routes
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
});
Route::resource('customers', CustomerController::class);
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
// Customer Routes
Route::resource('customers', CustomerController::class);

// Report Routes
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
});
Route::middleware('auth:sanctum')->group(function () {
    // API Reports
    Route::get('/reports/data', [ReportController::class, 'getReportData']);
});
// Report Routes
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
});
