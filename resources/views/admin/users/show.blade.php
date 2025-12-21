@extends('layouts.admin')
@section('title', 'User Details')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Details</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Name:</dt>
                <dd>{{ $user->name }}</dd>
                <dt class="font-medium">Email:</dt>
                <dd>{{ $user->email }}</dd>
                <dt class="font-medium">Username:</dt>
                <dd>{{ $user->username }}</dd>
                <dt class="font-medium">Role:</dt>
                <dd><span class="px-2 py-1 text-xs rounded {{ $user->role == 'admin' ? 'bg-red-100 text-red-800' : ($user->role == 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">{{ ucfirst($user->role) }}</span></dd>
                <dt class="font-medium">Status:</dt>
                <dd><span class="px-2 py-1 text-xs rounded {{ $user->account_status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($user->account_status) }}</span></dd>
            </dl>
        </div>

        @if($user->role == 'teacher')
        <div>
            <h3 class="text-lg font-semibold mb-4">Teacher Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Employee ID:</dt>
                <dd>{{ $user->employee_id }}</dd>
                <dt class="font-medium">Department:</dt>
                <dd>{{ $user->department ?? 'N/A' }}</dd>
                <dt class="font-medium">Expertise:</dt>
                <dd>{{ $user->expertise ?? 'N/A' }}</dd>
                <dt class="font-medium">Subjects Handled:</dt>
                <dd>
                    @if($user->subjects->count() > 0)
                        <ul class="list-disc list-inside">
                            @foreach($user->subjects as $subject)
                            <li>{{ $subject->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        None
                    @endif
                </dd>
            </dl>
        </div>
        @endif

        @if($user->role == 'student')
        <div>
            <h3 class="text-lg font-semibold mb-4">Student Information</h3>
            <dl class="space-y-2">
                <dt class="font-medium">Student ID:</dt>
                <dd>{{ $user->student_id }}</dd>
                <dt class="font-medium">Course/Strand:</dt>
                <dd>{{ $user->course_strand }}</dd>
                <dt class="font-medium">Year Level:</dt>
                <dd>{{ $user->year_level }}</dd>
                <dt class="font-medium">Section:</dt>
                <dd>{{ $user->section ?? 'N/A' }}</dd>
            </dl>
        </div>
        @endif
    </div>

    @if($user->schedules->count() > 0)
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">Schedules</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Section</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($user->schedules as $schedule)
                    <tr>
                        <td class="px-6 py-4">{{ $schedule->subject->name }}</td>
                        <td class="px-6 py-4">{{ $schedule->section->name }}</td>
                        <td class="px-6 py-4">{{ ucfirst($schedule->day) }}</td>
                        <td class="px-6 py-4">{{ $schedule->time_start }} - {{ $schedule->time_end }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection




