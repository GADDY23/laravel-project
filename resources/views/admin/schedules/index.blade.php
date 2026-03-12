@extends('layouts.admin')
@section('title', 'Posted Generated Timetable')
@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Posted Generated Timetable</h1>
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
                <span id="rooms-display">{{ isset($selectedRooms) && count($selectedRooms) ? implode(', ', $rooms->pluck('name')->toArray()) : '-' }}</span>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('admin.schedules.configure') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl shadow-sm">Create Schedule</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="px-5 py-4 bg-slate-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700 rounded-t-xl">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            @if(request()->filled('schedule_name'))
                <input type="hidden" name="schedule_name" value="{{ request('schedule_name') }}">
            @endif
            @if(request()->filled('term_id'))
                <input type="hidden" name="term_id" value="{{ request('term_id') }}">
            @endif
            @foreach((array) request('rooms', []) as $roomId)
                <input type="hidden" name="rooms[]" value="{{ $roomId }}">
            @endforeach

            @if(!empty($hasPublishFlag))
                <select name="status" class="rounded-xl border-gray-300 shadow-sm text-sm">
                    <option value="posted" {{ ($statusFilter ?? 'posted') === 'posted' ? 'selected' : '' }}>Posted Only</option>
                    <option value="draft" {{ ($statusFilter ?? '') === 'draft' ? 'selected' : '' }}>Draft Only</option>
                    <option value="all" {{ ($statusFilter ?? '') === 'all' ? 'selected' : '' }}>All</option>
                </select>
            @endif
            <select name="teacher_id" class="rounded-xl border-gray-300 shadow-sm text-sm">
                <option value="">All Teachers</option>
                @foreach(\App\Models\User::where('role', 'teacher')->get() as $teacher)
                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                @endforeach
            </select>
            <select name="section_id" class="rounded-xl border-gray-300 shadow-sm text-sm">
                <option value="">All Sections</option>
                @foreach(\App\Models\Section::all() as $section)
                    <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                @endforeach
            </select>
            <select name="room_id" class="rounded-xl border-gray-300 shadow-sm text-sm">
                <option value="">All Rooms</option>
                @foreach(\App\Models\Room::all() as $room)
                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-xl shadow-sm text-sm font-medium">Apply Filter</button>
        </form>
    </div>

    <div class="p-4">
        @php
            $scheduleCollection = $schedules instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $schedules->getCollection()
                : collect($schedules);
        @endphp

        @if($scheduleCollection->isEmpty())
            <p class="text-sm text-gray-500">No schedules found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Term</th>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Day</th>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Time</th>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Section</th>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Subject</th>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Teacher</th>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Room</th>
                            <th class="bg-green-600 text-white font-bold px-4 py-3 text-left border-2 border-green-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduleCollection as $schedule)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ optional($schedule->term)->term_code ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ ucfirst($schedule->day) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ \Carbon\Carbon::parse($schedule->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->time_end)->format('H:i') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ optional($schedule->section)->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ optional($schedule->subject)->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ optional($schedule->teacher)->name ?? 'TBA' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ optional($schedule->room)->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm font-semibold {{ $schedule->is_published ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }} border border-gray-200 dark:border-gray-700">
                                    {{ $schedule->is_published ? 'Published' : 'Draft' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-4">{{ $schedules->links() }}</div>
    </div>
</div>
@endsection
