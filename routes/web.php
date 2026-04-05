<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallLogs\CallLogShowController;
use App\Http\Controllers\CallLogs\CallLogsAndAnalyticsController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('call-logs-and-analytics', [CallLogsAndAnalyticsController::class, 'index'])->name('call-logs-and-analytics');
    Route::get('call-logs/{callLog}', [CallLogShowController::class, 'show'])->name('call-logs.show');
});



require __DIR__.'/settings.php';
