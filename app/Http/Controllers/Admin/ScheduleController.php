<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Room;
use App\Models\Term;
use App\Models\Curriculum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->get('status', 'posted');
        $hasPublishFlag = Schema::hasColumn('schedules', 'is_published');

        $query = Schedule::with(['teacher', 'subject', 'section', 'room', 'term'])
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->whereHas('room', fn($q) => $q->available())
            ->whereHas('term', fn($q) => $q->active());

        if ($hasPublishFlag) {
            if ($statusFilter === 'draft') {
                $query->where('is_published', false);
            } elseif ($statusFilter === 'all') {
                // keep all
            } else {
                $query->where('is_published', true);
                $statusFilter = 'posted';
            }
        }

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

        $schedules = $query->orderBy('day')->orderBy('time_start')->paginate(50);

        $scheduleName = $request->get('schedule_name');
        $selectedRooms = (array) $request->get('rooms', []);
        $termId = $request->get('term_id');
        $term = $termId ? Term::find($termId) : null;

        $sections = Section::active()
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->get();

        $rooms = $selectedRooms ? Room::whereIn('id', $selectedRooms)->get() : collect();

        return view('admin.schedules.index', compact('schedules', 'statusFilter', 'hasPublishFlag', 'scheduleName', 'selectedRooms', 'term', 'rooms', 'sections'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')
            ->where('account_status', 'active')
            ->with(['subjects:id'])
            ->get();
        $subjects = Subject::active()->get();
        $sections = Section::active()->get();
        $rooms = Room::available()
            ->when(count($selectedRooms) > 0, fn($q) => $q->whereIn('id', $selectedRooms))
            ->get();
        $terms = Term::enabled()->get();

        return view('admin.schedules.create', compact('teachers', 'subjects', 'sections', 'rooms', 'terms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'teacher')->where('account_status', 'active'))],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('status', 'active')],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('status', 'active')],
            'room_id' => ['required', Rule::exists('rooms', 'id')->where('status', 'available')],
            'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        $relationErrors = $this->validateTimetableRelations($validated);
        if (!empty($relationErrors)) {
            return back()->withErrors($relationErrors)->withInput();
        }

        // Check for conflicts
        $conflicts = $this->checkConflicts($validated);

        if (!empty($conflicts)) {
            return back()->withErrors($conflicts)->withInput();
        }

        $schedule = Schedule::create($validated);

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
        $teachers = User::where('role', 'teacher')
            ->where('account_status', 'active')
            ->with(['subjects:id'])
            ->get();
        $subjects = Subject::active()->get();
        $sections = Section::active()->get();
        $rooms = Room::available()->get();
        $terms = Term::enabled()->get();

        return view('admin.schedules.edit', compact('schedule', 'teachers', 'subjects', 'sections', 'rooms', 'terms'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        // Handle _method override for PUT requests
        if ($request->has('_method')) {
            $request->merge(['_method' => $request->input('_method')]);
        }

        $validated = $request->validate([
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'teacher')->where('account_status', 'active'))],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('status', 'active')],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('status', 'active')],
            'room_id' => ['required', Rule::exists('rooms', 'id')->where('status', 'available')],
            'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        $relationErrors = $this->validateTimetableRelations($validated);
        if (!empty($relationErrors)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $relationErrors
                ], 422);
            }
            return back()->withErrors($relationErrors)->withInput();
        }

        // Check for conflicts (excluding current schedule)
        $conflicts = $this->checkConflicts($validated, $schedule->id, (bool) $request->boolean('draft_only'));

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

    public function destroy(Request $request, Schedule $schedule)
    {
        $schedule->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully.'
            ]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    private function checkConflicts(array $data, $excludeScheduleId = null, bool $draftOnly = false)
    {
        $conflicts = [];
        $hasPublishFlag = Schema::hasColumn('schedules', 'is_published');

        // Overlap condition: existing.start < new.end AND existing.end > new.start
        $overlap = function ($query) use ($data, $excludeScheduleId, $draftOnly, $hasPublishFlag) {
            return $query
                ->where('day', $data['day'])
                ->where('term_id', $data['term_id'])
                ->when($draftOnly && $hasPublishFlag, fn($q) => $q->where('is_published', false))
                ->where('time_start', '<', $data['time_end'])
                ->where('time_end', '>', $data['time_start'])
                ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId));
        };

        $roomConflictExists = Schedule::query()
            ->where('room_id', $data['room_id'])
            ->where($overlap)
            ->exists();

        if ($roomConflictExists) {
            $conflicts['room'] = 'Room is already booked at this time.';
        }

        return $conflicts;
    }

    public function configure(Request $request)
    {
        $terms = Term::enabled()->get();
        $rooms = Room::available()->get();

        // Keep selected values in session so the user can return to the timetable view later
        // and still see the rooms they chose in the configuration step.
        $sessionKey = 'schedule_config';

        $rawTerm = $request->get('term_id') ?? $request->get('term');
        $selectedTermId = $this->resolveTermId($rawTerm);
        $selectedRooms = $request->get('rooms', []);
        $scheduleName = $request->get('schedule_name');

        if ($request->has('term_id') || $request->has('rooms') || $request->has('schedule_name')) {
            session()->put($sessionKey, [
                'term_id' => $selectedTermId,
                'rooms' => $selectedRooms,
                'schedule_name' => $scheduleName,
            ]);
        } else {
            $stored = session()->get($sessionKey, []);
            $selectedTermId = $selectedTermId ?? $stored['term_id'] ?? null;
            $selectedRooms = $selectedRooms ?: ($stored['rooms'] ?? []);
            $scheduleName = $scheduleName ?? $stored['schedule_name'] ?? null;
        }

        return view('admin.schedules.configure', compact('terms', 'rooms', 'selectedTermId', 'selectedRooms', 'scheduleName'));
    }

    public function timetable(Request $request)
    {
        $activeTerm = Term::active()->first();
        $sessionKey = 'schedule_config';

        $rawTerm = $request->get('term_id') ?? $request->get('term');
        $termId = $this->resolveTermId($rawTerm);
        $selectedRooms = (array) $request->get('rooms', []);
        $scheduleName = $request->get('schedule_name');

        // Persist selection across navigations so users don't lose their selection when
        // revisiting the timetable page without query parameters.
        if ($request->has('term_id') || $request->has('rooms') || $request->has('schedule_name')) {
            session()->put($sessionKey, [
                'term_id' => $termId,
                'rooms' => $selectedRooms,
                'schedule_name' => $scheduleName,
            ]);
        } else {
            $stored = session()->get($sessionKey, []);
            $termId = $termId ?? $stored['term_id'] ?? null;
            $selectedRooms = $selectedRooms ?: ($stored['rooms'] ?? []);
            $scheduleName = $scheduleName ?? $stored['schedule_name'] ?? null;
        }

        // Normalize the term ID to ensure the `when()` filter works as expected
        // (empty string should not bypass filtering and show all sections).
        $termId = $termId ? (int) $termId : null;
        $termId = $termId ?? $activeTerm?->id;
        $term = Term::find($termId);

        // Normalize room IDs from request/session to ints to ensure consistent filtering.
        $selectedRooms = array_values(array_filter(array_map('intval', $selectedRooms)));

        $schedules = Schedule::with(['teacher', 'subject', 'section', 'room', 'term'])
            ->whereHas('subject', fn($q) => $q->active())
            ->whereHas('section', fn($q) => $q->active())
            ->whereHas('room', fn($q) => $q->available())
            ->whereHas('term', fn($q) => $q->enabled())
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->when(count($selectedRooms) > 0, fn($q) => $q->whereIn('room_id', $selectedRooms))
            ->orderBy('day')
            ->orderBy('time_start')
            ->get();

        $sections = Section::active()
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->with(['curriculum.subjects' => fn($q) => $q->active()])
            ->get();

        // Determine semester number from term for matching curriculum period_number (semestral)
        $periodNumber = 1;
        if ($term && str_contains(strtolower($term->semester ?? ''), '2nd')) {
            $periodNumber = 2;
        }

        $sectionsPayload = $sections->map(function ($section) use ($periodNumber) {
            $curriculum = $section->curriculum;
            $subjects = collect();

            if ($curriculum) {
                $sectionYearLevel = $section->year_level;
                // Normalize grade levels for Senior High (if using grade_11/grade_12) to match curriculum_subject enum values.
                if ($sectionYearLevel === 'grade_11') {
                    $sectionYearLevel = '1st_year';
                } elseif ($sectionYearLevel === 'grade_12') {
                    $sectionYearLevel = '2nd_year';
                }

                $subjects = $curriculum->subjects->filter(function ($subject) use ($sectionYearLevel, $periodNumber) {
                    return ($subject->pivot->year_level ?? '') === ($sectionYearLevel ?? '')
                        && (int) ($subject->pivot->period_number ?? 1) === $periodNumber;
                })->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name,
                        'description' => $subject->description,
                    ];
                })->values();
            }

            return [
                'id' => $section->id,
                'name' => $section->name,
                'year_level' => $section->year_level,
                'term_id' => $section->term_id,
                'curriculum_id' => $section->curriculum_id,
                'curriculum_code' => $curriculum?->curriculum_code,
                'subjects' => $subjects,
            ];
        })->values();

        $publishedCount = 0;
        if (Schema::hasColumn('schedules', 'is_published')) {
            $publishedCount = Schedule::query()
                ->when($termId, fn($q) => $q->where('term_id', $termId))
                ->where('is_published', true)
                ->count();
        }

        $teachers = User::where('role', 'teacher')
            ->where('account_status', 'active')
            ->with(['subjects:id'])
            ->get();
        $subjects = Subject::active()->get();
        $rooms = Room::available()->get();
        $terms = Term::enabled()->get();
        $curricula = Curriculum::active()
            ->with([
                'term:id,term_code,academic_year,semester',
                'subjects' => fn($q) => $q->active()->select('subjects.id')
            ])
            ->orderBy('curriculum_code')
            ->get();

        return view('admin.schedules.timetable', compact(
            'schedules',
            'teachers',
            'subjects',
            'sections',
            'rooms',
            'terms',
            'activeTerm',
            'termId',
            'curricula',
            'publishedCount',
            'scheduleName',
            'selectedRooms',
            'term',
            'sectionsPayload'
        ));
    }

    public function storeFromTimetable(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'teacher')->where('account_status', 'active'))],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('status', 'active')],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('status', 'active')],
            'room_id' => ['required', Rule::exists('rooms', 'id')->where('status', 'available')],
            'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
            'curriculum_id' => ['nullable', Rule::exists('curricula', 'id')],
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        $relationErrors = $this->validateTimetableRelations($validated);
        if (!empty($relationErrors)) {
            return response()->json([
                'success' => false,
                'errors' => $relationErrors
            ], 422);
        }

        // Check for conflicts
        $conflicts = $this->checkConflicts($validated);

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'errors' => $conflicts
            ], 422);
        }

        $schedule = Schedule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully.',
            'schedule' => $schedule->load(['teacher', 'subject', 'section', 'room'])
        ]);
    }

    public function checkConflictsAjax(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'teacher')->where('account_status', 'active'))],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('status', 'active')],
            'room_id' => ['required', Rule::exists('rooms', 'id')->where('status', 'available')],
            'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'exclude_id' => 'nullable|exists:schedules,id',
            'draft_only' => 'nullable|boolean',
        ]);

        $excludeId = $request->get('exclude_id');
        $draftOnly = (bool) $request->boolean('draft_only');
        $conflicts = $this->checkConflicts($validated, $excludeId, $draftOnly);

        return response()->json([
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }

    public function publishWeek(Request $request)
    {
        if (!Schema::hasColumn('schedules', 'is_published') || !Schema::hasColumn('schedules', 'published_at')) {
            return response()->json([
                'success' => false,
                'message' => 'Publish fields are missing. Run migrations first.'
            ], 422);
        }

        $validated = $request->validate([
            'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
            'curriculum_id' => ['nullable', Rule::exists('curricula', 'id')],
        ]);

        $query = Schedule::query()->where('term_id', $validated['term_id']);

        if (!empty($validated['curriculum_id'])) {
            $curriculum = Curriculum::query()->with('subjects:id')->find($validated['curriculum_id']);

            if (!$curriculum) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected curriculum is invalid.'
                ], 422);
            }

            if (!empty($curriculum->term_id) && (int) $curriculum->term_id !== (int) $validated['term_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curriculum and term do not match.'
                ], 422);
            }

            $subjectIds = $curriculum->subjects->pluck('id');
            $query->whereIn('subject_id', $subjectIds);
        }

        $affectedRows = DB::transaction(function () use ($query) {
            return $query->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
        });

        if ($affectedRows === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No schedules found to publish for the selected week.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Week timetable published successfully. {$affectedRows} schedule(s) are now official."
        ]);
    }

    private function validateTimetableRelations(array $data): array
    {
        $errors = [];

        $subject = Subject::query()->find($data['subject_id'] ?? null);
        $room = Room::query()->find($data['room_id'] ?? null);
        $section = Section::query()->find($data['section_id'] ?? null);
        $curriculumId = $data['curriculum_id'] ?? null;

        if ($subject && $room && $subject->required_room_type !== $room->room_type) {
            $errors['room_type'] = 'Selected room type is not compatible with this subject.';
        }

        if ($section && $section->term_id && isset($data['term_id']) && (int) $section->term_id !== (int) $data['term_id']) {
            $errors['section_term'] = 'Selected section is not linked to the selected term.';
        }

        if ($curriculumId) {
            $curriculum = Curriculum::query()->with('subjects:id')->find($curriculumId);

            if (!$curriculum) {
                $errors['curriculum'] = 'Selected curriculum is invalid.';
            } else {
                if (!empty($curriculum->term_id) && isset($data['term_id']) && (int) $curriculum->term_id !== (int) $data['term_id']) {
                    $errors['curriculum_term'] = 'Selected curriculum and term do not match.';
                }

                if ($subject && !$curriculum->subjects->contains('id', $subject->id)) {
                    $errors['curriculum_subject'] = 'Selected subject is not part of the selected curriculum.';
                }
            }
        }

        return $errors;
    }

    private function resolveTermId($rawTerm): ?int
    {
        if (empty($rawTerm)) {
            return null;
        }

        // If an ID was given and it exists, use it.
        if (is_numeric($rawTerm) && Term::where('id', (int) $rawTerm)->exists()) {
            return (int) $rawTerm;
        }

        // Try treating the value as a term_code (eg. "2531")
        $term = Term::where('term_code', $rawTerm)->first();
        return $term?->id;
    }
}
