<?php

namespace App\Livewire;

use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionAttachment;
use App\Models\QuestionSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class QuestionSetQuestionForm extends Component
{
    use WithFileUploads;

    public $questionSetId;

    public $questionId = null;

    public $questionSet;

    public $question;

    public $questionsCount = 0; // Pre-calculated count to avoid dynamic queries in view

    // Question properties
    public $questionText = '';

    public $questionType = 'multiple_choice';

    public $difficultyLevel = 'medium';

    public $marks = 1;

    public $explanation = '';

    public $examSection = '';

    // Options
    public $options = [];

    public $correctOptions = [];

    // File uploads
    public $questionImages = [];

    public $existingImages = [];

    public $imagesToDelete = [];

    public $tableData = null;

    public $existingTableData = null;

    // UI state
    public $isEditing = false;

    public $isSaving = false;

    public $addingMultiple = false;  // Flag for adding multiple questions

    public $questions = [];          // Array to store multiple questions

    public $hasResponses = false;    // Flag to check if question has been answered

    protected $rules = [
        'questionText' => 'required|string|min:5',
        'questionType' => 'required|in:multiple_choice,true_false,short_answer',
        'difficultyLevel' => 'required|in:easy,medium,hard',
        'marks' => 'required|integer|min:1|max:100',
        'explanation' => 'nullable|string',
        'examSection' => 'nullable|string|max:255',
        'options' => 'required|array|min:2',
        'options.*' => 'required|string|min:1',
        'correctOptions' => 'required|array|min:1',
        'correctOptions.*' => 'required|integer|min:0',
        'questionImages.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'tableData' => 'nullable|json',
    ];

    protected $messages = [
        'questionText.required' => 'Question text is required',
        'questionText.min' => 'Question text must be at least 5 characters',
        'options.required' => 'At least 2 options are required',
        'options.min' => 'At least 2 options are required',
        'options.*.required' => 'Option text is required',
        'options.*.min' => 'Option text cannot be empty',
        'correctOptions.required' => 'Please select at least one correct answer',
        'correctOptions.min' => 'Please select at least one correct answer',
    ];

    public function mount($questionSetId, $questionId = null)
    {
        $this->questionSetId = $questionSetId;

        // Eager load relationships to prevent loading issues in the view
        $this->questionSet = QuestionSet::with(['course'])->find($questionSetId);

        // If question set doesn't exist, we'll handle it in the view
        if ($this->questionSet) {
            // Ensure name is not null to prevent view errors
            if (! $this->questionSet->name) {
                $this->questionSet->name = 'Untitled Question Set';
            }

            // Pre-calculate questions count to avoid dynamic queries in view
            $this->questionsCount = $this->questionSet->questions()->count();

            if ($questionId) {
                $this->questionId = $questionId;
                $this->isEditing = true;
                $this->loadQuestion();
            } else {
                $this->initializeNewQuestion();
            }
        }
    }

    private function loadQuestion()
    {
        $this->question = Question::with(['options', 'attachments'])
            ->where('question_set_id', $this->questionSetId)
            ->findOrFail($this->questionId);

        // Check if this question has any responses
        $this->hasResponses = $this->question->responses()->exists();

        $this->questionText = $this->question->question_text;
        $this->questionType = $this->question->type ?? 'multiple_choice';
        $this->difficultyLevel = $this->question->difficulty_level ?? 'medium';
        $this->marks = $this->question->mark ?? 1;
        $this->explanation = $this->question->explanation ?? '';
        $this->examSection = $this->question->exam_section ?? '';

        // Load options
        $this->options = [];
        $this->correctOptions = [];

        foreach ($this->question->options as $index => $option) {
            $this->options[] = $option->option_text;
            if ($option->is_correct) {
                $this->correctOptions[] = $index;
            }
        }

        // Ensure we have at least 2 options for the form
        while (count($this->options) < 4) {
            $this->options[] = '';
        }

        // Load existing attachments
        $this->loadExistingAttachments();
    }

    private function loadExistingAttachments()
    {
        if (! $this->question) {
            return;
        }

        // Load existing images
        $this->existingImages = $this->question->images()->get()->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'url' => $attachment->url,
                'filename' => $attachment->original_filename,
                'size' => $attachment->formatted_size,
            ];
        })->toArray();

        // Load existing table data
        $tableAttachment = $this->question->tables()->first();
        if ($tableAttachment) {
            $this->existingTableData = $tableAttachment->metadata;
        }
    }

    private function initializeNewQuestion()
    {
        $this->options = ['', '', '', '']; // 4 empty options by default
        $this->correctOptions = [];
    }

    public function addOption()
    {
        if (count($this->options) < 10) { // Maximum 10 options
            $this->options[] = '';
        }
    }

    public function removeOption($index)
    {
        if (count($this->options) > 2) { // Minimum 2 options
            unset($this->options[$index]);
            $this->options = array_values($this->options); // Re-index array

            // Remove from correct options if it was selected
            $this->correctOptions = array_values(array_filter($this->correctOptions, function ($i) use ($index) {
                return $i !== $index;
            }));

            // Adjust indices of correct options after the removed index
            $this->correctOptions = array_map(function ($i) use ($index) {
                return $i > $index ? $i - 1 : $i;
            }, $this->correctOptions);
        }
    }

    public function removeNewImage($index)
    {
        if (isset($this->questionImages[$index])) {
            array_splice($this->questionImages, $index, 1);
        }
    }

    public function removeExistingImage($imageId)
    {
        // Mark for deletion
        $this->imagesToDelete[] = $imageId;

        // Remove from existing images array
        $this->existingImages = array_filter($this->existingImages, function ($img) use ($imageId) {
            return $img['id'] !== $imageId;
        });
        $this->existingImages = array_values($this->existingImages);
    }

    public function clearTableData()
    {
        $this->tableData = null;
        $this->existingTableData = null;
    }

    public function updated($propertyName)
    {
        // Validate only the changed property
        $this->validateOnly($propertyName);

        // If options were updated, ensure correct option is valid
        if (str_starts_with($propertyName, 'options.')) {
            $this->validateCorrectOptions();
        }
    }

    public function toggleCorrectOption($index)
    {
        if (in_array($index, $this->correctOptions)) {
            $this->correctOptions = array_values(array_filter($this->correctOptions, fn ($i) => $i !== $index));
        } else {
            $this->correctOptions[] = $index;
            sort($this->correctOptions);
        }
    }

    private function validateCorrectOptions()
    {
        $nonEmptyOptions = array_filter($this->options, fn ($option) => ! empty(trim($option)));
        $optionCount = count($nonEmptyOptions);

        // Filter out any invalid indices from correctOptions
        $this->correctOptions = array_values(array_filter($this->correctOptions, function ($index) use ($optionCount) {
            return $index < $optionCount;
        }));
    }

    private function mapQuestionTypeToDatabase($type)
    {
        return match($type) {
            'multiple_choice' => 'MCQ',
            'true_false' => 'TF',
            'short_answer' => 'ESSAY',
            default => 'MCQ',
        };
    }

    public function save()
    {
        // Prevent editing questions that have been answered
        if ($this->isEditing && $this->hasResponses) {
            session()->flash('error', 'This question cannot be edited because it has already been answered by students. Create a new question instead.');

            return;
        }

        // Filter out empty options before validation
        $this->options = array_filter($this->options, fn ($option) => ! empty(trim($option)));
        $this->options = array_values($this->options); // Re-index

        // Validate correct options after filtering
        $this->validateCorrectOptions();

        // Add validation rule to ensure at least one correct option is selected
        $this->rules['correctOptions'] = ['required', 'array', 'min:1'];
        $this->rules['correctOptions.*'] = ['integer', 'min:0', 'lt:'.count($this->options)];

        $this->messages['correctOptions.required'] = 'Please select at least one correct answer';
        $this->messages['correctOptions.min'] = 'Please select at least one correct answer';
        $this->messages['correctOptions.*.lt'] = 'Invalid correct answer selected';

        $this->validate();

        try {
            $this->isSaving = true;

            DB::beginTransaction();

            if ($this->isEditing) {
                $this->updateQuestion();
            } else {
                $this->createQuestion();
            }

            DB::commit();

            session()->flash('success', $this->isEditing ? 'Question updated successfully!' : 'Question created successfully!');

            // Redirect to questions list
            return redirect()->route('question.sets.questions', $this->questionSetId);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question save error: '.$e->getMessage());
            session()->flash('error', 'Error saving question: '.$e->getMessage());
        } finally {
            $this->isSaving = false;
        }
    }

    private function createQuestion()
    {
        $question = Question::create([
            'question_set_id' => $this->questionSetId,
            'question_text' => $this->questionText,
            'type' => $this->mapQuestionTypeToDatabase($this->questionType),
            'difficulty_level' => $this->difficultyLevel,
            'mark' => $this->marks,
            'explanation' => $this->explanation,
            'exam_section' => $this->examSection,
        ]);

        $this->createOptions($question);
        $this->saveAttachments($question);
    }

    private function updateQuestion()
    {
        $this->question->update([
            'question_text' => $this->questionText,
            'type' => $this->mapQuestionTypeToDatabase($this->questionType),
            'difficulty_level' => $this->difficultyLevel,
            'mark' => $this->marks,
            'explanation' => $this->explanation,
            'exam_section' => $this->examSection,
        ]);

        // Delete existing options and create new ones
        $this->question->options()->delete();
        $this->createOptions($this->question);

        // Handle attachment updates
        $this->updateAttachments($this->question);
    }

    private function createOptions($question)
    {
        foreach ($this->options as $index => $optionText) {
            if (! empty(trim($optionText))) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => trim($optionText),
                    'is_correct' => in_array($index, $this->correctOptions),
                ]);
            }
        }
    }

    private function saveAttachments($question)
    {
        // Save image attachments
        if (! empty($this->questionImages)) {
            foreach ($this->questionImages as $index => $image) {
                try {
                    $filename = $this->generateSecureFilename($image, $question->id);
                    $path = $image->storeAs('questions/images', $filename, 'exams');

                    QuestionAttachment::create([
                        'question_id' => $question->id,
                        'attachment_type' => 'image',
                        'file_path' => $path,
                        'original_filename' => $image->getClientOriginalName(),
                        'mime_type' => $image->getMimeType(),
                        'file_size' => $image->getSize(),
                        'display_order' => $index,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error saving image attachment', [
                        'question_id' => $question->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Save table data as attachment
        if (! empty($this->tableData)) {
            try {
                $tableMetadata = json_decode($this->tableData, true);

                QuestionAttachment::create([
                    'question_id' => $question->id,
                    'attachment_type' => 'table',
                    'metadata' => $tableMetadata,
                    'display_order' => 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Error saving table attachment', [
                    'question_id' => $question->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function updateAttachments($question)
    {
        // Delete marked images
        if (! empty($this->imagesToDelete)) {
            foreach ($this->imagesToDelete as $imageId) {
                $attachment = QuestionAttachment::find($imageId);
                if ($attachment && $attachment->file_path) {
                    Storage::disk('exams')->delete($attachment->file_path);
                    $attachment->delete();
                }
            }
        }

        // Save new images
        if (! empty($this->questionImages)) {
            $existingImageCount = count($this->existingImages);
            foreach ($this->questionImages as $index => $image) {
                try {
                    $filename = $this->generateSecureFilename($image, $question->id);
                    $path = $image->storeAs('questions/images', $filename, 'exams');

                    QuestionAttachment::create([
                        'question_id' => $question->id,
                        'attachment_type' => 'image',
                        'file_path' => $path,
                        'original_filename' => $image->getClientOriginalName(),
                        'mime_type' => $image->getMimeType(),
                        'file_size' => $image->getSize(),
                        'display_order' => $existingImageCount + $index,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error updating image attachment', [
                        'question_id' => $question->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Update table data
        if ($this->tableData !== null || $this->existingTableData === null) {
            // Delete existing table attachment
            $question->tables()->delete();

            // Create new table attachment if data exists
            if (! empty($this->tableData)) {
                try {
                    $tableMetadata = json_decode($this->tableData, true);

                    QuestionAttachment::create([
                        'question_id' => $question->id,
                        'attachment_type' => 'table',
                        'metadata' => $tableMetadata,
                        'display_order' => 0,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error updating table attachment', [
                        'question_id' => $question->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    private function generateSecureFilename($file, $questionId)
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName);

        return "question_{$questionId}_{$safeName}_".time().'.'.$extension;
    }

    public function addAnotherQuestion()
    {
        // Save current question state
        $this->questions[] = [
            'questionText' => $this->questionText,
            'questionType' => $this->questionType,
            'difficultyLevel' => $this->difficultyLevel,
            'marks' => $this->marks,
            'explanation' => $this->explanation,
            'examSection' => $this->examSection,
            'options' => $this->options,
            'correctOptions' => $this->correctOptions,
        ];

        // Reset form for new question
        $this->resetQuestionForm();
    }

    private function resetQuestionForm()
    {
        $this->questionText = '';
        $this->questionType = 'multiple_choice';
        $this->difficultyLevel = 'medium';
        $this->marks = 1;
        $this->explanation = '';
        $this->examSection = '';
        $this->options = ['', '', '', ''];
        $this->correctOptions = [];
    }

    public function removeQuestion($index)
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function createAllQuestions()
    {
        try {
            DB::beginTransaction();

            // Save current question first
            if (! empty(trim($this->questionText))) {
                $this->addAnotherQuestion();
            }

            // Create all saved questions
            foreach ($this->questions as $questionData) {
                $question = Question::create([
                    'question_set_id' => $this->questionSetId,
                    'question_text' => $questionData['questionText'],
                    'type' => $questionData['questionType'],
                    'difficulty_level' => $questionData['difficultyLevel'],
                    'mark' => $questionData['marks'],
                    'explanation' => $questionData['explanation'],
                    'exam_section' => $questionData['examSection'],
                ]);

                // Create options for the question
                foreach ($questionData['options'] as $index => $optionText) {
                    if (! empty(trim($optionText))) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => trim($optionText),
                            'is_correct' => in_array($index, $questionData['correctOptions']),
                        ]);
                    }
                }
            }

            DB::commit();

            session()->flash('success', count($this->questions).' questions created successfully!');

            return redirect()->route('question.sets.questions', $this->questionSetId);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question save error: '.$e->getMessage());
            session()->flash('error', 'Error saving questions: '.$e->getMessage());
        }
    }

    public function resetForm()
    {
        if ($this->isEditing) {
            $this->loadQuestion();
        } else {
            $this->initializeNewQuestion();
            $this->reset([
                'questionText', 'questionType', 'difficultyLevel',
                'marks', 'explanation', 'examSection',
            ]);
            $this->marks = 1;
            $this->questionType = 'multiple_choice';
            $this->difficultyLevel = 'medium';
        }
    }

    public function render()
    {
        return view('livewire.question-set-question-form');
    }
}
