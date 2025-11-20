<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

class SupportRequestModal extends Component
{
    public $support_message;

    #[On('closeSupportModal')]
    public function hideModal()
    {
        $this->reset(['support_message']);
    }

    #[On('showRequestSupportModal')]
    public function showModal()
    {
        $this->reset(['support_message']);
    }

    public function render()
    {
        return view('livewire.support-request-modal');
    }

    public function cancelRequest()
    {
        $this->dispatch('closeSupportModal');
    }

    public function submitRequest()
    {
        // Validate Request
        $validatedData = $this->validate([
            'support_message' => 'required|string',
        ]);

        // Get the logged-in user's name and email
        $user = Auth::user();
        if ($user) {
            $validatedData['user_name'] = $user->name;
            $validatedData['user_email'] = $user->email;
        }

        // Send an Email to johnson@pnmtc.edu.gh with request details
        Mail::send('emails.support_request', $validatedData, function ($message) use ($validatedData) {
            $message->to('johnson@pnmtc.edu.gh')
                ->subject('New Support Request: '.$validatedData['user_name']);
        });

        session()->flash('success', 'Congratulations! Your support request has been sent successfully.');

        // Optionally, reset the form fields after submission
        $this->reset(['support_message']);

        // Dispatch an event to close the modal
        $this->dispatch('closeFeatureRequestModal');
    }
}
