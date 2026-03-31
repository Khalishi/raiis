<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'summary_script',
        'agent_name',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];


protected function formattedDuration(): Attribute
{
    return Attribute::make(
        get: function () {
            $seconds = (int) ($this->duration ?? 0);

            $hours = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);
            $remainingSeconds = $seconds % 60;

            if ($hours > 0) {
                return "{$hours}h {$minutes}m";
            }

            if ($minutes > 0) {
                return "{$minutes}m {$remainingSeconds}s";
            }

            return "{$remainingSeconds}s";
        }
    );
}
}
