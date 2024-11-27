<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BulkUpload extends Component
{
    /**
     * Create a new component instance.
     */
    public $examId;
    public function __construct($examId = null)
    {
        // dd($exam_id);
        $this->examId = $examId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.bulk-upload', [
            'examId' => $this->examId
        ]);
    }
}
