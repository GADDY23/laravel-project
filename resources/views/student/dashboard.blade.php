<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen md:flex">
        <aside class="w-full md:w-72 md:min-h-screen bg-gradient-to-b from-[#041a3d] to-[#0b336f] text-blue-100">
            <div class="px-6 py-7 border-b border-blue-900/60">
                <p class="text-[11px] tracking-[0.2em] uppercase text-blue-300">Student Panel</p>
                <h1 class="text-xl font-semibold mt-1 truncate">{{ auth()->user()->name }}</h1>
                @if($section)
                    <p class="text-xs text-blue-300 mt-2">Section {{ $section->name }}</p>
                @endif
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-800/60 text-white font-medium">
                    <span class="w-2 h-2 rounded-full bg-cyan-300"></span>
                    Dashboard
                </a>
            </nav>

            <div class="p-4 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium transition">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 p-4 md:p-8">
            <div class="bg-gradient-to-r from-[#0a2b5f] via-[#0f3b84] to-[#1350a8] text-white rounded-2xl p-6 shadow-lg">
                <p class="text-blue-200 text-sm">Welcome, {{ auth()->user()->name }}</p>
                <h2 class="text-2xl md:text-3xl font-bold mt-1">Class Overview</h2>
                <p class="text-blue-200 mt-2">Stay on track with your subjects, teachers, and schedule.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-sm text-slate-500">Subjects</p>
                    <p class="text-3xl font-bold text-[#0a2b5f] mt-2">{{ $schedules->unique('subject_id')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-sm text-slate-500">Teachers</p>
                    <p class="text-3xl font-bold text-[#0a2b5f] mt-2">{{ $schedules->whereNotNull('teacher_id')->unique('teacher_id')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-sm text-slate-500">Classes</p>
                    <p class="text-3xl font-bold text-[#0a2b5f] mt-2">{{ $schedules->count() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 mt-6 overflow-x-auto shadow-sm p-4">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-[#0a2b5f]">Generated Weekly Timetable</h3>
                    @if($activeTerm)
                        <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700">
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

                    $timeToMinutes = function ($time) {
                        $parts = explode(':', $time);
                        return ((int) $parts[0] * 60) + (int) $parts[1];
                    };
                    $formatStandard = function ($time) {
                        return \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A');
                    };

                    $schedulePositions = [];
                    foreach ($schedules as $schedule) {
                        $day = strtolower($schedule->day);
                        $scheduleStart = \Carbon\Carbon::parse($schedule->time_start)->format('H:i');
                        $scheduleEnd = \Carbon\Carbon::parse($schedule->time_end)->format('H:i');

                        $startMin = $timeToMinutes($scheduleStart);
                        $endMin = $timeToMinutes($scheduleEnd);

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

                @if($schedules->isEmpty())
                    <p class="mb-3 text-sm text-slate-500">No official schedules generated yet.</p>
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
                        @foreach($timeSlots as $index => $slot)
                            <tr>
                                @php
                                    $slotParts = explode(' - ', $slot);
                                    $slotLabel = $formatStandard($slotParts[0]) . ' - ' . $formatStandard($slotParts[1]);
                                @endphp
                                <td class="bg-gray-100 px-3 py-2 text-xs text-gray-700 font-medium border border-gray-300 text-right w-32 whitespace-nowrap">
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
                                    <td class="relative border border-gray-300 bg-gray-50 p-0" style="height: 60px;">
                                        @if($hasSchedule && $scheduleInCell)
                                            <div class="absolute inset-0 m-0.5 bg-blue-200 rounded border border-blue-400 overflow-hidden" style="height: calc({{ $scheduleInCell['slotCount'] }} * 60px - 4px); z-index: 10;">
                                                <div class="p-1.5 text-[10px] leading-tight h-full flex flex-col justify-center">
                                                    <div class="font-semibold text-gray-900">{{ $scheduleInCell['schedule']->subject->name }}</div>
                                                    <div class="text-gray-700">{{ $scheduleInCell['schedule']->section->name }}</div>
                                                    <div class="text-gray-600">{{ $scheduleInCell['schedule']->teacher?->name ?? 'TBA' }}</div>
                                                    <div class="text-gray-600">{{ $scheduleInCell['schedule']->room->name }}</div>
                                                    <div class="text-gray-600">{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_start)->format('g:i A') }} - {{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_end)->format('g:i A') }}</div>
                                                </div>
                                            </div>
                                        @elseif(!$isCovered)
                                            <div class="absolute inset-0"></div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
