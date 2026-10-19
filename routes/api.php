<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\CvController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\MlmTreeController;
use App\Http\Controllers\Api\PinController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::get('cv/tiers', [CvController::class, 'tiers']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        Route::get('users', [UserController::class, 'index'])->middleware('permission:view users');
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::get('users/{id}/downline', [UserController::class, 'downline']);
        Route::get('users/{id}/commissions', [UserController::class, 'commissions']);

        Route::get('mlm/tree/{userId}', [MlmTreeController::class, 'tree']);
        Route::get('mlm/sponsor/{userId}', [MlmTreeController::class, 'sponsor']);
        Route::get('mlm/downline/{userId}', [MlmTreeController::class, 'downline']);
        Route::post('mlm/place', [MlmTreeController::class, 'place']);

        Route::post('cv/purchase', [CvController::class, 'purchase']);
        Route::post('cv/{purchase}/activate', [CvController::class, 'activate']);
        Route::post('cv/{purchase}/pay', [CvController::class, 'markPaid'])->middleware('permission:approve payments');
        Route::get('cv/purchases/{userId}', [CvController::class, 'purchases']);
        Route::get('cv/activations/{userId}', [CvController::class, 'activations']);

        Route::post('pins/generate', [PinController::class, 'generate'])->middleware('permission:manage pins');
        Route::post('pins/assign', [PinController::class, 'assign'])->middleware('permission:manage pins');
        Route::get('pins/available', [PinController::class, 'available'])->middleware('permission:manage pins');
        Route::post('pins/use', [PinController::class, 'use']);

        Route::get('commissions/rules', [CommissionController::class, 'rules']);
        Route::get('commissions/calculate/{userId}', [CommissionController::class, 'calculate']);
        Route::get('commissions/history/{userId}', [CommissionController::class, 'history']);
        Route::post('commissions/process', [CommissionController::class, 'process'])->middleware('permission:process commissions');

        Route::get('wallet/balance', [WalletController::class, 'balance']);
        Route::get('wallet/transactions', [WalletController::class, 'transactions']);
        Route::post('withdrawal/request', [WalletController::class, 'requestWithdrawal']);
        Route::get('withdrawal/history', [WalletController::class, 'withdrawalHistory']);
        Route::post('withdrawal/{id}/approve', [WalletController::class, 'approveWithdrawal'])->middleware('permission:approve withdrawals');
        Route::post('withdrawal/{id}/reject', [WalletController::class, 'rejectWithdrawal'])->middleware('permission:approve withdrawals');

        Route::apiResource('tickets', SupportTicketController::class)->except(['edit', 'create']);
        Route::post('tickets/{ticket}/reply', [SupportTicketController::class, 'reply']);
        Route::patch('tickets/{ticket}/status', [SupportTicketController::class, 'status'])->middleware('permission:manage tickets');

        Route::post('kyc/upload', [KycController::class, 'upload']);
        Route::get('kyc/documents', [KycController::class, 'index']);
        Route::post('kyc/{kyc}/review', [KycController::class, 'review'])->middleware('permission:review kyc');

        Route::get('reports/earnings', [ReportController::class, 'earnings']);
        Route::get('reports/downline', [ReportController::class, 'downline']);
        Route::get('reports/commissions', [ReportController::class, 'commissions']);
        Route::get('reports/withdrawals', [ReportController::class, 'withdrawals']);
        Route::get('reports/export/{type}', [ReportController::class, 'export']);
    });
});
