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
        $Sites = Site::where('status', 1)->select('id', 'name')->get();

        return view('dashboard.reports.inventory_report', compact('user','Categories','TransactionTypes','Sites'));
    }

    public function getInventoryReportData(Request $request)
    {
        // Get form data
        $startDateInput = $request->input('start');
        $endDateInput = $request->input('end');
        $sites = $request->input('ir_site', []); // Default to empty array if not provided
        
        // Parse date range from separate inputs
        $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
        $endDate = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();
        
        // Convert to timestamps
        $startTimestamp = $startDate->timestamp;
        $endTimestamp = $endDate->timestamp;
        
        // Build query with joins for inventory_balance, inventory_management, transaction_type, brand, generic, and source/destination types
        $query = DB::table('inventory_balance')
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
            // Convert site IDs to integers for proper database comparison
            $siteIds = array_map('intval', $sites);
            $query->whereIn('inventory_balance.site_id', $siteIds);
        }
        // If "0101" is selected or sites array is empty, don't add site_id condition (get all sites)
        
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
                'inventory_transaction_type.name as transaction_type_name',
                'inventory_brand.name as brand_name',
                'inventory_generic.name as generic_name',
                'source_type.name as source_type_name',
                'destination_type.name as destination_type_name',
                'source_location.name as source_location_name',
                'destination_location.name as destination_location_name'
            )
            ->orderBy('inventory_balance.timestamp', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reportData,
            'total_records' => $reportData->count(),
            'date_range' => $startDateInput . ' - ' . $endDateInput,
            'sites' => $sites
        ]);
    }

    public function downloadInventoryReportPDF(Request $request)
    {
        // Get form data
        $startDateInput = $request->input('start');
        $endDateInput = $request->input('end');
        $sites = $request->input('ir_site');
        
        // Parse date range from separate inputs
        $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
        $endDate = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();
        
        // Convert to timestamps
        $startTimestamp = $startDate->timestamp;
        $endTimestamp = $endDate->timestamp;
        
        // Build query with joins for inventory_balance, inventory_management, transaction_type, brand, generic, and source/destination types
        $query = DB::table('inventory_balance')
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
            // Convert site IDs to integers for proper database comparison
            $siteIds = array_map('intval', $sites);
            $query->whereIn('inventory_balance.site_id', $siteIds);
        }
        // If "0101" is selected or sites array is empty, don't add site_id condition (get all sites)
        
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
                'inventory_transaction_type.name as transaction_type_name',
                'inventory_brand.name as brand_name',
                'inventory_generic.name as generic_name',
                'source_type.name as source_type_name',
                'destination_type.name as destination_type_name',
                'source_location.name as source_location_name',
                'destination_location.name as destination_location_name'
            )
            ->orderBy('inventory_balance.timestamp', 'asc')
            ->get();

        // Generate PDF
        $pdf = new \Dompdf\Dompdf();
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $pdf->setOptions($options);

        $html = view('dashboard.reports.inventory_report_pdf', compact('reportData', 'startDateInput', 'endDateInput', 'sites'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $filename = 'inventory_report_' . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->stream($filename);
    }

   
}
