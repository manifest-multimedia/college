<?php

namespace Database\Seeders;

use App\Imports\QuestionImport;
use Illuminate\Database\Seeder; // Correct facade
use Maatwebsite\Excel\Facades\Excel;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure you have a valid exam_id
        // $exam_id = 5;
        $exam_id = 13;
        // Path to the Excel file
        $path = public_path('datasets/abnormal_p.xlsx');

        // Import questions using the QuestionImport class and pass the exam_id if necessary
        Excel::import(new QuestionImport($exam_id), $path);
    }
}
