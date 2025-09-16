<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmpEmailUpdate extends Mailable
{
    use Queueable, SerializesModels;
    public $oldEmail;
    public $newEmail;
    public $userName;
    public $pwd;
    public $sessionName;

    /**
     * Create a new message instance.
     */
    public function __construct($oldEmail, $newEmail, $userName,$pwd,$sessionName)
    {
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
        $this->userName = $userName;
        $this->pwd = $pwd;
        $this->sessionName = $sessionName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ZMT Primary HealthCare Network - Email Update Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.emp_email_update',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
