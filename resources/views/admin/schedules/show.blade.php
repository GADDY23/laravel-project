@extends('layouts.admin')
@section('title', 'Schedule Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Schedule Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.schedules.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Schedule Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Teacher:</dt>
                <dd>{{ $schedule->teacher->name }}</dd>
                <dt class="font-medium">Subject:</dt>
                <dd>{{ $schedule->subject->name }}</dd>
                <dt class="font-medium">Section:</dt>
                <dd>{{ $schedule->section->name }}</dd>
                <dt class="font-medium">Room:</dt>
                <dd>{{ $schedule->room->name }}</dd>
                <dt class="font-medium">Day:</dt>
                <dd>{{ ucfirst($schedule->day) }}</dd>
                <dt class="font-medium">Time:</dt>
                <dd>{{ $schedule->time_start }} - {{ $schedule->time_end }}</dd>
                <dt class="font-medium">Term:</dt>
                <dd>{{ $schedule->term->term_code }} - {{ $schedule->term->academic_year }} - {{ $schedule->term->semester }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection



