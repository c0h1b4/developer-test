<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LedgerController;

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

Route::group(['middleware' => 'api'], function ($router) {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::get('user', [UserAuthController::class, 'user']);

    Route::get('deposit/{id}', [DepositController::class, 'index']);
    Route::get('pending', [DepositController::class, 'pending']);
    Route::post('deposit', [DepositController::class, 'deposit']);
    Route::get('approve/{id}', [DepositController::class, 'approveDeposit']);
    Route::get('reject/{id}', [DepositController::class, 'rejectDeposit']);

    Route::post('purchase', [ExpenseController::class, 'expense']);

    Route::get('balance', [LedgerController::class, 'index']);
});
