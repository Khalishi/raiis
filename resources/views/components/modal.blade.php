@props([
    'state' => 'open',
    'maxWidth' => 'max-w-md',
    'showCloseButton' => true,
])

<div
    x-cloak
    x-show="{{ $state }}"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-on:keydown.escape.window="{{ $state }} = false"
    x-on:click.self="{{ $state }} = false"
    tabindex="-1"
    role="dialog"
    class="fixed inset-0 z-90 overflow-x-hidden overflow-y-auto bg-gray-900/75 p-4 backdrop-blur-xs lg:p-8"
>
    <div
        x-cloak
        x-show="{{ $state }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-125"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-125"
        role="document"
        class="mx-auto flex w-full {{ $maxWidth }} flex-col overflow-hidden rounded-lg bg-white shadow-xs dark:bg-gray-800 dark:text-gray-100"
    >
        <div class="flex items-center justify-between bg-gray-50 px-5 py-4 dark:bg-gray-700/50">
            <h3 class="font-medium text-gray-900 dark:text-gray-50">{{ $title ?? 'Modal' }}</h3>

            @if ($showCloseButton)
                <div class="-my-4">
                    <button
                        x-on:click="{{ $state }} = false"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent px-3 py-2 text-sm leading-5 font-semibold text-gray-800 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-transparent dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
                    >
                        <svg
                            class="hi-solid hi-x -mx-1 inline-block size-4"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <div class="grow p-5 text-gray-900 dark:text-gray-50">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="space-x-1 bg-gray-50 px-5 py-4 text-right dark:bg-gray-700/50">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>