@extends('layouts.admin')
@section('title', 'Create Subject')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Subject</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.subjects.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject Code</label>
                <input type="text" name="code" value="{{ old('code') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year Level</label>
                <input type="text" name="year_level" value="{{ old('year_level') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Semester</label>
                <input type="text" name="semester" value="{{ old('semester') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course/Strand</label>
                <input type="text" name="course_strand" value="{{ old('course_strand') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hours</label>
                <input type="number" name="hours" value="{{ old('hours') }}" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Required Room Type</label>
                <select name="required_room_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="any" {{ old('required_room_type') == 'any' ? 'selected' : '' }}>Any</option>
                    <option value="lecture" {{ old('required_room_type') == 'lecture' ? 'selected' : '' }}>Lecture</option>
                    <option value="laboratory" {{ old('required_room_type') == 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                    <option value="computer_lab" {{ old('required_room_type') == 'computer_lab' ? 'selected' : '' }}>Computer Lab</option>
                    <option value="science_lab" {{ old('required_room_type') == 'science_lab' ? 'selected' : '' }}>Science Lab</option>
                    <option value="workshop" {{ old('required_room_type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Subject</button>
            <a href="{{ route('admin.subjects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection




