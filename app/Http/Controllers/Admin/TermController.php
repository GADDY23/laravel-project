<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::orderBy('academic_year', 'desc')
            ->orderBy('semester')
            ->paginate(20);

        return view('admin.terms.index', compact('terms'));
    }

    public function create()
    {
        $academicYearOptions = Term::academicYearOptions();
        $semesterOptions = Term::semesterOptions();

        return view('admin.terms.create', compact('academicYearOptions', 'semesterOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term_code' => 'required|string|max:255|unique:terms,term_code',
            'academic_year' => ['required', Rule::in(Term::academicYearOptions())],
            'semester' => ['required', Rule::in(Term::semesterOptions())],
            'status' => 'required|in:active,inactive',
        ]);

        Term::create($validated);

        return redirect()->route('admin.terms.index')
            ->with('success', 'Term created successfully.');
    }

    public function show(Term $term)
    {
        $term->load('schedules');
        return view('admin.terms.show', compact('term'));
    }

    public function edit(Term $term)
    {
        $academicYearOptions = Term::academicYearOptions();
        $semesterOptions = Term::semesterOptions();

        return view('admin.terms.edit', compact('term', 'academicYearOptions', 'semesterOptions'));
    }

    public function update(Request $request, Term $term)
    {
        $validated = $request->validate([
            'term_code' => 'required|string|max:255|unique:terms,term_code,' . $term->id,
            'academic_year' => ['required', Rule::in(Term::academicYearOptions())],
            'semester' => ['required', Rule::in(Term::semesterOptions())],
            'status' => 'required|in:active,inactive',
        ]);

        $term->update($validated);

        return redirect()->route('admin.terms.index')
            ->with('success', 'Term updated successfully.');
    }

    public function destroy(Term $term)
    {
        $term->delete();

        return redirect()->route('admin.terms.index')
            ->with('success', 'Term deleted successfully.');
    }

    public function updateStatus(Request $request, Term $term)
    {
        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        $term->update(['is_enabled' => (bool) $validated['is_enabled']]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Term availability updated successfully.',
                'status' => $term->status,
                'is_enabled' => (bool) $term->is_enabled,
                'term_id' => $term->id,
            ]);
        }

        return redirect()->back()->with('success', 'Term availability updated successfully.');
    }
}
