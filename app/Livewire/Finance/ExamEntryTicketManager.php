<?php

namespace App\Livewire\Finance;

use App\Models\ExamClearance;
use App\Models\ExamType;
use App\Models\ExamEntryTicket;
use App\Services\ExamClearanceManager;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExamEntryTicketManager extends Component
{
    use WithPagination;
    
    public $clearanceId;
    public $examTypeId;
    public $expiryDate;
    public $expiryTime;
    public $showGenerateModal = false;
    
    protected $rules = [
        'examTypeId' => 'required|exists:exam_types,id',
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
        $this->dispatch('show-generate-modal');
    }
    
    public function generateTicket()
    {
        $this->validate();
        
        $clearance = ExamClearance::findOrFail($this->clearanceId);
        $examType = ExamType::findOrFail($this->examTypeId);
        
        $expiresAt = null;
        if ($this->expiryDate && $this->expiryTime) {
            $expiresAt = Carbon::createFromFormat(
                'Y-m-d H:i', 
                $this->expiryDate . ' ' . $this->expiryTime
            );
        }
        
        try {
            $clearanceManager = new ExamClearanceManager();
            $ticket = $clearanceManager->generateExamEntryTicket($clearance, $examType, $expiresAt);
            
            Log::info('Exam ticket generated successfully', [
                'ticketId' => $ticket->id,
                'studentId' => $clearance->student_id,
                'examTypeId' => $examType->id
            ]);
            
            session()->flash('message', 'Exam entry ticket generated successfully.');
            $this->showGenerateModal = false;
            $this->dispatch('close-modal');
        } catch (\Exception $e) {
            Log::error('Failed to generate ticket', [
                'error' => $e->getMessage(),
                'studentId' => $clearance->student_id,
                'examTypeId' => $examType->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to generate ticket: ' . $e->getMessage());
        }
    }
    
    public function deactivateTicket($ticketId)
    {
        $ticket = ExamEntryTicket::findOrFail($ticketId);
        
        try {
            $ticket->update([
                'is_active' => false
            ]);
            
            Log::info('Ticket deactivated', [
                'ticketId' => $ticket->id
            ]);
            
            session()->flash('message', 'Ticket has been deactivated.');
        } catch (\Exception $e) {
            Log::error('Failed to deactivate ticket', [
                'error' => $e->getMessage(),
                'ticketId' => $ticket->id
            ]);
            
            session()->flash('error', 'Failed to deactivate ticket: ' . $e->getMessage());
        }
    }
    
    public function getExamTypesProperty()
    {
        if (!$this->clearanceId) {
            return collect();
        }
        
        $clearance = ExamClearance::findOrFail($this->clearanceId);
        
        // Get all active exam types, prioritizing the one from the clearance
        return ExamType::where('is_active', true)
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$clearance->exam_type_id])
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
            ->with(['examType'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    
    public function render()
    {
        return view('livewire.finance.exam-entry-ticket-manager', [
            'clearance' => $this->clearance,
            'tickets' => $this->tickets,
            'examTypes' => $this->examTypes
        ])
        ->layout('components.dashboard.default');
    }
}