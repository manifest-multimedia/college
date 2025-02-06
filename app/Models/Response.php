<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = ['exam_session_id', 'question_id', 'selected_option', 'is_correct', 'student_id', 'option_id'];

    /**
     * The exam session this response is part of.
     */
    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    /**
     * The question that this response is answering.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The option that was selected by the student.
     */
    public function option()
    {
        return $this->belongsTo(Option::class);
    }

    public function markCorrect()

    {
        $return_option=Option::where('id',$this->selected_option)->first();
        dd($this->question->id,$this->selected_option,$return_option,$this->question->options);

        // dd($this->question->options(),$this->selected_option);

        // // Get the correct option for this question
        // $correctOption = $this->question->options()->where('is_correct', true)->first();
        // if($correctOption){

        //     return $this->selected_option === $correctOption->id;
        // }else{
        //     return false;
        // }
        // // Check if the selected option is the correct option
    }
    /**
     * The student who submitted this response.
     */
    public function student()
    {
        return $this->belongsTo(User::class);
    }
}
