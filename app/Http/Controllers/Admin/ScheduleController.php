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
        $statusFilter = $request->get('status', 'all');
        $hasPublishFlag = Schema::hasColumn('schedules', 'is_published');
        $hasScheduleName = Schema::hasColumn('schedules', 'schedule_name');

        $query = Schedule::query()
            ->select('term_id')
            ->when($hasScheduleName, fn($q) => $q->addSelect('schedule_name'))
            ->selectRaw('GROUP_CONCAT(DISTINCT rooms.name ORDER BY rooms.name SEPARATOR \', \') as room_names')
            ->selectRaw('GROUP_CONCAT(DISTINCT rooms.id ORDER BY rooms.id SEPARATOR \',\') as room_ids')
            ->selectRaw('COUNT(DISTINCT rooms.id) as room_count')
            ->leftJoin('rooms', 'schedules.room_id', '=', 'rooms.id');

        if ($hasPublishFlag) {
            $query->addSelect('is_published');
        }

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

        // Filter by term
        if ($request->has('term_id') && $request->term_id) {
            $query->where('term_id', $request->term_id);
        }

        if ($hasScheduleName && $request->filled('schedule_name')) {
            $query->where('schedule_name', $request->schedule_name);
        }

        $groupColumns = ['term_id'];
        if ($hasScheduleName) {
            $groupColumns[] = 'schedule_name';
        }
        if ($hasPublishFlag) {
            $groupColumns[] = 'is_published';
        }

        $schedules = $query
            ->groupBy($groupColumns)
            ->orderBy('term_id')
            ->orderBy('schedule_name')
            ->paginate(50);

        $termCodes = Term::query()->pluck('term_code', 'id');

        $scheduleName = $request->get('schedule_name');
        $selectedRooms = (array) $request->get('rooms', []);
        $termId = $request->get('term_id');
        $term = $termId ? Term::find($termId) : null;

        return view('admin.schedules.index', compact('schedules', 'statusFilter', 'hasPublishFlag', 'scheduleName', 'selectedRooms', 'term', 'termCodes'));
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
        $hasSlotCount = Schema::hasColumn('schedules', 'slot_count');
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

        if ($hasSlotCount) {
            $validated['slot_count'] = $this->computeSlotCount($validated['time_start'], $validated['time_end']);
        }

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

        $hasSlotCount = Schema::hasColumn('schedules', 'slot_count');
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

        if ($hasSlotCount) {
            $validated['slot_count'] = $this->computeSlotCount($validated['time_start'], $validated['time_end']);
        }

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

    public function destroyGroup(Request $request)
    {
        $hasPublishFlag = Schema::hasColumn('schedules', 'is_published');

        $validated = $request->validate([
            'term_id' => ['required', 'integer'],
            'schedule_name' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $query = Schedule::query()->where('term_id', (int) $validated['term_id']);

        if (array_key_exists('schedule_name', $validated)) {
            $scheduleName = $validated['schedule_name'];
            if (is_null($scheduleName) || $scheduleName === '') {
                $query->where(function ($q) {
                    $q->whereNull('schedule_name')
                        ->orWhere('schedule_name', '');
                });
            } else {
                $query->where('schedule_name', $scheduleName);
            }
        }

        if ($hasPublishFlag && isset($validated['is_published'])) {
            $isPublished = (bool) $validated['is_published'];
            if ($isPublished === false) {
                $query->where(function ($q) {
                    $q->where('is_published', false)
                        ->orWhereNull('is_published');
                });
            } else {
                $query->where('is_published', true);
            }
        }

        $deleted = $query->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', "Deleted {$deleted} schedule item(s).");
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
        if ($request->boolean('reset')) {
            session()->forget($sessionKey);
        }

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
        $addRoomId = $request->get('add_room_id');
        $removeRoomId = $request->get('remove_room_id');

        if (!empty($addRoomId)) {
            $selectedRooms[] = $addRoomId;
        }

        if (!empty($removeRoomId)) {
            $selectedRooms = array_filter($selectedRooms, fn($id) => (int) $id !== (int) $removeRoomId);
        }

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
        $selectedRooms = array_values(array_unique(array_filter(array_map('intval', $selectedRooms))));

        if ($request->boolean('reset')) {
            $schedules = collect();
        } else {
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
        }

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
        if (Schema::hasColumn('schedules', 'is_published') && !$request->boolean('reset')) {
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
        $hasSlotCount = Schema::hasColumn('schedules', 'slot_count');
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

        if ($hasSlotCount) {
            $validated['slot_count'] = $this->computeSlotCount($validated['time_start'], $validated['time_end']);
        }

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

        if ($request->has('draft_schedules')) {
            $validated = $request->validate([
                'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
                'curriculum_id' => ['nullable', Rule::exists('curricula', 'id')],
                'schedule_name' => ['nullable', 'string', 'max:255'],
                'selected_rooms' => ['nullable', 'array'],
                'selected_rooms.*' => ['integer', Rule::exists('rooms', 'id')],
                'draft_schedules' => ['required', 'array'],
                'draft_schedules.*.teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'teacher')->where('account_status', 'active'))],
                'draft_schedules.*.subject_id' => ['required', Rule::exists('subjects', 'id')->where('status', 'active')],
                'draft_schedules.*.section_id' => ['required', Rule::exists('sections', 'id')->where('status', 'active')],
                'draft_schedules.*.room_id' => ['nullable', Rule::exists('rooms', 'id')->where('status', 'available')],
                'draft_schedules.*.term_id' => ['required', 'integer'],
                'draft_schedules.*.day' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
                'draft_schedules.*.time_start' => ['required', 'date_format:H:i'],
                'draft_schedules.*.time_end' => ['required', 'date_format:H:i'],
            ]);

            $draftSchedules = $validated['draft_schedules'];
            $termId = (int) $validated['term_id'];
            $selectedRooms = array_map('intval', $validated['selected_rooms'] ?? []);
            $curriculumId = $validated['curriculum_id'] ?? null;
            $scheduleName = $validated['schedule_name'] ?? null;

            $relationErrors = [];
            foreach ($draftSchedules as $idx => $draft) {
                if ((int) $draft['term_id'] !== $termId) {
                    $relationErrors["draft_schedules.{$idx}.term_id"] = 'Schedule term does not match the selected term.';
                    continue;
                }

                if ($draft['time_end'] <= $draft['time_start']) {
                    $relationErrors["draft_schedules.{$idx}.time_end"] = 'End time must be after start time.';
                    continue;
                }

                $dataForValidation = [
                    'teacher_id' => $draft['teacher_id'] ?? null,
                    'subject_id' => $draft['subject_id'],
                    'section_id' => $draft['section_id'],
                    'room_id' => $draft['room_id'],
                    'term_id' => $termId,
                    'curriculum_id' => $curriculumId,
                ];
                $errors = $this->validateTimetableRelations($dataForValidation);
                if (!empty($errors)) {
                    $relationErrors["draft_schedules.{$idx}"] = implode(' ', array_values($errors));
                }
            }

            if (!empty($relationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft schedules failed validation.',
                    'errors' => $relationErrors,
                ], 422);
            }

            $roomDayBuckets = [];
            foreach ($draftSchedules as $draft) {
                if (empty($draft['room_id'])) {
                    continue;
                }
                $bucketKey = $draft['room_id'] . '|' . $draft['day'];
                $roomDayBuckets[$bucketKey][] = $draft;
            }

            foreach ($roomDayBuckets as $bucketKey => $items) {
                usort($items, fn($a, $b) => strcmp($a['time_start'], $b['time_start']));
                for ($i = 1; $i < count($items); $i++) {
                    $prev = $items[$i - 1];
                    $current = $items[$i];
                    if ($current['time_start'] < $prev['time_end']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Draft schedules contain a room conflict. Please resolve overlaps before publishing.',
                        ], 422);
                    }
                }
            }

            $affectedRows = DB::transaction(function () use ($termId, $selectedRooms, $draftSchedules, $scheduleName) {
                $query = Schedule::query()->where('term_id', $termId);
                if (!empty($selectedRooms)) {
                    $query->whereIn('room_id', $selectedRooms);
                }

                $query->delete();

                if (empty($draftSchedules)) {
                    return 0;
                }

                $now = now();
                $hasSlotCount = Schema::hasColumn('schedules', 'slot_count');
                $payload = array_map(function ($draft) use ($now, $scheduleName, $hasSlotCount) {
                    $slotCount = $draft['slot_count'] ?? $this->computeSlotCount($draft['time_start'], $draft['time_end']);
                    return [
                        'teacher_id' => $draft['teacher_id'] ?? null,
                        'subject_id' => $draft['subject_id'],
                        'section_id' => $draft['section_id'],
                        'room_id' => $draft['room_id'],
                        'term_id' => $draft['term_id'],
                        'schedule_name' => $scheduleName,
                        'day' => $draft['day'],
                        'time_start' => $draft['time_start'],
                        'time_end' => $draft['time_end'],
                        ...($hasSlotCount ? ['slot_count' => $slotCount] : []),
                        'is_published' => true,
                        'published_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $draftSchedules);

                return Schedule::query()->insert($payload);
            });

            return response()->json([
                'success' => true,
                'message' => 'Week timetable published successfully.',
                'updated' => $affectedRows ? count($draftSchedules) : 0,
            ]);
        }

        $validated = $request->validate([
            'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
            'curriculum_id' => ['nullable', Rule::exists('curricula', 'id')],
            'schedule_name' => ['nullable', 'string', 'max:255'],
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

        $affectedRows = DB::transaction(function () use ($query, $validated) {
            return $query->update([
                'is_published' => true,
                'published_at' => now(),
                'schedule_name' => $validated['schedule_name'] ?? null,
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

    public function saveDraftWeek(Request $request)
    {
        if (!Schema::hasColumn('schedules', 'is_published')) {
            return response()->json([
                'success' => false,
                'message' => 'Draft fields are missing. Run migrations first.'
            ], 422);
        }

        $validated = $request->validate([
            'term_id' => ['required', Rule::exists('terms', 'id')->where('is_enabled', true)],
            'curriculum_id' => ['nullable', Rule::exists('curricula', 'id')],
            'schedule_name' => ['nullable', 'string', 'max:255'],
            'selected_rooms' => ['nullable', 'array'],
            'selected_rooms.*' => ['integer', Rule::exists('rooms', 'id')],
            'draft_schedules' => ['required', 'array'],
            'draft_schedules.*.teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'teacher')->where('account_status', 'active'))],
            'draft_schedules.*.subject_id' => ['required', Rule::exists('subjects', 'id')->where('status', 'active')],
            'draft_schedules.*.section_id' => ['required', Rule::exists('sections', 'id')->where('status', 'active')],
            'draft_schedules.*.room_id' => ['required', Rule::exists('rooms', 'id')->where('status', 'available')],
            'draft_schedules.*.term_id' => ['required', 'integer'],
            'draft_schedules.*.day' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'draft_schedules.*.time_start' => ['required', 'date_format:H:i'],
                'draft_schedules.*.time_end' => ['required', 'date_format:H:i'],
                'draft_schedules.*.slot_count' => ['nullable', 'integer', 'min:1'],
        ]);

        $draftSchedules = $validated['draft_schedules'];
        $termId = (int) $validated['term_id'];
        $selectedRooms = array_map('intval', $validated['selected_rooms'] ?? []);
        $curriculumId = $validated['curriculum_id'] ?? null;
        $scheduleName = $validated['schedule_name'] ?? null;

        $relationErrors = [];
        foreach ($draftSchedules as $idx => $draft) {
            if ((int) $draft['term_id'] !== $termId) {
                $relationErrors["draft_schedules.{$idx}.term_id"] = 'Schedule term does not match the selected term.';
                continue;
            }

            if ($draft['time_end'] <= $draft['time_start']) {
                $relationErrors["draft_schedules.{$idx}.time_end"] = 'End time must be after start time.';
                continue;
            }

            $dataForValidation = [
                'teacher_id' => $draft['teacher_id'] ?? null,
                'subject_id' => $draft['subject_id'],
                'section_id' => $draft['section_id'],
                'room_id' => $draft['room_id'],
                'term_id' => $termId,
                'curriculum_id' => $curriculumId,
            ];
            $errors = $this->validateTimetableRelations($dataForValidation);
            if (!empty($errors)) {
                $relationErrors["draft_schedules.{$idx}"] = implode(' ', array_values($errors));
            }
        }

        if (!empty($relationErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Draft schedules failed validation.',
                'errors' => $relationErrors,
            ], 422);
        }

        $roomDayBuckets = [];
        foreach ($draftSchedules as $draft) {
            $bucketKey = $draft['room_id'] . '|' . $draft['day'];
            $roomDayBuckets[$bucketKey][] = $draft;
        }

        foreach ($roomDayBuckets as $items) {
            usort($items, fn($a, $b) => strcmp($a['time_start'], $b['time_start']));
            for ($i = 1; $i < count($items); $i++) {
                $prev = $items[$i - 1];
                $current = $items[$i];
                if ($current['time_start'] < $prev['time_end']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Draft schedules contain a room conflict. Please resolve overlaps before saving.',
                    ], 422);
                }
            }
        }

        $publishedConflicts = Schedule::query()
            ->when(!empty($selectedRooms), fn($q) => $q->whereIn('room_id', $selectedRooms))
            ->where('term_id', $termId)
            ->where('is_published', true)
            ->get(['room_id', 'day', 'time_start', 'time_end']);

        foreach ($draftSchedules as $draft) {
            foreach ($publishedConflicts as $published) {
                if ((int) $published->room_id !== (int) $draft['room_id']) {
                    continue;
                }
                if ($published->day !== $draft['day']) {
                    continue;
                }
                if ($draft['time_start'] < $published->time_end && $draft['time_end'] > $published->time_start) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Draft schedules conflict with published schedules. Please resolve overlaps before saving.',
                    ], 422);
                }
            }
        }

        $affectedRows = DB::transaction(function () use ($termId, $selectedRooms, $draftSchedules, $scheduleName) {
            $query = Schedule::query()
                ->where('term_id', $termId)
                ->where('is_published', false);

            if (!empty($selectedRooms)) {
                $query->whereIn('room_id', $selectedRooms);
            }

            $query->delete();

            if (empty($draftSchedules)) {
                return 0;
            }

            $now = now();
                $hasSlotCount = Schema::hasColumn('schedules', 'slot_count');
                $payload = array_map(function ($draft) use ($now, $scheduleName, $hasSlotCount) {
                    $slotCount = $draft['slot_count'] ?? $this->computeSlotCount($draft['time_start'], $draft['time_end']);
                    return [
                        'teacher_id' => $draft['teacher_id'] ?? null,
                        'subject_id' => $draft['subject_id'],
                        'section_id' => $draft['section_id'],
                        'room_id' => $draft['room_id'],
                        'term_id' => $draft['term_id'],
                        'schedule_name' => $scheduleName,
                        'day' => $draft['day'],
                        'time_start' => $draft['time_start'],
                        'time_end' => $draft['time_end'],
                        ...($hasSlotCount ? ['slot_count' => $slotCount] : []),
                        'is_published' => false,
                        'published_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $draftSchedules);

            return Schedule::query()->insert($payload);
        });

        return response()->json([
            'success' => true,
            'message' => 'Draft timetable saved successfully.',
            'updated' => $affectedRows ? count($draftSchedules) : 0,
        ]);
    }

    private function validateTimetableRelations(array $data): array
    {
        $errors = [];

        $subject = Subject::query()->find($data['subject_id'] ?? null);
        $room = Room::query()->find($data['room_id'] ?? null);
        $section = Section::query()->find($data['section_id'] ?? null);
        $curriculumId = $data['curriculum_id'] ?? null;

        if ($subject && $room) {
            $labRoomTypes = ['computer_lab', 'chemistry_lab'];
            $requiredRoomType = $subject->required_room_type;
            $roomType = $room->room_type;

            if ($requiredRoomType === 'lecture') {
                $compatible = in_array($roomType, array_merge(['lecture'], $labRoomTypes), true);
            } else {
                $compatible = $roomType === $requiredRoomType;
            }

            if (!$compatible) {
                $errors['room_type'] = 'Selected room type is not compatible with this subject.';
            }
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

    private function computeSlotCount(string $start, string $end): int
    {
        try {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $start);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $end);
            $diff = $endTime->diffInMinutes($startTime, false);
            return (int) max(1, ceil($diff / 60));
        } catch (\Throwable $e) {
            return 1;
        }
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
