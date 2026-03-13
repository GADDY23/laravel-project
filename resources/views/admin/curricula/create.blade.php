@extends('layouts.admin')
@section('title', 'Create Curriculum')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Curriculum</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form action="{{ route('admin.curricula.store') }}" method="POST" id="curriculum-form">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">School Year Start</label>
                <input type="number" name="school_year_start" id="school_year_start" value="{{ old('school_year_start', date('Y')) }}" min="2000" max="2100" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('school_year_start')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Program Type</label>
                <select name="course_type" id="course_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Type</option>
                    <option value="shs" {{ old('course_type') === 'shs' ? 'selected' : '' }}>SHS</option>
                    <option value="college" {{ old('course_type', 'college') === 'college' ? 'selected' : '' }}>College</option>
                </select>
                @error('course_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Program</label>
                <select name="course_strand" id="course_strand" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></select>
                @error('course_strand')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                <select name="curriculum_type" id="curriculum_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="semestral" {{ old('curriculum_type', 'semestral') === 'semestral' ? 'selected' : '' }}>Semestral</option>
                    <option value="trimestral" {{ old('curriculum_type') === 'trimestral' ? 'selected' : '' }}>Trimestral</option>
                </select>
                @error('curriculum_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mb-4 p-3 rounded border border-blue-200 bg-blue-50 text-sm text-blue-800">
             Curriculum Code: <span class="font-semibold" id="generated-code">-</span>
        </div>

        <div id="year-period-container" class="space-y-6"></div>

        @error('row_subject_ids')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        @error('row_subject_ids.*')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        @error('row_prerequisites.*')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Create</button>
            <a href="{{ route('admin.curricula.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
        </div>
    </form>
</div>

<datalist id="subject-lookup">
    @foreach($subjects as $subject)
        <option
            value="{{ $subject->code ?? $subject->name }}"
            data-id="{{ $subject->id }}"
            data-code="{{ $subject->code ?? 'N/A' }}"
            data-name="{{ $subject->name }}"
            data-description="{{ $subject->description ?? '' }}"
        ></option>
    @endforeach
</datalist>

<script>
const strandsByType = @json($courseStrands);
const oldRows = [];
const previousStrand = @json(old('course_strand'));
const years = [
    { key: '1st_year', label: '1st Year' },
    { key: '2nd_year', label: '2nd Year' },
    { key: '3rd_year', label: '3rd Year' },
    { key: '4th_year', label: '4th Year' },
];

const subjectOptions = Array.from(document.querySelectorAll('#subject-lookup option'));
const yearPeriodContainer = document.getElementById('year-period-container');
const curriculumFormEl = document.getElementById('curriculum-form');
const courseTypeEl = document.getElementById('course_type');
const courseStrandEl = document.getElementById('course_strand');
const curriculumTypeEl = document.getElementById('curriculum_type');
const schoolYearStartEl = document.getElementById('school_year_start');
const generatedCodeEl = document.getElementById('generated-code');

function periodLabel(curriculumType, periodNumber) {
    if (curriculumType === 'trimestral') return `${periodNumber}${periodNumber === 1 ? 'st' : periodNumber === 2 ? 'nd' : 'rd'} Tri`;
    return `${periodNumber}${periodNumber === 1 ? 'st' : 'nd'} Sem`;
}

function periodCount(curriculumType) {
    return curriculumType === 'trimestral' ? 3 : 2;
}

function safeCodePart(value) {
    return String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
}

function renderCourseStrands() {
    const selectedType = courseTypeEl.value;
    const strands = strandsByType[selectedType] || [];
    courseStrandEl.innerHTML = '<option value="">Select Program</option>';
    strands.forEach((strand) => {
        const option = document.createElement('option');
        option.value = strand.name;
        option.textContent = strand.name;
        if (previousStrand && previousStrand === strand.name) {
            option.selected = true;
        }
        courseStrandEl.appendChild(option);
    });
}

function updateGeneratedCode() {
    const year = schoolYearStartEl.value || '0000';
    const type = safeCodePart(courseTypeEl.value || '');
    const strand = safeCodePart(courseStrandEl.value || '');
    const currType = curriculumTypeEl.value ? curriculumTypeEl.value.charAt(0).toUpperCase() + curriculumTypeEl.value.slice(1) : '';

    if (!type || !strand || !currType) {
        generatedCodeEl.textContent = '-';
        return;
    }

    generatedCodeEl.textContent = `${year}-${type}-${strand}-${currType}`;
}

function getSelectedSubjectIds(excludeRow = null) {
    const selected = new Set();
    document.querySelectorAll('#year-period-container .subject-id').forEach((input) => {
        if (excludeRow && excludeRow.contains(input)) return;
        if (input.value) {
            selected.add(String(input.value));
        }
    });
    return selected;
}

function refreshSubjectPickers() {
    document.querySelectorAll('#year-period-container tr').forEach((row) => {
        const datalist = row.querySelector('.subject-datalist');
        const hidden = row.querySelector('.subject-id');
        if (!datalist || !hidden) return;

        const excluded = getSelectedSubjectIds(row);
        datalist.innerHTML = '';
        subjectOptions.forEach((option) => {
            const id = String(option.dataset.id || '');
            if (!id) return;
            if (excluded.has(id)) return;
            const opt = document.createElement('option');
            opt.value = option.value;
            datalist.appendChild(opt);
        });
    });
}

let subjectRowCounter = 0;

function createSubjectRow(yearKey, periodNumber, periodLabelText, preset = null) {
    const rowId = ++subjectRowCounter;
    const subjectDatalistId = `subject-lookup-${rowId}`;

    const row = document.createElement('tr');
    row.className = 'divide-x divide-gray-200';
    row.innerHTML = `
        <td class="px-2 py-2">
            <input type="text" list="${subjectDatalistId}" class="subject-picker w-full rounded border-gray-300" placeholder="Subject code or name">
            <datalist id="${subjectDatalistId}" class="subject-datalist"></datalist>
            <input type="hidden" name="row_subject_ids[]" class="subject-id" value="${preset?.subject_id || ''}">
            <input type="hidden" name="row_year_levels[]" value="${yearKey}">
            <input type="hidden" name="row_period_numbers[]" value="${periodNumber}">
            <input type="hidden" name="row_period_labels[]" value="${periodLabelText}">
        </td>
        <td class="px-2 py-2">
            <input type="text" class="subject-description w-full rounded border-gray-300 bg-gray-100" readonly>
        </td>
        <td class="px-2 py-2">
            <select name="row_prerequisites[]" class="prerequisite-select w-full rounded border-gray-300">
                <option value="">Select prerequisite (optional)</option>
            </select>
        </td>
        <td class="px-2 py-2">
            <button type="button" class="remove-row bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Delete</button>
        </td>
    `;

    const picker = row.querySelector('.subject-picker');
    const hidden = row.querySelector('.subject-id');
    const description = row.querySelector('.subject-description');
    const prereqSelect = row.querySelector('.prerequisite-select');
    const subjectDatalist = row.querySelector('.subject-datalist');

    function formatSubjectOption(option) {
        const code = option.dataset.code !== 'N/A' ? option.dataset.code : option.dataset.name;
        const name = option.dataset.name || '';
        const label = name ? `${code} - ${name}` : code;
        return { value: label, label };
    }

    function setPrerequisiteOptions(subjectId) {
        const subjectOpt = subjectOptions.find((option) => option.dataset.id === String(subjectId));
        const options = subjectOpt
            ? subjectOptions.filter((option) => String(option.dataset.id) !== String(subjectId))
            : subjectOptions.slice();

        prereqSelect.innerHTML = '<option value="">Select prerequisite (optional)</option>';
        options.forEach((option) => {
            const opt = document.createElement('option');
            opt.value = formatSubjectOption(option).label;
            opt.textContent = formatSubjectOption(option).label;
            prereqSelect.appendChild(opt);
        });

        if (!options.length) {
            prereqSelect.innerHTML = '<option value="">No other subjects available</option>';
        }
    }

    function syncSubject() {
        const match = subjectOptions.find((option) => (
            option.value === picker.value ||
            option.dataset.name === picker.value ||
            formatSubjectOption(option).label === picker.value
        ));
        if (!match) {
            hidden.value = '';
            description.value = '';
            setPrerequisiteOptions(null);
            refreshSubjectPickers();
            return;
        }

        const selectedElsewhere = getSelectedSubjectIds(row).has(String(match.dataset.id));
        if (selectedElsewhere) {
            alert('This subject is already selected in another year. Please choose a different subject.');
            picker.value = '';
            hidden.value = '';
            description.value = '';
            setPrerequisiteOptions(null);
            refreshSubjectPickers();
            return;
        }

        hidden.value = match.dataset.id;
        description.value = match.dataset.description || match.dataset.name || '';
        picker.value = match.dataset.code !== 'N/A' ? match.dataset.code : match.dataset.name;
        setPrerequisiteOptions(match.dataset.id);
        refreshSubjectPickers();
    }

    picker.addEventListener('input', syncSubject);
    row.querySelector('.remove-row').addEventListener('click', () => {
        row.remove();
        refreshSubjectPickers();
    });

    if (preset?.subject_id) {
        const selected = subjectOptions.find((option) => option.dataset.id === String(preset.subject_id));
        if (selected) {
            picker.value = selected.dataset.code !== 'N/A' ? selected.dataset.code : selected.dataset.name;
            description.value = selected.dataset.description || selected.dataset.name || '';
            setPrerequisiteOptions(String(preset.subject_id));
            if (preset?.prerequisite) {
                prereqSelect.value = preset.prerequisite;
            }
        }
    } else {
        setPrerequisiteOptions(null);
    }

    refreshSubjectPickers();
    return row;
}

function renderYearPeriodBlocks() {
    const cType = curriculumTypeEl.value;
    const periods = periodCount(cType);
    yearPeriodContainer.innerHTML = '';

    years.forEach((year) => {
        for (let p = 1; p <= periods; p++) {
            const pLabel = periodLabel(cType, p);
            const block = document.createElement('div');
            block.className = 'border border-gray-200 rounded-lg p-4';
            block.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${year.label} ${pLabel}</h3>
                    <button type="button" class="add-row bg-blue-900 hover:bg-blue-800 text-white px-3 py-1 rounded text-sm">Add subjects</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Subject Code</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Description</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Prerequisite</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="rows"></tbody>
                    </table>
                </div>
            `;

            const tbody = block.querySelector('.rows');
            const addBtn = block.querySelector('.add-row');

            addBtn.addEventListener('click', () => {
                tbody.appendChild(createSubjectRow(year.key, p, pLabel));
                refreshSubjectPickers();
            });

            const blockRows = oldRows.filter((r) => r.year_level === year.key && Number(r.period_number) === p);
            if (blockRows.length) {
                blockRows.forEach((r) => tbody.appendChild(createSubjectRow(year.key, p, pLabel, r)));
            } else {
                tbody.appendChild(createSubjectRow(year.key, p, pLabel));
            }

            yearPeriodContainer.appendChild(block);
        }
    });
    refreshSubjectPickers();
}

courseTypeEl.addEventListener('change', () => {
    renderCourseStrands();
    updateGeneratedCode();
});
courseStrandEl.addEventListener('change', updateGeneratedCode);
curriculumTypeEl.addEventListener('change', () => {
    renderYearPeriodBlocks();
    updateGeneratedCode();
});
schoolYearStartEl.addEventListener('input', updateGeneratedCode);
curriculumFormEl.addEventListener('submit', () => {
    document.querySelectorAll('#year-period-container tr').forEach((row) => {
        const subjectId = row.querySelector('.subject-id');
        if (!subjectId || !subjectId.value) {
            row.remove();
        }
    });
});

renderCourseStrands();
renderYearPeriodBlocks();
updateGeneratedCode();
</script>
@endsection
