<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionSetQuestionController extends Controller
{
    /**
     * Map form question types to database enum values
     */
    private function mapQuestionType($formType)
    {
        $mapping = [
            'multiple_choice' => 'MCQ',
            'true_false' => 'TF',
            'essay' => 'ESSAY',
            'multiple_answer' => 'MA',
        ];

        return $mapping[$formType] ?? 'MCQ'; // Default to MCQ if not found
    }

    /**
     * Show the create question form
     */
    public function create($questionSetId)
    {
        $questionSet = QuestionSet::with('course')->find($questionSetId);

        if (! $questionSet) {
            return redirect()->route('question.sets')
                ->with('error', 'Question set not found.');
        }

        // Pre-calculate questions count
        $questionsCount = $questionSet->questions()->count();

        return view('question-sets.questions.create', compact('questionSet', 'questionsCount', 'questionSetId'));
    }

    /**
     * Show the edit question form
     */
    public function edit($questionSetId, $questionId)
    {
        $questionSet = QuestionSet::with('course')->find($questionSetId);

        if (! $questionSet) {
            return redirect()->route('question.sets')
                ->with('error', 'Question set not found.');
        }

        $question = Question::with('options')
            ->where('question_set_id', $questionSetId)
            ->find($questionId);

        if (! $question) {
            return redirect()->route('question.sets.questions', $questionSetId)
                ->with('error', 'Question not found.');
        }

        // Pre-calculate questions count
        $questionsCount = $questionSet->questions()->count();

        return view('question-sets.questions.edit', compact('questionSet', 'question', 'questionsCount', 'questionSetId', 'questionId'));
    }

    /**
     * Store a new question via AJAX
     */
    public function store(Request $request, $questionSetId)
    {
        // Log incoming request
        Log::info('Question creation request received', [
            'question_set_id' => $questionSetId,
            'has_questions_json' => $request->has('questions'),
            'has_question_text' => $request->filled('question_text'),
            'request_keys' => array_keys($request->all()),
            'raw_questions' => $request->input('questions'),
        ]);

        // Get all questions data
        $questionsData = json_decode($request->input('questions'), true) ?: [];

        Log::info('Parsed questions data', [
            'questions_count' => count($questionsData),
            'questions_data' => $questionsData,
        ]);

        // Add current form data if not empty
        if ($request->filled('question_text')) {
            Log::info('Adding current form data to questions');

            $currentQuestion = [
                'question_text' => $request->input('question_text'),
                'question_type' => $request->input('question_type'),
                'difficulty_level' => 'medium', // Default
                'marks' => $request->input('marks'),
                'explanation' => $request->input('explanation'),
                'exam_section' => $request->input('exam_section'),
                'options' => [],
                'correct_options' => [],
            ];

            // Get options and correct answers from form
            $options = array_filter($request->input('options', []), fn ($opt) => ! empty(trim($opt)));
            $correctOptions = $request->input('correct_options', []);

            $currentQuestion['options'] = array_values($options);
            $currentQuestion['correct_options'] = array_map('intval', $correctOptions);

            Log::info('Current question prepared', [
                'question' => $currentQuestion,
            ]);

            $questionsData[] = $currentQuestion;
        }

        // Validate we have at least one question
        if (empty($questionsData)) {
            Log::warning('No questions provided in request');

            return response()->json(['error' => 'No questions provided.'], 422);
        }

        Log::info('Total questions to create', [
            'count' => count($questionsData),
        ]);

        // Custom validation rules
        foreach ($questionsData as $index => $question) {
            if (empty($question['question_text'])) {
                return response()->json(['error' => 'Question text is required for question '.($index + 1)], 422);
            }

            if (strlen($question['question_text']) < 5) {
                return response()->json(['error' => 'Question text must be at least 5 characters for question '.($index + 1)], 422);
            }

            if (count($question['options']) < 2) {
                return response()->json(['error' => 'At least 2 options are required for question '.($index + 1)], 422);
            }

            if (empty($question['correct_options'])) {
                return response()->json(['error' => 'Please select at least one correct answer for question '.($index + 1)], 422);
            }

            foreach ($question['correct_options'] as $optionIndex) {
                if (! isset($question['options'][$optionIndex])) {
                    return response()->json(['error' => 'Invalid correct option selected for question '.($index + 1)], 422);
                }
            }
        }

        $questionSet = QuestionSet::find($questionSetId);
        if (! $questionSet) {
            Log::warning('Question set not found', ['question_set_id' => $questionSetId]);

            return response()->json(['error' => 'Question set not found.'], 404);
        }

        Log::info('Question set found', [
            'question_set_id' => $questionSet->id,
            'question_set_name' => $questionSet->name,
        ]);

        try {
            DB::beginTransaction();

            Log::info('Database transaction started');

            $createdQuestions = [];

            foreach ($questionsData as $index => $questionData) {
                Log::info('Creating question', [
                    'index' => $index,
                    'question_text_length' => strlen($questionData['question_text']),
                    'options_count' => count($questionData['options']),
                    'correct_options_count' => count($questionData['correct_options']),
                ]);

                $question = Question::create([
                    'question_set_id' => $questionSetId,
                    'question_text' => $questionData['question_text'],
                    'type' => $this->mapQuestionType($questionData['question_type']),
                    'difficulty_level' => $questionData['difficulty_level'] ?? 'medium',
                    'mark' => $questionData['marks'],
                    'explanation' => $questionData['explanation'] ?? null,
                    'exam_section' => $questionData['exam_section'] ?? null,
                ]);

                Log::info('Question created', [
                    'question_id' => $question->id,
                    'index' => $index,
                ]);

                // Create options
                foreach ($questionData['options'] as $optIndex => $optionText) {
                    $isCorrect = in_array($optIndex, $questionData['correct_options']);

                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => $isCorrect,
                    ]);

                    Log::debug('Option created', [
                        'question_id' => $question->id,
                        'option_index' => $optIndex,
                        'is_correct' => $isCorrect,
                    ]);
                }

                $createdQuestions[] = $question->id;
            }

            DB::commit();

            Log::info('Questions created successfully', [
                'count' => count($createdQuestions),
                'question_ids' => $createdQuestions,
            ]);

            return response()->json([
                'success' => true,
                'message' => count($createdQuestions).' question(s) created successfully!',
                'question_ids' => $createdQuestions,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'question_set_id' => $questionSetId,
            ]);

            return response()->json(['error' => 'Failed to create questions: '.$e->getMessage()], 500);
        }
    }

    /**
     * Update a question via AJAX
     */
    public function update(Request $request, $questionSetId, $questionId)
    {
        $request->validate([
            'question_text' => 'required|string|min:5',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'marks' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string',
            'exam_section' => 'nullable|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|min:1',
            'correct_options' => 'required|array|min:1',
            'correct_options.*' => 'required|integer|min:0',
        ]);

        $questionSet = QuestionSet::find($questionSetId);
        if (! $questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        $question = Question::where('question_set_id', $questionSetId)->find($questionId);
        if (! $question) {
            return response()->json(['error' => 'Question not found.'], 404);
        }

        // Prevent editing questions that have been answered
        if ($question->responses()->exists()) {
            return response()->json([
                'error' => 'This question cannot be edited because it has already been answered by students. Create a new question instead.',
            ], 422);
        }

        // Validate correct options
        $correctOptions = $request->input('correct_options');
        $options = $request->input('options');

        foreach ($correctOptions as $optionIndex) {
            if ($optionIndex >= count($options)) {
                return response()->json(['error' => 'Invalid correct option selected.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            $question->update([
                'question_text' => $request->input('question_text'),
                'type' => $this->mapQuestionType($request->input('question_type')),
                'difficulty_level' => $request->input('difficulty_level'),
                'mark' => $request->input('marks'),
                'explanation' => $request->input('explanation'),
                'exam_section' => $request->input('exam_section'),
            ]);

            // Delete existing options and create new ones
            $question->options()->delete();

            foreach ($options as $index => $optionText) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => in_array($index, $correctOptions),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question update error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to update question: '.$e->getMessage()], 500);
        }
    }

    /**
     * Delete a question via AJAX
     */
    public function destroy($questionSetId, $questionId)
    {
        $questionSet = QuestionSet::find($questionSetId);
        if (! $questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        $question = Question::where('question_set_id', $questionSetId)->find($questionId);
        if (! $question) {
            return response()->json(['error' => 'Question not found.'], 404);
        }

        try {
            DB::beginTransaction();

            // Delete options first (due to foreign key constraint)
            $question->options()->delete();

            // Delete the question
            $question->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question deletion error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to delete question: '.$e->getMessage()], 500);
        }
    }

    /**
     * Get question data via AJAX (for editing)
     */
    public function show($questionSetId, $questionId)
    {
        $questionSet = QuestionSet::find($questionSetId);
        if (! $questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        $question = Question::with('options')
            ->where('question_set_id', $questionSetId)
            ->find($questionId);

        if (! $question) {
            return response()->json(['error' => 'Question not found.'], 404);
        }

        $options = [];
        $correctOptions = [];

        foreach ($question->options as $index => $option) {
            $options[] = $option->option_text;
            if ($option->is_correct) {
                $correctOptions[] = $index;
            }
        }

        return response()->json([
            'success' => true,
            'question' => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'difficulty_level' => $question->difficulty_level,
                'marks' => $question->mark,
                'explanation' => $question->explanation,
                'exam_section' => $question->exam_section,
                'options' => $options,
                'correct_options' => $correctOptions,
            ],
        ]);
    }
}
