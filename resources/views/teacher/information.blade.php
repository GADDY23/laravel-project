<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teacher Information</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen md:flex">
        <aside class="w-full md:w-72 md:min-h-screen bg-gradient-to-b from-[#041b42] to-[#072d66] text-blue-100 flex flex-col">
            <div class="px-6 py-7 border-b border-blue-900/60">
                <div class="flex flex-col items-center gap-3 text-center">
                    <img src="{{ asset('aclclogo.png') }}" alt="ACLC logo" class="h-10 w-auto object-contain">
                    <div>
                        <h1 class="text-lg font-semibold tracking-wide text-white">School Scheduling</h1>
                        <p class="text-[11px] tracking-[0.2em] uppercase text-blue-300">Teacher Panel</p>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm font-medium text-blue-100 truncate">{{ auth()->user()->name }}</p>
                    @if($activeTerm)
                        <p class="text-xs text-blue-300 mt-1">{{ $activeTerm->term_code }} | {{ $activeTerm->academic_year }}</p>
                    @endif
                </div>
            </div>

            <nav class="p-4 space-y-2 flex-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                    Dashboard
                </a>
                <a href="{{ route('teacher.timetable') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-800/40 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-300"></span>
                    My Timetable
                </a>
                <a href="{{ route('teacher.information') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-800/60 text-white font-medium">
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
            <div class="bg-gradient-to-r from-[#0a2a5f] via-[#0d3478] to-[#0e3f90] text-white rounded-2xl p-6 shadow-lg">
                <p class="text-blue-200 text-sm">Teacher Information</p>
                <h2 class="text-2xl md:text-3xl font-bold mt-1">Information</h2>
                <p class="text-blue-200 mt-2">Your account details are shown below.</p>
            </div>

            @php
                $user = auth()->user();
            @endphp
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm mt-6">
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-700 space-y-2">
                    <p class="text-sm font-semibold text-slate-900">Teacher Info</p>
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
                        <p class="text-slate-500">Employee ID</p>
                        <p class="font-medium text-slate-900">{{ $user->employee_id ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Department</p>
                        <p class="font-medium text-slate-900">{{ $user->department ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Expertise</p>
                        <p class="font-medium text-slate-900">{{ $user->expertise ?? '-' }}</p>
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
