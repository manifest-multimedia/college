<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class FeatureRequestModal extends Component
{
    public $feature_title;
    public $feature_description;

    #[On('showFeatureRequestModal')]
    public function showModal()
    {
        dd('Working');
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
                    ->subject('New Feature Request: ' . $validatedData['feature_title']);
        });

        // Optionally, reset the form fields after submission
        $this->reset(['feature_title', 'feature_description']);

        // Dispatch an event to close the modal
        $this->dispatch('closeFeatureRequestModal');
    }
}