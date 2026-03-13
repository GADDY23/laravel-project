@extends('layouts.admin')
@section('title', 'Schedule Timetable')
@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Schedule Timetable</h1>
        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-700 dark:text-gray-300">Schedule:</span>
                <span id="schedule-name">{{ $scheduleName ?? 'Untitled' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-700 dark:text-gray-300">Term:</span>
                <span id="term-display">{{ optional($term)->term_code ?? '-' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-700 dark:text-gray-300">Rooms:</span>
                <span id="rooms-display">{{ isset($selectedRooms) && count($selectedRooms) ? implode(', ', $rooms->whereIn('id', $selectedRooms)->pluck('name')->toArray()) : '-' }}</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-2 lg:items-end">
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto">
            <button type="button" id="save-schedule-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center">
                Save Schedule
            </button>
            <button type="button" id="publish-week-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-center">
                Publish Week
            </button>
            <a href="{{ route('admin.schedules.configure', ['reset' => 1]) }}" id="cancel-schedule-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-center">Cancel</a>
        </div>
        
    </div>
</div>

<div id="conflict-popup" class="hidden fixed top-4 right-4 z-50 w-[360px] max-w-[90vw] rounded-lg border border-red-300 bg-red-50 text-red-800 shadow-lg">
    <div class="flex items-start justify-between gap-3 px-4 py-3 border-b border-red-200">
        <div>
            <p class="font-semibold">Schedule Conflict Detected</p>
            <p class="text-xs text-red-700/90">Please change the drop slot or schedule details.</p>
        </div>
        <button type="button" id="conflict-popup-close" class="text-red-700 hover:text-red-900 text-lg leading-none">&times;</button>
    </div>
    <ul id="conflict-popup-list" class="px-4 py-3 space-y-1 text-sm"></ul>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Left Sidebar - Schedule Builder --}}
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-4 lg:sticky lg:top-6 lg:max-h-[calc(100vh-3rem)] lg:overflow-y-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Sections & Subjects</h2>
        <div id="section-subjects" class="space-y-3 mb-4">
            @php
                $termIdValue = $termId ?? $term?->id ?? null;
            @endphp

            @if(!$termIdValue)
                <p class="text-sm text-gray-500">Select a term to load sections.</p>
            @elseif($sections->isEmpty())
                <p class="text-sm text-gray-500">No sections found for the selected term.</p>
            @else
                <div class="space-y-2">
                    @foreach($sectionsPayload as $section)
                        <details class="group border border-gray-200 dark:border-gray-700 rounded-lg" open>
                            <summary class="flex items-center justify-between cursor-pointer px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 rounded-t-lg">
                                <span>{{ $section['name'] }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $section['year_level'] ?? '' }}</span>
                            </summary>
                            <div class="px-3 py-2 bg-white dark:bg-gray-800 rounded-b-lg">
                                @if(empty($section['subjects']))
                                    <p class="text-sm text-gray-500">No subjects assigned for this section.</p>
                                @else
                                    <ul class="space-y-1">
                                        @foreach($section['subjects'] as $subject)
                                            <li class="subject-draggable touch-none text-sm text-gray-700 dark:text-gray-300 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-grab flex flex-col gap-0.5"
                                                draggable="false"
                                                data-subject-id="{{ $subject['id'] }}"
                                                data-section-id="{{ $section['id'] }}"
                                                data-term-id="{{ $termIdValue }}">
                                                <div class="subject-title">
                                                    {{ $subject['code'] ? $subject['code'].' - '.$subject['name'] : $subject['name'] }}
                                                </div>
                                                <div class="subject-meta hidden"></div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Create Schedule form is hidden (kept for JS state and drag/drop logic) --}}
        <div class="hidden">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Create Schedule</h2>
            <form id="schedule-form" class="space-y-4">
                @csrf
                <input type="hidden" name="term_id" id="term_id" value="{{ $termId ?? '' }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                    <select name="subject_id" id="subject_id" required class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Subject</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room</label>
                    <select name="room_id" id="room_id" required class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Room</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section</label>
                    <select name="section_id" id="section_id" required class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Section</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teacher</label>
                    <select name="teacher_id" id="teacher_id" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Teacher (optional)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (hours)</label>
                    <input type="number" id="duration" min="1" max="4" step="1" value="1" class="w-full rounded-md border-gray-300 shadow-sm">
                    <div class="mt-2 grid grid-cols-4 gap-2">
                        <button type="button" class="duration-preset text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600" data-duration="1">1h</button>
                        <button type="button" class="duration-preset text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600" data-duration="2">2h</button>
                        <button type="button" class="duration-preset text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600" data-duration="3">3h</button>
                        <button type="button" class="duration-preset text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600" data-duration="4">4h</button>
                    </div>
                </div>

                <button type="button" id="confirm-schedule-card" class="w-full px-3 py-2 rounded text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
                    Add To Card
                </button>

                <div class="space-y-2 p-3 rounded border border-gray-200 dark:border-gray-700">
                    <button type="button" id="click-place-toggle" class="w-full px-3 py-2 rounded text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white">
                        Click-to-place: OFF
                    </button>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="reuse-last" class="px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 disabled:opacity-50" disabled>
                            Reuse Last
                        </button>
                        <button type="button" id="clear-form" class="px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600">
                            Clear Form
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                        Tip: Turn on click-to-place, then click any empty timetable slot to create quickly.
                    </p>
                </div>
                
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded border-2 border-dashed border-indigo-300 dark:border-indigo-600">
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Set details, click <strong>Add To Card</strong>, then drag this card to the timetable:</p>
                    <div id="draggable-schedule" 
                         class="draggable-schedule-item cursor-move bg-indigo-100 dark:bg-indigo-900/40 p-3 rounded border-2 border-indigo-400 dark:border-indigo-600 opacity-70"
                         draggable="true">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white" id="preview-subject">No confirmed card yet</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400" id="preview-teacher"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-500" id="preview-section"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-500" id="preview-room"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-500" id="preview-duration"></div>
                        <div class="text-[11px] text-amber-600 dark:text-amber-400 mt-2" id="preview-status">Waiting for confirmation</div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Timetable Grid --}}
    @php
        $roomsToShow = isset($selectedRooms) && count($selectedRooms)
            ? $rooms->whereIn('id', $selectedRooms)
            : $rooms;
    @endphp
    <div id="timetable-grid" class="lg:col-span-9 bg-white dark:bg-gray-800 rounded-lg shadow p-4 overflow-x-auto">
        @php
            $selectedRoomIds = array_values(array_filter(array_map('intval', (array) ($selectedRooms ?? []))));
            $availableRooms = $rooms->whereNotIn('id', $selectedRoomIds);
            $selectedRoomsForSelect = $rooms->whereIn('id', $selectedRoomIds);
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-10 mb-2">
            <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-900 p-1">
                <button type="button" class="timetable-tab px-4 py-1.5 text-sm font-semibold rounded-md bg-white text-gray-900 shadow-sm" data-view="room">
                    Rooms
                </button>
                <button type="button" class="timetable-tab px-4 py-1.5 text-sm font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:text-gray-900" data-view="section">
                    Sections
                </button>
                <button type="button" class="timetable-tab px-4 py-1.5 text-sm font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:text-gray-900" data-view="teacher">
                    Teachers
                </button>
            </div>
            <div class="flex flex-nowrap items-center gap-3 text-sm overflow-x-auto">
                <form method="GET" action="{{ route('admin.schedules.timetable') }}" class="flex items-center gap-2 room-controls shrink-0">
                    <input type="hidden" name="term_id" value="{{ $termId }}">
                    <input type="hidden" name="schedule_name" value="{{ $scheduleName }}">
                    @foreach($selectedRoomIds as $roomId)
                        <input type="hidden" name="rooms[]" value="{{ $roomId }}">
                    @endforeach
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Add Room</label>
                    <select name="add_room_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select room</option>
                        @foreach($availableRooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-md">Add</button>
                </form>

                <form method="GET" action="{{ route('admin.schedules.timetable') }}" class="flex items-center gap-2 section-controls hidden shrink-0">
                    <input type="hidden" name="term_id" value="{{ $termId }}">
                    <input type="hidden" name="schedule_name" value="{{ $scheduleName }}">
                    @foreach($selectedRoomIds as $roomId)
                        <input type="hidden" name="rooms[]" value="{{ $roomId }}">
                    @endforeach
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Add Section</label>
                    <select name="add_section_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-md">Add</button>
                </form>

                <form method="GET" action="{{ route('admin.schedules.timetable') }}" class="flex items-center gap-2 section-controls hidden shrink-0">
                    <input type="hidden" name="term_id" value="{{ $termId }}">
                    <input type="hidden" name="schedule_name" value="{{ $scheduleName }}">
                    @foreach($selectedRoomIds as $roomId)
                        <input type="hidden" name="rooms[]" value="{{ $roomId }}">
                    @endforeach
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Delete Section</label>
                    <select name="remove_section_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-md">Delete</button>
                </form>

                <form method="GET" action="{{ route('admin.schedules.timetable') }}" class="flex items-center gap-2 teacher-controls hidden shrink-0">
                    <input type="hidden" name="term_id" value="{{ $termId }}">
                    <input type="hidden" name="schedule_name" value="{{ $scheduleName }}">
                    @foreach($selectedRoomIds as $roomId)
                        <input type="hidden" name="rooms[]" value="{{ $roomId }}">
                    @endforeach
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Add Teacher</label>
                    <select name="add_teacher_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-md">Add</button>
                </form>

                <form method="GET" action="{{ route('admin.schedules.timetable') }}" class="flex items-center gap-2 teacher-controls hidden shrink-0">
                    <input type="hidden" name="term_id" value="{{ $termId }}">
                    <input type="hidden" name="schedule_name" value="{{ $scheduleName }}">
                    @foreach($selectedRoomIds as $roomId)
                        <input type="hidden" name="rooms[]" value="{{ $roomId }}">
                    @endforeach
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Remove Teacher</label>
                    <select name="remove_teacher_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-md">Remove</button>
                </form>

                <form method="GET" action="{{ route('admin.schedules.timetable') }}" class="flex items-center gap-2 room-controls shrink-0">
                    <input type="hidden" name="term_id" value="{{ $termId }}">
                    <input type="hidden" name="schedule_name" value="{{ $scheduleName }}">
                    @foreach($selectedRoomIds as $roomId)
                        <input type="hidden" name="rooms[]" value="{{ $roomId }}">
                    @endforeach
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Remove Room</label>
                    <select name="remove_room_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select room</option>
                        @foreach($selectedRoomsForSelect as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-md">Remove</button>
                </form>
            </div>
        </div>
        @php
            $timeSlots = [];
            for ($hour = 7; $hour < 19; $hour++) {
                $timeSlots[] = sprintf('%02d:00', $hour) . ' - ' . sprintf('%02d:00', $hour + 1);
            }
            
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            function timeToMinutes($time) {
                $parts = explode(':', $time);
                return (int)$parts[0] * 60 + (int)$parts[1];
            }

            function formatTimeNoMeridiem($time) {
                $parts = explode(':', $time);
                $hour = (int)($parts[0] ?? 0);
                $minute = (int)($parts[1] ?? 0);
                $hour12 = $hour % 12;
                if ($hour12 === 0) {
                    $hour12 = 12;
                }
                return sprintf('%d:%02d', $hour12, $minute);
            }
            
            $schedulePositionsByRoom = [];
            $schedulePositionsByTeacher = [];
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
                    if ($slotParts[0] === $scheduleStart) {
                        $startSlotIndex = $idx;
                        break;
                    }
                }
                
                if ($startSlotIndex !== null) {
                    $slotCount = (int) max(1, ceil(($endMin - $startMin) / 60));
                    $pos = [
                        'schedule' => $schedule,
                        'day' => $day,
                        'dayIndex' => array_search($day, $days),
                        'startSlotIndex' => $startSlotIndex,
                        'slotCount' => $slotCount,
                    ];

                    $schedulePositionsByRoom[$schedule->room_id][] = $pos;

                    if (!empty($schedule->teacher_id)) {
                        $schedulePositionsByTeacher[$schedule->teacher_id][] = $pos;
                    }
                    if (!empty($schedule->section_id)) {
                        $schedulePositionsBySection[$schedule->section_id][] = $pos;
                    }
                }
            }
            
            $colorClasses = [
                'bg-pink-200 dark:bg-pink-900/40',
                'bg-yellow-200 dark:bg-yellow-900/40',
                'bg-blue-200 dark:bg-blue-900/40',
                'bg-green-200 dark:bg-green-900/40',
                'bg-purple-200 dark:bg-purple-900/40',
            ];
        @endphp

        <div id="timetable-views" class="space-y-8">
            {{-- Room view --}}
            <div id="view-room" class="view-mode">
                @foreach($roomsToShow as $room)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Room: {{ $room->name }}</h3>
                        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-sm">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr>
                                        <th class="bg-blue-600 dark:bg-blue-700 text-white font-bold px-4 py-3 text-center border-2 border-blue-700 dark:border-blue-800">
                                            Time
                                        </th>
                                        @foreach($dayNames as $dayName)
                                            <th class="bg-blue-600 dark:bg-blue-700 text-white font-bold px-4 py-3 text-center border-2 border-blue-700 dark:border-blue-800">
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
                                            <td class="bg-gray-100 dark:bg-gray-900 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 font-medium border border-gray-300 dark:border-gray-600 text-right w-32">
                                                {{ formatTimeNoMeridiem($slotStart) }} - {{ formatTimeNoMeridiem($slotEnd) }}
                                            </td>

                                            @foreach($days as $dayIndex => $day)
                                                @php
                                                    $hasSchedule = false;
                                                    $scheduleInCell = null;
                                                    $positions = $schedulePositionsByRoom[$room->id] ?? [];
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

                                                    $colorIndex = $scheduleInCell ? (array_search($scheduleInCell['schedule']->subject_id, array_column($schedules->toArray(), 'subject_id')) % count($colorClasses)) : 0;
                                                @endphp

                                                <td class="relative border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 min-h-[40px] p-0 drop-zone" 
                                                    data-day="{{ $day }}"
                                                    data-time-start="{{ $slotStart }}"
                                                    data-time-end="{{ $slotEnd }}"
                                                    data-room-id="{{ $room->id }}"
                                                    ondragover="event.preventDefault(); event.dataTransfer.dropEffect = 'move';"
                                                    ondrop="handleTimetableDrop(event)"
                                                    style="height: var(--timetable-slot-height);">
                                                    @if($hasSchedule && $scheduleInCell)
                                                        <div 
                                                            class="schedule-item absolute inset-0 m-0.5 {{ $colorClasses[$colorIndex] }} rounded border border-gray-400 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow"
                                                            data-schedule-id="{{ $scheduleInCell['schedule']->id }}"
                                                            data-teacher-id="{{ $scheduleInCell['schedule']->teacher_id }}"
                                                            data-subject-id="{{ $scheduleInCell['schedule']->subject_id }}"
                                                            data-section-id="{{ $scheduleInCell['schedule']->section_id }}"
                                                            data-room-id="{{ $scheduleInCell['schedule']->room_id }}"
                                                            data-term-id="{{ $scheduleInCell['schedule']->term_id }}"
                                                            data-is-published="{{ $scheduleInCell['schedule']->is_published ? '1' : '0' }}"
                                                            data-slot-count="{{ $scheduleInCell['slotCount'] }}"
                                                            data-day="{{ $day }}"
                                                            data-time-start="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_start)->format('H:i') }}"
                                                            data-time-end="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_end)->format('H:i') }}"
                                                            style="height: calc({{ $scheduleInCell['slotCount'] }} * var(--timetable-slot-height) - 4px); z-index: 10;"
                                                            draggable="true"
                                                        >
                                                            <button
                                                                type="button"
                                                                class="delete-schedule-btn absolute top-1 right-1 h-5 w-5 rounded-full bg-red-600 hover:bg-red-700 text-white text-[10px] leading-none flex items-center justify-center shadow"
                                                                data-schedule-id="{{ $scheduleInCell['schedule']->id }}"
                                                                title="Remove schedule block"
                                                                aria-label="Remove schedule block"
                                                            >
                                                                &times;
                                                            </button>
                                                            @php
                                                                $sizeClass = $scheduleInCell['slotCount'] <= 1 ? 'size-xs' : ($scheduleInCell['slotCount'] <= 2 ? 'size-sm' : 'size-md');
                                                            @endphp
                                                            <div class="schedule-body {{ $sizeClass }} h-full flex flex-col">
                                                                @if($scheduleInCell['schedule']->is_published)
                                                                    <div class="mb-1">
                                                                        <span class="inline-flex items-center rounded bg-emerald-600 px-1.5 py-0.5 text-[9px] font-semibold text-white">PUBLISHED</span>
                                                                    </div>
                                                                @endif
                                                                <div class="subject-line font-semibold text-gray-900 dark:text-white" title="{{ $scheduleInCell['schedule']->subject->name }}">
                                                                    {{ \Illuminate\Support\Str::limit($scheduleInCell['schedule']->subject->name, 32) }}
                                                                </div>
                                                                <div class="meta-line text-gray-700 dark:text-gray-300">{{ $scheduleInCell['schedule']->section->name }}</div>
                                                                <div class="meta-line text-gray-600 dark:text-gray-400">{{ $scheduleInCell['schedule']->room->name }}</div>
                                                            </div>
                                                        </div>
                                                    @elseif(!$isCovered)
                                                        <div class="absolute inset-0 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center justify-center">
                                                            <span class="text-[9px] text-gray-400 dark:text-gray-500 opacity-0 hover:opacity-100">Drop here</span>
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
                @endforeach
            </div>

            {{-- Teacher view --}}
            <div id="view-teacher" class="view-mode hidden">
                @foreach($teachers as $teacher)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Teacher: {{ $teacher->name }}</h3>
                        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-sm">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr>
                                        <th class="bg-blue-600 dark:bg-blue-700 text-white font-bold px-4 py-3 text-center border-2 border-blue-700 dark:border-blue-800">
                                            Time
                                        </th>
                                        @foreach($dayNames as $dayName)
                                            <th class="bg-blue-600 dark:bg-blue-700 text-white font-bold px-4 py-3 text-center border-2 border-blue-700 dark:border-blue-800">
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
                                            <td class="bg-gray-100 dark:bg-gray-900 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 font-medium border border-gray-300 dark:border-gray-600 text-right w-32">
                                                {{ formatTimeNoMeridiem($slotStart) }} - {{ formatTimeNoMeridiem($slotEnd) }}
                                            </td>

                                            @foreach($days as $dayIndex => $day)
                                                @php
                                                    $hasSchedule = false;
                                                    $scheduleInCell = null;
                                                    $positions = $schedulePositionsByTeacher[$teacher->id] ?? [];
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

                                                    $colorIndex = $scheduleInCell ? (array_search($scheduleInCell['schedule']->subject_id, array_column($schedules->toArray(), 'subject_id')) % count($colorClasses)) : 0;
                                                @endphp

                                                <td class="relative border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 min-h-[40px] p-0 drop-zone" 
                                                    data-day="{{ $day }}"
                                                    data-time-start="{{ $slotStart }}"
                                                    data-time-end="{{ $slotEnd }}"
                                                    data-teacher-id="{{ $teacher->id }}"
                                                    ondragover="event.preventDefault(); event.dataTransfer.dropEffect = 'move';"
                                                    ondrop="handleTimetableDrop(event)"
                                                    style="height: var(--timetable-slot-height);">
                                                    @if($hasSchedule && $scheduleInCell)
                                                        <div 
                                                            class="schedule-item absolute inset-0 m-0.5 {{ $colorClasses[$colorIndex] }} rounded border border-gray-400 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow"
                                                            data-schedule-id="{{ $scheduleInCell['schedule']->id }}"
                                                            data-teacher-id="{{ $scheduleInCell['schedule']->teacher_id }}"
                                                            data-subject-id="{{ $scheduleInCell['schedule']->subject_id }}"
                                                            data-section-id="{{ $scheduleInCell['schedule']->section_id }}"
                                                            data-room-id="{{ $scheduleInCell['schedule']->room_id }}"
                                                            data-term-id="{{ $scheduleInCell['schedule']->term_id }}"
                                                            data-is-published="{{ $scheduleInCell['schedule']->is_published ? '1' : '0' }}"
                                                            data-slot-count="{{ $scheduleInCell['slotCount'] }}"
                                                            data-day="{{ $day }}"
                                                            data-time-start="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_start)->format('H:i') }}"
                                                            data-time-end="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_end)->format('H:i') }}"
                                                            style="height: calc({{ $scheduleInCell['slotCount'] }} * var(--timetable-slot-height) - 4px); z-index: 10;"
                                                            draggable="true"
                                                        >
                                                            <button
                                                                type="button"
                                                                class="delete-schedule-btn absolute top-1 right-1 h-5 w-5 rounded-full bg-red-600 hover:bg-red-700 text-white text-[10px] leading-none flex items-center justify-center shadow"
                                                                data-schedule-id="{{ $scheduleInCell['schedule']->id }}"
                                                                title="Remove schedule block"
                                                                aria-label="Remove schedule block"
                                                            >
                                                                &times;
                                                            </button>
                                                            @php
                                                                $sizeClass = $scheduleInCell['slotCount'] <= 1 ? 'size-xs' : ($scheduleInCell['slotCount'] <= 2 ? 'size-sm' : 'size-md');
                                                            @endphp
                                                            <div class="schedule-body {{ $sizeClass }} h-full flex flex-col">
                                                                @if($scheduleInCell['schedule']->is_published)
                                                                    <div class="mb-1">
                                                                        <span class="inline-flex items-center rounded bg-emerald-600 px-1.5 py-0.5 text-[9px] font-semibold text-white">PUBLISHED</span>
                                                                    </div>
                                                                @endif
                                                                <div class="subject-line font-semibold text-gray-900 dark:text-white" title="{{ $scheduleInCell['schedule']->subject->name }}">
                                                                    {{ \Illuminate\Support\Str::limit($scheduleInCell['schedule']->subject->name, 32) }}
                                                                </div>
                                                                <div class="meta-line text-gray-700 dark:text-gray-300">{{ $scheduleInCell['schedule']->section->name }}</div>
                                                                <div class="meta-line text-gray-600 dark:text-gray-400">{{ $scheduleInCell['schedule']->room->name }}</div>
                                                            </div>
                                                        </div>
                                                    @elseif(!$isCovered)
                                                        <div class="absolute inset-0 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center justify-center">
                                                            <span class="text-[9px] text-gray-400 dark:text-gray-500 opacity-0 hover:opacity-100">Drop here</span>
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
                @endforeach
            </div>

            {{-- Section view --}}
            <div id="view-section" class="view-mode hidden">
                @if($sections->isEmpty())
                    <div class="text-sm text-gray-500 dark:text-gray-400">No sections found for the selected term.</div>
                @else
                    @foreach($sections as $section)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Section: {{ $section->name }}</h3>
                            <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-sm">
                                <table class="min-w-full border-collapse">
                                    <thead>
                                        <tr>
                                            <th class="bg-blue-600 dark:bg-blue-700 text-white font-bold px-4 py-3 text-center border-2 border-blue-700 dark:border-blue-800">
                                                Time
                                            </th>
                                            @foreach($dayNames as $dayName)
                                                <th class="bg-blue-600 dark:bg-blue-700 text-white font-bold px-4 py-3 text-center border-2 border-blue-700 dark:border-blue-800">
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
                                                <td class="bg-gray-100 dark:bg-gray-900 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 font-medium border border-gray-300 dark:border-gray-600 text-right w-32">
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

                                                        $colorIndex = $scheduleInCell ? (array_search($scheduleInCell['schedule']->subject_id, array_column($schedules->toArray(), 'subject_id')) % count($colorClasses)) : 0;
                                                    @endphp

                                                    <td class="relative border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 min-h-[40px] p-0 drop-zone" 
                                                        data-day="{{ $day }}"
                                                        data-time-start="{{ $slotStart }}"
                                                        data-time-end="{{ $slotEnd }}"
                                                        data-section-id="{{ $section->id }}"
                                                        ondragover="event.preventDefault(); event.dataTransfer.dropEffect = 'move';"
                                                        ondrop="handleTimetableDrop(event)"
                                                        style="height: var(--timetable-slot-height);">
                                                        @if($hasSchedule && $scheduleInCell)
                                                            <div 
                                                                class="schedule-item absolute inset-0 m-0.5 {{ $colorClasses[$colorIndex] }} rounded border border-gray-400 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow"
                                                                data-schedule-id="{{ $scheduleInCell['schedule']->id }}"
                                                                data-teacher-id="{{ $scheduleInCell['schedule']->teacher_id }}"
                                                                data-subject-id="{{ $scheduleInCell['schedule']->subject_id }}"
                                                                data-section-id="{{ $scheduleInCell['schedule']->section_id }}"
                                                                data-room-id="{{ $scheduleInCell['schedule']->room_id }}"
                                                                data-term-id="{{ $scheduleInCell['schedule']->term_id }}"
                                                                data-is-published="{{ $scheduleInCell['schedule']->is_published ? '1' : '0' }}"
                                                                data-slot-count="{{ $scheduleInCell['slotCount'] }}"
                                                                data-day="{{ $day }}"
                                                                data-time-start="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_start)->format('H:i') }}"
                                                                data-time-end="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_end)->format('H:i') }}"
                                                                style="height: calc({{ $scheduleInCell['slotCount'] }} * var(--timetable-slot-height) - 4px); z-index: 10;"
                                                                draggable="true"
                                                            >
                                                                <button
                                                                    type="button"
                                                                    class="delete-schedule-btn absolute top-1 right-1 h-5 w-5 rounded-full bg-red-600 hover:bg-red-700 text-white text-[10px] leading-none flex items-center justify-center shadow"
                                                                    data-schedule-id="{{ $scheduleInCell['schedule']->id }}"
                                                                    title="Remove schedule block"
                                                                    aria-label="Remove schedule block"
                                                                >
                                                                    &times;
                                                                </button>
                                                                @php
                                                                    $sizeClass = $scheduleInCell['slotCount'] <= 1 ? 'size-xs' : ($scheduleInCell['slotCount'] <= 2 ? 'size-sm' : 'size-md');
                                                                @endphp
                                                                <div class="schedule-body {{ $sizeClass }} h-full flex flex-col">
                                                                    @if($scheduleInCell['schedule']->is_published)
                                                                        <div class="mb-1">
                                                                            <span class="inline-flex items-center rounded bg-emerald-600 px-1.5 py-0.5 text-[9px] font-semibold text-white">PUBLISHED</span>
                                                                        </div>
                                                                    @endif
                                                                    <div class="subject-line font-semibold text-gray-900 dark:text-white" title="{{ $scheduleInCell['schedule']->subject->name }}">
                                                                        {{ \Illuminate\Support\Str::limit($scheduleInCell['schedule']->subject->name, 32) }}
                                                                    </div>
                                                                    <div class="meta-line text-gray-700 dark:text-gray-300">{{ $scheduleInCell['schedule']->section->name }}</div>
                                                                    <div class="meta-line text-gray-600 dark:text-gray-400">{{ $scheduleInCell['schedule']->room->name }}</div>
                                                                </div>
                                                            </div>
                                                        @elseif(!$isCovered)
                                                            <div class="absolute inset-0 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center justify-center">
                                                                <span class="text-[9px] text-gray-400 dark:text-gray-500 opacity-0 hover:opacity-100">Drop here</span>
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
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $curriculaPayload = $curricula->map(function ($curriculum) {
        return [
            'id' => $curriculum->id,
            'curriculum_code' => $curriculum->curriculum_code,
            'term_id' => $curriculum->term_id,
            'term_label' => $curriculum->term
                ? ($curriculum->term->term_code . ' - ' . $curriculum->term->academic_year . ' - ' . $curriculum->term->semester)
                : 'No term',
            'subjects' => $curriculum->subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'year_level' => $subject->pivot->year_level ?? null,
                    'period_number' => $subject->pivot->period_number ?? null,
                    'period_label' => $subject->pivot->period_label ?? null,
                ];
            })->values(),
        ];
    })->values();

    $subjectsPayload = $subjects->map(function ($subject) {
        $lec = (int) ($subject->lec_unit ?? 0);
        $lab = (int) ($subject->lab_unit ?? 0);
        $required = max(1, $lec + $lab);
        $type = $lab > 0 ? 'Lab' : 'Lecture';

        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'code' => $subject->code,
            'required_room_type' => $subject->required_room_type,
            'required_hours' => $required,
            'subject_type' => $type,
        ];
    })->values();

    $roomsPayload = $roomsToShow->map(function ($room) {
        return [
            'id' => $room->id,
            'name' => $room->name,
            'room_type' => $room->room_type,
        ];
    })->values();

    $sectionsSelectPayload = $sections->map(function ($section) {
        return [
            'id' => $section->id,
            'name' => $section->name,
            'term_id' => $section->term_id,
            'year_level' => $section->year_level,
        ];
    })->values();

    $teachersPayload = $teachers->map(function ($teacher) {
        return [
            'id' => $teacher->id,
            'name' => $teacher->name,
            'expertise' => $teacher->expertise,
            'subject_ids' => $teacher->subjects->pluck('id')->values(),
        ];
    })->values();

    $schedulesPayload = $schedules->map(function ($schedule) {
        return [
            'id' => $schedule->id,
            'teacher_id' => $schedule->teacher_id,
            'subject_id' => $schedule->subject_id,
            'section_id' => $schedule->section_id,
            'room_id' => $schedule->room_id,
            'term_id' => $schedule->term_id,
            'day' => strtolower($schedule->day),
            'time_start' => \Carbon\Carbon::parse($schedule->time_start)->format('H:i'),
            'time_end' => \Carbon\Carbon::parse($schedule->time_end)->format('H:i'),
            'is_published' => (bool) ($schedule->is_published ?? false),
        ];
    })->values();

    $conflictSchedulesPayload = ($conflictSchedules ?? collect())->map(function ($schedule) {
        return [
            'schedule_id' => $schedule->id,
            'teacher_id' => $schedule->teacher_id,
            'section_id' => $schedule->section_id,
            'room_id' => $schedule->room_id,
            'day' => strtolower($schedule->day),
            'time_start' => \Carbon\Carbon::parse($schedule->time_start)->format('H:i'),
            'time_end' => \Carbon\Carbon::parse($schedule->time_end)->format('H:i'),
        ];
    })->values();
@endphp

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

    .schedule-body.size-xs .subject-line {
        -webkit-line-clamp: 1;
    }

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

    .drop-zone-conflict {
        background-color: rgba(239, 68, 68, 0.14) !important;
        box-shadow: inset 0 0 0 2px rgba(239, 68, 68, 0.55);
    }

    .subject-meta {
        font-size: 11px;
        line-height: 1.2;
        font-weight: 600;
        color: #059669;
    }

    .timetable-tab.active {
        background: #ffffff;
        color: #111827;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }

    .schedule-item,
    .resize-handle {
        touch-action: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    console.debug('Timetable script loaded');

    // Debug overlay (helps verify script is running in the browser)
    const debugOverlay = document.createElement('div');
    debugOverlay.id = 'timetable-debug-overlay';
    debugOverlay.style.position = 'fixed';
    debugOverlay.style.top = '1rem';
    debugOverlay.style.right = '1rem';
    debugOverlay.style.zIndex = '9999';
    debugOverlay.style.background = 'rgba(0, 0, 0, 0.7)';
    debugOverlay.style.color = 'white';
    debugOverlay.style.fontSize = '12px';
    debugOverlay.style.lineHeight = '1.3';
    debugOverlay.style.padding = '0.5rem 0.75rem';
    debugOverlay.style.borderRadius = '0.5rem';
    debugOverlay.style.maxWidth = '240px';
    debugOverlay.style.pointerEvents = 'none';
    debugOverlay.innerText = 'debug: script loaded';
    document.body.appendChild(debugOverlay);

    function setDebug(text) {
        if (debugOverlay) debugOverlay.innerText = text;
        console.debug(text);
    }

    setDebug('script loaded');

    const draggableItem = document.getElementById('draggable-schedule');
    const termFilter = document.getElementById('term-filter');
    const timetableGrid = document.getElementById('timetable-grid');
    const durationInput = document.getElementById('duration');
    const curriculumSelect = document.getElementById('curriculum_id');
    const subjectSelect = document.getElementById('subject_id');
    const roomSelect = document.getElementById('room_id');
    const sectionSelect = document.getElementById('section_id');
    const sectionFilter = document.getElementById('section-filter') || sectionSelect;
    const viewTypeSelect = document.getElementById('view_type');
    const sectionSubjectsContainer = document.getElementById('section-subjects');
    const teacherSelect = document.getElementById('teacher_id');
    const termInput = document.getElementById('term_id');
    const confirmScheduleCardBtn = document.getElementById('confirm-schedule-card');
    const saveScheduleBtn = document.getElementById('save-schedule-btn');
    const clickPlaceToggle = document.getElementById('click-place-toggle');
    const reuseLastBtn = document.getElementById('reuse-last');
    const clearFormBtn = document.getElementById('clear-form');
    const curriculumCodeDisplay = document.getElementById('curriculum-code-display');
    const curriculumTermDisplay = document.getElementById('curriculum-term-display');
    const previewSubject = document.getElementById('preview-subject');
    const previewTeacher = document.getElementById('preview-teacher');
    const previewSection = document.getElementById('preview-section');
    const previewRoom = document.getElementById('preview-room');
    const previewDuration = document.getElementById('preview-duration');
    const previewStatus = document.getElementById('preview-status');
    const timetableTabs = document.querySelectorAll('.timetable-tab');
    const viewRoom = document.getElementById('view-room');
    const viewSection = document.getElementById('view-section');
    const viewTeacher = document.getElementById('view-teacher');
    const publishWeekBtn = document.getElementById('publish-week-btn');
    const conflictPopup = document.getElementById('conflict-popup');
    const conflictPopupList = document.getElementById('conflict-popup-list');
    const conflictPopupClose = document.getElementById('conflict-popup-close');
    let conflictPopupTimer = null;
    let draggedData = null;
    let activeDropZone = null;
    let dragShadowEl = null;
    let clickPlaceMode = false;
    let lastScheduleDraft = null;
    let confirmedDraft = null;
    let teacherSelectionConfirmed = false;
    const autosaveEnabled = false;
    let draftIdCounter = 1;

    let pointerDragging = false;
    let pointerDragGhost = null;
    let pointerDragCell = null;
    let lastPointerX = 0;
    let lastPointerY = 0;
    const SLOT_HEIGHT = 40;

    // Pointer-driven custom subject drag (replaces native HTML5 drag for consistent behavior)
    let subjectDragActive = false;
    let subjectDragCard = null;
    let subjectDragPointerId = null;

    // Schedule resize (drag bottom handle to adjust duration)
    let resizingScheduleItem = null;
    let resizeStartY = 0;
    let resizeStartHeight = 0;
    let resizeStartSlots = 0;
    let resizeOriginalHeight = 0;
    let resizeWasDraggable = null;
    let resizeOriginalEndTime = null;

    const curriculaData = @json($curriculaPayload);
    const subjectsData = @json($subjectsPayload);
    const roomsData = @json($roomsPayload);
    const sectionsData = @json($sectionsSelectPayload);
    const sectionSubjectsData = @json($sectionsPayload);
    const teachersData = @json($teachersPayload);
    const schedulesData = @json($schedulesPayload);
    const conflictSchedulePool = @json($conflictSchedulesPayload);
    const colorClasses = @json($colorClasses);

    const subjectMap = new Map(subjectsData.map((subject) => [String(subject.id), subject]));
    const roomsMap = new Map(roomsData.map((room) => [String(room.id), room]));
    const sectionsMap = new Map(sectionsData.map((section) => [String(section.id), section]));
    const teachersMap = new Map(teachersData.map((teacher) => [String(teacher.id), teacher]));
    const curriculumMap = new Map(curriculaData.map((curriculum) => [String(curriculum.id), curriculum]));
    const storageKeyCurriculum = 'timetable_selected_curriculum_id';
    const storageKeyView = 'timetable_view_mode';
    const storageKeyDraft = 'timetable_draft_state';

    const initialScheduleName = @json($scheduleName ?? '');
    const initialSelectedRooms = @json($selectedRooms ?? []);

    const endpoints = {
        timetable: '{{ route("admin.schedules.timetable", [], false) }}',
        checkConflicts: '{{ route("admin.schedules.check-conflicts", [], false) }}',
        storeFromTimetable: '{{ route("admin.schedules.store-from-timetable", [], false) }}',
        saveDraft: '{{ route("admin.schedules.save-draft", [], false) }}',
        publishWeek: '{{ route("admin.schedules.publish-week", [], false) }}',
        updateTemplate: '{{ route("admin.schedules.update", ["schedule" => "__SCHEDULE_ID__"], false) }}',
        destroyTemplate: '{{ route("admin.schedules.destroy", ["schedule" => "__SCHEDULE_ID__"], false) }}'
    };

    function normalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function getDraftStorageKey() {
        const termId = termInput.value || termFilter?.value || '';
        const roomsKey = (initialSelectedRooms || []).join(',');
        const nameKey = initialScheduleName || '';
        return `${storageKeyDraft}:${termId}:${roomsKey}:${nameKey}`;
    }

    function saveDraftState() {
        if (autosaveEnabled) return;
        const payload = getCurrentScheduleSnapshots().map((item) => ({
            id: item.id,
            teacher_id: item.teacher_id || null,
            subject_id: item.subject_id,
            section_id: item.section_id,
            room_id: item.room_id,
            term_id: item.term_id,
            day: item.day,
            time_start: item.time_start,
            time_end: item.time_end,
            slot_count: item.slot_count,
            is_published: !!item.is_published
        }));
        localStorage.setItem(getDraftStorageKey(), JSON.stringify(payload));
    }

    function loadDraftState() {
        if (autosaveEnabled) return [];
        const raw = localStorage.getItem(getDraftStorageKey());
        if (!raw) return [];
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (_) {
            return [];
        }
    }

    function setTimetableView(view) {
        const selected = view === 'section' || view === 'teacher' ? view : 'room';
        if (viewRoom) viewRoom.classList.toggle('hidden', selected !== 'room');
        if (viewSection) viewSection.classList.toggle('hidden', selected !== 'section');
        if (viewTeacher) viewTeacher.classList.toggle('hidden', selected !== 'teacher');

        document.querySelectorAll('.room-controls').forEach((el) => {
            el.classList.toggle('hidden', selected !== 'room');
        });
        document.querySelectorAll('.section-controls').forEach((el) => {
            el.classList.toggle('hidden', selected !== 'section');
        });
        document.querySelectorAll('.teacher-controls').forEach((el) => {
            el.classList.toggle('hidden', selected !== 'teacher');
        });

        timetableTabs.forEach((tab) => {
            const isActive = tab.dataset.view === selected;
            tab.classList.toggle('active', isActive);
            if (isActive) {
                tab.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                tab.classList.remove('text-gray-600', 'dark:text-gray-300');
            } else {
                tab.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                tab.classList.add('text-gray-600', 'dark:text-gray-300');
            }
        });

        localStorage.setItem(storageKeyView, selected);

        if (selected === 'section') {
            refreshSectionView();
        }
        if (selected === 'teacher') {
            refreshTeacherView();
        }

        applySidebarGradient();
    }

    const adminSidebar = document.querySelector('.admin-sidebar');
    function applySidebarGradient() {
        if (!adminSidebar) return;
        adminSidebar.style.background = 'linear-gradient(180deg, #1e3a8a 0%, #112a5f 55%, #0a1533 100%)';
        adminSidebar.style.backgroundAttachment = 'fixed';
    }

    const labRoomTypes = new Set(['computer_lab', 'chemistry_lab']);

    function isRoomCompatible(subject, room) {
        if (!subject || !room) {
            return false;
        }

        if (subject.required_room_type === 'lecture') {
            return room.room_type === 'lecture' || labRoomTypes.has(room.room_type);
        }

        return room.room_type === subject.required_room_type;
    }

    function getConflictLabel(key) {
        const map = {
            teacher: 'Teacher Conflict',
            room: 'Room Conflict',
            section: 'Section Conflict',
            slot: 'Time Slot Conflict',
            room_type: 'Room Type Conflict',
            section_term: 'Section-Term Conflict',
            curriculum_term: 'Curriculum-Term Conflict',
            curriculum_subject: 'Curriculum-Subject Conflict',
            curriculum: 'Curriculum Conflict',
        };
        return map[key] || 'Schedule Conflict';
    }

    function getConflictSign(key) {
        const map = {
            teacher: '⚠',
            room: '⚠',
            section: '⚠',
            slot: '⛔',
            room_type: '⚠',
            section_term: '⚠',
            curriculum_term: '⚠',
            curriculum_subject: '⚠',
            curriculum: '⚠',
        };
        return map[key] || '⚠';
    }

    function showConflictPopup(conflicts) {
        if (!conflictPopup || !conflictPopupList) return;
        if (conflictPopupTimer) {
            clearTimeout(conflictPopupTimer);
        }

        conflictPopupList.innerHTML = '';
        Object.entries(conflicts || {}).forEach(([key, message]) => {
            const li = document.createElement('li');
            li.innerHTML = `<span class="font-semibold">${getConflictSign(key)} ${getConflictLabel(key)}:</span> ${message}`;
            conflictPopupList.appendChild(li);
        });

        conflictPopup.classList.remove('hidden');
        conflictPopupTimer = setTimeout(() => {
            conflictPopup.classList.add('hidden');
        }, 7000);
    }

    conflictPopupClose?.addEventListener('click', () => {
        if (conflictPopupTimer) {
            clearTimeout(conflictPopupTimer);
        }
        conflictPopup.classList.add('hidden');
    });

    function setSelectOptions(selectEl, options, placeholderText) {
        if (!selectEl) return;
        selectEl.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = placeholderText;
        selectEl.appendChild(placeholder);

        options.forEach((option) => {
            const item = document.createElement('option');
            item.value = option.value;
            item.textContent = option.label;
            selectEl.appendChild(item);
        });

        selectEl.disabled = options.length === 0;
    }

    function clearCurriculumDisplay() {
        if (curriculumCodeDisplay) curriculumCodeDisplay.textContent = '-';
        if (curriculumTermDisplay) curriculumTermDisplay.textContent = '-';
        termInput.value = termFilter?.value || '';

        // Ensure sections are selectable even without a curriculum selected.
        populateSections(termFilter?.value);
    }

    function markCardAsPending() {
        confirmedDraft = null;
        draggableItem.classList.add('opacity-70');
        previewStatus.textContent = 'Waiting for confirmation';
        previewStatus.className = 'text-[11px] text-amber-600 dark:text-amber-400 mt-2';
        updatePreview(null);
    }

    function setCardReady() {
        draggableItem.classList.remove('opacity-70');
        previewStatus.textContent = 'Card ready to drag';
        previewStatus.className = 'text-[11px] text-emerald-600 dark:text-emerald-400 mt-2';
    }

    function populateCurricula() {
        if (!curriculumSelect) return;
        const options = curriculaData.map((curriculum) => ({
            value: String(curriculum.id),
            label: `${curriculum.curriculum_code} (${curriculum.term_label})`
        }));
        setSelectOptions(curriculumSelect, options, 'Select Curriculum');
    }

    function populateSubjects(curriculumId, sectionYearLevel = null, selectedSubjectId = '') {
        const curriculum = curriculumMap.get(String(curriculumId));
        if (!curriculum) {
            setSelectOptions(subjectSelect, [], 'Select Subject');
            return;
        }

        const allowedIds = new Set();
        (curriculum.subjects || []).forEach((entry) => {
            const yearLevel = entry.year_level || null;
            if (!sectionYearLevel || !yearLevel || String(yearLevel) === String(sectionYearLevel)) {
                allowedIds.add(String(entry.id));
            }
        });

        const options = subjectsData
            .filter((subject) => allowedIds.has(String(subject.id)))
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((subject) => ({
                value: String(subject.id),
                label: subject.code ? `${subject.code} - ${subject.name}` : subject.name
            }));

        setSelectOptions(subjectSelect, options, 'Select Subject');
        if (selectedSubjectId) {
            subjectSelect.value = String(selectedSubjectId);
        }
    }

    function populateRooms(subjectId, selectedRoomId = '') {
        const subject = subjectMap.get(String(subjectId));
        if (!subject) {
            setSelectOptions(roomSelect, [], 'Select Room');
            return;
        }

        const options = roomsData
            .filter((room) => isRoomCompatible(subject, room))
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((room) => ({
                value: String(room.id),
                label: `${room.name} (${room.room_type.replace('_', ' ')})`
            }));

        setSelectOptions(roomSelect, options, 'Select Room');
        if (selectedRoomId) {
            roomSelect.value = String(selectedRoomId);
        }
    }

    function populateSections(termId, selectedSectionId = '') {
        if (!termId) {
            setSelectOptions(sectionSelect, [], 'Select Section');
            setSelectOptions(sectionFilter, [], 'Select Section');
            return;
        }

        const options = sectionsData
            .filter((section) => section.term_id && String(section.term_id) === String(termId))
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((section) => ({
                value: String(section.id),
                label: section.name
            }));

        setSelectOptions(sectionSelect, options, 'Select Section');
        setSelectOptions(sectionFilter, options, 'Select Section');

        if (selectedSectionId) {
            sectionSelect.value = String(selectedSectionId);
            sectionFilter.value = String(selectedSectionId);
        }
    }

    function isTeacherCompatible(teacher, subject) {
        if (!subject) {
            return false;
        }
        if ((teacher.subject_ids || []).map(String).includes(String(subject.id))) {
            return true;
        }

        const expertise = normalize(teacher.expertise);
        if (!expertise) {
            return false;
        }

        return [subject.name, subject.code].some((value) => expertise.includes(normalize(value)));
    }

    function getDefaultTeacherId(subjectId) {
        const subject = subjectMap.get(String(subjectId));
        if (!subject) return '';

        const compatibleTeachers = teachersData
            .filter((teacher) => isTeacherCompatible(teacher, subject));

        if (compatibleTeachers.length) {
            return String(compatibleTeachers[0].id);
        }

        return teachersData.length ? String(teachersData[0].id) : '';
    }

    function getDefaultRoomId(subjectId) {
        const subject = subjectMap.get(String(subjectId));
        if (!subject) return '';

        const matchingRooms = roomsData.filter((room) => isRoomCompatible(subject, room));
        if (matchingRooms.length) {
            return String(matchingRooms[0].id);
        }

        return roomsData.length ? String(roomsData[0].id) : '';
    }

    function populateTeachers(subjectId, selectedTeacherId = '') {
        const subject = subjectMap.get(String(subjectId));
        if (!subject) {
            setSelectOptions(teacherSelect, [], 'Select Teacher');
            teacherSelectionConfirmed = false;
            return;
        }

        const options = teachersData
            .map((teacher) => ({
                value: String(teacher.id),
                label: teacher.name,
                compatible: isTeacherCompatible(teacher, subject),
            }))
            .sort((a, b) => {
                if (a.compatible !== b.compatible) {
                    return a.compatible ? -1 : 1;
                }
                return a.label.localeCompare(b.label);
            })
            .map((teacher) => ({
                value: teacher.value,
                label: teacher.compatible ? `[Compatible] ${teacher.label}` : teacher.label
            }));

        setSelectOptions(teacherSelect, options, 'Select Teacher');
        if (selectedTeacherId) {
            teacherSelect.value = String(selectedTeacherId);
        }
        // Always require explicit user selection after teacher list changes.
        teacherSelectionConfirmed = false;
    }

    function handleCurriculumChange(selectedSubjectId = '', selectedSectionId = '', selectedRoomId = '', selectedTeacherId = '') {
        if (!curriculumSelect) {
            clearCurriculumDisplay();
            populateSubjects('');
            populateRooms('');
            populateSections(termFilter?.value || termInput.value);
            populateTeachers('');
            renderSectionSubjects();
            return;
        }
        const curriculum = curriculumMap.get(String(curriculumSelect.value));
        if (!curriculum) {
            clearCurriculumDisplay();
            populateSubjects('');
            populateRooms('');
            populateSections('');
            populateTeachers('');
            renderSectionSubjects();
            return;
        }

        if (curriculumCodeDisplay) curriculumCodeDisplay.textContent = curriculum.curriculum_code || '-';
        if (curriculumTermDisplay) curriculumTermDisplay.textContent = curriculum.term_label || '-';
        termInput.value = curriculum.term_id ? String(curriculum.term_id) : '';

        populateSections(curriculum.term_id, selectedSectionId);

        const sectionId = selectedSectionId || sectionFilter.value || sectionSelect.value;
        const section = sectionsData.find((s) => String(s.id) === String(sectionId));
        const sectionYearLevel = section?.year_level || null;

        populateSubjects(curriculum.id, sectionYearLevel, selectedSubjectId);

        if (subjectSelect.value) {
            populateRooms(subjectSelect.value, selectedRoomId);
            populateTeachers(subjectSelect.value, selectedTeacherId);
        } else {
            populateRooms('');
            populateTeachers('');
        }

        renderSectionSubjects();
    }

    function calculateScheduledHours(subjectId, sectionId, termId) {
        const schedules = schedulesData.filter((s) =>
            String(s.subject_id) === String(subjectId) &&
            String(s.section_id) === String(sectionId) &&
            String(s.term_id) === String(termId)
        );

        return schedules.reduce((total, schedule) => {
            const start = parseMinutes(schedule.time_start);
            const end = parseMinutes(schedule.time_end);
            return total + ((end - start) / 60);
        }, 0);
    }

    function renderSectionSubjects() {
        const termId = termInput.value || termFilter?.value;

        sectionSubjectsContainer.innerHTML = '';

        if (!termId) {
            sectionSubjectsContainer.innerHTML = '<p class="text-sm text-gray-500">Select a term to load sections and subjects.</p>';
            return;
        }

        if (!sectionSubjectsData || !sectionSubjectsData.length) {
            sectionSubjectsContainer.innerHTML = '<p class="text-sm text-gray-500">No sections found for the selected term.</p>';
            return;
        }

        sectionSubjectsData.forEach((section) => {
            const sectionBlock = document.createElement('div');
            sectionBlock.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-3';

            const sectionHeader = document.createElement('div');
            sectionHeader.className = 'flex items-center justify-between mb-2';
            sectionHeader.innerHTML = `
                <div class="text-sm font-semibold text-gray-900 dark:text-white">${section.name}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">${section.curriculum_code || ''}</div>
            `;
            sectionBlock.appendChild(sectionHeader);

            if (!section.subjects || !section.subjects.length) {
                const empty = document.createElement('p');
                empty.className = 'text-sm text-gray-500';
                empty.textContent = 'No subjects found for this section.';
                sectionBlock.appendChild(empty);
            } else {
                const list = document.createElement('div');
                list.className = 'space-y-2';

                section.subjects.forEach((subjectEntry) => {
                    const subject = subjectMap.get(String(subjectEntry.id));
                    if (!subject) {
                        return;
                    }

                    const requiredHours = subject.required_hours || 0;
                    const scheduledHours = calculateScheduledHours(subject.id, section.id, termId);
                    const remainingHours = Math.max(0, requiredHours - scheduledHours);

                    const card = document.createElement('div');
                    card.className = 'subject-card subject-draggable touch-none p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm cursor-grab';
                    // We use a custom pointer-based drag system for consistent behavior across browsers.
                    card.draggable = false;
                    card.dataset.subjectId = String(subject.id);
                    card.dataset.sectionId = String(section.id);
                    card.dataset.termId = String(termId);

                    const title = document.createElement('div');
                    title.className = 'font-semibold text-gray-900 dark:text-white text-sm';
                    title.textContent = subject.code ? `${subject.code} - ${subject.name}` : subject.name;

                    const meta = document.createElement('div');
                    meta.className = 'text-xs text-gray-600 dark:text-gray-400 mt-1';
                    meta.innerHTML = `
                        <div>${remainingHours.toFixed(1)} hour(s) remaining</div>
                        <div>${subject.subject_type || ''}</div>
                    `;

                    const assignment = getAssignedScheduleForSubject(subject.id, section.id, termId);
                    const assignmentMeta = document.createElement('div');
                    assignmentMeta.className = 'subject-meta mt-1';
                    assignmentMeta.textContent = '';
                    assignmentMeta.classList.add('hidden');
                    if (assignment) {
                        const roomName = roomsMap.get(String(assignment.room_id))?.name || '';
                        const dayLabel = capitalizeLabel(assignment.day);
                        const timeLabel = `${formatTimeLabel(assignment.time_start)} - ${formatTimeLabel(assignment.time_end)}`;
                        assignmentMeta.textContent = [roomName, dayLabel, timeLabel].filter(Boolean).join(' · ');
                        assignmentMeta.classList.remove('hidden');
                    }

                    if (remainingHours <= 0) {
                        card.classList.add('opacity-50');
                        const done = document.createElement('div');
                        done.className = 'text-[11px] font-semibold text-emerald-700 dark:text-emerald-200 mt-2';
                        done.textContent = 'Completed';
                        meta.appendChild(done);
                        // Allow pointer-based dragging even if completed.
                        card.draggable = false;
                    }

                    card.appendChild(title);
                    card.appendChild(meta);
                    card.appendChild(assignmentMeta);

                    list.appendChild(card);
                });

                sectionBlock.appendChild(list);
            }

            sectionSubjectsContainer.appendChild(sectionBlock);
        });

        // Ensure drag handlers are attached to newly rendered subject cards.
        initSubjectDragHandlers(sectionSubjectsContainer);
        hydrateSidebarAssignments();
    }

    function updatePreview(draft) {
        if (!draft) {
            previewSubject.textContent = 'No confirmed card yet';
            previewTeacher.textContent = '';
            previewSection.textContent = '';
            previewRoom.textContent = '';
            previewDuration.textContent = '';
            return;
        }

        const subjectText = subjectSelect.options[subjectSelect.selectedIndex]?.text || '';
        const teacherText = teacherSelect.options[teacherSelect.selectedIndex]?.text || '';
        const sectionText = sectionSelect.options[sectionSelect.selectedIndex]?.text || '';
        const roomText = roomSelect.options[roomSelect.selectedIndex]?.text || '';
        const duration = parseFloat(draft.duration || '1');

        previewSubject.textContent = subjectText || 'No subject';
        previewTeacher.textContent = teacherText;
        previewSection.textContent = sectionText;
        previewRoom.textContent = roomText;
        previewDuration.textContent = `Duration: ${duration} hour(s)`;
    }

    function buildDraftFromForm(showAlerts = true) {
        const curriculumId = curriculumSelect ? curriculumSelect.value : '';
        const termId = termInput.value || termFilter?.value;
        const subjectId = subjectSelect.value;
        const roomId = roomSelect.value;
        const sectionId = sectionSelect.value;
        const teacherId = teacherSelect.value;
        const duration = parseFloat(durationInput.value || '1');

        if ((curriculumSelect && !curriculumId) || !termId || !subjectId || !roomId || !sectionId) {
            if (showAlerts) {
                alert('Complete curriculum, subject, room, section, and duration first.');
            }
            return null;
        }

        return {
            curriculum_id: curriculumId || null,
            term_id: termId,
            subject_id: subjectId,
            room_id: roomId,
            section_id: sectionId,
            teacher_id: teacherId || null,
            duration: duration
        };
    }

    function setClickPlaceMode(enabled) {
        clickPlaceMode = enabled;
        clickPlaceToggle.textContent = `Click-to-place: ${enabled ? 'ON' : 'OFF'}`;
        clickPlaceToggle.classList.toggle('bg-indigo-600', enabled);
        clickPlaceToggle.classList.toggle('hover:bg-indigo-700', enabled);
        clickPlaceToggle.classList.toggle('bg-gray-600', !enabled);
        clickPlaceToggle.classList.toggle('hover:bg-gray-700', !enabled);
    }

    function resetFormFields() {
        ['teacher_id', 'subject_id', 'section_id', 'room_id'].forEach((id) => {
            document.getElementById(id).value = '';
        });
        durationInput.value = '1';
        teacherSelectionConfirmed = false;
        if (curriculumSelect && curriculumSelect.value) {
            handleCurriculumChange();
        } else {
            clearCurriculumDisplay();
            populateSubjects('');
            populateRooms('');
            populateSections('');
            populateTeachers('');
        }
        markCardAsPending();
    }

    function applyDraftToForm(draft) {
        if (!draft) return;
        if (curriculumSelect) {
            curriculumSelect.value = draft.curriculum_id || '';
            handleCurriculumChange(draft.subject_id, draft.section_id, draft.room_id, draft.teacher_id);
        }
        teacherSelect.value = draft.teacher_id || teacherSelect.value;
        // Programmatic assignment should not count as explicit selection.
        teacherSelectionConfirmed = false;
        if (draft.duration) {
            durationInput.value = draft.duration;
        }
        markCardAsPending();
    }

    function clearDragShadow() {
        if (!dragShadowEl) return;
        dragShadowEl.remove();
        dragShadowEl = null;
    }

    // Custom subject drag system using pointer events (works in Edge and avoids native HTML5 drag limitations)
    function handleSubjectPointerDown(e) {
        const card = e.target.closest('.subject-draggable');
        if (!card || (e.button !== undefined && e.button !== 0)) return;

        // Prevent text selection and native drag behaviors.
        e.preventDefault();
        e.stopPropagation();

        const point = (e.touches && e.touches[0]) || e;
        const pageX = point.pageX;
        const pageY = point.pageY;
        const clientX = point.clientX;
        const clientY = point.clientY;

        lastPointerX = clientX;
        lastPointerY = clientY;

        const subjectId = card.dataset.subjectId;
        const sectionId = card.dataset.sectionId;
        const termId = card.dataset.termId || termInput.value || termFilter?.value;
        if (!subjectId || !sectionId || !termId) return;

        const defaultRoomId = getDefaultRoomId(subjectId);
        const defaultTeacherId = getDefaultTeacherId(subjectId);
        const duration = parseFloat(durationInput.value) || 1;

        draggedData = {
            subject_id: subjectId,
            section_id: sectionId,
            term_id: termId,
            room_id: defaultRoomId,
            teacher_id: defaultTeacherId,
            duration: duration
        };

        renderConflictHeatmap(draggedData);

        subjectDragActive = true;
        subjectDragCard = card;
        subjectDragPointerId = e.pointerId || null;

        console.debug('subject pointer drag start', { subjectId, sectionId, termId, pointerId: subjectDragPointerId });

        // Visual feedback while dragging
        card.style.opacity = '0.5';

        pointerDragging = true;
        pointerDragCell = null;
        document.body.style.userSelect = 'none';

        pointerDragGhost = createDragGhost(card.textContent || 'Dragging');
        pointerDragGhost.style.left = `${pageX + 10}px`;
        pointerDragGhost.style.top = `${pageY + 10}px`;

        // Ensure we keep receiving pointer events even if the pointer leaves the card
        if (subjectDragPointerId !== null && card.setPointerCapture) {
            card.setPointerCapture(subjectDragPointerId);
        }

        document.addEventListener('pointermove', handleSubjectPointerMove, { passive: false });
        document.addEventListener('pointerup', handleSubjectPointerUp);
        document.addEventListener('pointercancel', handleSubjectPointerCancel);

        // Fallbacks for browsers that don't support pointer events
        document.addEventListener('touchmove', handleSubjectPointerMove, { passive: false });
        document.addEventListener('touchend', handleSubjectPointerUp);
        document.addEventListener('touchcancel', handleSubjectPointerCancel);
        document.addEventListener('mousemove', handleSubjectPointerMove, { passive: false });
        document.addEventListener('mouseup', handleSubjectPointerUp);
    }

    function handleSubjectPointerMove(e) {
        if (!subjectDragActive) return;
        e.preventDefault();

        pointerDragGhost.style.left = `${e.pageX + 10}px`;
        pointerDragGhost.style.top = `${e.pageY + 10}px`;

        const cell = document.elementFromPoint(e.clientX, e.clientY)?.closest('.drop-zone');
        if (!cell) {
            if (pointerDragCell) {
                pointerDragCell.classList.remove('ring-2', 'ring-indigo-500');
                pointerDragCell = null;
                clearDragShadow();
            }
            return;
        }

        if (pointerDragCell && pointerDragCell !== cell) {
            pointerDragCell.classList.remove('ring-2', 'ring-indigo-500');
        }

        if (pointerDragCell !== cell) {
            console.debug('pointer drag over cell', { day: cell.dataset.day, timeStart: cell.dataset.timeStart });
        }

        pointerDragCell = cell;
        pointerDragCell.classList.add('ring-2', 'ring-indigo-500');
        showDragShadow(cell, draggedData);
    }

    async function handleSubjectPointerUp() {
        console.debug('subject pointer drag end', { pointerDragCell });

        if (!pointerDragCell) {
            const potentialCell = document.elementFromPoint(lastPointerX, lastPointerY)?.closest('.drop-zone');
            if (potentialCell) {
                pointerDragCell = potentialCell;
                pointerDragCell.classList.add('ring-2', 'ring-indigo-500');
            }
        }

        if (pointerDragCell) {
            pointerDragCell.classList.remove('ring-2', 'ring-indigo-500');
            await placeScheduleInCell(pointerDragCell, draggedData);
        }

        if (subjectDragCard) {
            subjectDragCard.style.opacity = '1';
        }

        if (subjectDragCard && subjectDragCard.releasePointerCapture) {
            subjectDragCard.releasePointerCapture(subjectDragPointerId);
        }

        clearPointerDrag();
        document.body.style.userSelect = '';
        subjectDragActive = false;
        subjectDragCard = null;
        subjectDragPointerId = null;

        document.removeEventListener('pointermove', handleSubjectPointerMove);
        document.removeEventListener('pointerup', handleSubjectPointerUp);
        document.removeEventListener('pointercancel', handleSubjectPointerCancel);
        document.removeEventListener('touchmove', handleSubjectPointerMove);
        document.removeEventListener('touchend', handleSubjectPointerUp);
        document.removeEventListener('touchcancel', handleSubjectPointerCancel);
        document.removeEventListener('mousemove', handleSubjectPointerMove);
        document.removeEventListener('mouseup', handleSubjectPointerUp);
    }

    function initSubjectDragHandlers(root = document) {
        root.querySelectorAll('.subject-draggable').forEach((card) => {
            if (card._hasSubjectDragHandlers) return;
            card._hasSubjectDragHandlers = true;
            card.addEventListener('pointerdown', handleSubjectPointerDown, { passive: false });
            card.addEventListener('mousedown', handleSubjectPointerDown, { passive: false });
            card.addEventListener('touchstart', handleSubjectPointerDown, { passive: false });
        });
    }

    function handleSubjectPointerCancel() {
        if (subjectDragCard && subjectDragCard.releasePointerCapture) {
            subjectDragCard.releasePointerCapture(subjectDragPointerId);
        }

        clearPointerDrag();
        document.body.style.userSelect = '';
        subjectDragActive = false;
        subjectDragCard = null;
        subjectDragPointerId = null;

        document.removeEventListener('pointermove', handleSubjectPointerMove);
        document.removeEventListener('pointerup', handleSubjectPointerUp);
        document.removeEventListener('pointercancel', handleSubjectPointerCancel);
        document.removeEventListener('touchmove', handleSubjectPointerMove);
        document.removeEventListener('touchend', handleSubjectPointerUp);
        document.removeEventListener('touchcancel', handleSubjectPointerCancel);
        document.removeEventListener('mousemove', handleSubjectPointerMove);
        document.removeEventListener('mouseup', handleSubjectPointerUp);
    }

    function clearPointerDrag() {
        if (!pointerDragging) return;
        pointerDragging = false;
        pointerDragCell = null;
        draggedData = null;
        if (pointerDragGhost) {
            pointerDragGhost.remove();
            pointerDragGhost = null;
        }
        clearConflictHeatmap();
        clearDragShadow();
    }

    function createDragGhost(label) {
        const ghost = document.createElement('div');
        ghost.className = 'pointer-drag-ghost fixed z-50 pointer-events-none rounded bg-white/90 border border-gray-300 shadow px-3 py-2 text-sm text-gray-900';
        ghost.textContent = label;
        document.body.appendChild(ghost);
        return ghost;
    }

    function parseMinutes(time) {
        const [hours, minutes] = String(time).split(':').map(Number);
        return (hours * 60) + minutes;
    }

    function formatMinutes(totalMinutes) {
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
    }

    function normalizeTimeValue(time) {
        if (!time) return '';
        const value = String(time);
        return value.length >= 5 ? value.slice(0, 5) : value;
    }

    function formatTimeLabel(time) {
        const value = normalizeTimeValue(time);
        if (!value) return '';
        const [hoursRaw, minutesRaw] = value.split(':').map(Number);
        const period = hoursRaw >= 12 ? 'PM' : 'AM';
        const hours12 = ((hoursRaw + 11) % 12) + 1;
        return `${hours12}:${String(minutesRaw || 0).padStart(2, '0')} ${period}`;
    }

    function capitalizeLabel(value) {
        if (!value) return '';
        const text = String(value);
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    function getScheduleColorClass(subjectId) {
        if (!colorClasses || !colorClasses.length) {
            return 'bg-indigo-200';
        }
        const index = subjectsData.findIndex((subject) => String(subject.id) === String(subjectId));
        if (index < 0) return colorClasses[0];
        return colorClasses[index % colorClasses.length];
    }

    function getSlotCountFromTimes(startTime, endTime) {
        const duration = parseMinutes(endTime) - parseMinutes(startTime);
        return Math.max(1, Math.round(duration / 60));
    }

    function normalizeSchedulePayload(schedule) {
        if (!schedule) return null;
        const normalized = { ...schedule };
        normalized.id = schedule.id ?? schedule.schedule_id;
        normalized.subject_id = schedule.subject_id ?? schedule.subject?.id;
        normalized.section_id = schedule.section_id ?? schedule.section?.id;
        normalized.room_id = schedule.room_id ?? schedule.room?.id;
        normalized.teacher_id = schedule.teacher_id ?? schedule.teacher?.id ?? null;
        normalized.term_id = schedule.term_id ?? schedule.term?.id ?? null;
        normalized.day = String(schedule.day || '').toLowerCase();
        normalized.time_start = normalizeTimeValue(schedule.time_start);
        normalized.time_end = normalizeTimeValue(schedule.time_end);
        normalized.is_published = !!schedule.is_published;
        return normalized;
    }

    function findSubjectListItems(subjectId, sectionId) {
        if (!subjectId || !sectionId) return [];
        return Array.from(document.querySelectorAll(`.subject-draggable[data-subject-id="${subjectId}"][data-section-id="${sectionId}"]`));
    }

    function updateSidebarAssignment(schedule, options = {}) {
        const normalized = normalizeSchedulePayload(schedule);
        if (!normalized) return;
        const targets = findSubjectListItems(normalized.subject_id, normalized.section_id);
        if (!targets.length) return;

        targets.forEach((target) => {
            const meta = target.querySelector('.subject-meta');
            if (!meta) return;

            if (options.clear) {
                meta.textContent = '';
                meta.classList.add('hidden');
                return;
            }

            const roomName = roomsMap.get(String(normalized.room_id))?.name || '';
            const dayLabel = capitalizeLabel(normalized.day);
            const timeLabel = normalized.time_start && normalized.time_end
                ? `${formatTimeLabel(normalized.time_start)} - ${formatTimeLabel(normalized.time_end)}`
                : '';
            const parts = [roomName, dayLabel, timeLabel].filter(Boolean);

            meta.textContent = parts.join(' · ');
            meta.classList.remove('hidden');
        });
    }

    function hydrateSidebarAssignments() {
        schedulesData.forEach((schedule) => {
            updateSidebarAssignment(schedule);
        });
    }

    function getAssignedScheduleForSubject(subjectId, sectionId, termId) {
        const matches = schedulesData.filter((schedule) =>
            String(schedule.subject_id) === String(subjectId) &&
            String(schedule.section_id) === String(sectionId) &&
            String(schedule.term_id) === String(termId)
        );
        if (!matches.length) return null;
        return matches[matches.length - 1];
    }

    function collectDraftSchedules() {
        const roomViewItems = Array.from(document.querySelectorAll('#view-room .schedule-item'));
        if (roomViewItems.length) {
            return roomViewItems.map((item) => ({
                teacher_id: item.dataset.teacherId || null,
                subject_id: item.dataset.subjectId,
                section_id: item.dataset.sectionId,
                room_id: item.dataset.roomId,
                term_id: item.dataset.termId,
                day: item.dataset.day,
                time_start: item.dataset.timeStart,
                time_end: item.dataset.timeEnd
            }));
        }

        return schedulesData.map((schedule) => ({
            teacher_id: schedule.teacher_id || null,
            subject_id: schedule.subject_id,
            section_id: schedule.section_id,
            room_id: schedule.room_id,
            term_id: schedule.term_id,
            day: schedule.day,
            time_start: schedule.time_start,
            time_end: schedule.time_end
        }));
    }

    function getLocalConflicts(candidate, excludeId = null) {
        const conflicts = {};
        conflictSchedulePool.forEach((item) => {
            if (excludeId && String(item.schedule_id) === String(excludeId)) {
                return;
            }
            if (item.day !== candidate.day) {
                return;
            }
            if (!overlaps(candidate.time_start, candidate.time_end, item.time_start, item.time_end)) {
                return;
            }

            if (String(item.room_id) === String(candidate.room_id)) {
                conflicts.room = 'Room is already booked at this time.';
            }
            if (String(item.section_id) === String(candidate.section_id)) {
                conflicts.section = 'Section already has a class at this time.';
            }
            if (candidate.teacher_id && item.teacher_id && String(item.teacher_id) === String(candidate.teacher_id)) {
                conflicts.teacher = 'Teacher is already scheduled at this time.';
            }
        });
        return conflicts;
    }

    function upsertScheduleCache(schedule) {
        const normalized = normalizeSchedulePayload(schedule);
        if (!normalized) return;

        const index = schedulesData.findIndex((item) => String(item.id) === String(normalized.id));
        if (index >= 0) {
            schedulesData[index] = {
                id: normalized.id,
                teacher_id: normalized.teacher_id,
                subject_id: normalized.subject_id,
                section_id: normalized.section_id,
                room_id: normalized.room_id,
                term_id: normalized.term_id,
                day: normalized.day,
                time_start: normalized.time_start,
                time_end: normalized.time_end,
                is_published: normalized.is_published
            };
        } else {
            schedulesData.push({
                id: normalized.id,
                teacher_id: normalized.teacher_id,
                subject_id: normalized.subject_id,
                section_id: normalized.section_id,
                room_id: normalized.room_id,
                term_id: normalized.term_id,
                day: normalized.day,
                time_start: normalized.time_start,
                time_end: normalized.time_end,
                is_published: normalized.is_published
            });
        }

        const conflictIndex = conflictSchedulePool.findIndex((item) => String(item.schedule_id) === String(normalized.id));
        const conflictEntry = {
            schedule_id: normalized.id,
            teacher_id: normalized.teacher_id,
            section_id: normalized.section_id,
            room_id: normalized.room_id,
            day: normalized.day,
            time_start: normalized.time_start,
            time_end: normalized.time_end
        };
        if (conflictIndex >= 0) {
            conflictSchedulePool[conflictIndex] = conflictEntry;
        } else {
            conflictSchedulePool.push(conflictEntry);
        }
    }

    function removeScheduleCache(scheduleId) {
        const index = schedulesData.findIndex((item) => String(item.id) === String(scheduleId));
        if (index >= 0) {
            schedulesData.splice(index, 1);
        }
        const conflictIndex = conflictSchedulePool.findIndex((item) => String(item.schedule_id) === String(scheduleId));
        if (conflictIndex >= 0) {
            conflictSchedulePool.splice(conflictIndex, 1);
        }
    }

    function removeScheduleItemInstances(scheduleId) {
        document.querySelectorAll(`.schedule-item[data-schedule-id="${scheduleId}"]`).forEach((item) => {
            item.remove();
        });
    }

    function buildScheduleItemElement(schedule, slotCount) {
        const subject = schedule.subject || subjectMap.get(String(schedule.subject_id));
        const section = schedule.section || sectionsMap.get(String(schedule.section_id));
        const room = schedule.room || roomsMap.get(String(schedule.room_id));
        const subjectName = subject?.name || 'Subject';
        const sectionName = section?.name || '';
        const roomName = room?.name || '';
        const colorClass = getScheduleColorClass(schedule.subject_id);
        const sizeClass = slotCount <= 1 ? 'size-xs' : (slotCount <= 2 ? 'size-sm' : 'size-md');

        const wrapper = document.createElement('div');
        wrapper.className = `schedule-item absolute inset-0 m-0.5 ${colorClass} rounded border border-gray-400 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow`;
        wrapper.dataset.scheduleId = schedule.id;
        wrapper.dataset.teacherId = schedule.teacher_id || '';
        wrapper.dataset.subjectId = schedule.subject_id;
        wrapper.dataset.sectionId = schedule.section_id;
        wrapper.dataset.roomId = schedule.room_id;
        wrapper.dataset.termId = schedule.term_id;
        wrapper.dataset.day = schedule.day;
        wrapper.dataset.timeStart = schedule.time_start;
        wrapper.dataset.timeEnd = schedule.time_end;
        wrapper.dataset.isPublished = schedule.is_published ? '1' : '0';
        wrapper.dataset.slotCount = String(slotCount);
        wrapper.style.height = `calc(${slotCount} * var(--timetable-slot-height) - 4px)`;
        wrapper.style.zIndex = '10';
        wrapper.draggable = true;

        const badge = schedule.is_published
            ? `<div class="mb-1"><span class="inline-flex items-center rounded bg-emerald-600 px-1.5 py-0.5 text-[9px] font-semibold text-white">PUBLISHED</span></div>`
            : '';

        wrapper.innerHTML = `
            <button type="button" class="delete-schedule-btn absolute top-1 right-1 h-5 w-5 rounded-full bg-red-600 hover:bg-red-700 text-white text-[10px] leading-none flex items-center justify-center shadow"
                data-schedule-id="${schedule.id}" title="Remove schedule block" aria-label="Remove schedule block">
                &times;
            </button>
            <div class="schedule-body ${sizeClass} h-full flex flex-col">
                ${badge}
                <div class="subject-line font-semibold text-gray-900 dark:text-white" title="${subjectName}">
                    ${subjectName.length > 32 ? `${subjectName.slice(0, 32)}...` : subjectName}
                </div>
                <div class="meta-line text-gray-700 dark:text-gray-300">${sectionName}</div>
                <div class="meta-line text-gray-600 dark:text-gray-400">${roomName}</div>
            </div>
        `;

        return wrapper;
    }

    function updateScheduleBodySize(scheduleItem, slotCount) {
        const body = scheduleItem.querySelector('.schedule-body');
        if (!body) return;
        body.classList.remove('size-xs', 'size-sm', 'size-md');
        const sizeClass = slotCount <= 1 ? 'size-xs' : (slotCount <= 2 ? 'size-sm' : 'size-md');
        body.classList.add(sizeClass);
    }

    const scheduleResizeObserver = window.ResizeObserver ? new ResizeObserver((entries) => {
        entries.forEach((entry) => {
            const item = entry.target;
            if (!item || !item.classList.contains('schedule-item')) return;
            const height = entry.contentRect?.height || item.getBoundingClientRect().height;
            const slots = getSlotCountFromHeight(height);
            item.dataset.slotCount = String(slots);
            updateScheduleBodySize(item, slots);
        });
    }) : null;

    function observeScheduleItem(item) {
        if (!scheduleResizeObserver || item._resizeObserved) return;
        scheduleResizeObserver.observe(item);
        item._resizeObserved = true;
    }

    function findTargetCell(schedule, viewType, overrideCell = null) {
        if (overrideCell && viewType === 'room') {
            return overrideCell;
        }

        if (viewType === 'room') {
            return document.querySelector(`#view-room .drop-zone[data-room-id="${schedule.room_id}"][data-day="${schedule.day}"][data-time-start="${schedule.time_start}"]`);
        }

        if (viewType === 'teacher') {
            if (!schedule.teacher_id) return null;
            return document.querySelector(`#view-teacher .drop-zone[data-teacher-id="${schedule.teacher_id}"][data-day="${schedule.day}"][data-time-start="${schedule.time_start}"]`);
        }

        if (viewType === 'section') {
            return document.querySelector(`#view-section .drop-zone[data-section-id="${schedule.section_id}"][data-day="${schedule.day}"][data-time-start="${schedule.time_start}"]`);
        }

        return null;
    }

    function insertScheduleIntoView(viewType, schedule, overrideCell = null) {
        const cell = findTargetCell(schedule, viewType, overrideCell);
        if (!cell) return;
        const slotCount = schedule.slot_count
            ? Math.max(1, parseInt(schedule.slot_count, 10))
            : getSlotCountFromTimes(schedule.time_start, schedule.time_end);
        if (schedule.slot_count && schedule.time_start) {
            schedule.time_end = getEndTimeFromStartAndSlots(schedule.time_start, slotCount);
        }
        const scheduleEl = buildScheduleItemElement(schedule, slotCount);
        cell.appendChild(scheduleEl);
        observeScheduleItem(scheduleEl);
    }

    function getCurrentScheduleSnapshots() {
        const roomItems = Array.from(document.querySelectorAll('#view-room .schedule-item'));
        if (roomItems.length) {
            return roomItems.map((item) => ({
                id: item.dataset.scheduleId,
                teacher_id: item.dataset.teacherId || null,
                subject_id: item.dataset.subjectId,
                section_id: item.dataset.sectionId,
                room_id: item.dataset.roomId,
                term_id: item.dataset.termId,
                day: item.dataset.day,
                time_start: item.dataset.timeStart,
                time_end: item.dataset.timeEnd,
                slot_count: item.dataset.slotCount,
                is_published: item.dataset.isPublished === '1'
            }));
        }
        return schedulesData.map((item) => ({
            id: item.id,
            teacher_id: item.teacher_id || null,
            subject_id: item.subject_id,
            section_id: item.section_id,
            room_id: item.room_id,
            term_id: item.term_id,
            day: item.day,
            time_start: item.time_start,
            time_end: item.time_end,
            is_published: item.is_published
        }));
    }

    function refreshSectionView() {
        document.querySelectorAll('#view-section .schedule-item').forEach((item) => item.remove());
        getCurrentScheduleSnapshots().forEach((schedule) => {
            insertScheduleIntoView('section', schedule);
        });
        ensureResizeHandles();
    }

    function refreshTeacherView() {
        document.querySelectorAll('#view-teacher .schedule-item').forEach((item) => item.remove());
        getCurrentScheduleSnapshots().forEach((schedule) => {
            if (schedule.teacher_id) {
                insertScheduleIntoView('teacher', schedule);
            }
        });
        ensureResizeHandles();
    }

    function applyDraftSchedulesToView(draftSchedules) {
        if (!draftSchedules.length) return;
        document.querySelectorAll('.schedule-item').forEach((item) => item.remove());
        schedulesData.length = 0;
        conflictSchedulePool.length = 0;

        draftSchedules.forEach((draft) => {
            const normalized = normalizeSchedulePayload(draft);
            if (!normalized) return;
            upsertScheduleCache(normalized);
            insertScheduleIntoView('room', normalized);
        });

        refreshSectionView();
        refreshTeacherView();
        ensureResizeHandles();
        hydrateSidebarAssignments();
    }

    function applyScheduleUpdate(schedule, overrideCell = null) {
        const normalized = normalizeSchedulePayload(schedule);
        if (!normalized) return;

        upsertScheduleCache(normalized);
        removeScheduleItemInstances(normalized.id);

        const roomOverride = overrideCell && overrideCell.dataset && overrideCell.dataset.roomId ? overrideCell : null;
        insertScheduleIntoView('room', normalized, roomOverride);
        insertScheduleIntoView('teacher', normalized);
        insertScheduleIntoView('section', normalized);

        updateSidebarAssignment(normalized);
        ensureResizeHandles();
        saveDraftState();
    }

    function overlaps(startA, endA, startB, endB) {
        return parseMinutes(startA) < parseMinutes(endB) && parseMinutes(endA) > parseMinutes(startB);
    }

    function clearConflictHeatmap() {
        document.querySelectorAll('.drop-zone.drop-zone-conflict').forEach((cell) => {
            cell.classList.remove('drop-zone-conflict');
        });
    }

    function renderConflictHeatmap(placementData) {
        clearConflictHeatmap();
        if (!placementData) return;

        const slotCount = getPlacementSlotCount(placementData);
        const durationMinutes = slotCount * 60;

        document.querySelectorAll('.drop-zone').forEach((cell) => {
            const day = cell.dataset.day;
            const start = cell.dataset.timeStart;
            const end = formatMinutes(parseMinutes(start) + durationMinutes);

            const isConflict = conflictSchedulePool.some((item) => {
                if (placementData.schedule_id && String(item.schedule_id) === String(placementData.schedule_id)) {
                    return false;
                }
                if (item.day !== day) {
                    return false;
                }
                if (!overlaps(start, end, item.time_start, item.time_end)) {
                    return false;
                }

                return (
                    (placementData.teacher_id && item.teacher_id && String(item.teacher_id) === String(placementData.teacher_id)) ||
                    String(item.section_id) === String(placementData.section_id) ||
                    String(item.room_id) === String(placementData.room_id)
                );
            });

            if (isConflict) {
                cell.classList.add('drop-zone-conflict');
            }
        });
    }

    function getPlacementSlotCount(placementData) {
        if (placementData.schedule_id && placementData.time_start && placementData.time_end) {
            const [startHours, startMinutes] = placementData.time_start.split(':').map(Number);
            const [endHours, endMinutes] = placementData.time_end.split(':').map(Number);
            const durationMinutes = (endHours * 60 + endMinutes) - (startHours * 60 + startMinutes);
            return Math.max(1, Math.round(durationMinutes / 60));
        }

        const duration = parseFloat(placementData.duration || '1');
        return Math.max(1, Math.round(duration));
    }

    function showDragShadow(cell, placementData) {
        if (!cell || !placementData) return;
        clearDragShadow();

        const slotCount = getPlacementSlotCount(placementData);
        const shadow = document.createElement('div');
        // Match the real schedule item footprint for exact visual alignment while dragging.
        shadow.className = 'absolute inset-0 m-0.5 rounded border border-indigo-400 bg-indigo-200/45 dark:bg-indigo-700/35 pointer-events-none';
        shadow.style.height = `calc(${slotCount} * ${SLOT_HEIGHT}px - 4px)`;
        shadow.style.zIndex = '10';
        shadow.style.boxShadow = '0 10px 18px rgba(79, 70, 229, 0.25)';
        cell.appendChild(shadow);
        dragShadowEl = shadow;
    }

    populateCurricula();
    const savedCurriculumId = localStorage.getItem(storageKeyCurriculum);
    if (curriculumSelect) {
        if (savedCurriculumId && curriculumMap.has(savedCurriculumId)) {
            curriculumSelect.value = savedCurriculumId;
            handleCurriculumChange();
        } else {
            clearCurriculumDisplay();
        }
    } else {
        clearCurriculumDisplay();
    }

    // Ensure sections list is populated on initial load based on selected term
    populateSections(termFilter?.value || termInput.value);

    markCardAsPending();
    setClickPlaceMode(false);
    initSubjectDragHandlers();
    hydrateSidebarAssignments();
    console.debug('initSubjectDragHandlers attached to', document.querySelectorAll('.subject-draggable').length, 'items');

    const savedView = localStorage.getItem(storageKeyView);
    setTimetableView(savedView || 'room');
    timetableTabs.forEach((tab) => {
        tab.addEventListener('click', () => setTimetableView(tab.dataset.view));
    });

    const draftSchedules = loadDraftState();
    if (draftSchedules.length) {
        applyDraftSchedulesToView(draftSchedules);
    }

    if (curriculumSelect) {
        curriculumSelect.addEventListener('change', () => {
            if (curriculumSelect.value) {
                localStorage.setItem(storageKeyCurriculum, curriculumSelect.value);
            } else {
                localStorage.removeItem(storageKeyCurriculum);
            }
            handleCurriculumChange();
            markCardAsPending();
        });
    }

    subjectSelect.addEventListener('change', () => {
        populateRooms(subjectSelect.value);
        populateTeachers(subjectSelect.value);
        roomSelect.value = '';
        teacherSelect.value = '';
        markCardAsPending();
    });

    sectionSelect.addEventListener('change', () => {
        renderSectionSubjects();
        markCardAsPending();
    });

    sectionFilter.addEventListener('change', () => {
        sectionSelect.value = sectionFilter.value;
        renderSectionSubjects();
        markCardAsPending();
    });

    ['room_id', 'section_id', 'duration'].forEach((id) => {
        document.getElementById(id).addEventListener('change', markCardAsPending);
    });

    teacherSelect.addEventListener('change', () => {
        teacherSelectionConfirmed = teacherSelect.value !== '';
        markCardAsPending();
    });

    // Quick duration presets
    document.querySelectorAll('.duration-preset').forEach((button) => {
        button.addEventListener('click', () => {
            durationInput.value = button.dataset.duration;
            markCardAsPending();
        });
    });

    confirmScheduleCardBtn.addEventListener('click', () => {
        const draft = buildDraftFromForm(true);
        if (!draft) {
            return;
        }
        confirmedDraft = draft;
        updatePreview(confirmedDraft);
        setCardReady();
    });

    saveScheduleBtn?.addEventListener('click', async () => {
        const termId = termInput.value || termFilter?.value;
        const curriculumId = curriculumSelect ? (curriculumSelect.value || null) : null;

        const draftSchedules = collectDraftSchedules();
        if (!draftSchedules.length) {
            alert('No schedules to save yet.');
            return;
        }
        const resolvedTermId = termId || draftSchedules[0]?.term_id;

        try {
            saveScheduleBtn.disabled = true;
            saveScheduleBtn.textContent = 'Saving...';

            const response = await fetch(endpoints.saveDraft, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    term_id: resolvedTermId,
                    curriculum_id: curriculumId,
                    schedule_name: initialScheduleName || null,
                    selected_rooms: initialSelectedRooms,
                    draft_schedules: draftSchedules
                })
            });

            const result = await response.json();
            if (!response.ok || result.success === false) {
                const message = result.message || 'Failed to save schedule.';
                alert(message);
                return;
            }

            alert(result.message || 'Schedule saved successfully.');
            localStorage.removeItem(getDraftStorageKey());
            window.location.href = '{{ route("admin.schedules.index") }}';
        } catch (error) {
            alert('Unable to save schedule: ' + (error.message || 'Unknown error'));
        } finally {
            saveScheduleBtn.disabled = false;
            saveScheduleBtn.textContent = 'Save Schedule';
        }
    });

    publishWeekBtn.addEventListener('click', async () => {
        const termId = termInput.value || termFilter?.value;
        const curriculumId = curriculumSelect ? (curriculumSelect.value || null) : null;
        const draftSchedules = collectDraftSchedules();
        const resolvedTermId = termId || draftSchedules[0]?.term_id;

        if (!autosaveEnabled && draftSchedules.length === 0) {
            alert('No schedules to publish yet.');
            return;
        }

        const confirmed = confirm('Publish this week timetable as official?');
        if (!confirmed) {
            return;
        }

        try {
            publishWeekBtn.disabled = true;
            publishWeekBtn.textContent = 'Publishing...';

            const response = await fetch(endpoints.publishWeek, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(autosaveEnabled ? {
                    term_id: resolvedTermId,
                    curriculum_id: curriculumId
                } : {
                    term_id: resolvedTermId,
                    curriculum_id: curriculumId,
                    schedule_name: initialScheduleName || null,
                    selected_rooms: initialSelectedRooms,
                    draft_schedules: draftSchedules
                })
            });

            const result = await response.json();

            if (!response.ok || result.success === false) {
                const errorMessage = result.message || 'Failed to publish week timetable.';
                alert(errorMessage);
                return;
            }

            alert(result.message || 'Week timetable published successfully.');
            localStorage.removeItem(getDraftStorageKey());
            window.location.reload();
        } catch (error) {
            alert('Unable to publish timetable: ' + (error.message || 'Unknown error'));
        } finally {
            publishWeekBtn.disabled = false;
            publishWeekBtn.textContent = 'Publish Week';
        }
    });

    clickPlaceToggle.addEventListener('click', () => {
        setClickPlaceMode(!clickPlaceMode);
    });

    clearFormBtn.addEventListener('click', () => {
        resetFormFields();
    });

    reuseLastBtn.addEventListener('click', () => {
        applyDraftToForm(lastScheduleDraft);
    });

    // Term filter (only active if termFilter element exists)
    if (termFilter) {
        termFilter.addEventListener('change', () => {
            const params = new URLSearchParams();
            if (termFilter.value) params.set('term_id', termFilter.value);
            if (initialScheduleName) params.set('schedule_name', initialScheduleName);
            if (initialSelectedRooms.length) {
                initialSelectedRooms.forEach((roomId) => params.append('rooms[]', roomId));
            }
            window.location.href = `${endpoints.timetable}?${params.toString()}`;
        });
    }

    // Drag from form
    draggableItem.addEventListener('dragstart', (e) => {
        if (!confirmedDraft) {
            alert('Click "Add To Card" first before dragging.');
            e.preventDefault();
            return;
        }
        draggedData = { ...confirmedDraft };
        renderConflictHeatmap(draggedData);

        try {
            const payload = JSON.stringify(draggedData);
            e.dataTransfer.setData('text/plain', payload);
            e.dataTransfer.setData('application/json', payload);
        } catch (err) {
            // Some browsers restrict setData in dragstart; ignore.
        }

        e.dataTransfer.effectAllowed = 'move';
        draggableItem.style.opacity = '0.5';
    });

    draggableItem.addEventListener('dragend', () => {
        draggableItem.style.opacity = '1';
        clearDragShadow();
        clearConflictHeatmap();
    });


    // Drag existing schedule (or subject cards) - use event delegation for dynamically loaded items
    document.addEventListener('dragstart', (e) => {
        if (e.target.closest('.delete-schedule-btn')) {
            e.preventDefault();
            return;
        }

        const scheduleItem = e.target.closest('.schedule-item');
        if (scheduleItem) {
            console.debug('dragstart on schedule item', { scheduleId: scheduleItem.dataset.scheduleId });
            // Validate required data attributes
            if (!scheduleItem.dataset.scheduleId || !scheduleItem.dataset.subjectId) {
                console.error('Missing required data attributes on schedule item:', scheduleItem);
                e.preventDefault();
                alert('Schedule item is missing required data. Please refresh the page.');
                return;
            }

            draggedData = {
                schedule_id: scheduleItem.dataset.scheduleId,
                teacher_id: scheduleItem.dataset.teacherId,
                subject_id: scheduleItem.dataset.subjectId,
                section_id: scheduleItem.dataset.sectionId,
                room_id: scheduleItem.dataset.roomId,
                term_id: scheduleItem.dataset.termId,
                day: scheduleItem.dataset.day,
                time_start: scheduleItem.dataset.timeStart,
                time_end: scheduleItem.dataset.timeEnd,
                slot_count: scheduleItem.dataset.slotCount
            };
            renderConflictHeatmap(draggedData);

            try {
                const payload = JSON.stringify(draggedData);
                e.dataTransfer.setData('text', payload);
                e.dataTransfer.setData('text/plain', payload);
                e.dataTransfer.setData('application/json', payload);
            } catch (err) {
                // Some browsers restrict setData in dragstart; ignore.
            }

            e.dataTransfer.effectAllowed = 'move';
            scheduleItem.style.opacity = '0.5';
            return;
        }

        const subjectCard = e.target.closest('.subject-draggable');
        if (subjectCard) {
            console.debug('dragstart on subject card', { subjectId: subjectCard.dataset.subjectId });
            const subjectId = subjectCard.dataset.subjectId;
            const sectionId = subjectCard.dataset.sectionId;
            const termId = subjectCard.dataset.termId || termInput.value || termFilter?.value;

            if (!subjectId || !sectionId || !termId) {
                alert('Missing required subject information to place schedule.');
                e.preventDefault();
                return;
            }

            const defaultRoomId = getDefaultRoomId(subjectId);
            const defaultTeacherId = getDefaultTeacherId(subjectId);
            const duration = parseFloat(durationInput.value) || 1;

            draggedData = {
                subject_id: subjectId,
                section_id: sectionId,
                term_id: termId,
                room_id: defaultRoomId,
                teacher_id: defaultTeacherId,
                duration: duration
            };

            renderConflictHeatmap(draggedData);

            try {
                const payload = JSON.stringify(draggedData);
                e.dataTransfer.setData('text/plain', payload);
                e.dataTransfer.setData('application/json', payload);
            } catch (err) {
                // Some browsers restrict setData in dragstart; ignore.
            }

            e.dataTransfer.effectAllowed = 'move';
            subjectCard.style.opacity = '0.5';
        }
    });

    document.addEventListener('dragend', (e) => {
        if (activeDropZone) {
            activeDropZone.classList.remove('ring-2', 'ring-indigo-500');
            activeDropZone = null;
        }
        clearDragShadow();
        clearConflictHeatmap();

        const scheduleItem = e.target.closest('.schedule-item');
        if (scheduleItem) {
            scheduleItem.style.opacity = '1';
        }

        const subjectCard = e.target.closest('.subject-draggable');
        if (subjectCard) {
            subjectCard.style.opacity = '1';
        }
    });

    // Delegated drop-zone handlers for better performance on large grids
    timetableGrid.addEventListener('dragenter', (e) => {
        const cell = e.target.closest('.drop-zone');
        if (!cell) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });

    timetableGrid.addEventListener('dragover', (e) => {
        const cell = e.target.closest('.drop-zone');
        if (!cell) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        if (activeDropZone && activeDropZone !== cell) {
            activeDropZone.classList.remove('ring-2', 'ring-indigo-500');
        }
        activeDropZone = cell;
        activeDropZone.classList.add('ring-2', 'ring-indigo-500');

        if (draggedData) {
            showDragShadow(cell, draggedData);
        }
    });

    timetableGrid.addEventListener('dragleave', (e) => {
        if (!timetableGrid.contains(e.relatedTarget)) {
            if (activeDropZone) {
                activeDropZone.classList.remove('ring-2', 'ring-indigo-500');
                activeDropZone = null;
            }
            clearDragShadow();
        }
    });

    function initDropZones() {
        document.querySelectorAll('.drop-zone').forEach((cell) => {
            if (cell._timetableInit) return;
            cell._timetableInit = true;

            cell.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (activeDropZone && activeDropZone !== cell) {
                    activeDropZone.classList.remove('ring-2', 'ring-indigo-500');
                }
                activeDropZone = cell;
                activeDropZone.classList.add('ring-2', 'ring-indigo-500');

                if (draggedData) {
                    showDragShadow(cell, draggedData);
                }
            });

            cell.addEventListener('drop', async (e) => {
                if (e._timetableHandled) return;
                e._timetableHandled = true;

                console.log('timetable drop event', { target: e.target, draggedData, cell });

                e.preventDefault();
                if (activeDropZone) {
                    activeDropZone.classList.remove('ring-2', 'ring-indigo-500');
                    activeDropZone = null;
                }
                clearDragShadow();

                if (!draggedData) {
                    try {
                        const stored = e.dataTransfer.getData('text') ||
                            e.dataTransfer.getData('application/json') ||
                            e.dataTransfer.getData('text/plain');
                        if (stored) {
                            draggedData = JSON.parse(stored);
                        }
                    } catch (err) {
                        // ignore parse errors
                    }
                }

                if (!draggedData) return;
                const placementData = draggedData;
                draggedData = null;
                await placeScheduleInCell(cell, placementData);
            });
        });
    }

    initDropZones();
    ensureResizeHandles();

    async function placeScheduleInCell(cell, placementData) {
        const day = cell.dataset.day;
        const timeStart = cell.dataset.timeStart;
        const roomId = cell.dataset.roomId || placementData.room_id;

        let timeEnd;
        if (placementData.schedule_id && placementData.slot_count) {
            const durationMinutes = parseInt(placementData.slot_count, 10) * 60;
            const [newStartHours, newStartMinutes] = timeStart.split(':').map(Number);
            const newEndMinutes = newStartMinutes + durationMinutes;
            const finalHours = newStartHours + Math.floor(newEndMinutes / 60);
            const finalMinutes = newEndMinutes % 60;
            timeEnd = `${String(finalHours).padStart(2, '0')}:${String(finalMinutes).padStart(2, '0')}`;
        } else if (placementData.schedule_id && placementData.time_end) {
            const [startHours, startMinutes] = placementData.time_start.split(':').map(Number);
            const [endHours, endMinutes] = placementData.time_end.split(':').map(Number);
            const durationMinutes = (endHours * 60 + endMinutes) - (startHours * 60 + startMinutes);

            const [newStartHours, newStartMinutes] = timeStart.split(':').map(Number);
            const newEndMinutes = newStartMinutes + durationMinutes;
            const finalHours = newStartHours + Math.floor(newEndMinutes / 60);
            const finalMinutes = newEndMinutes % 60;
            timeEnd = `${String(finalHours).padStart(2, '0')}:${String(finalMinutes).padStart(2, '0')}`;
        } else {
            const duration = placementData.duration || 1;
            const [hours, minutes] = timeStart.split(':').map(Number);
            const endDate = new Date();
            endDate.setHours(hours, minutes, 0);
            endDate.setHours(endDate.getHours() + Math.floor(duration));
            endDate.setMinutes(endDate.getMinutes() + (duration % 1) * 60);
            timeEnd = `${String(endDate.getHours()).padStart(2, '0')}:${String(endDate.getMinutes()).padStart(2, '0')}`;
        }

        const isUpdate = placementData.schedule_id && placementData.schedule_id !== 'undefined' && placementData.schedule_id !== '';
        if (isUpdate && placementData.day === day && placementData.time_start === timeStart && placementData.time_end === timeEnd) {
            return;
        }

        const subject = subjectMap.get(String(placementData.subject_id));
        const room = roomsData.find((item) => String(item.id) === String(roomId));
        if (!isRoomCompatible(subject, room)) {
            showConflictPopup({ room_type: 'Selected room type is not compatible with this subject.' });
            return;
        }

        if (!autosaveEnabled) {
            const draftId = placementData.schedule_id || placementData.draft_id || `draft-${Date.now()}-${draftIdCounter++}`;
            const draftSchedule = {
                id: draftId,
                teacher_id: placementData.teacher_id || null,
                subject_id: placementData.subject_id,
                section_id: placementData.section_id,
                room_id: roomId,
                term_id: placementData.term_id,
                day: day,
                time_start: timeStart,
                time_end: timeEnd,
                is_published: false
            };

            const conflicts = getLocalConflicts(draftSchedule, placementData.schedule_id || placementData.draft_id || null);
            if (Object.keys(conflicts).length) {
                showConflictPopup(conflicts);
                return null;
            }

            applyScheduleUpdate(draftSchedule, cell);
            return draftSchedule;
        }

        const conflictData = {
            teacher_id: placementData.teacher_id || null,
            section_id: placementData.section_id,
            room_id: roomId,
            term_id: placementData.term_id,
            day: day,
            time_start: timeStart,
            time_end: timeEnd,
            exclude_id: placementData.schedule_id || null
        };

        try {
            const conflictResponse = await fetch(endpoints.checkConflicts, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(conflictData)
            });

            if (!conflictResponse.ok) {
                let conflictError = `Conflict check failed: ${conflictResponse.status} ${conflictResponse.statusText}`;
                const conflictType = conflictResponse.headers.get('content-type') || '';

                if (conflictType.includes('application/json')) {
                    try {
                        const conflictErrorData = await conflictResponse.json();
                        if (conflictErrorData.message) {
                            conflictError = conflictErrorData.message;
                        } else if (conflictErrorData.errors) {
                            const errors = typeof conflictErrorData.errors === 'object'
                                ? Object.values(conflictErrorData.errors).flat()
                                : [conflictErrorData.errors];
                            conflictError = errors.join(', ');
                        }
                    } catch (_) {
                        // Fall back to generic status message.
                    }
                } else if (conflictResponse.status === 401 || conflictResponse.status === 419) {
                    conflictError = 'Your session has expired. Please refresh the page and try again.';
                }

                alert('Error: ' + conflictError);
                return;
            }

            const conflictResult = await conflictResponse.json();

            if (conflictResult.has_conflicts) {
                showConflictPopup(conflictResult.conflicts);
                return;
            }

            let saveData = {
                teacher_id: placementData.teacher_id || null,
                subject_id: placementData.subject_id,
                section_id: placementData.section_id,
                room_id: roomId,
                term_id: placementData.term_id,
                day: day,
                time_start: timeStart,
                time_end: timeEnd
            };

            if (placementData.curriculum_id) {
                saveData.curriculum_id = placementData.curriculum_id;
            }

            let url;
            let method;
            if (isUpdate) {
                url = endpoints.updateTemplate.replace('__SCHEDULE_ID__', placementData.schedule_id);
                method = 'POST';
                saveData._method = 'PUT';
            } else {
                url = endpoints.storeFromTimetable;
                method = 'POST';
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(saveData)
            });

            if (!response.ok) {
                let errorMessage = 'Failed to save schedule';
                try {
                    const errorData = await response.json();
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    } else if (errorData.errors) {
                        const errors = typeof errorData.errors === 'object'
                            ? Object.values(errorData.errors).flat()
                            : [errorData.errors];
                        errorMessage = errors.join(', ');
                    } else if (errorData.error) {
                        errorMessage = errorData.error;
                    }
                } catch (e) {
                    console.error('Failed to parse error response:', e);
                    errorMessage = `Server error: ${response.status} ${response.statusText}`;
                }
                alert('Error: ' + errorMessage);
                return;
            }

            let result;
            try {
                result = await response.json();
            } catch (e) {
                console.error('Failed to parse success response:', e);
                window.location.reload();
                return;
            }

            if (result.success !== false) {
                if (!isUpdate) {
                    lastScheduleDraft = {
                        teacher_id: saveData.teacher_id,
                        subject_id: saveData.subject_id,
                        section_id: saveData.section_id,
                        room_id: saveData.room_id,
                        curriculum_id: saveData.curriculum_id || '',
                        term_id: saveData.term_id,
                        duration: placementData.duration || parseFloat(durationInput.value || '1')
                    };
                    reuseLastBtn.disabled = false;
                }

                if (result.schedule) {
                    applyScheduleUpdate(result.schedule, cell);
                }
                return result.schedule || null;
            } else {
                const errorMsg = result.message || result.errors || 'Failed to save schedule';
                alert('Error: ' + (typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg));
                return null;
            }
        } catch (error) {
            console.error('Exception caught:', error);
            console.error('Placement Data:', placementData);
            console.error('Error stack:', error.stack);
            const hint = navigator.onLine
                ? 'Request could not reach the server. Please refresh and make sure you are using the same app URL/host.'
                : 'You appear to be offline. Check your network and try again.';
            alert('An error occurred while saving the schedule: ' + (error.message || 'Unknown error') + '. ' + hint);
            return null;
        }
    }

    function getSlotCountFromHeight(heightPx) {
        // Each slot is SLOT_HEIGHT px tall; schedule items use height calc(slotCount * SLOT_HEIGHT - 4px).
        const normalized = Math.max(1, Math.round((heightPx + 4) / SLOT_HEIGHT));
        return normalized;
    }

    function getEndTimeFromStartAndSlots(startTime, slotCount) {
        const startMinutes = parseMinutes(startTime);
        const endMinutes = startMinutes + slotCount * 60;
        return formatMinutes(endMinutes);
    }

    async function resizeScheduleItem(scheduleItem, newEndTime) {
        const cell = scheduleItem.closest('.drop-zone');
        if (!cell) return;

        const placementData = {
            schedule_id: scheduleItem.dataset.scheduleId,
            teacher_id: scheduleItem.dataset.teacherId,
            subject_id: scheduleItem.dataset.subjectId,
            section_id: scheduleItem.dataset.sectionId,
            room_id: scheduleItem.dataset.roomId,
            term_id: scheduleItem.dataset.termId,
            day: scheduleItem.dataset.day,
            time_start: scheduleItem.dataset.timeStart,
            time_end: newEndTime
        };

        // Provide conflict preview while resizing.
        renderConflictHeatmap(placementData);

        const result = await placeScheduleInCell(cell, placementData);
        if (!result) {
            updateSidebarAssignment(getScheduleDataFromItem(scheduleItem));
        }
    }

    function ensureResizeHandles() {
        document.querySelectorAll('.schedule-item').forEach((item) => {
            if (item.querySelector('.resize-handle')) return;
            const handle = document.createElement('div');
            handle.className = 'resize-handle absolute bottom-0 left-0 right-0 h-2 cursor-s-resize bg-transparent hover:bg-indigo-200/40';
            handle.title = 'Drag to adjust duration';
            item.appendChild(handle);
            observeScheduleItem(item);
            const slots = parseInt(item.dataset.slotCount || '0', 10) || getSlotCountFromHeight(item.getBoundingClientRect().height);
            updateScheduleBodySize(item, slots);
        });
    }

    function handleScheduleResizeStart(e) {
        const handle = e.target.closest('.resize-handle');
        if (!handle) return;

        e.preventDefault();
        e.stopPropagation();

        const scheduleItem = handle.closest('.schedule-item');
        if (!scheduleItem) return;

        resizingScheduleItem = scheduleItem;
        resizeWasDraggable = scheduleItem.draggable;
        scheduleItem.draggable = false;
        resizeStartY = e.pageY;
        resizeOriginalHeight = scheduleItem.getBoundingClientRect().height;
        resizeStartHeight = resizeOriginalHeight;
        resizeStartSlots = getSlotCountFromHeight(resizeOriginalHeight);
        resizeOriginalEndTime = scheduleItem.dataset.timeEnd;

        document.body.style.userSelect = 'none';
        document.addEventListener('pointermove', handleScheduleResizeMove, { passive: false });
        document.addEventListener('pointerup', handleScheduleResizeEnd);
        document.addEventListener('pointercancel', handleScheduleResizeEnd);
    }

    function handleScheduleResizeMove(e) {
        if (!resizingScheduleItem) return;
        e.preventDefault();

        const deltaY = e.pageY - resizeStartY;
        const rawHeight = resizeStartHeight + deltaY;
        const newSlots = Math.max(1, getSlotCountFromHeight(rawHeight));
        const newHeight = Math.max(SLOT_HEIGHT, newSlots * SLOT_HEIGHT - 4);

        resizingScheduleItem.style.height = `${newHeight}px`;
        resizingScheduleItem.dataset.slotCount = String(newSlots);
        updateScheduleBodySize(resizingScheduleItem, newSlots);

        const newEndTime = getEndTimeFromStartAndSlots(resizingScheduleItem.dataset.timeStart, newSlots);
        resizingScheduleItem.dataset.timeEnd = newEndTime;
        renderConflictHeatmap({
            ...getScheduleDataFromItem(resizingScheduleItem),
            time_end: newEndTime
        });

        updateSidebarAssignment({
            subject_id: resizingScheduleItem.dataset.subjectId,
            section_id: resizingScheduleItem.dataset.sectionId,
            room_id: resizingScheduleItem.dataset.roomId,
            day: resizingScheduleItem.dataset.day,
            time_start: resizingScheduleItem.dataset.timeStart,
            time_end: newEndTime
        });
    }

    async function handleScheduleResizeEnd() {
        if (!resizingScheduleItem) return;

        const finalHeight = resizingScheduleItem.getBoundingClientRect().height;
        const finalSlots = getSlotCountFromHeight(finalHeight);
        const finalEndTime = getEndTimeFromStartAndSlots(resizingScheduleItem.dataset.timeStart, finalSlots);

        const result = await resizeScheduleItem(resizingScheduleItem, finalEndTime);
        if (!result && resizeOriginalEndTime) {
            resizingScheduleItem.dataset.timeEnd = resizeOriginalEndTime;
        }

        if (resizeWasDraggable !== null) {
            resizingScheduleItem.draggable = resizeWasDraggable;
        }
        resizingScheduleItem = null;
        resizeWasDraggable = null;
        resizeOriginalEndTime = null;
        clearConflictHeatmap();
        saveDraftState();
        document.body.style.userSelect = '';
        document.removeEventListener('pointermove', handleScheduleResizeMove);
        document.removeEventListener('pointerup', handleScheduleResizeEnd);
        document.removeEventListener('pointercancel', handleScheduleResizeEnd);
    }

    function getScheduleDataFromItem(item) {
        return {
            schedule_id: item.dataset.scheduleId,
            teacher_id: item.dataset.teacherId,
            subject_id: item.dataset.subjectId,
            section_id: item.dataset.sectionId,
            room_id: item.dataset.roomId,
            term_id: item.dataset.termId,
            day: item.dataset.day,
            time_start: item.dataset.timeStart,
            time_end: item.dataset.timeEnd
        };
    }

    async function handleTimetableDrop(e) {
        if (e._timetableHandled && !draggedData) return;
        e._timetableHandled = true;

        const cell = e.target.closest('.drop-zone');
        if (!cell) return;
        e.preventDefault();
        console.debug('handleTimetableDrop fired', { cell, draggedData });

        if (activeDropZone) {
            activeDropZone.classList.remove('ring-2', 'ring-indigo-500');
            activeDropZone = null;
        }
        clearDragShadow();

        // Some browsers (e.g. Firefox/Edge) may not expose our global drag state reliably.
        if (!draggedData) {
            try {
                const stored = e.dataTransfer.getData('text') ||
                    e.dataTransfer.getData('application/json') ||
                    e.dataTransfer.getData('text/plain');
                if (stored) {
                    draggedData = JSON.parse(stored);
                }
            } catch (err) {
                // ignore parse errors
            }
        }

        if (!draggedData) return;
        const placementData = draggedData;
        draggedData = null;
        await placeScheduleInCell(cell, placementData);
    }

    timetableGrid.addEventListener('drop', handleTimetableDrop);

    document.addEventListener('pointerdown', handleScheduleResizeStart);

    // Expose handlers for inline attributes (Edge/older browsers)
    window.handleTimetableDrop = handleTimetableDrop;

    // Global fallback for browsers that don't bubble drag events reliably (Edge/Firefox)
    document.addEventListener('dragover', (e) => {
        const cell = e.target.closest('.drop-zone');
        if (!cell) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }, true);

    document.addEventListener('drop', async (e) => {
        if (e._timetableHandled) return;
        e._timetableHandled = true;

        const cell = e.target.closest('.drop-zone');
        if (!cell) return;
        e.preventDefault();

        if (activeDropZone) {
            activeDropZone.classList.remove('ring-2', 'ring-indigo-500');
            activeDropZone = null;
        }
        clearDragShadow();

        if (!draggedData) {
            try {
                const stored = e.dataTransfer.getData('text') ||
                    e.dataTransfer.getData('application/json') ||
                    e.dataTransfer.getData('text/plain');
                if (stored) {
                    draggedData = JSON.parse(stored);
                }
            } catch (err) {
                // ignore parse errors
            }
        }

        if (!draggedData) return;
        const placementData = draggedData;
        draggedData = null;
        await placeScheduleInCell(cell, placementData);
    }, true);

    timetableGrid.addEventListener('click', async (e) => {
        const deleteBtn = e.target.closest('.delete-schedule-btn');
        if (deleteBtn) {
            e.preventDefault();
            e.stopPropagation();

            const scheduleId = deleteBtn.dataset.scheduleId;
            if (!scheduleId) return;
            const scheduleItem = deleteBtn.closest('.schedule-item');
            const subjectId = scheduleItem?.dataset.subjectId;
            const sectionId = scheduleItem?.dataset.sectionId;

            const confirmed = confirm('Remove this schedule block?');
            if (!confirmed) return;

            try {
                if (!autosaveEnabled) {
                    removeScheduleItemInstances(scheduleId);
                    removeScheduleCache(scheduleId);
                    if (subjectId && sectionId) {
                        updateSidebarAssignment({ subject_id: subjectId, section_id: sectionId }, { clear: true });
                    }
                    saveDraftState();
                    return;
                }

                const deleteUrl = endpoints.destroyTemplate.replace('__SCHEDULE_ID__', scheduleId);
                const response = await fetch(deleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                if (!response.ok) {
                    let message = `Failed to remove schedule (${response.status})`;
                    try {
                        const data = await response.json();
                        message = data.message || message;
                    } catch (_) {
                        // Keep fallback message.
                    }
                    alert(message);
                    return;
                }

                removeScheduleItemInstances(scheduleId);
                removeScheduleCache(scheduleId);
                if (subjectId && sectionId) {
                    updateSidebarAssignment({ subject_id: subjectId, section_id: sectionId }, { clear: true });
                }
            } catch (error) {
                alert('Unable to remove schedule block: ' + (error.message || 'Unknown error'));
            }
            return;
        }

        if (!clickPlaceMode) return;
        if (e.target.closest('.schedule-item')) return;

        const cell = e.target.closest('.drop-zone');
        if (!cell) return;

        if (!confirmedDraft) {
            alert('Click "Add To Card" first before click-to-place.');
            return;
        }
        await placeScheduleInCell(cell, { ...confirmedDraft });
    });
});
</script>
@endsection
