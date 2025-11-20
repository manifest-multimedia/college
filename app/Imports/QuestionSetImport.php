<?php

namespace App\Imports;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class QuestionSetImport implements ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    protected $questionSetId;

    protected $importStats = [
        'total' => 0,
        'imported' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    public function __construct($questionSetId)
    {
        $this->questionSetId = $questionSetId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $this->importStats['total']++;

                // Skip empty rows
                if (empty($row['question']) || trim($row['question']) === '') {
                    continue;
                }

                // Create question
                $question = Question::create([
                    'question_set_id' => $this->questionSetId,
                    'question_text' => $row['question'],
                    'exam_section' => $row['exam_section'] ?? '',
                    'mark' => $row['marks'] ?? 1,
                    'explanation' => $row['explanation'] ?? '',
                    'type' => 'multiple_choice',
                    'difficulty_level' => $row['difficulty_level'] ?? 'medium',
                ]);

                // Create options
                $options = [
                    [
                        'option_text' => $this->replaceBooleanValue($row['option_one'] ?? ''),
                        'is_correct' => $this->isCorrectOption($row['correct_option'] ?? '', 'option_one', $row['option_one'] ?? ''),
                    ],
                    [
                        'option_text' => $this->replaceBooleanValue($row['option_two'] ?? ''),
                        'is_correct' => $this->isCorrectOption($row['correct_option'] ?? '', 'option_two', $row['option_two'] ?? ''),
                    ],
                    [
                        'option_text' => $this->replaceBooleanValue($row['option_three'] ?? ''),
                        'is_correct' => $this->isCorrectOption($row['correct_option'] ?? '', 'option_three', $row['option_three'] ?? ''),
                    ],
                    [
                        'option_text' => $this->replaceBooleanValue($row['option_four'] ?? ''),
                        'is_correct' => $this->isCorrectOption($row['correct_option'] ?? '', 'option_four', $row['option_four'] ?? ''),
                    ],
                ];

                // Only create options that have text
                foreach ($options as $option) {
                    if (! empty(trim($option['option_text']))) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => $option['option_text'],
                            'is_correct' => $option['is_correct'],
                        ]);
                    }
                }

                $this->importStats['imported']++;

            } catch (\Exception $e) {
                $this->importStats['failed']++;
                $this->importStats['errors'][] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }
    }

    private function replaceBooleanValue($option)
    {
        // Check if option is boolean
        if (is_bool($option)) {
            return $option ? 'True' : 'False';
        }

        return $option;
    }

    private function isCorrectOption($correctOption, $currentOptionKey, $currentOptionValue)
    {
        $correctOption = strtolower(trim($correctOption));
        $currentOptionKey = strtolower(trim($currentOptionKey));

        // Check by option key
        if ($correctOption === $currentOptionKey ||
            $correctOption === str_replace('_', ' ', $currentOptionKey)) {
            return true;
        }

        // Check by option value (exact match)
        if (strtolower(trim($currentOptionValue)) === $correctOption) {
            return true;
        }

        // Check by letter (A, B, C, D)
        $letterMap = [
            'a' => 'option_one',
            'b' => 'option_two',
            'c' => 'option_three',
            'd' => 'option_four',
        ];

        if (isset($letterMap[$correctOption]) && $letterMap[$correctOption] === $currentOptionKey) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string',
            'option_one' => 'required|string',
            'option_two' => 'required|string',
            'correct_option' => 'required|string',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getImportStats()
    {
        return $this->importStats;
    }
}
