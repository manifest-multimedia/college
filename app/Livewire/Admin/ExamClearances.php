<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\ExamClearance;
use App\Models\Student;
use App\Models\OfflineExam;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ExamClearances extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    // Search and filter properties
    public $search = '';
    public $examFilter = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Properties for actions
    public $selectedClearance = null;
    public $showingGenerateTicketModal = false;
    public $ticketMessage = '';
    
    // For the filter dropdowns
    public $exams = [];
    
    public function mount()
    {
        // Load exams for the filter dropdown
        $this->loadExams();
    }
    
    public function loadExams()
    {
        $this->exams = OfflineExam::where('status', 'published')
                            ->orderBy('date', 'desc')
                            ->get();
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedExamFilter()
    {
        $this->resetPage();
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
    
    public function viewClearanceDetails($clearanceId)
    {
        $this->selectedClearance = ExamClearance::with(['student', 'clearable', 'clearable.course'])
                                    ->findOrFail($clearanceId);
        
        $this->dispatch('show-clearance-details-modal');
    }
    
    public function showGenerateTicketModal($clearanceId)
    {
        $this->selectedClearance = ExamClearance::with(['student', 'clearable'])
                                    ->findOrFail($clearanceId);
        
        // Only allow ticket generation for cleared students
        if ($this->selectedClearance->status !== 'cleared') {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot generate ticket. Student not cleared for this exam.',
                'timer' => 3000
            ]);
            return;
        }
        
        // Check if ticket already exists
        if ($this->selectedClearance->examEntryTicket) {
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Ticket has already been generated for this clearance.',
                'timer' => 3000
            ]);
            return;
        }
        
        $this->showingGenerateTicketModal = true;
        $this->dispatch('show-generate-ticket-modal');
    }
    
    public function generateTicket()
    {
        // Validate
        $this->validate([
            'ticketMessage' => 'nullable|string|max:255',
        ]);
        
        try {
            // Check again if ticket exists (in case of race condition)
            if ($this->selectedClearance->examEntryTicket) {
                $this->dispatch('notify', [
                    'type' => 'info',
                    'message' => 'Ticket has already been generated for this clearance.',
                    'timer' => 3000
                ]);
                $this->showingGenerateTicketModal = false;
                $this->ticketMessage = '';
                return;
            }
            
            // Generate the ticket
            $ticket = $this->selectedClearance->generateExamEntryTicket($this->ticketMessage);
            
            // Notify and close modal
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Exam entry ticket generated successfully.',
                'timer' => 3000
            ]);
            
            // Reset and close
            $this->showingGenerateTicketModal = false;
            $this->ticketMessage = '';
            
            // Redirect to the ticket printing page
            return redirect()->route('finance.exam.ticket.print', ['ticketId' => $ticket->id]);
            
        } catch (\Exception $e) {
            Log::error('Failed to generate exam ticket: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to generate ticket. Please try again.',
                'timer' => 3000
            ]);
        }
    }
    
    public function cancelTicketGeneration()
    {
        $this->showingGenerateTicketModal = false;
        $this->ticketMessage = '';
    }
    
    public function render()
    {
        $clearancesQuery = ExamClearance::query()
            ->with(['student', 'clearable'])
            ->when($this->search, function ($query) {
                $query->whereHas('student', function (Builder $query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('student_id', 'like', '%' . $this->search . '%');
                })->orWhereHas('clearable', function (Builder $query) {
                    $query->where('title', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->examFilter, function ($query) {
                $query->where('clearable_id', $this->examFilter)
                      ->where('clearable_type', 'App\\Models\\OfflineExam');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest();
            
        $clearances = $clearancesQuery->paginate($this->perPage);
        
        return view('livewire.admin.exam-clearances', [
            'clearances' => $clearances,
            'exams' => $this->exams
        ]);
    }
}
