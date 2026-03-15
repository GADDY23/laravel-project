<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isTeacher()) {
            return $this->teacherDashboard($user);
        } else {
            return $this->studentDashboard($user);
        }
    }

    private function adminDashboard()
    {
        $activeTerm = Term::active()->first();

        $stats = [
            'total_users' => User::count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_rooms' => Room::available()->count(),
            'total_sections' => Section::active()->count(),
            'total_subjects' => Subject::active()->count(),
            'total_schedules' => Schedule::query()
                ->whereHas('subject', fn($q) => $q->active())
                ->whereHas('section', fn($q) => $q->active())
                ->whereHas('room', fn($q) => $q->available())
                ->whereHas('term', fn($q) => $q->active())
                ->count(),
            'active_term' => $activeTerm,
        ];

        $recentSchedulesQuery = Schedule::with(['teacher', 'subject', 'section', 'room', 'term'])
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->whereHas('room', fn($q) => $q->available())
            ->whereHas('term', fn($q) => $q->active());

        if (Schema::hasColumn('schedules', 'is_published')) {
            $recentSchedulesQuery->where('is_published', true);
        }

        $recentSchedules = $recentSchedulesQuery->latest()
            ->take(10)
            ->get();

        $timetableSchedulesQuery = Schedule::with(['teacher', 'subject', 'section', 'room', 'term'])
            ->when($activeTerm, fn($q) => $q->where('term_id', $activeTerm->id))
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->whereHas('room', fn($q) => $q->available())
            ->whereHas('term', fn($q) => $q->active());

        if (Schema::hasColumn('schedules', 'is_published')) {
            $timetableSchedulesQuery->where('is_published', true);
        }

        $timetableSchedules = $timetableSchedulesQuery->orderBy('day')
            ->orderBy('time_start')
            ->get();

        return view('dashboard', compact('stats', 'recentSchedules', 'timetableSchedules', 'activeTerm'));
    }

    private function teacherDashboard($user)
    {
        $activeTerm = Term::active()->first();
        
        $schedulesQuery = Schedule::where('teacher_id', $user->id)
            ->when($activeTerm, fn($q) => $q->where('term_id', $activeTerm->id))
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->where(function ($q) {
                $q->whereNull('room_id')
                    ->orWhereHas('room', fn($roomQuery) => $roomQuery->available());
            })
            ->whereHas('term', fn($q) => $q->enabled())
            ->with(['subject', 'section', 'room', 'term']);

        if (Schema::hasColumn('schedules', 'is_published')) {
            $schedulesQuery->where('is_published', true);
        }

        $schedules = $schedulesQuery->orderBy('day')
            ->orderBy('time_start')
            ->get();

        return view('teacher.dashboard', compact('schedules', 'activeTerm'));
    }

    public function teacherTimetable(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->isTeacher(), 403);

        $activeTerm = Term::active()->first();
        $termId = $request->get('term_id') ?: $activeTerm?->id;
        $schedulesQuery = Schedule::where('teacher_id', $user->id)
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->where(function ($q) {
                $q->whereNull('room_id')
                    ->orWhereHas('room', fn($roomQuery) => $roomQuery->available());
            })
            ->whereHas('term', fn($q) => $q->enabled())
            ->with(['subject', 'section', 'room', 'term']);

        if (Schema::hasColumn('schedules', 'is_published')) {
            $schedulesQuery->where('is_published', true);
        }

        $schedules = $schedulesQuery->orderBy('day')
            ->orderBy('time_start')
            ->get();

        return view('teacher.timetable', compact('schedules', 'activeTerm'));
    }

    public function teacherInformation()
    {
        $user = Auth::user();
        abort_unless($user?->isTeacher(), 403);

        $activeTerm = Term::active()->first();

        return view('teacher.information', compact('activeTerm'));
    }

    private function studentDashboard($user)
    {
        $activeTerm = Term::active()->first();
        
        $section = Section::active()->where('name', $user->section)->first();
        
        $schedulesQuery = Schedule::where('section_id', $section?->id ?? 0)
            ->when($activeTerm, fn($q) => $q->where('term_id', $activeTerm->id))
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->where(function ($q) {
                $q->whereNull('room_id')
                    ->orWhereHas('room', fn($roomQuery) => $roomQuery->available());
            })
            ->whereHas('term', fn($q) => $q->enabled())
            ->with(['teacher', 'subject', 'section', 'room', 'term']);

        if (Schema::hasColumn('schedules', 'is_published')) {
            $schedulesQuery->where('is_published', true);
        }

        $schedules = $schedulesQuery->orderBy('day')
            ->orderBy('time_start')
            ->get();

        return view('student.dashboard', compact('schedules', 'activeTerm', 'section'));
    }

    public function studentTimetable(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->isStudent(), 403);

        $activeTerm = Term::active()->first();
        $termId = $request->get('term_id') ?: $activeTerm?->id;
        $section = Section::active()->where('name', $user->section)->first();

        $schedulesQuery = Schedule::where('section_id', $section?->id ?? 0)
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->where(function ($q) {
                $q->whereNull('room_id')
                    ->orWhereHas('room', fn($roomQuery) => $roomQuery->available());
            })
            ->whereHas('term', fn($q) => $q->enabled())
            ->with(['teacher', 'subject', 'section', 'room', 'term']);

        if (Schema::hasColumn('schedules', 'is_published')) {
            $schedulesQuery->where('is_published', true);
        }

        $schedules = $schedulesQuery->orderBy('day')
            ->orderBy('time_start')
            ->get();

        return view('student.timetable', compact('schedules', 'activeTerm', 'section'));
    }

    public function studentInformation()
    {
        $user = Auth::user();
        abort_unless($user?->isStudent(), 403);

        $activeTerm = Term::active()->first();
        $section = Section::active()->where('name', $user->section)->first();

        return view('student.information', compact('activeTerm', 'section'));
    }
}
