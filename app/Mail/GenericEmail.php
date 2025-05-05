<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class GenericEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The email data.
     *
     * @var array
     */
    public $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailData['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->emailData['template'] ?? 'emails.generic',
            with: [
                'content' => $this->emailData['content'],
                'emailData' => $this->emailData,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if (!empty($this->emailData['attachments'])) {
            foreach ($this->emailData['attachments'] as $attachment) {
                if (is_array($attachment) && isset($attachment['path'])) {
                    $attachmentObj = Attachment::fromPath($attachment['path']);
                    
                    if (isset($attachment['name'])) {
                        $attachmentObj->as($attachment['name']);
                    }
                    
                    if (isset($attachment['mime'])) {
                        $attachmentObj->withMime($attachment['mime']);
                    }
                    
                    $attachments[] = $attachmentObj;
                } else if (is_string($attachment)) {
                    $attachments[] = Attachment::fromPath($attachment);
                }
            }
        }
        
        return $attachments;
    }
}