<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $request->search . '%')
                  ->orWhere('student_id', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $subjects = Subject::all();
        $sections = Section::all();
        return view('admin.users.create', compact('subjects', 'sections'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,teacher,student',
            'account_status' => 'required|in:active,inactive',
        ];

        if ($request->role === 'teacher') {
            $rules['employee_id'] = 'required|string|max:255|unique:users';
            $rules['department'] = 'nullable|string|max:255';
            $rules['expertise'] = 'nullable|string';
            $rules['subjects'] = 'nullable|array';
            $rules['subjects.*'] = 'exists:subjects,id';
        }

        if ($request->role === 'student') {
            $rules['student_id'] = 'required|string|max:255|unique:users';
            $rules['course_strand'] = 'required|string|max:255';
            $rules['year_level'] = 'required|string|max:255';
            $rules['section'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'account_status' => $validated['account_status'],
        ];

        if ($request->role === 'teacher') {
            $userData['employee_id'] = $validated['employee_id'];
            $userData['department'] = $validated['department'] ?? null;
            $userData['expertise'] = $validated['expertise'] ?? null;
        }

        if ($request->role === 'student') {
            $userData['student_id'] = $validated['student_id'];
            $userData['course_strand'] = $validated['course_strand'];
            $userData['year_level'] = $validated['year_level'];
            $userData['section'] = $validated['section'] ?? null;
        }

        $user = User::create($userData);

        if ($request->role === 'teacher' && isset($validated['subjects'])) {
            $user->subjects()->attach($validated['subjects']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['subjects', 'schedules', 'sectionsAdvised']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $subjects = Subject::all();
        $sections = Section::all();
        $user->load('subjects');
        return view('admin.users.edit', compact('user', 'subjects', 'sections'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,teacher,student',
            'account_status' => 'required|in:active,inactive',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['nullable', 'confirmed', Rules\Password::defaults()];
        }

        if ($request->role === 'teacher') {
            $rules['employee_id'] = 'required|string|max:255|unique:users,employee_id,' . $user->id;
            $rules['department'] = 'nullable|string|max:255';
            $rules['expertise'] = 'nullable|string';
            $rules['subjects'] = 'nullable|array';
            $rules['subjects.*'] = 'exists:subjects,id';
        }

        if ($request->role === 'student') {
            $rules['student_id'] = 'required|string|max:255|unique:users,student_id,' . $user->id;
            $rules['course_strand'] = 'required|string|max:255';
            $rules['year_level'] = 'required|string|max:255';
            $rules['section'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'role' => $validated['role'],
            'account_status' => $validated['account_status'],
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if ($request->role === 'teacher') {
            $userData['employee_id'] = $validated['employee_id'];
            $userData['department'] = $validated['department'] ?? null;
            $userData['expertise'] = $validated['expertise'] ?? null;
            $userData['student_id'] = null;
            $userData['course_strand'] = null;
            $userData['year_level'] = null;
            $userData['section'] = null;
        }

        if ($request->role === 'student') {
            $userData['student_id'] = $validated['student_id'];
            $userData['course_strand'] = $validated['course_strand'];
            $userData['year_level'] = $validated['year_level'];
            $userData['section'] = $validated['section'] ?? null;
            $userData['employee_id'] = null;
            $userData['department'] = null;
            $userData['expertise'] = null;
        }

        $user->update($userData);

        if ($request->role === 'teacher' && isset($validated['subjects'])) {
            $user->subjects()->sync($validated['subjects']);
        } else {
            $user->subjects()->detach();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
