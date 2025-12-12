<?php

namespace App\Livewire;

use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SupportTickets extends Component
{
    use WithFileUploads;

    public $statusFilter = 'all';

    public $subject = '';

    public $category_id = '';

    public $priority = 'Medium';

    public $message = '';

    public $attachments = [];

    public $selectedTicket = null;

    public $showCreateModal = false;

    protected $rules = [
        'subject' => 'required|string|max:255',
        'category_id' => 'required|exists:support_categories,id',
        'priority' => 'required|in:Low,Medium,High,Urgent',
        'message' => 'required|string',
        'attachments.*' => 'nullable|file|max:2048', // 2MB max
    ];

    public function mount()
    {
        // Initialize
    }

    public function render()
    {
        $tickets = SupportTicket::with(['user', 'category', 'replies'])
            ->where('user_id', Auth::id())
            ->when($this->statusFilter !== 'all', function ($query) {
                if ($this->statusFilter === 'open') {
                    return $query->where('status', 'Open');
                } elseif ($this->statusFilter === 'in_progress') {
                    return $query->where('status', 'In Progress');
                } elseif ($this->statusFilter === 'closed') {
                    return $query->whereIn('status', ['Closed', 'Resolved']);
                }
            })
            ->latest()
            ->get();

        $categories = SupportCategory::where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('livewire.support-tickets', [
            'tickets' => $tickets,
            'categories' => $categories,
        ])->layout('components.dashboard.default');
    }

    public function createTicket()
    {
        $this->validate();

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'support_category_id' => $this->category_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'priority' => $this->priority,
            'status' => 'Open',
        ]);

        // Handle file uploads
        if (! empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $path = $file->store('support_attachments', 'public');

                TicketAttachment::create([
                    'attachable_type' => SupportTicket::class,
                    'attachable_id' => $ticket->id,
                    'user_id' => Auth::id(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        session()->flash('success', 'Your support ticket has been created successfully!');

        $this->reset(['subject', 'category_id', 'priority', 'message', 'attachments', 'showCreateModal']);
        $this->dispatch('ticket-created');
        $this->dispatch('close-modal', 'create_ticket_modal');
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
    }

    public function viewTicket($ticketId)
    {
        return redirect()->route('support.ticket.detail', $ticketId);
    }

    public function closeTicket($ticketId)
    {
        $ticket = SupportTicket::where('id', $ticketId)
            ->where('user_id', Auth::id())
            ->first();

        if ($ticket && ! $ticket->isClosed()) {
            $ticket->update([
                'status' => 'Closed',
                'closed_at' => now(),
            ]);

            session()->flash('success', 'Ticket has been closed!');
        }
    }

    public function reopenTicket($ticketId)
    {
        $ticket = SupportTicket::where('id', $ticketId)
            ->where('user_id', Auth::id())
            ->first();

        if ($ticket && $ticket->isClosed()) {
            $ticket->update([
                'status' => 'Open',
                'closed_at' => null,
                'resolved_at' => null,
            ]);

            session()->flash('success', 'Ticket has been reopened!');
        }
    }
}
