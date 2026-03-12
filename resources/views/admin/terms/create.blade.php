@extends('layouts.admin')
@section('title', 'Create Term')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Term</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.terms.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Term Code</label>
                <input type="text" name="term_code" value="{{ old('term_code') }}" required placeholder="e.g., AY2425-1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('term_code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Academic Year</label>
                <select name="academic_year" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Academic Year</option>
                    @foreach($academicYearOptions as $academicYearOption)
                        <option value="{{ $academicYearOption }}" {{ old('academic_year') === $academicYearOption ? 'selected' : '' }}>
                            {{ $academicYearOption }}
                        </option>
                    @endforeach
                </select>
                @error('academic_year')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Semester</label>
                <select name="semester" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Semester</option>
                    @foreach($semesterOptions as $semesterOption)
                        <option value="{{ $semesterOption }}" {{ old('semester') === $semesterOption ? 'selected' : '' }}>
                            {{ $semesterOption }}
                        </option>
                    @endforeach
                </select>
                @error('semester')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="inactive" {{ old('status', 'inactive') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Term</button>
            <a href="{{ route('admin.terms.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection


