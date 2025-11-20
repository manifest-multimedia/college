<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfflineExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the user has permission to create exams
        return $this->user() && $this->user()->can('create exams');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after:today',
            'duration' => 'required|integer|min:15|max:300',
            'status' => 'required|string|in:draft,published,completed,canceled',
            'course_id' => 'required|exists:subjects,id',
            'type_id' => 'nullable|exists:exam_types,id',
            'proctor_id' => 'nullable|exists:users,id',
            'venue' => 'required|string|max:255',
            'clearance_threshold' => 'nullable|integer|min:0|max:100|default:60',
            'passing_percentage' => 'nullable|integer|min:0|max:100|default:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.after' => 'The exam date must be set for a future date.',
            'duration.min' => 'The exam duration must be at least 15 minutes.',
            'duration.max' => 'The exam duration cannot exceed 300 minutes (5 hours).',
            'clearance_threshold.min' => 'The clearance threshold must be between 0 and 100 percent.',
            'clearance_threshold.max' => 'The clearance threshold must be between 0 and 100 percent.',
        ];
    }
}
