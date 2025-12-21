@extends('layouts.admin')
@section('title', 'Term Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Term Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.terms.edit', $term) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.terms.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Term Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Academic Year:</dt>
                <dd>{{ $term->academic_year }}</dd>
                <dt class="font-medium">Semester:</dt>
                <dd>{{ $term->semester }}</dd>
                <dt class="font-medium">Start Date:</dt>
                <dd>{{ $term->start_date->format('F d, Y') }}</dd>
                <dt class="font-medium">End Date:</dt>
                <dd>{{ $term->end_date->format('F d, Y') }}</dd>
                <dt class="font-medium">Status:</dt>
                <dd><span class="px-2 py-1 text-xs rounded {{ $term->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $term->is_active ? 'Active' : 'Inactive' }}</span></dd>
            </dl>
        </div>
    </div>
</div>
@endsection




