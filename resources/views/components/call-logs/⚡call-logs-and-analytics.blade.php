<?php

use Livewire\Component;
use App\Models\CallLog;
use App\Services\AiCallSummaryService;
use App\Services\CallRecordingUrlService;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $outcomeFilter = '';
    public ?int $selectedCallLogId = null;
    public ?int $summaryForCallLogId = null;
    public ?array $selectedCallMeta = null;
    public ?string $aiSummary = null;
    public ?string $summaryError = null;

    public ?int $recordingCallLogId = null;

    public ?string $recordingPlaybackUrl = null;

    public ?string $recordingError = null;

    public bool $recordingModalOpen = false;

    public function updatedRecordingModalOpen(bool $value): void
    {
        if (! $value) {
            $this->recordingCallLogId = null;
            $this->recordingPlaybackUrl = null;
            $this->recordingError = null;
        }
    }

    public function getCallLogsProperty()
    {
        return CallLog::query()
            ->when($this->search !== '', function ($query) {
                $term = '%' . trim($this->search) . '%';

                $query->where(function ($innerQuery) use ($term) {
                    $innerQuery->where('agent_name', 'like', $term)
                        ->orWhere('outcome', 'like', $term);
                });
            })
            ->when($this->outcomeFilter !== '', function ($query) {
                $query->where('outcome', $this->outcomeFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingOutcomeFilter(): void
    {
        $this->resetPage();
    }

    public function openSummaryModal(int $callLogId, AiCallSummaryService $aiCallSummaryService): void
    {
        $this->selectedCallLogId = $callLogId;
        $this->summaryForCallLogId = null;
        $this->aiSummary = null;
        $this->summaryError = null;
        $this->selectedCallMeta = null;

        try {
            $callLog = CallLog::query()->findOrFail($callLogId);

            $this->selectedCallMeta = [
                'call_id' => $callLog->call_id,
                'caller' => $callLog->caller,
                'agent_name' => $callLog->agent_name,
                'outcome' => $callLog->outcome,
                'booking_status' => $callLog->booking_status,
                'duration' => $callLog->formatted_duration,
                'created_at' => optional($callLog->created_at)->format('M d, h:i A'),
            ];

            if (! empty($callLog->summary_script)) {
                $this->aiSummary = $callLog->summary_script;
                $this->summaryForCallLogId = $callLogId;
                return;
            }

            $generatedSummary = $aiCallSummaryService->summarizeCall($callLog);
            $callLog->summary_script = $generatedSummary;
            $callLog->save();

            $this->aiSummary = $generatedSummary;
            $this->summaryForCallLogId = $callLogId;
        } catch (\Throwable $e) {
            report($e);
            $this->summaryError = 'Failed to generate summary for this call. Please try again.';
        }
    }

    public function openRecordingModal(int $callLogId, CallRecordingUrlService $recordingUrlService): void
    {
        $this->recordingCallLogId = $callLogId;
        $this->recordingPlaybackUrl = null;
        $this->recordingError = null;

        try {
            $callLog = CallLog::query()->findOrFail($callLogId);

            if (! filled($callLog->recording_url) && ! filled($callLog->recording_object_key)) {
                $this->recordingError = __('No recording is available for this call.');

                return;
            }

            $url = $recordingUrlService->playbackUrl($callLog);
            if ($url === null) {
                $this->recordingError = __('Could not load the recording. Check storage configuration.');

                return;
            }

            $this->recordingPlaybackUrl = $url;
        } catch (\Throwable $e) {
            report($e);
            $this->recordingError = __('Could not load the recording. Please try again.');
        } finally {
            $this->recordingModalOpen = true;
        }
    }

    public function resetRecordingPlayer(): void
    {
        $this->recordingModalOpen = false;
    }

};
?>

<div>
        <!-- Tables: In Card with Search and Actions -->
    <div
    class="flex flex-col overflow-hidden rounded-lg bg-white shadow-xs dark:bg-gray-800 dark:text-gray-100"
    >
    <div
        class="flex flex-col gap-3 bg-gray-50 px-5 py-4 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left dark:bg-gray-700/50"
    >
        <div>
        <h3 class="mb-1 font-semibold text-gray-900 dark:text-gray-50">Call Logs & Analytics</h3>

        </div>
        <div class="flex items-center gap-2">
        <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-5 font-semibold text-gray-900 dark:text-gray-50 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
        >
            Export
        </button>
        <select
            id="outcome_filter"
            name="outcome_filter"
            wire:model.live="outcomeFilter"
            class="block w-full rounded-lg border border-gray-200 py-2 pr-10 pl-3 text-sm text-gray-900 dark:text-gray-50 leading-5 font-semibold focus:border-gray-500 focus:ring-3 focus:ring-gray-500/50 sm:w-44 dark:border-gray-700 dark:bg-gray-800 dark:focus:border-gray-500"
            >
            <option value="">All Outcomes</option>
            <option value="Completed">Completed</option>
            <option value="Resolved">Resolved</option>
            <option value="Booking Made">Booking Made</option>
            <option value="Escalated">Escalated</option>
        </select>
        </div>
    </div>
    <div class="grow border-b border-gray-100 p-5 dark:border-gray-700">
        <div class="relative">
        <div
            class="pointer-events-none absolute inset-y-0 left-0 my-px ml-px flex w-10 items-center justify-center rounded-l text-gray-500"
        >
            <svg
            class="hi-mini hi-magnifying-glass inline-block size-5"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
            >
            <path
                fill-rule="evenodd"
                d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                clip-rule="evenodd"
            />
            </svg>
        </div>
        <input
            type="text"
            id="search"
            name="search"
            wire:model.live.debounce.300ms="search"
            class="block w-full rounded-lg border border-gray-200 py-2 pr-3 pl-10 text-sm leading-6 placeholder-gray-400 focus:border-gray-500 focus:ring-3 focus:ring-gray-500/50 dark:border-gray-700 dark:bg-gray-800 dark:focus:border-gray-500"
            placeholder="Search by agent or outcome.."
        />
        </div>
    </div>
    <div
        class="grow p-5"
        x-data="{
            openSummary: false,
            openRecording: @entangle('recordingModalOpen').live,
        }"
    >
        <!-- Responsive Table Container -->
        <div
        class="min-w-full overflow-x-auto rounded-sm bg-white dark:border-gray-700 dark:bg-gray-800"
        >
        <!-- Table -->
        <table class="min-w-full align-middle text-sm whitespace-nowrap">
            <!-- Table Header -->
            <thead>
            <tr>
                <th
                class="border-b-2 border-gray-200/50 pr-3 pb-4 text-left font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-50"
                >
                Date
                </th>
                <th
                class="border-b-2 border-gray-200/50 px-3 pb-4 text-left font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-50"
                >
                Caller
                </th>
                <th
                class="border-b-2 border-gray-200/50 px-3 pb-4 text-left font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-50"
                >
                Agent
                </th>
                <th
                class="border-b-2 border-gray-200/50 px-3 pb-4 text-left font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-50"
                >
                Outcome
                </th>
                <th
                class="border-b-2 border-gray-200/50 px-3 pb-4 text-left font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-50"
                >
                Duration
                </th>
                <th
                class="border-b-2 border-gray-200/50 px-3 pb-4 text-left font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-50"
                >
                Booking
                </th>
                <th
                class="border-b-2 border-gray-200/50 px-3 pb-4 text-left font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-50"
                >
                Actions
                </th>
            </tr>
            </thead>
            <!-- END Table Header -->

            <!-- Table Body -->
            <tbody>
            <tr
                class="border-b border-gray-100 dark:border-gray-700/50"
            >
               @forelse($this->callLogs as $callLog)
                <td class="py-3 pr-3 font-medium text-gray-900 dark:text-gray-50">{{ $callLog->created_at->format('Y-m-d') }}</td>
                <td class="flex items-center gap-2 p-3 text-gray-900 dark:text-gray-50 text-xl">
                <svg
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" 
                    stroke-width="1.5" 
                    stroke="currentColor" 
                    class="hi-outline hi-phone-arrow-down-left inline-block size-5 text-green-500"
                    >
                 <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    d="M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0 6-6m-3 18c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 0 1 4.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 0 0-.38 1.21 12.035 12.035 0 0 0 7.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 0 1 1.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 0 1-2.25 2.25h-2.25Z"
                    />
                    </svg>
                    {{ $callLog->caller }}
                </td>
                <td class="p-3 text-gray-900 dark:text-gray-50">{{ $callLog->agent_name }}</td>
                <td class="p-3">
                    <div
                        class="inline-flex rounded-full border border-transparent bg-emerald-100 px-2 py-1 text-xs leading-4 font-semibold text-emerald-900 dark:border-emerald-900 dark:bg-emerald-700/10 dark:font-medium dark:text-emerald-200"
                    >
                        {{ $callLog->outcome }}
                    </div>
                </td>
                <td class="p-3 text-gray-900 dark:text-gray-50">{{ $callLog->formatted_duration }}</td>
                <td class="p-3 text-gray-900 dark:text-gray-50">{{ $callLog->booking_status }}</td>
                <td class="p-3">
                <div class="inline-flex items-center gap-1">
                    <button
                    type="button"
                    x-on:click="openSummary = true; $wire.set('selectedCallLogId', {{ $callLog->id }})"
                    wire:click="openSummaryModal({{ $callLog->id }})"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm leading-5 font-semibold text-gray-900 dark:text-gray-50 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         fill="none" 
                         viewBox="0 0 24 24" 
                         stroke-width="1.5" 
                         stroke="currentColor" 
                         class="hi-outline hi-document-text inline-block size-6"
                         >
                      <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                        />
                    </svg>
                    </button>
                    @if(filled($callLog->recording_url) || filled($callLog->recording_object_key))
                    <button
                    type="button"
                    wire:click="openRecordingModal({{ $callLog->id }})"
                    wire:loading.class.add="pointer-events-none opacity-50"
                    wire:target="openRecordingModal"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm leading-5 font-semibold text-gray-900 dark:text-gray-50 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
                    >
                    <svg
                       xmlns="http://www.w3.org/2000/svg"
                       fill="none" viewBox="0 0 24 24"
                       stroke-width="1.5"
                       stroke="currentColor"
                       class="hi-outline hi-play inline-block size-6"
                       >
                    <path
                       stroke-linecap="round"
                       stroke-linejoin="round"
                       d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"
                       />
                    </svg>
                    </button>
                    @else
                    <span
                    class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-sm leading-5 font-semibold text-gray-400 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-500"
                    title="{{ __('No recording') }}"
                    >
                    <svg
                       xmlns="http://www.w3.org/2000/svg"
                       fill="none" viewBox="0 0 24 24"
                       stroke-width="1.5"
                       stroke="currentColor"
                       class="hi-outline hi-play inline-block size-6"
                       >
                    <path
                       stroke-linecap="round"
                       stroke-linejoin="round"
                       d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"
                       />
                    </svg>
                    </span>
                    @endif
                </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-3 pr-3 font-medium text-center text-gray-900 dark:text-gray-50">No call logs found</td>
            </tr>
            @endforelse
            </tbody>
            <!-- END Table Body -->
        </table>
        <!-- END Table -->
         <div class="mt-4">
            {{ $this->callLogs->links() }}
         </div>
        </div>
        <!-- END Responsive Table Container -->
        <x-modal state="openSummary" max-width="max-w-2xl">
            <x-slot:title>
                AI Call Summary
            </x-slot:title>

            <div class="space-y-3">
                @if($selectedCallMeta)
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $selectedCallMeta['caller'] ?? 'Unknown caller' }}
                        @if(!empty($selectedCallMeta['created_at']))
                            - {{ $selectedCallMeta['created_at'] }}
                        @endif
                        @if(!empty($selectedCallMeta['agent_name']))
                            - {{ $selectedCallMeta['agent_name'] }}
                        @endif
                    </p>
                @endif

                <div wire:loading wire:target="openSummaryModal" class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200">
                    Generating summary...
                </div>

                @if($summaryError)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        {{ $summaryError }}
                    </div>
                @endif

                @if($aiSummary && $summaryForCallLogId === $selectedCallLogId)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                        <div class="prose prose-sm max-w-none whitespace-pre-line text-gray-800 dark:prose-invert dark:text-gray-100">
                            {{ $aiSummary }}
                        </div>
                    </div>
                @endif
            </div>

            <x-slot:footer>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                        @if(!empty($selectedCallMeta['duration']))
                            <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-800">
                                Duration: {{ $selectedCallMeta['duration'] }}
                            </span>
                        @endif
                        @if(!empty($selectedCallMeta['outcome']))
                            <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-800">
                                Outcome: {{ $selectedCallMeta['outcome'] }}
                            </span>
                        @endif
                        @if(!empty($selectedCallMeta['booking_status']))
                            <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-800">
                                Booking: {{ $selectedCallMeta['booking_status'] }}
                            </span>
                        @endif
                    </div>

                    <button
                        x-on:click="openSummary = false"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-5 font-semibold text-gray-900 dark:text-gray-50 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
                    >
                        Close
                    </button>
                </div>
            </x-slot:footer>
    </x-modal>

        <x-modal state="openRecording" max-width="max-w-lg">
            <x-slot:title>
                {{ __('Call recording') }}
            </x-slot:title>

            <div class="space-y-4">
                <div wire:loading wire:target="openRecordingModal" class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200">
                    {{ __('Preparing audio…') }}
                </div>

                @if($recordingError)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        {{ $recordingError }}
                    </div>
                @elseif($recordingPlaybackUrl)
                    <audio
                        wire:key="recording-{{ $recordingCallLogId }}"
                        class="w-full"
                        controls
                        preload="metadata"
                        src="{!! $recordingPlaybackUrl !!}"
                    >
                        {{ __('Your browser does not support the audio element.') }}
                    </audio>
                @endif
            </div>

            <x-slot:footer>
                <button
                    wire:click="resetRecordingPlayer"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-5 font-semibold text-gray-900 dark:text-gray-50 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
                >
                    {{ __('Close') }}
                </button>
            </x-slot:footer>
        </x-modal>
</div>
</div>
<!-- END Tables: In Card with Search and Actions -->