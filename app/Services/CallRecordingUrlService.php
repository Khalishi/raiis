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
        $bucket = trim((string) config('filesystems.disks.s3.bucket', ''), '/');
        $bucketCallPrefix = ($bucket !== '' && $callPrefix !== null) ? $bucket . '/' . $callPrefix : null;

        $existingKey = $this->normalizeObjectKey((string) ($callLog->recording_object_key ?? ''), $basePrefix);
        $fromStoredUrl = $this->extractObjectKeyFromUrl((string) ($callLog->recording_url ?? ''), $basePrefix);
        $folderFiles = $callPrefix !== null ? $this->audioObjectsForPrefix($disk, $callPrefix) : collect();
        $bucketFolderFiles = $bucketCallPrefix !== null ? $this->audioObjectsForPrefix($disk, $bucketCallPrefix) : collect();
        $latestFromFolder = $folderFiles->first();
        $latestFromBucketFolder = $bucketFolderFiles->first();

        $selectedKey = $existingKey !== '' ? $existingKey : ($fromStoredUrl !== '' ? $fromStoredUrl : ($latestFromFolder['path'] ?? ($latestFromBucketFolder['path'] ?? null)));
        $resolvedSelectedKey = is_string($selectedKey) ? $this->firstExistingKeyCandidate($disk, $selectedKey, $bucket) : null;
        $exists = false;
        $signedUrl = null;

        if (is_string($resolvedSelectedKey) && $resolvedSelectedKey !== '') {
            try {
                $exists = Storage::disk($disk)->exists($resolvedSelectedKey);
                if ($exists) {
                    $signedUrl = Storage::disk($disk)->temporaryUrl(
                        $resolvedSelectedKey,
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
            'bucket_call_prefix' => $bucketCallPrefix,
            'db_recording_object_key' => $callLog->recording_object_key,
            'normalized_db_key' => $existingKey,
            'recording_url' => $callLog->recording_url,
            'key_from_recording_url' => $fromStoredUrl,
            'files_found_in_call_folder' => $folderFiles->count(),
            'latest_files_in_call_folder' => $folderFiles->take(5)->pluck('path')->values()->all(),
            'files_found_in_bucket_call_folder' => $bucketFolderFiles->count(),
            'latest_files_in_bucket_call_folder' => $bucketFolderFiles->take(5)->pluck('path')->values()->all(),
            'selected_key' => $selectedKey,
            'resolved_selected_key' => $resolvedSelectedKey,
            'selected_key_exists' => $exists,
            'playback_url' => $signedUrl,
        ];
    }

    private function resolveRecordingObjectKey(CallLog $callLog): ?string
    {
        $disk = config('filesystems.recordings_disk', 's3');
        $basePrefix = trim((string) config('filesystems.recordings_prefix', 'value-logistics/recordings'), '/');
        $callId = trim((string) ($callLog->call_id ?? ''));
        $bucket = trim((string) config('filesystems.disks.s3.bucket', ''), '/');

        $existingKey = $this->normalizeObjectKey((string) ($callLog->recording_object_key ?? ''), $basePrefix);
        if ($existingKey !== '') {
            $resolvedExistingKey = $this->firstExistingKeyCandidate($disk, $existingKey, $bucket);
            if ($resolvedExistingKey !== null) {
                if ($resolvedExistingKey !== $callLog->recording_object_key) {
                    $callLog->forceFill(['recording_object_key' => $resolvedExistingKey])->saveQuietly();
                }

                return $resolvedExistingKey;
            }
        }

        $fromStoredUrl = $this->extractObjectKeyFromUrl((string) ($callLog->recording_url ?? ''), $basePrefix);
        if ($fromStoredUrl !== '') {
            $callLog->forceFill(['recording_object_key' => $fromStoredUrl])->saveQuietly();

            return $fromStoredUrl;
        }

        if ($callId === '') {
            return null;
        }

        $candidatePrefix = $basePrefix . '/' . $callId;
        $candidateKey = $this->latestAudioObjectForPrefix($disk, $candidatePrefix);
        if (! is_string($candidateKey) || $candidateKey === '') {
            if ($bucket !== '') {
                $candidateKey = $this->latestAudioObjectForPrefix($disk, $bucket . '/' . $candidatePrefix);
            }
        }
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

    private function firstExistingKeyCandidate(string $disk, string $key, string $bucket): ?string
    {
        $candidates = [$key];
        if ($bucket !== '' && ! Str::startsWith($key, $bucket . '/')) {
            $candidates[] = $bucket . '/' . $key;
        }

        foreach ($candidates as $candidate) {
            try {
                if (Storage::disk($disk)->exists($candidate)) {
                    return $candidate;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return null;
    }
}
