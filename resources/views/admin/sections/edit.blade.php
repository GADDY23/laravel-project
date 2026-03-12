@extends('layouts.admin')
@section('title', 'Edit Section')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Section</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.sections.update', $section) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section Name</label>
                <input type="text" name="name" value="{{ old('name', $section->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Program Type</label>
                <select name="course_type" id="course_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Type</option>
                    <option value="shs" {{ old('course_type', optional($courseStrands['shs'] ?? collect())->contains('name', $section->course_strand) ? 'shs' : null) == 'shs' ? 'selected' : '' }}>SHS</option>
                    <option value="college" {{ old('course_type', optional($courseStrands['college'] ?? collect())->contains('name', $section->course_strand) ? 'college' : null) == 'college' ? 'selected' : '' }}>College</option>
                </select>
                @error('course_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Program</label>
                <select name="course_strand" id="course_strand" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                @error('course_strand')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year Level</label>
                <select name="year_level" id="year_level" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                @error('year_level')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity', $section->capacity) }}" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Term</label>
                <select name="term_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ old('term_id', $section->term_id) == $term->id ? 'selected' : '' }}>
                            {{ $term->term_code }} - {{ $term->academic_year }} - {{ $term->semester }}
                        </option>
                    @endforeach
                </select>
                @error('term_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Curriculum</label>
                <select name="curriculum_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Curriculum</option>
                    @foreach($curricula as $curriculum)
                        <option value="{{ $curriculum->id }}" {{ old('curriculum_id', $section->curriculum_id) == $curriculum->id ? 'selected' : '' }}>
                            {{ $curriculum->curriculum_code }}
                        </option>
                    @endforeach
                </select>
                @error('curriculum_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="active" {{ old('status', $section->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $section->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adviser</label>
                <select name="adviser_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Adviser</option>
                    @foreach($advisers as $adviser)
                    <option value="{{ $adviser->id }}" {{ old('adviser_id', $section->adviser_id) == $adviser->id ? 'selected' : '' }}>{{ $adviser->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Section</button>
            <a href="{{ route('admin.sections.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>

<script>
const strandsByType = @json($courseStrands);
const courseType = document.getElementById('course_type');
const courseStrand = document.getElementById('course_strand');
const yearLevel = document.getElementById('year_level');
const previousStrand = @json(old('course_strand', $section->course_strand));
const previousYearLevel = @json(old('year_level', $section->year_level));

const yearLevelsByType = {
    shs: [
        { value: 'grade_11', label: 'Grade 11' },
        { value: 'grade_12', label: 'Grade 12' },
    ],
    college: [
        { value: '1st_year', label: '1st Year' },
        { value: '2nd_year', label: '2nd Year' },
        { value: '3rd_year', label: '3rd Year' },
        { value: '4th_year', label: '4th Year' },
    ],
};

function renderCourseStrands() {
    const selectedType = courseType.value;
    const strands = strandsByType[selectedType] ?? [];

    courseStrand.innerHTML = '<option value="">Select Course/Strand</option>';
    strands.forEach((strand) => {
        const option = document.createElement('option');
        option.value = strand.name;
        option.textContent = strand.name;
        if (previousStrand && previousStrand === strand.name) {
            option.selected = true;
        }
        courseStrand.appendChild(option);
    });
}

function renderYearLevels() {
    const selectedType = courseType.value;
    const levels = yearLevelsByType[selectedType] ?? [];

    yearLevel.innerHTML = '<option value="">Select Year Level</option>';
    levels.forEach((level) => {
        const option = document.createElement('option');
        option.value = level.value;
        option.textContent = level.label;
        if (previousYearLevel && previousYearLevel === level.value) {
            option.selected = true;
        }
        yearLevel.appendChild(option);
    });
}

courseType.addEventListener('change', () => {
    renderCourseStrands();
    renderYearLevels();
});

renderCourseStrands();
renderYearLevels();
</script>
@endsection
