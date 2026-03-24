<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CallLogsAndAnalyticsController;

Route::post('/v1/call-logs', [CallLogsAndAnalyticsController::class, 'store'])->middleware('auth:sanctum');