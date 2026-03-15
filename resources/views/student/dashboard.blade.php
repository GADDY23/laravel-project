<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Dashboard</title>
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
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-800/60 text-white font-medium">
                    <span class="w-2 h-2 rounded-full bg-cyan-300"></span>
                    Dashboard
                </a>
                <a href="{{ route('student.timetable') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                    My Timetable
                </a>
                <a href="{{ route('student.information') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
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
                <p class="text-blue-200 text-sm">Welcome, {{ auth()->user()->name }}</p>
                <h2 class="text-2xl md:text-3xl font-bold mt-1">Class Overview</h2>
                <p class="text-blue-200 mt-2">Stay on track with your subjects, teachers, and schedule.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-sm text-slate-500">Subjects</p>
                    <p class="text-3xl font-bold text-[#0a2b5f] mt-2">{{ $schedules->unique('subject_id')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-sm text-slate-500">Teachers</p>
                    <p class="text-3xl font-bold text-[#0a2b5f] mt-2">{{ $schedules->whereNotNull('teacher_id')->unique('teacher_id')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-sm text-slate-500">Classes</p>
                    <p class="text-3xl font-bold text-[#0a2b5f] mt-2">{{ $schedules->count() }}</p>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
