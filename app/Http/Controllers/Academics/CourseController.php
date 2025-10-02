<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(): View
    {
        $courses = Course::where('is_deleted', false)
            ->orderBy('name')
            ->paginate(10);

        return view('academics.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create(): View
    {
        return view('academics.courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(Course::$rules, Course::$messages);

        $course = Course::create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('academics.courses.index')
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course): View
    {
        return view('academics.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course): View
    {
        return view('academics.courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $rules = Course::$rules;
        $rules['course_code'] = str_replace(
            'unique:courses,course_code',
            'unique:courses,course_code,'.$course->id,
            $rules['course_code']
        );

        $validated = $request->validate($rules, Course::$messages);

        $course->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()
            ->route('academics.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        if ($course->registrations()->exists()) {
            return redirect()
                ->route('academics.courses.index')
                ->with('error', 'Cannot delete course. There are students registered for this course.');
        }

        $course->update(['is_deleted' => true]);

        return redirect()
            ->route('academics.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
