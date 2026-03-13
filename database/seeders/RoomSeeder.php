<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'name' => '101',
                'capacity' => 40,
                'building' => 'Aclc Main',
                'floor' => '1st_floor',
                'room_type' => 'lecture',
                'status' => 'available',
                'description' => 'Standard lecture room.',
            ],
            [
                'name' => '102',
                'capacity' => 40,
                'building' => 'Aclc Main',
                'floor' => '1st_floor',
                'room_type' => 'lecture',
                'status' => 'available',
                'description' => 'Standard lecture room.',
            ],
            [
                'name' => 'Computer Lab 1',
                'capacity' => 32,
                'building' => 'Aclc Main',
                'floor' => '2nd_floor',
                'room_type' => 'computer_lab',
                'status' => 'available',
                'description' => 'Computer laboratory for IT classes.',
            ],
            [
                'name' => 'Computer Lab 2',
                'capacity' => 32,
                'building' => 'Aclc Main',
                'floor' => '2nd_floor',
                'room_type' => 'computer_lab',
                'status' => 'available',
                'description' => 'Computer laboratory for programming sessions.',
            ],
            [
                'name' => 'Chem Lab 1',
                'capacity' => 24,
                'building' => 'Aclc SHS',
                'floor' => '3rd_floor',
                'room_type' => 'chemistry_lab',
                'status' => 'available',
                'description' => 'Chemistry laboratory for experiments.',
            ],
            [
                'name' => 'Room 301',
                'capacity' => 45,
                'building' => 'Aclc SHS',
                'floor' => '3rd_floor',
                'room_type' => 'lecture',
                'status' => 'available',
                'description' => 'Large lecture room.',
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['name' => $room['name']], $room);
        }
    }
}
