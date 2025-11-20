<?php

namespace App\Livewire;

use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Year;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class CourseImport extends Component
{
    use WithFileUploads;

    public $file;

    public $progress = 0;

    public $importing = false;

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Validate file types and size
    ];

    public function render()
    {
        return view('livewire.course-import');
    }

    public function updatedFile()
    {
        // Automatically reset progress when a new file is uploaded
        $this->progress = 0;
    }

    public function importCourses()
    {
        $this->validate();

        $this->importing = true;

        // Start a new import process
        $this->dispatch('import-start');

        // Load the Excel file
        $filePath = $this->file->getRealPath();
        $data = Excel::toCollection(null, $filePath)->first();
        $data = $data->slice(1); // Skip the header row

        // Check if data was loaded
        if (! $data || $data->isEmpty()) {
            session()->flash('error', 'No data found in the Excel sheet.');
            $this->importing = false;

            return;
        }

        $totalRows = $data->count();
        $processedRows = 0;

        foreach ($data as $row) {
            if (! $this->importing) {
                break;
            }

            if (! isset($row[0], $row[1], $row[2], $row[3], $row[4])) {
                continue; // Skip rows with missing data
            }

            // Map the fields based on index positions in each row
            $courseCode = $row[0];
            $courseName = $row[1];
            $semesterName = $row[2];
            $yearName = $row[3];
            $programName = $row[4];

            // Process the data (find or create)
            $semester = Semester::firstOrCreate(['name' => $semesterName]);
            $year = Year::firstOrCreate(['name' => $yearName]);
            $program = CollegeClass::firstOrCreate(
                ['name' => $programName],
                ['slug' => generateSlug($programName)]
            );

            // Create the subject
            $subject = Subject::firstOrCreate(
                ['course_code' => $courseCode, 'name' => $courseName, 'college_class_id' => $program->id],
                [
                    'slug' => generateSlug($courseName),
                    'semester_id' => $semester->id,
                    'year_id' => $year->id,
                ]
            );

            $processedRows++;

            // Calculate the progress (rounded percentage)
            $this->progress = (int) (($processedRows / $totalRows) * 100);

            // Simulate progress with a slight delay (for UI responsiveness)
            usleep(50000); // Sleep for 50ms (simulate a small delay per row)
        }

        // If the loop finishes or user cancels
        $this->importing = false;
        $this->dispatch('import-complete');
        usleep(50000);
        $this->progress = 100;

        session()->flash('message', 'Courses imported successfully!');
    }
}
