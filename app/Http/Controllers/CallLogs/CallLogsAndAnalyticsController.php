<?php

namespace App\Http\Controllers\CallLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CallLogsAndAnalyticsController extends Controller
{
    public function index()
    {
        return view('call-logs.index');
    }

}
