@if(Auth::user()->isAdmin())
    @extends('layouts.admin')
    @section('title', 'Admin Dashboard')
    @section('content')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_users'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Teachers</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_teachers'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Students</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_students'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Schedules</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_schedules'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 overflow-x-auto">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Generated Weekly Timetable</h3>
                @if(!empty($activeTerm))
                    <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        {{ $activeTerm->term_code }} - {{ $activeTerm->academic_year }} - {{ $activeTerm->semester }}
                    </span>
                @endif
            </div>

            @php
                $timeSlots = [];
                for ($hour = 7; $hour < 19; $hour++) {
                    $timeSlots[] = sprintf('%02d:00', $hour) . ' - ' . sprintf('%02d:00', $hour + 1);
                }

                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                $dashboardTimeToMinutes = function ($time) {
                    $parts = explode(':', $time);
                    return ((int) $parts[0] * 60) + (int) $parts[1];
                };
                $formatStandard = function ($time) {
                    return \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A');
                };

                $schedulePositions = [];
                foreach (($timetableSchedules ?? collect()) as $schedule) {
                    $day = strtolower($schedule->day);
                    $scheduleStart = \Carbon\Carbon::parse($schedule->time_start)->format('H:i');
                    $scheduleEnd = \Carbon\Carbon::parse($schedule->time_end)->format('H:i');

                    $startMin = $dashboardTimeToMinutes($scheduleStart);
                    $endMin = $dashboardTimeToMinutes($scheduleEnd);

                    $startSlotIndex = null;
                    foreach ($timeSlots as $idx => $slot) {
                        $slotParts = explode(' - ', $slot);
                        if ($slotParts[0] === $scheduleStart) {
                            $startSlotIndex = $idx;
                            break;
                        }
                    }

                    if ($startSlotIndex !== null) {
                        $slotCount = ($endMin - $startMin) / 60;
                        $schedulePositions[] = [
                            'schedule' => $schedule,
                            'dayIndex' => array_search($day, $days),
                            'startSlotIndex' => $startSlotIndex,
                            'slotCount' => $slotCount,
                        ];
                    }
                }
            @endphp

            @if(($timetableSchedules ?? collect())->isEmpty())
                <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">No official schedules generated yet. Publish a week timetable first.</p>
            @endif

            <table class="min-w-full border-collapse">
                <thead>
                    <tr>
                        <th class="bg-blue-600 text-white font-bold px-4 py-3 text-center border-2 border-blue-700">Time</th>
                        @foreach($dayNames as $dayName)
                            <th class="bg-blue-600 text-white font-bold px-4 py-3 text-center border-2 border-blue-700">{{ $dayName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($timeSlots as $index => $slot)
                        @php
                            $slotParts = explode(' - ', $slot);
                            $slotStart = $slotParts[0];
                        @endphp
                        <tr>
                            @php
                                $slotParts = explode(' - ', $slot);
                                $slotLabel = $formatStandard($slotParts[0]) . ' - ' . $formatStandard($slotParts[1]);
                            @endphp
                            <td class="bg-gray-100 dark:bg-gray-900 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 font-medium border border-gray-300 dark:border-gray-600 text-right w-32 whitespace-nowrap">
                                {{ $slotLabel }}
                            </td>
                            @foreach($days as $dayIndex => $day)
                                @php
                                    $hasSchedule = false;
                                    $scheduleInCell = null;

                                    foreach ($schedulePositions as $pos) {
                                        if ($pos['dayIndex'] === $dayIndex && $pos['startSlotIndex'] === $index) {
                                            $hasSchedule = true;
                                            $scheduleInCell = $pos;
                                            break;
                                        }
                                    }

                                    $isCovered = false;
                                    if (!$hasSchedule) {
                                        foreach ($schedulePositions as $pos) {
                                            if ($pos['dayIndex'] === $dayIndex && $index > $pos['startSlotIndex'] && $index < $pos['startSlotIndex'] + $pos['slotCount']) {
                                                $isCovered = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <td class="relative border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-0" style="height: 60px;">
                                    @if($hasSchedule && $scheduleInCell)
                                        <div class="absolute inset-0 m-0.5 bg-blue-200 dark:bg-blue-900/40 rounded border border-blue-400 dark:border-blue-600 overflow-hidden" style="height: calc({{ $scheduleInCell['slotCount'] }} * 60px - 4px); z-index: 10;">
                                            <div class="p-1.5 text-[10px] leading-tight h-full flex flex-col justify-center">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $scheduleInCell['schedule']->subject->name }}</div>
                                                <div class="text-gray-700 dark:text-gray-300">{{ $scheduleInCell['schedule']->section->name }}</div>
                                                <div class="text-gray-600 dark:text-gray-400">{{ $scheduleInCell['schedule']->teacher->name }}</div>
                                                <div class="text-gray-600 dark:text-gray-400">{{ $scheduleInCell['schedule']->room->name }}</div>
                                                <div class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_start)->format('g:i A') }} - {{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_end)->format('g:i A') }}</div>
                                            </div>
                                        </div>
                                    @elseif(!$isCovered)
                                        <div class="absolute inset-0"></div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No official schedules generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endsection
@else
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if(Auth::user()->isTeacher())
                    @include('teacher.dashboard')
                @else
                    @include('student.dashboard')
                @endif
            </div>
        </div>
    </x-app-layout>
@endif
