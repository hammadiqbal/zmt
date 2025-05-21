<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRegistration extends Mailable
{
    use Queueable, SerializesModels;
    public $username;
    public $useremail;
    public $pwd;
    public $roleName;
    public $orgName;
    public $siteName;
    public $empName;
    public $emailStatus;
    public $emailEdt;
    public $emailTimestamp;

    /**
     * Create a new message instance.
     */
    public function __construct($username, $useremail, $pwd,
                $roleName, $orgName, $siteName, $empName, $emailStatus,
                $emailEdt, $emailTimestamp)
    {
        $this->username = $username;
        $this->useremail = $useremail;
        $this->pwd = $pwd;
        $this->roleName = $roleName;
        $this->orgName = $orgName;
        $this->siteName = $siteName;
        $this->empName = $empName;
        $this->emailStatus = $emailStatus;
        $this->emailEdt = $emailEdt;
        $this->emailTimestamp = $emailTimestamp;
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome On Board',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_registration',
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
