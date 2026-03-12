@extends('layouts.admin')
@section('title', 'Course/Strand Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Program Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.course-strands.edit', $courseStrand) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.course-strands.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <dl class="space-y-2">
        <dt class="font-medium">Name:</dt>
        <dd>{{ $courseStrand->name }}</dd>
        <dt class="font-medium">Type:</dt>
        <dd>{{ strtoupper($courseStrand->type) }}</dd>
        <dt class="font-medium">Description:</dt>
        <dd>{{ $courseStrand->description ?? 'N/A' }}</dd>
    </dl>
</div>
@endsection
