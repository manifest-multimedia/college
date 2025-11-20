<?php

namespace Database\Seeders;

use App\Models\CollegeClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentData extends Seeder
{
    public function run()
    {
        // Dataset Location: public/datasets/students.csv

        // $path = storage_path('app/public/datasets/correct_data.csv');
        $path = public_path('datasets/year_three_students.csv');
        // $handle = fopen('storage/app/public/datasets/correct_data.csv', 'r');

        $handle = fopen($path, 'r');

        // Get the column names from the first row
        $columnNames = fgetcsv($handle);

        // Initialize an empty array to store the data
        $data = [];

        // Iterate over the remaining rows
        while (($row = fgetcsv($handle)) !== false) {
            // Combine the column names with the row data to create an associative array
            $rowData = array_combine($columnNames, $row);

            // Now you can access the data using the column names
            echo $rowData['first_name']."\n";

            // You can also use the data to create a new student
            // $studentId = generateStudentID(null, $rowData['class']);
            // $existingStudent = Student::where('student_id', $studentId)->first();

            $student = Student::firstOrCreate([
                'student_id' => $rowData['index_number'] ? $rowData['index_number'] : null,
                'first_name' => ucfirst(strtolower($rowData['first_name'])),
                'last_name' => ucfirst(strtolower($rowData['last_name'])),
                'other_name' => ucfirst(strtolower($rowData['other_names'])),
                'gender' => ucfirst(strtolower($rowData['gender'])),
                'date_of_birth' => ucfirst(strtolower($rowData['date_of_birth'])),
                'nationality' => ucfirst(strtolower($rowData['nationality'])),
                'marital_status' => ucfirst(strtolower($rowData['marital_status'])),
                'country_of_residence' => ucfirst(strtolower($rowData['country_of_residence'])),
                'home_region' => ucfirst(strtolower($rowData['home_region'])),
                'home_town' => ucfirst(strtolower($rowData['home_town'])),
                'religion' => ucfirst(strtolower($rowData['religion'])),
                'mobile_number' => $rowData['mobile_number'],
                'email' => strtolower($rowData['email']),
                'postal_address' => ucfirst(strtolower($rowData['postal_address'])),
                'residential_address' => ucfirst(strtolower($rowData['residential_address'])),
                'gps_address' => 'BD-0003-5130',
            ]);
            $class = CollegeClass::firstOrCreate([
                'name' => $rowData['class'],
                'slug' => strtolower($rowData['class']),
            ]);
            // Associate the student with the class
            $student->collegeClass()->associate($class);
            $student->save();
        }

        // Close the file

        fclose($handle);
    }
}
