@extends('layouts.admin')
@section('title', 'Edit Subject')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Subject</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject Name</label>
                <input type="text" name="name" value="{{ old('name', $subject->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject Code</label>
                <input type="text" name="code" value="{{ old('code', $subject->code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lec Unit</label>
                <input type="number" step="1" name="lec_unit" value="{{ old('lec_unit', $subject->lec_unit ?? 0) }}" required min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('lec_unit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lab Unit</label>
                <input type="number" step="1" name="lab_unit" value="{{ old('lab_unit', $subject->lab_unit ?? 0) }}" required min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('lab_unit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Required Room Type</label>
                <select name="required_room_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="lecture" {{ old('required_room_type', $subject->required_room_type) == 'lecture' ? 'selected' : '' }}>Lecture</option>
                    <option value="computer_lab" {{ old('required_room_type', $subject->required_room_type) == 'computer_lab' ? 'selected' : '' }}>Computer Lab</option>
                    <option value="chemistry_lab" {{ old('required_room_type', $subject->required_room_type) == 'chemistry_lab' ? 'selected' : '' }}>Chemistry Lab</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="active" {{ old('status', $subject->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $subject->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $subject->description) }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Subject</button>
            <a href="{{ route('admin.subjects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection
