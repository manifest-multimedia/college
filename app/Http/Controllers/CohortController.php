<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CohortController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // $this->middleware(['auth', 'permission:manage-academics']);
    }

    /**
     * Display a listing of cohorts.
     */
    public function index()
    {
        $cohorts = Cohort::paginate(10);

        return view('academics.cohorts.index', compact('cohorts'));
    }

    /**
     * Show the form for creating a new cohort.
     */
    public function create()
    {
        return view('academics.cohorts.create');
    }

    /**
     * Store a newly created cohort in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cohorts',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $cohort = Cohort::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_deleted' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('academics.cohorts.index')
            ->with('success', 'Cohort created successfully.');
    }

    /**
     * Display the specified cohort.
     */
    public function show(Cohort $cohort)
    {
        $cohort->load('students');

        return view('academics.cohorts.show', compact('cohort'));
    }

    /**
     * Show the form for editing the specified cohort.
     */
    public function edit(Cohort $cohort)
    {
        return view('academics.cohorts.edit', compact('cohort'));
    }

    /**
     * Update the specified cohort in storage.
     */
    public function update(Request $request, Cohort $cohort)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cohorts,name,'.$cohort->id,
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $cohort->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $validated['is_active'] ?? false,
        ]);

        return redirect()->route('academics.cohorts.index')
            ->with('success', 'Cohort updated successfully.');
    }

    /**
     * Remove the specified cohort from storage.
     */
    public function destroy(Cohort $cohort)
    {
        // Check if the cohort has any students
        if ($cohort->students()->count() > 0) {
            return redirect()->route('academics.cohorts.index')
                ->with('error', 'Cannot delete cohort with associated students.');
        }

        $cohort->delete();

        return redirect()->route('academics.cohorts.index')
            ->with('success', 'Cohort deleted successfully.');
    }
}
