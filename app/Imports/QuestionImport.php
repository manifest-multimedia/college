<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Option;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToModel, WithHeadingRow
{
    protected $exam_id;

    public function __construct($exam_id)
    {
        $this->exam_id = $exam_id;
    }

    public function model(array $row)
    {

        // dd($row);
        if (!isset($row['question'], $row['correct_option'])) {
            return null; // Skip rows with insufficient data
        }
        $question = Question::create([
            'exam_id' => $this->exam_id,
            'question_text' => $row['question'],
            'exam_section' => $row['exam_section'] ?? '',
            'mark' => $row['marks'] ?? 1,
            'explanation' => $row['explanation'] ?? '',
        ]);

        $options = [
            [
                'option_text' => $this->replaceBooleanValue($row['option_one']),
                'is_correct' => ($row['correct_option'] === 'option_one' || $row['correct_option'] === 'option one' ||

                    $this->parseCorrectOption($row['option_one'], $row(['correct_option']), 'option_one') === 'option_one'

                ),
            ],
            [
                'option_text' => $this->replaceBooleanValue($row['option_two']),
                'is_correct' => ($row['correct_option'] === 'option_two' || $row['correct_option'] === 'option two' ||
                    $this->parseCorrectOption($row['option_two'], $row(['correct_option']), 'option_two') === 'option_two'
                ),
            ],
            [
                'option_text' => $this->replaceBooleanValue($row['option_three']),
                'is_correct' => ($row['correct_option'] === 'option_three' || $row['correct_option'] === 'option three' ||
                    $this->parseCorrectOption($row['option_three'], $row(['correct_option']), 'option_three') === 'option_three'

                ),
            ],
            [
                'option_text' => $this->replaceBooleanValue($row['option_four']),
                'is_correct' => ($row['correct_option'] === 'option_four' || $row['correct_option'] === 'option four' ||
                    $this->parseCorrectOption($row['option_four'], $row(['correct_option']), 'option_four') === 'option_four'
                ),
            ],
        ];
        foreach ($options as $option) {

            Option::create([
                'question_id' => $question->id,
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct'],
            ]);
        }

        // foreach (['option_one', 'option_two', 'option_three', 'option_four'] as $key => $option_key) {
        //     if (!empty($row[$option_key])) {
        //         Option::create([
        //             'question_id' => $question->id,
        //             'option_text' => $row[$option_key],
        //             'is_correct' => ($row['correct_option'] === $option_key),
        //         ]);
        //     }
        // }

        return $question;
    }
    private function replaceBooleanValue($option)
    {
        //  Check if option is boolean
        if (is_bool($option)) {
            // Check if bool is true or false and replace with text
            return $option ? 'True' : 'False';
        } else {
            return $option;
        }
    }

    private function parseCorrectOption($option, $correctOption, $value)
    {
        // Clean sanitize row and correct option value;
        try {
            //code...

            $option = strtolower(trim($option));
            $correctOption = strtolower(trim('$correctOption'));
            if ($option === $correctOption) {
                return $value;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
