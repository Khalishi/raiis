<?php

namespace App\Services;

use App\Models\CallLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CallRecordingUrlService
{
    public function playbackUrl(CallLog $callLog): ?string
    {
        $key = $this->resolveRecordingObjectKey($callLog);
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

    private function resolveRecordingObjectKey(CallLog $callLog): ?string
    {
        $existingKey = $callLog->recording_object_key;
        if (is_string($existingKey) && $existingKey !== '') {
            return $existingKey;
        }

        $disk = config('filesystems.recordings_disk', 's3');
        $basePrefix = trim((string) config('filesystems.recordings_prefix', 'value-logistics/recordings'), '/');
        $callId = trim((string) ($callLog->call_id ?? ''));

        $candidateKey = null;

        if ($callId !== '') {
            $candidateKey = $this->latestAudioObjectForPrefix($disk, $basePrefix . '/' . $callId);
        }

        if (! is_string($candidateKey) || $candidateKey === '') {
            // Fallback: latest object in recordings root when call-specific folder is missing.
            $candidateKey = $this->latestAudioObjectForPrefix($disk, $basePrefix);
        }

        if (is_string($candidateKey) && $candidateKey !== '') {
            $callLog->forceFill(['recording_object_key' => $candidateKey])->saveQuietly();

            return $candidateKey;
        }

        return null;
    }

    private function latestAudioObjectForPrefix(string $disk, string $prefix): ?string
    {
        try {
            $files = collect(Storage::disk($disk)->allFiles($prefix))
                ->filter(fn (string $path): bool => Str::endsWith(Str::lower($path), ['.wav', '.mp3', '.m4a', '.ogg']))
                ->values();

            if ($files->isEmpty()) {
                return null;
            }

            return $files
                ->map(fn (string $path): array => [
                    'path' => $path,
                    'last_modified' => (int) Storage::disk($disk)->lastModified($path),
                ])
                ->sortByDesc('last_modified')
                ->pluck('path')
                ->first();
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
