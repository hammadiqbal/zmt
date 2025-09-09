<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alertType;
    public $itemGeneric;
    public $itemBrand;
    public $siteName;
    public $orgName;
    public $locationName;
    public $currentBalance;
    public $thresholdValue;
    public $alertMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($alertType, $itemGeneric, $itemBrand, $siteName, $orgName, $locationName, $currentBalance, $thresholdValue)
    {
        $this->alertType = $alertType;
        $this->itemGeneric = $itemGeneric;
        $this->itemBrand = $itemBrand;
        $this->siteName = $siteName;
        $this->orgName = $orgName;
        $this->locationName = $locationName;
        $this->currentBalance = $currentBalance;
        $this->thresholdValue = $thresholdValue;
        
        // Generate alert message based on type
        switch ($alertType) {
            case 'min_stock':
                $this->alertMessage = "Stock level is below minimum threshold. Current balance: {$currentBalance}, Minimum required: {$thresholdValue}";
                break;
            case 'max_stock':
                $this->alertMessage = "Stock level exceeds maximum threshold. Current balance: {$currentBalance}, Maximum allowed: {$thresholdValue}";
                break;
            case 'consumption_ceiling':
                $this->alertMessage = "Monthly consumption exceeds. Limit: {$thresholdValue}";
                break;
            case 'reorder_qty':
                $this->alertMessage = "Stock level is below reorder quantity. Current balance: {$currentBalance}, Minimum reorder: {$thresholdValue}";
                break;
            default:
                $this->alertMessage = "Stock alert for {$itemGeneric} - {$itemBrand}";
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Stock Alert - ' . ucfirst(str_replace('_', ' ', $this->alertType)),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.stock_alert',
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
