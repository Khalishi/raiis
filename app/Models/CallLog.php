<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $fillable = [
        'call_id',
        'duration',
        'outcome',
        'caller',
        'to_number',
        'booking_status',
        'started_at',
        'ended_at',
        'transcript',
        'agent_name',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
