<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class SupportRequestModal extends Component
{
    public $message; 

    #[On('closeSupportModal')]
    public function hideModal(){
        $this->reset(['message']);
    }

    #[On('showRequestSupportModal')]
    public function showModal(){
        $this->reset(['message']);
    }


    public function render()
    {
        return view('livewire.support-request-modal');
    }

    public function cancelRequest(){
        $this->dispatch('closeSupportModal');
    }

    public function submitRequest(){
        // Validate Request
        $validatedData = $this->validate([
            'message' => 'required|string',
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
                    ->subject('New Support Request: ' . $validatedData['user_name']);
        });

        session()->flash('success', 'Congratulations! Your support request has been sent successfully.');

        // Optionally, reset the form fields after submission
        $this->reset(['feature_title', 'feature_description']);

        // Dispatch an event to close the modal
        $this->dispatch('closeFeatureRequestModal');
    }

}
