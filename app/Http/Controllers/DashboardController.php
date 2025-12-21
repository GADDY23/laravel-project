<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $stats = [
            'total_users' => User::count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_rooms' => Room::count(),
            'total_sections' => Section::count(),
            'total_subjects' => Subject::count(),
            'total_schedules' => Schedule::count(),
            'active_term' => Term::where('is_active', true)->first(),
        ];

        $recentSchedules = Schedule::with(['teacher', 'subject', 'section', 'room'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact('stats', 'recentSchedules'));
    }

    private function teacherDashboard($user)
    {
        $activeTerm = Term::where('is_active', true)->first();
        
        $schedules = Schedule::where('teacher_id', $user->id)
            ->when($activeTerm, fn($q) => $q->where('term_id', $activeTerm->id))
            ->with(['subject', 'section', 'room', 'term'])
            ->orderBy('day')
            ->orderBy('time_start')
            ->get();

        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('teacher.dashboard', compact('schedules', 'notifications', 'activeTerm'));
    }

    private function studentDashboard($user)
    {
        $activeTerm = Term::where('is_active', true)->first();
        
        $section = Section::where('name', $user->section)->first();
        
        $schedules = Schedule::where('section_id', $section?->id ?? 0)
            ->when($activeTerm, fn($q) => $q->where('term_id', $activeTerm->id))
            ->with(['teacher', 'subject', 'section', 'room', 'term'])
            ->orderBy('day')
            ->orderBy('time_start')
            ->get();

        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('student.dashboard', compact('schedules', 'notifications', 'activeTerm', 'section'));
    }
}
