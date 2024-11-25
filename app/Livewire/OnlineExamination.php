<?php

namespace App\Livewire;

use Livewire\Component;

class OnlineExamination extends Component
{
    public $examPassword;
    public function render()
    {

        return view('livewire.online-examination');
    }
}
