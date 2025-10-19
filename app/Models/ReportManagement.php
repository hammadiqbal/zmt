<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;

class ReportManagement extends Model
{
    use HasFactory;

    protected $table = 'report_mgmt';

    protected $fillable = [
        'user_id',
        'module_name',
        'report_type',
        'request_data',
        'status',
        'file_path',
        'file_name',
        'error_message',
        'progress_percentage',
        'processed_at',
        'email_sent_at'
    ];

    protected $casts = [
        'request_data' => 'array',
        'processed_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Module constants
    const MODULE_INVENTORY_REPORT = 'inventory_report';

    // Report type constants
    const TYPE_PDF = 'pdf';
    const TYPE_EXCEL = 'excel';
    const TYPE_CSV = 'csv';

    /**
     * Get pending reports for processing
     */
    public static function getPendingReports()
    {
        return self::where('status', self::STATUS_PENDING)
                  ->orderBy('created_at', 'asc')
                  ->get();
    }

    /**
     * Mark report as processing
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'progress_percentage' => 10,
            'processed_at' => now()
        ]);
    }

    /**
     * Update progress percentage
     */
    public function updateProgress($percentage)
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage))
        ]);
    }

    /**
     * Mark report as completed (without email sent)
     */
    public function markAsCompletedWithoutEmail()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'progress_percentage' => 100,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark report as completed
     */
    public function markAsCompleted($filePath, $fileName)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'progress_percentage' => 100,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark report as failed
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark email as sent
     */
    public function markEmailSent()
    {
        $this->update([
            'email_sent_at' => now()
        ]);
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedDate()
    {
        return $this->created_at->format('M d, Y H:i:s');
    }

    /**
     * Get estimated completion time
     */
    public function getEstimatedCompletionTime()
    {
        $minutes = 5; // Default estimate
        return now()->addMinutes($minutes)->format('H:i');
    }

    /**
     * Check if report is ready for download
     */
    public function isReadyForDownload()
    {
        return $this->status === self::STATUS_COMPLETED && !empty($this->file_path);
    }

    /**
     * Get user who requested the report
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    /**
     * Scope for inventory reports
     */
    public function scopeInventoryReports($query)
    {
        return $query->where('module_name', self::MODULE_INVENTORY_REPORT);
    }

    /**
     * Scope for PDF reports
     */
    public function scopePdfReports($query)
    {
        return $query->where('report_type', self::TYPE_PDF);
    }

    /**
     * Scope for user's reports
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
