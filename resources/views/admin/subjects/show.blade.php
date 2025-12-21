@extends('layouts.admin')
@section('title', 'Subject Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subject Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.subjects.edit', $subject) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.subjects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Subject Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Name:</dt>
                <dd>{{ $subject->name }}</dd>
                <dt class="font-medium">Code:</dt>
                <dd>{{ $subject->code ?? 'N/A' }}</dd>
                <dt class="font-medium">Year Level:</dt>
                <dd>{{ $subject->year_level }}</dd>
                <dt class="font-medium">Semester:</dt>
                <dd>{{ $subject->semester }}</dd>
                <dt class="font-medium">Course/Strand:</dt>
                <dd>{{ $subject->course_strand }}</dd>
                <dt class="font-medium">Hours:</dt>
                <dd>{{ $subject->hours }}</dd>
                <dt class="font-medium">Required Room Type:</dt>
                <dd>{{ ucfirst(str_replace('_', ' ', $subject->required_room_type)) }}</dd>
                <dt class="font-medium">Description:</dt>
                <dd>{{ $subject->description ?? 'N/A' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection




