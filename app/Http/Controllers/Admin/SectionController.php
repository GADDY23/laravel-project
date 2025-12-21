<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::with('adviser');

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
        return view('admin.sections.create', compact('advisers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sections',
            'course_strand' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'adviser_id' => 'nullable|exists:users,id',
        ]);

        Section::create($validated);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function show(Section $section)
    {
        $section->load(['adviser', 'schedules', 'students']);
        return view('admin.sections.show', compact('section'));
    }

    public function edit(Section $section)
    {
        $advisers = User::where('role', 'teacher')->where('account_status', 'active')->get();
        return view('admin.sections.edit', compact('section', 'advisers'));
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sections,name,' . $section->id,
            'course_strand' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'adviser_id' => 'nullable|exists:users,id',
        ]);

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
