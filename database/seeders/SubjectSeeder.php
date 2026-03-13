<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = base_path('database/seeders/data/subjects.csv');
        $subjects = file_exists($csvPath)
            ? $this->loadSubjectsFromCsv($csvPath)
            : [
            [
                'name' => 'General Mathematics',
                'code' => 'GENMATH',
                'year_level' => 'grade_11',
                'course_strand' => 'ABM',
                'lec_unit' => 3,
                'lab_unit' => 0,
                'required_room_type' => 'lecture',
                'status' => 'active',
                'description' => 'Core mathematics for SHS.',
            ],
            [
                'name' => 'Basic Calculus',
                'code' => 'BSCALC',
                'year_level' => 'grade_12',
                'course_strand' => 'STEM',
                'lec_unit' => 3,
                'lab_unit' => 0,
                'required_room_type' => 'lecture',
                'status' => 'active',
                'description' => 'Differential and integral calculus.',
            ],
            [
                'name' => 'Oral Communication',
                'code' => 'ORALCOM',
                'year_level' => 'grade_11',
                'course_strand' => 'HUMSS',
                'lec_unit' => 3,
                'lab_unit' => 0,
                'required_room_type' => 'lecture',
                'status' => 'active',
                'description' => 'Communication skills and presentations.',
            ],
            [
                'name' => 'Computer Programming 1',
                'code' => 'PROG1',
                'year_level' => '1st_year',
                'course_strand' => 'BSIT',
                'lec_unit' => 2,
                'lab_unit' => 1,
                'required_room_type' => 'computer_lab',
                'status' => 'active',
                'description' => 'Introduction to programming fundamentals.',
            ],
            [
                'name' => 'Data Structures',
                'code' => 'DATASTR',
                'year_level' => '2nd_year',
                'course_strand' => 'BSCS',
                'lec_unit' => 2,
                'lab_unit' => 1,
                'required_room_type' => 'computer_lab',
                'status' => 'active',
                'description' => 'Arrays, lists, stacks, queues, and trees.',
            ],
            [
                'name' => 'Database Systems',
                'code' => 'DBSYS',
                'year_level' => '2nd_year',
                'course_strand' => 'BSIT',
                'lec_unit' => 2,
                'lab_unit' => 1,
                'required_room_type' => 'computer_lab',
                'status' => 'active',
                'description' => 'Relational database concepts and SQL.',
            ],
            [
                'name' => 'General Chemistry',
                'code' => 'GENCHEM',
                'year_level' => 'grade_11',
                'course_strand' => 'STEM',
                'lec_unit' => 2,
                'lab_unit' => 1,
                'required_room_type' => 'chemistry_lab',
                'status' => 'active',
                'description' => 'Fundamentals of chemistry with lab.',
            ],
            [
                'name' => 'Physical Education 1',
                'code' => 'PE1',
                'year_level' => '1st_year',
                'course_strand' => 'BSIT',
                'lec_unit' => 2,
                'lab_unit' => 0,
                'required_room_type' => 'lecture',
                'status' => 'active',
                'description' => 'Fitness and wellness basics.',
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(['code' => $subject['code']], $subject);
        }
    }

    private function loadSubjectsFromCsv(string $path): array
    {
        $rows = [];
        if (!is_readable($path)) {
            Log::warning('SubjectSeeder CSV not readable.', ['path' => $path]);
            return $rows;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            Log::warning('SubjectSeeder failed to open CSV.', ['path' => $path]);
            return $rows;
        }

        $header = null;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($header === null) {
                $header = array_map([$this, 'normalizeHeader'], $data);
                continue;
            }
            if ($this->isEmptyRow($data)) {
                continue;
            }
            $row = [];
            foreach ($header as $index => $key) {
                if ($key === '') {
                    continue;
                }
                $row[$key] = $data[$index] ?? null;
            }

            $subject = $this->mapCsvRow($row);
            if (empty($subject['code']) && empty($subject['name'])) {
                continue;
            }
            if (empty($subject['code'])) {
                $subject['code'] = strtoupper(preg_replace('/\s+/', '', (string) $subject['name']));
            }
            $rows[] = $subject;
        }

        fclose($handle);
        return $rows;
    }

    private function normalizeHeader(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }

    private function isEmptyRow(array $data): bool
    {
        foreach ($data as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function mapCsvRow(array $row): array
    {
        $code = $row['code'] ?? $row['subject_code'] ?? $row['subjectcode'] ?? null;
        $name = $row['name'] ?? $row['subject_name'] ?? $row['subjectname'] ?? null;
        $yearLevel = $row['year_level'] ?? $row['year'] ?? null;
        $courseStrand = $row['course_strand'] ?? $row['strand'] ?? $row['program'] ?? null;
        $lecUnit = $row['lec_unit'] ?? $row['lecture_unit'] ?? $row['unit'] ?? $row['hours'] ?? 0;
        $labUnit = $row['lab_unit'] ?? $row['laboratory_unit'] ?? 0;
        $roomType = $row['required_room_type'] ?? $row['room_type'] ?? 'lecture';
        $status = $row['status'] ?? 'active';
        $description = $row['description'] ?? null;

        return [
            'code' => $code ? trim((string) $code) : null,
            'name' => $name ? trim((string) $name) : null,
            'year_level' => $yearLevel ? trim((string) $yearLevel) : null,
            'course_strand' => $courseStrand ? trim((string) $courseStrand) : null,
            'lec_unit' => is_numeric($lecUnit) ? (float) $lecUnit : 0,
            'lab_unit' => is_numeric($labUnit) ? (float) $labUnit : 0,
            'required_room_type' => $roomType ? trim((string) $roomType) : 'lecture',
            'status' => $status ? trim((string) $status) : 'active',
            'description' => $description ? trim((string) $description) : null,
        ];
    }
}
