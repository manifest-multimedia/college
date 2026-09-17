<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCredentialsMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $credentialsData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $credentialsData)
    {
        $this->credentialsData = $credentialsData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $institutionName = config('branding.institution.name', config('app.name', 'College Portal'));
        return new Envelope(
            subject: "Your {$institutionName} Account Credentials",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.account-credentials',
            with: [
                'data' => $this->credentialsData,
            ],
        );
    }
}
