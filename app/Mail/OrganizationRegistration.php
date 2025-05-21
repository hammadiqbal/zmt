<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationRegistration extends Mailable
{
    use Queueable, SerializesModels;
    public $orgName;
    public $orgCode;
    public $orgRemarks;
    public $orgpersonname;
    public $districtName;
    public $divisionName;
    public $provinceName;
    public $orgaddress;
    public $orglandline;
    public $orgcell;
    public $orggps;
    public $orgwebsite;
    public $orgpersonemail;
    public $emailEdt;
    public $emailTimestamp;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct($orgName, $orgCode, $orgRemarks,
                                $orgaddress, $provinceName, $divisionName, $districtName, $orgpersonname,
                                $orgpersonemail, $orgwebsite, $orggps, $orgcell, $orglandline,
                                $status, $emailTimestamp, $emailEdt)
    {
        $this->orgName = $orgName;
        $this->orgCode = $orgCode;
        $this->orgRemarks = $orgRemarks;
        $this->orgaddress = $orgaddress;
        $this->provinceName = $provinceName;
        $this->divisionName = $divisionName;
        $this->districtName = $districtName;
        $this->orgpersonname = $orgpersonname;
        $this->orgpersonemail = $orgpersonemail;
        $this->orgwebsite = $orgwebsite;
        $this->orggps = $orggps;
        $this->orgcell = $orgcell;
        $this->orglandline = $orglandline;
        $this->status = $status;
        $this->emailTimestamp = $emailTimestamp;
        $this->emailEdt = $emailEdt;
    }

    // /**
    //  * Build the message.
    //  *
    //  * @return $this
    //  */
    // public function build()
    // {
    //     return $this->view('emails.organization_registration');
    // }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome To ZMT Primary HealthCare Network',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.organization_registration',
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
