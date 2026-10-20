<?php

namespace App\Http\Controllers;

use App\Exports\StudentImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    /**
     * Show the student import page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('students.import');
    }

    /**
     * Download the recommended spreadsheet layout for student imports.
     */
    public function downloadTemplate()
    {
        return Excel::download(
            new StudentImportTemplateExport,
            'student-import-template.xlsx'
        );
    }
}
