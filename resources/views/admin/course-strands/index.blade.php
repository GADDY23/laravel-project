@extends('layouts.admin')
@section('title', 'Program Management')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Program Management</h1>
    <a href="{{ route('admin.course-strands.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add New</a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="flex-1 rounded-lg border-gray-300">
            <select name="type" class="rounded-lg border-gray-300">
                <option value="">All Types</option>
                <option value="shs" {{ request('type') == 'shs' ? 'selected' : '' }}>SHS</option>
                <option value="college" {{ request('type') == 'college' ? 'selected' : '' }}>College</option>
            </select>
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($courseStrands as $item)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ strtoupper($item->type) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $item->description ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm font-medium">
                        <a href="{{ route('admin.course-strands.show', $item) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="{{ route('admin.course-strands.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <form action="{{ route('admin.course-strands.destroy', $item) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No course/strands found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $courseStrands->links() }}</div>
</div>
@endsection
