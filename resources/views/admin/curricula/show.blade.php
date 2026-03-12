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
                <dd>{{ $curriculum->curriculum_code }}</dd>
                <dt class="font-medium">School Year Start:</dt>
                <dd>{{ $curriculum->school_year_start ?? '-' }}</dd>
                <dt class="font-medium">Program Type:</dt>
                <dd>{{ strtoupper($curriculum->course_type ?? '-') }}</dd>
                <dt class="font-medium">Curriculum Type:</dt>
                <dd>{{ ucfirst($curriculum->curriculum_type ?? '-') }}</dd>
                <dt class="font-medium">Program:</dt>
                <dd>{{ $curriculum->course_strand }}</dd>
            </dl>
        </div>
    </div>

    @if($curriculum->subjects->count() > 0)
        @php
            $yearLabels = [
                '1st_year' => '1st Year',
                '2nd_year' => '2nd Year',
                '3rd_year' => '3rd Year',
                '4th_year' => '4th Year',
            ];
            $periodWord = ($curriculum->curriculum_type ?? 'semestral') === 'trimestral' ? 'Tri' : 'Sem';
        @endphp
        <div class="mt-8 space-y-6">
            @foreach($groupedRows as $groupKey => $subjects)
                @php
                    [$yearKey, $periodNumber] = explode('|', $groupKey);
                    $yearTitle = $yearLabels[$yearKey] ?? ucfirst(str_replace('_', ' ', $yearKey));
                    $periodTitle = $periodNumber . ' ' . $periodWord;
                @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-3">{{ $yearTitle }} {{ $periodTitle }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prerequisite</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($subjects as $subject)
                                    <tr>
                                        <td class="px-6 py-4">{{ $subject->code ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $subject->name }}</td>
                                        <td class="px-6 py-4">{{ $subject->description ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $subject->pivot->prerequisite ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

