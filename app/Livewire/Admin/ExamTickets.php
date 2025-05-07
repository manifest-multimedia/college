<?php

namespace App\Livewire\Admin;

use App\Models\ExamEntryTicket;
use App\Models\OfflineExam;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ExamTickets extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    // Search and filter properties
    public $search = '';
    public $examFilter = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Properties for actions
    public $selectedTicket = null;
    public $showingTicketDetailsModal = false;
    
    // For the filter dropdown
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
    
    public function viewTicketDetails($ticketId)
    {
        $this->selectedTicket = ExamEntryTicket::with(['examClearance', 'examClearance.student', 'examClearance.clearable'])
                                ->findOrFail($ticketId);
                                
        $this->showingTicketDetailsModal = true;
        $this->dispatch('show-ticket-details-modal');
    }
    
    public function closeTicketDetails()
    {
        $this->showingTicketDetailsModal = false;
        $this->selectedTicket = null;
    }
    
    public function printTicket($ticketId)
    {
        return redirect()->route('finance.exam.ticket.print', ['ticketId' => $ticketId]);
    }
    
    public function render()
    {
        $ticketsQuery = ExamEntryTicket::query()
            ->with(['examClearance', 'examClearance.student', 'examClearance.clearable'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('ticket_number', 'like', '%' . $this->search . '%')
                          ->orWhere('verification_code', 'like', '%' . $this->search . '%')
                          ->orWhereHas('examClearance.student', function (Builder $query) {
                              $query->where('name', 'like', '%' . $this->search . '%')
                                   ->orWhere('student_id', 'like', '%' . $this->search . '%');
                          });
                });
            })
            ->when($this->examFilter, function ($query) {
                $query->whereHas('examClearance', function (Builder $query) {
                    $query->where('clearable_id', $this->examFilter)
                          ->where('clearable_type', 'App\\Models\\OfflineExam');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest();
            
        $tickets = $ticketsQuery->paginate($this->perPage);
        
        return view('livewire.admin.exam-tickets', [
            'tickets' => $tickets,
            'exams' => $this->exams
        ]);
    }
}
