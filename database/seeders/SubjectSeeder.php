<?php

namespace Database\Seeders;

use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        // Define the correct file path
        // $filePath = public_path('storage/datasets/subjects.xlsx');
        $filePath = public_path('datasets/subjects.xlsx');

        // Load the Excel data, skipping the first row for headers
        $data = Excel::toCollection(null, $filePath)->first();

        // Skip the first row (header)
        $data = $data->slice(1);

        // Check if data was loaded
        if (! $data || $data->isEmpty()) {
            Log::warning('No data found in the Excel sheet.');
            $this->command->info('No data found in the Excel sheet.');

            return;
        }

        // Loop through each row in the Excel sheet
        foreach ($data as $row) {
            // Ensure the necessary keys are present
            if (! isset($row[0], $row[1], $row[2], $row[3], $row[4])) {
                Log::warning('Skipping row due to missing required fields', $row->toArray());

                continue;
            }

            // Map the fields based on index positions in each row
            $courseCode = $row[0];
            $courseName = $row[1];
            $semesterName = $row[2];
            $yearName = $row[3];
            $programName = $row[4];

            // Find or create the Semester
            $semester = Semester::firstOrCreate(['name' => $semesterName]);

            // Find or create the Year
            $year = Year::firstOrCreate(['name' => $yearName]);

            // Find or create the Program (CollegeClass)
            $program = CollegeClass::firstOrCreate(
                ['name' => $programName],
                ['slug' => generateSlug($programName)]
            );

            // Find or create the Subject
            // Ensure the combination of `course_code`, `course_name`, and `college_class_id` is unique
            $subject = Subject::firstOrCreate(
                ['course_code' => $courseCode, 'name' => $courseName, 'college_class_id' => $program->id],
                [
                    'slug' => generateSlug($courseName),
                    'semester_id' => $semester->id,
                    'year_id' => $year->id,
                ]
            );

            // Confirm subject creation
            if ($subject->wasRecentlyCreated) {
                Log::info('Subject created: '.$subject->name);
                $this->command->info('Subject created: '.$subject->name);
            } else {
                Log::info('Subject already exists: '.$subject->name);
                $this->command->info('Subject already exists: '.$subject->name);
            }
        }
    }
}
