<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProctoringSession;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class ProctoringLivewire extends Component
{
    use WithFileUploads;

    public $user_id;
    public $proctor_id;
    public $exam_id;
    public $recordedVideo;

    public function mount($examId = null, $userId = null)
    {
        $this->dispatch('startProctoring');
        $this->user_id = $userId;
        $this->exam_id = $examId;
    }

    public function startProctoring()
    {
        $this->dispatch('startProctoring'); // Dispatch event for JavaScript to handle
    }

    public function stopProctoring()
    {
        $this->dispatch('stopProctoring'); // Dispatch event for JavaScript to handle
    }

    public function saveRecording()
    {
        $this->dispatch('saveRecording');
    }
    public function fileUpload($file)
    {
        if ($file) {
            // Save the uploaded file to the 'exam_feed' disk
            $path = $file->store('recordings', 'exams');

            // Save the recording details in the database
            ProctoringSession::create([
                'user_id' => $this->user_id ?? Auth::id(),
                'exam_id' => $this->exam_id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'started_at' => now(), // Adjust if needed
                'ended_at' => now(),
                'flagged' => false,
                'report' => "Recording saved at: $path",
            ]);

            $this->dispatch('recordingSaved', $path);
        } else {
            $this->dispatch('recordingFailed', 'No file received.');
        }
    }

    public function render()
    {
        return view('livewire.proctoring-livewire');
    }
}
