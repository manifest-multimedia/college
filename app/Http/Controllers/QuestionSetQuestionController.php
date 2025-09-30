<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\QuestionSet;
use App\Models\Question;
use App\Models\Option;

class QuestionSetQuestionController extends Controller
{
    /**
     * Show the create question form
     */
    public function create($questionSetId)
    {
        $questionSet = QuestionSet::with('course')->find($questionSetId);
        
        if (!$questionSet) {
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
        
        if (!$questionSet) {
            return redirect()->route('question.sets')
                ->with('error', 'Question set not found.');
        }

        $question = Question::with('options')
            ->where('question_set_id', $questionSetId)
            ->find($questionId);

        if (!$question) {
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
        $request->validate([
            'question_text' => 'required|string|min:5',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'marks' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string',
            'exam_section' => 'nullable|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|min:1',
            'correct_option' => 'required|integer|min:0',
        ], [
            'question_text.required' => 'Question text is required',
            'question_text.min' => 'Question text must be at least 5 characters',
            'options.required' => 'At least 2 options are required',
            'options.min' => 'At least 2 options are required',
            'options.*.required' => 'All options must have text',
            'correct_option.required' => 'Please select the correct option',
        ]);

        $questionSet = QuestionSet::find($questionSetId);
        if (!$questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        // Validate correct option index
        $correctOptionIndex = $request->input('correct_option');
        $options = $request->input('options');
        
        if ($correctOptionIndex >= count($options)) {
            return response()->json(['error' => 'Invalid correct option selected.'], 422);
        }

        try {
            DB::beginTransaction();

            $question = Question::create([
                'question_set_id' => $questionSetId,
                'question_text' => $request->input('question_text'),
                'type' => $request->input('question_type'),
                'difficulty_level' => $request->input('difficulty_level'),
                'mark' => $request->input('marks'),
                'explanation' => $request->input('explanation'),
                'exam_section' => $request->input('exam_section'),
            ]);

            // Create options
            foreach ($options as $index => $optionText) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $index === $correctOptionIndex,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully!',
                'question_id' => $question->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create question: ' . $e->getMessage()], 500);
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
            'correct_option' => 'required|integer|min:0',
        ]);

        $questionSet = QuestionSet::find($questionSetId);
        if (!$questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        $question = Question::where('question_set_id', $questionSetId)->find($questionId);
        if (!$question) {
            return response()->json(['error' => 'Question not found.'], 404);
        }

        // Validate correct option index
        $correctOptionIndex = $request->input('correct_option');
        $options = $request->input('options');
        
        if ($correctOptionIndex >= count($options)) {
            return response()->json(['error' => 'Invalid correct option selected.'], 422);
        }

        try {
            DB::beginTransaction();

            $question->update([
                'question_text' => $request->input('question_text'),
                'type' => $request->input('question_type'),
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
                    'is_correct' => $index === $correctOptionIndex,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update question: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a question via AJAX
     */
    public function destroy($questionSetId, $questionId)
    {
        $questionSet = QuestionSet::find($questionSetId);
        if (!$questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        $question = Question::where('question_set_id', $questionSetId)->find($questionId);
        if (!$question) {
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
                'message' => 'Question deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question deletion error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete question: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get question data via AJAX (for editing)
     */
    public function show($questionSetId, $questionId)
    {
        $questionSet = QuestionSet::find($questionSetId);
        if (!$questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        $question = Question::with('options')
            ->where('question_set_id', $questionSetId)
            ->find($questionId);

        if (!$question) {
            return response()->json(['error' => 'Question not found.'], 404);
        }

        $correctOptionIndex = null;
        $options = [];

        foreach ($question->options as $index => $option) {
            $options[] = $option->option_text;
            if ($option->is_correct) {
                $correctOptionIndex = $index;
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
                'correct_option' => $correctOptionIndex
            ]
        ]);
    }
}