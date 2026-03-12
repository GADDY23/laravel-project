@extends('layouts.admin')
@section('title', 'Section Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Section Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.sections.edit', $section) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.sections.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Section Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Name:</dt>
                <dd>{{ $section->name }}</dd>
                <dt class="font-medium">Program:</dt>
                <dd>{{ $section->course_strand }}</dd>
                <dt class="font-medium">Year Level:</dt>
                <dd>{{ ucfirst(str_replace('_', ' ', $section->year_level)) }}</dd>
                <dt class="font-medium">Term:</dt>
                <dd>{{ $section->term?->term_code ?? 'N/A' }}</dd>
                <dt class="font-medium">Status:</dt>
                <dd>{{ ucfirst($section->status) }}</dd>
                <dt class="font-medium">Capacity:</dt>
                <dd>{{ $section->capacity }}</dd>
                <dt class="font-medium">Adviser:</dt>
                <dd>{{ $section->adviser->name ?? 'N/A' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection



