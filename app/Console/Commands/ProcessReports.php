<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportManagement;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Log;

class ProcessReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending reports in the queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting chunked report processing...');
        Log::info('ProcessReports: Starting chunked report processing');
        
        $startTime = time();
        $maxExecutionTime = 20; // 20 seconds max per cron job
        
        try {
            // First: Process pending/processing reports
            $this->processActiveReports($startTime, $maxExecutionTime);
            
            // Second: Send emails for completed reports (if time allows)
            $remainingTime = $maxExecutionTime - (time() - $startTime);
            if ($remainingTime > 5) { // At least 5 seconds left
                $this->processCompletedReports($remainingTime);
            }
            
            $this->info("Chunked processing completed for this cron job.");
            Log::info('ProcessReports: Chunked processing completed successfully');
            
        } catch (\Exception $e) {
            $this->error("ProcessReports failed: " . $e->getMessage());
            Log::error('ProcessReports: Command failed with exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Process active reports (pending/processing)
     */
    private function processActiveReports($startTime, $maxExecutionTime)
    {
        Log::info('ProcessReports: Starting processActiveReports', [
            'startTime' => $startTime,
            'maxExecutionTime' => $maxExecutionTime
        ]);
        
        // Get pending and processing reports
            $activeReports = ReportManagement::whereIn('status', [
                ReportManagement::STATUS_PENDING, 
                ReportManagement::STATUS_PROCESSING
            ])->where('progress_percentage', '<', 95) // Exclude reports ready for PDF generation (95%)
            ->orderBy('created_at', 'asc')->get();
        
        Log::info('ProcessReports: Found active reports', [
            'count' => $activeReports->count(),
            'reports' => $activeReports->map(function($report) {
                return [
                    'id' => $report->id,
                    'status' => $report->status,
                    'progress' => $report->progress_percentage,
                    'user_id' => $report->user_id
                ];
            })->toArray()
        ]);
        
        if ($activeReports->isEmpty()) {
            $this->info('No active reports to process.');
            Log::info('ProcessReports: No active reports found');
            return;
        }
        
        $this->info("Found {$activeReports->count()} active report(s).");
        
        foreach ($activeReports as $report) {
            // Check if we have time left
            $currentTime = time();
            $elapsedTime = $currentTime - $startTime;
            
            Log::info('ProcessReports: Processing report', [
                'reportId' => $report->id,
                'userId' => $report->user_id,
                'elapsedTime' => $elapsedTime,
                'maxTime' => $maxExecutionTime
            ]);
            
            if ($elapsedTime >= $maxExecutionTime) {
                $this->info("Time limit reached. Stopping processing for this cron job.");
                Log::info('ProcessReports: Time limit reached, stopping processing', [
                    'elapsedTime' => $elapsedTime,
                    'maxTime' => $maxExecutionTime
                ]);
                break;
            }
            
            $this->info("Processing report ID: {$report->id} for user: {$report->user_id}");
            
            try {
                // Process report in chunks
                $result = $this->processReportChunked($report, $startTime, $maxExecutionTime);
                
                Log::info('ProcessReports: Report processing completed', [
                    'reportId' => $report->id,
                    'result' => $result
                ]);
                
            } catch (\Exception $e) {
                $this->error("✗ Report ID {$report->id} failed with error: " . $e->getMessage());
                
                Log::error('ProcessReports: Report processing failed', [
                    'reportId' => $report->id,
                    'userId' => $report->user_id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                // Mark report as failed
                $report->markAsFailed($e->getMessage());
            }
        }
        
        Log::info('ProcessReports: Finished processing active reports');
    }
    
    /**
     * Process completed reports (send emails)
     */
    private function processCompletedReports($maxTime)
    {
        // Get completed reports that haven't had emails sent
        $completedReports = ReportManagement::where('status', ReportManagement::STATUS_COMPLETED)
            ->whereNull('email_sent_at')
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($completedReports->isEmpty()) {
            $this->info('No completed reports waiting for email.');
            return;
        }
        
        $this->info("Found {$completedReports->count()} completed report(s) waiting for email.");
        
        $startTime = time();
        
        foreach ($completedReports as $report) {
            // Check if we have time left
            if ((time() - $startTime) >= $maxTime) {
                $this->info("Time limit reached for email processing.");
                break;
            }
            
            $this->info("Sending email for completed report ID: {$report->id}");
            
            try {
                // Create ReportController instance and send email
                $reportController = new ReportController();
                $reportController->sendReportEmailAsync($report);
                
                $this->info("✓ Email sent for report ID: {$report->id}");
                
            } catch (\Exception $e) {
                $this->error("✗ Email failed for report ID {$report->id}: " . $e->getMessage());
                Log::error("Email sending failed for report ID {$report->id}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Process report in chunks with time limit
     */
    private function processReportChunked($report, $startTime, $maxExecutionTime)
    {
        switch ($report->module_name) {
            case ReportManagement::MODULE_INVENTORY_REPORT:
                return $this->processInventoryReportChunked($report, $startTime, $maxExecutionTime);
            
            default:
                $this->error("Unknown module: {$report->module_name}");
                $report->markAsFailed("Unknown module: {$report->module_name}");
                return false;
        }
    }
    
    /**
     * Process inventory report
     */
    private function processInventoryReport($report)
    {
        switch ($report->report_type) {
            case ReportManagement::TYPE_PDF:
                return $this->processInventoryReportPDF($report);
            
            default:
                $this->error("Unknown report type: {$report->report_type}");
                $report->markAsFailed("Unknown report type: {$report->report_type}");
                return false;
        }
    }
    
    /**
     * Process inventory report in chunks
     */
    private function processInventoryReportChunked($report, $startTime, $maxExecutionTime)
    {
        Log::info('ProcessReports: Starting inventory report processing', [
            'reportId' => $report->id,
            'userId' => $report->user_id,
            'startTime' => $startTime,
            'maxExecutionTime' => $maxExecutionTime
        ]);
        
        try {
            // Create ReportController instance
            $reportController = new ReportController();
            
            Log::info('ProcessReports: Created ReportController instance');
            
            // Process the report in chunks
            $success = $reportController->processInventoryReportChunked($report->id, $startTime, $maxExecutionTime);
            
            Log::info('ProcessReports: Inventory report processing completed', [
                'reportId' => $report->id,
                'success' => $success
            ]);
            
            return $success;
            
        } catch (\Exception $e) {
            $this->error("Inventory PDF processing failed: " . $e->getMessage());
            
            Log::error('ProcessReports: Inventory PDF processing failed', [
                'reportId' => $report->id,
                'userId' => $report->user_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $report->markAsFailed($e->getMessage());
            return false;
        }
    }
}
