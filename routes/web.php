<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CommissionController;
use App\Http\Controllers\Web\CvController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\TicketController;
use App\Http\Controllers\Web\TreeController;
use App\Http\Controllers\Web\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('web.login.submit');
    Route::get('register', [AuthController::class, 'showRegister'])->name('web.register');
    Route::post('register', [AuthController::class, 'register'])->name('web.register.submit');
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('web.logout');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tree', [TreeController::class, 'index'])->name('web.tree');
    Route::get('/cv', [CvController::class, 'index'])->name('web.cv');
    Route::post('/cv/purchase', [CvController::class, 'store'])->name('web.cv.purchase');
    Route::post('/cv/{purchase}/activate', [CvController::class, 'activate'])->name('web.cv.activate');

    Route::get('/commissions', [CommissionController::class, 'index'])->name('web.commissions');
    Route::get('/wallet', [WalletController::class, 'index'])->name('web.wallet');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('web.wallet.withdraw');

    Route::get('/tickets', [TicketController::class, 'index'])->name('web.tickets');
    Route::post('/tickets', [TicketController::class, 'store'])->name('web.tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('web.tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('web.tickets.reply');

    Route::get('/reports', [ReportController::class, 'index'])->name('web.reports');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('web.reports.export');

    Route::get('/profile', [ProfileController::class, 'show'])->name('web.profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('web.profile.update');
    Route::post('/profile/kyc', [ProfileController::class, 'uploadKyc'])->name('web.profile.kyc');

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('users', [AdminController::class, 'users'])->name('admin.users');
        Route::put('users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');

        Route::get('pins', [AdminController::class, 'pins'])->name('admin.pins');
        Route::post('pins/generate', [AdminController::class, 'generatePins'])->name('admin.pins.generate');
        Route::post('pins/assign', [AdminController::class, 'assignPin'])->name('admin.pins.assign');

        Route::get('withdrawals', [AdminController::class, 'withdrawals'])->name('admin.withdrawals');
        Route::post('withdrawals/{id}/approve', [AdminController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
        Route::post('withdrawals/{id}/reject', [AdminController::class, 'rejectWithdrawal'])->name('admin.withdrawals.reject');

        Route::get('kyc', [AdminController::class, 'kyc'])->name('admin.kyc');
        Route::post('kyc/{id}', [AdminController::class, 'reviewKyc'])->name('admin.kyc.review');

        Route::get('settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::post('settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    });
});
