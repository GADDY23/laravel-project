<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Subject;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $query = Curriculum::with('subjects');

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('strand_program', 'like', '%' . $request->search . '%');
            });
        }

        $curricula = $query->orderBy('code')->paginate(20);

        return view('admin.curricula.index', compact('curricula'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('admin.curricula.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:curricula',
            'name' => 'required|string|max:255',
            'strand_program' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $curriculum = Curriculum::create($validated);

        if (isset($validated['subjects'])) {
            $curriculum->subjects()->attach($validated['subjects']);
        }

        return redirect()->route('admin.curricula.index')
            ->with('success', 'Curriculum created successfully.');
    }

    public function show(Curriculum $curriculum)
    {
        $curriculum->load('subjects');
        return view('admin.curricula.show', compact('curriculum'));
    }

    public function edit(Curriculum $curriculum)
    {
        $subjects = Subject::all();
        $curriculum->load('subjects');
        return view('admin.curricula.edit', compact('curriculum', 'subjects'));
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:curricula,code,' . $curriculum->id,
            'name' => 'required|string|max:255',
            'strand_program' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $curriculum->update($validated);

        if (isset($validated['subjects'])) {
            $curriculum->subjects()->sync($validated['subjects']);
        } else {
            $curriculum->subjects()->detach();
        }

        return redirect()->route('admin.curricula.index')
            ->with('success', 'Curriculum updated successfully.');
    }

    public function destroy(Curriculum $curriculum)
    {
        $curriculum->delete();

        return redirect()->route('admin.curricula.index')
            ->with('success', 'Curriculum deleted successfully.');
    }
}
