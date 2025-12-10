<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Webhook Midtrans — HARUS ADA DI SINI (bukan di web.php!)
Route::post('/midtrans/notification', [TransactionController::class, 'handleMidtransNotification'])
     ->name('midtrans.notification');


// Opsional: kalau kamu mau tambah API lain nanti
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
