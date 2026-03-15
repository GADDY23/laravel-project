<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Timetable</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen md:flex">
        <aside class="w-full md:w-72 md:min-h-screen bg-gradient-to-b from-[#041a3d] to-[#0b336f] text-blue-100 flex flex-col">
            <div class="px-6 py-7 border-b border-blue-900/60">
                <div class="flex flex-col items-center gap-3 text-center">
                    <img src="{{ asset('aclclogo.png') }}" alt="ACLC logo" class="h-10 w-auto object-contain">
                    <div>
                        <p class="text-[11px] tracking-[0.2em] uppercase text-blue-300">Student Panel</p>
                        <h1 class="text-xl font-semibold mt-1 text-white truncate">{{ auth()->user()->name }}</h1>
                    </div>
                </div>
                @if($section)
                    <p class="text-xs text-blue-300 mt-3 text-center">Section {{ $section->name }}</p>
                @endif
            </div>

            <nav class="p-4 space-y-2 flex-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                    Dashboard
                </a>
                <a href="{{ route('student.timetable') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-800/60 text-white font-medium">
                    <span class="w-2 h-2 rounded-full bg-cyan-300"></span>
                    My Timetable
                </a>
                <a href="{{ route('student.information') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                    Information
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
                <h2 class="text-2xl md:text-3xl font-bold mt-1">My Timetable</h2>
                <p class="text-blue-200 mt-2">This shows the schedules assigned to your section.</p>
            </div>

            <div class="bg-white rounded-2xl p-4 md:p-6 border border-slate-200 shadow-sm mt-6">
                @if(!$section)
                    <div class="text-sm text-slate-500">No section assigned to your account.</div>
                @elseif($schedules->isEmpty())
                    <div class="text-sm text-slate-500">No schedules available yet for your section.</div>
                @else
                    @php
                        $timeSlots = [];
                        for ($hour = 7; $hour < 19; $hour++) {
                            $timeSlots[] = sprintf('%02d:00', $hour) . ' - ' . sprintf('%02d:00', $hour + 1);
                        }
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                        function timeToMinutes($time) {
                            $parts = explode(':', $time);
                            return (int) $parts[0] * 60 + (int) $parts[1];
                        }

                        function formatTimeNoMeridiem($time) {
                            $parts = explode(':', $time);
                            $hour = (int) ($parts[0] ?? 0);
                            $minute = (int) ($parts[1] ?? 0);
                            $hour12 = $hour % 12;
                            if ($hour12 === 0) {
                                $hour12 = 12;
                            }
                            return sprintf('%d:%02d', $hour12, $minute);
                        }

                        $schedulePositionsBySection = [];
                        foreach ($schedules as $schedule) {
                            $day = strtolower($schedule->day);
                            $scheduleStart = \Carbon\Carbon::parse($schedule->time_start)->format('H:i');
                            $scheduleEnd = \Carbon\Carbon::parse($schedule->time_end)->format('H:i');
                            $startMin = timeToMinutes($scheduleStart);
                            $endMin = timeToMinutes($scheduleEnd);

                            $startSlotIndex = null;
                            foreach ($timeSlots as $idx => $slot) {
                                $slotParts = explode(' - ', $slot);
                                if (($slotParts[0] ?? '') === $scheduleStart) {
                                    $startSlotIndex = $idx;
                                    break;
                                }
                            }

                            if ($startSlotIndex !== null) {
                                $slotCount = (int) max(1, ceil(($endMin - $startMin) / 60));
                                $pos = [
                                    'schedule' => $schedule,
                                    'day' => $day,
                                    'dayIndex' => array_search($day, $days, true),
                                    'startSlotIndex' => $startSlotIndex,
                                    'slotCount' => $slotCount,
                                ];
                                $schedulePositionsBySection[$schedule->section_id][] = $pos;
                            }
                        }

                        $colorClasses = [
                            'bg-pink-200',
                            'bg-yellow-200',
                            'bg-blue-200',
                            'bg-green-200',
                            'bg-purple-200',
                        ];
                    @endphp

                    <div id="timetable-grid" class="timetable-shell timetable-view-only overflow-x-auto">
                        <div class="timetable-panels-grid">
                            <div class="timetable-panel">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Section: {{ $section->name }}</h3>
                                <div class="overflow-x-auto bg-white rounded-lg shadow-sm">
                                    <table class="min-w-full border-collapse">
                                        <thead>
                                            <tr>
                                                <th class="bg-blue-600 text-white font-bold px-4 py-3 text-center border-2 border-blue-700">
                                                    Time
                                                </th>
                                                @foreach($dayNames as $dayName)
                                                    <th class="bg-blue-600 text-white font-bold px-4 py-3 text-center border-2 border-blue-700">
                                                        {{ $dayName }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($timeSlots as $index => $slot)
                                                @php
                                                    $slotParts = explode(' - ', $slot);
                                                    $slotStart = $slotParts[0];
                                                    $slotEnd = $slotParts[1];
                                                @endphp
                                                <tr class="relative">
                                                    <td class="bg-gray-100 px-3 py-2 text-xs text-gray-700 font-medium border border-gray-300 text-right w-32">
                                                        {{ formatTimeNoMeridiem($slotStart) }} - {{ formatTimeNoMeridiem($slotEnd) }}
                                                    </td>

                                                    @foreach($days as $dayIndex => $day)
                                                        @php
                                                            $hasSchedule = false;
                                                            $scheduleInCell = null;
                                                            $positions = $schedulePositionsBySection[$section->id] ?? [];
                                                            foreach ($positions as $pos) {
                                                                if ($pos['dayIndex'] === $dayIndex && $pos['startSlotIndex'] === $index) {
                                                                    $hasSchedule = true;
                                                                    $scheduleInCell = $pos;
                                                                    break;
                                                                }
                                                            }

                                                            $isCovered = false;
                                                            if (!$hasSchedule) {
                                                                foreach ($positions as $pos) {
                                                                    if ($pos['dayIndex'] === $dayIndex &&
                                                                        $index > $pos['startSlotIndex'] &&
                                                                        $index < $pos['startSlotIndex'] + $pos['slotCount']) {
                                                                        $isCovered = true;
                                                                        break;
                                                                    }
                                                                }
                                                            }

                                                            $colorIndex = $scheduleInCell
                                                                ? (array_search($scheduleInCell['schedule']->subject_id, array_column($schedules->toArray(), 'subject_id')) % count($colorClasses))
                                                                : 0;
                                                        @endphp

                                                        <td class="relative border border-gray-300 bg-gray-50 min-h-[40px] p-0 drop-zone"
                                                            style="height: var(--timetable-slot-height);">
                                                            @if($hasSchedule && $scheduleInCell)
                                                                <div
                                                                    class="schedule-item absolute inset-0 m-0.5 {{ $colorClasses[$colorIndex] }} rounded border border-gray-400 hover:shadow-md transition-shadow"
                                                                    style="height: calc({{ $scheduleInCell['slotCount'] }} * var(--timetable-slot-height) - 4px); z-index: 10;"
                                                                >
                                                                    @php
                                                                        $sizeClass = $scheduleInCell['slotCount'] <= 1 ? 'size-xs' : ($scheduleInCell['slotCount'] <= 2 ? 'size-sm' : 'size-md');
                                                                    @endphp
                                                                    <div class="schedule-body {{ $sizeClass }} h-full flex flex-col">
                                                                        <div class="subject-line font-semibold text-gray-900" title="{{ $scheduleInCell['schedule']->subject->name }}">
                                                                            {{ \Illuminate\Support\Str::limit($scheduleInCell['schedule']->subject->name, 32) }}
                                                                        </div>
                                                                        <div class="meta-line text-gray-700">{{ $scheduleInCell['schedule']->section->name }}</div>
                                                                        @if(!empty($scheduleInCell['schedule']->teacher))
                                                                            <div class="meta-line text-gray-600">{{ $scheduleInCell['schedule']->teacher->name }}</div>
                                                                        @endif
                                                                        <div class="meta-line text-gray-600">{{ $scheduleInCell['schedule']->room->name ?? 'Room: TBD' }}</div>
                                                                    </div>
                                                                </div>
                                                            @elseif(!$isCovered)
                                                                <div class="absolute inset-0 hover:bg-indigo-50 transition-colors flex items-center justify-center">
                                                                    <span class="text-[9px] text-gray-400 opacity-0 hover:opacity-100">Drop here</span>
                                                                </div>
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
                @endif
            </div>
        </main>
    </div>

    <style>
        :root {
            --timetable-slot-height: 40px;
        }

        .schedule-body.size-xs {
            font-size: 8px;
            line-height: 1.05;
            padding: 1px;
            gap: 1px;
            justify-content: flex-start;
        }

        .schedule-body.size-sm {
            font-size: 9px;
            line-height: 1.2;
            padding: 4px;
            gap: 2px;
            justify-content: center;
        }

        .schedule-body.size-md {
            font-size: 10px;
            line-height: 1.3;
            padding: 6px;
            gap: 4px;
            justify-content: center;
        }

        .schedule-body .subject-line,
        .schedule-body .meta-line {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .schedule-body.size-xs .subject-line,
        .schedule-body.size-xs .meta-line {
            -webkit-line-clamp: 1;
        }

        .schedule-body.size-sm .subject-line,
        .schedule-body.size-md .subject-line {
            -webkit-line-clamp: 2;
        }

        .schedule-body.size-sm .meta-line,
        .schedule-body.size-md .meta-line {
            -webkit-line-clamp: 1;
        }

        .timetable-view-only .timetable-panels-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .timetable-shell table {
            table-layout: fixed;
            width: 100%;
        }

        .timetable-shell th,
        .timetable-shell td {
            width: 120px;
        }

        .timetable-shell th:first-child,
        .timetable-shell td:first-child {
            width: 110px;
        }

        .timetable-view-only .schedule-item {
            pointer-events: none;
            cursor: default;
        }

        .timetable-view-only .drop-zone {
            pointer-events: none;
        }

        @media (max-width: 640px) {
            :root {
                --timetable-slot-height: 34px;
            }
        }
    </style>
</body>
</html>
