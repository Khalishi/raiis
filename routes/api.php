<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CallLogController;

Route::post('/v1/call-logs', [CallLogController::class, 'store']);