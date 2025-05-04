<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\AcademicsService;
use Illuminate\Http\Request;

class AcademicSettingsController extends Controller
{
    protected $academicsService;
    
    public function __construct(AcademicsService $academicsService)
    {
        $this->academicsService = $academicsService;
        $this->middleware(['auth', 'permission:manage-academics']);
    }
    
    /**
     * Display the settings page
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $semesters = Semester::all();
        $currentAcademicYear = $this->academicsService->getCurrentAcademicYear();
        $currentSemester = $this->academicsService->getCurrentSemester();
        
        return view('academics.settings', compact('academicYears', 'semesters', 'currentAcademicYear', 'currentSemester'));
    }
    
    /**
     * Update the current academic settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);
        
        // Check if the selected semester belongs to the selected academic year
        $semester = Semester::find($validated['semester_id']);
        
        if ($semester && $semester->academic_year_id != $validated['academic_year_id']) {
            return redirect()->back()
                ->withErrors(['semester_id' => 'The selected semester does not belong to the selected academic year.'])
                ->withInput();
        }
        
        // Update current academic year
        $yearSuccess = $this->academicsService->setCurrentAcademicYear($validated['academic_year_id']);
        
        // Update current semester
        $semesterSuccess = $this->academicsService->setCurrentSemester($validated['semester_id']);
        
        if ($yearSuccess && $semesterSuccess) {
            return redirect()->route('academics.settings.index')
                ->with('success', 'Current academic year and semester updated successfully.');
        }
        
        return redirect()->back()->with('error', 'An error occurred while updating settings.');
    }
}