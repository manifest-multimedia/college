<?php

namespace App\Services\TutorEvaluations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EvaluationSchema
{
    public const TYPES = ['rating', 'single_choice', 'multiple_choice', 'text', 'textarea', 'yes_no'];

    public function validate(array $sections): array
    {
        Validator::make(['sections' => $sections], [
            'sections' => 'required|array|min:1|max:20',
            'sections.*.title' => 'required|string|max:200',
            'sections.*.questions' => 'required|array|min:1|max:50',
            'sections.*.questions.*.id' => 'required|alpha_dash|max:80',
            'sections.*.questions.*.label' => 'required|string|max:500',
            'sections.*.questions.*.type' => 'required|in:'.implode(',', self::TYPES),
            'sections.*.questions.*.required' => 'required|boolean',
            'sections.*.questions.*.options' => 'sometimes|array|max:30',
            'sections.*.questions.*.options.*' => 'string|max:200',
            'sections.*.questions.*.max_rating' => 'sometimes|integer|min:2|max:10',
        ])->validate();

        $ids = [];
        foreach ($sections as $section) {
            foreach ($section['questions'] as $question) {
                if (in_array($question['id'], $ids, true)) {
                    throw ValidationException::withMessages(['sections' => 'Question IDs must be unique across all sections.']);
                }
                $ids[] = $question['id'];
                if (in_array($question['type'], ['single_choice', 'multiple_choice'], true) && count($question['options'] ?? []) < 2) {
                    throw ValidationException::withMessages(['sections' => 'Choice questions require at least two options.']);
                }
            }
        }

        return $sections;
    }

    public function validateAnswers(array $sections, array $answers): array
    {
        $rules = [];
        $allowed = [];
        foreach ($sections as $section) {
            foreach ($section['questions'] as $q) {
                $key = $q['id'];
                $allowed[] = $key;
                $prefix = ($q['required'] ? 'required' : 'nullable');
                $rules[$key] = match ($q['type']) {
                    'rating' => [$prefix, 'integer', 'between:1,'.($q['max_rating'] ?? 5)],
                    'yes_no' => [$prefix, 'in:yes,no'],
                    'single_choice' => [$prefix, 'string', \Illuminate\Validation\Rule::in($q['options'])],
                    'multiple_choice' => [$prefix, 'array', 'max:30'],
                    'text', 'textarea' => [$prefix, 'string', 'max:'.($q['type'] === 'text' ? 500 : 3000)],
                };
                if ($q['type'] === 'multiple_choice') {
                    $rules[$key.'.*'] = [\Illuminate\Validation\Rule::in($q['options'])];
                }
            }
        }
        if (array_diff(array_keys($answers), $allowed)) {
            throw ValidationException::withMessages(['answers' => 'Unrecognized answer fields were submitted.']);
        }
        return Validator::make($answers, $rules)->validate();
    }
}
