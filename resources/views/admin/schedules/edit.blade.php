@extends('layouts.admin')
@section('title', 'Edit Schedule')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Schedule</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teacher</label>
                <select name="teacher_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $schedule->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                    @endforeach
                </select>
                @if($errors->has('teacher'))<p class="text-red-500 text-xs mt-1">{{ $errors->first('teacher') }}</p>@endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                <select name="subject_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ old('subject_id', $schedule->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                <select name="section_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ old('section_id', $schedule->section_id) == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                </select>
                @if($errors->has('section'))<p class="text-red-500 text-xs mt-1">{{ $errors->first('section') }}</p>@endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Room</label>
                <select name="room_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ old('room_id', $schedule->room_id) == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                    @endforeach
                </select>
                @if($errors->has('room'))<p class="text-red-500 text-xs mt-1">{{ $errors->first('room') }}</p>@endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Term</label>
                <select name="term_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ old('term_id', $schedule->term_id) == $term->id ? 'selected' : '' }}>{{ $term->academic_year }} - {{ $term->semester }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Day</label>
                <select name="day" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="monday" {{ old('day', $schedule->day) == 'monday' ? 'selected' : '' }}>Monday</option>
                    <option value="tuesday" {{ old('day', $schedule->day) == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                    <option value="wednesday" {{ old('day', $schedule->day) == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                    <option value="thursday" {{ old('day', $schedule->day) == 'thursday' ? 'selected' : '' }}>Thursday</option>
                    <option value="friday" {{ old('day', $schedule->day) == 'friday' ? 'selected' : '' }}>Friday</option>
                    <option value="saturday" {{ old('day', $schedule->day) == 'saturday' ? 'selected' : '' }}>Saturday</option>
                    <option value="sunday" {{ old('day', $schedule->day) == 'sunday' ? 'selected' : '' }}>Sunday</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time Start</label>
                <input type="time" name="time_start" value="{{ old('time_start', $schedule->time_start) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time End</label>
                <input type="time" name="time_end" value="{{ old('time_end', $schedule->time_end) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Schedule</button>
            <a href="{{ route('admin.schedules.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection




