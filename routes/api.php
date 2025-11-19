<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PixController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WithdrawController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Endpoints públicos com rate limiting mais restritivo
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    // Endpoints autenticados
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/user', [UserController::class, 'show']);
        
        Route::post('/payment/process', [PaymentController::class, 'store'])
            ->middleware('throttle:30,1'); // Limite mais restritivo para pagamentos

        // PIX
        Route::get('/pix', [PixController::class, 'index']);
        Route::post('/pix', [PixController::class, 'store'])
            ->middleware('throttle:20,1'); // Máximo 20 PIX por minuto
        Route::get('/pix/{pix}', [PixController::class, 'show'])
            ->where('pix', '[0-9]+');

        // Saques
        Route::get('/withdraws', [WithdrawController::class, 'index']);
        Route::post('/withdraws', [WithdrawController::class, 'store'])
            ->middleware('throttle:10,1'); // Máximo 10 saques por minuto
        Route::get('/withdraws/{withdraw}', [WithdrawController::class, 'show'])
            ->where('withdraw', '[0-9]+');
    });
});
