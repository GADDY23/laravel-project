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
                        <select name="subjects[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ $user->subjects->contains($subject->id) ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course/Strand</label>
                        <input type="text" name="course_strand" value="{{ old('course_strand', $user->course_strand) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year Level</label>
                        <input type="text" name="year_level" value="{{ old('year_level', $user->year_level) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    document.getElementById('teacher-fields').style.display = role === 'teacher' ? 'block' : 'none';
    document.getElementById('student-fields').style.display = role === 'student' ? 'block' : 'none';
});
</script>
@endsection




