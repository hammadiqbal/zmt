<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonorRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $orgName;
    public $CorporateName;
    public $DonorType;
    public $FocalPersonName;
    public $FocalPersonEmail;
    public $FocalPersonCell;
    public $FocalPersonLandline;
    public $Address;
    public $Remarks;
    public $emailEdt;

    /**
     * Create a new message instance.
     */
    public function __construct($orgName, $CorporateName, $DonorType,
    $FocalPersonName, $FocalPersonEmail, $FocalPersonCell, $FocalPersonLandline, $Address,
    $Remarks, $emailEdt)
    {
        $this->orgName = $orgName;
        $this->CorporateName = $CorporateName;
        $this->DonorType = $DonorType;
        $this->FocalPersonName = $FocalPersonName;
        $this->FocalPersonEmail = $FocalPersonEmail;
        $this->FocalPersonCell = $FocalPersonCell;
        $this->FocalPersonLandline = $FocalPersonLandline;
        $this->Address = $Address;
        $this->Remarks = $Remarks;
        $this->emailEdt = $emailEdt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ZMT: Donor Registration Successful',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.donor_registration',
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
