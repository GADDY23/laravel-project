@extends('layouts.admin')
@section('title', 'Schedule Timetable')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Schedule Timetable</h1>
    <div class="flex gap-4">
        <select id="term-filter" class="rounded-lg border-gray-300">
            <option value="">All Terms</option>
            @foreach($terms as $term)
                <option value="{{ $term->id }}" {{ $termId == $term->id ? 'selected' : '' }}>{{ $term->academic_year }} - {{ $term->semester }}</option>
            @endforeach
        </select>
        <a href="{{ route('admin.schedules.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back to List</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Left Sidebar - Schedule Builder --}}
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Create Schedule</h2>
        
        <form id="schedule-form" class="space-y-4">
            @csrf
            <input type="hidden" name="term_id" id="term_id" value="{{ $termId ?? '' }}">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teacher</label>
                <select name="teacher_id" id="teacher_id" required class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                <select name="subject_id" id="subject_id" required class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section</label>
                <select name="section_id" id="section_id" required class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room</label>
                <select name="room_id" id="room_id" required class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Room</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (hours)</label>
                <input type="number" id="duration" min="0.5" max="4" step="0.5" value="1" class="w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded border-2 border-dashed border-indigo-300 dark:border-indigo-600">
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Fill the form above, then drag this card to the timetable:</p>
                <div id="draggable-schedule" 
                     class="draggable-schedule-item cursor-move bg-indigo-100 dark:bg-indigo-900/40 p-3 rounded border-2 border-indigo-400 dark:border-indigo-600"
                     draggable="true">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white" id="preview-subject">Select options...</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400" id="preview-teacher"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-500" id="preview-section"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-500" id="preview-room"></div>
                </div>
            </div>
        </form>
    </div>

    {{-- Timetable Grid --}}
    <div class="lg:col-span-9 bg-white dark:bg-gray-800 rounded-lg shadow p-4 overflow-x-auto">
        <div id="conflict-alert" class="hidden mb-4 p-4 bg-red-100 dark:bg-red-900/20 border-l-4 border-red-500 text-red-700 dark:text-red-400 rounded">
            <p class="font-semibold">Schedule Conflict Detected!</p>
            <ul id="conflict-list" class="mt-2 text-sm list-disc list-inside"></ul>
        </div>

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
                'bg-pink-200 dark:bg-pink-900/40',
                'bg-yellow-200 dark:bg-yellow-900/40',
                'bg-blue-200 dark:bg-blue-900/40',
                'bg-green-200 dark:bg-green-900/40',
                'bg-purple-200 dark:bg-purple-900/40',
            ];
        @endphp

        <table class="min-w-full border-collapse">
            <thead>
                <tr>
                    <th class="bg-green-600 dark:bg-green-700 text-white font-bold px-4 py-3 text-center border-2 border-green-700 dark:border-green-800">
                        Time
                    </th>
                    @foreach($dayNames as $dayName)
                        <th class="bg-green-600 dark:bg-green-700 text-white font-bold px-4 py-3 text-center border-2 border-green-700 dark:border-green-800">
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
                                
                                $colorIndex = $scheduleInCell ? (array_search($scheduleInCell['schedule']->subject_id, array_column($schedules->toArray(), 'subject_id')) % count($colorClasses)) : 0;
                            @endphp
                            
                            <td class="relative border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 min-h-[60px] p-0 drop-zone" 
                                data-day="{{ $day }}"
                                data-time-start="{{ $slotStart }}"
                                data-time-end="{{ $slotEnd }}"
                                style="height: 60px;">
                                @if($hasSchedule && $scheduleInCell)
                                    <div 
                                        class="schedule-item absolute inset-0 m-0.5 {{ $colorClasses[$colorIndex] }} rounded border border-gray-400 dark:border-gray-600 cursor-move hover:shadow-md transition-shadow"
                                        data-schedule-id="{{ $scheduleInCell['schedule']->id }}"
                                        data-teacher-id="{{ $scheduleInCell['schedule']->teacher_id }}"
                                        data-subject-id="{{ $scheduleInCell['schedule']->subject_id }}"
                                        data-section-id="{{ $scheduleInCell['schedule']->section_id }}"
                                        data-room-id="{{ $scheduleInCell['schedule']->room_id }}"
                                        data-term-id="{{ $scheduleInCell['schedule']->term_id }}"
                                        data-day="{{ $day }}"
                                        data-time-start="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_start)->format('H:i') }}"
                                        data-time-end="{{ \Carbon\Carbon::parse($scheduleInCell['schedule']->time_end)->format('H:i') }}"
                                        style="height: calc({{ $scheduleInCell['slotCount'] }} * 60px - 4px); z-index: 10;"
                                        draggable="true"
                                    >
                                        <div class="p-1.5 text-[10px] leading-tight h-full flex flex-col justify-center">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $scheduleInCell['schedule']->subject->name }}</div>
                                            <div class="text-gray-700 dark:text-gray-300">{{ $scheduleInCell['schedule']->section->name }}</div>
                                            <div class="text-gray-600 dark:text-gray-400">{{ $scheduleInCell['schedule']->teacher->name }}</div>
                                            <div class="text-gray-600 dark:text-gray-400">{{ $scheduleInCell['schedule']->room->name }}</div>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('schedule-form');
    const draggableItem = document.getElementById('draggable-schedule');
    const termFilter = document.getElementById('term-filter');
    let draggedData = null;
    let draggedElement = null;

    // Update preview
    function updatePreview() {
        const teacherSelect = document.getElementById('teacher_id');
        const subjectSelect = document.getElementById('subject_id');
        const sectionSelect = document.getElementById('section_id');
        const roomSelect = document.getElementById('room_id');
        
        const teacher = teacherSelect.options[teacherSelect.selectedIndex]?.text || '';
        const subject = subjectSelect.options[subjectSelect.selectedIndex]?.text || '';
        const section = sectionSelect.options[sectionSelect.selectedIndex]?.text || '';
        const room = roomSelect.options[roomSelect.selectedIndex]?.text || '';
        
        document.getElementById('preview-subject').textContent = subject || 'Select options...';
        document.getElementById('preview-teacher').textContent = teacher;
        document.getElementById('preview-section').textContent = section;
        document.getElementById('preview-room').textContent = room;
    }

    ['teacher_id', 'subject_id', 'section_id', 'room_id'].forEach(id => {
        document.getElementById(id).addEventListener('change', updatePreview);
    });
    updatePreview();

    // Term filter
    termFilter.addEventListener('change', () => {
        window.location.href = `{{ route('admin.schedules.timetable') }}?term_id=${termFilter.value}`;
    });

    // Drag from form
    draggableItem.addEventListener('dragstart', (e) => {
        const teacherId = document.getElementById('teacher_id').value;
        const subjectId = document.getElementById('subject_id').value;
        const sectionId = document.getElementById('section_id').value;
        const roomId = document.getElementById('room_id').value;
        const termId = document.getElementById('term_id').value;
        const duration = parseFloat(document.getElementById('duration').value);

        if (!teacherId || !subjectId || !sectionId || !roomId) {
            e.preventDefault();
            alert('Please fill all required fields first!');
            return;
        }

        draggedData = {
            teacher_id: teacherId,
            subject_id: subjectId,
            section_id: sectionId,
            room_id: roomId,
            term_id: termId,
            duration: duration
        };

        e.dataTransfer.effectAllowed = 'move';
        draggableItem.style.opacity = '0.5';
    });

    draggableItem.addEventListener('dragend', () => {
        draggableItem.style.opacity = '1';
    });

    // Drag existing schedule - use event delegation for dynamically loaded items
    document.addEventListener('dragstart', (e) => {
        if (e.target.classList.contains('schedule-item')) {
            const item = e.target;
            draggedElement = item;
            
            // Validate required data attributes
            if (!item.dataset.scheduleId || !item.dataset.teacherId || !item.dataset.subjectId) {
                console.error('Missing required data attributes on schedule item:', item);
                e.preventDefault();
                alert('Schedule item is missing required data. Please refresh the page.');
                return;
            }
            
            draggedData = {
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
            
            console.log('Dragging schedule:', draggedData);
            e.dataTransfer.effectAllowed = 'move';
            item.style.opacity = '0.5';
        }
    });

    document.addEventListener('dragend', (e) => {
        if (e.target.classList.contains('schedule-item')) {
            e.target.style.opacity = '1';
        }
    });

    // Drop zones
    document.querySelectorAll('.drop-zone').forEach(cell => {
        cell.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            cell.classList.add('ring-2', 'ring-indigo-500');
        });

        cell.addEventListener('dragleave', () => {
            cell.classList.remove('ring-2', 'ring-indigo-500');
        });

        cell.addEventListener('drop', async (e) => {
            e.preventDefault();
            cell.classList.remove('ring-2', 'ring-indigo-500');

            if (!draggedData) return;

            const day = cell.dataset.day;
            const timeStart = cell.dataset.timeStart;
            
            // Calculate time end - use existing duration if updating, otherwise use form duration
            let timeEnd;
            if (draggedData.schedule_id && draggedData.time_end) {
                // For existing schedules, calculate duration from original times
                const [startHours, startMinutes] = draggedData.time_start.split(':').map(Number);
                const [endHours, endMinutes] = draggedData.time_end.split(':').map(Number);
                const durationMinutes = (endHours * 60 + endMinutes) - (startHours * 60 + startMinutes);
                
                // Apply same duration to new start time
                const [newStartHours, newStartMinutes] = timeStart.split(':').map(Number);
                const newEndMinutes = newStartMinutes + durationMinutes;
                const finalHours = newStartHours + Math.floor(newEndMinutes / 60);
                const finalMinutes = newEndMinutes % 60;
                timeEnd = `${String(finalHours).padStart(2, '0')}:${String(finalMinutes).padStart(2, '0')}`;
            } else {
                // For new schedules, use form duration
                const duration = draggedData.duration || 1;
                const [hours, minutes] = timeStart.split(':').map(Number);
                const endDate = new Date();
                endDate.setHours(hours, minutes, 0);
                endDate.setHours(endDate.getHours() + Math.floor(duration));
                endDate.setMinutes(endDate.getMinutes() + (duration % 1) * 60);
                timeEnd = `${String(endDate.getHours()).padStart(2, '0')}:${String(endDate.getMinutes()).padStart(2, '0')}`;
            }
            
            // Check if this is the same position (no change needed)
            const isUpdate = draggedData.schedule_id && draggedData.schedule_id !== 'undefined' && draggedData.schedule_id !== '';
            if (isUpdate && draggedData.day === day && draggedData.time_start === timeStart && draggedData.time_end === timeEnd) {
                // Same position, no need to update
                draggedData = null;
                return;
            }

            // Check conflicts
            const conflictData = {
                teacher_id: draggedData.teacher_id,
                section_id: draggedData.section_id,
                room_id: draggedData.room_id,
                term_id: draggedData.term_id,
                day: day,
                time_start: timeStart,
                time_end: timeEnd,
                exclude_id: draggedData.schedule_id || null
            };

            try {
                const conflictResponse = await fetch('{{ route("admin.schedules.check-conflicts") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(conflictData)
                });

                const conflictResult = await conflictResponse.json();

                if (conflictResult.has_conflicts) {
                    const alert = document.getElementById('conflict-alert');
                    const list = document.getElementById('conflict-list');
                    list.innerHTML = '';
                    Object.values(conflictResult.conflicts).forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        list.appendChild(li);
                    });
                    alert.classList.remove('hidden');
                    setTimeout(() => alert.classList.add('hidden'), 5000);
                    return;
                }

                // Check if this is an update (existing schedule) or create (new schedule)
                const isUpdate = draggedData.schedule_id && draggedData.schedule_id !== 'undefined' && draggedData.schedule_id !== '';
                
                let saveData = {
                    teacher_id: draggedData.teacher_id,
                    subject_id: draggedData.subject_id,
                    section_id: draggedData.section_id,
                    room_id: draggedData.room_id,
                    term_id: draggedData.term_id,
                    day: day,
                    time_start: timeStart,
                    time_end: timeEnd
                };

                let url, method;
                if (isUpdate) {
                    // Update existing schedule - use PUT method with _method override for Laravel
                    url = `/admin/schedules/${draggedData.schedule_id}`;
                    method = 'POST';
                    saveData._method = 'PUT';
                } else {
                    // Create new schedule
                    url = '{{ route("admin.schedules.store-from-timetable") }}';
                    method = 'POST';
                }

                console.log('Saving schedule:', { url, method, saveData, isUpdate });
                
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

                console.log('Response status:', response.status, response.statusText);

                // Check if response is ok
                if (!response.ok) {
                    let errorMessage = 'Failed to save schedule';
                    try {
                        const errorData = await response.json();
                        console.error('Error response:', errorData);
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
                    draggedData = null;
                    return;
                }

                let result;
                try {
                    result = await response.json();
                    console.log('Success response:', result);
                } catch (e) {
                    console.error('Failed to parse success response:', e);
                    // If response is ok but not JSON, assume success
                    window.location.reload();
                    return;
                }

                if (result.success !== false) {
                    // Reload page to show updated schedule
                    window.location.reload();
                } else {
                    const errorMsg = result.message || result.errors || 'Failed to save schedule';
                    alert('Error: ' + (typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg));
                }
            } catch (error) {
                console.error('Exception caught:', error);
                console.error('Dragged Data:', draggedData);
                console.error('Error stack:', error.stack);
                alert('An error occurred while saving the schedule: ' + (error.message || 'Unknown error'));
            }

            draggedData = null;
        });
    });
});
</script>
@endsection


