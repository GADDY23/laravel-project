<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    {{ $slot ?? '' }}
</body>
</html>





<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm text-indigo-100">Student Dashboard</p>
                <h2 class="text-3xl font-bold">Welcome back, {{ auth()->user()->name }}</h2>
                <div class="flex flex-wrap gap-3 mt-2 text-sm text-indigo-100">
                    @if($activeTerm)
                        <span class="px-3 py-1 rounded-full bg-white/15 border border-white/20">Term: {{ $activeTerm->academic_year }} • {{ $activeTerm->semester }}</span>
                    @endif
                    @if($section)
                        <span class="px-3 py-1 rounded-full bg-white/15 border border-white/20">Section: {{ $section->name }}</span>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white font-semibold transition">Logout</button>
            </form>
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Subjects</p>
            <div class="flex items-center justify-between mt-2">
                <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $schedules->unique('subject_id')->count() }}</span>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">Enrolled</span>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Teachers</p>
            <div class="flex items-center justify-between mt-2">
                <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $schedules->unique('teacher_id')->count() }}</span>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">Assigned</span>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Classes This Week</p>
            <div class="flex items-center justify-between mt-2">
                <span class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $schedules->count() }}</span>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200">Scheduled</span>
            </div>
        </div>
    </div>

    {{-- Notifications --}}
    @if($notifications->count() > 0)
        <div class="bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 rounded-xl p-4 shadow-sm">
            <div class="flex items-start">
                <div class="p-2 bg-amber-500 rounded-full text-amber-900">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">You have {{ $notifications->count() }} new notification(s)</h3>
                    <p class="text-sm text-amber-800 dark:text-amber-200">Open notifications to view the latest updates.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Upcoming classes --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Classes</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">Next 7 days</span>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($schedules->sortBy(fn($s) => [$s->day, $s->time_start])->take(6) as $schedule)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $schedule->subject->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex gap-2 mt-1">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ ucfirst($schedule->day) }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $schedule->time_start }} - {{ $schedule->time_end }}
                            </span>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ $schedule->teacher->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $schedule->room->name }}</p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No classes scheduled yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Timetable --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Weekly Timetable</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $section->name ?? 'My Section' }}</span>
        </div>
        <div class="overflow-x-auto p-4">
            @php
                $timeSlots = [];
                for ($hour = 7; $hour < 19; $hour++) {
                    $timeSlots[] = sprintf('%02d:00', $hour) . ' - ' . sprintf('%02d:30', $hour);
                    $timeSlots[] = sprintf('%02d:30', $hour) . ' - ' . sprintf('%02d:00', $hour + 1);
                }
                $timeSlots[] = '19:00 - 19:30';
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                function timeToMinutes($time) {
                    $parts = explode(':', $time);
                    return (int)$parts[0] * 60 + (int)$parts[1];
                }
                $schedulePositions = [];
                foreach ($schedules as $schedule) {
                    $day = strtolower($schedule->day);
                    $scheduleStart = \Carbon\Carbon::parse($schedule->time_start)->format('H:i');
                    $scheduleEnd = \Carbon\Carbon::parse($schedule->time_end)->format('H:i');
                    $startMin = timeToMinutes($scheduleStart);
                    $endMin = timeToMinutes($scheduleEnd);
                    $startSlotIndex = null;
                    foreach ($timeSlots as $idx => $slot) {
                        $slotParts = explode(' - ', $slot);
                        if ($slotParts[0] === $scheduleStart) {
                            $startSlotIndex = $idx;
                            break;
                        }
                    }
                    if ($startSlotIndex !== null) {
                        $slotCount = ($endMin - $startMin) / 30;
                        $schedulePositions[] = [
                            'schedule' => $schedule,
                            'day' => $day,
                            'dayIndex' => array_search($day, $days),
                            'startSlotIndex' => $startSlotIndex,
                            'slotCount' => $slotCount,
                        ];
                    }
                }
                $colorClasses = [
                    'bg-indigo-100 dark:bg-indigo-900/60 border-indigo-300 dark:border-indigo-600',
                    'bg-amber-100 dark:bg-amber-900/60 border-amber-300 dark:border-amber-600',
                    'bg-emerald-100 dark:bg-emerald-900/60 border-emerald-300 dark:border-emerald-600',
                    'bg-sky-100 dark:bg-sky-900/60 border-sky-300 dark:border-sky-600',
                    'bg-pink-100 dark:bg-pink-900/60 border-pink-300 dark:border-pink-600',
                ];
            @endphp
            <div class="inline-block min-w-full">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-semibold px-4 py-3 text-left border border-gray-200 dark:border-gray-700">Time</th>
                            @foreach($dayNames as $dayName)
                                <th class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-semibold px-4 py-3 text-center border border-gray-200 dark:border-gray-700">{{ $dayName }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeSlots as $index => $slot)
                            @php
                                $slotParts = explode(' - ', $slot);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                                <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300 font-semibold border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/80">
                                    {{ $slot }}
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
                                                if ($pos['dayIndex'] === $dayIndex &&
                                                    $index > $pos['startSlotIndex'] &&
                                                    $index < $pos['startSlotIndex'] + $pos['slotCount']) {
                                                    $isCovered = true;
                                                    break;
                                                }
                                            }
                                        }
                                        $colorIndex = $scheduleInCell ? ($scheduleInCell['dayIndex'] % count($colorClasses)) : 0;
                                    @endphp
                                    <td class="relative border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/60 h-16 p-0">
                                        @if($hasSchedule && $scheduleInCell)
                                            <div class="absolute inset-0 m-1 rounded-lg border {{ $colorClasses[$colorIndex] }} shadow-sm flex flex-col justify-center px-2 text-[11px] leading-tight">
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $scheduleInCell['schedule']->subject->name }}</span>
                                                <span class="text-gray-700 dark:text-gray-200">{{ $scheduleInCell['schedule']->teacher->name }}</span>
                                                <span class="text-gray-500 dark:text-gray-300 text-[10px]">{{ $scheduleInCell['schedule']->room->name }}</span>
                                            </div>
                                        @elseif($isCovered)
                                            <div class="absolute inset-0"></div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
