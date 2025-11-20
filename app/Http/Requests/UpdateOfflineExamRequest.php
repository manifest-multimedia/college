<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfflineExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the user has permission to update exams
        return $this->user() && $this->user()->can('update exams');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date' => 'sometimes|date',
            'duration' => 'sometimes|integer|min:15|max:300',
            'status' => 'sometimes|string|in:draft,published,completed,canceled',
            'course_id' => 'sometimes|exists:subjects,id',
            'type_id' => 'nullable|exists:exam_types,id',
            'proctor_id' => 'nullable|exists:users,id',
            'venue' => 'sometimes|string|max:255',
            'clearance_threshold' => 'nullable|integer|min:0|max:100',
            'passing_percentage' => 'nullable|integer|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'duration.min' => 'The exam duration must be at least 15 minutes.',
            'duration.max' => 'The exam duration cannot exceed 300 minutes (5 hours).',
            'clearance_threshold.min' => 'The clearance threshold must be between 0 and 100 percent.',
            'clearance_threshold.max' => 'The clearance threshold must be between 0 and 100 percent.',
        ];
    }
}
