<?php

use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/initial-balance', [TransactionController::class, 'initialBalance'])->name('v1.initial-balance');
    Route::post('/payment', [TransactionController::class, 'payment'])->name('v1.payment');
    Route::get('/balance/{machine}', [TransactionController::class, 'balance'])->name('v1.balance');
    Route::post('/withdraw/{machine}', [TransactionController::class, 'withdraw'])->name('v1.withdraw');
    Route::get('/transactions/{machine}', [TransactionController::class, 'transactions'])->name('v1.transactions');
});
