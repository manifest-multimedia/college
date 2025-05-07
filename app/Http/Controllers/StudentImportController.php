<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}