<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportGenerator extends Controller
{
    public function studentReport(Request $request)
    {
        $class = $request->input('class');
        // Generate the report and return the response
        generateReport('Student Report', $class);
    }
}
