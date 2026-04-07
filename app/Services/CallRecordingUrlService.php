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

    public function debugDetails(CallLog $callLog): array
    {
        $disk = config('filesystems.recordings_disk', 's3');
        $basePrefix = trim((string) config('filesystems.recordings_prefix', 'value-logistics/recordings'), '/');
        $callId = trim((string) ($callLog->call_id ?? ''));
        $callPrefix = $callId !== '' ? $basePrefix . '/' . $callId : null;

        $existingKey = $this->normalizeObjectKey((string) ($callLog->recording_object_key ?? ''), $basePrefix);
        $fromStoredUrl = $this->extractObjectKeyFromUrl((string) ($callLog->recording_url ?? ''), $basePrefix);
        $folderFiles = $callPrefix !== null ? $this->audioObjectsForPrefix($disk, $callPrefix) : collect();
        $latestFromFolder = $folderFiles->first();

        $selectedKey = $existingKey !== '' ? $existingKey : ($fromStoredUrl !== '' ? $fromStoredUrl : ($latestFromFolder['path'] ?? null));
        $exists = false;
        $signedUrl = null;

        if (is_string($selectedKey) && $selectedKey !== '') {
            try {
                $exists = Storage::disk($disk)->exists($selectedKey);
                if ($exists) {
                    $signedUrl = Storage::disk($disk)->temporaryUrl(
                        $selectedKey,
                        now()->addMinutes(max(1, (int) config('filesystems.recordings_temporary_url_ttl', 60)))
                    );
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return [
            'call_log_id' => $callLog->id,
            'call_id' => $callLog->call_id,
            'disk' => $disk,
            'prefix' => $basePrefix,
            'call_prefix' => $callPrefix,
            'db_recording_object_key' => $callLog->recording_object_key,
            'normalized_db_key' => $existingKey,
            'recording_url' => $callLog->recording_url,
            'key_from_recording_url' => $fromStoredUrl,
            'files_found_in_call_folder' => $folderFiles->count(),
            'latest_files_in_call_folder' => $folderFiles->take(5)->pluck('path')->values()->all(),
            'selected_key' => $selectedKey,
            'selected_key_exists' => $exists,
            'playback_url' => $signedUrl,
        ];
    }

    private function resolveRecordingObjectKey(CallLog $callLog): ?string
    {
        $disk = config('filesystems.recordings_disk', 's3');
        $basePrefix = trim((string) config('filesystems.recordings_prefix', 'value-logistics/recordings'), '/');
        $callId = trim((string) ($callLog->call_id ?? ''));

        $existingKey = $this->normalizeObjectKey((string) ($callLog->recording_object_key ?? ''), $basePrefix);
        if ($existingKey !== '') {
            return $existingKey;
        }

        $fromStoredUrl = $this->extractObjectKeyFromUrl((string) ($callLog->recording_url ?? ''), $basePrefix);
        if ($fromStoredUrl !== '') {
            $callLog->forceFill(['recording_object_key' => $fromStoredUrl])->saveQuietly();

            return $fromStoredUrl;
        }

        if ($callId === '') {
            return null;
        }

        $candidateKey = $this->latestAudioObjectForPrefix($disk, $basePrefix . '/' . $callId);
        if (is_string($candidateKey) && $candidateKey !== '') {
            $callLog->forceFill(['recording_object_key' => $candidateKey])->saveQuietly();

            return $candidateKey;
        }

        return null;
    }

    private function latestAudioObjectForPrefix(string $disk, string $prefix): ?string
    {
        return $this->audioObjectsForPrefix($disk, $prefix)->first()['path'] ?? null;
    }

    private function audioObjectsForPrefix(string $disk, string $prefix)
    {
        try {
            $files = collect(Storage::disk($disk)->allFiles($prefix))
                ->filter(fn (string $path): bool => Str::endsWith(Str::lower($path), ['.wav', '.mp3', '.m4a', '.ogg']))
                ->values();

            if ($files->isEmpty()) {
                return collect();
            }

            return $files
                ->map(fn (string $path): array => [
                    'path' => $path,
                    'last_modified' => (int) Storage::disk($disk)->lastModified($path),
                ])
                ->sortByDesc('last_modified')
                ->values();
        } catch (Throwable $e) {
            report($e);

            return collect();
        }
    }

    private function extractObjectKeyFromUrl(string $url, string $basePrefix): string
    {
        if ($url === '') {
            return '';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        if ($path === '') {
            return '';
        }

        return $this->normalizeObjectKey($path, $basePrefix);
    }

    private function normalizeObjectKey(string $keyOrPath, string $basePrefix): string
    {
        $value = trim($keyOrPath);
        if ($value === '') {
            return '';
        }

        $value = ltrim($value, '/');
        $bucket = trim((string) config('filesystems.disks.s3.bucket', ''), '/');

        // Handle malformed URLs/paths like /bucket/value-logistics/recordings/...
        if ($bucket !== '' && Str::startsWith($value, $bucket . '/')) {
            $value = substr($value, strlen($bucket) + 1);
        }

        $prefixPos = strpos($value, $basePrefix . '/');
        if ($prefixPos === false) {
            return '';
        }

        return substr($value, $prefixPos);
    }
}
