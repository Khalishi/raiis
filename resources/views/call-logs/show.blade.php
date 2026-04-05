<x-layouts::app :title="__('Call recording')">
    <div class="mx-auto flex max-w-3xl flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ __('Call') }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Twilio Call SID') }}:
                    <span class="font-mono text-gray-900 dark:text-gray-100">{{ $callLog->call_id }}</span>
                </p>
            </div>
            <a
                href="{{ route('call-logs-and-analytics') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:hover:border-gray-600"
            >
                {{ __('Back to call logs') }}
            </a>
        </div>

        <div
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-xs dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-gray-50">{{ __('Recording') }}</h2>
            </div>
            <div class="space-y-4 p-5">
                @if($playbackUrl)
                    <audio class="w-full" controls preload="metadata" src="{!! $playbackUrl !!}">
                        {{ __('Your browser does not support the audio element.') }}
                    </audio>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @if(filled($callLog->recording_url))
                            {{ __('Playing from stored URL (may expire if it was presigned).') }}
                        @else
                            {{ __('Signed link from object storage; refresh this page for a new link when it expires.') }}
                        @endif
                    </p>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __('No playable recording is available for this call.') }}
                    </p>
                @endif
            </div>
        </div>

        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            @if(filled($callLog->caller))
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Caller') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-50">{{ $callLog->caller }}</dd>
                </div>
            @endif
            @if(filled($callLog->agent_name))
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Agent') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-50">{{ $callLog->agent_name }}</dd>
                </div>
            @endif
            @if(filled($callLog->outcome))
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Outcome') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-50">{{ $callLog->outcome }}</dd>
                </div>
            @endif
            @if($callLog->duration !== null)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Duration') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-50">{{ $callLog->formatted_duration }}</dd>
                </div>
            @endif
        </dl>
    </div>
</x-layouts::app>
