@extends('layouts.admin')
@section('title', 'Configure Schedule')
@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configure Schedule</h1>
    <a href="{{ route('admin.schedules.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back to List</a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-3xl">
    <form action="{{ route('admin.schedules.timetable') }}" method="GET" class="space-y-6">
        @if(request()->boolean('reset'))
            <input type="hidden" name="reset" value="1">
        @endif
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Schedule Name</label>
            <input type="text" name="schedule_name" value="{{ old('schedule_name', $scheduleName ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="E.g. BSIS Timetable - 1st Semester" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Term</label>
            <select name="term_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                <option value="">Select Term</option>
                @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ ($selectedTermId ?? '') == $term->id ? 'selected' : '' }}>{{ $term->term_code }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rooms</label>
            <p class="text-xs text-gray-500 mt-1">Click one or more rooms to include them in the generated timetable.</p>
            <div id="room-picker" class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($rooms as $room)
                    @php $selected = in_array($room->id, (array) ($selectedRooms ?? [])); @endphp
                    <label class="room-option relative cursor-pointer rounded-lg border px-3 py-2 text-sm font-medium transition-colors {{ $selected ? 'border-indigo-500 bg-indigo-50 dark:border-indigo-400 dark:bg-indigo-900/30' : 'border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800' }}">
                        <input type="checkbox" name="rooms[]" value="{{ $room->id }}" class="sr-only room-checkbox" {{ $selected ? 'checked' : '' }}>
                        <div class="flex items-center justify-between">
                            <span>{{ $room->name }}</span>
                            <span class="room-pill inline-flex items-center justify-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $selected ? 'border-indigo-500 bg-indigo-600 text-white' : 'border-gray-300 bg-white text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200' }}">
                                {{ $selected ? 'Selected' : 'Select' }}
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('#room-picker .room-option').forEach((label) => {
                    const checkbox = label.querySelector('input.room-checkbox');
                    const pill = label.querySelector('.room-pill');

                    const updateState = () => {
                        if (checkbox.checked) {
                            label.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:border-indigo-400', 'dark:bg-indigo-900/30');
                            label.classList.remove('border-gray-300', 'bg-white', 'dark:border-gray-700', 'dark:bg-gray-800');
                            pill.classList.remove('border-gray-300', 'bg-white', 'text-gray-600', 'dark:border-gray-600', 'dark:bg-gray-700', 'dark:text-gray-200');
                            pill.classList.add('border-indigo-500', 'bg-indigo-600', 'text-white');
                            pill.textContent = 'Selected';
                        } else {
                            label.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:border-indigo-400', 'dark:bg-indigo-900/30');
                            label.classList.add('border-gray-300', 'bg-white', 'dark:border-gray-700', 'dark:bg-gray-800');
                            pill.classList.add('border-gray-300', 'bg-white', 'text-gray-600', 'dark:border-gray-600', 'dark:bg-gray-700', 'dark:text-gray-200');
                            pill.classList.remove('border-indigo-500', 'bg-indigo-600', 'text-white');
                            pill.textContent = 'Select';
                        }
                    };

                    label.addEventListener('click', (event) => {
                        event.preventDefault();
                        checkbox.checked = !checkbox.checked;
                        updateState();
                    });

                    checkbox.addEventListener('change', updateState);

                    // Initialize state so already-checked items render correctly
                    updateState();
                });
            });
        </script>

        <div class="flex items-center gap-3">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Generate Timetable</button>
            <a href="{{ route('admin.schedules.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>
@endsection
