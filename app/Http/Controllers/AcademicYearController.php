<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademicYearController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // $this->middleware(['auth', 'permission:manage-academics']);
    }
    
    /**
     * Display a listing of academic years.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->paginate(10);
        
        return view('academics.academic-years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new academic year.
     */
    public function create()
    {
        return view('academics.academic-years.create');
    }

    /**
     * Store a newly created academic year in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        
        // Extract year from start date
        $year = date('Y', strtotime($validated['start_date']));
        
        $academicYear = AcademicYear::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'year' => $year,
            'created_by' => auth()->id(),
        ]);
        
        return redirect()->route('academics.academic-years.index')
            ->with('success', 'Academic year created successfully.');
    }

    /**
     * Display the specified academic year.
     */
    public function show(AcademicYear $academicYear)
    {
        return view('academics.academic-years.show', compact('academicYear'));
    }

    /**
     * Show the form for editing the specified academic year.
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('academics.academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        
        // Extract year from start date
        $year = date('Y', strtotime($validated['start_date']));
        
        $academicYear->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'year' => $year,
        ]);
        
        return redirect()->route('academics.academic-years.index')
            ->with('success', 'Academic year updated successfully.');
    }

    /**
     * Remove the specified academic year from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        // Check if the academic year has any semesters
        if ($academicYear->semesters->count() > 0) {
            return redirect()->route('academics.academic-years.index')
                ->with('error', 'Cannot delete academic year with associated semesters.');
        }
        
        $academicYear->delete();
        
        return redirect()->route('academics.academic-years.index')
            ->with('success', 'Academic year deleted successfully.');
    }
}