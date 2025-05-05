<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GradeController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // $this->middleware(['auth', 'permission:manage-academics']);
    }
    
    /**
     * Display a listing of grade types.
     */
    public function index()
    {
        $grades = Grade::paginate(10);
        
        return view('academics.grades.index', compact('grades'));
    }

    /**
     * Show the form for creating a new grade type.
     */
    public function create()
    {
        return view('academics.grades.create');
    }

    /**
     * Store a newly created grade type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:grades',
            'type' => 'nullable|string|max:255',
            'value' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $grade = Grade::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
            'created_by' => auth()->id(),
        ]);
        
        return redirect()->route('academics.grades.index')
            ->with('success', 'Grade type created successfully.');
    }

    /**
     * Display the specified grade type.
     */
    public function show(Grade $grade)
    {
        return view('academics.grades.show', compact('grade'));
    }

    /**
     * Show the form for editing the specified grade type.
     */
    public function edit(Grade $grade)
    {
        return view('academics.grades.edit', compact('grade'));
    }

    /**
     * Update the specified grade type in storage.
     */
    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:grades,name,' . $grade->id,
            'type' => 'nullable|string|max:255',
            'value' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $grade->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
        ]);
        
        return redirect()->route('academics.grades.index')
            ->with('success', 'Grade type updated successfully.');
    }

    /**
     * Remove the specified grade type from storage.
     */
    public function destroy(Grade $grade)
    {
        // Check if the grade type has any student grades
        if ($grade->studentGrades()->count() > 0) {
            return redirect()->route('academics.grades.index')
                ->with('error', 'Cannot delete grade type with associated student grades.');
        }
        
        $grade->delete();
        
        return redirect()->route('academics.grades.index')
            ->with('success', 'Grade type deleted successfully.');
    }
}