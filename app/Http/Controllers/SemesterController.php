<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SemesterController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // $this->middleware(['auth', 'permission:manage-academics']);
    }
    
    /**
     * Display a listing of semesters.
     */
    public function index()
    {
        $semesters = Semester::with('academicYear')->paginate(10);
        
        return view('academics.semesters.index', compact('semesters'));
    }

    /**
     * Show the form for creating a new semester.
     */
    public function create()
    {
        $academicYears = AcademicYear::all();
        return view('academics.semesters.create', compact('academicYears'));
    }

    /**
     * Store a newly created semester in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        
        // Check if the dates fall within the academic year dates
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);
        if ($validated['start_date'] < $academicYear->start_date || $validated['end_date'] > $academicYear->end_date) {
            return redirect()->back()
                ->withErrors(['date_range' => 'Semester dates must fall within the academic year date range.'])
                ->withInput();
        }
        
        $semester = Semester::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'academic_year_id' => $validated['academic_year_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);
        
        return redirect()->route('academics.semesters.index')
            ->with('success', 'Semester created successfully.');
    }

    /**
     * Display the specified semester.
     */
    public function show(Semester $semester)
    {
        $semester->load('academicYear');
        return view('academics.semesters.show', compact('semester'));
    }

    /**
     * Show the form for editing the specified semester.
     */
    public function edit(Semester $semester)
    {
        $academicYears = AcademicYear::all();
        return view('academics.semesters.edit', compact('semester', 'academicYears'));
    }

    /**
     * Update the specified semester in storage.
     */
    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        
        // Check if the dates fall within the academic year dates
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);
        if ($validated['start_date'] < $academicYear->start_date || $validated['end_date'] > $academicYear->end_date) {
            return redirect()->back()
                ->withErrors(['date_range' => 'Semester dates must fall within the academic year date range.'])
                ->withInput();
        }
        
        $semester->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'academic_year_id' => $validated['academic_year_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);
        
        return redirect()->route('academics.semesters.index')
            ->with('success', 'Semester updated successfully.');
    }

    /**
     * Remove the specified semester from storage.
     */
    public function destroy(Semester $semester)
    {
        // Check if the semester has any classes
        if ($semester->collegeClasses()->count() > 0) {
            return redirect()->route('academics.semesters.index')
                ->with('error', 'Cannot delete semester with associated classes.');
        }
        
        $semester->delete();
        
        return redirect()->route('academics.semesters.index')
            ->with('success', 'Semester deleted successfully.');
    }

    /**
     * Toggle the active status of the semester.
     */
    public function toggleActive(Semester $semester)
    {
        try {
            if ($semester->is_current) {
                // If already active, just deactivate it
                $semester->is_current = false;
                $semester->save();
                $message = 'Semester deactivated successfully.';
            } else {
                // If not active, set it as current (which deactivates all others)
                $result = $semester->setAsCurrent();
                if (!$result) {
                    throw new \Exception('Failed to set semester as active');
                }
                $message = 'Semester activated successfully.';
            }
            
            return redirect()->route('academics.semesters.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error toggling semester active status: ' . $e->getMessage());
            return redirect()->route('academics.semesters.index')
                ->with('error', 'An error occurred while updating semester status: ' . $e->getMessage());
        }
    }
}