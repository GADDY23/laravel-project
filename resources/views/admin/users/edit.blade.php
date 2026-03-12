@extends('layouts.admin')
@section('title', 'Edit User')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password (leave blank to keep current)</label>
                <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                <select name="role" id="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="teacher" {{ old('role', $user->role) == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Status</label>
                <select name="account_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="active" {{ old('account_status', $user->account_status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('account_status', $user->account_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div id="teacher-fields" style="display: {{ $user->role == 'teacher' ? 'block' : 'none' }};" class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Teacher Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Employee ID</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                        <input type="text" name="department" value="{{ old('department', $user->department) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expertise</label>
                        <textarea name="expertise" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('expertise', $user->expertise) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subjects Handled</label>
                        @php
                            $selectedSubjects = old('subjects', $user->subjects->pluck('id')->all());
                        @endphp
                        <div class="mt-1">
                            <input
                                type="text"
                                id="subject-search"
                                placeholder="Search subjects..."
                                class="w-full rounded-md border-gray-300 shadow-sm px-3 py-2 text-sm"
                            />
                            <div id="subject-options" class="mt-2 max-h-52 overflow-y-auto rounded-md border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                                @foreach($subjects as $subject)
                                <label class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="subjects[]"
                                        value="{{ $subject->id }}"
                                        {{ in_array($subject->id, (array) $selectedSubjects) ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ $subject->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @error('subjects')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div id="student-fields" style="display: {{ $user->role == 'student' ? 'block' : 'none' }};" class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Student Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course Type</label>
                        <select name="course_type" id="course_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select Type</option>
                            <option value="shs" {{ old('course_type', optional($courseStrands['shs'] ?? collect())->contains('name', $user->course_strand) ? 'shs' : null) == 'shs' ? 'selected' : '' }}>SHS</option>
                            <option value="college" {{ old('course_type', optional($courseStrands['college'] ?? collect())->contains('name', $user->course_strand) ? 'college' : null) == 'college' ? 'selected' : '' }}>College</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course/Strand</label>
                        <select name="course_strand" id="course_strand" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year Level</label>
                        <select name="year_level" id="year_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                        <select name="section" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->name }}" {{ old('section', $user->section) == $section->name ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update User</button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>

<script>
const strandsByType = @json($courseStrands);
const roleSelect = document.getElementById('role');
const teacherFields = document.getElementById('teacher-fields');
const studentFields = document.getElementById('student-fields');
const courseType = document.getElementById('course_type');
const courseStrand = document.getElementById('course_strand');
const yearLevel = document.getElementById('year_level');
const previousStrand = @json(old('course_strand', $user->course_strand));
const previousYearLevel = @json(old('year_level', $user->year_level));

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

function toggleRoleFields() {
    const role = roleSelect.value;
    teacherFields.style.display = role === 'teacher' ? 'block' : 'none';
    studentFields.style.display = role === 'student' ? 'block' : 'none';
}

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

roleSelect.addEventListener('change', toggleRoleFields);
courseType.addEventListener('change', () => {
    renderCourseStrands();
    renderYearLevels();
});

// Searchable multi-select for Subjects Handled
const subjectSearch = document.getElementById('subject-search');
const subjectOptions = document.getElementById('subject-options');
if (subjectSearch && subjectOptions) {
    subjectSearch.addEventListener('input', () => {
        const query = subjectSearch.value.toLowerCase();
        subjectOptions.querySelectorAll('label').forEach((label) => {
            const text = label.textContent.toLowerCase();
            label.style.display = text.includes(query) ? 'flex' : 'none';
        });
    });
}

toggleRoleFields();
renderCourseStrands();
renderYearLevels();
</script>
@endsection
