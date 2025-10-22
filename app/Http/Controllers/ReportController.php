<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;

use App\Models\InventoryManagement;
use App\Models\InventoryBalance;
use App\Models\InventoryCategory;
use App\Models\InventorySubCategory;
use App\Models\InventoryType;
use App\Models\InventoryBrand;
use App\Models\InventoryGeneric;
use App\Models\InventoryTransactionType;
use App\Models\InventoryTransactionActivity;
use App\Models\InventorySourceDestinationType;

use App\Models\Organization;
use App\Models\Site;
use App\Models\PatientRegistration;
use App\Models\Users;
use App\Models\ServiceLocation;
use App\Models\ThirdPartyRegistration;
use App\Models\ConsumptionGroup;
use App\Models\ConsumptionMethod;
use App\Models\StockMonitoring;
use App\Models\MedicationRoutes;
use App\Models\MedicationFrequency;
use App\Models\ReportManagement;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    private $assignedSites;
    private $spreadsheet;
    
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // if (Auth::check() && Auth::user()->role_id == 1) {
            $this->currentDatetime = Carbon::now('Asia/Karachi')->timestamp;
            $this->sessionUser = session('user');
            $this->roles = session('role');
            $this->rights = session('rights');
            $this->assignedSites = session('sites');
            if (Auth::check()) {
                // dd('if');
                return $next($request);
            } else {
                // dd('else'        );

                return redirect('/');
            }
        });
    }


    public function InventoryReport()
    {
        $colName = 'inventory_report';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Categories = InventoryCategory::where('status', 1)->select('id', 'name')->get();
        $TransactionTypes = InventoryTransactionType::where('status', 1)->select('id', 'name')->get();
        $Generics = InventoryGeneric::where('status', 1)->select('id', 'name')->get();
        $Sites = Site::where('status', 1)->select('id', 'name');
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Sites->whereIn('id', $sessionSiteIds);
            }
        }
        $Sites = $Sites->get();
        $Organizations = Organization::where('status', 1)->select('id', 'organization')->get();

        return view('dashboard.reports.inventory_report', compact('user','Categories','TransactionTypes','Sites','Generics','Organizations'));
    }

    public function getInventoryReportData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->inventory_report)[1];
        if ($view != 1) {
            abort(403, 'Forbidden');
        }

        // Get form data
        $startDateInput = $request->input('start');
        $endDateInput = $request->input('end');
        $sites = $request->input('ir_site', []); // Default to empty array if not provided
        $transactionTypes = $request->input('ir_transactiontype', []); // Default to empty array if not provided
        $generics = $request->input('ir_generic', []); // Default to empty array if not provided
        $brands = $request->input('ir_brand', []); // Default to empty array if not provided
        $batches = $request->input('ir_batch', []); // Default to empty array if not provided
        $locations = $request->input('ir_location', []); // Default to empty array if not provided
        
        // Parse date range from separate inputs
        $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
        $endDate = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();
        
        // Convert to timestamps
        $startTimestamp = $startDate->timestamp;
        $endTimestamp = $endDate->timestamp;
        
        // Build query with joins for inventory_balance, inventory_management, transaction_type, brand, generic, and source/destination types
        $query = DB::table('inventory_balance')->distinct()
            ->join('inventory_management', 'inventory_balance.management_id', '=', 'inventory_management.id')
            ->leftJoin('inventory_transaction_type', 'inventory_management.transaction_type_id', '=', 'inventory_transaction_type.id')
            ->join('inventory_brand', 'inventory_balance.brand_id', '=', 'inventory_brand.id')
            ->join('inventory_generic', 'inventory_balance.generic_id', '=', 'inventory_generic.id')
            ->leftJoin('inventory_source_destination_type as source_type', 'source_type.id', '=', 'inventory_transaction_type.source_location_type')
            ->leftJoin('inventory_source_destination_type as destination_type', 'destination_type.id', '=', 'inventory_transaction_type.destination_location_type')
            ->leftJoin('service_location as source_location', function($join) {
                $join->on('source_location.id', '=', 'inventory_management.source')
                     ->whereRaw('LOWER(source_type.name) LIKE "%location%"');
            })
            ->leftJoin('service_location as destination_location', function($join) {
                $join->on('destination_location.id', '=', 'inventory_management.destination')
                     ->whereRaw('LOWER(destination_type.name) LIKE "%location%"');
            })
            ->leftJoin('org_site', 'inventory_balance.site_id', '=', 'org_site.id')
            ->leftJoin('service_location as balance_location', 'inventory_balance.location_id', '=', 'balance_location.id')
            ->leftJoin('third_party as source_vendor', function($join) {
                $join->on('source_vendor.id', '=', 'inventory_management.source')
                     ->whereRaw('LOWER(source_type.name) LIKE "%vendor%"');
            })
            ->leftJoin('third_party as destination_vendor', function($join) {
                $join->on('destination_vendor.id', '=', 'inventory_management.destination')
                     ->whereRaw('LOWER(destination_type.name) LIKE "%vendor%"');
            })
            ->whereBetween('inventory_balance.timestamp', [$startTimestamp, $endTimestamp]);
        
        // Handle site filtering
        if (!empty($sites) && !in_array('0101', $sites)) {
            $siteIds = [];
            foreach ($sites as $site) {
                if (strpos($site, ',') !== false) {
                    $siteIds = array_merge($siteIds, array_map('intval', explode(',', $site)));
                } else {
                    $siteIds[] = intval($site);
                }
            }
            
            if (!empty($siteIds)) {
                $query->whereIn('inventory_balance.site_id', $siteIds);
            }
        }
        
        // Handle transaction type filtering
        if (!empty($transactionTypes) && !in_array('0101', $transactionTypes)) {
            $transactionTypeIds = [];
            foreach ($transactionTypes as $transactionType) {
                if (strpos($transactionType, ',') !== false) {
                    $transactionTypeIds = array_merge($transactionTypeIds, array_map('intval', explode(',', $transactionType)));
                } else {
                    $transactionTypeIds[] = intval($transactionType);
                }
            }
            
            if (!empty($transactionTypeIds)) {
                $query->whereIn('inventory_management.transaction_type_id', $transactionTypeIds);
            }
        }
        
        // Handle generic filtering
        if (!empty($generics) && !in_array('0101', $generics)) {
            $genericIds = [];
            foreach ($generics as $generic) {
                if (strpos($generic, ',') !== false) {
                    $genericIds = array_merge($genericIds, array_map('intval', explode(',', $generic)));
                } else {
                    $genericIds[] = intval($generic);
                }
            }
            
            if (!empty($genericIds)) {
                $query->whereIn('inventory_balance.generic_id', $genericIds);
            }
        }
        
        // Handle brand filtering
        if (!empty($brands) && !in_array('0101', $brands)) {
            $brandIds = [];
            foreach ($brands as $brand) {
                if (strpos($brand, ',') !== false) {
                    $brandIds = array_merge($brandIds, array_map('intval', explode(',', $brand)));
                } else {
                    $brandIds[] = intval($brand);
                }
            }
            
            if (!empty($brandIds)) {
                $query->whereIn('inventory_balance.brand_id', $brandIds);
            }
        }

        // Handle batch filtering
        if (!empty($batches) && !in_array('0101', $batches)) {
            $batchNos = [];
            foreach ($batches as $batch) {
                if (strpos($batch, ',') !== false) {
                    $batchNos = array_merge($batchNos, explode(',', $batch));
                } else {
                    $batchNos[] = $batch;
                }
            }
            
            if (!empty($batchNos)) {
                // Trim whitespace from each batch number
                $batchNos = array_map('trim', $batchNos);
                $query->whereIn('inventory_balance.batch_no', $batchNos);
            }
        }
        
        // Handle location filtering
        if (!empty($locations) && !in_array('0101', $locations)) {
            $locationIds = [];
            foreach ($locations as $location) {
                if (strpos($location, ',') !== false) {
                    $locationIds = array_merge($locationIds, array_map('intval', explode(',', $location)));
                } else {
                    $locationIds[] = intval($location);
                }
            }
            
            if (!empty($locationIds)) {
                $query->whereIn('inventory_balance.location_id', $locationIds);
            }
        }
        // dd($query->toSql());
        
        // Select all required fields from all tables
        $reportData = $query->select(
                'inventory_balance.id as balance_id',
                'inventory_balance.site_id',
                'inventory_balance.management_id',
                'inventory_balance.generic_id',
                'inventory_balance.brand_id',
                'inventory_balance.batch_no',
                'inventory_balance.org_balance',
                'inventory_balance.site_balance',
                'inventory_balance.location_balance',
                'inventory_management.remarks',
                'inventory_balance.timestamp',
                'inventory_management.transaction_type_id',
                'inventory_management.ref_document_no',
                'inventory_management.source',
                'inventory_management.destination',
                'inventory_management.mr_code',
                'inventory_management.transaction_qty',
                'inventory_management.site_id as management_site_id',
                'inventory_management.d_site_id as management_d_site_id',
                'inventory_management.inv_generic_id as management_generic_ids',
                'inventory_management.brand_id as management_brand_ids',
                'inventory_management.batch_no as management_batch_nos',
                'inventory_transaction_type.name as transaction_type_name',
                'inventory_brand.name as brand_name',
                'inventory_generic.name as generic_name',
                'source_type.name as source_type_name',
                'destination_type.name as destination_type_name',
                'source_location.name as source_location_name',
                'destination_location.name as destination_location_name',
                'org_site.name as site_name',
                'balance_location.name as location_name',
                'source_vendor.person_name as source_vendor_person_name',
                'source_vendor.corporate_name as source_vendor_corporate_name',
                'destination_vendor.person_name as destination_vendor_person_name',
                'destination_vendor.corporate_name as destination_vendor_corporate_name'
            )
            ->orderBy('inventory_balance.timestamp', 'asc')
            ->get();

        // Process comma-separated values to get accurate transaction_qty
        $processedData = $reportData->map(function($item) {
            $genericIds = $item->management_generic_ids ? explode(',', $item->management_generic_ids) : [];
            $brandIds = $item->management_brand_ids ? explode(',', $item->management_brand_ids) : [];
            $batchNos = $item->management_batch_nos ? explode(',', $item->management_batch_nos) : [];
            $transactionQtys = $item->transaction_qty ? explode(',', $item->transaction_qty) : [];
            
            $matchedIndex = -1;
            for ($i = 0; $i < count($genericIds); $i++) {
                if (isset($genericIds[$i]) && isset($brandIds[$i]) && isset($batchNos[$i])) {
                    $genericMatch = trim($genericIds[$i]) == $item->generic_id;
                    $brandMatch = trim($brandIds[$i]) == $item->brand_id;
                    $batchMatch = trim($batchNos[$i]) == $item->batch_no;
                    
                    if ($genericMatch && $brandMatch && $batchMatch) {
                        $matchedIndex = $i;
                        break;
                    }
                }
            }
            
            if ($matchedIndex >= 0 && isset($transactionQtys[$matchedIndex])) {
                $item->accurate_transaction_qty = trim($transactionQtys[$matchedIndex]);
            } else {
                $item->accurate_transaction_qty = '0';
            }
            
            unset($item->management_generic_ids);
            unset($item->management_brand_ids);
            unset($item->management_batch_nos);
            
            // Add site label
            if (!empty($item->management_site_id) && !empty($item->management_d_site_id)) {
                if ($item->site_id == $item->management_site_id) {
                    $item->site_label = 'Source Site';
                } elseif ($item->site_id == $item->management_d_site_id) {
                    $item->site_label = 'Destination Site';
                }
            }
            else{
                $item->site_label = 'Site:';
            }
            
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $processedData,
            'total_records' => $processedData->count(),
            'date_range' => $startDateInput . ' - ' . $endDateInput,
            'sites' => $sites,
            'transaction_types' => $transactionTypes,
            'generics' => $generics,
            'brands' => $brands,
            'batches' => $batches,
            'locations' => $locations
        ]);
    }

    /**
     * Request inventory report Excel (background processing)
     */
    public function requestInventoryReportExcel(Request $request)
    {
        $rights = $this->rights;
        $download = explode(',', $rights->inventory_report)[0];
        if ($download != 1) {
            abort(403, 'Forbidden');
        }

        // Store report request in database
        $reportRequest = ReportManagement::create([
            'user_id' => auth()->id(),
            'module_name' => ReportManagement::MODULE_INVENTORY_REPORT,
            'report_type' => ReportManagement::TYPE_EXCEL,
            'request_data' => $request->all(),
            'status' => ReportManagement::STATUS_PENDING,
            'progress_percentage' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report request submitted successfully. You will receive an email when ready.',
            'report_id' => $reportRequest->id,
            'estimated_time' => $reportRequest->getEstimatedCompletionTime()
        ]);
    }

    /**
     * Check report status
     */
    public function checkReportStatus(Request $request)
    {
        $reportId = $request->input('report_id');
        
        $report = ReportManagement::where('id', $reportId)
                                 ->where('user_id', auth()->id())
                                 ->first();

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $report->status,
            'progress' => $report->progress_percentage,
            'message' => $this->getStatusMessage($report->status, $report->progress_percentage, $report->email_sent_at),
            'download_ready' => $report->isReadyForDownload(),
            'file_name' => $report->file_name,
            'email_sent' => $report->email_sent_at ? true : false
        ]);
    }

    /**
     * Check user reports (pending/processing/completed)
     */
    public function checkUserReports(Request $request)
    {
        $userId = auth()->id();
        
        $reports = ReportManagement::where('user_id', $userId)
                                 ->whereIn('status', [
                                     ReportManagement::STATUS_PENDING,
                                     ReportManagement::STATUS_PROCESSING,
                                     ReportManagement::STATUS_COMPLETED,
                                     ReportManagement::STATUS_FAILED
                                 ])
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5) // Show only last 5 reports
                                 ->get();

        $reportData = $reports->map(function($report) {
            return [
                'id' => $report->id,
                'status' => $report->status,
                'progress_percentage' => $report->progress_percentage,
                'message' => $this->getStatusMessage($report->status, $report->progress_percentage),
                'created_at' => $report->created_at->format('M d, Y H:i'),
                'file_name' => $report->file_name
            ];
        });

        return response()->json([
            'success' => true,
            'reports' => $reportData
        ]);
    }

    /**
     * Process inventory report Excel in chunks (called by cron job)
     */
    public function processInventoryReportChunked($reportId, $startTime, $maxExecutionTime)
    {
        Log::info('ReportController: Starting processInventoryReportChunked', [
            'reportId' => $reportId,
            'startTime' => $startTime,
            'maxExecutionTime' => $maxExecutionTime
        ]);
        
        $report = ReportManagement::find($reportId);
        
        if (!$report) {
            Log::error('ReportController: Report not found', ['reportId' => $reportId]);
            return false;
        }
        
        Log::info('ReportController: Found report', [
            'reportId' => $report->id,
            'status' => $report->status,
            'progress' => $report->progress_percentage,
            'userId' => $report->user_id
        ]);

        try {
            // Check if this is the first run
            if ($report->status === ReportManagement::STATUS_PENDING) {
                Log::info('ReportController: Marking report as processing');
                $report->markAsProcessing();
                $report->updateProgress(5);
            }
            
            // Get request data
            $requestData = $report->request_data;
            
            // Check time limit
            $currentTime = time();
            $elapsedTime = $currentTime - $startTime;
            
            Log::info('ReportController: Time check', [
                'elapsedTime' => $elapsedTime,
                'maxExecutionTime' => $maxExecutionTime,
                'timeRemaining' => $maxExecutionTime - $elapsedTime
            ]);
            
            if ($elapsedTime >= $maxExecutionTime) {
                Log::info('ReportController: Time limit reached, continuing in next cron job');
                return true; // Continue in next cron job
            }
            
            // Parse date range (only once)
            if ($report->progress_percentage < 10) {
                $startDate = Carbon::createFromFormat('m/d/Y', $requestData['start'])->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $requestData['end'])->endOfDay();
                $startTimestamp = $startDate->timestamp;
                $endTimestamp = $endDate->timestamp;
        
                $report->updateProgress(10);
                
                // Store timestamps in request_data for next run
                $requestData['startTimestamp'] = $startTimestamp;
                $requestData['endTimestamp'] = $endTimestamp;
                $report->update(['request_data' => $requestData]);
            } else {
                $startTimestamp = $requestData['startTimestamp'];
                $endTimestamp = $requestData['endTimestamp'];
            }
            
            // Check time limit again
            if ((time() - $startTime) >= $maxExecutionTime) {
                return true;
            }
            
            // Build query (only if not done yet)
            if ($report->progress_percentage < 20) {
            $query = DB::table('inventory_balance')->distinct()
            ->join('inventory_management', 'inventory_balance.management_id', '=', 'inventory_management.id')
            ->leftJoin('inventory_transaction_type', 'inventory_management.transaction_type_id', '=', 'inventory_transaction_type.id')
            ->join('inventory_brand', 'inventory_balance.brand_id', '=', 'inventory_brand.id')
            ->join('inventory_generic', 'inventory_balance.generic_id', '=', 'inventory_generic.id')
            ->leftJoin('inventory_source_destination_type as source_type', 'source_type.id', '=', 'inventory_transaction_type.source_location_type')
            ->leftJoin('inventory_source_destination_type as destination_type', 'destination_type.id', '=', 'inventory_transaction_type.destination_location_type')
            ->leftJoin('service_location as source_location', function($join) {
                $join->on('source_location.id', '=', 'inventory_management.source')
                     ->whereRaw('LOWER(source_type.name) LIKE "%location%"');
            })
            ->leftJoin('service_location as destination_location', function($join) {
                $join->on('destination_location.id', '=', 'inventory_management.destination')
                     ->whereRaw('LOWER(destination_type.name) LIKE "%location%"');
            })
            ->leftJoin('org_site', 'inventory_balance.site_id', '=', 'org_site.id')
            ->leftJoin('service_location as balance_location', 'inventory_balance.location_id', '=', 'balance_location.id')
            ->leftJoin('third_party as source_vendor', function($join) {
                $join->on('source_vendor.id', '=', 'inventory_management.source')
                     ->whereRaw('LOWER(source_type.name) LIKE "%vendor%"');
            })
            ->leftJoin('third_party as destination_vendor', function($join) {
                $join->on('destination_vendor.id', '=', 'inventory_management.destination')
                     ->whereRaw('LOWER(destination_type.name) LIKE "%vendor%"');
            })
            ->whereBetween('inventory_balance.timestamp', [$startTimestamp, $endTimestamp]);
        
                // Apply filters
                $this->applyReportFilters($query, $requestData);
                
                $report->updateProgress(20);
            }
            
            // Check time limit again
            if ((time() - $startTime) >= $maxExecutionTime) {
                return true;
            }
            
            // Get data in chunks (only if not done yet)
            if ($report->progress_percentage < 25) {
                Log::info('ReportController: Setting up initial query and counting records');
                $query = $this->buildReportQuery($requestData);
                $totalRecords = $query->count();
                
                // Store total records for progress calculation
                $requestData['totalRecords'] = $totalRecords;
                $requestData['processedRecords'] = 0;
                $report->update(['request_data' => $requestData]);
                
                $report->updateProgress(25);
                Log::info('ReportController: Initial setup completed', [
                    'totalRecords' => $totalRecords,
                    'progress' => $report->progress_percentage
                ]);
            } else {
                Log::info('ReportController: Skipping initial setup (already completed)', [
                    'currentProgress' => $report->progress_percentage
                ]);
            }
            
            // Check time limit again
            if ((time() - $startTime) >= $maxExecutionTime) {
                return true;
            }
            
            // Process data in chunks (only if not already processed)
            if ($report->progress_percentage < 90) {
                Log::info('ReportController: Starting data processing in chunks');
                $this->processDataInChunks($report, $requestData, $startTime, $maxExecutionTime);
                
                Log::info('ReportController: Data processing completed', [
                    'progress' => $report->progress_percentage
                ]);
            } else {
                Log::info('ReportController: Skipping data processing (already completed)', [
                    'currentProgress' => $report->progress_percentage
                ]);
            }
            
            // Check if processing is complete (90% progress)
            if ($report->progress_percentage >= 90) {
                Log::info('ReportController: Report ready for Excel generation');
                
                // Check if Excel file already exists
                if ($report->file_path && Storage::exists($report->file_path)) {
                    Log::info('ReportController: Excel file already exists, skipping generation', [
                        'filePath' => $report->file_path
                    ]);
                    
                    // Keep status as processing until email is sent
                    $report->updateProgress(100);
                    
                    Log::info('ReportController: Report ready for email sending');
                    
                    return true;
                }
                
                // Check time limit before Excel generation
                $elapsedTime = time() - $startTime;
                // Require at least a small buffer to finish writing the file
                if ($elapsedTime >= ($maxExecutionTime - 5)) {
                    Log::info('ReportController: Time limit reached before Excel generation', [
                        'elapsedTime' => $elapsedTime,
                        'maxExecutionTime' => $maxExecutionTime
                    ]);
                    return true; // Stop processing, will continue in next cron job
                }
                
                // Generate Excel file with time limit
                $this->generateExcel($report, $requestData, $startTime, $maxExecutionTime);
                
                Log::info('ReportController: Excel generated successfully');
                
                // Keep status as processing until email is sent - email will be sent in next cron job
                $report->updateProgress(100);
                
                Log::info('ReportController: Report marked as completed');
                
                return true;
            }
            
            Log::info('ReportController: Continuing in next cron job', [
                'progress' => $report->progress_percentage
            ]);
            
            return true; // Continue in next cron job
            
        } catch (\Exception $e) {
            Log::error('ReportController: Exception in processInventoryReportChunked', [
                'reportId' => $reportId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clean up temporary data file on error
            $tempFilePath = storage_path('app/temp/report_' . $report->id . '_data.json');
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
                Log::info('ReportController: Cleaned up temporary data file');
            }
            
            $report->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Apply report filters to query
     */
    private function applyReportFilters($query, $requestData)
    {
        // Site filtering
        if (!empty($requestData['ir_site']) && !in_array('0101', $requestData['ir_site'])) {
            $siteIds = [];
            foreach ($requestData['ir_site'] as $site) {
                if (strpos($site, ',') !== false) {
                    $siteIds = array_merge($siteIds, array_map('intval', explode(',', $site)));
                } else {
                    $siteIds[] = intval($site);
                }
            }
            
            if (!empty($siteIds)) {
                $query->whereIn('inventory_balance.site_id', $siteIds);
            }
        }
        
        // Transaction type filtering
        if (!empty($requestData['ir_transactiontype']) && !in_array('0101', $requestData['ir_transactiontype'])) {
            $transactionTypeIds = [];
            foreach ($requestData['ir_transactiontype'] as $transactionType) {
                if (strpos($transactionType, ',') !== false) {
                    $transactionTypeIds = array_merge($transactionTypeIds, array_map('intval', explode(',', $transactionType)));
                } else {
                    $transactionTypeIds[] = intval($transactionType);
                }
            }
            
            if (!empty($transactionTypeIds)) {
                $query->whereIn('inventory_management.transaction_type_id', $transactionTypeIds);
            }
        }
        
        // Generic filtering
        if (!empty($requestData['ir_generic']) && !in_array('0101', $requestData['ir_generic'])) {
            $genericIds = [];
            foreach ($requestData['ir_generic'] as $generic) {
                if (strpos($generic, ',') !== false) {
                    $genericIds = array_merge($genericIds, array_map('intval', explode(',', $generic)));
                } else {
                    $genericIds[] = intval($generic);
                }
            }
            
            if (!empty($genericIds)) {
                $query->whereIn('inventory_balance.generic_id', $genericIds);
            }
        }
        
        // Brand filtering
        if (!empty($requestData['ir_brand']) && !in_array('0101', $requestData['ir_brand'])) {
            $brandIds = [];
            foreach ($requestData['ir_brand'] as $brand) {
                if (strpos($brand, ',') !== false) {
                    $brandIds = array_merge($brandIds, array_map('intval', explode(',', $brand)));
                } else {
                    $brandIds[] = intval($brand);
                }
            }
            
            if (!empty($brandIds)) {
                $query->whereIn('inventory_balance.brand_id', $brandIds);
            }
        }
        
        // Batch filtering
        if (!empty($requestData['ir_batch']) && !in_array('0101', $requestData['ir_batch'])) {
            $batchNos = [];
            foreach ($requestData['ir_batch'] as $batch) {
                if (strpos($batch, ',') !== false) {
                    $batchNos = array_merge($batchNos, explode(',', $batch));
                } else {
                    $batchNos[] = $batch;
                }
            }
            
            if (!empty($batchNos)) {
                $batchNos = array_map('trim', $batchNos);
                $query->whereIn('inventory_balance.batch_no', $batchNos);
            }
        }
        
        // Location filtering
        if (!empty($requestData['ir_location']) && !in_array('0101', $requestData['ir_location'])) {
            $locationIds = [];
            foreach ($requestData['ir_location'] as $location) {
                if (strpos($location, ',') !== false) {
                    $locationIds = array_merge($locationIds, array_map('intval', explode(',', $location)));
                } else {
                    $locationIds[] = intval($location);
                }
            }
            
            if (!empty($locationIds)) {
                $query->whereIn('inventory_balance.location_id', $locationIds);
            }
        }
    }

    /**
     * Process report data
     */
    private function processReportData($reportData)
    {
        return $reportData->map(function($item) {
            $genericIds = $item->management_generic_ids ? explode(',', $item->management_generic_ids) : [];
            $brandIds = $item->management_brand_ids ? explode(',', $item->management_brand_ids) : [];
            $batchNos = $item->management_batch_nos ? explode(',', $item->management_batch_nos) : [];
            $transactionQtys = $item->transaction_qty ? explode(',', $item->transaction_qty) : [];
            
            $matchedIndex = -1;
            for ($i = 0; $i < count($genericIds); $i++) {
                if (isset($genericIds[$i]) && isset($brandIds[$i]) && isset($batchNos[$i])) {
                    $genericMatch = trim($genericIds[$i]) == $item->generic_id;
                    $brandMatch = trim($brandIds[$i]) == $item->brand_id;
                    $batchMatch = trim($batchNos[$i]) == $item->batch_no;
                    
                    if ($genericMatch && $brandMatch && $batchMatch) {
                        $matchedIndex = $i;
                        break;
                    }
                }
            }
            
            if ($matchedIndex >= 0 && isset($transactionQtys[$matchedIndex])) {
                $item->accurate_transaction_qty = trim($transactionQtys[$matchedIndex]);
            } else {
                $item->accurate_transaction_qty = '0';
            }
            
            unset($item->management_generic_ids);
            unset($item->management_brand_ids);
            unset($item->management_batch_nos);
            
            // Add site label
            if (!empty($item->management_site_id) && !empty($item->management_d_site_id)) {
                if ($item->site_id == $item->management_site_id) {
                    $item->site_label = 'Source Site';
                } elseif ($item->site_id == $item->management_d_site_id) {
                    $item->site_label = 'Destination Site';
                }
            } else {
                $item->site_label = 'Site:';
            }
            
            return $item;
        });
    }

    /**
     * Prepare filter data for email display
     */
    private function prepareFilterDataForEmail($requestData)
    {
        $filterData = [];
        
        // Date Range
        $filterData['date_range'] = ($requestData['start'] ?? 'N/A') . ' to ' . ($requestData['end'] ?? 'N/A');
        
        // Sites
        if (!empty($requestData['ir_site']) && !in_array('0101', $requestData['ir_site'])) {
            $siteIds = [];
            foreach ($requestData['ir_site'] as $site) {
                if (strpos($site, ',') !== false) {
                    $siteIds = array_merge($siteIds, array_map('intval', explode(',', $site)));
                } else {
                    $siteIds[] = intval($site);
                }
            }
            
            if (!empty($siteIds)) {
                $siteNames = DB::table('org_site')
                    ->whereIn('id', $siteIds)
                    ->pluck('name')
                    ->toArray();
                $filterData['sites'] = implode(', ', $siteNames);
            } else {
                $filterData['sites'] = 'All Sites';
            }
        } else {
            $filterData['sites'] = 'All Sites';
        }
        
        // Transaction Types
        if (!empty($requestData['ir_transactiontype']) && !in_array('0101', $requestData['ir_transactiontype'])) {
            $transactionTypeIds = [];
            foreach ($requestData['ir_transactiontype'] as $transactionType) {
                if (strpos($transactionType, ',') !== false) {
                    $transactionTypeIds = array_merge($transactionTypeIds, array_map('intval', explode(',', $transactionType)));
                } else {
                    $transactionTypeIds[] = intval($transactionType);
                }
            }
            
            if (!empty($transactionTypeIds)) {
                $transactionTypeNames = DB::table('inventory_transaction_type')
                    ->whereIn('id', $transactionTypeIds)
                    ->pluck('name')
                    ->toArray();
                $filterData['transaction_types'] = implode(', ', $transactionTypeNames);
            } else {
                $filterData['transaction_types'] = 'All Transaction Types';
            }
        } else {
            $filterData['transaction_types'] = 'All Transaction Types';
        }
        
        // Generics
        if (!empty($requestData['ir_generic']) && !in_array('0101', $requestData['ir_generic'])) {
            $genericIds = [];
            foreach ($requestData['ir_generic'] as $generic) {
                if (strpos($generic, ',') !== false) {
                    $genericIds = array_merge($genericIds, array_map('intval', explode(',', $generic)));
                } else {
                    $genericIds[] = intval($generic);
                }
            }
            
            if (!empty($genericIds)) {
                $genericNames = DB::table('inventory_generic')
                    ->whereIn('id', $genericIds)
                    ->pluck('name')
                    ->toArray();
                $filterData['generics'] = implode(', ', $genericNames);
            } else {
                $filterData['generics'] = 'All Generics';
            }
        } else {
            $filterData['generics'] = 'All Generics';
        }
        
        // Brands
        if (!empty($requestData['ir_brand']) && !in_array('0101', $requestData['ir_brand'])) {
            $brandIds = [];
            foreach ($requestData['ir_brand'] as $brand) {
                if (strpos($brand, ',') !== false) {
                    $brandIds = array_merge($brandIds, array_map('intval', explode(',', $brand)));
                } else {
                    $brandIds[] = intval($brand);
                }
            }
            
            if (!empty($brandIds)) {
                $brandNames = DB::table('inventory_brand')
                    ->whereIn('id', $brandIds)
                    ->pluck('name')
                    ->toArray();
                $filterData['brands'] = implode(', ', $brandNames);
            } else {
                $filterData['brands'] = 'All Brands';
            }
        } else {
            $filterData['brands'] = 'All Brands';
        }
        
        // Batch Numbers
        if (!empty($requestData['ir_batch']) && !in_array('0101', $requestData['ir_batch'])) {
            $batchNos = [];
            foreach ($requestData['ir_batch'] as $batch) {
                if (strpos($batch, ',') !== false) {
                    $batchNos = array_merge($batchNos, explode(',', $batch));
                } else {
                    $batchNos[] = $batch;
                }
            }
            
            if (!empty($batchNos)) {
                $batchNos = array_map('trim', $batchNos);
                $filterData['batches'] = implode(', ', $batchNos);
            } else {
                $filterData['batches'] = 'All Batches';
            }
        } else {
            $filterData['batches'] = 'All Batches';
        }
        
        // Locations
        if (!empty($requestData['ir_location']) && !in_array('0101', $requestData['ir_location'])) {
            $locationIds = [];
            foreach ($requestData['ir_location'] as $location) {
                if (strpos($location, ',') !== false) {
                    $locationIds = array_merge($locationIds, array_map('intval', explode(',', $location)));
                } else {
                    $locationIds[] = intval($location);
                }
            }
            
            if (!empty($locationIds)) {
                $locationNames = DB::table('service_location')
                    ->whereIn('id', $locationIds)
                    ->pluck('name')
                    ->toArray();
                $filterData['locations'] = implode(', ', $locationNames);
            } else {
                $filterData['locations'] = 'All Locations';
            }
        } else {
            $filterData['locations'] = 'All Locations';
        }
        
        return $filterData;
    }

    /**
     * Send report email notification asynchronously
     */
    public function sendReportEmailAsync($report)
    {
        Log::info('ReportController: Starting sendReportEmailAsync', [
            'reportId' => $report->id,
            'userId' => $report->user_id,
            'emailSentAt' => $report->email_sent_at
        ]);
        
        // Check if email was already sent
        if ($report->email_sent_at) {
            Log::info('ReportController: Email already sent, skipping', [
                'reportId' => $report->id,
                'emailSentAt' => $report->email_sent_at
            ]);
            return;
        }
        
        try {
            Log::info('ReportController: Finding user', ['userId' => $report->user_id]);
            $user = Users::find($report->user_id);
            
            if (!$user) {
                Log::error('ReportController: User not found', [
                    'reportId' => $report->id,
                    'userId' => $report->user_id
                ]);
                return;
            }
            
            Log::info('ReportController: User found', [
                'userId' => $user->id,
                'userName' => $user->name ?? 'NULL',
                'userEmail' => $user->email ?? 'NULL',
                'userAttributes' => array_keys($user->toArray())
            ]);
            
            // Prepare filter data for email
            Log::info('ReportController: Preparing filter data');
            $filterData = $this->prepareFilterDataForEmail($report->request_data);
            
            Log::info('ReportController: Filter data prepared', ['filterData' => $filterData]);
            
            // Check if file exists
            $fileExists = false;
            if ($report->file_path && Storage::exists($report->file_path)) {
                $fileExists = true;
                Log::info('ReportController: Report file exists', [
                    'filePath' => $report->file_path,
                    'fullPath' => storage_path('app/' . $report->file_path)
                ]);
            } else {
                Log::warning('ReportController: Report file not found', [
                    'filePath' => $report->file_path,
                    'storageExists' => $report->file_path ? Storage::exists($report->file_path) : false
                ]);
            }
            
            Log::info('ReportController: Sending email');
            Mail::send('emails.report_delivery', [
                'user' => $user,
                'report' => $report,
                'filterData' => $filterData
            ], function($message) use ($user, $report, $fileExists) {
                $message->to($user->email, $user->name)
                        ->subject('Your Inventory Report is Ready');
                
                if ($fileExists) {
                    $message->attach(storage_path('app/' . $report->file_path));
                    Log::info('ReportController: Email attachment added', [
                        'filePath' => $report->file_path
                    ]);
                }
            });
            
            Log::info('ReportController: Email sent successfully');
            
            // Mark email as sent and report as completed
            $report->markEmailSent();
            $report->markAsCompleted($report->file_path, $report->file_name);
            
            Log::info('ReportController: Email marked as sent and report completed', [
                'reportId' => $report->id,
                'emailSentAt' => $report->email_sent_at,
                'status' => $report->status
            ]);
            
        } catch (\Exception $e) {
            Log::error('ReportController: Failed to send report email', [
                'reportId' => $report->id,
                'userId' => $report->user_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Build report query
     */
    private function buildReportQuery($requestData)
    {
        $startTimestamp = $requestData['startTimestamp'];
        $endTimestamp = $requestData['endTimestamp'];
        
        $query = DB::table('inventory_balance')->distinct()
            ->join('inventory_management', 'inventory_balance.management_id', '=', 'inventory_management.id')
            ->leftJoin('inventory_transaction_type', 'inventory_management.transaction_type_id', '=', 'inventory_transaction_type.id')
            ->join('inventory_brand', 'inventory_balance.brand_id', '=', 'inventory_brand.id')
            ->join('inventory_generic', 'inventory_balance.generic_id', '=', 'inventory_generic.id')
            ->leftJoin('inventory_source_destination_type as source_type', 'source_type.id', '=', 'inventory_transaction_type.source_location_type')
            ->leftJoin('inventory_source_destination_type as destination_type', 'destination_type.id', '=', 'inventory_transaction_type.destination_location_type')
            ->leftJoin('service_location as source_location', function($join) {
                $join->on('source_location.id', '=', 'inventory_management.source')
                     ->whereRaw('LOWER(source_type.name) LIKE "%location%"');
            })
            ->leftJoin('service_location as destination_location', function($join) {
                $join->on('destination_location.id', '=', 'inventory_management.destination')
                     ->whereRaw('LOWER(destination_type.name) LIKE "%location%"');
            })
            ->leftJoin('org_site', 'inventory_balance.site_id', '=', 'org_site.id')
            ->leftJoin('service_location as balance_location', 'inventory_balance.location_id', '=', 'balance_location.id')
            ->leftJoin('third_party as source_vendor', function($join) {
                $join->on('source_vendor.id', '=', 'inventory_management.source')
                     ->whereRaw('LOWER(source_type.name) LIKE "%vendor%"');
            })
            ->leftJoin('third_party as destination_vendor', function($join) {
                $join->on('destination_vendor.id', '=', 'inventory_management.destination')
                     ->whereRaw('LOWER(destination_type.name) LIKE "%vendor%"');
            })
            ->whereBetween('inventory_balance.timestamp', [$startTimestamp, $endTimestamp]);
        
        $this->applyReportFilters($query, $requestData);
        
        return $query;
    }

    /**
     * Process data in chunks
     */
    private function processDataInChunks($report, $requestData, $startTime, $maxExecutionTime)
    {
        Log::info('ReportController: Starting processDataInChunks', [
            'reportId' => $report->id,
            'processedRecords' => $requestData['processedRecords'] ?? 0,
            'totalRecords' => $requestData['totalRecords'] ?? 0
        ]);
        
        $chunkSize = 100; // Process 100 records at a time
        $processedRecords = $requestData['processedRecords'] ?? 0;
        $totalRecords = $requestData['totalRecords'] ?? 0;
        
        if ($totalRecords == 0) {
            Log::info('ReportController: No records to process');
            return;
        }
        
        $query = $this->buildReportQuery($requestData);
        
        Log::info('ReportController: Built query, starting chunk processing', [
            'chunkSize' => $chunkSize,
            'processedRecords' => $processedRecords,
            'totalRecords' => $totalRecords
        ]);
        
        // Process multiple chunks until time limit or all records processed
        while ($processedRecords < $totalRecords) {
            // Check time limit
            $currentTime = time();
            $elapsedTime = $currentTime - $startTime;
            
            if ($elapsedTime >= $maxExecutionTime) {
                Log::info('ReportController: Time limit reached in processDataInChunks', [
                    'elapsedTime' => $elapsedTime,
                    'maxExecutionTime' => $maxExecutionTime,
                    'processedRecords' => $processedRecords,
                    'totalRecords' => $totalRecords
                ]);
                break;
            }
            
            // Get data in chunks
            $offset = $processedRecords;
            $chunkData = $query->select(
                'inventory_balance.id as balance_id',
                'inventory_balance.site_id',
                'inventory_balance.management_id',
                'inventory_balance.generic_id',
                'inventory_balance.brand_id',
                'inventory_balance.batch_no',
                'inventory_balance.org_balance',
                'inventory_balance.site_balance',
                'inventory_balance.location_balance',
                'inventory_management.remarks',
                'inventory_balance.timestamp',
                'inventory_management.transaction_type_id',
                'inventory_management.ref_document_no',
                'inventory_management.source',
                'inventory_management.destination',
                'inventory_management.mr_code',
                'inventory_management.transaction_qty',
                'inventory_management.site_id as management_site_id',
                'inventory_management.d_site_id as management_d_site_id',
                'inventory_management.inv_generic_id as management_generic_ids',
                'inventory_management.brand_id as management_brand_ids',
                'inventory_management.batch_no as management_batch_nos',
                'inventory_transaction_type.name as transaction_type_name',
                'inventory_brand.name as brand_name',
                'inventory_generic.name as generic_name',
                'source_type.name as source_type_name',
                'destination_type.name as destination_type_name',
                'source_location.name as source_location_name',
                'destination_location.name as destination_location_name',
                'org_site.name as site_name',
                'balance_location.name as location_name',
                'source_vendor.person_name as source_vendor_person_name',
                'source_vendor.corporate_name as source_vendor_corporate_name',
                'destination_vendor.person_name as destination_vendor_person_name',
                'destination_vendor.corporate_name as destination_vendor_corporate_name'
            )
            ->orderBy('inventory_balance.timestamp', 'asc')
            ->offset($offset)
            ->limit($chunkSize)
            ->get();

            // If no more data, break
            if ($chunkData->isEmpty()) {
                break;
            }

            // Process chunk
            $processedData = $this->processReportData($chunkData);
            
            // Debug logging
            
            // Store processed data in temporary file instead of database
            $tempFilePath = storage_path('app/temp/report_' . $report->id . '_data.json');
            
            // Ensure temp directory exists
            if (!file_exists(dirname($tempFilePath))) {
                mkdir(dirname($tempFilePath), 0755, true);
            }
            
            // Read existing data from file
            $allProcessedData = collect();
            if (file_exists($tempFilePath)) {
                $existingData = json_decode(file_get_contents($tempFilePath), true);
                if ($existingData) {
                    $allProcessedData = collect($existingData);
                }
            }
            
            // Merge new data
            $allProcessedData = $allProcessedData->merge($processedData);
            
            // Write back to file
            file_put_contents($tempFilePath, json_encode($allProcessedData->toArray()));
            
            $processedRecords += $chunkData->count();
            
            // Update progress
            $progress = min(90, 25 + (($processedRecords / $totalRecords) * 65));
            $report->updateProgress($progress);
            
            // Debug logging
            
            // Store only essential metadata in database (not the full data)
            $requestData['processedRecords'] = $processedRecords;
            $requestData['tempDataFile'] = 'temp/report_' . $report->id . '_data.json';
            $report->update(['request_data' => $requestData]);
        }
        
        // Check if we've processed all records
        if ($processedRecords >= $totalRecords) {
            $report->updateProgress(90);
        }
    }

    /**
     * Generate CSV from processed data
     */
    private function generateExcel($report, $requestData, $startTime = null, $maxExecutionTime = null)
    {
        Log::info('ReportController: Starting generateExcel', [
            'reportId' => $report->id,
            'tempDataFile' => $requestData['tempDataFile'] ?? 'temp/report_' . $report->id . '_data.json'
        ]);
        
        // Check time limit if parameters provided
        if ($startTime && $maxExecutionTime) {
            $elapsedTime = time() - $startTime;
            if ($elapsedTime >= $maxExecutionTime) {
                Log::info('ReportController: Time limit reached in generateExcel', [
                    'elapsedTime' => $elapsedTime,
                    'maxExecutionTime' => $maxExecutionTime
                ]);
                throw new \Exception('Time limit reached during Excel generation');
            }
        }
        
        // Read processed data from temporary file
        $tempFilePath = storage_path('app/' . ($requestData['tempDataFile'] ?? 'temp/report_' . $report->id . '_data.json'));
        
        Log::info('ReportController: Reading temp file', ['tempFilePath' => $tempFilePath]);
        
        if (!file_exists($tempFilePath)) {
            Log::error('ReportController: Temp file not found', ['tempFilePath' => $tempFilePath]);
            throw new \Exception('Processed data file not found: ' . $tempFilePath);
        }
        
        $processedDataArray = json_decode(file_get_contents($tempFilePath), true);
        if (!$processedDataArray) {
            Log::error('ReportController: Failed to decode temp file data');
            throw new \Exception('Failed to read processed data from file');
        }
        
        $processedData = collect($processedDataArray);
        
        Log::info('ReportController: Temp file read successfully', [
            'recordCount' => $processedData->count()
        ]);

        // Prepare variables for the Excel
        $startDateInput = $requestData['start'];
        $endDateInput = $requestData['end'];
        // Generate Excel content (single pass)
        Log::info('ReportController: Creating Excel file');
        $this->generateExcelContent($processedData, $startDateInput, $endDateInput, $report->id);

        // Save file
        $filename = 'inventory_report_' . $report->id . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = 'reports/' . $filename;
        
        // Set worksheet name to match filename (without extension)
        $this->spreadsheet->getActiveSheet()->setTitle('Inventory Report');
        
        Log::info('ReportController: Saving Excel file', ['filename' => $filename, 'filePath' => $filePath]);
        $reportsDir = storage_path('app/reports');
        if (!file_exists($reportsDir)) { mkdir($reportsDir, 0755, true); }
        (new Xlsx($this->spreadsheet))->save(storage_path('app/' . $filePath));

        // Update report info
        $report->update(['file_path' => $filePath, 'file_name' => $filename, 'processed_at' => now()]);

        // Cleanup temp data
        if (file_exists($tempFilePath)) { @unlink($tempFilePath); }
    }


    /**
     * Generate Excel content with PDF-matching design
     */
    private function generateExcelContent($processedData, $startDateInput, $endDateInput, $reportId)
    {
        // Create new Spreadsheet
        $this->spreadsheet = new Spreadsheet();
        $sheet = $this->spreadsheet->getActiveSheet();
        
        // Helper function to get property from array or object
        $getItemProperty = function($item, $property, $default = null) {
            return is_array($item) ? ($item[$property] ?? $default) : ($item->$property ?? $default);
        };
        
        // Helper function to format date
        $formatDate = function($timestamp) {
            if ($timestamp) {
                return \Carbon\Carbon::createFromTimestamp($timestamp)->format('m/d/Y H:i');
            }
            return 'N/A';
        };
        
        // Helper function to format source/destination
        $formatSourceDestination = function($item, $type) use ($getItemProperty) {
            $display = $getItemProperty($item, $type, '');
            $typeName = $getItemProperty($item, $type . '_type_name');
            $locationName = $getItemProperty($item, $type . '_location_name');
            
            if ($typeName && str_contains(strtolower($typeName), 'location') && $locationName) {
                $display = $locationName . ' (' . $typeName . ')';
            } elseif ($typeName && str_contains(strtolower($typeName), 'vendor')) {
                $vendorName = $getItemProperty($item, $type . '_vendor_person_name', '');
                $corporateName = $getItemProperty($item, $type . '_vendor_corporate_name', '');
                if ($vendorName && $corporateName) {
                    $display = $vendorName . ' - ' . $corporateName;
                } elseif ($vendorName) {
                    $display = $vendorName;
                } elseif ($corporateName) {
                    $display = $corporateName;
                } else {
                    $display = 'Vendor ID: ' . $getItemProperty($item, $type, '');
                }
                $display .= ' (' . $typeName . ')';
            } elseif ($typeName) {
                $display = $display . ' (' . $typeName . ')';
            }
            
            return $display;
        };
        
        $currentRow = 1;
        
        // Calculate site count
        $siteCount = 0;
        if ($processedData->count() > 0) {
            $uniqueSiteIds = $processedData->pluck('site_id')->unique()->filter()->count();
            $siteCount = $uniqueSiteIds;
        }

        // Construct the full header text with line breaks
        $headerText = "INVENTORY REPORT\n" .
                      "─────────────────────────────────────────────\n" .
                      "Generated on: " . date('m/d/Y H:i') . "\n" .
                      "Date Range: " . $startDateInput . " to " . $endDateInput . "\n" .
                      "Total Sites: " . $siteCount . "\n" .
                      "Total Records: " . $processedData->count();

        // === CONSOLIDATED REPORT HEADER SECTION ===
        $sheet->setCellValue('A' . $currentRow, $headerText);
        $sheet->mergeCells('A' . $currentRow . ':L' . ($currentRow + 5)); // Merge across A to L, and 6 rows down for the 6 lines of text (including HR)
        
        // Apply styles
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setWrapText(true); // Enable text wrapping for line breaks
        
        // Set row height to accommodate multiple lines
        $sheet->getRowDimension($currentRow)->setRowHeight(70);

        // Apply background color (lighter grey - same as generic/brand/batch lines)
        $sheet->getStyle('A' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0'); // Lighter grey background

        // Apply border (thick dark green)
        $sheet->getStyle('A' . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK)->setColor(new Color('FF006100')); // Dark green border
        
        $currentRow += 7; // Move currentRow past the merged block (6 lines of text + 1 empty row for spacing)

        
        if ($processedData->count() > 0) {
            // Group data by generic_name + brand_name + batch_no
            $grouped = [];
            foreach($processedData as $item) {
                $genericName = $getItemProperty($item, 'generic_name', 'Unknown');
                $brandName = $getItemProperty($item, 'brand_name', 'Unknown');
                $batchNo = $getItemProperty($item, 'batch_no', 'Unknown');
                
                $key = $genericName . '|' . $brandName . '|' . $batchNo;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'generic' => $genericName,
                        'brand' => $brandName,
                        'batch' => $batchNo,
                        'items' => [],
                        'final_org_balance' => 0,
                        'site_balances' => [],
                        'location_balances' => []
                    ];
                }
                $grouped[$key]['items'][] = $item;
                
                // Calculate final balances (use the last transaction's balance)
                $grouped[$key]['final_org_balance'] = $getItemProperty($item, 'org_balance', 0);
                
                // Collect site balances
                $siteName = $getItemProperty($item, 'site_name');
                if (!empty($siteName)) {
                    $grouped[$key]['site_balances'][$siteName] = $getItemProperty($item, 'site_balance', 0);
                }
                
                // Collect location balances
                $locationName = $getItemProperty($item, 'location_name');
                if (!empty($locationName)) {
                    $grouped[$key]['location_balances'][$locationName] = $getItemProperty($item, 'location_balance', 0);
                }
            }

            // === PRODUCT SECTIONS ===
            foreach($grouped as $key => $group) {
                $productGroupStartRow = $currentRow;
                
                // Product Identification Section - Horizontal layout with background (centered)
                $sheet->mergeCells('B' . $currentRow . ':D' . $currentRow);
                $sheet->setCellValue('B' . $currentRow, 'GENERIC: ' . $group['generic']);
                $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
                $sheet->setCellValue('E' . $currentRow, 'BRAND: ' . $group['brand']);
                $sheet->mergeCells('H' . $currentRow . ':J' . $currentRow);
                $sheet->setCellValue('H' . $currentRow, 'BATCH: ' . $group['batch']);
                
                // Apply different background color (lighter grey) and bold formatting to all columns
                $sheet->getStyle('A' . $currentRow . ':L' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0'); // Lighter grey background
                $sheet->getStyle('B' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('H' . $currentRow)->getFont()->setBold(true);
                
                // Center text within merged cells (both horizontally and vertically)
                $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                
                // Set compact row height for the first row (Generic/Brand/Batch)
                $sheet->getRowDimension($currentRow)->setRowHeight(25);
                $currentRow++;
                
                // Summary Balances Section - Horizontal layout with background and colors (centered)
                $sheet->mergeCells('B' . $currentRow . ':D' . $currentRow);
                $sheet->setCellValue('B' . $currentRow, 'ORG BALANCE: ' . $group['final_org_balance']);
                $sheet->getStyle('B' . $currentRow)->getFont()->setBold(true)->setColor(new Color('70AD47')); // Green
                $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                
                // Site-Location Balance Section
                $combinedBalances = [];
                foreach($group['items'] as $item) {
                    $siteName = $getItemProperty($item, 'site_name');
                    $locationName = $getItemProperty($item, 'location_name');
                    
                    if (!empty($siteName) && !empty($locationName)) {
                        $key = $siteName . ' - ' . $locationName;
                        $combinedBalances[$key] = $getItemProperty($item, 'site_balance', 0);
                    } elseif (!empty($siteName)) {
                        $combinedBalances[$siteName] = $getItemProperty($item, 'site_balance', 0);
                    } elseif (!empty($locationName)) {
                        $combinedBalances[$locationName] = $getItemProperty($item, 'location_balance', 0);
                    }
                }
                
                if (count($combinedBalances) > 0) {
                    $balanceStrings = [];
                    foreach($combinedBalances as $name => $balance) {
                        $balanceStrings[] = $name . ': ' . $balance;
                    }
                $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
                $sheet->setCellValue('E' . $currentRow, 'SITE - LOCATION BALANCE: ' . implode(', ', $balanceStrings));
                $sheet->getStyle('E' . $currentRow)->getFont()->setBold(true)->setColor(new Color('FF6B46C1')); // Purple instead of orange
                $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                }
                
                $sheet->mergeCells('H' . $currentRow . ':J' . $currentRow);
                $sheet->setCellValue('H' . $currentRow, 'TOTAL TRANSACTIONS: ' . count($group['items']));
                $sheet->getStyle('H' . $currentRow)->getFont()->setBold(true)->setColor(new Color('5B9BD5')); // Blue
                $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                
                // Apply different background color to the entire row across all columns
                $sheet->getStyle('A' . $currentRow . ':L' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0'); // Lighter grey background
                
                // Set compact row height for the second row (Summary Balances)
                $sheet->getRowDimension($currentRow)->setRowHeight(25);
                
                // Note: Border removed from product summary section for cleaner appearance
                
                $currentRow++; // Empty line before transaction table
                
                // === TRANSACTION TABLE HEADERS ===
                $headers = [
                    'Transaction Type',
                    'Ref Doc #',
                    'Date & Time',
                    'Site',
                    'Source',
                    'Destination',
                    'MR Code',
                    'Transaction Qty',
                    'Org Balance',
                    'Site Balance',
                    'Location Balance',
                    'Remarks'
                ];
                
                $headerRow = $currentRow;
                foreach($headers as $col => $header) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $currentRow;
                    $sheet->setCellValue($cell, $header);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('4472C4'); // Dark blue background
                    $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF'); // White text
                }
                // Note: Header border will be applied as part of the transaction table group border
                $currentRow++;
                
                // === TRANSACTION DATA ROWS ===
                $transactionTableStartRow = $currentRow; // Store start row for grouping border
                foreach($group['items'] as $item) {
                    $formattedDate = $formatDate($getItemProperty($item, 'timestamp'));
                    $sourceDisplay = $formatSourceDestination($item, 'source');
                    $destinationDisplay = $formatSourceDestination($item, 'destination');
                    
                    $rowData = [
                        $getItemProperty($item, 'transaction_type_name', 'N/A'),
                        $getItemProperty($item, 'ref_document_no', 'N/A'),
                        $formattedDate,
                        $getItemProperty($item, 'site_name', 'N/A'),
                        $sourceDisplay,
                        $destinationDisplay,
                        $getItemProperty($item, 'mr_code', 'N/A'),
                        $getItemProperty($item, 'accurate_transaction_qty', '0'),
                        $getItemProperty($item, 'org_balance', '0'),
                        $getItemProperty($item, 'site_balance', '0'),
                        $getItemProperty($item, 'location_balance', '0'),
                        $getItemProperty($item, 'remarks', 'N/A')
                    ];
                    
                    foreach($rowData as $col => $value) {
                        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $currentRow;
                        $sheet->setCellValue($cell, $value);
                        
                        // Apply special formatting for balance columns
                        if (in_array($col, [7, 8, 9, 10])) { // Transaction Qty, Org Balance, Site Balance, Location Balance
                            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID);
                            
                            // Different colors for different balance types
                            switch($col) {
                                case 7: // Transaction Qty
                                    $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('5B9BD5'); // Light blue
                                    break;
                                case 8: // Org Balance
                                    $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('70AD47'); // Light green
                                    break;
                                case 9: // Site Balance
                                    $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('FF6B46C1'); // Purple (changed from orange)
                                    break;
                                case 10: // Location Balance
                                    $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('A5A5A5'); // Light grey
                                    break;
                            }
                            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF'); // White text
                        } else {
                            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                    }
                    // Note: Individual row borders removed - will apply grouping border around entire transaction table
                    $currentRow++;
                }
                
                // Apply grouping border around the entire transaction table (headers + all data rows)
                $transactionTableEndRow = $currentRow - 1; // Last data row
                $sheet->getStyle('A' . $headerRow . ':L' . $transactionTableEndRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                $currentRow += 2; // Two empty lines between product groups for better spacing
            }
        } else {
            $sheet->setCellValue('A' . $currentRow, 'No Data Found');
        }
        
        $columnWidths = [
            'A' => 15,
            'B' => 9,
            'C' => 10,
            'D' => 15,
            'E' => 30,
            'F' => 30,
            'G' => 10,
            'H' => 15,
            'I' => 12,
            'J' => 12,
            'K' => 15,
            'L' => 15
        ];
        
        foreach($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    /**
     * Get status message
     */
    private function getStatusMessage($status, $progress, $emailSentAt = null)
    {
        switch ($status) {
            case ReportManagement::STATUS_PENDING:
                return 'Report request submitted. Waiting to be processed...';
            case ReportManagement::STATUS_PROCESSING:
                if ($progress >= 100) {
                    return 'Sending email...';
                }
                return "Processing report... {$progress}% complete";
            case ReportManagement::STATUS_COMPLETED:
                if ($emailSentAt) {
                    return 'Report completed successfully! Check your email for the report.';
                } else {
                    return 'Report ready! Sending email...';
                }
            case ReportManagement::STATUS_FAILED:
                return 'Report generation failed. Please try again.';
            default:
                return 'Unknown status';
        }
    }
}
