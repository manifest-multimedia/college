<?php

namespace App\Livewire\Academics;

use App\Models\Course;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCourses extends Component
{
    use WithPagination;

    public $name;

    public $course_code;

    public $description;

    public $editMode = false;

    public $editingId;

    public $searchTerm = '';

    protected function rules()
    {
        $rules = Course::$rules;

        // Modify course_code uniqueness rule for updates
        if ($this->editMode) {
            $rules['course_code'] = str_replace(
                'unique:courses,course_code',
                'unique:courses,course_code,'.$this->editingId,
                $rules['course_code']
            );
        }

        return $rules;
    }

    protected $messages = [];

    public function boot()
    {
        $this->messages = Course::$messages;
    }

    protected $listeners = [
        'refreshCourses' => '$refresh',
        'delete' => 'delete',
    ];

    public function render()
    {
        $courses = Course::query()
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('course_code', 'like', '%'.$this->searchTerm.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.academics.manage-courses', [
            'courses' => $courses,
        ]);
    }

    public function create()
    {
        $this->validate();

        Course::create([
            'name' => $this->name,
            'course_code' => $this->course_code,
            'description' => $this->description,
            'slug' => Str::slug($this->name),
            'created_by' => auth()->id(),
        ]);

        $this->reset(['name', 'course_code', 'description']);
        session()->flash('success', 'Course created successfully.');
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $this->editingId = $id;
        $this->name = $course->name;
        $this->course_code = $course->course_code;
        $this->description = $course->description;
        $this->editMode = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:3',
            'course_code' => 'required|min:2|unique:courses,course_code,'.$this->editingId,
            'description' => 'nullable',
        ]);

        $course = Course::findOrFail($this->editingId);
        $course->update([
            'name' => $this->name,
            'course_code' => $this->course_code,
            'description' => $this->description,
            'slug' => Str::slug($this->name),
        ]);

        $this->reset(['name', 'course_code', 'description', 'editMode', 'editingId']);
        session()->flash('success', 'Course updated successfully.');
    }

    public function delete($id)
    {
        try {
            $course = Course::findOrFail($id);

            // Check if course has any active registrations
            if ($course->registrations()->exists()) {
                session()->flash('error', 'Cannot delete course. There are students registered for this course.');

                return;
            }

            // Soft delete by setting is_deleted flag
            $course->update(['is_deleted' => true]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course deleted successfully.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error deleting course: '.$e->getMessage(),
            ]);
        }
    }

    public function cancel()
    {
        $this->reset(['name', 'course_code', 'description', 'editMode', 'editingId']);
    }
}
