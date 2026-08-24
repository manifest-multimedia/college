<?php

namespace App\Livewire;

use App\Models\SupportTicket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketDetail extends Component
{
    use WithFileUploads;

    public $ticketId;

    public $ticket;

    public $replyMessage = '';

    public $attachments = [];

    protected $rules = [
        'replyMessage' => 'required|string',
        'attachments.*' => 'nullable|file|max:2048',
    ];

    public function mount($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->loadTicket();

        if (Schema::hasColumn('support_tickets', 'public_id') &&
            (string) $this->ticket->public_id !== (string) $ticketId) {
            return redirect()->route('support.ticket.detail', ['ticketId' => $this->ticket->public_id]);
        }
    }

    public function loadTicket()
    {
        $ticketQuery = SupportTicket::with([
            'user',
            'category',
            'assignedTo',
            'replies.user',
            'replies.attachments',
            'attachments',
        ])->where('user_id', Auth::id());

        $hasPublicId = Schema::hasColumn('support_tickets', 'public_id');

        $this->ticket = $hasPublicId
            ? (clone $ticketQuery)->where('public_id', $this->ticketId)->first()
            : null;

        // Existing bookmarks remain usable during the staged production rollout.
        if (! $this->ticket && (! $hasPublicId || ctype_digit((string) $this->ticketId))) {
            $this->ticket = $ticketQuery->whereKey((int) $this->ticketId)->first();
        }

        abort_unless($this->ticket, 404);
    }

    public function render()
    {
        return view('livewire.ticket-detail')
            ->layout('components.dashboard.default', [
                'title' => 'Ticket Detail - '.$this->ticket->ticket_number,
            ]);
    }

    public function submitReply()
    {
        if ($this->ticket->isClosed()) {
            session()->flash('error', 'Cannot reply to a closed ticket.');

            return;
        }

        $this->validate();

        $reply = TicketReply::create([
            'support_ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'message' => $this->replyMessage,
            'is_internal_note' => false,
            'is_system_message' => false,
        ]);

        // Handle file uploads
        if (! empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $path = $file->store('support_attachments', 'public');

                TicketAttachment::create([
                    'attachable_type' => TicketReply::class,
                    'attachable_id' => $reply->id,
                    'user_id' => Auth::id(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Update ticket status if it's not already in progress
        if ($this->ticket->status === 'Open') {
            $this->ticket->update(['status' => 'In Progress']);
        }

        session()->flash('success', 'Your reply has been submitted successfully!');

        $this->reset(['replyMessage', 'attachments']);
        $this->loadTicket();
    }

    public function closeTicket()
    {
        if (! $this->ticket->isClosed()) {
            $this->ticket->update([
                'status' => 'Closed',
                'closed_at' => now(),
            ]);

            session()->flash('success', 'Ticket has been closed!');
            $this->loadTicket();
        }
    }

    public function reopenTicket()
    {
        if ($this->ticket->isClosed()) {
            $this->ticket->update([
                'status' => 'Open',
                'closed_at' => null,
                'resolved_at' => null,
            ]);

            session()->flash('success', 'Ticket has been reopened!');
            $this->loadTicket();
        }
    }
}
