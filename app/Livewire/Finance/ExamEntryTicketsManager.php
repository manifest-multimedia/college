<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use App\Models\ExamClearance;
use App\Models\Exam;
use App\Models\ExamEntryTicket;
use App\Services\ExamClearanceManager;
use Livewire\WithPagination;
use Carbon\Carbon;

class ExamEntryTicketsManager extends Component
{
    use WithPagination;

    public $clearanceId;
    public $examId;
    public $expiryDate;
    public $expiryTime;
    public $showGenerateModal = false;

    protected $rules = [
        'examId' => 'required|exists:exams,id',
        'expiryDate' => 'nullable|date',
        'expiryTime' => 'nullable'
    ];

    public function mount($clearanceId = null)
    {
        $this->clearanceId = $clearanceId;
        $this->expiryDate = now()->addDays(1)->format('Y-m-d');
        $this->expiryTime = '23:59';
    }
    public function openGenerateModal($clearanceId)
    {
        $this->clearanceId = $clearanceId;
        $this->showGenerateModal = true;
    }
    public function generateTicket()
    {
        $this->validate();

        $clearance = ExamClearance::findOrFail($this->clearanceId);
        $exam = Exam::findOrFail($this->examId);

        $expiresAt = null;
        if ($this->expiryDate && $this->expiryTime) {
            $expiresAt = Carbon::createFromFormat(
                'Y-m-d H:i',
                $this->expiryDate . ' ' . $this->expiryTime
            );
        }

        try {
            $clearanceManager = new ExamClearanceManager();
            $ticket = $clearanceManager->generateExamEntryTicket($clearance, $exam, $expiresAt);

            session()->flash('success', 'Exam entry ticket generated successfully.');
            $this->showGenerateModal = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate exam entry ticket: ' . $e->getMessage());
        }
    }
    public function deleteTicket($ticketId)
    {
        $ticket = ExamEntryTicket::findOrFail($ticketId);
        $ticket->delete();

        session()->flash('success', 'Exam entry ticket deleted successfully.');
    }
    public function deactivateTicket($ticketId)
    {
        $ticket = ExamEntryTicket::findOrFail($ticketId);
        $ticket->deactivate();

        session()->flash('success', 'Exam entry ticket deactivated successfully.');
    }

    public function getExamsProperty()
    {
        if (!$this->clearanceId) {
            return collect();
        }
        
        $clearance = ExamClearance::findOrFail($this->clearanceId);
        
        return Exam::where('semester_id', $clearance->semester_id)
            ->where('active', true)
            ->get();
    }
    
    public function getClearanceProperty()
    {
        if (!$this->clearanceId) {
            return null;
        }
        
        return ExamClearance::with(['student', 'academicYear', 'semester', 'examType'])
            ->findOrFail($this->clearanceId);
    }
    
    public function getTicketsProperty()
    {
        if (!$this->clearanceId) {
            return collect();
        }
        
        return ExamEntryTicket::where('exam_clearance_id', $this->clearanceId)
            ->with(['exam'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    

    public function render()
    {
        return view('livewire.finance.exam-entry-tickets-manager',[
            'clearance'=>$this->clearance,
            'tickets'=>$this->tickets,
            'exams'=>$this->exams,
        ]);
    }
}
