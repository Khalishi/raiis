<?php

$spacesBucket = env('DO_SPACES_BUCKET', env('AWS_BUCKET'));
$spacesEndpoint = env('DO_SPACES_ENDPOINT', env('AWS_ENDPOINT'));

if (is_string($spacesEndpoint) && is_string($spacesBucket) && $spacesEndpoint !== '' && $spacesBucket !== '') {
    $parsedEndpoint = parse_url($spacesEndpoint);
    $endpointHost = $parsedEndpoint['host'] ?? null;

    if (is_string($endpointHost)) {
        $bucketPrefix = $spacesBucket . '.';
        if (str_starts_with($endpointHost, $bucketPrefix)) {
            $parsedEndpoint['host'] = substr($endpointHost, strlen($bucketPrefix));

            $scheme = isset($parsedEndpoint['scheme']) ? $parsedEndpoint['scheme'] . '://' : '';
            $userInfo = '';
            if (isset($parsedEndpoint['user'])) {
                $userInfo = $parsedEndpoint['user'];
                if (isset($parsedEndpoint['pass'])) {
                    $userInfo .= ':' . $parsedEndpoint['pass'];
                }
                $userInfo .= '@';
            }
            $port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
            $path = $parsedEndpoint['path'] ?? '';
            $query = isset($parsedEndpoint['query']) ? '?' . $parsedEndpoint['query'] : '';
            $fragment = isset($parsedEndpoint['fragment']) ? '#' . $parsedEndpoint['fragment'] : '';

            $spacesEndpoint = $scheme . $userInfo . $parsedEndpoint['host'] . $port . $path . $query . $fragment;
        }
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Call recordings (S3-compatible, e.g. DigitalOcean Spaces)
    |--------------------------------------------------------------------------
    */

    'recordings_disk' => env('RECORDINGS_FILESYSTEM_DISK', 's3'),

    'recordings_temporary_url_ttl' => (int) env('RECORDINGS_TEMPORARY_URL_TTL', 60),

    'recordings_prefix' => env('RECORDINGS_PREFIX', 'value-logistics/recordings'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('DO_SPACES_SECRET', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('DO_SPACES_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
            'bucket' => $spacesBucket,
            'url' => env('DO_SPACES_CDN_URL', env('AWS_URL')),
            'endpoint' => $spacesEndpoint,
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
