<?php

namespace App\Services;

use App\Models\CallLog;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CallRecordingUrlService
{
    public function playbackUrl(CallLog $callLog): ?string
    {
        return $this->signedUrl($callLog);
    }

    public function downloadUrl(CallLog $callLog): ?string
    {
        $key = $callLog->recording_object_key;
        if (! is_string($key) || $key === '') {
            return $this->signedUrl($callLog);
        }

        $filename = basename($key);

        return $this->signedUrl($callLog, [
            'ResponseContentDisposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function signedUrl(CallLog $callLog, array $options = []): ?string
    {
        $storedUrl = $callLog->recording_url;
        if (is_string($storedUrl) && $storedUrl !== '') {
            return $storedUrl;
        }

        $key = $callLog->recording_object_key;
        if (! is_string($key) || $key === '') {
            return null;
        }

        $disk = config('filesystems.recordings_disk', 's3');
        $ttlMinutes = (int) config('filesystems.recordings_temporary_url_ttl', 60);

        try {
            return Storage::disk($disk)->temporaryUrl(
                $key,
                now()->addMinutes(max(1, $ttlMinutes)),
                $options
            );
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
