<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $subjects = $query->orderBy('name')->paginate(20);

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:subjects',
            'year_level' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'course_strand' => 'required|string|max:255',
            'hours' => 'required|integer|min:1',
            'required_room_type' => 'required|in:lecture,laboratory,computer_lab,science_lab,workshop,any',
            'description' => 'nullable|string',
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        $subject->load(['teachers', 'schedules', 'curricula']);
        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:subjects,code,' . $subject->id,
            'year_level' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'course_strand' => 'required|string|max:255',
            'hours' => 'required|integer|min:1',
            'required_room_type' => 'required|in:lecture,laboratory,computer_lab,science_lab,workshop,any',
            'description' => 'nullable|string',
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
