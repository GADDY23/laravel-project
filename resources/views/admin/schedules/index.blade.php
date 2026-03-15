@extends('layouts.admin')
@section('title', 'Schedules')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Schedules</h1>
    <a href="{{ route('admin.schedules.configure', ['reset' => 1]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add New</a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="px-5 py-4 bg-slate-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700 rounded-t-xl">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @if(!empty($hasPublishFlag))
                <select name="status" class="rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="posted" {{ ($statusFilter ?? '') === 'posted' ? 'selected' : '' }}>Posted Only</option>
                    <option value="draft" {{ ($statusFilter ?? '') === 'draft' ? 'selected' : '' }}>Draft Only</option>
                </select>
            @endif
            <select name="term_id" class="rounded-lg border-gray-300 shadow-sm text-sm">
                <option value="">All Terms</option>
                @foreach(\App\Models\Term::enabled()->get() as $termOption)
                    <option value="{{ $termOption->id }}" {{ request('term_id') == $termOption->id ? 'selected' : '' }}>{{ $termOption->term_code }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium">Apply Filter</button>
        </form>
    </div>

    <div class="p-0">
        @php
            $scheduleCollection = $schedules instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $schedules->getCollection()
                : collect($schedules);
        @endphp

        @if($scheduleCollection->isEmpty())
            <div class="p-6 text-sm text-gray-500">No schedules found.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide text-gray-500 bg-white dark:bg-gray-800">
                            <th class="px-6 py-4 text-left border-b border-gray-200 dark:border-gray-700">Schedule Name</th>
                            <th class="px-6 py-4 text-left border-b border-gray-200 dark:border-gray-700">Term</th>
                            <th class="px-6 py-4 text-left border-b border-gray-200 dark:border-gray-700">Rooms</th>
                            <th class="px-6 py-4 text-left border-b border-gray-200 dark:border-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduleCollection as $schedule)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                    {{ $schedule->schedule_name ?: 'Untitled' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                    {{ $termCodes[$schedule->term_id] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                    {{ $schedule->room_count ? $schedule->room_count : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $roomIds = $schedule->room_ids ? explode(',', $schedule->room_ids) : [];
                                        @endphp
                                        <a href="{{ route('admin.schedules.timetable-view', ['term_id' => $schedule->term_id, 'schedule_name' => $schedule->schedule_name, 'rooms' => $roomIds]) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                        <a href="{{ route('admin.schedules.timetable-edit', ['term_id' => $schedule->term_id, 'schedule_name' => $schedule->schedule_name, 'rooms' => $roomIds]) }}" class="text-amber-600 hover:text-amber-800">Edit</a>
                                        <form action="{{ route('admin.schedules.destroy-group') }}" method="POST" onsubmit="return confirm('Delete this schedule group?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="term_id" value="{{ $schedule->term_id }}">
                                            <input type="hidden" name="schedule_name" value="{{ $schedule->schedule_name }}">
                                            @if(!empty($hasPublishFlag))
                                                <input type="hidden" name="is_published" value="{{ (int) ($schedule->is_published ?? 0) }}">
                                            @endif
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="px-6 py-4">{{ $schedules->links() }}</div>
    </div>
</div>
@endsection
