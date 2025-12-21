<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Room;
use App\Models\Term;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['teacher', 'subject', 'section', 'room', 'term']);

        // Filter by teacher
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by section
        if ($request->has('section_id') && $request->section_id) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by room
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by term
        if ($request->has('term_id') && $request->term_id) {
            $query->where('term_id', $request->term_id);
        }

        $schedules = $query->orderBy('day')->orderBy('time_start')->paginate(20);

        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')->where('account_status', 'active')->get();
        $subjects = Subject::all();
        $sections = Section::all();
        $rooms = Room::all();
        $terms = Term::all();

        return view('admin.schedules.create', compact('teachers', 'subjects', 'sections', 'rooms', 'terms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'room_id' => 'required|exists:rooms,id',
            'term_id' => 'required|exists:terms,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        // Check for conflicts
        $conflicts = $this->checkConflicts($validated);

        if (!empty($conflicts)) {
            return back()->withErrors($conflicts)->withInput();
        }

        $schedule = Schedule::create($validated);

        // Create notifications for teacher and students
        $this->notifyScheduleCreated($schedule);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['teacher', 'subject', 'section', 'room', 'term']);
        return view('admin.schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule)
    {
        $teachers = User::where('role', 'teacher')->where('account_status', 'active')->get();
        $subjects = Subject::all();
        $sections = Section::all();
        $rooms = Room::all();
        $terms = Term::all();

        return view('admin.schedules.edit', compact('schedule', 'teachers', 'subjects', 'sections', 'rooms', 'terms'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        // Handle _method override for PUT requests
        if ($request->has('_method')) {
            $request->merge(['_method' => $request->input('_method')]);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'room_id' => 'required|exists:rooms,id',
            'term_id' => 'required|exists:terms,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        // Check for conflicts (excluding current schedule)
        $conflicts = $this->checkConflicts($validated, $schedule->id);

        if (!empty($conflicts)) {
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $conflicts
                ], 422);
            }
            return back()->withErrors($conflicts)->withInput();
        }

        $schedule->update($validated);

        // Create notifications for schedule update
        $this->notifyScheduleUpdated($schedule);

        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully.',
                'schedule' => $schedule->load(['teacher', 'subject', 'section', 'room'])
            ]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        // Create notifications for schedule deletion
        $this->notifyScheduleDeleted($schedule);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    private function checkConflicts(array $data, $excludeScheduleId = null)
    {
        $conflicts = [];

        $timeStart = Carbon::parse($data['time_start']);
        $timeEnd = Carbon::parse($data['time_end']);

        // Check teacher conflict
        $teacherConflicts = Schedule::where('teacher_id', $data['teacher_id'])
            ->where('day', $data['day'])
            ->where('term_id', $data['term_id'])
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->get()
            ->filter(function ($schedule) use ($timeStart, $timeEnd) {
                $schedStart = Carbon::parse($schedule->time_start);
                $schedEnd = Carbon::parse($schedule->time_end);
                return $timeStart->lt($schedEnd) && $timeEnd->gt($schedStart);
            });

        if ($teacherConflicts->isNotEmpty()) {
            $conflicts['teacher'] = 'Teacher has a conflicting schedule at this time.';
        }

        // Check room conflict
        $roomConflicts = Schedule::where('room_id', $data['room_id'])
            ->where('day', $data['day'])
            ->where('term_id', $data['term_id'])
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->get()
            ->filter(function ($schedule) use ($timeStart, $timeEnd) {
                $schedStart = Carbon::parse($schedule->time_start);
                $schedEnd = Carbon::parse($schedule->time_end);
                return $timeStart->lt($schedEnd) && $timeEnd->gt($schedStart);
            });

        if ($roomConflicts->isNotEmpty()) {
            $conflicts['room'] = 'Room is already booked at this time.';
        }

        // Check section conflict
        $sectionConflicts = Schedule::where('section_id', $data['section_id'])
            ->where('day', $data['day'])
            ->where('term_id', $data['term_id'])
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->get()
            ->filter(function ($schedule) use ($timeStart, $timeEnd) {
                $schedStart = Carbon::parse($schedule->time_start);
                $schedEnd = Carbon::parse($schedule->time_end);
                return $timeStart->lt($schedEnd) && $timeEnd->gt($schedStart);
            });

        if ($sectionConflicts->isNotEmpty()) {
            $conflicts['section'] = 'Section has a conflicting schedule at this time.';
        }

        return $conflicts;
    }

    private function notifyScheduleCreated(Schedule $schedule)
    {
        // Notify teacher
        Notification::create([
            'user_id' => $schedule->teacher_id,
            'type' => 'info',
            'title' => 'New Schedule Assigned',
            'message' => "You have been assigned to teach {$schedule->subject->name} for {$schedule->section->name} on {$schedule->day} from {$schedule->time_start} to {$schedule->time_end}.",
            'notifiable_type' => Schedule::class,
            'notifiable_id' => $schedule->id,
        ]);

        // Notify students in the section
        $students = User::where('role', 'student')
            ->where('section', $schedule->section->name)
            ->get();

        foreach ($students as $student) {
            Notification::create([
                'user_id' => $student->id,
                'type' => 'info',
                'title' => 'New Schedule Added',
                'message' => "A new schedule for {$schedule->subject->name} has been added on {$schedule->day} from {$schedule->time_start} to {$schedule->time_end}.",
                'notifiable_type' => Schedule::class,
                'notifiable_id' => $schedule->id,
            ]);
        }
    }

    private function notifyScheduleUpdated(Schedule $schedule)
    {
        Notification::create([
            'user_id' => $schedule->teacher_id,
            'type' => 'warning',
            'title' => 'Schedule Updated',
            'message' => "Your schedule for {$schedule->subject->name} has been updated.",
            'notifiable_type' => Schedule::class,
            'notifiable_id' => $schedule->id,
        ]);

        $students = User::where('role', 'student')
            ->where('section', $schedule->section->name)
            ->get();

        foreach ($students as $student) {
            Notification::create([
                'user_id' => $student->id,
                'type' => 'warning',
                'title' => 'Schedule Updated',
                'message' => "Schedule for {$schedule->subject->name} has been updated.",
                'notifiable_type' => Schedule::class,
                'notifiable_id' => $schedule->id,
            ]);
        }
    }

    private function notifyScheduleDeleted(Schedule $schedule)
    {
        Notification::create([
            'user_id' => $schedule->teacher_id,
            'type' => 'error',
            'title' => 'Schedule Cancelled',
            'message' => "Your schedule for {$schedule->subject->name} has been cancelled.",
            'notifiable_type' => Schedule::class,
            'notifiable_id' => $schedule->id,
        ]);
    }

    public function timetable(Request $request)
    {
        $activeTerm = Term::where('is_active', true)->first();
        $termId = $request->get('term_id', $activeTerm?->id);
        
        $schedules = Schedule::with(['teacher', 'subject', 'section', 'room', 'term'])
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->orderBy('day')
            ->orderBy('time_start')
            ->get();

        $teachers = User::where('role', 'teacher')->where('account_status', 'active')->get();
        $subjects = Subject::all();
        $sections = Section::all();
        $rooms = Room::all();
        $terms = Term::all();

        return view('admin.schedules.timetable', compact('schedules', 'teachers', 'subjects', 'sections', 'rooms', 'terms', 'activeTerm', 'termId'));
    }

    public function storeFromTimetable(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'room_id' => 'required|exists:rooms,id',
            'term_id' => 'required|exists:terms,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        // Check for conflicts
        $conflicts = $this->checkConflicts($validated);

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'errors' => $conflicts
            ], 422);
        }

        $schedule = Schedule::create($validated);
        $this->notifyScheduleCreated($schedule);

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully.',
            'schedule' => $schedule->load(['teacher', 'subject', 'section', 'room'])
        ]);
    }

    public function checkConflictsAjax(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'section_id' => 'required|exists:sections,id',
            'room_id' => 'required|exists:rooms,id',
            'term_id' => 'required|exists:terms,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'exclude_id' => 'nullable|exists:schedules,id',
        ]);

        $excludeId = $request->get('exclude_id');
        $conflicts = $this->checkConflicts($validated, $excludeId);

        return response()->json([
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }
}
