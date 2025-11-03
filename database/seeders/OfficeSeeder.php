<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if departments exist first
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            $this->command->warn('No departments found. Please seed departments first.');
            return;
        }

        // Sample offices for each department
        foreach ($departments as $department) {
            Office::create([
                'department_id' => $department->id,
                'name' => $department->name . ' Main Office',
                'code' => $department->code . '-MAIN',
                'location' => 'Main Building',
                'phone' => '555-0100',
                'email' => strtolower(str_replace(' ', '', $department->name)) . '@college.local',
                'description' => 'Main office for ' . $department->name,
                'is_active' => true,
            ]);

            Office::create([
                'department_id' => $department->id,
                'name' => $department->name . ' Admin Office',
                'code' => $department->code . '-ADM',
                'location' => 'Administration Block',
                'phone' => '555-0101',
                'email' => strtolower(str_replace(' ', '', $department->name)) . '.admin@college.local',
                'description' => 'Administrative office for ' . $department->name,
                'is_active' => true,
            ]);
        }

        $this->command->info('Sample offices created successfully!');
    }
}
