@extends('layouts.admin')
@section('title', 'Create Notification')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Notification</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.notifications.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User (leave empty for all users)</label>
                <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">All Users</option>
                    @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info</option>
                    <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                <textarea name="message" required rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Notification</button>
            <a href="{{ route('admin.notifications.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection




