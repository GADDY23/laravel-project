<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'name' => 'ABM-11A',
                'course_strand' => 'ABM',
                'year_level' => 'grade_11',
                'capacity' => 40,
                'status' => 'active',
            ],
            [
                'name' => 'ABM-12A',
                'course_strand' => 'ABM',
                'year_level' => 'grade_12',
                'capacity' => 40,
                'status' => 'active',
            ],
            [
                'name' => 'STEM-11A',
                'course_strand' => 'STEM',
                'year_level' => 'grade_11',
                'capacity' => 42,
                'status' => 'active',
            ],
            [
                'name' => 'HUMSS-12A',
                'course_strand' => 'HUMSS',
                'year_level' => 'grade_12',
                'capacity' => 38,
                'status' => 'active',
            ],
            [
                'name' => 'BSIT-1A',
                'course_strand' => 'BSIT',
                'year_level' => '1st_year',
                'capacity' => 35,
                'status' => 'active',
            ],
            [
                'name' => 'BSIT-2A',
                'course_strand' => 'BSIT',
                'year_level' => '2nd_year',
                'capacity' => 35,
                'status' => 'active',
            ],
            [
                'name' => 'BSCS-1A',
                'course_strand' => 'BSCS',
                'year_level' => '1st_year',
                'capacity' => 32,
                'status' => 'active',
            ],
        ];

        foreach ($sections as $section) {
            Section::updateOrCreate(
                [
                    'name' => $section['name'],
                    'course_strand' => $section['course_strand'],
                    'year_level' => $section['year_level'],
                ],
                $section,
            );
        }
    }
}
