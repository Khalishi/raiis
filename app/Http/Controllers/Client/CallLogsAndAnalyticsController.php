<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CallLogsAndAnalyticsController extends Controller
{
    public function index()
    {
        return view('client-page.call-logs-and-analytics');
    }
}
