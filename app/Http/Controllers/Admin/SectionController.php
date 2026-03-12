<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseStrand;
use App\Models\Curriculum;
use App\Models\Section;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::active()->with(['adviser', 'term']);

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('course_strand', 'like', '%' . $request->search . '%');
            });
        }

        $sections = $query->orderBy('name')->paginate(20);

        return view('admin.sections.index', compact('sections'));
    }

    public function create()
    {
        $advisers = User::where('role', 'teacher')->where('account_status', 'active')->get();
        $terms = Term::enabled()->orderByDesc('academic_year')->orderBy('semester')->get();
        $curricula = Curriculum::orderBy('curriculum_code')->get();
        $courseStrands = Schema::hasTable('course_strands')
            ? CourseStrand::orderBy('type')->orderBy('name')->get()->groupBy('type')
            : collect();

        return view('admin.sections.create', compact('advisers', 'terms', 'curricula', 'courseStrands'));
    }

    public function store(Request $request)
    {
        $courseType = $request->input('course_type');
        $termId = $request->input('term_id');

        $nameRule = ['required', 'string', 'max:255'];
        if ($termId) {
            $nameRule[] = Rule::unique('sections', 'name')->where(fn ($query) => $query->where('term_id', $termId));
        } else {
            $nameRule[] = Rule::unique('sections', 'name')->whereNull('term_id');
        }

        $rules = [
            'name' => $nameRule,
            'course_type' => 'required|in:shs,college',
            'course_strand' => 'required|string|max:255',
            'year_level' => ['required', Rule::in(CourseStrand::yearLevelsForType($courseType))],
            'capacity' => 'required|integer|min:1',
            'term_id' => 'nullable|exists:terms,id',
            'curriculum_id' => 'nullable|exists:curricula,id',
            'status' => 'required|in:active,inactive',
            'adviser_id' => 'nullable|exists:users,id',
        ];

        if (Schema::hasTable('course_strands')) {
            $rules['course_strand'] = [
                'required',
                'string',
                'max:255',
                Rule::exists('course_strands', 'name')->where(fn($query) => $query->where('type', $courseType)),
            ];
        }

        $validated = $request->validate($rules);

        Section::create($validated);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function show(Section $section)
    {
        $section->load(['adviser', 'term', 'schedules', 'students']);
        return view('admin.sections.show', compact('section'));
    }

    public function edit(Section $section)
    {
        $advisers = User::where('role', 'teacher')->where('account_status', 'active')->get();
        $terms = Term::enabled()->orderByDesc('academic_year')->orderBy('semester')->get();
        $curricula = Curriculum::orderBy('curriculum_code')->get();
        $courseStrands = Schema::hasTable('course_strands')
            ? CourseStrand::orderBy('type')->orderBy('name')->get()->groupBy('type')
            : collect();

        return view('admin.sections.edit', compact('section', 'advisers', 'terms', 'curricula', 'courseStrands'));
    }

    public function update(Request $request, Section $section)
    {
        $courseType = $request->input('course_type');
        $termId = $request->input('term_id');

        $nameRule = ['required', 'string', 'max:255'];
        if ($termId) {
            $nameRule[] = Rule::unique('sections', 'name')
                ->ignore($section->id)
                ->where(fn ($query) => $query->where('term_id', $termId));
        } else {
            $nameRule[] = Rule::unique('sections', 'name')
                ->ignore($section->id)
                ->whereNull('term_id');
        }

        $rules = [
            'name' => $nameRule,
            'course_type' => 'required|in:shs,college',
            'course_strand' => 'required|string|max:255',
            'year_level' => ['required', Rule::in(CourseStrand::yearLevelsForType($courseType))],
            'capacity' => 'required|integer|min:1',
            'term_id' => 'nullable|exists:terms,id',
            'curriculum_id' => 'nullable|exists:curricula,id',
            'status' => 'required|in:active,inactive',
            'adviser_id' => 'nullable|exists:users,id',
        ];

        if (Schema::hasTable('course_strands')) {
            $rules['course_strand'] = [
                'required',
                'string',
                'max:255',
                Rule::exists('course_strands', 'name')->where(fn($query) => $query->where('type', $courseType)),
            ];
        }

        $validated = $request->validate($rules);

        $section->update($validated);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section deleted successfully.');
    }
}
