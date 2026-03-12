@extends('layouts.admin')
@section('title', 'Terms Management')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Terms Management</h1>
    <a href="{{ route('admin.terms.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add New Term</a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Availability</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($terms as $term)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $term->term_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $term->academic_year }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $term->semester }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($term->status === 'active')
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-green-100 text-green-700">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if(($term->is_enabled ?? true))
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-700">
                                Enabled
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700">
                                Disabled
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.terms.show', $term) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="{{ route('admin.terms.edit', $term) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <form action="{{ route('admin.terms.update-status', $term) }}" method="POST" class="inline mr-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_enabled" value="{{ ($term->is_enabled ?? true) ? '0' : '1' }}">
                            <button type="submit" class="{{ ($term->is_enabled ?? true) ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }}">
                                {{ ($term->is_enabled ?? true) ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.terms.destroy', $term) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No terms found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $terms->links() }}</div>
</div>

@endsection
