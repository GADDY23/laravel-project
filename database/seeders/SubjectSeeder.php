<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
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
}
