<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseStrand;
use Illuminate\Http\Request;

class CourseStrandController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseStrand::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $courseStrands = $query->orderBy('type')->orderBy('name')->paginate(20);

        return view('admin.course-strands.index', compact('courseStrands'));
    }

    public function create()
    {
        return view('admin.course-strands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:shs,college',
        ]);

        $exists = CourseStrand::where('name', $validated['name'])
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'This course/strand already exists for the selected type.'])
                ->withInput();
        }

        CourseStrand::create($validated);

        return redirect()->route('admin.course-strands.index')
            ->with('success', 'Course/strand created successfully.');
    }

    public function show(CourseStrand $courseStrand)
    {
        return view('admin.course-strands.show', compact('courseStrand'));
    }

    public function edit(CourseStrand $courseStrand)
    {
        return view('admin.course-strands.edit', compact('courseStrand'));
    }

    public function update(Request $request, CourseStrand $courseStrand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:shs,college',
        ]);

        $exists = CourseStrand::where('name', $validated['name'])
            ->where('type', $validated['type'])
            ->where('id', '!=', $courseStrand->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'This course/strand already exists for the selected type.'])
                ->withInput();
        }

        $courseStrand->update($validated);

        return redirect()->route('admin.course-strands.index')
            ->with('success', 'Course/strand updated successfully.');
    }

    public function destroy(CourseStrand $courseStrand)
    {
        $courseStrand->delete();

        return redirect()->route('admin.course-strands.index')
            ->with('success', 'Course/strand deleted successfully.');
    }
}
