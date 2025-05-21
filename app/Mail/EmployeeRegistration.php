<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeRegistration extends Mailable
{
    use Queueable, SerializesModels;
    public $Name;
    public $oldCode;
    public $genderName;
    public $orgName;
    public $siteName;
    public $ccName;
    public $cadreName;
    public $positionName;
    public $weekHrs;
    public $Manager;
    public $QualLevelName;
    public $employeeStatusName;
    public $empWorkingStatusName;
    public $provinceName;
    public $divisionName;
    public $districtName;
    public $mobileNo;
    public $CNIC;
    public $Landline;
    public $Email;
    public $Address;
    public $emailDOJ;
    public $emailDOB;
    public $emailStatus;
    public $emailTimestamp;
    public $emailEdt;
    /**
     * Create a new message instance.
     */
     public function __construct($Name, $oldCode, $genderName,
                                $orgName, $siteName, $ccName, $cadreName, $positionName,
                                $weekHrs, $Manager, $QualLevelName, $employeeStatusName, $empWorkingStatusName,
                                $provinceName, $divisionName, $districtName, $mobileNo, $CNIC, $Landline,
                                $Email, $Address, $emailDOJ, $emailDOB, $emailStatus, $emailTimestamp,
                                $emailEdt)
    {
        $this->Name = $Name;
        $this->oldCode = $oldCode;
        $this->genderName = $genderName;
        $this->orgName = $orgName;
        $this->siteName = $siteName;
        $this->ccName = $ccName;
        $this->cadreName = $cadreName;
        $this->positionName = $positionName;
        $this->provinceName = $provinceName;
        $this->divisionName = $divisionName;
        $this->districtName = $districtName;
        $this->mobileNo = $mobileNo;
        $this->CNIC = $CNIC;
        $this->Landline = $Landline;
        $this->Email = $Email;
        $this->Address = $Address;
        $this->emailDOJ = $emailDOJ;
        $this->Manager = $Manager;
        $this->emailDOB = $emailDOB;
        $this->emailStatus = $emailStatus;
        $this->emailTimestamp = $emailTimestamp;
        $this->emailEdt = $emailEdt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome To '.ucwords($this->orgName).'',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.employee_registration',
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
