<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportManagement;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Log;

class ProcessPDFGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:process-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process PDF generation for reports ready at 95% progress';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting PDF generation processing...');
        Log::info('ProcessPDFGeneration: Starting PDF generation processing');
        
        $startTime = time();
        $maxExecutionTime = 300; // 5 minutes max for PDF generation
        
        try {
            // Get reports ready for PDF generation (95% progress)
            $pdfReadyReports = ReportManagement::where('status', ReportManagement::STATUS_PROCESSING)
                ->where('progress_percentage', 95)
                ->orderBy('created_at', 'asc')
                ->get();
            
            Log::info('ProcessPDFGeneration: Found PDF-ready reports', [
                'count' => $pdfReadyReports->count(),
                'reports' => $pdfReadyReports->map(function($report) {
                    return [
                        'id' => $report->id,
                        'status' => $report->status,
                        'progress' => $report->progress_percentage,
                        'user_id' => $report->user_id
                    ];
                })->toArray()
            ]);
            
            if ($pdfReadyReports->isEmpty()) {
                Log::info('ProcessPDFGeneration: No PDF-ready reports found');
                $this->info('No reports ready for PDF generation.');
                return 0;
            }
            
            foreach ($pdfReadyReports as $report) {
                $currentTime = time();
                $elapsedTime = $currentTime - $startTime;
                
                Log::info('ProcessPDFGeneration: Processing PDF generation', [
                    'reportId' => $report->id,
                    'userId' => $report->user_id,
                    'elapsedTime' => $elapsedTime,
                    'maxTime' => $maxExecutionTime
                ]);
                
                if ($elapsedTime >= $maxExecutionTime) {
                    Log::info('ProcessPDFGeneration: Time limit reached, stopping PDF processing', [
                        'elapsedTime' => $elapsedTime,
                        'maxTime' => $maxExecutionTime
                    ]);
                    break;
                }
                
                try {
                    $result = $this->processPDFGeneration($report, $startTime, $maxExecutionTime);
                    
                    Log::info('ProcessPDFGeneration: PDF generation completed', [
                        'reportId' => $report->id,
                        'result' => $result
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('ProcessPDFGeneration: PDF generation failed', [
                        'reportId' => $report->id,
                        'userId' => $report->user_id,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    $report->markAsFailed($e->getMessage());
                }
            }
            
            Log::info('ProcessPDFGeneration: PDF generation processing completed successfully');
            $this->info('PDF generation processing completed successfully.');
            
        } catch (\Exception $e) {
            $this->error("ProcessPDFGeneration failed: " . $e->getMessage());
            Log::error('ProcessPDFGeneration: Command failed with exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    private function processPDFGeneration($report, $startTime, $maxExecutionTime)
    {
        Log::info('ProcessPDFGeneration: Starting PDF generation', [
            'reportId' => $report->id,
            'module' => $report->module_name
        ]);
        
        switch ($report->module_name) {
            case ReportManagement::MODULE_INVENTORY_REPORT:
                return $this->processInventoryPDFGeneration($report, $startTime, $maxExecutionTime);
            
            default:
                Log::error("ProcessPDFGeneration: Unknown module: {$report->module_name}");
                $report->markAsFailed("Unknown module: {$report->module_name}");
                return false;
        }
    }
    
    private function processInventoryPDFGeneration($report, $startTime, $maxExecutionTime)
    {
        Log::info('ProcessPDFGeneration: Starting inventory PDF generation', [
            'reportId' => $report->id,
            'userId' => $report->user_id,
            'startTime' => $startTime,
            'maxExecutionTime' => $maxExecutionTime
        ]);
        
        try {
            $reportController = new ReportController();
            Log::info('ProcessPDFGeneration: Created ReportController instance');
            
            // Call the PDF generation method directly
            $success = $reportController->generatePDFForReport($report->id);
            
            Log::info('ProcessPDFGeneration: Inventory PDF generation completed', [
                'reportId' => $report->id,
                'success' => $success
            ]);
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('ProcessPDFGeneration: Inventory PDF generation failed', [
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
