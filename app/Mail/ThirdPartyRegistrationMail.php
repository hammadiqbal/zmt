<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ThirdPartyRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $RegistrationType;
    public $VendorCat;
    public $Address;
    public $orgName;
    public $PersonName;
    public $Email;
    public $Cell;
    public $Landline;
    public $Remarks;
    public $emailStatus;
    public $emailEdt;
    public $emailTimestamp;

    /**
     * Create a new message instance.
     */
    public function __construct($RegistrationType,$VendorCat, $Address, $orgName,
                $PersonName,  $Email, $Cell, $Landline, $Remarks, $emailStatus,
                $emailEdt, $emailTimestamp)
    {
        $this->RegistrationType = $RegistrationType;
        $this->VendorCat = $VendorCat;
        $this->Address = $Address;
        $this->orgName = $orgName;
        $this->PersonName = $PersonName;
        $this->Email = $Email;
        $this->Cell = $Cell;
        $this->Landline = $Landline;
        $this->Remarks = $Remarks;
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
            subject: 'ZMT Primary HealthCare Network - '.$this->RegistrationType. ' Registration',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.thirdparty_registration',
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
