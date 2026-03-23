<?php

use Livewire\Component;

new class extends Component
{
    //
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
        <h3 class="mb-1 font-semibold">Call Logs & Analytics</h3>

        </div>
        <div class="flex items-center gap-2">
        <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-5 font-semibold text-gray-800 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
        >
            Export
        </button>
        <select
            id="date"
            name="date"
            class="block w-full rounded-lg border border-gray-200 py-2 pr-10 pl-3 text-sm leading-5 font-semibold focus:border-gray-500 focus:ring-3 focus:ring-gray-500/50 sm:w-36 dark:border-gray-700 dark:bg-gray-800 dark:focus:border-gray-500"
        >
            <option>Agency</option>
            <option>Pro</option>
            <option>Freelancer</option>
            <option>Trial</option>
            <option selected>All Outcomes</option>
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
            class="block w-full rounded-lg border border-gray-200 py-2 pr-3 pl-10 text-sm leading-6 placeholder-gray-400 focus:border-gray-500 focus:ring-3 focus:ring-gray-500/50 dark:border-gray-700 dark:bg-gray-800 dark:focus:border-gray-500"
            placeholder="Search calls.."
        />
        </div>
    </div>
    <div class="grow p-5">
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
                <td class="py-3 pr-3 font-medium">2026-03-23</td>
                <td class="p-3 text-gray-500 dark:text-gray-400">
                +1234567890
                </td>
                <td class="p-3">John Doe</td>
                <td class="p-3">
                    <div
                        class="inline-flex rounded-full border border-transparent bg-emerald-100 px-2 py-1 text-xs leading-4 font-semibold text-emerald-900 dark:border-emerald-900 dark:bg-emerald-700/10 dark:font-medium dark:text-emerald-200"
                    >
                        Success
                    </div>
                </td>
                <td class="p-3">10:00</td>
                <td class="p-3">10:00</td>
                <td class="p-3">
                <div class="inline-flex items-center gap-1">
                    <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm leading-5 font-semibold text-gray-800 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
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
                    <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm leading-5 font-semibold text-gray-800 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700"
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
                </div>
                </td>
            </tr>
            
            </tbody>
            <!-- END Table Body -->
        </table>
        <!-- END Table -->
        </div>
        <!-- END Responsive Table Container -->
    </div>
    </div>
    <!-- END Tables: In Card with Search and Actions -->

</div>