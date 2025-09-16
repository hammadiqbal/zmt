<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SiteRegistration extends Mailable
{
    use Queueable, SerializesModels;
    public $siteName;
    public $organizationName;
    public $siteRemarks;
    public $siteaddress;
    public $sitepersonname;
    public $districtName;
    public $divisionName;
    public $provinceName;
    public $sitelandline;
    public $sitecell;
    public $sitegps;
    public $sitewebsite;
    public $sitepersonemail;
    public $emailEdt;
    public $emailTimestamp;
    public $status;

    /**
     * Create a new message instance.
     */
     public function __construct($siteName, $organizationName, $siteRemarks,
                                $siteaddress, $provinceName, $divisionName, $districtName,
                                $sitepersonname,$sitepersonemail, $sitewebsite, $sitegps,
                                $sitecell, $sitelandline,$status, $emailTimestamp, $emailEdt)
    {
        $this->siteName = $siteName;
        $this->organizationName = $organizationName;
        $this->siteRemarks = $siteRemarks;
        $this->siteaddress = $siteaddress;
        $this->provinceName = $provinceName;
        $this->divisionName = $divisionName;
        $this->districtName = $districtName;
        $this->sitepersonname = $sitepersonname;
        $this->sitepersonemail = $sitepersonemail;
        $this->sitewebsite = $sitewebsite;
        $this->sitegps = $sitegps;
        $this->sitecell = $sitecell;
        $this->sitelandline = $sitelandline;
        $this->status = $status;
        $this->emailTimestamp = $emailTimestamp;
        $this->emailEdt = $emailEdt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome To '. config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.site_registration',
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
