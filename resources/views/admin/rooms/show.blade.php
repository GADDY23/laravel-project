@extends('layouts.admin')
@section('title', 'Room Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Room Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.rooms.edit', $room) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.rooms.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Room Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Name:</dt>
                <dd>{{ $room->name }}</dd>
                <dt class="font-medium">Capacity:</dt>
                <dd>{{ $room->capacity }}</dd>
                <dt class="font-medium">Building:</dt>
                <dd>{{ $room->building ?? 'N/A' }}</dd>
                <dt class="font-medium">Floor:</dt>
                <dd>{{ $room->floor ?? 'N/A' }}</dd>
                <dt class="font-medium">Type:</dt>
                <dd>{{ ucfirst(str_replace('_', ' ', $room->type)) }}</dd>
                <dt class="font-medium">Description:</dt>
                <dd>{{ $room->description ?? 'N/A' }}</dd>
            </dl>
        </div>
    </div>

    @if($room->schedules->count() > 0)
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">Schedules</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Section</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($room->schedules as $schedule)
                    <tr>
                        <td class="px-6 py-4">{{ $schedule->subject->name }}</td>
                        <td class="px-6 py-4">{{ $schedule->section->name }}</td>
                        <td class="px-6 py-4">{{ ucfirst($schedule->day) }}</td>
                        <td class="px-6 py-4">{{ $schedule->time_start }} - {{ $schedule->time_end }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection




