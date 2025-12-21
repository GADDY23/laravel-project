@extends('layouts.admin')
@section('title', 'Create Room')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Room</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.rooms.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Room Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity') }}" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('capacity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Building</label>
                <input type="text" name="building" value="{{ old('building') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Floor</label>
                <input type="text" name="floor" value="{{ old('floor') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="lecture" {{ old('type') == 'lecture' ? 'selected' : '' }}>Lecture</option>
                    <option value="laboratory" {{ old('type') == 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                    <option value="computer_lab" {{ old('type') == 'computer_lab' ? 'selected' : '' }}>Computer Lab</option>
                    <option value="science_lab" {{ old('type') == 'science_lab' ? 'selected' : '' }}>Science Lab</option>
                    <option value="workshop" {{ old('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Room</button>
            <a href="{{ route('admin.rooms.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection




