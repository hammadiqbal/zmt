<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

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

use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    private $assignedSites;
    
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
                return $next($request);
            } else {
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
        $Sites = Site::where('status', 1)->select('id', 'name')->get();

        return view('dashboard.reports.inventory_report', compact('user','Categories','TransactionTypes','Sites','Generics'));
    }

    public function getInventoryReportData(Request $request)
    {
        // Get form data
        $startDateInput = $request->input('start');
        $endDateInput = $request->input('end');
        $sites = $request->input('ir_site', []); // Default to empty array if not provided
        $transactionTypes = $request->input('ir_transactiontype', []); // Default to empty array if not provided
        $generics = $request->input('ir_generic', []); // Default to empty array if not provided
        
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
            // Convert comma-separated string to array and then to integers
            $siteIds = [];
            foreach ($sites as $site) {
                if (strpos($site, ',') !== false) {
                    // If it's a comma-separated string, explode it
                    $siteIds = array_merge($siteIds, array_map('intval', explode(',', $site)));
                } else {
                    // If it's a single value, add it directly
                    $siteIds[] = intval($site);
                }
            }
            
            if (!empty($siteIds)) {
                $query->whereIn('inventory_balance.site_id', $siteIds);
            }
        }
        // If "0101" is selected or sites array is empty, don't add site_id condition (get all sites)
        
        // Handle transaction type filtering
        if (!empty($transactionTypes) && !in_array('0101', $transactionTypes)) {
            // Convert comma-separated string to array and then to integers
            $transactionTypeIds = [];
            foreach ($transactionTypes as $transactionType) {
                if (strpos($transactionType, ',') !== false) {
                    // If it's a comma-separated string, explode it
                    $transactionTypeIds = array_merge($transactionTypeIds, array_map('intval', explode(',', $transactionType)));
                } else {
                    // If it's a single value, add it directly
                    $transactionTypeIds[] = intval($transactionType);
                }
            }
            
            if (!empty($transactionTypeIds)) {
                $query->whereIn('inventory_management.transaction_type_id', $transactionTypeIds);
            }
        }
        // If "0101" is selected or transaction types array is empty, don't add transaction_type_id condition (get all transaction types)
        
        // Handle generic filtering
        if (!empty($generics) && !in_array('0101', $generics)) {
            // Convert comma-separated string to array and then to integers
            $genericIds = [];
            foreach ($generics as $generic) {
                if (strpos($generic, ',') !== false) {
                    // If it's a comma-separated string, explode it
                    $genericIds = array_merge($genericIds, array_map('intval', explode(',', $generic)));
                } else {
                    // If it's a single value, add it directly
                    $genericIds[] = intval($generic);
                }
            }
            
            if (!empty($genericIds)) {
                $query->whereIn('inventory_balance.generic_id', $genericIds);
            }
        }
        // If "0101" is selected or generics array is empty, don't add generic_id condition (get all generics)
        
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
            // Get comma-separated arrays from inventory_management
            $genericIds = $item->management_generic_ids ? explode(',', $item->management_generic_ids) : [];
            $brandIds = $item->management_brand_ids ? explode(',', $item->management_brand_ids) : [];
            $batchNos = $item->management_batch_nos ? explode(',', $item->management_batch_nos) : [];
            $transactionQtys = $item->transaction_qty ? explode(',', $item->transaction_qty) : [];
            
            // Find the index that matches the inventory_balance item
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
            
            // Set the accurate transaction_qty
            if ($matchedIndex >= 0 && isset($transactionQtys[$matchedIndex])) {
                $item->accurate_transaction_qty = trim($transactionQtys[$matchedIndex]);
            } else {
                $item->accurate_transaction_qty = '0';
            }
            
            // Remove the comma-separated fields as they're no longer needed
            unset($item->management_generic_ids);
            unset($item->management_brand_ids);
            unset($item->management_batch_nos);
            
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $processedData,
            'total_records' => $processedData->count(),
            'date_range' => $startDateInput . ' - ' . $endDateInput,
            'sites' => $sites,
            'transaction_types' => $transactionTypes,
            'generics' => $generics
        ]);
    }

    public function downloadInventoryReportPDF(Request $request)
    {
        // Get form data
        $startDateInput = $request->input('start');
        $endDateInput = $request->input('end');
        $sites = $request->input('ir_site');
        $transactionTypes = $request->input('ir_transactiontype', []);
        $generics = $request->input('ir_generic', []);
        
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
            ->whereBetween('inventory_balance.timestamp', [$startTimestamp, $endTimestamp]);
        
        // Handle site filtering
        if (!empty($sites) && !in_array('0101', $sites)) {
            // Convert comma-separated string to array and then to integers
            $siteIds = [];
            foreach ($sites as $site) {
                if (strpos($site, ',') !== false) {
                    // If it's a comma-separated string, explode it
                    $siteIds = array_merge($siteIds, array_map('intval', explode(',', $site)));
                } else {
                    // If it's a single value, add it directly
                    $siteIds[] = intval($site);
                }
            }
            
            if (!empty($siteIds)) {
                $query->whereIn('inventory_balance.site_id', $siteIds);
            }
        }
        // If "0101" is selected or sites array is empty, don't add site_id condition (get all sites)
        
        // Handle transaction type filtering
        if (!empty($transactionTypes) && !in_array('0101', $transactionTypes)) {
            // Convert comma-separated string to array and then to integers
            $transactionTypeIds = [];
            foreach ($transactionTypes as $transactionType) {
                if (strpos($transactionType, ',') !== false) {
                    // If it's a comma-separated string, explode it
                    $transactionTypeIds = array_merge($transactionTypeIds, array_map('intval', explode(',', $transactionType)));
                } else {
                    // If it's a single value, add it directly
                    $transactionTypeIds[] = intval($transactionType);
                }
            }
            
            if (!empty($transactionTypeIds)) {
                $query->whereIn('inventory_management.transaction_type_id', $transactionTypeIds);
            }
        }
        // If "0101" is selected or transaction types array is empty, don't add transaction_type_id condition (get all transaction types)
        
        // Handle generic filtering
        if (!empty($generics) && !in_array('0101', $generics)) {
            // Convert comma-separated string to array and then to integers
            $genericIds = [];
            foreach ($generics as $generic) {
                if (strpos($generic, ',') !== false) {
                    // If it's a comma-separated string, explode it
                    $genericIds = array_merge($genericIds, array_map('intval', explode(',', $generic)));
                } else {
                    // If it's a single value, add it directly
                    $genericIds[] = intval($generic);
                }
            }
            
            if (!empty($genericIds)) {
                $query->whereIn('inventory_balance.generic_id', $genericIds);
            }
        }
        // If "0101" is selected or generics array is empty, don't add generic_id condition (get all generics)
        
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
                'inventory_balance.remarks',
                'inventory_balance.timestamp',
                'inventory_management.transaction_type_id',
                'inventory_management.ref_document_no',
                'inventory_management.source',
                'inventory_management.destination',
                'inventory_management.mr_code',
                'inventory_management.transaction_qty',
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

        // Process comma-separated values to get accurate transaction_qty for PDF
        $processedData = $reportData->map(function($item) {
            // Get comma-separated arrays from inventory_management
            $genericIds = $item->management_generic_ids ? explode(',', $item->management_generic_ids) : [];
            $brandIds = $item->management_brand_ids ? explode(',', $item->management_brand_ids) : [];
            $batchNos = $item->management_batch_nos ? explode(',', $item->management_batch_nos) : [];
            $transactionQtys = $item->transaction_qty ? explode(',', $item->transaction_qty) : [];
            
            // Find the index that matches the inventory_balance item
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
            
            // Set the accurate transaction_qty
            if ($matchedIndex >= 0 && isset($transactionQtys[$matchedIndex])) {
                $item->accurate_transaction_qty = trim($transactionQtys[$matchedIndex]);
            } else {
                $item->accurate_transaction_qty = '0';
            }
            
            // Remove the comma-separated fields as they're no longer needed
            unset($item->management_generic_ids);
            unset($item->management_brand_ids);
            unset($item->management_batch_nos);
            
            return $item;
        });

        // Generate PDF
        $pdf = new \Dompdf\Dompdf();
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $pdf->setOptions($options);

        $html = view('dashboard.reports.inventory_report_pdf', compact('processedData', 'startDateInput', 'endDateInput', 'sites', 'transactionTypes', 'generics'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $filename = 'inventory_report_' . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->stream($filename);
    }

   
}
