@extends('layouts.admin')
@section('title', 'Curriculum Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Curriculum Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.curricula.edit', $curriculum) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.curricula.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Curriculum Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Code:</dt>
                <dd>{{ $curriculum->code }}</dd>
                <dt class="font-medium">Name:</dt>
                <dd>{{ $curriculum->name }}</dd>
                <dt class="font-medium">Strand/Program:</dt>
                <dd>{{ $curriculum->strand_program }}</dd>
                <dt class="font-medium">Year Level:</dt>
                <dd>{{ $curriculum->year_level }}</dd>
                <dt class="font-medium">Semester:</dt>
                <dd>{{ $curriculum->semester }}</dd>
            </dl>
        </div>
    </div>
    @if($curriculum->subjects->count() > 0)
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">Subjects</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($curriculum->subjects as $subject)
                    <tr>
                        <td class="px-6 py-4">{{ $subject->code ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $subject->name }}</td>
                        <td class="px-6 py-4">{{ $subject->hours }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection




