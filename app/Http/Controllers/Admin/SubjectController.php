<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::active();

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
        $hasLecLabColumns = Schema::hasColumn('subjects', 'lec_unit') && Schema::hasColumn('subjects', 'lab_unit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:subjects',
            'lec_unit' => 'required|integer|min:0|max:99',
            'lab_unit' => 'required|integer|min:0|max:99',
            'required_room_type' => 'required|in:lecture,computer_lab,chemistry_lab',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $payload = [
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'required_room_type' => $validated['required_room_type'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'course_strand' => 'General',
            'year_level' => '1st_year',
        ];

        if ($hasLecLabColumns) {
            $payload['lec_unit'] = (int) $validated['lec_unit'];
            $payload['lab_unit'] = (int) $validated['lab_unit'];
        } else {
            $totalUnits = (int) $validated['lec_unit'] + (int) $validated['lab_unit'];
            if (Schema::hasColumn('subjects', 'hours')) {
                $payload['hours'] = $totalUnits;
            }
            if (Schema::hasColumn('subjects', 'unit')) {
                $payload['unit'] = $totalUnits;
            }
        }

        Subject::create($payload);

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
        $hasLecLabColumns = Schema::hasColumn('subjects', 'lec_unit') && Schema::hasColumn('subjects', 'lab_unit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:subjects,code,' . $subject->id,
            'lec_unit' => 'required|integer|min:0|max:99',
            'lab_unit' => 'required|integer|min:0|max:99',
            'required_room_type' => 'required|in:lecture,computer_lab,chemistry_lab',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $payload = [
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'required_room_type' => $validated['required_room_type'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'course_strand' => $subject->course_strand ?? 'General',
            'year_level' => $subject->year_level ?? '1st_year',
        ];

        if ($hasLecLabColumns) {
            $payload['lec_unit'] = (int) $validated['lec_unit'];
            $payload['lab_unit'] = (int) $validated['lab_unit'];
        } else {
            $totalUnits = (int) $validated['lec_unit'] + (int) $validated['lab_unit'];
            if (Schema::hasColumn('subjects', 'hours')) {
                $payload['hours'] = $totalUnits;
            }
            if (Schema::hasColumn('subjects', 'unit')) {
                $payload['unit'] = $totalUnits;
            }
        }

        $subject->update($payload);

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
