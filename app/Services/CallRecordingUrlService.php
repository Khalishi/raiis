<?php

namespace App\Services;

use App\Models\CallLog;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CallRecordingUrlService
{
    public function playbackUrl(CallLog $callLog): ?string
    {
        $key = $callLog->recording_object_key;
        if (is_string($key) && $key !== '') {
            $disk = config('filesystems.recordings_disk', 's3');
            $ttlMinutes = (int) config('filesystems.recordings_temporary_url_ttl', 60);

            try {
                return Storage::disk($disk)->temporaryUrl(
                    $key,
                    now()->addMinutes(max(1, $ttlMinutes))
                );
            } catch (Throwable $e) {
                report($e);
            }
        }

        $storedUrl = $callLog->recording_url;
        if (is_string($storedUrl) && $storedUrl !== '') {
            return $storedUrl;
        }

        return null;
    }
}
