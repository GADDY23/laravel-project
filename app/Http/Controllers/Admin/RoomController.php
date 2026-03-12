<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::available();

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('building', 'like', '%' . $request->search . '%')
                  ->orWhere('room_type', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('room_type') && $request->room_type) {
            $query->where('room_type', $request->room_type);
        }

        $rooms = $query->orderBy('name')->paginate(20);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('rooms', 'name')->where(fn ($query) => $query->where('building', $request->building)),
                ],
                'capacity' => 'required|integer|min:1',
                'building' => 'required|in:Aclc Main,Aclc SHS',
                'floor' => 'nullable|in:1st_floor,2nd_floor,3rd_floor,4th_floor',
                'room_type' => 'required|in:lecture,computer_lab,chemistry_lab',
                'status' => 'required|in:available,unavailable',
                'description' => 'nullable|string',
            ],
            [
                'name.unique' => 'Room name already exists in this building.',
            ]
        );

        Room::create($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        $room->load('schedules');
        return view('admin.rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('rooms', 'name')
                        ->where(fn ($query) => $query->where('building', $request->building))
                        ->ignore($room->id),
                ],
                'capacity' => 'required|integer|min:1',
                'building' => 'required|in:Aclc Main,Aclc SHS',
                'floor' => 'nullable|in:1st_floor,2nd_floor,3rd_floor,4th_floor',
                'room_type' => 'required|in:lecture,computer_lab,chemistry_lab',
                'status' => 'required|in:available,unavailable',
                'description' => 'nullable|string',
            ],
            [
                'name.unique' => 'Room name already exists in this building.',
            ]
        );

        $room->update($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
