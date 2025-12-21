@extends('layouts.admin')
@section('title', 'Edit Section')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Section</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.sections.update', $section) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section Name</label>
                <input type="text" name="name" value="{{ old('name', $section->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course/Strand</label>
                <input type="text" name="course_strand" value="{{ old('course_strand', $section->course_strand) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year Level</label>
                <input type="text" name="year_level" value="{{ old('year_level', $section->year_level) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity', $section->capacity) }}" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adviser</label>
                <select name="adviser_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Adviser</option>
                    @foreach($advisers as $adviser)
                    <option value="{{ $adviser->id }}" {{ old('adviser_id', $section->adviser_id) == $adviser->id ? 'selected' : '' }}>{{ $adviser->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Section</button>
            <a href="{{ route('admin.sections.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection




