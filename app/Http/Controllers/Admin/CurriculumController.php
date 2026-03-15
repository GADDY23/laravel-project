<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseStrand;
use App\Models\Curriculum;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $query = Curriculum::query()->with(['subjects']);

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('curriculum_code', 'like', '%' . $request->search . '%')
                    ->orWhere('course_strand', 'like', '%' . $request->search . '%');
            });
        }

        $curricula = $query->orderByDesc('id')->paginate(20);

        return view('admin.curricula.index', compact('curricula'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $courseStrands = Schema::hasTable('course_strands')
            ? CourseStrand::orderBy('type')->orderBy('name')->get()->groupBy('type')
            : collect();

        return view('admin.curricula.create', [
            'subjects' => $subjects,
            'courseStrands' => $courseStrands,
            'existingRows' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $courseType = $request->input('course_type');

        $rules = [
            'school_year_start' => 'required|integer|min:2000|max:2100',
            'course_type' => 'required|in:shs,college',
            'curriculum_type' => 'required|in:semestral,trimestral',
            'course_strand' => 'required|string|max:255',
            'row_subject_ids' => 'required|array|min:1',
            'row_subject_ids.*' => 'required|exists:subjects,id',
            'row_year_levels' => 'required|array',
            'row_year_levels.*' => 'required|in:1st_year,2nd_year,3rd_year,4th_year',
            'row_period_numbers' => 'required|array',
            'row_period_numbers.*' => 'required|integer|min:1|max:3',
            'row_period_labels' => 'required|array',
            'row_period_labels.*' => 'required|string|max:40',
            'row_prerequisites' => 'nullable|array',
            'row_prerequisites.*' => 'nullable|string|max:255',
        ];

        if (Schema::hasTable('course_strands')) {
            $rules['course_strand'] = [
                'required',
                'string',
                'max:255',
                Rule::exists('course_strands', 'name')->where(fn ($query) => $query->where('type', $courseType)),
            ];
        }

        $validated = $request->validate($rules);

        $generatedCode = $this->generateUniqueCurriculumCode(
            (int) $validated['school_year_start'],
            $validated['course_type'],
            $validated['course_strand'],
            $validated['curriculum_type']
        );

        $payload = [
            'curriculum_code' => $generatedCode,
            'course_strand' => $validated['course_strand'],
        ];

        if (Schema::hasColumn('curricula', 'school_year_start')) {
            $payload['school_year_start'] = (int) $validated['school_year_start'];
        }
        if (Schema::hasColumn('curricula', 'course_type')) {
            $payload['course_type'] = $validated['course_type'];
        }
        if (Schema::hasColumn('curricula', 'curriculum_type')) {
            $payload['curriculum_type'] = $validated['curriculum_type'];
        }
        if (Schema::hasColumn('curricula', 'description')) {
            $payload['description'] = 'Curriculum for ' . $validated['course_strand'];
        }
        if (Schema::hasColumn('curricula', 'year_level')) {
            $payload['year_level'] = '1st_year';
        }
        if (Schema::hasColumn('curricula', 'term_id')) {
            $payload['term_id'] = null;
        }

        $curriculum = Curriculum::create($payload);

        $this->syncCurriculumRows($curriculum, $validated);

        return redirect()->route('admin.curricula.index')
            ->with('success', 'Curriculum created successfully. Curriculum code generated automatically.');
    }

    public function show(Curriculum $curriculum)
    {
        $curriculum->load(['subjects']);

        $groupedRows = $this->groupRowsByYearAndPeriod($curriculum);

        return view('admin.curricula.show', compact('curriculum', 'groupedRows'));
    }

    public function edit(Curriculum $curriculum)
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $courseStrands = Schema::hasTable('course_strands')
            ? CourseStrand::orderBy('type')->orderBy('name')->get()->groupBy('type')
            : collect();

        $curriculum->load(['subjects']);
        $existingRows = $this->extractExistingRows($curriculum);

        return view('admin.curricula.edit', compact('curriculum', 'subjects', 'courseStrands', 'existingRows'));
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $courseType = $request->input('course_type');

        $rules = [
            'school_year_start' => 'required|integer|min:2000|max:2100',
            'course_type' => 'required|in:shs,college',
            'curriculum_type' => 'required|in:semestral,trimestral',
            'course_strand' => 'required|string|max:255',
            'row_subject_ids' => 'required|array|min:1',
            'row_subject_ids.*' => 'required|exists:subjects,id',
            'row_year_levels' => 'required|array',
            'row_year_levels.*' => 'required|in:1st_year,2nd_year,3rd_year,4th_year',
            'row_period_numbers' => 'required|array',
            'row_period_numbers.*' => 'required|integer|min:1|max:3',
            'row_period_labels' => 'required|array',
            'row_period_labels.*' => 'required|string|max:40',
            'row_prerequisites' => 'nullable|array',
            'row_prerequisites.*' => 'nullable|string|max:255',
        ];

        if (Schema::hasTable('course_strands')) {
            $rules['course_strand'] = [
                'required',
                'string',
                'max:255',
                Rule::exists('course_strands', 'name')->where(fn ($query) => $query->where('type', $courseType)),
            ];
        }

        $validated = $request->validate($rules);

        $generatedCode = $this->generateUniqueCurriculumCode(
            (int) $validated['school_year_start'],
            $validated['course_type'],
            $validated['course_strand'],
            $validated['curriculum_type'],
            $curriculum->id
        );

        $payload = [
            'curriculum_code' => $generatedCode,
            'course_strand' => $validated['course_strand'],
        ];

        if (Schema::hasColumn('curricula', 'school_year_start')) {
            $payload['school_year_start'] = (int) $validated['school_year_start'];
        }
        if (Schema::hasColumn('curricula', 'course_type')) {
            $payload['course_type'] = $validated['course_type'];
        }
        if (Schema::hasColumn('curricula', 'curriculum_type')) {
            $payload['curriculum_type'] = $validated['curriculum_type'];
        }
        if (Schema::hasColumn('curricula', 'description')) {
            $payload['description'] = 'Curriculum for ' . $validated['course_strand'];
        }
        if (Schema::hasColumn('curricula', 'year_level')) {
            $payload['year_level'] = '1st_year';
        }
        if (Schema::hasColumn('curricula', 'term_id')) {
            $payload['term_id'] = null;
        }

        $curriculum->update($payload);

        $this->syncCurriculumRows($curriculum, $validated);

        return redirect()->route('admin.curricula.edit', $curriculum)
            ->with('success', 'Curriculum updated successfully.');
    }

    public function destroy(Curriculum $curriculum)
    {
        $curriculum->delete();

        return redirect()->route('admin.curricula.index')
            ->with('success', 'Curriculum deleted successfully.');
    }

    private function syncCurriculumRows(Curriculum $curriculum, array $validated): void
    {
        $subjectIds = $validated['row_subject_ids'] ?? [];
        $yearLevels = $validated['row_year_levels'] ?? [];
        $periodNumbers = $validated['row_period_numbers'] ?? [];
        $periodLabels = $validated['row_period_labels'] ?? [];
        $prerequisites = $validated['row_prerequisites'] ?? [];

        $hasPrerequisite = Schema::hasColumn('curriculum_subject', 'prerequisite');
        $hasYearLevel = Schema::hasColumn('curriculum_subject', 'year_level');
        $hasPeriodNumber = Schema::hasColumn('curriculum_subject', 'period_number');
        $hasPeriodLabel = Schema::hasColumn('curriculum_subject', 'period_label');

        $curriculum->subjects()->detach();

        $uniqueRows = [];
        foreach ($subjectIds as $idx => $subjectId) {
            if (empty($subjectId)) {
                continue;
            }

            $yearLevel = $yearLevels[$idx] ?? null;
            $periodNumber = isset($periodNumbers[$idx]) ? (int) $periodNumbers[$idx] : null;
            $uniqueKey = $subjectId . '|' . ($yearLevel ?? '') . '|' . ($periodNumber ?? 0);

            if (isset($uniqueRows[$uniqueKey])) {
                continue;
            }

            $pivot = [];

            if ($hasPrerequisite) {
                $pivot['prerequisite'] = $prerequisites[$idx] ?? null;
            }
            if ($hasYearLevel) {
                $pivot['year_level'] = $yearLevel;
            }
            if ($hasPeriodNumber) {
                $pivot['period_number'] = $periodNumber;
            }
            if ($hasPeriodLabel) {
                $pivot['period_label'] = $periodLabels[$idx] ?? null;
            }

            $uniqueRows[$uniqueKey] = [
                'subject_id' => $subjectId,
                'pivot' => $pivot,
            ];
        }

        foreach ($uniqueRows as $row) {
            $curriculum->subjects()->attach($row['subject_id'], $row['pivot']);
        }
    }

    private function generateUniqueCurriculumCode(
        int $schoolYearStart,
        string $courseType,
        string $courseStrand,
        string $curriculumType,
        ?int $ignoreId = null
    ): string {
        $typePart = strtoupper($courseType);
        $strandPart = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $courseStrand));
        $curriculumTypeLabel = ucfirst($curriculumType);

        $base = "{$schoolYearStart}-{$typePart}-{$strandPart}-{$curriculumTypeLabel}";
        $candidate = $base;
        $counter = 2;

        while (
            Curriculum::query()
                ->where('curriculum_code', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function extractExistingRows(Curriculum $curriculum)
    {
        $rows = [];
        foreach ($curriculum->subjects as $subject) {
            $rows[] = [
                'subject_id' => $subject->id,
                'year_level' => $subject->pivot->year_level ?? '1st_year',
                'period_number' => (int) ($subject->pivot->period_number ?? 1),
                'period_label' => $subject->pivot->period_label ?? '1st Sem',
                'prerequisite' => $subject->pivot->prerequisite ?? '',
            ];
        }

        return collect($rows);
    }

    private function groupRowsByYearAndPeriod(Curriculum $curriculum)
    {
        $yearOrder = [
            '1st_year' => 1,
            '2nd_year' => 2,
            '3rd_year' => 3,
            '4th_year' => 4,
        ];

        return $curriculum->subjects
            ->sortBy(function ($subject) use ($yearOrder) {
                $yearLevel = $subject->pivot->year_level ?? '1st_year';
                $period = (int) ($subject->pivot->period_number ?? 1);
                $yearIndex = $yearOrder[$yearLevel] ?? 0;

                return $yearIndex * 10 + $period;
            })
            ->groupBy(fn ($subject) => ($subject->pivot->year_level ?? '1st_year') . '|' . ($subject->pivot->period_number ?? 1));
    }
}
