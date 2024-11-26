<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;

use Livewire\Component;
use Livewire\WithFileUploads;

class QuestionUploadWidget extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx,csv,ods,tsv|max:10240')]
    public $bulk_file;
    public function render()
    {
        return view('livewire.question-upload-widget');
    }
}
