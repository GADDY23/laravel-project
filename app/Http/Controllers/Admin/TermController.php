<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;

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
        return view('admin.terms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        // If this term is set as active, deactivate all other terms
        if ($request->has('is_active') && $request->is_active) {
            Term::where('is_active', true)->update(['is_active' => false]);
        }

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
        return view('admin.terms.edit', compact('term'));
    }

    public function update(Request $request, Term $term)
    {
        $validated = $request->validate([
            'academic_year' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        // If this term is set as active, deactivate all other terms
        if ($request->has('is_active') && $request->is_active) {
            Term::where('id', '!=', $term->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

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
}
