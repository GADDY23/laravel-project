<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Information</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen md:flex">
        <aside class="w-full md:w-72 md:min-h-screen bg-gradient-to-b from-[#041a3d] to-[#0b336f] text-blue-100 flex flex-col">
            <div class="px-6 py-7 border-b border-blue-900/60">
                <div class="flex flex-col items-center gap-3 text-center">
                    <img src="{{ asset('aclclogo.png') }}" alt="ACLC logo" class="h-10 w-auto object-contain">
                    <div>
                        <p class="text-[11px] tracking-[0.2em] uppercase text-blue-300">Student Panel</p>
                        <h1 class="text-xl font-semibold mt-1 text-white truncate">{{ auth()->user()->name }}</h1>
                    </div>
                </div>
                @if($section)
                    <p class="text-xs text-blue-300 mt-3 text-center">Section {{ $section->name }}</p>
                @endif
            </div>

            <nav class="p-4 space-y-2 flex-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                    Dashboard
                </a>
                <a href="{{ route('student.timetable') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                    My Timetable
                </a>
                <a href="{{ route('student.information') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-800/60 text-white font-medium">
                    <span class="w-2 h-2 rounded-full bg-cyan-300"></span>
                    Information
                </a>
            </nav>

            <div class="p-4 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium transition">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 p-4 md:p-8">
            <div class="bg-gradient-to-r from-[#0a2b5f] via-[#0f3b84] to-[#1350a8] text-white rounded-2xl p-6 shadow-lg">
                <p class="text-blue-200 text-sm">Student Information</p>
                <h2 class="text-2xl md:text-3xl font-bold mt-1">Information</h2>
                <p class="text-blue-200 mt-2">Your account details are shown below.</p>
            </div>

            @php
                $user = auth()->user();
            @endphp
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm mt-6">
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-700 space-y-2">
                    <p class="text-sm font-semibold text-slate-900">Student Info</p>
                    <div>
                        <p class="text-slate-500">Name</p>
                        <p class="font-medium text-slate-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Email</p>
                        <p class="font-medium text-slate-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Username</p>
                        <p class="font-medium text-slate-900">{{ $user->username ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Student ID</p>
                        <p class="font-medium text-slate-900">{{ $user->student_id ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Course / Strand</p>
                        <p class="font-medium text-slate-900">{{ $user->course_strand ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Year Level</p>
                        <p class="font-medium text-slate-900">{{ $user->year_level ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Section</p>
                        <p class="font-medium text-slate-900">{{ $user->section ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Account Status</p>
                        <p class="font-medium text-slate-900">{{ ucfirst($user->account_status ?? 'active') }}</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
