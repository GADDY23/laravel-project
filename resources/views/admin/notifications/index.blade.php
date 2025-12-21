@extends('layouts.admin')
@section('title', 'Notifications Management')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications Management</h1>
    <a href="{{ route('admin.notifications.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Notification</a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($notifications as $notification)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $notification->user->name ?? 'All Users' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded 
                            {{ $notification->type == 'error' ? 'bg-red-100 text-red-800' : 
                               ($notification->type == 'warning' ? 'bg-yellow-100 text-yellow-800' : 
                               ($notification->type == 'success' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800')) }}">
                            {{ ucfirst($notification->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $notification->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ Str::limit($notification->message, 50) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded {{ $notification->is_read ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $notification->is_read ? 'Read' : 'Unread' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No notifications found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $notifications->links() }}</div>
</div>
@endsection




