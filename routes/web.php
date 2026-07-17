<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PointTransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'page'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::redirect('/', '/dashboard');
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('transactions', PointTransactionController::class)->except(['show']);
    Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('leaderboard/export', [LeaderboardController::class, 'export'])->name('leaderboard.export');
});
