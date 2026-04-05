<?php

namespace App\Http\Controllers\CallLogs;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Services\CallRecordingUrlService;

class CallLogShowController extends Controller
{
    public function show(CallLog $callLog, CallRecordingUrlService $recordingUrlService)
    {
        return view('call-logs.show', [
            'callLog' => $callLog,
            'playbackUrl' => $recordingUrlService->playbackUrl($callLog),
        ]);
    }
}
