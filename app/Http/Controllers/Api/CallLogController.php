<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use Illuminate\Http\Request;

class CallLogController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'call_id' => ['required', 'string', 'max:255'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'outcome' => ['nullable', 'string', 'max:255'],
            'caller' => ['nullable', 'string', 'max:255'],
            'to_number' => ['nullable', 'string', 'max:255'],
            'booking_status' => ['nullable', 'string', 'max:255'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'transcript' => ['nullable', 'string'],
            'agent_name' => ['nullable', 'string', 'max:255'],
        ]);

        $callLog = CallLog::updateOrCreate(
            ['call_id' => $validated['call_id']],
            collect($validated)->except('call_id')->toArray()
        );

        return response()->json([
            'message' => 'Call log saved successfully.',
            'data' => $callLog,
        ], 201);
    }
}
