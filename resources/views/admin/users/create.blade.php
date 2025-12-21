@extends('layouts.admin')
@section('title', 'Create User')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New User</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                <select name="role" id="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Role</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                </select>
                @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Status</label>
                <select name="account_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="active" {{ old('account_status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('account_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Teacher Fields -->
            <div id="teacher-fields" style="display: none;" class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Teacher Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Employee ID</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('employee_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                        <input type="text" name="department" value="{{ old('department') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expertise</label>
                        <textarea name="expertise" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('expertise') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subjects Handled</label>
                        <select name="subjects[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ in_array($subject->id, old('subjects', [])) ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Student Fields -->
            <div id="student-fields" style="display: none;" class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Student Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('student_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course/Strand</label>
                        <input type="text" name="course_strand" value="{{ old('course_strand') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('course_strand')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year Level</label>
                        <input type="text" name="year_level" value="{{ old('year_level') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('year_level')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                        <select name="section" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->name }}" {{ old('section') == $section->name ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create User</button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    document.getElementById('teacher-fields').style.display = role === 'teacher' ? 'block' : 'none';
    document.getElementById('student-fields').style.display = role === 'student' ? 'block' : 'none';
});
</script>
@endsection




