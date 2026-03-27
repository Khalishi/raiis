<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallLogs\CallLogsAndAnalyticsController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('call-logs-and-analytics', [CallLogsAndAnalyticsController::class, 'index'])->name('call-logs-and-analytics');
});



require __DIR__.'/settings.php';
