@extends('layouts.admin')
@section('title', 'Edit Term')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Term</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.terms.update', $term) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Term Code</label>
                <input type="text" name="term_code" value="{{ old('term_code', $term->term_code) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Academic Year</label>
                <select name="academic_year" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Academic Year</option>
                    @foreach($academicYearOptions as $academicYearOption)
                        <option value="{{ $academicYearOption }}" {{ old('academic_year', $term->academic_year) === $academicYearOption ? 'selected' : '' }}>
                            {{ $academicYearOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Semester</label>
                <select name="semester" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Semester</option>
                    @foreach($semesterOptions as $semesterOption)
                        <option value="{{ $semesterOption }}" {{ old('semester', $term->semester) === $semesterOption ? 'selected' : '' }}>
                            {{ $semesterOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="inactive" {{ old('status', $term->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="active" {{ old('status', $term->status) == 'active' ? 'selected' : '' }}>Active</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Term</button>
            <a href="{{ route('admin.terms.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection


