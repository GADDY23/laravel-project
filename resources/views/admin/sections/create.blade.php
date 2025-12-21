@extends('layouts.admin')
@section('title', 'Create Section')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Section</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.sections.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course/Strand</label>
                <input type="text" name="course_strand" value="{{ old('course_strand') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('course_strand')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year Level</label>
                <input type="text" name="year_level" value="{{ old('year_level') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('year_level')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity') }}" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('capacity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adviser</label>
                <select name="adviser_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Adviser</option>
                    @foreach($advisers as $adviser)
                    <option value="{{ $adviser->id }}" {{ old('adviser_id') == $adviser->id ? 'selected' : '' }}>{{ $adviser->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Section</button>
            <a href="{{ route('admin.sections.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection




