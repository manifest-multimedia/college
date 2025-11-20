<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

class FeatureRequestModal extends Component
{
    public $feature_title;

    public $feature_description;

    #[On('showFeatureRequestModal')]
    public function showModal()
    {
        $this->reset(['feature_title', 'feature_description']);
    }

    #[On('closeFeatureRequestModal')]
    public function hideModal()
    {
        $this->reset(['feature_title', 'feature_description']);
    }

    public function render()
    {
        return view('livewire.feature-request-modal');
    }

    public function cancelRequest()
    {
        $this->dispatch('closeFeatureRequestModal');
    }

    public function submitRequest()
    {
        // Validate Request
        $validatedData = $this->validate([
            'feature_title' => 'required|string|max:255',
            'feature_description' => 'required|string',
        ]);

        // Get the logged-in user's name and email
        $user = Auth::user();
        if ($user) {
            $validatedData['user_name'] = $user->name;
            $validatedData['user_email'] = $user->email;
        }

        // Send an Email to johnson@pnmtc.edu.gh with request details
        Mail::send('emails.feature_request', $validatedData, function ($message) use ($validatedData) {
            $message->to('johnson@pnmtc.edu.gh')
                ->subject('New Feature Request: '.$validatedData['feature_title']);
        });

        session()->flash('success', 'Congratulations! Your new feature request has been successfully sent.');

        // Optionally, reset the form fields after submission
        $this->reset(['feature_title', 'feature_description']);

        // Dispatch an event to close the modal
        $this->dispatch('closeFeatureRequestModal');
    }
}
