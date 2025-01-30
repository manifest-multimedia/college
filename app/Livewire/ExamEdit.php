<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\Course;

class ExamEdit extends Component
{
    public $exam_slug;
    public $exam;
    public $duration;
    public $password;
    public $status;
    public $questions_per_session;
    public $passing_percentage;
    
    protected function rules()
    {
        return [
            'duration' => 'sometimes|integer|min:1',
            'password' => 'sometimes|string|min:4',
            'status' => 'sometimes|in:upcoming,active,completed',
            'questions_per_session' => 'sometimes|integer|min:1',
            'passing_percentage' => 'sometimes|numeric|min:0|max:100'
        ];
    }

    public function mount($exam_slug)
    {
        $this->exam_slug = $exam_slug;
        $this->exam = Exam::where('slug', $exam_slug)->firstOrFail();
        
        \Log::info('Mounting ExamEdit', [
            'exam_status' => $this->exam->status,
            'exam_id' => $this->exam->id
        ]);
        
        // Initialize the properties
        $this->duration = $this->exam->duration;
        $this->password = $this->exam->password;
        $this->status = $this->exam->status;
        $this->questions_per_session = $this->exam->questions_per_session;
        $this->passing_percentage = $this->exam->passing_percentage;
    }

    public function render()
    {
        return view('livewire.exam-edit', [
            'course' => $this->exam->course
        ])->layout('layouts.portal');
    }

    public function updateExam()
    {
        try {
            $validatedData = $this->validate();
            \Log::info('Validation passed', ['status' => $this->status]);

            // Only update fields that have been changed
            $updates = array_filter($validatedData, function($value) {
                return !is_null($value);
            });

            if (!empty($updates)) {
                $this->exam->update($updates);
                session()->flash('message', 'Exam updated successfully.');
            } else {
                session()->flash('message', 'No changes were made.');
            }
            
            $this->dispatch('examUpdated', $this->exam->slug);
            
        } catch (\Exception $e) {
            \Log::error('Exam update failed', [
                'error' => $e->getMessage(),
                'status' => $this->status
            ]);
            session()->flash('error', 'Failed to update exam. ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('examcenter');
    }
} 