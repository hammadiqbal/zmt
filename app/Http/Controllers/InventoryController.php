<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Logs;
use Illuminate\Support\Collection;
use Illuminate\Support\FacadesDB;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\InventoryCategoryRequest;
use App\Http\Requests\InventorySubCategoryRequest;
use App\Http\Requests\InventoryTypeRequest;
use App\Http\Requests\InventoryBrandRequest;
use App\Http\Requests\InventoryGenericRequest;
use App\Http\Requests\InventoryTransactionTypeRequest;
use App\Http\Requests\MedicationRoutesRegistration;
use App\Http\Requests\MedicationFrequencyRequest;
// use App\Http\Requests\VendorRegistrationRequest;
use App\Http\Requests\ThirdPartyRegistrationRequest;
use App\Http\Requests\ConsumptionGroupRequest;
use App\Http\Requests\ConsumptionMethodRequest;
use App\Http\Requests\MaterialConsumptionRequisitionRequest;
use App\Http\Requests\PurchaseOrderRequest;
use App\Http\Requests\WorkOrderRequest;
// use App\Http\Requests\InventoryManagementRequest;
use App\Http\Requests\StockMonitoringRequest;
use App\Http\Requests\InventorySourceDestinationTypeRequest;
use App\Http\Requests\InventoryTransactionActivityRequest;
use App\Http\Requests\ExternalTransactionRequest;
use App\Http\Requests\IssueDispenseRequest;
use App\Models\InventoryCategory;
use App\Models\Site;
use App\Models\InventorySubCategory;
use App\Models\InventoryType;
use App\Models\Organization;
use App\Models\InventoryBrand;
use App\Models\InventoryGeneric;
use App\Models\InventoryTransactionType;
use App\Models\PatientRegistration;
use App\Models\RequisitionForMedicationConsumption;
// use App\Models\VendorRegistration;
use App\Models\ThirdPartyRegistration;
use App\Models\ConsumptionGroup;
use App\Models\ConsumptionMethod;
use App\Models\StockMonitoring;
use App\Models\InventorySourceDestinationType;
use App\Models\InventoryTransactionActivity;
use App\Models\MedicationRoutes;
use App\Models\MedicationFrequency;
use App\Models\MaterialConsumptionRequisition;
use App\Models\PurchaseOrder;
use App\Models\WorkOrder;
use App\Models\ServiceLocation;
use App\Models\InventoryManagement;
use App\Models\InventoryBalance;
use App\Mail\ThirdPartyRegistrationMail;
use App\Models\PrefixSetup;
use App\Models\Users;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // if (Auth::check() && Auth::user()->role_id == 1) {
            $this->currentDatetime = Carbon::now('Asia/Karachi')->timestamp;
            $this->sessionUser = session('user');
            $this->roles = session('role');
            $this->rights = session('rights');
            if (Auth::check()) {
                return $next($request);
            } else {
                return redirect('/');
            }
        });
    }

    public function InventoryCategory()
    {
        $colName = 'item_category';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        $ConsumptionGroups = ConsumptionGroup::where('status', 1)->get();
        $ConsumptionMethods = ConsumptionMethod::where('status', 1)->get();
        return view('dashboard.inventory-category', compact('user','Organizations','ConsumptionGroups','ConsumptionMethods'));
    }

    public function AddInventoryCategory(InventoryCategoryRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->item_category)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryCategory = trim($request->input('inv_cat'));
        $Org = ($request->input('ic_org'));
        $ConsumptionGroup = trim($request->input('ic_cg'));
        $ConsumptionMethod = trim($request->input('ic_cm'));
        $Edt = $request->input('invcat_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; 
        } else {
            $status = 0; 
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InventoryCategoryExists = InventoryCategory::where('name', $InventoryCategory)
        ->exists();
        if ($InventoryCategoryExists) {
            return response()->json(['info' => 'Inventory Category already exists.']);
        }
        else
        {
            $InventoryCategories = new InventoryCategory();
            $InventoryCategories->name = $InventoryCategory;
            $InventoryCategories->org_id = $Org;
            $InventoryCategories->consumption_group = $ConsumptionGroup;
            $InventoryCategories->consumption_method = $ConsumptionMethod;
            $InventoryCategories->status = $status;
            $InventoryCategories->user_id = $sessionId;
            $InventoryCategories->last_updated = $last_updated;
            $InventoryCategories->timestamp = $timestamp;
            $InventoryCategories->effective_timestamp = $Edt;
            $InventoryCategories->save();

            if (empty($InventoryCategories->id)) {
                return response()->json(['error' => 'Failed to create Inventory Category.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$InventoryCategory}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventoryCategories->logid = $logs->id;
            $InventoryCategories->save();
            return response()->json(['success' => 'Inventory Category created successfully']);
        }

    }

    public function GetInventoryCategoryData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->item_category)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryCategories = InventoryCategory::select('inventory_category.*',
        'organization.organization as orgName','consumption_group.description as consumptionGroup',
        'consumption_method.description as consumptionMethod')
        ->join('organization', 'organization.id', '=', 'inventory_category.org_id')
        ->join('consumption_group', 'consumption_group.id', '=', 'inventory_category.consumption_group')
        ->join('consumption_method', 'consumption_method.id', '=', 'inventory_category.consumption_method')
        ->orderBy('inventory_category.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryCategories->where('inventory_category.org_id', '=', $sessionOrg);
        }
        $InventoryCategories = $InventoryCategories;
        // ->get()
        // return DataTables::of($InventoryCategories)
        return DataTables::eloquent($InventoryCategories)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_category.name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('consumption_group.description', 'like', "%{$search}%")
                        ->orWhere('consumption_method.description', 'like', "%{$search}%")
                        ->orWhere('inventory_category.id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventoryCategory) {
                return $InventoryCategory->id;  // Raw ID value
            })
            ->editColumn('id', function ($InventoryCategory) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $InventoryCategoryName = $InventoryCategory->name;
                $firstFourLetters = substr(str_replace(' ', '', strtoupper($InventoryCategoryName)), 0, 3);
                $idStr = str_pad($InventoryCategory->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($InventoryCategory->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventoryCategory->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventoryCategory->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventoryCategory->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'INV';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventoryCategoryName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($InventoryCategory->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-1">'.
                    ucwords($InventoryCategoryName).'<br>'
                    . '<span class="mt-1 label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('consumption_group', function ($InventoryCategory) {
                return ucwords($InventoryCategory->consumptionGroup);
            })
            ->editColumn('consumption_method', function ($InventoryCategory) {
                return ucwords($InventoryCategory->consumptionMethod);
            })
            ->addColumn('action', function ($InventoryCategory) {
                    $InventoryCategoryId = $InventoryCategory->id;
                    $logId = $InventoryCategory->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->item_category)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-inventorycategory" data-inventorycategory-id="'.$InventoryCategoryId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventoryCategory->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventoryCategory) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->item_category)[3];
                return $updateStatus == 1 ? ($InventoryCategory->status ? '<span class="label label-success inventorycategory_status cursor-pointer" data-id="'.$InventoryCategory->id.'" data-status="'.$InventoryCategory->status.'">Active</span>' : '<span class="label label-danger inventorycategory_status cursor-pointer" data-id="'.$InventoryCategory->id.'" data-status="'.$InventoryCategory->status.'">Inactive</span>') : ($InventoryCategory->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status', 'consumption_group', 'consumption_method','id'])
            ->make(true);
    }

    public function UpdateInventoryCategoryStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->item_category)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryCategoryID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventoryCategories = InventoryCategory::find($InventoryCategoryID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventoryCategories->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventoryCategories->status = $UpdateStatus;
        $InventoryCategories->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryCategoryLog = InventoryCategory::where('id', $InventoryCategoryID)->first();
        $logIds = $InventoryCategoryLog->logid ? explode(',', $InventoryCategoryLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryCategoryLog->logid = implode(',', $logIds);
        $InventoryCategoryLog->save();

        $InventoryCategories->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventoryCategoryModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_category)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryCategories = InventoryCategory::select('inventory_category.*',
        'organization.organization as orgName','consumption_group.description as consumptionGroup',
        'consumption_method.description as consumptionMethod')
        ->join('organization', 'organization.id', '=', 'inventory_category.org_id')
        ->join('consumption_group', 'consumption_group.id', '=', 'inventory_category.consumption_group')
        ->join('consumption_method', 'consumption_method.id', '=', 'inventory_category.consumption_method')
        ->where('inventory_category.id', '=', $id)
        ->first();

        $InventoryCategoryName = ucwords($InventoryCategories->name);
        $effective_timestamp = $InventoryCategories->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $InventoryCategoryName,
            'orgId' => $InventoryCategories->org_id,
            'orgName' => $InventoryCategories->orgName,
            'consumptionGroupId' => $InventoryCategories->consumption_group,
            'consumptionGroupName' => $InventoryCategories->consumptionGroup,
            'consumptionMethodId' => $InventoryCategories->consumption_method,
            'consumptionMethodName' => $InventoryCategories->consumptionMethod,
            'UsageType' => $InventoryCategories->usage_type,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateInventoryCategory(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_category)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryCategories = InventoryCategory::findOrFail($id);
        $InventoryCategories->name = $request->input('u_invcat');
        $orgID = $request->input('u_ic_org');
        if (isset($orgID)) {
            $InventoryCategories->org_id = $orgID;
        }  
        $InventoryCategories->consumption_group = $request->input('u_ic_cg');
        $InventoryCategories->consumption_method = $request->input('u_ic_cm');
        $effective_date = $request->input('u_ic_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $InventoryCategories->effective_timestamp = $effective_date;
        $InventoryCategories->last_updated = $this->currentDatetime;
        $InventoryCategories->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventoryCategories->save();

        if (empty($InventoryCategories->id)) {
            return response()->json(['error' => 'Failed to update Inventory Category. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryCategoryLog = InventoryCategory::where('id', $InventoryCategories->id)->first();
        $logIds = $InventoryCategoryLog->logid ? explode(',', $InventoryCategoryLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryCategoryLog->logid = implode(',', $logIds);
        $InventoryCategoryLog->save();
        return response()->json(['success' => 'Inventory Category updated successfully']);
    }

    public function InventorySubCategory()
    {
        $colName = 'item_sub_category';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.inventory-subcategory', compact('user'));
    }

    public function AddInventorySubCategory(InventorySubCategoryRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->item_sub_category)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventorySubCategory = trim($request->input('isc_description'));
        $catId = trim($request->input('isc_catid'));
        $orgId = trim($request->input('isc_org'));
        $Edt = $request->input('isc_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive

        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InventorySubCategoryExists = InventorySubCategory::where('name', $InventorySubCategory)
        ->where('cat_id', $catId)
        ->exists();

        if ($InventorySubCategoryExists) {
            return response()->json(['info' => 'Inventory Sub Category already exists.']);
        }
        else
        {
            $InventorySubCategories = new InventorySubCategory();
            $InventorySubCategories->name = $InventorySubCategory;
            $InventorySubCategories->cat_id = $catId;
            $InventorySubCategories->org_id = $orgId;
            $InventorySubCategories->status = $status;
            $InventorySubCategories->user_id = $sessionId;
            $InventorySubCategories->last_updated = $last_updated;
            $InventorySubCategories->timestamp = $timestamp;
            $InventorySubCategories->effective_timestamp = $Edt;
            $InventorySubCategories->save();

            if (empty($InventorySubCategories->id)) {
                return response()->json(['error' => 'Failed to create Inventory Sub Category.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$InventorySubCategory}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventorySubCategories->logid = $logs->id;
            $InventorySubCategories->save();
            return response()->json(['success' => 'Inventory Sub Category created successfully']);
        }

    }

    public function GetInventorySubCategoryData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->item_sub_category)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventorySubCategories = InventorySubCategory::select('inventory_subcategory.*',
        'organization.organization as orgName',
        'inventory_category.name as catName')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_subcategory.cat_id')
        ->leftJoin('organization', 'organization.id', '=', 'inventory_subcategory.org_id')
        ->orderBy('inventory_subcategory.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventorySubCategories->where('inventory_subcategory.org_id', '=', $sessionOrg);
        }
        $InventorySubCategories = $InventorySubCategories;
        // ->get()
        // return DataTables::of($InventorySubCategories)
        return DataTables::eloquent($InventorySubCategories)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_subcategory.name', 'like', "%{$search}%")
                            ->orWhere('inventory_category.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('inventory_subcategory.id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventorySubCategory) {
                return $InventorySubCategory->id;  // Raw ID value
            })
            ->editColumn('id', function ($InventorySubCategory) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $InventorySubCategoryName = $InventorySubCategory->name;
                $firstFourLetters = substr(str_replace(' ', '', strtoupper($InventorySubCategoryName)), 0, 3);
                $idStr = str_pad($InventorySubCategory->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($InventorySubCategory->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventorySubCategory->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventorySubCategory->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventorySubCategory->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'INV';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventorySubCategoryName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($InventorySubCategory->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($InventorySubCategory) {
                    $InventorySubCategoryId = $InventorySubCategory->id;
                    $logId = $InventorySubCategory->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->item_sub_category)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-invsubcategory" data-invsubcategory-id="'.$InventorySubCategoryId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventorySubCategory->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventorySubCategory) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->item_sub_category)[3];
                return $updateStatus == 1 ? ($InventorySubCategory->status ? '<span class="label label-success invsubcategory_status cursor-pointer" data-id="'.$InventorySubCategory->id.'" data-status="'.$InventorySubCategory->status.'">Active</span>' : '<span class="label label-danger invsubcategory_status cursor-pointer" data-id="'.$InventorySubCategory->id.'" data-status="'.$InventorySubCategory->status.'">Inactive</span>') : ($InventorySubCategory->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }
    public function UpdateInventorySubCategoryStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->item_sub_category)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventorySubCategoryID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventorySubCategories = InventorySubCategory::find($InventorySubCategoryID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventorySubCategories->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventorySubCategories->status = $UpdateStatus;
        $InventorySubCategories->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventorySubCategoryLog = InventorySubCategory::where('id', $InventorySubCategoryID)->first();
        $logIds = $InventorySubCategoryLog->logid ? explode(',', $InventorySubCategoryLog->logid) : [];
        $logIds[] = $logs->id;
        $InventorySubCategoryLog->logid = implode(',', $logIds);
        $InventorySubCategoryLog->save();

        $InventorySubCategories->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventorySubCategoryModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_sub_category)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        
        $InventorySubCategories = InventorySubCategory::select('inventory_subcategory.*',
        'inventory_category.name as catName','organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'inventory_subcategory.org_id')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_subcategory.cat_id')
        ->where('inventory_subcategory.id', '=', $id)
        ->first();
        $InventorySubCategoryName = ucwords($InventorySubCategories->name);
        $effective_timestamp = $InventorySubCategories->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $InventorySubCategoryName,
            'catId' => $InventorySubCategories->cat_id,
            'orgName' => ucwords($InventorySubCategories->orgName),
            'orgId' => $InventorySubCategories->org_id,
            'catName' => ucwords($InventorySubCategories->catName),
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateInventorySubCategory(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_sub_category)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventorySubCategories = InventorySubCategory::findOrFail($id);
        $InventorySubCategories->name = $request->input('u_isc_description');
        $InventorySubCategories->cat_id = $request->input('u_isc_catid');
        $orgID = $request->input('u_isc_org');
        if (isset($orgID)) {
            $InventorySubCategories->org_id = $orgID;
        }
        $effective_date = $request->input('u_isc_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $InventorySubCategories->effective_timestamp = $effective_date;
        $InventorySubCategories->last_updated = $this->currentDatetime;
        $InventorySubCategories->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventorySubCategories->save();

        if (empty($InventorySubCategories->id)) {
            return response()->json(['error' => 'Failed to update Inventory Sub Category. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventorySubCategoryLog = InventorySubCategory::where('id', $InventorySubCategories->id)->first();
        $logIds = $InventorySubCategoryLog->logid ? explode(',', $InventorySubCategoryLog->logid) : [];
        $logIds[] = $logs->id;
        $InventorySubCategoryLog->logid = implode(',', $logIds);
        $InventorySubCategoryLog->save();
        return response()->json(['success' => 'Inventory Sub Category updated successfully']);
    }

    public function GetInventoryCategory(Request $request)
    {
        $Categories = InventoryCategory::where('status', 1);
        if ($request->has('org_id') && $request->input('org_id') != 'null') {
            $orgId = $request->input('org_id');
            $Categories->where('org_id', $orgId);
        }
        $Categories = $Categories->get();
        return response()->json($Categories);
    }

    public function GetInventoryCategoryConsumption(Request $request)
    {
        $id = $request->input('cat_id');
        $InventoryCategories = InventoryCategory::select('inventory_category.consumption_group',
            'consumption_group.description as consumptionGroup')
            ->join('consumption_group', 'consumption_group.id', '=', 'inventory_category.consumption_group')
            ->where('inventory_category.id', '=', $id)
            ->first();
            
        $isConsumable = false;
        if ($InventoryCategories && preg_match('/\bconsumable\b/i', $InventoryCategories->consumptionGroup)) {
            $isConsumable = true;
        }
        if ($InventoryCategories && preg_match('/consumable/i', $InventoryCategories->consumptionGroup)) {
            $isConsumable = true;
        }
        return response()->json($isConsumable);
    }


    public function GetSelectedInventorySubCategory(Request $request)
    {
        if ($request->has('catId'))
        {
            $catId = $request->input('catId');
            $SubCategories = InventorySubCategory::where('status', 1)
                            ->where('cat_id', $catId)
                            ->get();
        }
        return response()->json($SubCategories);
    }

    public function InventoryType()
    {
        $colName = 'item_type';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Categories = InventoryCategory::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();

        return view('dashboard.inventory-type', compact('user','Categories','Organizations'));
    }

    public function AddInventoryType(InventoryTypeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->item_type)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryType = trim($request->input('it_description'));
        $cat = $request->input('it_cat');
        $subCat = $request->input('it_subcat');
        $org = $request->input('it_org');
        $Edt = $request->input('it_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive

        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InventoryTypeExists = InventoryType::where('name', $InventoryType)
        ->where('cat_id', $cat)
        ->where('sub_catid', $subCat)
        ->where('org_id', $org)
        ->exists();

        if ($InventoryTypeExists) {
            return response()->json(['info' => 'Inventory Type already exists.']);
        }
        else
        {
            $InventoryTypes = new InventoryType();
            $InventoryTypes->name = $InventoryType;
            $InventoryTypes->cat_id = $cat;
            $InventoryTypes->sub_catid = $subCat;
            $InventoryTypes->org_id = $org;
            $InventoryTypes->status = $status;
            $InventoryTypes->user_id = $sessionId;
            $InventoryTypes->last_updated = $last_updated;
            $InventoryTypes->timestamp = $timestamp;
            $InventoryTypes->effective_timestamp = $Edt;
            $InventoryTypes->save();

            if (empty($InventoryTypes->id)) {
                return response()->json(['error' => 'Failed to create Inventory Type.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$InventoryType}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventoryTypes->logid = $logs->id;
            $InventoryTypes->save();
            return response()->json(['success' => 'Inventory Type created successfully']);
        }

    }

    public function GetInventoryTypeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->item_type)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTypes = InventoryType::select('inventory_type.*',
        'inventory_category.name as catName','inventory_subcategory.name as subCatName',
        'organization.organization as orgName')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_type.cat_id')
        ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_type.sub_catid')
        ->join('organization', 'organization.id', '=', 'inventory_type.org_id')
        ->orderBy('inventory_type.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryTypes->where('inventory_type.org_id', '=', $sessionOrg);
        }
        $InventoryTypes = $InventoryTypes;
        // ->get()
        // return DataTables::of($InventoryTypes)
        return DataTables::eloquent($InventoryTypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_type.name', 'like', "%{$search}%")
                            ->orWhere('inventory_category.name', 'like', "%{$search}%")
                            ->orWhere('inventory_subcategory.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('inventory_type.id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventoryType) {
                return $InventoryType->id;  // Raw ID value
            })
            ->editColumn('id', function ($InventoryType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $InventoryTypeName = $InventoryType->name;
                $firstFourLetters = substr(str_replace(' ', '', strtoupper($InventoryTypeName)), 0, 3);
                $idStr = str_pad($InventoryType->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($InventoryType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventoryType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventoryType->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventoryType->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;
                        
                $ModuleCode = 'INV';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventoryTypeName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($InventoryType->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($InventoryType) {
                    $InventoryTypeId = $InventoryType->id;
                    $logId = $InventoryType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->item_type)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-invtype" data-invtype-id="'.$InventoryTypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventoryType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventoryType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->item_type)[3];
                return $updateStatus == 1 ? ($InventoryType->status ? '<span class="label label-success invtype_status cursor-pointer" data-id="'.$InventoryType->id.'" data-status="'.$InventoryType->status.'">Active</span>' : '<span class="label label-danger invtype_status cursor-pointer" data-id="'.$InventoryType->id.'" data-status="'.$InventoryType->status.'">Inactive</span>') : ($InventoryType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateInventoryTypeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->item_type)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTypeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventoryTypes = InventoryType::find($InventoryTypeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventoryTypes->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventoryTypes->status = $UpdateStatus;
        $InventoryTypes->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryTypeLog = InventoryType::where('id', $InventoryTypeID)->first();
        $logIds = $InventoryTypeLog->logid ? explode(',', $InventoryTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryTypeLog->logid = implode(',', $logIds);
        $InventoryTypeLog->save();

        $InventoryTypes->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventoryTypeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_type)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTypes = InventoryType::select('inventory_type.*',
        'inventory_category.name as catName','inventory_subcategory.name as subCatName',
        'organization.organization as orgName')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_type.cat_id')
        ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_type.sub_catid')
        ->join('organization', 'organization.id', '=', 'inventory_type.org_id')
        ->where('inventory_type.id', '=', $id)
        ->first();
        $InventoryType = ucwords($InventoryTypes->name);
        $effective_timestamp = $InventoryTypes->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $InventoryType,
            'catId' => $InventoryTypes->cat_id,
            'catName' => ucwords($InventoryTypes->catName),
            'subcatId' => $InventoryTypes->sub_catid,
            'subcatName' => ucwords($InventoryTypes->subCatName),
            'orgId' => $InventoryTypes->org_id,
            'orgName' => ucwords($InventoryTypes->orgName),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateInventoryType(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_type)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTypes = InventoryType::findOrFail($id);
        $InventoryTypes->name = $request->input('u_it_description');
        $InventoryTypes->cat_id = $request->input('u_it_catid');
        $InventoryTypes->cat_id = $request->input('u_it_subcat');
        $orgID = $request->input('u_it_org');
        if (isset($orgID)) {
            $InventoryTypes->org_id = $orgID;
        }
        $effective_date = $request->input('u_it_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $InventoryTypes->effective_timestamp = $effective_date;
        $InventoryTypes->last_updated = $this->currentDatetime;
        $InventoryTypes->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventoryTypes->save();

        if (empty($InventoryTypes->id)) {
            return response()->json(['error' => 'Failed to update Inventory Type. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryTypeLog = InventoryType::where('id', $InventoryTypes->id)->first();
        $logIds = $InventoryTypeLog->logid ? explode(',', $InventoryTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryTypeLog->logid = implode(',', $logIds);
        $InventoryTypeLog->save();
        return response()->json(['success' => 'Inventory Type updated successfully']);
    }

    public function InventoryGeneric()
    {
        $colName = 'item_generic_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Categories = InventoryCategory::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        $InventoryTypes = InventoryType::where('status', 1)->get();

        return view('dashboard.inventory-generic', compact('user','Categories','Organizations','InventoryTypes'));
    }

    public function AddInventoryGeneric(InventoryGenericRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->item_generic_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryGeneric = trim($request->input('ig_description'));
        $cat = $request->input('ig_cat');
        $subCat = $request->input('ig_subcat');
        $type = $request->input('ig_type');
        $org = $request->input('ig_org');
        $patientMandatory = $request->input('ig_patientmandatory');
        $Edt = $request->input('ig_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive

        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InventoryGenericExists = InventoryGeneric::where('name', $InventoryGeneric)
        ->where('cat_id', $cat)
        ->where('sub_catid', $subCat)
        ->where('type_id', $type)
        ->where('org_id', $org)
        ->where('patient_mandatory', $patientMandatory)
        ->exists();

        if ($InventoryGenericExists) {
            return response()->json(['info' => 'Inventory Generic already exists.']);
        }
        else
        {
            $InventoryGenerics = new InventoryGeneric();
            $InventoryGenerics->name = $InventoryGeneric;
            $InventoryGenerics->cat_id = $cat;
            $InventoryGenerics->sub_catid = $subCat;
            $InventoryGenerics->type_id = $type;
            $InventoryGenerics->org_id = $org;
            $InventoryGenerics->patient_mandatory = $patientMandatory;
            $InventoryGenerics->status = $status;
            $InventoryGenerics->user_id = $sessionId;
            $InventoryGenerics->last_updated = $last_updated;
            $InventoryGenerics->timestamp = $timestamp;
            $InventoryGenerics->effective_timestamp = $Edt;
            $InventoryGenerics->save();

            if (empty($InventoryGenerics->id)) {
                return response()->json(['error' => 'Failed to create Inventory Generic.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$InventoryGeneric}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventoryGenerics->logid = $logs->id;
            $InventoryGenerics->save();
            return response()->json(['success' => 'Inventory Generic created successfully']);
        }

    }

    public function GetInventoryGenericData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->item_generic_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryGenerics = InventoryGeneric::select('inventory_generic.*',
        'inventory_category.name as catName','inventory_subcategory.name as subCatName',
        'organization.organization as orgName','inventory_type.name as typeName')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_generic.cat_id')
        ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_generic.sub_catid')
        ->join('inventory_type', 'inventory_type.id', '=', 'inventory_generic.type_id')
        ->join('organization', 'organization.id', '=', 'inventory_generic.org_id')
        ->orderBy('inventory_type.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryGenerics->where('inventory_generic.org_id', '=', $sessionOrg);
        }
        $InventoryGenerics = $InventoryGenerics;
        // ->get()
        // return DataTables::of($InventoryGenerics)
        return DataTables::eloquent($InventoryGenerics)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_generic.name', 'like', "%{$search}%")
                            ->orWhere('inventory_category.name', 'like', "%{$search}%")
                            ->orWhere('inventory_subcategory.name', 'like', "%{$search}%")
                            ->orWhere('inventory_type.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('inventory_generic.id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventoryGeneric) {
                return $InventoryGeneric->id;  
            })
            ->editColumn('id', function ($InventoryGeneric) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $InventoryGenericName = $InventoryGeneric->name;
                // $firstFourLetters = substr(str_replace(' ', '', strtoupper($InventoryGenericName)), 0, 3);
                $idStr = str_pad($InventoryGeneric->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($InventoryGeneric->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventoryGeneric->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventoryGeneric->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventoryGeneric->user_id);
                $patientMandatory = $InventoryGeneric->patient_mandatory;
                $patientMandatory = ['y' => 'Yes','n' => 'No'][$patientMandatory] ?? $patientMandatory;
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'INV';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventoryGenericName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($InventoryGeneric->orgName);
                }
                $patientMandatory ='<hr class="mt-1 mb-1"><b>Patient Mandatory Status:</b> '.ucwords($patientMandatory);
                return $Code.$orgName.$patientMandatory
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($InventoryGeneric) {
                    $InventoryGenericId = $InventoryGeneric->id;
                    $logId = $InventoryGeneric->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->item_generic_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-invgeneric" data-invgeneric-id="'.$InventoryGenericId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventoryGeneric->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventoryGeneric) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->item_generic_setup)[3];
                return $updateStatus == 1 ? ($InventoryGeneric->status ? '<span class="label label-success invgeneric_status cursor-pointer" data-id="'.$InventoryGeneric->id.'" data-status="'.$InventoryGeneric->status.'">Active</span>' : '<span class="label label-danger invgeneric_status cursor-pointer" data-id="'.$InventoryGeneric->id.'" data-status="'.$InventoryGeneric->status.'">Inactive</span>') : ($InventoryGeneric->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','patientMandatory',
            'id'])
            ->make(true);
    }

    public function UpdateInventoryGenericStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->item_generic_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryGenericID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventoryGenerics = InventoryGeneric::find($InventoryGenericID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventoryGenerics->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventoryGenerics->status = $UpdateStatus;
        $InventoryGenerics->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryGenericLog = InventoryType::where('id', $InventoryGenericID)->first();
        $logIds = $InventoryGenericLog->logid ? explode(',', $InventoryGenericLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryGenericLog->logid = implode(',', $logIds);
        $InventoryGenericLog->save();

        $InventoryGenerics->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventoryGenericModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_generic_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryGenerics = InventoryGeneric::select('inventory_generic.*',
        'inventory_category.name as catName','inventory_subcategory.name as subCatName',
        'organization.organization as orgName','inventory_type.name as typeName')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_generic.cat_id')
        ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_generic.sub_catid')
        ->join('inventory_type', 'inventory_type.id', '=', 'inventory_generic.type_id')
        ->join('organization', 'organization.id', '=', 'inventory_generic.org_id')
        ->where('inventory_generic.id', '=', $id)
        ->first();

        $InventoryGeneric = ucwords($InventoryGenerics->name);
        $effective_timestamp = $InventoryGenerics->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $InventoryGeneric,
            'catId' => $InventoryGenerics->cat_id,
            'catName' => ucwords($InventoryGenerics->catName),
            'subcatId' => $InventoryGenerics->sub_catid,
            'subcatName' => ucwords($InventoryGenerics->subCatName),
            'orgId' => $InventoryGenerics->org_id,
            'orgName' => ucwords($InventoryGenerics->orgName),
            'typeId' => $InventoryGenerics->type_id,
            'typeName' => ucwords($InventoryGenerics->typeName),
            'patientMandatory' => $InventoryGenerics->patient_mandatory,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateInventoryGeneric(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_generic_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryGenerics = InventoryGeneric::findOrFail($id);

        $InventoryGenerics->name = $request->input('u_ig_description');
        $InventoryGenerics->cat_id = $request->input('u_ig_cat');
        $InventoryGenerics->sub_catid = $request->input('u_ig_subcat');
        $orgID = $request->input('u_ig_org');
        if (isset($orgID)) {
            $InventoryGenerics->org_id = $orgID;
        }
        $InventoryGenerics->type_id = $request->input('u_ig_type');
        $InventoryGenerics->patient_mandatory = $request->input('u_ig_patientmandatory');
        $effective_date = $request->input('u_ig_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $InventoryGenerics->effective_timestamp = $effective_date;
        $InventoryGenerics->last_updated = $this->currentDatetime;
        $InventoryGenerics->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventoryGenerics->save();

        if (empty($InventoryGenerics->id)) {
            return response()->json(['error' => 'Failed to update Inventory Generic. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryGenericLog = InventoryGeneric::where('id', $InventoryGenerics->id)->first();
        $logIds = $InventoryGenericLog->logid ? explode(',', $InventoryGenericLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryGenericLog->logid = implode(',', $logIds);
        $InventoryGenericLog->save();
        return response()->json(['success' => 'Inventory Generic updated successfully']);
    }

    public function GetSelectedInventoryType(Request $request)
    {
        $catId = $request->input('catId');
        $subcatId = $request->input('subcatId');
        $orgId = $request->input('orgId');

        $InventoryTypes = InventoryType::select('id', 'name')
            ->where('cat_id', $catId)
            ->where('sub_catid', $subcatId)
            ->where('org_id', $orgId)
            ->get();

        return response()->json($InventoryTypes);
    }

    public function GetSelectedInventoryGeneric(Request $request)
    {
        $typeId = $request->input('typeId');
        $InventoryGenerics = InventoryGeneric::select('id', 'name')
            ->where('type_id', $typeId)
            ->where('status', 1)
            ->get();
        return response()->json($InventoryGenerics);
    }
    public function GetInventoryGenerics(Request $request)
    {
        // $InventoryGenerics = InventoryGeneric::select('id', 'name')
        //     ->where('status', 1)
        //     ->get();

        $condition = $request->input('condition');
        $InventoryGenerics = InventoryGeneric::select('inventory_generic.id', 'inventory_generic.name')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_generic.cat_id')
        ->where('inventory_generic.status', 1);
            
        if($condition == 'material'){
            $InventoryGenerics->where('inventory_category.name', 'not like', 'Medicine%');
        }
        else if($condition == 'medication'){
            $InventoryGenerics->where('inventory_category.name', 'like', 'Medicine%');
        }
        $InventoryGenerics = $InventoryGenerics->get();
        return response()->json($InventoryGenerics);
    }

    public function FetchMedicationRoutes(Request $request)
    {
        $Routes = MedicationRoutes::select('id', 'name')
            ->where('status', 1)
            ->get();
        return response()->json($Routes);
    }

    public function FetchMedicationFrequency(Request $request)
    {
        $Routes = MedicationFrequency::select('id', 'name')
            ->where('status', 1)
            ->get();
        return response()->json($Routes);
    }

    public function InventoryBrand()
    {
        $colName = 'item_brand_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Categories = InventoryCategory::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        $InventoryTypes = InventoryType::where('status', 1)->get();

        return view('dashboard.inventory-brand', compact('user','Categories','Organizations','InventoryTypes'));
    }


    public function AddInventoryBrand(InventoryBrandRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->item_brand_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryBrand = trim($request->input('ib_description'));
        $cat = $request->input('ib_cat');
        $subCat = $request->input('ib_subcat');
        $type = $request->input('ib_type');
        $generic = $request->input('ib_generic');
        $org = $request->input('ib_org');
        $Edt = $request->input('ib_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive

        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InventoryBrandExists = InventoryBrand::where('name', $InventoryBrand)
        ->where('cat_id', $cat)
        ->where('sub_catid', $subCat)
        ->where('type_id', $type)
        ->where('generic_id', $generic)
        ->where('org_id', $org)
        ->exists();

        if ($InventoryBrandExists) {
            return response()->json(['info' => 'Inventory Brand already exists.']);
        }
        else
        {
            $InventoryBrands = new InventoryBrand();
            $InventoryBrands->name = $InventoryBrand;
            $InventoryBrands->cat_id = $cat;
            $InventoryBrands->sub_catid = $subCat;
            $InventoryBrands->type_id = $type;
            $InventoryBrands->generic_id = $generic;
            $InventoryBrands->org_id = $org;
            $InventoryBrands->status = $status;
            $InventoryBrands->user_id = $sessionId;
            $InventoryBrands->last_updated = $last_updated;
            $InventoryBrands->timestamp = $timestamp;
            $InventoryBrands->effective_timestamp = $Edt;
            $InventoryBrands->save();

            if (empty($InventoryBrands->id)) {
                return response()->json(['error' => 'Failed to create Inventory Brand.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$InventoryBrand}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventoryBrands->logid = $logs->id;
            $InventoryBrands->save();
            return response()->json(['success' => 'Inventory Brand created successfully']);
        }

    }

    public function GetInventoryBrandData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->item_brand_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryBrands = InventoryBrand::select('inventory_brand.*',
        'inventory_category.name as catName','inventory_subcategory.name as subCatName',
        'organization.organization as orgName','inventory_type.name as typeName',
        'inventory_generic.name as genericName')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_brand.cat_id')
        ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_brand.sub_catid')
        ->join('inventory_type', 'inventory_type.id', '=', 'inventory_brand.type_id')
        ->join('inventory_generic', 'inventory_generic.id', '=', 'inventory_brand.generic_id')
        ->leftJoin('organization', 'organization.id', '=', 'inventory_brand.org_id')
        ->orderBy('inventory_brand.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryBrands->where('inventory_brand.org_id', '=', $sessionOrg);
        }
        $InventoryBrands = $InventoryBrands;
        // ->get()
        // return DataTables::of($InventoryBrands)
        return DataTables::eloquent($InventoryBrands)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_brand.name', 'like', "%{$search}%")
                            ->orWhere('inventory_category.name', 'like', "%{$search}%")
                            ->orWhere('inventory_subcategory.name', 'like', "%{$search}%")
                            ->orWhere('inventory_type.name', 'like', "%{$search}%")
                            ->orWhere('inventory_generic.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('inventory_brand.id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventoryBrand) {
                return $InventoryBrand->id;  // Raw ID value
            })
            ->editColumn('id', function ($InventoryBrand) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $InventoryBrandName = $InventoryBrand->name;
                // $firstFourLetters = substr(str_replace(' ', '', strtoupper($InventoryGenericName)), 0, 3);
                $idStr = str_pad($InventoryBrand->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($InventoryBrand->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventoryBrand->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventoryBrand->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventoryBrand->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'INV';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventoryBrandName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-2"><b>Organization:</b> '.ucwords($InventoryBrand->orgName);
                }

                return $Code.ucwords($orgName)
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($InventoryBrand) {
                    $InventoryBrandId = $InventoryBrand->id;
                    $logId = $InventoryBrand->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->item_brand_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-invbrand" data-invbrand-id="'.$InventoryBrandId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventoryBrand->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventoryBrand) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->item_brand_setup)[3];
                return $updateStatus == 1 ? ($InventoryBrand->status ? '<span class="label label-success invbrand_status cursor-pointer" data-id="'.$InventoryBrand->id.'" data-status="'.$InventoryBrand->status.'">Active</span>' : '<span class="label label-danger invbrand_status cursor-pointer" data-id="'.$InventoryBrand->id.'" data-status="'.$InventoryBrand->status.'">Inactive</span>') : ($InventoryBrand->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateInventoryBrandStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->item_brand_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryBrandID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventoryBrands = InventoryBrand::find($InventoryBrandID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventoryBrands->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventoryBrands->status = $UpdateStatus;
        $InventoryBrands->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryBrandLog = InventoryBrand::where('id', $InventoryBrandID)->first();
        $logIds = $InventoryBrandLog->logid ? explode(',', $InventoryBrandLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryBrandLog->logid = implode(',', $logIds);
        $InventoryBrandLog->save();

        $InventoryBrands->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventoryBrandModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_brand_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryBrands = InventoryBrand::select('inventory_brand.*',
        'inventory_category.name as catName','inventory_subcategory.name as subCatName',
        'organization.organization as orgName','inventory_type.name as typeName',
        'inventory_generic.name as genericName')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_brand.cat_id')
        ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_brand.sub_catid')
        ->join('inventory_type', 'inventory_type.id', '=', 'inventory_brand.type_id')
        ->join('inventory_generic', 'inventory_generic.id', '=', 'inventory_brand.generic_id')
        ->join('organization', 'organization.id', '=', 'inventory_brand.org_id')
        ->where('inventory_brand.id', '=', $id)
        ->first();

        $InventoryBrand = ucwords($InventoryBrands->name);
        $effective_timestamp = $InventoryBrands->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $InventoryBrand,
            'catId' => $InventoryBrands->cat_id,
            'catName' => ucwords($InventoryBrands->catName),
            'subcatId' => $InventoryBrands->sub_catid,
            'subcatName' => ucwords($InventoryBrands->subCatName),
            'orgId' => $InventoryBrands->org_id,
            'orgName' => ucwords($InventoryBrands->orgName),
            'typeId' => $InventoryBrands->type_id,
            'typeName' => ucwords($InventoryBrands->typeName),
            'genericId' => $InventoryBrands->generic_id,
            'genericName' => ucwords($InventoryBrands->genericName),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateInventoryBrand(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_brand_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryBrands = InventoryBrand::findOrFail($id);
        $InventoryBrands->name = $request->input('u_ib_description');
        $InventoryBrands->cat_id = $request->input('u_ib_cat');
        $InventoryBrands->sub_catid = $request->input('u_ib_subcat');
        $InventoryBrands->type_id = $request->input('u_ib_type');
        $InventoryBrands->generic_id = $request->input('u_ib_generic');
        $orgID = $request->input('u_ib_org');
        if (isset($orgID)) {
            $InventoryBrands->org_id = $orgID;
        }
        $effective_date = $request->input('u_ib_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $InventoryBrands->effective_timestamp = $effective_date;
        $InventoryBrands->last_updated = $this->currentDatetime;
        $InventoryBrands->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventoryBrands->save();

        if (empty($InventoryBrands->id)) {
            return response()->json(['error' => 'Failed to update Inventory Brand. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryBrandLog = InventoryBrand::where('id', $InventoryBrands->id)->first();
        $logIds = $InventoryBrandLog->logid ? explode(',', $InventoryBrandLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryBrandLog->logid = implode(',', $logIds);
        $InventoryBrandLog->save();
        return response()->json(['success' => 'Inventory Brand updated successfully']);
    }

    public function InventoryTransactionType()
    {
        $colName = 'transaction_types';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $TransactionActivities = InventoryTransactionActivity::where('status', 1)->get();
        $InventorySourceDestinationTypes = InventorySourceDestinationType::where('status', 1)->get();
        $ServiceLocations = ServiceLocation::where('status', 1)->get();
        
        return view('dashboard.inv-transaction-type', compact('user','TransactionActivities','InventorySourceDestinationTypes','ServiceLocations'));
    }

    public function AddInventoryTransactionType(InventoryTransactionTypeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->transaction_types)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Org = $request->input('itt_org');
        $InventoryTransactionType = trim($request->input('description'));
        $ActivityType = $request->input('activity_type');
        $requestMandatoryStatus = $request->input('request_mandatory');
        $requestLocationMandatoryStatus = $request->input('request_location_mandatory');
        $sourceLocationType = $request->input('source_location_type');
        $sourceAction = $request->input('source_action');
        $destinationLocationType = $request->input('destination_location_type');
        $destinationAction = $request->input('destination_action');
        $inventoryLocation =  implode(',', $request->input('inventory_location'));

        $ApplicableLocation = $request->input('applicable_location');
        $TransactionExpiredStatus = $request->input('transaction_expired_status');
        $Edt = $request->input('itt_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InventoryTransactionTypeExists = InventoryTransactionType::where('name', $InventoryTransactionType)
        ->where('org_id', $Org)
        ->exists();

        if ($InventoryTransactionTypeExists) {
            return response()->json(['info' => 'Inventory Transaction Type already exists.']);
        }
        else
        {
            $InventoryTransactionTypes = new InventoryTransactionType();
            $InventoryTransactionTypes->name = $InventoryTransactionType;
            $InventoryTransactionTypes->activity_type = $ActivityType;
            $InventoryTransactionTypes->request_mandatory = $requestMandatoryStatus;
            $InventoryTransactionTypes->request_location_mandatory = $requestLocationMandatoryStatus;
            $InventoryTransactionTypes->source_location_type = $sourceLocationType;
            $InventoryTransactionTypes->source_action = $sourceAction;
            $InventoryTransactionTypes->destination_location_type = $destinationLocationType;
            $InventoryTransactionTypes->destination_action = $destinationAction;
            $InventoryTransactionTypes->service_location_id = $inventoryLocation;
            $InventoryTransactionTypes->applicable_location_to = $ApplicableLocation;
            $InventoryTransactionTypes->transaction_expired_status = $TransactionExpiredStatus;
            $InventoryTransactionTypes->org_id = $Org;
            $InventoryTransactionTypes->status = $status;
            $InventoryTransactionTypes->user_id = $sessionId;
            $InventoryTransactionTypes->last_updated = $last_updated;
            $InventoryTransactionTypes->timestamp = $timestamp;
            $InventoryTransactionTypes->effective_timestamp = $Edt;
            $InventoryTransactionTypes->save();

            if (empty($InventoryTransactionTypes->id)) {
                return response()->json(['error' => 'Failed to create Inventory Transaction Type.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$InventoryTransactionType}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventoryTransactionTypes->logid = $logs->id;
            $InventoryTransactionTypes->save();
            return response()->json(['success' => 'Inventory Transaction Type created successfully']);
        }
    }

    public function GetInventoryTransactionTypeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->transaction_types)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $InventoryTransactionTypes = InventoryTransactionType::select(
            'inventory_transaction_type.*',
            'organization.organization as orgName',
            'source_type.name as sourceLocationType',
            'destination_type.name as destinationLocationType', 
            // 'service_location.name as serviceLocation',
            'inventory_transaction_activity.name as transactionActivity'
        )
        ->join('organization', 'organization.id', '=', 'inventory_transaction_type.org_id')
        ->join('inventory_transaction_activity', 'inventory_transaction_activity.id', '=', 'inventory_transaction_type.activity_type')
        ->join('inventory_source_destination_type as source_type', 'source_type.id', '=', 'inventory_transaction_type.source_location_type') 
        ->join('inventory_source_destination_type as destination_type', 'destination_type.id', '=', 'inventory_transaction_type.destination_location_type') 
        // ->join('service_location', 'service_location.id', '=', 'inventory_transaction_type.service_location_id')
        ->orderBy('inventory_transaction_type.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryTransactionTypes->where('inventory_transaction_type.org_id', '=', $sessionOrg);
        }
        $InventoryTransactionTypes = $InventoryTransactionTypes;
        // ->get()
        // return DataTables::of($InventoryTransactionTypes)
        return DataTables::eloquent($InventoryTransactionTypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_transaction_type.name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('source_type.name', 'like', "%{$search}%")
                        ->orWhere('destination_type.name', 'like', "%{$search}%")
                        // ->orWhere('service_location.location', 'like', "%{$search}%")
                        ->orWhere('inventory_transaction_activity.name', 'like', "%{$search}%")
                        ->orWhere('inventory_transaction_type.id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventoryTransactionType) {
                return $InventoryTransactionType->id;  // Raw ID value
            })
            ->editColumn('id', function ($InventoryTransactionType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $InventoryTransactionTypeName = $InventoryTransactionType->name;
                $effectiveDate = Carbon::createFromTimestamp($InventoryTransactionType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventoryTransactionType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventoryTransactionType->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventoryTransactionType->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($InventoryTransactionType->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'TRT';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventoryTransactionTypeName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($InventoryTransactionType->orgName).'<hr class="mt-1 mb-2">';
                }

                $requestMandatory = $InventoryTransactionType->request_mandatory === 'y' ? "Yes" : "No";
                $requestLocationMandatory = $InventoryTransactionType->request_location_mandatory === 'y' ? "Yes" : "No";
                $transactionExpiredStatus = $InventoryTransactionType->transaction_expired_status === 'y' ? "Yes" : "No";

                return $Code.'<hr class="mt-1 mb-2">'.ucwords($InventoryTransactionTypeName)
                    . '<hr class="mt-1 mb-2">'
                    .  $orgName
                    .'<b>Activity Type: </b>'.ucwords($InventoryTransactionType->transactionActivity).'<br>'
                    .'<b>Request Mandatory: </b>'.$requestMandatory.'<br>'
                    .'<b>Request Location Mandatory: </b>'.$requestLocationMandatory.'<br>'
                    .'<b>Transaction Expired Status: </b>'.$transactionExpiredStatus.'<br>'
                    . '<span class="mt-2 label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('sourceDestination', function ($InventoryTransactionType) {
                $sourceLocationType = $InventoryTransactionType->sourceLocationType;
                $destinationLocationType = $InventoryTransactionType->destinationLocationType;
                $sourceAction = [
                    'a' => 'Add',
                    's' => 'Subtract',
                    'r' => 'Reversal',
                    'n' => 'Not Applicable'
                ][$InventoryTransactionType->source_action] ?? $InventoryTransactionType->source_action;

                $destinationAction = [
                    'a' => 'Add',
                    's' => 'Subtract',
                    'r' => 'Reversal',
                    'n' => 'Not Applicable'
                ][$InventoryTransactionType->destination_action] ?? $InventoryTransactionType->destination_action;

                return '<b>Source Location Type: </b>'.ucwords($sourceLocationType).'<br>'
                    .'<b>Source Action: </b>'.$sourceAction.'<br>'
                    .'<b>Destination Location Type: </b>'.$destinationLocationType.'<br>'
                    .'<b>Destination Action: </b>'.$destinationAction.'<br>';
            })
            ->addColumn('locationDetails', function ($InventoryTransactionType) {
                $serviceLocationIds = explode(',', $InventoryTransactionType->service_location_id); 
                $serviceLocationNames = ServiceLocation::whereIn('id', $serviceLocationIds)
                ->pluck('name')
                ->toArray();  
                $serviceLocationNames = implode(', ', $serviceLocationNames);

                // $AllocatedInventoryLocation = $InventoryTransactionType->serviceLocation;
                return '<b>Allocated Inventory Location: </b>'.ucwords($serviceLocationNames).'<br>'
                .'<b>Applicable Location Action: </b>'.ucwords($InventoryTransactionType->applicable_location_to);
            })
            ->addColumn('action', function ($InventoryTransactionType) {
                    $InventoryTransactionTypeId = $InventoryTransactionType->id;
                    $logId = $InventoryTransactionType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->transaction_types)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-invtransactiontype" data-invtransactiontype-id="'.$InventoryTransactionTypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventoryTransactionType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventoryTransactionType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->transaction_types)[3];
                return $updateStatus == 1 ? ($InventoryTransactionType->status ? '<span class="label label-success invtransactiontype_status cursor-pointer" data-id="'.$InventoryTransactionType->id.'" data-status="'.$InventoryTransactionType->status.'">Active</span>' : '<span class="label label-danger invtransactiontype_status cursor-pointer" data-id="'.$InventoryTransactionType->id.'" data-status="'.$InventoryTransactionType->status.'">Inactive</span>') : ($InventoryTransactionType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id','sourceDestination','locationDetails'])
            ->make(true);
    }

    public function UpdateInventoryTransactionTypeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->transaction_types)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTransactionTypeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventoryTransactionTypes = InventoryTransactionType::find($InventoryTransactionTypeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventoryTransactionTypes->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventoryTransactionTypes->status = $UpdateStatus;
        $InventoryTransactionTypes->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryTransactionTypeLog = InventoryTransactionType::where('id', $InventoryTransactionTypeID)->first();
        $logIds = $InventoryTransactionTypeLog->logid ? explode(',', $InventoryTransactionTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryTransactionTypeLog->logid = implode(',', $logIds);
        $InventoryTransactionTypeLog->save();

        $InventoryTransactionTypes->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventoryTransactionTypeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->transaction_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }    
        $InventoryTransactionTypes = InventoryTransactionType::select(
            'inventory_transaction_type.*',
            'organization.organization as orgName',
            'source_type.name as sourceLocationType',
            'destination_type.name as destinationLocationType', 
            // 'service_location.name as serviceLocation',
            'inventory_transaction_activity.name as transactionActivity'
        )
        ->join('organization', 'organization.id', '=', 'inventory_transaction_type.org_id')
        ->join('inventory_transaction_activity', 'inventory_transaction_activity.id', '=', 'inventory_transaction_type.activity_type')
        ->join('inventory_source_destination_type as source_type', 'source_type.id', '=', 'inventory_transaction_type.source_location_type') 
        ->join('inventory_source_destination_type as destination_type', 'destination_type.id', '=', 'inventory_transaction_type.destination_location_type') 
        // ->join('service_location', 'service_location.id', '=', 'inventory_transaction_type.service_location_id')
        ->where('inventory_transaction_type.id', '=', $id)
        ->first();

        $serviceLocationIds = explode(',', $InventoryTransactionTypes->service_location_id);
        $serviceLocationNames = DB::table('service_location')
            ->whereIn('id', $serviceLocationIds)
            ->pluck('name')
            ->toArray();
        $serviceLocationNames = implode(', ', $serviceLocationNames);

        $InventoryTransactionType = ucwords($InventoryTransactionTypes->name);
        $effective_timestamp = $InventoryTransactionTypes->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $InventoryTransactionType,
            'orgId' => $InventoryTransactionTypes->org_id,
            'orgName' => ucwords($InventoryTransactionTypes->orgName),
            'activitytypeId' => $InventoryTransactionTypes->activity_type,
            'activitytype' => $InventoryTransactionTypes->transactionActivity,
            'requestMandatory' => $InventoryTransactionTypes->request_mandatory,
            'requestLocationMandatory' => ($InventoryTransactionTypes->request_location_mandatory),
            'sourceLocationTypeId' => $InventoryTransactionTypes->source_location_type,
            'sourceLocationType' => ucwords($InventoryTransactionTypes->sourceLocationType),
            'sourceAction' => ($InventoryTransactionTypes->source_action),
            'destinationLocationTypeId' => $InventoryTransactionTypes->destination_location_type,
            'destinationLocationType' => ucwords($InventoryTransactionTypes->destinationLocationType),
            'destinationAction' => ($InventoryTransactionTypes->destination_action),
            'serviceLocationId' => $InventoryTransactionTypes->service_location_id,
            'serviceLocation' => ucwords($serviceLocationNames),
            'ApplicableLocation' => ($InventoryTransactionTypes->applicable_location_to),
            'TransactionExpiredStatus' => ($InventoryTransactionTypes->transaction_expired_status),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateInventoryTransactionType(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->transaction_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTransactionTypes = InventoryTransactionType::findOrFail($id);

        $orgID = $request->input('u_itt_org');
        if (isset($orgID)) {
            $InventoryTransactionTypes->org_id = $orgID;
        }
        $InventoryTransactionTypes->name = $request->input('u_description');
        $InventoryTransactionTypes->activity_type = $request->input('u_activity_type');
        $InventoryTransactionTypes->request_mandatory = $request->input('u_request_mandatory');
        $InventoryTransactionTypes->request_location_mandatory = $request->input('u_request_location_mandatory');
        $InventoryTransactionTypes->source_location_type = $request->input('u_source_location_type');
        $InventoryTransactionTypes->source_action = $request->input('u_source_action');
        $InventoryTransactionTypes->destination_location_type = $request->input('u_destination_location_type');
        $InventoryTransactionTypes->destination_action = $request->input('u_destination_action');
        // $InventoryTransactionTypes->service_location_id = $request->input('u_inventory_location');
        $InventoryTransactionTypes->service_location_id = implode(',', $request->input('u_inventory_location'));
        $InventoryTransactionTypes->applicable_location_to = $request->input('u_applicable_location');
        $InventoryTransactionTypes->transaction_expired_status = $request->input('u_transaction_expired_status');
        
        $effective_date = $request->input('u_itt_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $InventoryTransactionTypes->effective_timestamp = $effective_date;
        $InventoryTransactionTypes->last_updated = $this->currentDatetime;
        $InventoryTransactionTypes->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventoryTransactionTypes->save();

        if (empty($InventoryTransactionTypes->id)) {
            return response()->json(['error' => 'Failed to update Inventory Transaction Type. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryTransactionTypeLog = InventoryTransactionType::where('id', $InventoryTransactionTypes->id)->first();
        $logIds = $InventoryTransactionTypeLog->logid ? explode(',', $InventoryTransactionTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryTransactionTypeLog->logid = implode(',', $logIds);
        $InventoryTransactionTypeLog->save();
        return response()->json(['success' => 'Inventory Transaction Type updated successfully']);
    }

    // public function InventoryVendorRegistration()
    // {
    //     $colName = 'vendor_registration';
    //     if (PermissionDenied($colName)) {
    //         abort(403); 
    //     }
    //     $user = auth()->user();
    //     return view('dashboard.inventory-vendor-registration', compact('user'));
    // }

    // public function VendorRegistration(VendorRegistrationRequest $request)
    // {
    //     $rights = $this->rights;
    //     $add = explode(',', $rights->vendor_registration)[0];
    //     if($add == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $VendorDescription = trim($request->input('vendor_desc'));
    //     $Organization = $request->input('vendor_org');
    //     $Address = $request->input('vendor_address');
    //     $FocalPersonName = $request->input('vendor_name');
    //     $Email = $request->input('vendor_email');
    //     $Cell = $request->input('vendor_cell');
    //     $Landline = $request->input('vendor_landline');
    //     $Remarks = $request->input('vendor_remarks');
    //     $Edt = $request->input('vendor_edt');
    //     $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
    //     $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
    //     $EffectDateTime->subMinute(1);
    //     if ($EffectDateTime->isPast()) {
    //         $status = 1; //Active
    //         $emailStatus = 'Active';

    //     } else {
    //         $status = 0; //Inactive
    //         $emailStatus = 'Inactive';
    //     }

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $last_updated = $this->currentDatetime;
    //     $timestamp = $this->currentDatetime;
    //     $logId = null;

    //     $VendorRegistrationExists = VendorRegistration::where('name', $VendorDescription)
    //     ->where('address', $Address)
    //     ->where('org_id', $Organization)
    //     ->where('person_email', $Email)
    //     ->where('cell_no', $Cell)
    //     ->exists();

    //     if ($VendorRegistrationExists) {
    //         return response()->json(['info' => 'Vendor already exists.']);
    //     }
    //     else
    //     {
    //         $Vendor = new VendorRegistration();
    //         $Vendor->name = $VendorDescription;
    //         $Vendor->address = $Address;
    //         $Vendor->org_id = $Organization;
    //         $Vendor->person_name = $FocalPersonName;
    //         $Vendor->person_email = $Email;
    //         $Vendor->cell_no = $Cell;
    //         $Vendor->landline_no = $Landline;
    //         $Vendor->remarks = $Remarks;
    //         $Vendor->status = $status;
    //         $Vendor->user_id = $sessionId;
    //         $Vendor->last_updated = $last_updated;
    //         $Vendor->timestamp = $timestamp;
    //         $Vendor->effective_timestamp = $Edt;


    //         try {
    //             $emailTimestamp = Carbon::createFromTimestamp($timestamp);
    //             $emailTimestamp = $emailTimestamp->format('l d F Y - h:i A');
    //             $emailEdt = $request->input('vendor_edt');
    //             $orgName = Organization::find($Organization)->organization;
    //             if($Landline === null)
    //             {
    //                 $Landline = 'N/A';
    //             }

    //             Mail::to($Email)->send(new VendorRegistrationMail($VendorDescription, $Address, $orgName,
    //             $FocalPersonName, $Email, $Cell, $Landline, $Remarks,
    //             $emailStatus, $emailEdt, $emailTimestamp));

    //             $Vendor->save();

    //         }
    //         catch (TransportExceptionInterface $ex)
    //         {
    //             return response()->json(['info' => 'There is an issue with email. Please try again!.']);
    //         }

    //         if (empty($Vendor->id)) {
    //             return response()->json(['error' => 'Failed to create Vendor.']);
    //         }

    //         $logs = Logs::create([
    //             'module' => 'inventory',
    //             'content' => "'{$VendorDescription}' has been added by '{$sessionName}'",
    //             'event' => 'add',
    //             'timestamp' => $timestamp,
    //         ]);
    //         $logId = $logs->id;
    //         $Vendor->logid = $logs->id;
    //         $Vendor->save();
    //         return response()->json(['success' => 'Vendor Registered successfully']);
    //     }
    // }

    // public function GetVendorRegistrationData(Request $request)
    // {
    //     $rights = $this->rights;
    //     $view = explode(',', $rights->vendor_registration)[1];
    //     if($view == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $Vendors = VendorRegistration::select('vendor.*',
    //     'organization.organization as orgName')
    //     ->join('organization', 'organization.id', '=', 'vendor.org_id')
    //     ->orderBy('vendor.id', 'desc');

    //     $session = auth()->user();
    //     $sessionOrg = $session->org_id;
    //     if($sessionOrg != '0')
    //     {
    //         $Vendors->where('vendor.org_id', '=', $sessionOrg);
    //     }
    //     $Vendors = $Vendors;
    //     // ->get()
    //     // return DataTables::of($Vendors)
    //     return DataTables::eloquent($Vendors)
    //         ->filter(function ($query) use ($request) {
    //             if ($request->has('search') && $request->search['value']) {
    //                 $search = $request->search['value'];
    //                 $query->where(function ($q) use ($search) {
    //                     $q->where('vendor.name', 'like', "%{$search}%")
    //                         ->orWhere('organization.organization', 'like', "%{$search}%")
    //                         ->orWhere('vendor.person_name', 'like', "%{$search}%")
    //                         ->orWhere('vendor.person_email', 'like', "%{$search}%")
    //                         ->orWhere('vendor.cell_no', 'like', "%{$search}%")
    //                         ->orWhere('vendor.landline_no', 'like', "%{$search}%");
    //                 });
    //             }
    //         })
    //         ->addColumn('id_raw', function ($Vendor) {
    //             return $Vendor->id;  // Raw ID value
    //         })
    //         ->editColumn('id', function ($Vendor) {
    //             $session = auth()->user();
    //             $sessionName = $session->name;
    //             $sessionId = $session->id;
    //             $VendorDescription = $Vendor->name;
    //             $effectiveDate = Carbon::createFromTimestamp($Vendor->effective_timestamp)->format('l d F Y - h:i A');
    //             $timestamp = Carbon::createFromTimestamp($Vendor->timestamp)->format('l d F Y - h:i A');
    //             $lastUpdated = Carbon::createFromTimestamp($Vendor->last_updated)->format('l d F Y - h:i A');
    //             $createdByName = getUserNameById($Vendor->user_id);
    //             $createdInfo = "
    //                     <b>Created By:</b> " . ucwords($createdByName) . "  <br>
    //                     <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
    //                     <b>RecordedAt:</b> " . $timestamp ." <br>
    //                     <b>LastUpdated:</b> " . $lastUpdated;

    //             $idStr = str_pad($Vendor->id, 5, "0", STR_PAD_LEFT);
    //             $ModuleCode = 'VRG';
    //             $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $VendorDescription))));
    //             $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

    //             $sessionOrg = $session->org_id;
    //             $orgName = '';
    //             if($sessionOrg == 0)
    //             {
    //                 $orgName ='<b>Organization:</b> '.ucwords($Vendor->orgName).'<br>';
    //             }

    //             return $Code.'<hr class="mt-1 mb-2">'.ucwords($VendorDescription)
    //                 . '<hr class="mt-1 mb-2">'
    //                 . $orgName
    //                 .'<b>Focal Person Name:</b> '.ucwords($Vendor->person_name)
    //                 . '<hr class="mt-1 mb-2">'
    //                 .'<b>Remarks:</b> '.ucwords($Vendor->remarks)
    //                 . '<hr class="mt-1 mb-2">'
    //                 . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
    //                 . '<i class="fa fa-toggle-right"></i> View Details'
    //                 . '</span>';
    //         })
    //         ->editColumn('contactDetails', function ($Vendor) {

    //             return 
    //                 '<b>Email:</b> '.($Vendor->person_email)
    //                 .'<br><b>Cell #:</b> '.ucwords($Vendor->cell_no)
    //                 .'<br><b>Landline:</b> '.ucwords($Vendor->landline_no)
    //                 ;
    //         })
    //         ->addColumn('landline', function ($Vendor) {
    //             $Landline = $Vendor->landline_no;
    //             if($Landline === null)
    //             {
    //                 $Landline = 'N/A';
    //             }

    //             return $Landline;
    //         })
    //         ->addColumn('action', function ($Vendor) {
    //                 $VendorId = $Vendor->id;
    //                 $logId = $Vendor->logid;
    //                 $Rights = $this->rights;
    //                 $edit = explode(',', $Rights->vendor_registration)[2];
    //                 $actionButtons = '';
    //                 if ($edit == 1) {
    //                     $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-vendor" data-vendor-id="'.$VendorId.'">'
    //                     . '<i class="fa fa-edit"></i> Edit'
    //                     . '</button>';
    //                 }
    //                 $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
    //                 . '<i class="fa fa-eye"></i> View Logs'
    //                 . '</button>';
    //                 return $Vendor->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

    //         })
    //         ->editColumn('status', function ($Vendor) {
    //             $rights = $this->rights;
    //             $updateStatus = explode(',', $rights->vendor_registration)[3];
    //             return $updateStatus == 1 ? ($Vendor->status ? '<span class="label label-success vendor_status cursor-pointer" data-id="'.$Vendor->id.'" data-status="'.$Vendor->status.'">Active</span>' : '<span class="label label-danger vendor_status cursor-pointer" data-id="'.$Vendor->id.'" data-status="'.$Vendor->status.'">Inactive</span>') : ($Vendor->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

    //         })
    //         ->rawColumns(['action', 'status','contactDetails',
    //         'id'])
    //         ->make(true);
    // }

    // public function UpdateVenderStatus(Request $request)
    // {
    //     $rights = $this->rights;
    //     $UpdateStatus = explode(',', $rights->vendor_registration)[3];
    //     if($UpdateStatus == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $VendorID = $request->input('id');
    //     $Status = $request->input('status');
    //     $CurrentTimestamp = $this->currentDatetime;
    //     $Vendors = VendorRegistration::find($VendorID);

    //     if($Status == 0)
    //     {
    //         $UpdateStatus = 1;
    //         $statusLog = 'Active';
    //         $Vendors->effective_timestamp = $CurrentTimestamp;
    //     }
    //     else{
    //         $UpdateStatus = 0;
    //         $statusLog = 'Inactive';

    //     }
    //     $Vendors->status = $UpdateStatus;
    //     $Vendors->last_updated = $CurrentTimestamp;

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $logs = Logs::create([
    //         'module' => 'inventory',
    //         'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
    //         'event' => 'update',
    //         'timestamp' => $this->currentDatetime,
    //     ]);
    //     $VendorLog = VendorRegistration::where('id', $VendorID)->first();
    //     $logIds = $VendorLog->logid ? explode(',', $VendorLog->logid) : [];
    //     $logIds[] = $logs->id;
    //     $VendorLog->logid = implode(',', $logIds);
    //     $VendorLog->save();

    //     $Vendors->save();
    //     return response()->json(['success' => true, 200]);
    // }

    // public function UpdateVendorRegistrationModal($id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->vendor_registration)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $Vendors = VendorRegistration::select('vendor.*',
    //     'organization.organization as orgName')
    //     ->join('organization', 'organization.id', '=', 'vendor.org_id')
    //     ->where('vendor.id', '=', $id)
    //     ->first();

    //     $effective_timestamp = $Vendors->effective_timestamp;
    //     $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
    //     $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

    //     $data = [
    //         'id' => $id,
    //         'name' => ucwords($Vendors->name),
    //         'address' => ucwords($Vendors->address),
    //         'orgId' => $Vendors->org_id,
    //         'orgName' => ucwords($Vendors->orgName),
    //         'personName' => ucwords($Vendors->person_name),
    //         'personEmail' => ucwords($Vendors->person_email),
    //         'cellNo' => ucwords($Vendors->cell_no),
    //         'landlineNo' => ucwords($Vendors->landline_no),
    //         'remarks' => ucwords($Vendors->remarks),
    //         'effective_timestamp' => $effective_timestamp,
    //     ];
    //     return response()->json($data);
    // }

    // public function UpdateVendorRegistration(Request $request, $id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->vendor_registration)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $Vendors = VendorRegistration::findOrFail($id);
    //     $Vendors->name = $request->input('u_vendor_desc');
    //     $Vendors->address = $request->input('u_vendor_address');
    //     $orgID = $request->input('u_vendor_org');
    //     if (isset($orgID)) {
    //         $Vendors->org_id = $orgID;
    //     }
    //     $Vendors->person_name = $request->input('u_vendor_name');
    //     $Vendors->person_email = $request->input('u_vendor_email');
    //     $Vendors->cell_no = $request->input('u_vendor_cell');
    //     $Vendors->landline_no = $request->input('u_vendor_landline');
    //     $Vendors->remarks = $request->input('u_vendor_remarks');
    //     $effective_date = $request->input('u_vendor_edt');
    //     $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
    //     $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
    //     $EffectDateTime->subMinute(1);

    //     if ($EffectDateTime->isPast()) {
    //         $status = 1; //Active
    //     } else {
    //          $status = 0; //Inactive
    //     }

    //     $Vendors->effective_timestamp = $effective_date;
    //     $Vendors->last_updated = $this->currentDatetime;
    //     $Vendors->status = $status;

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $Vendors->save();

    //     if (empty($Vendors->id)) {
    //         return response()->json(['error' => 'Failed to update Vendor Details. Please try again']);
    //     }
    //     $logs = Logs::create([
    //         'module' => 'inventory',
    //         'content' => "Data has been updated by '{$sessionName}'",
    //         'event' => 'update',
    //         'timestamp' => $this->currentDatetime,
    //     ]);
    //     $VendorLog = InventoryTransactionType::where('id', $Vendors->id)->first();
    //     $logIds = $VendorLog->logid ? explode(',', $VendorLog->logid) : [];
    //     $logIds[] = $logs->id;
    //     $VendorLog->logid = implode(',', $logIds);
    //     $VendorLog->save();
    //     return response()->json(['success' => 'Vendor Details updated successfully']);
    // }

    public function ThirdParty()
    {
        $colName = 'third_party_registration';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        $Prefixes = PrefixSetup::where('status', 1)->get();
        return view('dashboard.third_party', compact('user','Organizations','Prefixes'));
    }

    public function ThirdPartyRegistration(ThirdPartyRegistrationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->third_party_registration)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('tp_org');
        $RegistrationType = $request->input('registration_type');
        $VendorCat = $request->input('vendor_cat');
        $Address = $request->input('vendor_address');
        $CorporateName = $request->input('tp_corporate_name');
        $Prefix = $request->input('tp_prefix');
        $PersonName = $request->input('tp_name');
        $Email = $request->input('tp_email');
        $Cell = $request->input('tp_cell');
        $Landline = $request->input('tp_landline');
        $Address = $request->input('tp_address');
        $Remarks = $request->input('tp_remarks');
        $Edt = $request->input('tp_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
            $emailStatus = 'Active';
        } else {
            $status = 0;
            $emailStatus = 'Inactive';
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $ThirdPartyRegistrationExists = ThirdPartyRegistration::where('org_id', $Organization)
        ->where('person_email', $Email)
        ->where('person_cell', $Cell)
        ->exists();

        if ($ThirdPartyRegistrationExists) {
            return response()->json(['info' => 'Vendor/Donor already exists.']);
        }
        else
        {
            $ThirdParty = new ThirdPartyRegistration();
            $ThirdParty->org_id = $Organization;
            $ThirdParty->type = $RegistrationType;
            $ThirdParty->category = $VendorCat;
            $ThirdParty->corporate_name = $CorporateName;
            $ThirdParty->prefix_id = $Prefix;
            $ThirdParty->person_name = $PersonName;
            $ThirdParty->person_email = $Email;
            $ThirdParty->person_cell = $Cell;
            $ThirdParty->landline = $Landline;
            $ThirdParty->address = $Address;
            $ThirdParty->remarks = $Remarks;
            $ThirdParty->status = $status;
            $ThirdParty->user_id = $sessionId;
            $ThirdParty->last_updated = $last_updated;
            $ThirdParty->timestamp = $timestamp;
            $ThirdParty->effective_timestamp = $Edt;

            try {
                $emailTimestamp = Carbon::createFromTimestamp($timestamp);
                $emailTimestamp = $emailTimestamp->format('l d F Y - h:i A');
                $emailEdt = $request->input('tp_edt');
                $orgName = Organization::find($Organization)->organization;
                if($Landline === null)
                {
                    $Landline = 'N/A';
                }
                $RegistrationType = ($RegistrationType == 'v') ? 'Vendor' : 'Donor';
                $VendorCat = ($VendorCat == 'c') ? 'Corporate' : 'Individual';

                Mail::to($Email)->send(new ThirdPartyRegistrationMail($RegistrationType, $VendorCat, $Address, $orgName,
                $PersonName, $Email, $Cell, $Landline, $Remarks,
                $emailStatus, $emailEdt, $emailTimestamp));

                $ThirdParty->save();

            }
            catch (TransportExceptionInterface $ex)
            {
                return response()->json(['info' => 'There is an issue with email. Please try again!.']);
            }

            if (empty($ThirdParty->id)) {
                return response()->json(['error' => 'Failed to create Vendor/Donor.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$RegistrationType} -- {$PersonName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ThirdParty->logid = $logs->id;
            $ThirdParty->save();
            return response()->json(['success' => $RegistrationType.' Registered successfully']);
        }
    }

    public function GetThirdPartyRegistrationData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->third_party_registration)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ThirdParties = ThirdPartyRegistration::select('third_party.*',
        'organization.organization as orgName','prefix.name as prefixName')
        ->join('organization', 'organization.id', '=', 'third_party.org_id')
        ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
        ->orderBy('third_party.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ThirdParties->where('third_party.org_id', '=', $sessionOrg);
        }
        $ThirdParties = $ThirdParties;
        // ->get()
        // return DataTables::of($Vendors)
        return DataTables::eloquent($ThirdParties)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('third_party.person_name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('third_party.person_email', 'like', "%{$search}%")
                        ->orWhere('third_party.person_cell', 'like', "%{$search}%")
                        ->orWhere('third_party.address', 'like', "%{$search}%")
                        ->orWhere('third_party.remarks', 'like', "%{$search}%")
                        ->orWhere('third_party.type', 'like', "%{$search}%")
                        ->orWhere('third_party.category', 'like', "%{$search}%")
                        ->orWhere('third_party.landline', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ThirdParty) {
                return $ThirdParty->id;  // Raw ID value
            })
            ->editColumn('id', function ($ThirdParty) {
                $corporateName = '';
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $PersonName = $ThirdParty->prefixName.' '.$ThirdParty->person_name;
                $effectiveDate = Carbon::createFromTimestamp($ThirdParty->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ThirdParty->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ThirdParty->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ThirdParty->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($ThirdParty->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'TPR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $PersonName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($ThirdParty->orgName).'<br>';
                }
                if($ThirdParty->corporate_name != '')
                {
                    $corporateName ='<b>Corporate Name:</b> '.ucwords($ThirdParty->corporate_name).'<br>';
                }
                $RegistrationType = ($ThirdParty->type == 'v') ? 'Vendor' : 'Donor';
                $VendorCat = ($ThirdParty->category == 'c') ? 'Corporate' : 'Individual';
                $remarks = $ThirdParty->remarks ?? 'N/A';

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . $orgName
                    .'<b>Focal Person Name:</b> '.ucwords($PersonName).'<br>'
                    .'<b>Type:</b> '.ucwords($RegistrationType).'<br>'
                    .'<b>Category:</b> '.ucwords($VendorCat).'<br>'
                    .($corporateName)
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Remarks:</b> '.ucwords($remarks)
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('contactDetails', function ($ThirdParty) {
                return 
                    '<b>Email:</b> '.($ThirdParty->person_email)
                    .'<br><b>Cell #:</b> '.ucwords($ThirdParty->person_cell)
                    .'<br><b>Landline:</b> '. (!empty($ThirdParty->landline) ? ucwords($ThirdParty->landline) : 'N/A')
                ;
            })
            ->addColumn('action', function ($ThirdParty) {
                    $ThirdPartyId = $ThirdParty->id;
                    $logId = $ThirdParty->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->third_party_registration)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-tp" data-tp-id="'.$ThirdPartyId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $ThirdParty->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ThirdParty) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->third_party_registration)[3];
                return $updateStatus == 1 ? ($ThirdParty->status ? '<span class="label label-success tp_status cursor-pointer" data-id="'.$ThirdParty->id.'" data-status="'.$ThirdParty->status.'">Active</span>' : '<span class="label label-danger tp_status cursor-pointer" data-id="'.$ThirdParty->id.'" data-status="'.$ThirdParty->status.'">Inactive</span>') : ($ThirdParty->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','contactDetails',
            'id'])
            ->make(true);
    }

    public function UpdateThirdPartyStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->third_party_registration)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ThirdPartyID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ThirdParty = ThirdPartyRegistration::find($ThirdPartyID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ThirdParty->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $ThirdParty->status = $UpdateStatus;
        $ThirdParty->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ThirdPartyLog = ThirdPartyRegistration::where('id', $ThirdPartyID)->first();
        $logIds = $ThirdPartyLog->logid ? explode(',', $ThirdPartyLog->logid) : [];
        $logIds[] = $logs->id;
        $ThirdPartyLog->logid = implode(',', $logIds);
        $ThirdPartyLog->save();

        $ThirdParty->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateThirdPartyRegistrationModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->third_party_registration)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ThirdParty = ThirdPartyRegistration::select('third_party.*',
        'organization.organization as orgName','prefix.name as prefixName')
        ->join('organization', 'organization.id', '=', 'third_party.org_id')
        ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
        ->where('third_party.id', '=', $id)
        ->first();

        $effective_timestamp = $ThirdParty->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $ThirdParty->org_id,
            'orgName' => ucwords($ThirdParty->orgName),
            'prefixId' => $ThirdParty->prefix_id,
            'prefixName' => ucwords($ThirdParty->prefixName),
            'registrationType' => ($ThirdParty->type),
            'vendorCat' => ($ThirdParty->category),
            'corporateName' => ($ThirdParty->corporate_name),
            'personName' => ucwords($ThirdParty->person_name),
            'personEmail' => ucwords($ThirdParty->person_email),
            'cellNo' => ucwords($ThirdParty->person_cell),
            'landlineNo' => ucwords($ThirdParty->landline),
            'address' => ucwords($ThirdParty->address),
            'remarks' => ucwords($ThirdParty->remarks),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateThirdPartyRegistration(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->third_party_registration)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ThirdParty = ThirdPartyRegistration::findOrFail($id);
        $orgID = $request->input('u_tp_org');
        if (isset($orgID)) {
            $ThirdParty->org_id = $orgID;
        }
        $ThirdParty->type = $request->input('u_registration_type');
        $ThirdParty->category = $request->input('u_vendor_cat');
        $ThirdParty->corporate_name = $request->input('u_tp_corporate_name');
        $ThirdParty->prefix_id = $request->input('u_tp_prefix');
        $ThirdParty->person_name = $request->input('u_tp_name');
        $ThirdParty->person_email = $request->input('u_tp_email');
        $ThirdParty->person_cell = $request->input('u_tp_cell');
        $ThirdParty->landline = $request->input('u_tp_landline');
        $ThirdParty->address = $request->input('u_tp_address');
        $ThirdParty->remarks = $request->input('u_tp_remarks');
        $effective_date = $request->input('u_tp_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ThirdParty->effective_timestamp = $effective_date;
        $ThirdParty->last_updated = $this->currentDatetime;
        $ThirdParty->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ThirdParty->save();

        if (empty($ThirdParty->id)) {
            return response()->json(['error' => 'Failed to update Vendor/Donor Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ThirdPartyLog = ThirdPartyRegistration::where('id', $ThirdParty->id)->first();
        $logIds = $ThirdPartyLog->logid ? explode(',', $ThirdPartyLog->logid) : [];
        $logIds[] = $logs->id;
        $ThirdPartyLog->logid = implode(',', $logIds);
        $ThirdPartyLog->save();
        return response()->json(['success' => 'Vendor/Donor Details updated successfully']);
    }

    public function ViewConsumptionGroups()
    {
        $colName = 'consumption_group';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.consumption_group', compact('user','Organizations'));
    }

    public function AddConsumptionGroup(ConsumptionGroupRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->consumption_group)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('cg_org');
        $Desc = $request->input('cg_desc');
        $Remarks = $request->input('cg_remarks');
        $Edt = $request->input('cg_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
            $emailStatus = 'Active';
        } else {
            $status = 0;
            $emailStatus = 'Inactive';
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $CGExists = ConsumptionGroup::where('org_id', $Organization)
        ->where('description', $Desc)
        ->exists();

        if ($CGExists) {
            return response()->json(['info' => 'Consumption Group already exists.']);
        }
        else
        {
            $ConsumptionGroups = new ConsumptionGroup();
            $ConsumptionGroups->org_id = $Organization;
            $ConsumptionGroups->description = $Desc;
            $ConsumptionGroups->remarks = $Remarks;
            $ConsumptionGroups->status = $status;
            $ConsumptionGroups->user_id = $sessionId;
            $ConsumptionGroups->last_updated = $last_updated;
            $ConsumptionGroups->timestamp = $timestamp;
            $ConsumptionGroups->effective_timestamp = $Edt;

            $ConsumptionGroups->save();

            if (empty($ConsumptionGroups->id)) {
                return response()->json(['error' => 'Failed to create Consumption Group.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$Desc}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ConsumptionGroups->logid = $logs->id;
            $ConsumptionGroups->save();
            return response()->json(['success' => 'Consumption Group Added successfully']);
        }
    }

    public function GetConsumptionGroupData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->consumption_group)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ConsumptionGroups = ConsumptionGroup::select('consumption_group.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'consumption_group.org_id')
        ->orderBy('consumption_group.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ConsumptionGroups->where('consumption_group.org_id', '=', $sessionOrg);
        }
        $ConsumptionGroups = $ConsumptionGroups;
        // ->get()
        // return DataTables::of($Vendors)
        return DataTables::eloquent($ConsumptionGroups)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('consumption_group.description', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('consumption_group.description', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ConsumptionGroup) {
                return $ConsumptionGroup->id; 
            })
            ->editColumn('id', function ($ConsumptionGroup) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $Desc = $ConsumptionGroup->description;
                $effectiveDate = Carbon::createFromTimestamp($ConsumptionGroup->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ConsumptionGroup->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ConsumptionGroup->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ConsumptionGroup->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($ConsumptionGroup->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'CG';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Desc))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($ConsumptionGroup->orgName).'<br>';
                }
                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . $orgName
                    .'<b>Description:</b> '.ucwords($Desc).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('remarks', function ($ConsumptionGroup) {
                return ucwords($ConsumptionGroup->remarks);
            })
            ->addColumn('action', function ($ConsumptionGroup) {
                    $ConsumptionGroupId = $ConsumptionGroup->id;
                    $logId = $ConsumptionGroup->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->consumption_group)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-cg" data-cg-id="'.$ConsumptionGroupId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $ConsumptionGroup->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ConsumptionGroup) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->consumption_group)[3];
                return $updateStatus == 1 ? ($ConsumptionGroup->status ? '<span class="label label-success cg_status cursor-pointer" data-id="'.$ConsumptionGroup->id.'" data-status="'.$ConsumptionGroup->status.'">Active</span>' : '<span class="label label-danger cg_status cursor-pointer" data-id="'.$ConsumptionGroup->id.'" data-status="'.$ConsumptionGroup->status.'">Inactive</span>') : ($ConsumptionGroup->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','remarks',
            'id'])
            ->make(true);
    }

    public function UpdateConsumptionGroupStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->consumption_group)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ConsumptionGroupID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ConsumptionGroup = ConsumptionGroup::find($ConsumptionGroupID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ConsumptionGroup->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $ConsumptionGroup->status = $UpdateStatus;
        $ConsumptionGroup->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ConsumptionGroupLog = ConsumptionGroup::where('id', $ConsumptionGroupID)->first();
        $logIds = $ConsumptionGroupLog->logid ? explode(',', $ConsumptionGroupLog->logid) : [];
        $logIds[] = $logs->id;
        $ConsumptionGroupLog->logid = implode(',', $logIds);
        $ConsumptionGroupLog->save();

        $ConsumptionGroup->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateConsumptionGroupModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->consumption_group)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ConsumptionGroup = ConsumptionGroup::select('consumption_group.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'consumption_group.org_id')
        ->where('consumption_group.id', '=', $id)
        ->first();

        $effective_timestamp = $ConsumptionGroup->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $ConsumptionGroup->org_id,
            'orgName' => ucwords($ConsumptionGroup->orgName),
            'desc' => ucwords($ConsumptionGroup->description),
            'remarks' => ucwords($ConsumptionGroup->remarks),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateConsumptionGroup(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->consumption_group)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ConsumptionGroup = ConsumptionGroup::findOrFail($id);
        $orgID = $request->input('u_cg_org');

        if (isset($orgID)) {
            $ConsumptionGroup->org_id = $orgID;
        }
        $ConsumptionGroup->description = $request->input('u_cg_desc');
        $ConsumptionGroup->remarks = $request->input('u_cg_remarks');
        $effective_date = $request->input('u_cg_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ConsumptionGroup->effective_timestamp = $effective_date;
        $ConsumptionGroup->last_updated = $this->currentDatetime;
        $ConsumptionGroup->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ConsumptionGroup->save();

        if (empty($ConsumptionGroup->id)) {
            return response()->json(['error' => 'Failed to update Consumption Group. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ConsumptionGroupLog = ConsumptionGroup::where('id', $ConsumptionGroup->id)->first();
        $logIds = $ConsumptionGroupLog->logid ? explode(',', $ConsumptionGroupLog->logid) : [];
        $logIds[] = $logs->id;
        $ConsumptionGroupLog->logid = implode(',', $logIds);
        $ConsumptionGroupLog->save();
        return response()->json(['success' => 'Consumption Group updated successfully']);
    }


    public function ViewConsumptionMethods()
    {
        $colName = 'consumption_method';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        $ConsumptionGroups = ConsumptionGroup::where('status', 1)->get();
        return view('dashboard.consumption_method', compact('user','Organizations','ConsumptionGroups'));
    }

    public function AddConsumptionMethod(ConsumptionMethodRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->consumption_method)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('cm_org');
        $Desc = $request->input('cm_desc');
        $Criteria = $request->input('cm_criteria');
        $GroupId = $request->input('cm_group');
        $Edt = $request->input('cm_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
            $emailStatus = 'Active';
        } else {
            $status = 0;
            $emailStatus = 'Inactive';
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $CMExists = ConsumptionMethod::where('org_id', $Organization)
        ->where('description', $Desc)
        ->where('group_id', $GroupId)
        ->exists();

        if ($CMExists) {
            return response()->json(['info' => 'Consumption Method already exists.']);
        }
        else
        {
            $ConsumptionMethods = new ConsumptionMethod();
            $ConsumptionMethods->org_id = $Organization;
            $ConsumptionMethods->description = $Desc;
            $ConsumptionMethods->criteria = $Criteria;
            $ConsumptionMethods->group_id = $GroupId;
            $ConsumptionMethods->status = $status;
            $ConsumptionMethods->user_id = $sessionId;
            $ConsumptionMethods->last_updated = $last_updated;
            $ConsumptionMethods->timestamp = $timestamp;
            $ConsumptionMethods->effective_timestamp = $Edt;

            $ConsumptionMethods->save();

            if (empty($ConsumptionMethods->id)) {
                return response()->json(['error' => 'Failed to create Consumption Method.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$Desc}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ConsumptionMethods->logid = $logs->id;
            $ConsumptionMethods->save();
            return response()->json(['success' => 'Consumption Method Added successfully']);
        }
    }

    public function GetConsumptionMethodData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->consumption_method)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ConsumptionMethods = ConsumptionMethod::select('consumption_method.*',
        'organization.organization as orgName','consumption_group.description as groupDesc')
        ->join('organization', 'organization.id', '=', 'consumption_method.org_id')
        ->join('consumption_group', 'consumption_group.id', '=', 'consumption_method.group_id')
        ->orderBy('consumption_method.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ConsumptionMethods->where('consumption_method.org_id', '=', $sessionOrg);
        }
        $ConsumptionMethods = $ConsumptionMethods;
        // ->get()
        // return DataTables::of($Vendors)
        return DataTables::eloquent($ConsumptionMethods)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('consumption_method.description', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('consumption_method.criteria', 'like', "%{$search}%")
                        ->orWhere('consumption_group.description', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ConsumptionMethod) {
                return $ConsumptionMethod->id; 
            })
            ->editColumn('id', function ($ConsumptionMethod) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $Desc = $ConsumptionMethod->description;
                $effectiveDate = Carbon::createFromTimestamp($ConsumptionMethod->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ConsumptionMethod->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ConsumptionMethod->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ConsumptionMethod->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($ConsumptionMethod->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'CM';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Desc))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($ConsumptionMethod->orgName).'<br>';
                }
                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . $orgName
                    .'<b>Description:</b> '.ucwords($Desc).'<br>'
                    .'<b>Consumption Group:</b> '.ucwords($ConsumptionMethod->groupDesc).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('criteria', function ($ConsumptionMethod) {
                return ucwords($ConsumptionMethod->criteria);
            })
            ->addColumn('action', function ($ConsumptionMethod) {
                    $ConsumptionMethodId = $ConsumptionMethod->id;
                    $logId = $ConsumptionMethod->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->consumption_method)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-cm" data-cm-id="'.$ConsumptionMethodId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $ConsumptionMethod->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ConsumptionMethod) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->consumption_method)[3];
                return $updateStatus == 1 ? ($ConsumptionMethod->status ? '<span class="label label-success cm_status cursor-pointer" data-id="'.$ConsumptionMethod->id.'" data-status="'.$ConsumptionMethod->status.'">Active</span>' : '<span class="label label-danger cm_status cursor-pointer" data-id="'.$ConsumptionMethod->id.'" data-status="'.$ConsumptionMethod->status.'">Inactive</span>') : ($ConsumptionMethod->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','criteria',
            'id'])
            ->make(true);
    }

    public function UpdateConsumptionMethodStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->consumption_method)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ConsumptionMethodID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ConsumptionMethod = ConsumptionMethod::find($ConsumptionMethodID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ConsumptionMethod->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $ConsumptionMethod->status = $UpdateStatus;
        $ConsumptionMethod->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ConsumptionMethodLog = ConsumptionMethod::where('id', $ConsumptionMethodID)->first();
        $logIds = $ConsumptionMethodLog->logid ? explode(',', $ConsumptionMethodLog->logid) : [];
        $logIds[] = $logs->id;
        $ConsumptionMethodLog->logid = implode(',', $logIds);
        $ConsumptionMethodLog->save();

        $ConsumptionMethod->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateConsumptionMethodModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->consumption_method)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ConsumptionMethod = ConsumptionMethod::select('consumption_method.*',
        'organization.organization as orgName','consumption_group.description as groupDesc')
        ->join('organization', 'organization.id', '=', 'consumption_method.org_id')
        ->join('consumption_group', 'consumption_group.id', '=', 'consumption_method.group_id')
        ->where('consumption_method.id', '=', $id)
        ->first();

        $effective_timestamp = $ConsumptionMethod->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $ConsumptionMethod->org_id,
            'orgName' => ucwords($ConsumptionMethod->orgName),
            'desc' => ucwords($ConsumptionMethod->description),
            'criteria' => ucwords($ConsumptionMethod->criteria),
            'groupId' => ucwords($ConsumptionMethod->group_id),
            'groupDesc' => ucwords($ConsumptionMethod->groupDesc),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateConsumptionMethod(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->consumption_method)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ConsumptionMethod = ConsumptionMethod::findOrFail($id);
        $orgID= $request->input('u_cm_org');
        if (isset($orgID)) {
            $ConsumptionMethod->org_id = $orgID;
        }
        $ConsumptionMethod->description = $request->input('u_cm_desc');
        $ConsumptionMethod->criteria = $request->input('u_cm_criteria');
        $ConsumptionMethod->group_id = $request->input('u_cm_group');
        $effective_date = $request->input('u_cm_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ConsumptionMethod->effective_timestamp = $effective_date;
        $ConsumptionMethod->last_updated = $this->currentDatetime;
        $ConsumptionMethod->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ConsumptionMethod->save();

        if (empty($ConsumptionMethod->id)) {
            return response()->json(['error' => 'Failed to update Consumption Method. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ConsumptionMethodLog = ConsumptionGroup::where('id', $ConsumptionMethod->id)->first();
        $logIds = $ConsumptionMethodLog->logid ? explode(',', $ConsumptionMethodLog->logid) : [];
        $logIds[] = $logs->id;
        $ConsumptionMethodLog->logid = implode(',', $logIds);
        $ConsumptionMethodLog->save();
        return response()->json(['success' => 'Consumption Method updated successfully']);
    }

    public function ViewStockMonitoring()
    {
        $colName = 'stock_monitoring';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.stock_monitoring', compact('user','Organizations'));
    }

    public function AddStockMonitoring(StockMonitoringRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->stock_monitoring)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('sm_org');
        $Site = $request->input('sm_site');
        $Generic = $request->input('sm_generic');
        $Brand = $request->input('sm_brand');
        $ServiceLocation = $request->input('sm_location');
        $MinStock = $request->input('sm_min_stock');
        $MaxStock = $request->input('sm_max_stock');
        $MonthlyConsumption = $request->input('sm_monthly_consumption');
        $MinReorder = $request->input('sm_min_reorder');
        $PrimaryEmail = $request->input('sm_primary_email');
        $SecondaryEmail = $request->input('sm_secondary_email');

        
        $Edt = $request->input('sm_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
            $status = 0;
        }

        if ($MaxStock < $MinStock) {
            return response()->json(['error' => 'Maximum stock cannot be less than Minimum stock.']);
        }
        if ($MinReorder < $MinStock) {
            return response()->json(['error' => 'Minimum reorder quantity cannot be less than Minimum stock.']);
        }
        if ($PrimaryEmail === $SecondaryEmail) {
            return response()->json(['error' => 'Primary email and Secondary email must be different.']);
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $SMExists = StockMonitoring::where('org_id', $Organization)
        ->where('site_id', $Site)
        ->where('item_generic_id', $Generic)
        ->where('item_brand_id', $Brand)
        ->where('service_location_id', $ServiceLocation)
        ->exists();

        if ($SMExists) {
            return response()->json(['info' => 'Stock Monitoring already exists.']);
        }
        else
        {
            $StockMonitoring = new StockMonitoring();
            $StockMonitoring->org_id = $Organization;
            $StockMonitoring->site_id = $Site;
            $StockMonitoring->item_generic_id = $Generic;
            $StockMonitoring->item_brand_id = $Brand;
            $StockMonitoring->service_location_id = $ServiceLocation;
            $StockMonitoring->min_stock = $MinStock;
            $StockMonitoring->max_stock = $MaxStock;
            $StockMonitoring->monthly_consumption_ceiling = $MonthlyConsumption;
            $StockMonitoring->min_reorder_qty = $MinReorder;
            $StockMonitoring->primary_email = $PrimaryEmail;
            $StockMonitoring->secondary_email = $SecondaryEmail;
            $StockMonitoring->status = $status;
            $StockMonitoring->user_id = $sessionId;
            $StockMonitoring->last_updated = $last_updated;
            $StockMonitoring->timestamp = $timestamp;
            $StockMonitoring->effective_timestamp = $Edt;

            $StockMonitoring->save();

            if (empty($StockMonitoring->id)) {
                return response()->json(['error' => 'Failed to create Stock Monitoring.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "Stock Monitoring has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $StockMonitoring->logid = $logs->id;
            $StockMonitoring->save();
            return response()->json(['success' => 'Stock Monitoring Added successfully']);
        }
    }

    public function GetStockMonitoringData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->stock_monitoring)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $StockMonitoringData = StockMonitoring::select('stock_monitoring.*',
        'organization.organization as orgName','org_site.name as siteName',
        'inventory_generic.name as genericName','inventory_brand.name as brandName','service_location.name as serviceLocationName')
        ->join('organization', 'organization.id', '=', 'stock_monitoring.org_id')
        ->join('org_site', 'org_site.id', '=', 'stock_monitoring.site_id')
        ->join('inventory_generic', 'inventory_generic.id', '=', 'stock_monitoring.item_generic_id')
        ->leftJoin('inventory_brand', 'inventory_brand.id', '=', 'stock_monitoring.item_brand_id')
        ->join('service_location', 'service_location.id', '=', 'stock_monitoring.service_location_id')
        ->orderBy('stock_monitoring.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $StockMonitoringData->where('stock_monitoring.org_id', '=', $sessionOrg);
        }
        $StockMonitoringData = $StockMonitoringData;
        // ->get()
        // return DataTables::of($Vendors)
        return DataTables::eloquent($StockMonitoringData)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('stock_monitoring.min_stock', 'like', "%{$search}%")
                        ->orWhere('stock_monitoring.max_stock', 'like', "%{$search}%")
                        ->orWhere('stock_monitoring.monthly_consumption_ceiling', 'like', "%{$search}%")
                        ->orWhere('stock_monitoring.min_reorder_qty', 'like', "%{$search}%")
                        ->orWhere('stock_monitoring.primary_email', 'like', "%{$search}%")
                        ->orWhere('stock_monitoring.secondary_email', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('org_site.name', 'like', "%{$search}%")
                        ->orWhere('inventory_generic.name', 'like', "%{$search}%")
                        ->orWhere('inventory_brand.name', 'like', "%{$search}%")
                        ->orWhere('service_location.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($StockMonitoring) {
                return $StockMonitoring->id; 
            })
            ->editColumn('id', function ($StockMonitoring) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $effectiveDate = Carbon::createFromTimestamp($StockMonitoring->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($StockMonitoring->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($StockMonitoring->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($StockMonitoring->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($StockMonitoring->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'SM';
                $Code = $ModuleCode.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($StockMonitoring->orgName).'<br>';
                }
                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . $orgName
                    .'<b>Site Name:</b> '.ucwords($StockMonitoring->siteName)
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Item Generic:</b> '.ucwords($StockMonitoring->genericName).'<br>'
                    .'<b>Item Brand:</b> '. ucwords( $StockMonitoring->brandName ?? 'N/A' ).'<br>'
                    .'<b>Service Location:</b> '.ucwords($StockMonitoring->serviceLocationName).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('stock_details', function ($StockMonitoring) {
                return '<b>Min Stock:</b> '.($StockMonitoring->min_stock).'<br>'
                .'<b>Max Stock:</b> '.($StockMonitoring->max_stock).'<br>'
                .'<b>Monthly Consumption Ceiling:</b> '.($StockMonitoring->monthly_consumption_ceiling).'<br>'
                .'<b>Min Redorder Qty:</b> '.($StockMonitoring->min_reorder_qty);
            })
            ->editColumn('contact_details', function ($StockMonitoring) {
                return 
                '<b>Primary Email:</b> '.($StockMonitoring->primary_email).'<br>'
                .'<b>Secondary Email:</b> '.($StockMonitoring->secondary_email);
            })
            ->addColumn('action', function ($StockMonitoring) {
                    $StockMonitoringId = $StockMonitoring->id;
                    $logId = $StockMonitoring->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->stock_monitoring)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-sm" data-sm-id="'.$StockMonitoringId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $StockMonitoring->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($StockMonitoring) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->stock_monitoring)[3];
                return $updateStatus == 1 ? ($StockMonitoring->status ? '<span class="label label-success sm_status cursor-pointer" data-id="'.$StockMonitoring->id.'" data-status="'.$StockMonitoring->status.'">Active</span>' : '<span class="label label-danger sm_status cursor-pointer" data-id="'.$StockMonitoring->id.'" data-status="'.$StockMonitoring->status.'">Inactive</span>') : ($StockMonitoring->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','stock_details','contact_details',
            'id'])
            ->make(true);
    }

    public function UpdateStockMonitoringStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->stock_monitoring)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $StockMonitoringID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $StockMonitoring = StockMonitoring::find($StockMonitoringID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $StockMonitoring->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $StockMonitoring->status = $UpdateStatus;
        $StockMonitoring->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $StockMonitoringLog = StockMonitoring::where('id', $StockMonitoringID)->first();
        $logIds = $StockMonitoringLog->logid ? explode(',', $StockMonitoringLog->logid) : [];
        $logIds[] = $logs->id;
        $StockMonitoringLog->logid = implode(',', $logIds);
        $StockMonitoringLog->save();

        $StockMonitoring->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateStockMonitoringModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->stock_monitoring)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
   
        $StockMonitoring = StockMonitoring::select('stock_monitoring.*',
        'organization.organization as orgName','org_site.name as siteName',
        'inventory_generic.name as genericName','inventory_brand.name as brandName','service_location.name as serviceLocationName')
        ->join('organization', 'organization.id', '=', 'stock_monitoring.org_id')
        ->join('org_site', 'org_site.id', '=', 'stock_monitoring.site_id')
        ->join('inventory_generic', 'inventory_generic.id', '=', 'stock_monitoring.item_generic_id')
        ->join('inventory_brand', 'inventory_brand.id', '=', 'stock_monitoring.item_brand_id')
        ->join('service_location', 'service_location.id', '=', 'stock_monitoring.service_location_id')
        ->where('stock_monitoring.id', '=', $id)
        ->first();

        $effective_timestamp = $StockMonitoring->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $StockMonitoring->org_id,
            'orgName' => ucwords($StockMonitoring->orgName),
            'siteId' => $StockMonitoring->site_id,
            'siteName' => ucwords($StockMonitoring->siteName),
            'genericId' => $StockMonitoring->item_generic_id,
            'genericName' => ucwords($StockMonitoring->genericName),
            'brandId' => $StockMonitoring->item_brand_id,
            'brandName' => ucwords($StockMonitoring->brandName),
            'serviceLocationId' => $StockMonitoring->service_location_id,
            'serviceLocation' => ucwords($StockMonitoring->serviceLocationName),
            'minStock' => ($StockMonitoring->min_stock),
            'maxStock' => ($StockMonitoring->max_stock),
            'monthlyConsumption' => ($StockMonitoring->monthly_consumption_ceiling),
            'minReorder' => ($StockMonitoring->min_reorder_qty),
            'PrimaryEmail' => ($StockMonitoring->primary_email),
            'secondaryEmail' => ($StockMonitoring->secondary_email),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateStockMonitoring(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->stock_monitoring)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $StockMonitoring = StockMonitoring::findOrFail($id);
        $orgID = $request->input('u_sm_org');

        if (isset($orgID)) {
            $StockMonitoring->org_id = $orgID;
        }
        $StockMonitoring->site_id = $request->input('u_sm_site');
        $StockMonitoring->item_generic_id = $request->input('u_sm_generic');
        $StockMonitoring->item_brand_id = $request->input('u_sm_brand');
        $StockMonitoring->service_location_id = $request->input('u_sm_servicelocation');
       
        $effective_date = $request->input('u_sm_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        $minStock = $request->input('u_sm_min_stock');
        $maxStock = $request->input('u_sm_max_stock');
        $MonthlyConsumption = $request->input('u_sm_monthly_consumption');
        $minReorder = $request->input('u_sm_min_reorder');
        $PrimaryEmail = $request->input('u_sm_primary_email');
        $SecondaryEmail = $request->input('u_sm_secondary_email');

        
        if ($maxStock < $minStock) {
            return response()->json(['error' => 'Maximum stock cannot be less than Minimum stock.']);
        }
        if ($minReorder < $minStock) {
            return response()->json(['error' => 'Minimum reorder quantity cannot be less than Minimum stock.']);
        }
        if ($PrimaryEmail === $SecondaryEmail) {
            return response()->json(['error' => 'Primary email and Secondary email must be different.']);
        }

        $StockMonitoring->min_stock = $minStock;
        $StockMonitoring->max_stock = $maxStock;
        $StockMonitoring->monthly_consumption_ceiling = $MonthlyConsumption;
        $StockMonitoring->min_reorder_qty = $minReorder;
        $StockMonitoring->primary_email = $PrimaryEmail;
        $StockMonitoring->secondary_email = $SecondaryEmail;

        if ($EffectDateTime->isPast()) {
            $status = 1; 
        } else {
             $status = 0; 
        }

        $StockMonitoring->effective_timestamp = $effective_date;
        $StockMonitoring->last_updated = $this->currentDatetime;
        $StockMonitoring->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $StockMonitoring->save();

        if (empty($StockMonitoring->id)) {
            return response()->json(['error' => 'Failed to update Stock Monitoring. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $StockMonitoringLog = StockMonitoring::where('id', $StockMonitoring->id)->first();
        $logIds = $StockMonitoringLog->logid ? explode(',', $StockMonitoringLog->logid) : [];
        $logIds[] = $logs->id;
        $StockMonitoringLog->logid = implode(',', $logIds);
        $StockMonitoringLog->save();
        return response()->json(['success' => 'Stock Monitoring updated successfully']);
    }

    public function InventorySourceDestinationType()
    {
        $colName = 'inventory_source_destination_type';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.inventory-source-destination-type', compact('user','Organizations'));
    }

    public function AddInventorySourceDestinationType(InventorySourceDestinationTypeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->inventory_source_destination_type)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('invsdt_org');
        $Type = $request->input('invsd_type');
        $ThirdPartyStatus = $request->input('invsdt_tps');
        $Edt = $request->input('invsdt_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
            $status = 0;
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InvSDTExists = InventorySourceDestinationType::where('org_id', $Organization)
        ->where('name', $Type)
        ->exists();

        if ($InvSDTExists) {
            return response()->json(['info' => 'Inventory Source Destination Type already exists.']);
        }
        else
        {
            $InventorySourceDestinationType = new InventorySourceDestinationType();
            $InventorySourceDestinationType->org_id = $Organization;
            $InventorySourceDestinationType->name = $Type;
            $InventorySourceDestinationType->third_party = $ThirdPartyStatus;
            $InventorySourceDestinationType->status = $status;
            $InventorySourceDestinationType->user_id = $sessionId;
            $InventorySourceDestinationType->last_updated = $last_updated;
            $InventorySourceDestinationType->timestamp = $timestamp;
            $InventorySourceDestinationType->effective_timestamp = $Edt;

            $InventorySourceDestinationType->save();

            if (empty($InventorySourceDestinationType->id)) {
                return response()->json(['error' => 'Failed to create Inventory Source Destination Type.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "Inventory Source Destination Type has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventorySourceDestinationType->logid = $logs->id;
            $InventorySourceDestinationType->save();
            return response()->json(['success' => 'Inventory Source Destination Type Added successfully']);
        }
    }

    public function GetInventorySourceDestinationTypeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->inventory_source_destination_type)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventorySourceDestinationTypeData = InventorySourceDestinationType::select('inventory_source_destination_type.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'inventory_source_destination_type.org_id')
        ->orderBy('inventory_source_destination_type.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventorySourceDestinationTypeData->where('inventory_source_destination_type.org_id', '=', $sessionOrg);
        }
        $InventorySourceDestinationTypeData = $InventorySourceDestinationTypeData;
        // ->get()
        // return DataTables::of($Vendors)
        return DataTables::eloquent($InventorySourceDestinationTypeData)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_source_destination_type.name', 'like', "%{$search}%")
                        ->orWhere('inventory_source_destination_type.third_party', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventorySourceDestinationType) {
                return $InventorySourceDestinationType->id; 
            })
            ->editColumn('id', function ($InventorySourceDestinationType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $effectiveDate = Carbon::createFromTimestamp($InventorySourceDestinationType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventorySourceDestinationType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventorySourceDestinationType->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventorySourceDestinationType->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($InventorySourceDestinationType->id, 5, "0", STR_PAD_LEFT);
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventorySourceDestinationType->name))));

                $Code = $firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($InventorySourceDestinationType->orgName).'<br>';
                }
                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . $orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('type', function ($InventorySourceDestinationType) {
                return ucwords($InventorySourceDestinationType->name);
            })
            ->editColumn('third_party_status', function ($InventorySourceDestinationType) {
                return $InventorySourceDestinationType->third_party === 'y' ? 'Yes' : ($InventorySourceDestinationType->third_party === 'n' ? 'No' : 'Invalid value');
            })
            ->addColumn('action', function ($InventorySourceDestinationType) {
                    $InventorySourceDestinationTypeId = $InventorySourceDestinationType->id;
                    $logId = $InventorySourceDestinationType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->inventory_source_destination_type)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-invsdt" data-invsdt-id="'.$InventorySourceDestinationTypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventorySourceDestinationType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventorySourceDestinationType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->inventory_source_destination_type)[3];
                return $updateStatus == 1 ? ($InventorySourceDestinationType->status ? '<span class="label label-success invsdt_status cursor-pointer" data-id="'.$InventorySourceDestinationType->id.'" data-status="'.$InventorySourceDestinationType->status.'">Active</span>' : '<span class="label label-danger invsdt_status cursor-pointer" data-id="'.$InventorySourceDestinationType->id.'" data-status="'.$InventorySourceDestinationType->status.'">Inactive</span>') : ($InventorySourceDestinationType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','type','third_party_status',
            'id'])
            ->make(true);
    }

    public function UpdateInventorySourceDestinationTypeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->inventory_source_destination_type)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventorySourceDestinationTypeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventorySourceDestinationType = InventorySourceDestinationType::find($InventorySourceDestinationTypeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventorySourceDestinationType->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventorySourceDestinationType->status = $UpdateStatus;
        $InventorySourceDestinationType->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventorySourceDestinationTypeLog = InventorySourceDestinationType::where('id', $InventorySourceDestinationTypeID)->first();
        $logIds = $InventorySourceDestinationTypeLog->logid ? explode(',', $InventorySourceDestinationTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $InventorySourceDestinationTypeLog->logid = implode(',', $logIds);
        $InventorySourceDestinationTypeLog->save();

        $InventorySourceDestinationType->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventorySourceDestinationTypeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->inventory_source_destination_type)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $InventorySourceDestinationType = InventorySourceDestinationType::select('inventory_source_destination_type.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'inventory_source_destination_type.org_id')
        ->where('inventory_source_destination_type.id', '=', $id)
        ->first();

        $effective_timestamp = $InventorySourceDestinationType->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $InventorySourceDestinationType->org_id,
            'orgName' => ucwords($InventorySourceDestinationType->orgName),
            'type' => ucwords($InventorySourceDestinationType->name),
            'third_party' => ($InventorySourceDestinationType->third_party),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateInventorySourceDestinationType(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->inventory_source_destination_type)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventorySourceDestinationType = InventorySourceDestinationType::findOrFail($id);
        $orgID = $request->input('u_invsdt_org');

        if (isset($orgID)) {
            $InventorySourceDestinationType->org_id = $orgID;
        }
        $InventorySourceDestinationType->name = $request->input('u_invsd_type');
        $InventorySourceDestinationType->third_party = $request->input('u_invsdt_tps');
        $effective_date = $request->input('u_invsd_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; 
        } else {
             $status = 0; 
        }

        $InventorySourceDestinationType->effective_timestamp = $effective_date;
        $InventorySourceDestinationType->last_updated = $this->currentDatetime;
        $InventorySourceDestinationType->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventorySourceDestinationType->save();

        if (empty($InventorySourceDestinationType->id)) {
            return response()->json(['error' => 'Failed to update Inventory Source Destination Type. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventorySourceDestinationTypeLog = InventorySourceDestinationType::where('id', $InventorySourceDestinationType->id)->first();
        $logIds = $InventorySourceDestinationTypeLog->logid ? explode(',', $InventorySourceDestinationTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $InventorySourceDestinationTypeLog->logid = implode(',', $logIds);
        $InventorySourceDestinationTypeLog->save();
        return response()->json(['success' => 'Inventory Source Destination Type updated successfully']);
    }

    public function InventoryTransactionActivity()
    {
        $colName = 'inventory_transaction_activity';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.inventory-transaction-activity', compact('user','Organizations'));
    }

    public function AddInventoryTransactionActivity(InventoryTransactionActivityRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->inventory_transaction_activity)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('invta_org');
        $Name = $request->input('inv_ta');
        $Edt = $request->input('invta_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
            $status = 0;
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $InvTAExists = InventoryTransactionActivity::where('org_id', $Organization)
        ->where('name', $Name)
        ->exists();

        if ($InvTAExists) {
            return response()->json(['info' => 'Inventory Transaction Activity already exists.']);
        }
        else
        {
            $InventoryTransactionActivity = new InventoryTransactionActivity();
            $InventoryTransactionActivity->org_id = $Organization;
            $InventoryTransactionActivity->name = $Name;
            $InventoryTransactionActivity->status = $status;
            $InventoryTransactionActivity->user_id = $sessionId;
            $InventoryTransactionActivity->last_updated = $last_updated;
            $InventoryTransactionActivity->timestamp = $timestamp;
            $InventoryTransactionActivity->effective_timestamp = $Edt;

            $InventoryTransactionActivity->save();

            if (empty($InventoryTransactionActivity->id)) {
                return response()->json(['error' => 'Failed to create Inventory Transaction Activity.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "Inventory Transaction Activity has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $InventoryTransactionActivity->logid = $logs->id;
            $InventoryTransactionActivity->save();
            return response()->json(['success' => 'Inventory Transaction Activity Added successfully']);
        }
    }

    public function GetInventoryTransactionActivity(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->inventory_transaction_activity)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTransactionActivities = InventoryTransactionActivity::select('inventory_transaction_activity.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'inventory_transaction_activity.org_id')
        ->orderBy('inventory_transaction_activity.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryTransactionActivities->where('inventory_transaction_activity.org_id', '=', $sessionOrg);
        }
        $InventoryTransactionActivities = $InventoryTransactionActivities;
        // ->get()
        // return DataTables::of($Vendors)
        return DataTables::eloquent($InventoryTransactionActivities)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('inventory_transaction_activity.name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($InventoryTransactionActivity) {
                return $InventoryTransactionActivity->id; 
            })
            ->editColumn('id', function ($InventoryTransactionActivity) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $effectiveDate = Carbon::createFromTimestamp($InventoryTransactionActivity->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($InventoryTransactionActivity->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($InventoryTransactionActivity->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($InventoryTransactionActivity->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($InventoryTransactionActivity->id, 5, "0", STR_PAD_LEFT);
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $InventoryTransactionActivity->name))));

                $Code = $firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($InventoryTransactionActivity->orgName).'<br>';
                }
                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . $orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('desc', function ($InventoryTransactionActivity) {
                return ucwords($InventoryTransactionActivity->name);
            })
            ->addColumn('action', function ($InventoryTransactionActivity) {
                    $InventoryTransactionActivityId = $InventoryTransactionActivity->id;
                    $logId = $InventoryTransactionActivity->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->inventory_transaction_activity)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-invta" data-invta-id="'.$InventoryTransactionActivityId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $InventoryTransactionActivity->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($InventoryTransactionActivity) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->inventory_transaction_activity)[3];
                return $updateStatus == 1 ? ($InventoryTransactionActivity->status ? '<span class="label label-success invta_status cursor-pointer" data-id="'.$InventoryTransactionActivity->id.'" data-status="'.$InventoryTransactionActivity->status.'">Active</span>' : '<span class="label label-danger invta_status cursor-pointer" data-id="'.$InventoryTransactionActivity->id.'" data-status="'.$InventoryTransactionActivity->status.'">Inactive</span>') : ($InventoryTransactionActivity->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','desc',
            'id'])
            ->make(true);
    }

    public function UpdateInventoryTransactionActivityStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->inventory_transaction_activity)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTransactionActivityID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $InventoryTransactionActivity = InventoryTransactionActivity::find($InventoryTransactionActivityID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $InventoryTransactionActivity->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $InventoryTransactionActivity->status = $UpdateStatus;
        $InventoryTransactionActivity->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryTransactionActivityLog = InventoryTransactionActivity::where('id', $InventoryTransactionActivityID)->first();
        $logIds = $InventoryTransactionActivityLog->logid ? explode(',', $InventoryTransactionActivityLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryTransactionActivityLog->logid = implode(',', $logIds);
        $InventoryTransactionActivityLog->save();

        $InventoryTransactionActivity->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateInventoryTransactionActivityModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->inventory_transaction_activity)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $InventoryTransactionActivity = InventoryTransactionActivity::select('inventory_transaction_activity.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'inventory_transaction_activity.org_id')
        ->where('inventory_transaction_activity.id', '=', $id)
        ->first();

        $effective_timestamp = $InventoryTransactionActivity->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $InventoryTransactionActivity->org_id,
            'orgName' => ucwords($InventoryTransactionActivity->orgName),
            'desc' => ucwords($InventoryTransactionActivity->name),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateInventoryTransactionActivity(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->inventory_transaction_activity)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $InventoryTransactionActivity = InventoryTransactionActivity::findOrFail($id);
        $orgID = $request->input('u_invta_org');

        if (isset($orgID)) {
            $InventoryTransactionActivity->org_id = $orgID;
        }
        $InventoryTransactionActivity->name = $request->input('u_invtransactionactivity');
        $effective_date = $request->input('u_invta_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; 
        } else {
             $status = 0; 
        }

        $InventoryTransactionActivity->effective_timestamp = $effective_date;
        $InventoryTransactionActivity->last_updated = $this->currentDatetime;
        $InventoryTransactionActivity->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $InventoryTransactionActivity->save();

        if (empty($InventoryTransactionActivity->id)) {
            return response()->json(['error' => 'Failed to update Inventory Transaction Activity. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $InventoryTransactionActivityLog = InventoryTransactionActivity::where('id', $InventoryTransactionActivity->id)->first();
        $logIds = $InventoryTransactionActivityLog->logid ? explode(',', $InventoryTransactionActivityLog->logid) : [];
        $logIds[] = $logs->id;
        $InventoryTransactionActivityLog->logid = implode(',', $logIds);
        $InventoryTransactionActivityLog->save();
        return response()->json(['success' => 'Inventory Transaction Activity updated successfully']);
    }

    public function ViewMedicationRoutes()
    {
        $colName = 'medication_routes';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.medication-routes', compact('user'));
    }

    public function AddMedicationRoutes(MedicationRoutesRegistration $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->medication_routes)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationRouteDescription = trim($request->input('medication_route'));
        $Organization = $request->input('medicationroute_org');
        $Edt = $request->input('medicationroute_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active

        } else {
            $status = 0; //Inactive
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $MedicationRouteExists = MedicationRoutes::where('name', $MedicationRouteDescription)
        ->where('org_id', $Organization)
        ->exists();

        if ($MedicationRouteExists) {
            return response()->json(['info' => 'Medication Route already exists.']);
        }
        else
        {
            $MedicationRoute = new MedicationRoutes();
            $MedicationRoute->name = $MedicationRouteDescription;
            $MedicationRoute->org_id = $Organization;
            $MedicationRoute->status = $status;
            $MedicationRoute->user_id = $sessionId;
            $MedicationRoute->last_updated = $last_updated;
            $MedicationRoute->timestamp = $timestamp;
            $MedicationRoute->effective_timestamp = $Edt;
            $MedicationRoute->save();

            if (empty($MedicationRoute->id)) {
                return response()->json(['error' => 'Failed to create Medication Route.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$MedicationRouteDescription}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $MedicationRoute->logid = $logs->id;
            $MedicationRoute->save();
            return response()->json(['success' => 'Medication Route added successfully']);
        }
    }

    public function GetMedicationRouteData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->medication_routes)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationRoutes = MedicationRoutes::select('medication_routes.*',
        'organization.organization as orgName','organization.code as orgCode')
        ->join('organization', 'organization.id', '=', 'medication_routes.org_id')
        ->orderBy('medication_routes.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $MedicationRoutes->where('medication_routes.org_id', '=', $sessionOrg);
        }
        $MedicationRoutes = $MedicationRoutes;
        // ->get()
        // return DataTables::of($MedicationRoutes)
        return DataTables::eloquent($MedicationRoutes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('medication_routes.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($MedicationRoute) {
                return $MedicationRoute->id;
            })
            ->editColumn('id', function ($MedicationRoute) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $MedicationRouteDescription = $MedicationRoute->name;
                $effectiveDate = Carbon::createFromTimestamp($MedicationRoute->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($MedicationRoute->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($MedicationRoute->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($MedicationRoute->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($MedicationRoute->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'MDR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $MedicationRouteDescription))));
                $Code = $ModuleCode.'-'.$MedicationRoute->orgCode.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($MedicationRoute->orgName);
                }
                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($MedicationRoute) {
                    $MedicationRouteId = $MedicationRoute->id;
                    $logId = $MedicationRoute->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->medication_routes)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-medicationRoute" data-medicationroute-id="'.$MedicationRouteId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $MedicationRoute->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($MedicationRoute) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->medication_routes)[3];
                return $updateStatus == 1 ? ($MedicationRoute->status ? '<span class="label label-success medicationRoute_status cursor-pointer" data-id="'.$MedicationRoute->id.'" data-status="'.$MedicationRoute->status.'">Active</span>' : '<span class="label label-danger medicationRoute_status cursor-pointer" data-id="'.$MedicationRoute->id.'" data-status="'.$MedicationRoute->status.'">Inactive</span>') : ($MedicationRoute->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateMedicationRouteStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->medication_routes)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationRouteID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $MedicationRoute = MedicationRoutes::find($MedicationRouteID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $MedicationRoute->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $MedicationRoute->status = $UpdateStatus;
        $MedicationRoute->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $MedicationRouteLog = MedicationRoutes::where('id', $MedicationRouteID)->first();
        $logIds = $MedicationRouteLog->logid ? explode(',', $MedicationRouteLog->logid) : [];
        $logIds[] = $logs->id;
        $MedicationRouteLog->logid = implode(',', $logIds);
        $MedicationRouteLog->save();

        $MedicationRoute->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateMedicationRoutesModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->medication_routes)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationRoute = MedicationRoutes::select('medication_routes.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'medication_routes.org_id')
        ->where('medication_routes.id', '=', $id)
        ->first();

        $effective_timestamp = $MedicationRoute->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => ucwords($MedicationRoute->name),
            'orgId' => $MedicationRoute->org_id,
            'orgName' => ucwords($MedicationRoute->orgName),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateMedicationRoutes(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->medication_routes)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationRoute = MedicationRoutes::findOrFail($id);
        $MedicationRoute->name = $request->input('u_medicationroute');
        $orgID = $request->input('u_medicationroute_org');
        if (isset($orgID)) {
            $MedicationRoute->org_id = $orgID;
        }  
        $effective_date = $request->input('u_medicationroute_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $MedicationRoute->effective_timestamp = $effective_date;
        $MedicationRoute->last_updated = $this->currentDatetime;
        $MedicationRoute->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $MedicationRoute->save();

        if (empty($MedicationRoute->id)) {
            return response()->json(['error' => 'Failed to update Medication Routes. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $MedicationRouteLog = MedicationRoutes::where('id', $MedicationRoute->id)->first();
        $logIds = $MedicationRouteLog->logid ? explode(',', $MedicationRouteLog->logid) : [];
        $logIds[] = $logs->id;
        $MedicationRouteLog->logid = implode(',', $logIds);
        $MedicationRouteLog->save();
        return response()->json(['success' => 'Medication Routes Details updated successfully']);
    }

    public function ViewMedicationFrequency()
    {
        $colName = 'medication_frequency';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.medication-frequency', compact('user'));
    }

    public function AddMedicationFrequency(MedicationFrequencyRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->medication_frequency)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationFrequencyDescription = trim($request->input('medication_frequency'));
        $Organization = $request->input('medicationfrequency_org');
        $Edt = $request->input('medicationfrequency_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active

        } else {
            $status = 0; //Inactive
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $MedicationFrequencyExists = MedicationFrequency::where('name', $MedicationFrequencyDescription)
        ->where('org_id', $Organization)
        ->exists();

        if ($MedicationFrequencyExists) {
            return response()->json(['info' => 'Medication Frequency already exists.']);
        }
        else
        {
            $MedicationFrequency = new MedicationFrequency();
            $MedicationFrequency->name = $MedicationFrequencyDescription;
            $MedicationFrequency->org_id = $Organization;
            $MedicationFrequency->status = $status;
            $MedicationFrequency->user_id = $sessionId;
            $MedicationFrequency->last_updated = $last_updated;
            $MedicationFrequency->timestamp = $timestamp;
            $MedicationFrequency->effective_timestamp = $Edt;
            $MedicationFrequency->save();

            if (empty($MedicationFrequency->id)) {
                return response()->json(['error' => 'Failed to create Medication Frequency.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "'{$MedicationFrequencyDescription}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $MedicationFrequency->logid = $logs->id;
            $MedicationFrequency->save();
            return response()->json(['success' => 'Medication Frequency added successfully']);
        }
    }

    public function GetMedicationFrequencyData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->medication_frequency)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationFrequencies = MedicationFrequency::select('medication_frequency.*',
        'organization.organization as orgName','organization.code as orgCode')
        ->join('organization', 'organization.id', '=', 'medication_frequency.org_id')
        ->orderBy('medication_frequency.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $MedicationFrequencies->where('medication_frequency.org_id', '=', $sessionOrg);
        }
        $MedicationFrequencies = $MedicationFrequencies;
        // ->get()
        // return DataTables::of($MedicationFrequencies)
        return DataTables::eloquent($MedicationFrequencies)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('medication_frequency.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('medication_frequency.description', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($MedicationFrequency) {
                return $MedicationFrequency->id;
            })
            ->editColumn('id', function ($MedicationFrequency) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $MedicationFrequencyDescription = $MedicationFrequency->name;
                $effectiveDate = Carbon::createFromTimestamp($MedicationFrequency->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($MedicationFrequency->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($MedicationFrequency->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($MedicationFrequency->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($MedicationFrequency->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'MFR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $MedicationFrequencyDescription))));
                $Code = $ModuleCode.'-'.$MedicationFrequency->orgCode.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($MedicationFrequency->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($MedicationFrequency) {
                    $MedicationFrequencyId = $MedicationFrequency->id;
                    $logId = $MedicationFrequency->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->medication_frequency)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-medicationfrequency" data-medicationfrequency-id="'.$MedicationFrequencyId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $MedicationFrequency->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($MedicationFrequency) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->medication_frequency)[3];
                return $updateStatus == 1 ? ($MedicationFrequency->status ? '<span class="label label-success medicationfrequency_status cursor-pointer" data-id="'.$MedicationFrequency->id.'" data-status="'.$MedicationFrequency->status.'">Active</span>' : '<span class="label label-danger medicationfrequency_status cursor-pointer" data-id="'.$MedicationFrequency->id.'" data-status="'.$MedicationFrequency->status.'">Inactive</span>') : ($MedicationFrequency->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateMedicationFrequencyStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->medication_frequency)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationFrequencyID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $MedicationFrequency = MedicationFrequency::find($MedicationFrequencyID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $MedicationFrequency->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $MedicationFrequency->status = $UpdateStatus;
        $MedicationFrequency->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $MedicationFrequencyLog = MedicationFrequency::where('id', $MedicationFrequencyID)->first();
        $logIds = $MedicationFrequencyLog->logid ? explode(',', $MedicationFrequencyLog->logid) : [];
        $logIds[] = $logs->id;
        $MedicationFrequencyLog->logid = implode(',', $logIds);
        $MedicationFrequencyLog->save();

        $MedicationFrequency->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateMedicationFrequencyModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->medication_frequency)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationFrequency = MedicationFrequency::select('medication_frequency.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'medication_frequency.org_id')
        ->where('medication_frequency.id', '=', $id)
        ->first();

        $effective_timestamp = $MedicationFrequency->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => ucwords($MedicationFrequency->name),
            'orgId' => $MedicationFrequency->org_id,
            'orgName' => ucwords($MedicationFrequency->orgName),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateMedicationFrequency(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->medication_frequency)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $MedicationFrequency = MedicationFrequency::findOrFail($id);
        $MedicationFrequency->name = $request->input('u_medicationfrequency');
        $orgID = $request->input('u_medicationfrequency_org');
        if (isset($orgID)) {
            $MedicationFrequency->org_id = $orgID;
        }  
        $effective_date = $request->input('u_medicationfrequency_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $MedicationFrequency->effective_timestamp = $effective_date;
        $MedicationFrequency->last_updated = $this->currentDatetime;
        $MedicationFrequency->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $MedicationFrequency->save();

        if (empty($MedicationFrequency->id)) {
            return response()->json(['error' => 'Failed to update Medication Frequency. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $MedicationFrequencyLog = MedicationFrequency::where('id', $MedicationFrequency->id)->first();
        $logIds = $MedicationFrequencyLog->logid ? explode(',', $MedicationFrequencyLog->logid) : [];
        $logIds[] = $logs->id;
        $MedicationFrequencyLog->logid = implode(',', $logIds);
        $MedicationFrequencyLog->save();
        return response()->json(['success' => 'Medication Frequency Details updated successfully']);
    }

    public function InventoryMaterialConsumption()
    {
        $colName = 'requisition_for_material_consumption';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $ServiceLocations = ServiceLocation::select('id', 'name')->where('status', 1)->get();
        $Generics = InventoryGeneric::select('inventory_generic.id', 'inventory_generic.name')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_generic.cat_id')
        ->where('inventory_generic.status', 1)
        ->where('inventory_category.name', 'not like', 'Medicine%')
        ->get();
        return view('dashboard.material-consumption', compact('user','ServiceLocations','Generics'));
    }

    public function GetTransactionTypes(Request $request)
    {
        $orgId = $request->input('orgId');
        $condition = $request->input('condition');
        $TransactionTypes = InventoryTransactionType::where('status', 1);
        if ($orgId !== 'null') {
            $TransactionTypes->where('org_id', $orgId);
        }
        // if ($condition == 'true') {
        //     $TransactionTypes->whereIn('transaction_type', ['general consumption', 'patient consumption']);
        // }
        if ($condition == 'true') {
            $TransactionTypes->where('request_mandatory', 'y');
        }
        $TransactionTypes = $TransactionTypes->orderBy('id', 'ASC')->get();
        return response()->json($TransactionTypes);
    }
    
    // public function GetMaterialManagementTransactionTypes(Request $request)
    // {
    //     $orgId = $request->input('orgId');
    //     $condition = $request->input('condition');

    //     $query = DB::table('inventory_transaction_type AS itt')
    //         ->select(
    //             'itt.id',
    //             'itt.name'
    //         )
    //         ->where('itt.status', 1);

    //     if ($orgId !== 'null') {
    //         $query->where('itt.org_id', $orgId);
    //     }
    //     if ($condition === 'external_transaction') {
    //         $query->join(
    //             'inventory_transaction_activity AS ita',
    //             'ita.id',
    //             '=',
    //             'itt.activity_type'
    //         )
    //         ->where('ita.name', 'LIKE', '%External%');
    //     }
    //     if ($condition === 'issue_dispense') {
    //         $query->join(
    //             'inventory_transaction_activity AS ita',
    //             'ita.id',
    //             '=',
    //             'itt.activity_type'
    //         )
    //         ->where('ita.name', 'LIKE', '%Issue%')
    //         ->where('ita.name', 'LIKE', '%Dispense%');
    //     }
    //     $query->orderBy('itt.id', 'ASC');
    //     $TransactionTypes = $query->get();

    //     return response()->json($TransactionTypes);
    // }

    public function GetMaterialManagementTransactionTypes(Request $request)
    {
        $orgId = $request->input('orgId');
        $condition = $request->input('condition');
        $request_mandatory = $request->input('request');

        $query = DB::table('inventory_transaction_type AS itt')
            ->select(
                'itt.id',
                'itt.name'
            )
            ->where('itt.status', 1);

        if ($orgId !== 'null') {
            $query->where('itt.org_id', $orgId);
        }
        if ($condition === 'external_transaction') {
            $query->join(
                'inventory_transaction_activity AS ita',
                'ita.id',
                '=',
                'itt.activity_type'
            )
            ->where('ita.name', 'LIKE', '%External%');
        }
        if ($condition === 'issue_dispense') {
            $query->join(
                'inventory_transaction_activity AS ita',
                'ita.id',
                '=',
                'itt.activity_type'
            )
            ->where('ita.name', 'LIKE', '%Issue%')
            ->where('ita.name', 'LIKE', '%Dispense%');
        }
        if ($request_mandatory === 'y') {
            $query->where('itt.request_mandatory', 'y');
        }
        elseif ($request_mandatory === 'n') {
            $query->where('itt.request_mandatory', 'n');
        }
        $query->orderBy('itt.id', 'ASC');
        $TransactionTypes = $query->get();

        return response()->json($TransactionTypes);
    }


    public function AddMaterialConsumptionRequisition(MaterialConsumptionRequisitionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->requisition_for_material_consumption)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('mc_org');
        $Site = $request->input('mc_site');
        $TransactionType = $request->input('mc_transactiontype');
        $InvLocation = $request->input('mc_inv_location');
      
        $Remarks = trim($request->input('mc_remarks'));

        $PatientMR = $request->input('mc_patient');
        $Service = $request->input('mc_service');
        

        if (!empty($PatientMR) && !empty($Service)) {

            $PatientDetails = PatientRegistration::select(
                'gender.id as genderId', 'patient.dob as patientDOB',
                'employee.id as empID',
                'patient_inout.org_id as OrgId', 'patient_inout.site_id as SiteId', 
                'service_mode.id as serviceModeId', 'billingCC.id as billingCCId', 'service_group.id as serviceGroupId',
                'service_type.id as serviceTypeId'
            )
            ->join('patient_inout', 'patient_inout.mr_code', '=', 'patient.mr_code')
            ->join('gender', 'gender.id', '=', 'patient.gender_id')
            ->join('costcenter as billingCC', 'billingCC.id', '=', 'patient_inout.billing_cc')
            ->join('employee', 'employee.id', '=', 'patient_inout.emp_id')
            ->join('service_mode', 'service_mode.id', '=', 'patient_inout.service_mode_id')
            ->join('services', 'services.id', '=', 'patient_inout.service_id')
            ->join('service_group', 'service_group.id', '=', 'services.group_id')
            ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->where('patient.status', 1)
            ->where('patient_inout.status', 1)
            ->where('patient_inout.service_id', $Service)
            ->where('patient.mr_code', $PatientMR)
            ->first();

           

            if ($PatientDetails) {
                $dob = Carbon::createFromTimestamp($PatientDetails->patientDOB);
                $now = Carbon::now();
                $diff = $dob->diff($now);
            
                $years = $diff->y;
                $months = $diff->m;
                $days = $diff->d;
            
                $ageString = "";
                if ($years > 0) {
                    $ageString .= $years . " " . ($years == 1 ? "year" : "years");
                }
                if ($months > 0) {
                    $ageString .= " " . $months . " " . ($months == 1 ? "month" : "months");
                }
                if ($days > 0) {
                    $ageString .= " " . $days . " " . ($days == 1 ? "day" : "days");
                }
                $Age = $ageString;
                $genderId = $PatientDetails->genderId;
                $serviceModeId = $PatientDetails->serviceModeId;
                $ResponsiblePhysician = $PatientDetails->empID;
                $billingCCId = $PatientDetails->billingCCId;
            }
        }
        else{
            $Age = null;
            $genderId = null;
            $serviceModeId = null;
            $ResponsiblePhysician = null;
            $billingCCId = null;
        }
       
        $itemGeneric =  implode(',',($request->input('mc_itemgeneric')));
        $Qty =  implode(',',($request->input('mc_qty')));

       
        $status = 1; 
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;


       
        $MaterialConsumptionRequisition = new MaterialConsumptionRequisition();
        $MaterialConsumptionRequisition->org_id = $Organization;
        $MaterialConsumptionRequisition->site_id = $Site;
        $MaterialConsumptionRequisition->transaction_type_id = $TransactionType;
        $MaterialConsumptionRequisition->inv_location_id = $InvLocation;
        $MaterialConsumptionRequisition->mr_code = $PatientMR;
        $MaterialConsumptionRequisition->patient_age = $Age;
        $MaterialConsumptionRequisition->patient_gender_id = $genderId;
        $MaterialConsumptionRequisition->service_id = $Service;
        $MaterialConsumptionRequisition->service_mode_id = $serviceModeId;
        $MaterialConsumptionRequisition->billing_cc = $billingCCId;
        $MaterialConsumptionRequisition->physician_id = $ResponsiblePhysician;
        $MaterialConsumptionRequisition->generic_id = $itemGeneric;
        $MaterialConsumptionRequisition->qty = $Qty;
        $MaterialConsumptionRequisition->remarks = $Remarks;
        $MaterialConsumptionRequisition->status = $status;
        $MaterialConsumptionRequisition->user_id = $sessionId;
        $MaterialConsumptionRequisition->last_updated = $last_updated;
        $MaterialConsumptionRequisition->timestamp = $timestamp;
        $MaterialConsumptionRequisition->effective_timestamp = $this->currentDatetime;
        $MaterialConsumptionRequisition->save();

        $SiteName = Site::find($Site); // $Site is the site_id
        $SiteName = $SiteName ? $SiteName->name : '';  
        $idStr = str_pad($MaterialConsumptionRequisition->id, 5, "0", STR_PAD_LEFT);
        $firstSiteNameLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $SiteName))));
        $RequisitionCode = $firstSiteNameLetters.'-MTC-'.$idStr;
        $MaterialConsumptionRequisition->code = $RequisitionCode;

        if (empty($MaterialConsumptionRequisition->id)) {
            return response()->json(['error' => 'Failed to add Requisition For Material Consumption.']);
        }

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Requisition has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $MaterialConsumptionRequisition->logid = $logs->id;
        $MaterialConsumptionRequisition->save();
        return response()->json(['success' => 'Requisition For Material Consumption added successfully']);
        // }
    }

    public function GetMaterialConsumptionData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->requisition_for_material_consumption)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
    
        $Requisitions = DB::table('material_consumption_requisition')
        ->join('organization', 'organization.id', '=', 'material_consumption_requisition.org_id')
        ->join('org_site', 'org_site.id', '=', 'material_consumption_requisition.site_id')
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'material_consumption_requisition.transaction_type_id')
        ->join('service_location', 'service_location.id', '=', 'material_consumption_requisition.inv_location_id')
        ->leftJoin('patient', 'patient.mr_code', '=', 'material_consumption_requisition.mr_code')
        ->leftJoin('gender', 'gender.id', '=', 'patient.gender_id')
        ->leftJoin(DB::raw('(SELECT * FROM patient_inout WHERE status = 1 AND id IN (SELECT MAX(id) FROM patient_inout WHERE status = 1 GROUP BY mr_code)) as patient_inout'), 
        'patient_inout.mr_code', '=', 'material_consumption_requisition.mr_code')
        ->leftJoin('employee', 'employee.id', '=', 'material_consumption_requisition.physician_id')
        ->leftJoin('services', 'services.id', '=', 'material_consumption_requisition.service_id')
        ->leftJoin('service_group', 'service_group.id', '=', 'services.group_id')
        ->leftJoin('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->leftJoin('service_mode', 'service_mode.id', '=', 'material_consumption_requisition.service_mode_id')
        ->leftJoin('costcenter', 'costcenter.id', '=', 'material_consumption_requisition.billing_cc')
        ->select(
            'material_consumption_requisition.*', 
            'inventory_transaction_type.name as transactionType',
            'organization.organization as orgName',
            'org_site.name as siteName', 
            'service_location.name as locationName',
            'gender.name as Gender', 
            'patient.name as patientName', 
            'patient.mr_code as mr_code', 
            'patient.dob as DOB',
            'employee.name as Physician', 
            'services.name as serviceName',
            'service_mode.name as serviceModeName', 
            'costcenter.name as CCName', 
            'service_group.name as serviceGroupName',
            'service_location.name as ServiceLocationName'
        );
        
        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $Requisitions->where('material_consumption_requisition.org_id', '=', $sessionOrg);
        }
        $Requisitions = $Requisitions
        ->get();
        return DataTables::of($Requisitions)
        // return DataTables::eloquent($Requisitions)
            // ->filter(function ($query) use ($request) {
            //     if ($request->has('search') && $request->search['value']) {
            //         $search = $request->search['value'];
            //         $query->where(function ($q) use ($search) {
            //             $q->where('material_consumption_requisition.mr_code', 'like', "%{$search}%")
            //                 ->orWhere('organization.organization', 'like', "%{$search}%")
            //                 ->orWhere('org_site.name', 'like', "%{$search}%")
            //                 ->orWhere('inventory_transaction_type.name', 'like', "%{$search}%")
            //                 ->orWhere('patient.mr_code', 'like', "%{$search}%")
            //                 ->orWhere('employee.name', 'like', "%{$search}%")
            //                 ->orWhere('services.name', 'like', "%{$search}%");
            //         });
            //     }
            // })
            ->addColumn('id_raw', function ($Requisition) {
                return $Requisition->id;
            })
            ->editColumn('requisition_detail', function ($Requisition) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $effectiveDate = Carbon::createFromTimestamp($Requisition->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Requisition->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Requisition->last_updated)->format('l d F Y - h:i A');
                $SiteName = $Requisition->siteName;
                $orgName = $Requisition->orgName;
                $RequisitionCode = $Requisition->code;

                if ($Requisition->mr_code != '' && $Requisition->DOB != '')
                {
                    $MrCode = $Requisition->mr_code;
                    $MrCode = '<b>MR#: </b>'.ucwords($MrCode).'<hr class="mt-1 mb-2">';
                    $dob = Carbon::createFromTimestamp($Requisition->DOB); // Assuming $Requisition->DOB contains the timestamp
                    $now = Carbon::now();
                    $years = $dob->diffInYears($now);
                    $days = $dob->addYears($years)->diffInDays($now);
                    $age = $years . ' Years' . ($days === 1 ? ' and 1 Day' : " and $days Days");
                    if(!empty($age) || !empty($age))
                    {
                        $Age = '<br><b>Age: </b> '.$age;
                        $Gender = '<br><b>Gender: </b> '.ucwords($Requisition->Gender);
                    }
                } 
                else
                {
                    $MrCode = '';
                    $Age = '';
                    $Gender = '';
                }

                $createdInfo = "
                        <b>Created By:</b> " . ucwords($sessionName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($Requisition->orgName);
                }

                return  $RequisitionCode
                        . '<hr class="mt-1 mb-2">'
                        .'<b>Transaction Type: </b> '.ucwords($Requisition->transactionType)
                        .'<br><b>Requesting Location: </b> '.ucwords($Requisition->locationName)
                        .'<br><b>Site: </b>'.ucwords($Requisition->siteName)
                        .'<br><b>Request Date: </b>'.$timestamp
                        .'<br><b>Effective Date:</b>'.$effectiveDate
                        .'<br><b>Remarks: </b> ' . (!empty($Requisition->remarks) ? ucwords($Requisition->remarks) : 'N/A');
            })
            ->editColumn('patientDetails', function ($Requisition) {
                if (empty($Requisition->mr_code) && empty($Requisition->patientName) && empty($Requisition->Physician) &&  empty($Requisition->serviceModeName) && empty($Requisition->serviceName) && empty($Requisition->serviceGroupName) && empty($Requisition->CCName) )
                {
                    return 'N/A';
                }
                else{
                    $MR = $Requisition->mr_code;
                    $patientName = ucwords($Requisition->patientName);
                    $Physician = ucwords($Requisition->Physician);
                    $serviceMode = ucwords($Requisition->serviceModeName);
                    $serviceName = ucwords($Requisition->serviceName);
                    $serviceGroup = ucwords($Requisition->serviceGroupName);
                    $billingCC = ucwords($Requisition->CCName);

                    return '<b>MR#</b>: '.$MR.'<br>'
                    .$patientName.'<br>'
                    .'<b>Service Mode</b>: '.$serviceMode.'<br>'
                    .'<b>Service Group</b>: '.$serviceGroup.'<br>'
                    .'<b>Service</b>: '.$serviceName.'<br>'
                    .'<b>Responsible Physician</b>: '.$Physician.'<br>'
                    .'<b>Billing CC</b>: '.$billingCC.'<br>';
                }
            })
            ->editColumn('InventoryDetails', function ($Requisition) {
                $genericIds = explode(',', $Requisition->generic_id);
                $Quantities = explode(',', $Requisition->qty);
            
                $genericNames = InventoryGeneric::whereIn('id', $genericIds)->pluck('name', 'id')->toArray();
            
                $tableRows = '';
                $maxRows = max(count($genericIds), count($Quantities));
            
                for ($i = 0; $i < $maxRows; $i++) {
                    $genericName = isset($genericIds[$i]) && isset($genericNames[$genericIds[$i]]) ? $genericNames[$genericIds[$i]] : 'N/A';
                    $qtyValue = isset($Quantities[$i]) ? $Quantities[$i] : 'N/A';  
            
                    $tableRows .= '
                        <tr>
                            <td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($genericName) . '</td>
                            <td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . $qtyValue . '</td>
                        </tr>';
                }
            
                // Return the table structure with dynamic rows
                return '
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Generic Name</th>
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Demand Qty</th>
                            </tr>
                        </thead>
                        <tbody>' . $tableRows . '</tbody>
                    </table>';
            })
            ->addColumn('action', function ($Requisition) {
                    $RequisitionId = $Requisition->id;
                    $logId = $Requisition->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->requisition_for_material_consumption)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-materialconsumption" data-mc-id="'.$RequisitionId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $Requisition->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($Requisition) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->requisition_for_material_consumption)[3];
                return $updateStatus == 1 ? ($Requisition->status ? '<span class="label label-success mc_status cursor-pointer" data-id="'.$Requisition->id.'" data-status="'.$Requisition->status.'">Active</span>' : '<span class="label label-danger mc_status cursor-pointer" data-id="'.$Requisition->id.'" data-status="'.$Requisition->status.'">Inactive</span>') : ($Requisition->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','service_details', 'InventoryDetails', 'patientDetails',
            'requisition_detail'])
            ->make(true);
    }
    public function UpdateMaterialConsumptionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->requisition_for_material_consumption)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $MaterialConsumptionID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $MaterialConsumptionRequisition = MaterialConsumptionRequisition::find($MaterialConsumptionID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $MaterialConsumptionRequisition->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $MaterialConsumptionRequisition->status = $UpdateStatus;
        $MaterialConsumptionRequisition->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $MaterialConsumptionRequisitionLog = MaterialConsumptionRequisition::where('id', $MaterialConsumptionID)->first();
        $logIds = $MaterialConsumptionRequisitionLog->logid ? explode(',', $MaterialConsumptionRequisitionLog->logid) : [];
        $logIds[] = $logs->id;
        $MaterialConsumptionRequisitionLog->logid = implode(',', $logIds);
        $MaterialConsumptionRequisitionLog->save();

        $MaterialConsumptionRequisition->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateMaterialConsumptionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->requisition_for_material_consumption)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Requisitions = DB::table('material_consumption_requisition')
        ->join('organization', 'organization.id', '=', 'material_consumption_requisition.org_id')
        ->join('org_site', 'org_site.id', '=', 'material_consumption_requisition.site_id')
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'material_consumption_requisition.transaction_type_id')
        ->join('inventory_generic', 'inventory_generic.id', '=', 'material_consumption_requisition.generic_id')
        ->join('service_location', 'service_location.id', '=', 'material_consumption_requisition.inv_location_id')
        ->leftJoin('services', 'services.id', '=', 'material_consumption_requisition.service_id')
        ->select('material_consumption_requisition.*', 'inventory_transaction_type.name as transactionType',
        'organization.organization as orgName','org_site.name as siteName',
        'service_location.name as ServiceLocationName','inventory_generic.name as invGeneric','services.name as serviceName',)
        ->where('material_consumption_requisition.id', '=', $id)
        ->first();

        $effective_timestamp = $Requisitions->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $InvGenericIds = explode(',', $Requisitions->generic_id);
        $genericNames = [];
        foreach ($InvGenericIds as $genericId) {
            $generic = InventoryGeneric::find($genericId);
            if ($generic) {
                $genericNames[] = $generic->name;
            }
        }
        $combinedGenericNames = implode(',', $genericNames);
        $Requisitions->genericNames = $combinedGenericNames;

        $data = [
            'id' => $id,
            'orgName' => ucwords($Requisitions->orgName),
            'orgId' => ($Requisitions->org_id),
            'siteName' => ucwords($Requisitions->siteName),
            'siteId' => ($Requisitions->site_id),
            'transactionType' => ucwords($Requisitions->transactionType),
            'transactionTypeId' => ($Requisitions->transaction_type_id),
            'invGeneric' => ucwords($Requisitions->invGeneric),
            'invGenericId' => ($Requisitions->generic_id),
            'remarks' => ucwords($Requisitions->remarks),
            'ServiceLocationId' => ($Requisitions->inv_location_id),
            'ServiceLocationName' => ucwords($Requisitions->ServiceLocationName),
            'mrCode' => ($Requisitions->mr_code),
            'serviceId' => ($Requisitions->service_id),
            'serviceName' => ucwords($Requisitions->serviceName),
            'genericIds' => $Requisitions->generic_id,
            'genericNames' => ucwords($Requisitions->genericNames),
            'Qty' => $Requisitions->qty,
        ];

   
        return response()->json($data);
    }

    public function UpdateMaterialConsumption(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->requisition_for_material_consumption)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $MaterialConsumptionRequisition = MaterialConsumptionRequisition::findOrFail($id);
        $orgID = $request->input('u_mc_org');
        if (isset($orgID)) {
            $MaterialConsumptionRequisition->org_id = $orgID;
        }  
        $MaterialConsumptionRequisition->site_id = $request->input('u_mc_site');
        $MaterialConsumptionRequisition->transaction_type_id = $request->input('u_mc_transactionType');
        $PatientMR =  $request->input('u_mc_patient');
        $MaterialConsumptionRequisition->mr_code = $PatientMR;
        $MaterialConsumptionRequisition->inv_location_id = $request->input('u_mc_inv_location');
        $MaterialConsumptionRequisition->remarks = $request->input('u_mc_remarks');
        // $MaterialConsumptionRequisition->service_id = $request->input('u_mc_service');

        $MaterialConsumptionRequisition->generic_id = implode(',',($request->input('u_mc_itemgeneric')));
        $MaterialConsumptionRequisition->qty = implode(',',($request->input('u_mc_qty')));

        $MaterialConsumptionRequisition->last_updated = $this->currentDatetime;
        $Service = $request->input('u_mc_service');

        if (!empty($PatientMR) && !empty($Service)) {

            $PatientDetails = PatientRegistration::select(
                'gender.id as genderId', 'patient.dob as patientDOB',
                'employee.id as empID',
                'patient_inout.org_id as OrgId', 'patient_inout.site_id as SiteId', 
                'service_mode.id as serviceModeId', 'billingCC.id as billingCCId', 'service_group.id as serviceGroupId',
                'service_type.id as serviceTypeId'
            )
            ->join('patient_inout', 'patient_inout.mr_code', '=', 'patient.mr_code')
            ->join('gender', 'gender.id', '=', 'patient.gender_id')
            ->join('costcenter as billingCC', 'billingCC.id', '=', 'patient_inout.billing_cc')
            ->join('employee', 'employee.id', '=', 'patient_inout.emp_id')
            ->join('service_mode', 'service_mode.id', '=', 'patient_inout.service_mode_id')
            ->join('services', 'services.id', '=', 'patient_inout.service_id')
            ->join('service_group', 'service_group.id', '=', 'services.group_id')
            ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->where('patient.status', 1)
            ->where('patient_inout.status', 1)
            ->where('patient_inout.service_id', $Service)
            ->where('patient.mr_code', $PatientMR)
            ->first();


            if ($PatientDetails) {
                $dob = Carbon::createFromTimestamp($PatientDetails->patientDOB);
                $now = Carbon::now();
                $diff = $dob->diff($now);
            
                $years = $diff->y;
                $months = $diff->m;
                $days = $diff->d;
            
                $ageString = "";
                if ($years > 0) {
                    $ageString .= $years . " " . ($years == 1 ? "year" : "years");
                }
                if ($months > 0) {
                    $ageString .= " " . $months . " " . ($months == 1 ? "month" : "months");
                }
                if ($days > 0) {
                    $ageString .= " " . $days . " " . ($days == 1 ? "day" : "days");
                }
                
                $Age = $ageString;

                $MaterialConsumptionRequisition->service_id = $Service;
                $MaterialConsumptionRequisition->patient_age = $Age;
                $MaterialConsumptionRequisition->patient_gender_id = $PatientDetails->genderId;
                $MaterialConsumptionRequisition->service_mode_id = $PatientDetails->serviceModeId;
                $MaterialConsumptionRequisition->physician_id = $PatientDetails->empID;
                $MaterialConsumptionRequisition->billing_cc = $PatientDetails->billingCCId;

            }
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $MaterialConsumptionRequisition->save();

        if (empty($MaterialConsumptionRequisition->id)) {
            return response()->json(['error' => 'Failed to update Requisition For material Consumption. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $MaterialConsumptionRequisitionLog = MaterialConsumptionRequisition::where('id', $MaterialConsumptionRequisition->id)->first();
        $logIds = $MaterialConsumptionRequisitionLog->logid ? explode(',', $MaterialConsumptionRequisitionLog->logid) : [];
        $logIds[] = $logs->id;
        $MaterialConsumptionRequisitionLog->logid = implode(',', $logIds);
        $MaterialConsumptionRequisitionLog->save();
        return response()->json(['success' => 'Requisition For Material Consumption updated successfully']);
    }

    public function PurchaseOrder()
    {
        $colName = 'purchase_order';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.purchase-order', compact('user'));
    }

    public function ShowVendors(Request $request)
    {
        $orgId = $request->input('orgId');
        // $vendorId = $request->input('vendorId');
        // if (isset($vendorId)) {
        //     $Vendors = VendorRegistration::whereNotIn('id', [$vendorId])
        //              ->where('org_id', $orgId)
        //              ->where('status', 1)
        //              ->select('id', 'name')
        //              ->get();
        // }
        $Vendors = ThirdPartyRegistration::where('org_id', $orgId)
        ->where('status', 1)
        ->where('type', 'v')
        ->select('id', 'person_name')
        ->get();
        return response()->json($Vendors);
    }

    public function ShowItemBrand(Request $request)
    {
        $orgId = $request->input('orgId');
        $brandId = $request->input('brandId');

        $Brands = InventoryBrand::where('status', 1)->select('id', 'name');
        if (isset($brandId)) {
            // $Brands->whereNotIn('id', [$brandId]);
            $Brands->where('id', '!=', $brandId);
        }
        if (isset($orgId)) {

            $Brands->where('org_id', $orgId);
        }
        $AllBrands = $Brands->get();
        return response()->json($AllBrands);
    }

    public function AddPurchaseOrder(PurchaseOrderRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->purchase_order)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('po_org');
        $Site = $request->input('po_site');
        $Vendor = $request->input('po_vendor');
        $Brand = implode(',',$request->input('po_brand'));
        $Qty = implode(',',$request->input('po_qty'));
        $AmountArray = $request->input('po_amount'); 
        $DiscountArray = $request->input('po_discount');
        $Remarks = implode(',',$request->input('po_remarks'));
        $Edt = $request->input('po_edt');

        foreach ($AmountArray as $key => $amount) {
            $discount = $DiscountArray[$key] ?? 0;
            if ($discount >= $amount) {
                return response()->json(['info' => "Discount cannot be greater than or equal to the amount for the item at index " . ($key + 1)]);
            }
        }
    
        $Amount = implode(',', $AmountArray);
        $Discount = implode(',', $DiscountArray);


        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active

        } else {
            $status = 0; //Inactive
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $PurchaseOrderExists = PurchaseOrder::where('org_id', $Organization)
        ->where('site_id', $Site)
        ->where('vendor_id', $Vendor)
        ->where('inventory_brand_id', $Brand)
        ->exists();

        if ($PurchaseOrderExists) {
            return response()->json(['info' => 'Purchase Order already exists.']);
        }
        else
        {
            $PO = new PurchaseOrder();
            $PO->org_id = $Organization;
            $PO->site_id = $Site;
            $PO->vendor_id = $Vendor;
            $PO->inventory_brand_id = $Brand;
            $PO->demand_qty = $Qty;
            $PO->amount = $Amount;
            $PO->discount = $Discount;
            $PO->remarks = $Remarks;
            $PO->status = $status;
            $PO->user_id = $sessionId;
            $PO->last_updated = $last_updated;
            $PO->timestamp = $timestamp;
            $PO->effective_timestamp = $Edt;
            $PO->save();

            if (empty($PO->id)) {
                return response()->json(['error' => 'Failed to create Purchase Order.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "Purchase Order has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);

            $logId = $logs->id;
            $PO->logid = $logs->id;
            $PO->save();
            return response()->json(['success' => 'Purchase Order added successfully']);
        }
    }

    public function generatePdfPO($id)
    {
        $PO = PurchaseOrder::select('purchase_order.*',
            'organization.organization as orgName', 'organization.code as orgCode',
            'org_site.name as siteName',
            'third_party.person_name as vendorName',
            DB::raw('(SELECT GROUP_CONCAT(name) FROM inventory_brand WHERE FIND_IN_SET(inventory_brand.id, purchase_order.inventory_brand_id) > 0) as brandNames'))
            ->leftJoin('organization', 'organization.id', '=', 'purchase_order.org_id')
            ->join('org_site', 'org_site.id', '=', 'purchase_order.site_id')
            ->join('third_party', 'third_party.id', '=', 'purchase_order.vendor_id')
            ->where('purchase_order.id', '=', $id)
            ->first();

        $BrandIds = explode(',', $PO->inventory_brand_id);
        $Quantities = explode(',', $PO->demand_qty);
        $Amounts = explode(',', $PO->amount);
        $Discounts = explode(',', $PO->discount);
        $Remarks = explode(',', $PO->remarks);

        $effectiveDate = Carbon::createFromTimestamp($PO->effective_timestamp)->format('l d F Y - h:i A');

        $ApprovedBy = $PO->approved_by;
        $Users = Users::where('id', $ApprovedBy)
        ->where('status', '1')
        ->first();
        $ApproverName = ucwords($Users->name);

        $orgCode= $PO->orgCode;
        $Site= $PO->siteName;
        $RawDate= $PO->timestamp;
        $SitefirstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Site))));
        $idStr = str_pad($PO->id, 4, "0", STR_PAD_LEFT);
        $PONo = $orgCode.'-'.$SitefirstLetters.'-'.$RawDate.'-'.$idStr;

        $netQty = 0;
        $netAmount = 0;
        $netDiscount = 0;
        $netPayable = 0;

        $formattedData = '';
        foreach ($BrandIds as $key => $brandId) {
            $brandName = InventoryBrand::where('id', $brandId)->pluck('name')->first();
            
            $Payable = $Amounts[$key] - $Discounts[$key];
            
            $remarksValue = (!empty($Remarks[$key]) && $Remarks[$key] !== null) ? $Remarks[$key] : 'N/A';
            
            $netQty += $Quantities[$key];
            $netAmount += $Amounts[$key];
            $netDiscount += $Discounts[$key];
            $netPayable += $Payable;

            $formattedData .= '<tr>';
            $formattedData .= '<td>' . ucwords($brandName) . '</td>';
            $formattedData .= '<td>' . ucwords($Quantities[$key]) . '</td>';
            $formattedData .= '<td> Rs ' . number_format($Amounts[$key], 2) . '</td>';
            $formattedData .= '<td> Rs ' . number_format($Discounts[$key], 2) . '</td>';
            $formattedData .= '<td> Rs ' . number_format($Payable, 2) . '</td>';
            $formattedData .= '<td>' . ucwords($remarksValue) . '</td>';
            $formattedData .= '</tr>';
        }

        $formattedData .= '<tr style="font-weight: bold">';
        $formattedData .= '<td>Total</td>';
        $formattedData .= '<td>' . number_format($netQty) . '</td>';
        $formattedData .= '<td>Rs ' . number_format($netAmount, 2) . '</td>';
        $formattedData .= '<td>Rs ' . number_format($netDiscount, 2) . '</td>';
        $formattedData .= '<td>Rs ' . number_format($netPayable, 2) . '</td>';
        $formattedData .= '<td></td>';
        $formattedData .= '</tr>';
        $orgName = $PO->orgName;
        $siteName = $PO->siteName;
        $vendorName = $PO->vendorName;

        $html = view('pdf.purchase_order_template', compact('orgName','siteName','vendorName','PONo','effectiveDate', 'formattedData', 'ApproverName', 'netQty', 'netAmount', 'netDiscount', 'netPayable'))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->stream("PurchaseOrder - " . $PONo . ".pdf");
    }

    public function GetPurchaseOrderData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->purchase_order)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $PurchaseOrders = PurchaseOrder::select('purchase_order.*',
        'organization.organization as orgName','organization.code as orgCode',
        'org_site.name as siteName',
        'third_party.person_name as vendorName',
        DB::raw('(SELECT GROUP_CONCAT(name) FROM inventory_brand WHERE FIND_IN_SET(inventory_brand.id, purchase_order.inventory_brand_id) > 0) as brandNames'))
        ->leftJoin('organization', 'organization.id', '=', 'purchase_order.org_id')
        ->join('org_site', 'org_site.id', '=', 'purchase_order.site_id')
        ->join('third_party', 'third_party.id', '=', 'purchase_order.vendor_id')
        ->orderBy('purchase_order.id', 'desc')
        ->get();
        // $PurchaseOrders = PurchaseOrder::select('purchase_order.*',
        // 'organization.organization as orgName',
        // 'organization.code as orgCode',
        // 'org_site.name as siteName',
        // 'third_party.person_name as vendorName',
        //     DB::raw('(SELECT GROUP_CONCAT(name) FROM inventory_brand WHERE FIND_IN_SET(inventory_brand.id, purchase_order.inventory_brand_id) > 0) as brandNames'),
        //     DB::raw("CONCAT(organization.code, '-', UPPER(SUBSTRING_INDEX(org_site.name, ' ', 1)), '-', purchase_order.timestamp, '-', LPAD(purchase_order.id, 4, '0')) AS PONo")
        // )
        // ->leftJoin('organization', 'organization.id', '=', 'purchase_order.org_id')
        // ->join('org_site', 'org_site.id', '=', 'purchase_order.site_id')
        // ->join('third_party', 'third_party.id', '=', 'purchase_order.vendor_id')
        // ->orderBy('purchase_order.id', 'desc');

        // $session = auth()->user();
        // $sessionOrg = $session->org_id;
        // if($sessionOrg != '0')
        // {
        //     $PurchaseOrders->where('purchase_order.org_id', '=', $sessionOrg);
        // }
        // $PurchaseOrders = $PurchaseOrders;
        // ->get()
        return DataTables::of($PurchaseOrders)
        // return DataTables::eloquent($PurchaseOrders)
            // ->filter(function ($query) use ($request) {
            //     if ($request->has('search') && $request->search['value']) {
            //         $search = $request->search['value'];
            //         $query->where(function ($q) use ($search) {
            //             $q->where('purchase_order.id', 'like', "%{$search}%")
            //                 ->orWhere('organization.organization', 'like', "%{$search}%")
            //                 ->orWhere('org_site.name', 'like', "%{$search}%")
            //                 ->orWhere('vendor.person_name', 'like', "%{$search}%")
            //                 ->orWhereRaw('(SELECT GROUP_CONCAT(name) FROM inventory_brand WHERE FIND_IN_SET(inventory_brand.id, purchase_order.inventory_brand_id) > 0) LIKE ?', ["%{$search}%"]);
            //         });
            //     }
            // })
            ->addColumn('id_raw', function ($PO) {
                return $PO->id;  // Raw ID value
            })
            ->editColumn('id', function ($PO) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $orgCode= $PO->orgCode;
                $Site= $PO->siteName;
                $RawDate= $PO->timestamp;
                $SitefirstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Site))));
                $idStr = str_pad($PO->id, 4, "0", STR_PAD_LEFT);
                $PoNo = $orgCode.'-'.$SitefirstLetters.'-'.$RawDate.'-'.$idStr;

                $createdByName = getUserNameById($PO->user_id);
                $effectiveDate = Carbon::createFromTimestamp($PO->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($PO->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($PO->last_updated)->format('l d F Y - h:i A');
                $effectiveDate = Carbon::createFromTimestamp($PO->effective_timestamp)->format('l d F Y - h:i A');
                $createdInfo = "
                    <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                    <b>RecordedAt:</b> " . $timestamp ." <br>
                    <b>LastUpdated:</b> " . $lastUpdated;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($PO->orgName).'<br><hr class="mt-1 mb-2">';
                }

                return '<b>PO#: </b>' . $PoNo .
                    '<hr class="mt-1 mb-2">'.
                    '<b>Effective Date&amp;Time:</b> ' . $effectiveDate
                    .'<hr class="mt-1 mb-2">'
                    .$orgName.'
                    <b>Site: </b>'.ucwords($PO->siteName).'<br><hr class="mt-1 mb-2">
                    <b>Vendor: </b>'.ucwords($PO->vendorName).'<br>'
                    . '<span class="mt-1 label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
                    
            })
            ->editColumn('item_details', function ($PO) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $BrandIds = explode(',', $PO->inventory_brand_id);
                $Quantities = explode(',', $PO->demand_qty);
                $Amounts = explode(',', $PO->amount);
                $Discounts = explode(',', $PO->discount);
                $Remarks = explode(',', $PO->remarks);
            
                $netQty = 0;
                $netAmount = 0;
                $netDiscount = 0;
                $netPayable = 0;
            
                $formattedData = '<table>';
                $formattedData .= '<tr>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">BrandName</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Qty</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Amount</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Discount</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Net Payable</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Remarks</th>';
                $formattedData .= '</tr>';
            
                foreach ($BrandIds as $key => $brandId) {
                    $brandName = InventoryBrand::whereIn('id', [$brandId])->pluck('name')->first();
                    $Payable = $Amounts[$key] - $Discounts[$key];
                    $remarksValue = (!empty($Remarks[$key]) && $Remarks[$key] !== null) ? $Remarks[$key] : 'N/A'; // Check for null or empty remarks
            
                    $netQty += $Quantities[$key];
                    $netAmount += $Amounts[$key];
                    $netDiscount += $Discounts[$key];
                    $netPayable += $Payable;
            
                    $formattedData .= '<tr>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($brandName) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($Quantities[$key]) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format(($Amounts[$key]),2) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format(($Discounts[$key]),2) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format(($Payable),2) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($remarksValue) . '</td>';
                    $formattedData .= '</tr>';
                }
            
                // Adding the footer row with totals
                $formattedData .= '<tr style="font-weight:bold">';
                $formattedData .= '<td colspan="1" style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Total</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">'. number_format($netQty) . '</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Rs '. number_format($netAmount, 2) . '</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Rs '. number_format($netDiscount, 2) . '</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Rs '. number_format($netPayable, 2) . '</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> </td>';
                $formattedData .= '</tr>';
            
                $formattedData .= '</table>';
            
                return $formattedData;
            })
            // ->editColumn('other_details', function ($PO) {
            //     $session = auth()->user();
            //     $sessionOrg = $session->org_id;
            //     $orgName = '';
            //     if($sessionOrg == 0)
            //     {
            //         $orgName ='<b>Organization:</b> '.ucwords($PO->orgName).'<br><hr class="mt-1 mb-2">';
            //     }

            //     return $orgName.'
            //     <b>Site: </b>'.ucwords($PO->siteName).'<br><hr class="mt-1 mb-2">
            //     <b>Vendor: </b>'.ucwords($PO->vendorName).'<br>';
            // })
            ->addColumn('action', function ($PO) {
                    $POId = $PO->id;
                    $logId = $PO->logid;
                    $session = auth()->user();
                    $userId = $session->id;
                    $Rights = $this->rights;
                    $ApprovalData = '';
                    $ApprovalRights = explode(',', $Rights->purchase_order)[4];
                    if($ApprovalRights == 1)
                    {

                        $Approval = $PO->approval;
                        $ApprovedBy = $PO->approved_by;
                        $ApprovedTimestamp = $PO->approved_timestamp;
                        if ($Approval == 0 && $ApprovedBy == 0 && $ApprovedTimestamp == 0) {
                            $ApprovalData = '<span id="po_approve" class="text-underline" data-id="'.$PO->id.'" data-userid="'.$userId.'" style="cursor:pointer; color: #fb3a3a;font-weight: 500;">Click Here To Approve</span>';
                        }
                        else{
                            $Users = Users::where('id', $ApprovedBy)
                            ->where('status', '1')
                            ->first();
                            $ApproverName = ucwords($Users->name);
                            $ApprovalData = '<span class="text-underline" style="color: green;font-weight: 700;font-size: 14px;">Approved By <u>(' . $ApproverName . ')</u></span>';
                            $ApprovalData .= '<hr class="mt-1 mb-2"><button type="button" class="btn btn-outline-success save-pdf-po" data-purchaseorder-id="' . $POId . '">'
                            . '<i class="fa fa-file-pdf"></i> Save as PDF'
                            . '</button>';
                        }
                    }
                    $edit = explode(',', $Rights->purchase_order)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 mb-1 edit-purchaseorder" data-purchaseorder-id="'.$POId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button><br><br>';
                    $actionButtons .= $ApprovalData;
                    return $PO->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';


            })
            ->editColumn('status', function ($PO) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->purchase_order)[3];
                return $updateStatus == 1 ? ($PO->status ? '<span class="label label-success po_status cursor-pointer" data-id="'.$PO->id.'" data-status="'.$PO->status.'">Active</span>' : '<span class="label label-danger po_status cursor-pointer" data-id="'.$PO->id.'" data-status="'.$PO->status.'">Inactive</span>') : ($PO->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status', 'other_details','item_details',
            'id'])
            ->make(true);
    }

    public function UpdatePurchaseOrdertatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->purchase_order)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $POID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $PurchaseOrderStatus = PurchaseOrder::find($POID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $PurchaseOrderStatus->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $PurchaseOrderStatus->status = $UpdateStatus;
        $PurchaseOrderStatus->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $POLog = PurchaseOrder::where('id', $POID)->first();
        $logIds = $POLog->logid ? explode(',', $POLog->logid) : [];
        $logIds[] = $logs->id;
        $POLog->logid = implode(',', $logIds);
        $POLog->save();
        $PurchaseOrderStatus->save();
        
        return response()->json(['success' => true, 200]);
    }

    public function ApprovePurchaseOrder(Request $request)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->purchase_order)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $POID = $request->input('id');
        $userId = $request->input('userId');
        $CurrentTimestamp = $this->currentDatetime;
        $PurchaseOrder = PurchaseOrder::find($POID);

        $PurchaseOrder->approval = 1;
        $PurchaseOrder->approved_by = $userId;
        $PurchaseOrder->approved_timestamp = $CurrentTimestamp;
        $session = auth()->user();
        $sessionName = $session->name;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Purchase Order Approved By '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $POLog = PurchaseOrder::where('id', $POID)->first();
        $logIds = $POLog->logid ? explode(',', $POLog->logid) : [];
        $logIds[] = $logs->id;
        $POLog->logid = implode(',', $logIds);
        $POLog->save();
        $PurchaseOrder->save();
        
        return response()->json(['success' => true, 200]);
    }

    public function UpdatePurchaseOrderModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->purchase_order)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PurchaseOrders = PurchaseOrder::select('purchase_order.*',
        'organization.organization as orgName',
        'org_site.name as siteName',
        'third_party.person_name as vendorName',
        'purchase_order.inventory_brand_id as brandIds')
        ->join('organization', 'organization.id', '=', 'purchase_order.org_id')
        ->join('org_site', 'org_site.id', '=', 'purchase_order.site_id')
        ->join('third_party', 'third_party.id', '=', 'purchase_order.vendor_id')
        ->where('purchase_order.id', '=', $id)
        ->first();

        $brandIds = explode(',', $PurchaseOrders->brandIds);

        $brandNames = [];
        foreach ($brandIds as $brandId) {
            $brand = InventoryBrand::find($brandId);
            if ($brand) {
                $brandNames[] = $brand->name;
            }
        }
        $combinedBrandNames = implode(',', $brandNames);
        $PurchaseOrders->brandNames = $combinedBrandNames;

        $effective_timestamp = $PurchaseOrders->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $PurchaseOrders->org_id,
            'orgName' => ucwords($PurchaseOrders->orgName),
            'siteId' => $PurchaseOrders->site_id,
            'siteName' => ucwords($PurchaseOrders->siteName),
            'vendorId' => $PurchaseOrders->vendor_id,
            'vendorName' => ucwords($PurchaseOrders->vendorName),
            'brandId' => $PurchaseOrders->inventory_brand_id,
            'brandNames' => ucwords($PurchaseOrders->brandNames),
            'Quantities' => $PurchaseOrders->demand_qty,
            'Amounts' => $PurchaseOrders->amount,
            'Discounts' => $PurchaseOrders->discount,
            'remarks' => ucwords($PurchaseOrders->remarks),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdatePurchaseOrder(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->purchase_order)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PO = PurchaseOrder::findOrFail($id);
        $orgID = $request->input('u_po_org');
        if (isset($orgID)) {
            $PO->org_id = $orgID;
        }
        $PO->site_id = $request->input('u_po_site');
        $PO->vendor_id = $request->input('u_po_vendor');
        $PO->inventory_brand_id = implode(',',$request->input('u_po_brand'));
        $PO->demand_qty = implode(',',$request->input('u_po_qty'));

        $AmountArray = $request->input('u_po_amount'); 
        $DiscountArray = $request->input('u_po_discount');

        // $PO->amount = implode(',',$request->input('u_po_amount'));
        // $PO->discount = implode(',',$request->input('u_po_discount'));
        
        foreach ($AmountArray as $key => $amount) {
            $discount = $DiscountArray[$key] ?? 0;
            if ($discount >= $amount) {
                return response()->json(['error' => "Discount cannot be greater than or equal to the amount for the item at index " . ($key + 1)]);
            }
        }
        $Amount = implode(',', $AmountArray);
        $Discount = implode(',', $DiscountArray);

        $PO->amount = $Amount;
        $PO->discount = $Discount;

        $PO->remarks = implode(',',$request->input('u_po_remarks'));
        $effective_date = $request->input('u_po_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }
        $PO->effective_timestamp = $effective_date;
        $PO->last_updated = $this->currentDatetime;
        $PO->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;
        
        $PO->save();

        if (empty($PO->id)) {
            return response()->json(['error' => 'Failed to update Purchase Order Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PurchaseOrderLog = PurchaseOrder::where('id', $PO->id)->first();
        $logIds = $PurchaseOrderLog->logid ? explode(',', $PurchaseOrderLog->logid) : [];
        $logIds[] = $logs->id;
        $PurchaseOrderLog->logid = implode(',', $logIds);
        $PurchaseOrderLog->save();
        return response()->json(['success' => 'Purchase Order Details updated successfully']);
    }

    public function WorkOrder()
    {
        $colName = 'work_order';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.work-order', compact('user'));
    }

    public function AddWorkOrder(WorkOrderRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->work_order)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('wo_org');
        $Site = $request->input('wo_site');
        $Vendor = $request->input('wo_vendor');
        $Particulars = implode(',',$request->input('wo_particulars'));
        $Remarks = implode(',',$request->input('wo_remarks'));

        $AmountArray = $request->input('wo_amount'); 
        $DiscountArray = $request->input('wo_discount');


        $Edt = $request->input('wo_edt');

        foreach ($AmountArray as $key => $amount) {
            $discount = $DiscountArray[$key] ?? 0;
            if ($discount >= $amount) {
                return response()->json(['info' => "Discount cannot be greater than or equal to the amount for the item at index " . ($key + 1)]);
            }
        }

        $Amount = implode(',', $AmountArray);
        $Discount = implode(',', $DiscountArray);

        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active

        } else {
            $status = 0; //Inactive
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $WorkOrderExists = WorkOrder::where('org_id', $Organization)
        ->where('site_id', $Site)
        ->where('vendor_id', $Vendor)
        ->exists();

        if ($WorkOrderExists) {
            return response()->json(['info' => 'Work Order already exists.']);
        }
        else
        {
            $WO = new WorkOrder();
            $WO->org_id = $Organization;
            $WO->site_id = $Site;
            $WO->vendor_id = $Vendor;
            $WO->particulars = $Particulars;
            $WO->amount = $Amount;
            $WO->discount = $Discount;
            $WO->remarks = $Remarks;
            $WO->status = $status;
            $WO->user_id = $sessionId;
            $WO->last_updated = $last_updated;
            $WO->timestamp = $timestamp;
            $WO->effective_timestamp = $Edt;
            $WO->save();

            if (empty($WO->id)) {
                return response()->json(['error' => 'Failed to create Work Order.']);
            }

            $logs = Logs::create([
                'module' => 'inventory',
                'content' => "Purchase Order has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);

            $logId = $logs->id;
            $WO->logid = $logs->id;
            $WO->save();
            return response()->json(['success' => 'Work Order added successfully']);
        }
    }

    public function generatePdfWO($id)
    {
        $WO = WorkOrder::select('work_order.*',
            'organization.organization as orgName', 'organization.code as orgCode',
            'org_site.name as siteName',
            'third_party.person_name as vendorName')
            ->leftJoin('organization', 'organization.id', '=', 'work_order.org_id')
            ->join('org_site', 'org_site.id', '=', 'work_order.site_id')
            ->join('third_party', 'third_party.id', '=', 'work_order.vendor_id')
            ->where('work_order.id', '=', $id)
            ->first();

        $Particulars = explode(',', $WO->particulars);
        $Amounts = explode(',', $WO->amount);
        $Discounts = explode(',', $WO->discount);
        $Remarks = explode(',', $WO->remarks);

        $effectiveDate = Carbon::createFromTimestamp($WO->effective_timestamp)->format('l d F Y - h:i A');

        $ApprovedBy = $WO->approved_by;
        $Users = Users::where('id', $ApprovedBy)
        ->where('status', '1')
        ->first();
        $ApproverName = ucwords($Users->name);

        $orgCode= $WO->orgCode;
        $Site= $WO->siteName;
        $RawDate= $WO->timestamp;
        $SitefirstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Site))));
        $idStr = str_pad($WO->id, 4, "0", STR_PAD_LEFT);
        $WONo = $orgCode.'-'.$SitefirstLetters.'-'.$RawDate.'-'.$idStr;

        $netAmount = 0;
        $netDiscount = 0;
        $netPayable = 0;

        $formattedData = '';
        foreach ($Particulars as $key => $particular) {
            
            $Payable = $Amounts[$key] - $Discounts[$key];
            $particularValue = (!empty($Particulars[$key]) && $Particulars[$key] !== null) ? $Particulars[$key] : 'N/A';
            $remarksValue = (!empty($Remarks[$key]) && $Remarks[$key] !== null) ? $Remarks[$key] : 'N/A';
            
            $netAmount += $Amounts[$key];
            $netDiscount += $Discounts[$key];
            $netPayable += $Payable;

            $formattedData .= '<tr>';
            $formattedData .= '<td>' . ucwords($particularValue) . '</td>';
            $formattedData .= '<td> Rs ' . number_format($Amounts[$key], 2) . '</td>';
            $formattedData .= '<td> Rs ' . number_format($Discounts[$key], 2) . '</td>';
            $formattedData .= '<td> Rs ' . number_format($Payable, 2) . '</td>';
            $formattedData .= '<td>' . ucwords($remarksValue) . '</td>';
            $formattedData .= '</tr>';
        }

        $formattedData .= '<tr style="font-weight: bold">';
        $formattedData .= '<td>Total</td>';
        $formattedData .= '<td>Rs ' . number_format($netAmount, 2) . '</td>';
        $formattedData .= '<td>Rs ' . number_format($netDiscount, 2) . '</td>';
        $formattedData .= '<td>Rs ' . number_format($netPayable, 2) . '</td>';
        $formattedData .= '<td></td>';
        $formattedData .= '</tr>';
        $orgName = $WO->orgName;
        $siteName = $WO->siteName;
        $vendorName = $WO->vendorName;

        $html = view('pdf.work_order_template', compact('orgName','siteName','vendorName','WONo','effectiveDate', 'formattedData', 'ApproverName', 'netAmount', 'netDiscount', 'netPayable'))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->stream("Work Order - " . $WONo . ".pdf");
    }

    public function GetWorkOrderData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->work_order)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $WorkOrders = WorkOrder::select('work_order.*',
        'organization.organization as orgName','organization.code as orgCode',
        'org_site.name as siteName',
        'third_party.person_name as vendorName')
        ->leftJoin('organization', 'organization.id', '=', 'work_order.org_id')
        ->join('org_site', 'org_site.id', '=', 'work_order.site_id')
        ->join('third_party', 'third_party.id', '=', 'work_order.vendor_id')
        ->orderBy('work_order.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $WorkOrders->where('work_order.org_id', '=', $sessionOrg);
        }
        $WorkOrders = $WorkOrders;
        // ->get()
        // return DataTables::of($WorkOrders)
        return DataTables::eloquent($WorkOrders)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('work_order.id', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('vendor.name', 'like', "%{$search}%")
                            ->orWhere('work_order.particulars', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($WO) {
                return $WO->id;  // Raw ID value
            })
            ->editColumn('id', function ($WO) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $effectiveDate = Carbon::createFromTimestamp($WO->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($WO->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($WO->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($WO->user_id);
                $createdInfo = "
                    <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                    <b>RecordedAt:</b> " . $timestamp ." <br>
                    <b>LastUpdated:</b> " . $lastUpdated;
                    

                $session = auth()->user();
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization: </b>'.ucwords($WO->orgName).'<hr class="mt-1 mb-2">';
                }

                $orgCode= $WO->orgCode;
                $Site= $WO->siteName;
                $RawDate= $WO->timestamp;
                $SitefirstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Site))));
                $idStr = str_pad($WO->id, 4, "0", STR_PAD_LEFT);
                $WONo = $orgCode.'-'.$SitefirstLetters.'-'.$RawDate.'-'.$idStr;

                return '<b>WordOrder #:</b> ' . $WONo .'<br>'.
                '<b>Effective Date&amp;Time:</b> ' . $effectiveDate.'<br>'
                .'<hr class="mt-1 mb-2">'
                .$orgName.
                '<b>Site: </b>'.ucwords($WO->siteName).'<hr class="mt-1 mb-2">
                <b>Vendor: </b>'.ucwords($WO->vendorName)
                
             
                .'<hr class="mt-1 mb-2">'
                . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                . '<i class="fa fa-toggle-right"></i> View Details'
                . '</span>';
            })
            ->editColumn('item_details', function ($WO) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $effectiveDate = Carbon::createFromTimestamp($WO->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($WO->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($WO->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($WO->user_id);
                $createdInfo = "
                    <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                    <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                    <b>RecordedAt:</b> " . $timestamp ." <br>
                    <b>LastUpdated:</b> " . $lastUpdated;
                    
                $Particulars = explode(',', $WO->particulars);
                $Amounts = explode(',', $WO->amount);
                $Discounts = explode(',', $WO->discount);
                $Remarks = explode(',', $WO->remarks);

            
                $formattedData = '<table>';
                $formattedData .= '<tr>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Particulars</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Amount</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Discount</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Net Payable</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;width: 150px;">Remarks</th>';
                $formattedData .= '</tr>';
            
                // Initialize totals
                $totalAmount = 0;
                $totalDiscount = 0;
                $totalPayable = 0;
            
                foreach ($Particulars as $key => $Particular) {
                    $Payable = $Amounts[$key] - $Discounts[$key];
                    
                    // Add to totals
                    $totalAmount += $Amounts[$key];
                    $totalDiscount += $Discounts[$key];
                    $totalPayable += $Payable;
                    $remarksValue = (!empty($Remarks[$key]) && $Remarks[$key] !== null) ? $Remarks[$key] : 'N/A'; 
            
                    $formattedData .= '<tr>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($Particular) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format(($Amounts[$key]),2) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format(($Discounts[$key]),2) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format(($Payable),2) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($remarksValue) . '</td>';
                    $formattedData .= '</tr>';
                }
            
                // Footer row with totals
                $formattedData .= '<tr style="font-weight: bold;">';
                $formattedData .= '<td colspan="1" style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Total</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format($totalAmount, 2) . '</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format($totalDiscount, 2) . '</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> Rs ' . number_format($totalPayable, 2) . '</td>';
                $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"> </td>';
                $formattedData .= '</tr>';
            
                $formattedData .= '</table>';
                
                return $formattedData . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('other_details', function ($WO) {
                $session = auth()->user();
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization: </b>'.ucwords($WO->orgName).'<hr class="mt-1 mb-2">';
                }

                return $orgName.'
                <b>Site: </b>'.ucwords($WO->siteName).'<hr class="mt-1 mb-2">
                <b>Vendor: </b>'.ucwords($WO->vendorName).'<br>';
            })
            ->addColumn('action', function ($WO) {
                    $WOId = $WO->id;
                    $logId = $WO->logid;
                    $session = auth()->user();
                    $userId = $session->id;
                    $Rights = $this->rights;
                    $ApprovalData = '';
                    $ApprovalRights = explode(',', $Rights->work_order)[4];
                    if($ApprovalRights == 1)
                    {
                        $Approval = $WO->approval;
                        $ApprovedBy = $WO->approved_by;
                        $ApprovedTimestamp = $WO->approved_timestamp;

                        if ($Approval == 0 && $ApprovedBy == 0 && $ApprovedTimestamp == 0) {
                            $ApprovalData = '<span id="wo_approve" class="text-underline" data-id="'.$WO->id.'" data-userid="'.$userId.'" style="cursor:pointer; color: #fb3a3a;font-weight: 500;">Click Here To Approve</span>';
                        }
                        else{
                            $Users = Users::where('id', $ApprovedBy)
                            ->where('status', '1')
                            ->first();
                            $ApproverName = ucwords($Users->name);
                            $ApprovalData = '<span class="text-underline" style="color: green;font-weight: 700;font-size: 14px;">Approved By <u>(' . $ApproverName . ')</u></span>';
                            $ApprovalData .= '<hr class="mt-1 mb-2"><button type="button" class="btn btn-outline-success save-pdf-wo" data-workorder-id="' . $WOId . '">'
                            . '<i class="fa fa-file-pdf"></i> Save as PDF'
                            . '</button>';
                        }
                    }

                    $edit = explode(',', $Rights->work_order)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-workorder" data-workorder-id="'.$WOId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button><br><br>';
                    $actionButtons .= $ApprovalData;
                    return $WO->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($WO) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->work_order)[3];
                return $updateStatus == 1 ? ($WO->status ? '<span class="label label-success wo_status cursor-pointer" data-id="'.$WO->id.'" data-status="'.$WO->status.'">Active</span>' : '<span class="label label-danger wo_status cursor-pointer" data-id="'.$WO->id.'" data-status="'.$WO->status.'">Inactive</span>') : ($WO->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->rawColumns(['action', 'status', 'other_details','item_details',
            'id'])
            ->make(true);
    }

    public function UpdateWorkOrdertatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->work_order)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $WOID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $WorkOrderStatus = WorkOrder::find($WOID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $WorkOrderStatus->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $WorkOrderStatus->status = $UpdateStatus;
        $WorkOrderStatus->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $WOLog = WorkOrder::where('id', $WOID)->first();
        $logIds = $WOLog->logid ? explode(',', $WOLog->logid) : [];
        $logIds[] = $logs->id;
        $WOLog->logid = implode(',', $logIds);
        $WOLog->save();
        $WorkOrderStatus->save();
        
        return response()->json(['success' => true, 200]);
    }

    public function ApproveWorkOrder(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->work_order)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $WOID = $request->input('id');
        $userId = $request->input('userId');
        $CurrentTimestamp = $this->currentDatetime;
        $WorkOrder = WorkOrder::find($WOID);
        $WorkOrder->approval = 1;
        $WorkOrder->approved_by = $userId;
        $WorkOrder->approved_timestamp = $CurrentTimestamp;
        $session = auth()->user();
        $sessionName = $session->name;

        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Work Order Approved By '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $WOLog = WorkOrder::where('id', $WOID)->first();
        $logIds = $WOLog->logid ? explode(',', $WOLog->logid) : [];
        $logIds[] = $logs->id;
        $WOLog->logid = implode(',', $logIds);
        $WOLog->save();
        $WorkOrder->save();
        
        return response()->json(['success' => true, 200]);
    }

    public function UpdateWorkOrderModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->work_order)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $WorkOrders = WorkOrder::select('work_order.*',
        'organization.organization as orgName',
        'org_site.name as siteName',
        'third_party.person_name as vendorName')
        ->join('organization', 'organization.id', '=', 'work_order.org_id')
        ->join('org_site', 'org_site.id', '=', 'work_order.site_id')
        ->join('third_party', 'third_party.id', '=', 'work_order.vendor_id')
        ->where('work_order.id', '=', $id)
        ->first();

        $effective_timestamp = $WorkOrders->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgId' => $WorkOrders->org_id,
            'orgName' => ucwords($WorkOrders->orgName),
            'siteId' => $WorkOrders->site_id,
            'siteName' => ucwords($WorkOrders->siteName),
            'vendorId' => $WorkOrders->vendor_id,
            'vendorName' => ucwords($WorkOrders->vendorName),
            'Particulars' => $WorkOrders->particulars,
            'Amounts' => $WorkOrders->amount,
            'Discounts' => $WorkOrders->discount,
            'remarks' => ucwords($WorkOrders->remarks),
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateWorkOrder(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->work_order)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $WO = WorkOrder::findOrFail($id);
        $orgID = $request->input('u_wo_org');
        if (isset($orgID)) {
            $WO->org_id = $orgID;
        }
        $WO->site_id = $request->input('u_wo_site');
        $WO->vendor_id = $request->input('u_wo_vendor');

        $AmountArray = $request->input('u_wo_amount'); 
        $DiscountArray = $request->input('u_wo_discount');

        foreach ($AmountArray as $key => $amount) {
            $discount = $DiscountArray[$key] ?? 0;
            if ($discount >= $amount) {
                return response()->json(['error' => "Discount cannot be greater than or equal to the amount for the item at index " . ($key + 1)]);
            }
        }
        $Amount = implode(',', $AmountArray);
        $Discount = implode(',', $DiscountArray);

        $WO->amount = $Amount;
        $WO->discount = $Discount;

        $WO->particulars = implode(',',$request->input('u_wo_particulars'));
        $WO->remarks = implode(',',$request->input('u_wo_remarks'));

        $effective_date = $request->input('u_wo_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
             $status = 0;
        }
        $WO->effective_timestamp = $effective_date;
        $WO->last_updated = $this->currentDatetime;
        $WO->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;
        
        $WO->save();

        if (empty($WO->id)) {
            return response()->json(['error' => 'Failed to update Work Order Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'inventory',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $WorkOrderLog = WorkOrder::where('id', $WO->id)->first();
        $logIds = $WorkOrderLog->logid ? explode(',', $WorkOrderLog->logid) : [];
        $logIds[] = $logs->id;
        $WorkOrderLog->logid = implode(',', $logIds);
        $WorkOrderLog->save();
        return response()->json(['success' => 'Work Order Details updated successfully']);
    }

    public function GetTransactionTypeInventoryManagement(Request $request)
    {
        $siteId            = $request->input('siteId');
        $transactionTypeId = $request->input('transactionTypeId');
    
        // Current user info
        $user       = auth()->user();
        $roleId     = $user->role_id;
        $isEmployee = $user->is_employee;
        $empId      = $user->emp_id;
        // dd($empId);
    
        // 1) Get transaction type + service_location_id from the DB
        $TransactionType = InventoryTransactionType::select(
            'source_type.name as Source',
            'destination_type.name as Destination',
            'inventory_transaction_type.service_location_id',
            'inventory_transaction_type.transaction_expired_status',
            'inventory_transaction_type.applicable_location_to'
        )
        ->join('inventory_source_destination_type as source_type', 'source_type.id', '=', 'inventory_transaction_type.source_location_type')
        ->join('inventory_source_destination_type as destination_type', 'destination_type.id', '=', 'inventory_transaction_type.destination_location_type')
        ->where('inventory_transaction_type.id', '=', $transactionTypeId)
        ->first();
    
        if (! $TransactionType) {
            return response()->json([
                'Source'          => null,
                'Destination'     => null,
                'sourceData'      => [],
                'destinationData' => [],
            ]);
        }
    
        $sourceName      = strtolower($TransactionType->Source);
        $destinationName = strtolower($TransactionType->Destination);
        $applicableLocationTo = $TransactionType->applicable_location_to;

    
        $serviceLocationIDs = [];
        if (! empty($TransactionType->service_location_id)) {
            // e.g. "1,2,3"
            $serviceLocationIDs = explode(',', $TransactionType->service_location_id);
            $serviceLocationIDs = array_map('trim', $serviceLocationIDs);
            $serviceLocationIDs = array_filter($serviceLocationIDs);
        }
    
    
        // if ($roleId != 1 && $isEmployee == 1) {
        //     $empInv = DB::table('emp_inventory_location')
        //         ->where('site_id', $siteId)
        //         ->where('emp_id', $empId)
        //         ->where('status', 1)
        //         ->first();
        //         // dd($empInv);
    
        //     if ($empInv && ! empty($empInv->service_location_id)) {
        //         // Typically JSON like ["1","2","3"] or possibly nested
        //         $decoded = json_decode($empInv->service_location_id, true);
        //         if (! is_array($decoded)) {
        //             $decoded = [];
        //         } else {
        //             // Flatten if nested
        //             $flattened = [];
        //             array_walk_recursive($decoded, function($val) use (&$flattened) {
        //                 $flattened[] = (string) $val;
        //             });
        //             $decoded = $flattened; // now a flat array of strings
        //         }
    
        //         if (! empty($serviceLocationIDs)) {
        //             $serviceLocationIDs = array_intersect($serviceLocationIDs, $decoded);
        //         } else {
        //             // if no IDs from transaction type,
        //             // we just use the employee's allowed IDs
        //             $serviceLocationIDs = $decoded;
        //         }
        //     }
        // }
        $empServiceLocationIDs = [];
        if ($roleId != 1 && $isEmployee == 1) {
            $empInv = DB::table('emp_inventory_location')
                ->where('site_id', $siteId)
                ->where('emp_id', $empId)
                ->where('status', 1)
                ->first();

            if ($empInv && !empty($empInv->service_location_id)) {
                $decoded = json_decode($empInv->service_location_id, true);
                if (!is_array($decoded)) {
                    $decoded = [];
                } else {
                    $flattened = [];
                    array_walk_recursive($decoded, function($val) use (&$flattened) {
                        $flattened[] = (string) $val;
                    });
                    $decoded = $flattened;
                }
                $empServiceLocationIDs = $decoded;
            }
        }

        if (empty($serviceLocationIDs)) {
            return response()->json([
                'success'     => false,
                'message'     => 'Currently no locations are activated for you. Please contact the administrator.',
            ], 200);
        }
    
        // Prepare final arrays
        $sourceData      = [];
        $destinationData = [];
    
        if (stripos($sourceName, 'location') !== false) {
            $srcQuery = DB::table('service_location')
                ->where('service_location.status', 1)
                ->select('service_location.id', 'service_location.name')
                ->orderBy('service_location.name', 'asc');
        
            // if (count($serviceLocationIDs) > 0) {
            //     $srcQuery->whereIn('service_location.id', $serviceLocationIDs);
            // }

            if ($applicableLocationTo === 'source') {
                if(count($empServiceLocationIDs) > 0) {
                    $srcQuery->whereIn('service_location.id', array_intersect($serviceLocationIDs, $empServiceLocationIDs));
                } 
                else {
                    $srcQuery->whereIn('service_location.id', $serviceLocationIDs);
                }
            } 
            else {
                if (count($empServiceLocationIDs) > 0) {
                    $srcQuery->whereIn('service_location.id', $empServiceLocationIDs);
                }
            }

            // if ($applicableLocationTo === 'source' && count($empServiceLocationIDs) > 0) {
            //     $srcQuery->whereIn('service_location.id', array_intersect($serviceLocationIDs, $empServiceLocationIDs));
            // } else {
            //     $srcQuery->whereIn('service_location.id', $empServiceLocationIDs);
            // }
        
            $sourceData = $srcQuery->get();
        }
        elseif ($sourceName === 'vendor') {
            $sourceData = DB::table('third_party')
                ->select('id', 'person_name')
                ->where('type', 'v')
                ->orderBy('person_name', 'asc')
                ->get();
        }
        elseif ($sourceName === 'donor') {
            $sourceData = DB::table('third_party')
                ->select('id', 'person_name')
                ->where('type', 'd')
                ->orderBy('person_name', 'asc')
                ->get();
        }
        elseif ($sourceName === 'patient') {
            $sourceData = DB::table('patient_inout as pio')
                ->join('patient as p', 'p.mr_code', '=', 'pio.mr_code')
                ->select(
                    'pio.mr_code as id',
                    DB::raw("CONCAT(pio.mr_code, ' - ', p.name) as patient_name")
                )
                ->where('pio.status', 1)              
                ->where('pio.site_id', $siteId)    
                ->orderBy('p.name', 'asc')
                ->get();
        }
    
        // DESTINATION
        if (stripos($destinationName, 'location') !== false) {
        // if ($destinationName === 'inventory location') {
            $destQuery = DB::table('service_location')
                ->where('service_location.status', 1)
                ->select('service_location.id', 'service_location.name')
                ->orderBy('service_location.name', 'asc');
    
            // if (count($serviceLocationIDs) > 0) {
            //     $destQuery->whereIn('service_location.id', $serviceLocationIDs);
            // }

            if ($applicableLocationTo === 'destination') {
                if(count($empServiceLocationIDs) > 0) {
                    $destQuery->whereIn('service_location.id', array_intersect($serviceLocationIDs, $empServiceLocationIDs));
                } 
                else {
                    $destQuery->whereIn('service_location.id', $serviceLocationIDs);
                }
            } 
            else {
                if (count($empServiceLocationIDs) > 0) {
                    $destQuery->whereIn('service_location.id', $empServiceLocationIDs);
                }
            }
            // if ($applicableLocationTo === 'destination') {
            //     // dd($applicableLocationTo, $serviceLocationIDs, $empServiceLocationIDs); 
            //     $destQuery->whereIn('service_location.id', array_intersect($serviceLocationIDs, $empServiceLocationIDs));
            // } else {
            //     $destQuery->whereIn('service_location.id', $empServiceLocationIDs);
            // }
    
            $destinationData = $destQuery->get();
        }
        elseif ($destinationName === 'vendor') {
            $destinationData = DB::table('third_party')
                ->select('id', 'person_name')
                ->where('type', 'v')
                ->orderBy('person_name', 'asc')
                ->get();
        }
        elseif ($destinationName === 'donor') {
            $destinationData = DB::table('third_party')
                ->select('id', 'person_name')
                ->where('type', 'd')
                ->orderBy('person_name', 'asc')
                ->get();
        }
        elseif ($destinationName === 'patient') {
            $destinationData = DB::table('patient_inout as pio')
                ->join('patient as p', 'p.mr_code', '=', 'pio.mr_code')
                ->select(
                    'pio.mr_code as id',
                    DB::raw("CONCAT(pio.mr_code, ' - ', p.name) as patient_name")
                )
                ->where('pio.status', 1)     
                ->where('pio.site_id', $siteId)
                ->orderBy('p.name', 'asc')
                ->get();
        }
    
        return response()->json([
            'Source'          => $TransactionType->Source,
            'Destination'     => $TransactionType->Destination,
            'sourceData'      => $sourceData,
            'destinationData' => $destinationData,
            'transaction_expired_status' => $TransactionType->transaction_expired_status,
        ]);
    }

    public function ShowExternalTransaction()
    {
        $colName = 'external_transaction';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $empId = $user->emp_id;
        
        $costcenters = DB::table('emp_cc as e')
        ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
        ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
        ->where('e.emp_id', $empId)
        ->where('ct.performing', 1)
        ->select('c.id', 'c.name')
        ->get();

        return view('dashboard.material_management.external_transactions', compact('user','costcenters'));
    }

    public function AddExternalTransaction(ExternalTransactionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->external_transaction)[0];
        if ($add == 0) {
            abort(403, 'Forbidden');
        }
    
        // Retrieve single value inputs
        $TransactionTypeID   = $request->input('et_transactiontype');
        $Organization        = $request->input('et_org');
        $Site                = $request->input('et_site');
        $PerformingCC        = $request->input('et_performing_cc');
        $Source              = $request->input('et_source');
        $Destination         = $request->input('et_destination');
        $ReferenceDocument   = $request->input('et_reference_document');
        $Remarks             = $request->input('et_remarks');
    
        // Retrieve array inputs
        $Generics   = $request->input('et_generic');
        $Brands     = $request->input('et_brand');
        $Batches    = $request->input('et_batch');
        // $ExpireDate = $request->input('et_expiry');
        $Quantities = $request->input('et_qty');
    
        // Convert arrays to comma-separated strings for non-date fields
        $GenericsCSV   = is_array($Generics) ? implode(',', $Generics) : '';
        $BrandsCSV     = is_array($Brands) ? implode(',', $Brands) : '';
        $BatchesCSV    = is_array($Batches) ? implode(',', $Batches) : '';
        $QuantitiesCSV = is_array($Quantities) ? implode(',', $Quantities) : '';
    
        $ExpireDate = $request->input('et_expiry');
        $ExpireDates = [];
        foreach ($ExpireDate as $ed) {
            $timestamp = Carbon::createFromFormat('Y-m-d', $ed)->timestamp;
            $ExpireDates[] = $timestamp;
        }

        $ExpireDates = is_array($ExpireDates) ? implode(',', $ExpireDates) : '';
    
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;
        $last_updated = $this->currentDatetime;
        $timestampNow = $this->currentDatetime;
    
        // Create a single ExternalTransaction record with CSV data
        $ExternalTransaction = new InventoryManagement();
        $ExternalTransaction->transaction_type_id = $TransactionTypeID;
        $ExternalTransaction->org_id              = $Organization;
        $ExternalTransaction->site_id             = $Site;
        $ExternalTransaction->performing_cc       = $PerformingCC;
        $ExternalTransaction->brand_id            = $BrandsCSV;
        $ExternalTransaction->inv_generic_id      = $GenericsCSV;
        $ExternalTransaction->batch_no            = $BatchesCSV;
        $ExternalTransaction->expiry_date         = $ExpireDates; // Integer timestamps as CSV
        $ExternalTransaction->transaction_qty     = $QuantitiesCSV;
        $ExternalTransaction->ref_document_no     = $ReferenceDocument;
        $ExternalTransaction->remarks             = $Remarks;
        $ExternalTransaction->source              = $Source;
        $ExternalTransaction->destination         = $Destination;
        $ExternalTransaction->status              = 1;
        $ExternalTransaction->user_id             = $sessionId;
        $ExternalTransaction->last_updated        = $last_updated;
        $ExternalTransaction->timestamp           = $timestampNow;
        $ExternalTransaction->effective_timestamp = $timestampNow; // Adjust if necessary
    
        $ExternalTransaction->save();
    
        if (empty($ExternalTransaction->id)) {
            return response()->json(['error' => 'Failed to Add External Transaction.']);
        }
    
        // Log creation
        $logs = Logs::create([
            'module'    => 'inventory',
            'content'   => "External Transaction has been added by '{$sessionName}'",
            'event'     => 'add',
            'timestamp' => $timestampNow,
        ]);
    
        $ExternalTransaction->logid = $logs->id;
        $ExternalTransaction->save();

        $rule = DB::table('inventory_transaction_type')
        ->select('applicable_location_to','source_action','destination_action')
        ->where('id', $TransactionTypeID)
        ->first();

        $useAction = $rule->applicable_location_to === 'source'
        ? $rule->source_action
        : $rule->destination_action;

        $count = max(
            count($Generics),
            count($Brands),
            count($Batches),
            count($Quantities)
        );

        for ($i = 0; $i < $count; $i++) {
            $genId   = $Generics[$i]   ?? null;
            $brandId = $Brands[$i]     ?? null;
            $batchNo = $Batches[$i]    ?? null;
            $qty     = (int) ($Quantities[$i] ?? 0);
            $expTs   = $timestamps[$i] ?? null;

            if (! $genId || ! $brandId || ! $batchNo) {
                continue;
            }

            $prevOrgRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $Organization)
                ->orderBy('id', 'desc')
                ->first();
            $prevOrgBalance = $prevOrgRow->org_balance ?? 0;

            $prevSiteRow = InventoryBalance::where('generic_id', $genId)
            ->where('brand_id',  $brandId)
            ->where('batch_no',  $batchNo)
            ->where('org_id',    $Organization)
            ->where('site_id',   $Site)
            ->orderBy('id', 'desc')
            ->first();
            $prevSiteBalance = $prevSiteRow->site_balance ?? 0;

            switch ($useAction) {
                case 'a':  // add
                    $newOrgBalance  = $prevOrgBalance  + $qty;
                    $newSiteBalance = $prevSiteBalance + $qty;
                    break;
                case 's':  // subtract
                case 'r':  // reverse (treat like subtract)
                    $newOrgBalance  = $prevOrgBalance  - $qty;
                    $newSiteBalance = $prevSiteBalance - $qty;
                    break;
                default:   // 'n' or no‐op
                    $newOrgBalance  = $prevOrgBalance;
                    $newSiteBalance = $prevSiteBalance;
            }

            $dateTime   = Carbon::createFromTimestamp($timestampNow)->format('d-M-Y H:i');
            $remarkText = "Transaction by {$session->name} on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSiteBalance}";
        
            InventoryBalance::create([
                'management_id' => $ExternalTransaction->id,
                'generic_id'    => $genId,
                'brand_id'      => $brandId,
                'batch_no'      => $batchNo,
                'expiry_date'   => $expTs,
                'org_id'        => $Organization,
                'site_id'       => $Site,
                'org_balance'   => $newOrgBalance,
                'site_balance'  => $newSiteBalance,
                'remarks'       => $remarkText,
                'timestamp'     => $timestampNow,
            ]);
        }

        return response()->json(['success' => 'External Transaction added successfully']);
    }

    public function GetExternalTransactionData(Request $request)
    {
        $rights = $this->rights;
        $view   = explode(',', $rights->external_transaction)[1];
        if ($view == 0) {
            abort(403, 'Forbidden');
        }

        // 2) Base query (NO joins to brand/generic)
        // $ExternalTransactions = InventoryManagement::select(
        //     'inventory_management.*',
        //     'inventory_transaction_type.name as TransactionTypeName',
        //     'organization.organization as orgName',
        //     'organization.code as orgCode',
        //     'org_site.name as siteName'
        // )
        // ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'inventory_management.transaction_type_id')
        // ->leftJoin('organization', 'organization.id', '=', 'inventory_management.org_id')
        // ->join('org_site', 'org_site.id', '=', 'inventory_management.site_id')
        // ->leftJoin('inventory_generic',       'inventory_generic.id',       '=', 'inventory_management.inv_generic_id')
        // ->leftJoin('inventory_brand',         'inventory_brand.id',         '=', 'inventory_management.brand_id')
        // ->orderBy('inventory_management.id', 'desc');

        // 2) Base query (NO joins to brand/generic)
        $ExternalTransactions = InventoryManagement::select(
            'inventory_management.*',
            'inventory_transaction_type.name as TransactionTypeName',
            'inventory_transaction_type.activity_type',
            'organization.organization as orgName',
            'organization.code as orgCode',
            'org_site.name as siteName'
        )
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'inventory_management.transaction_type_id')
        ->join('inventory_transaction_activity', 'inventory_transaction_activity.id', '=', 'inventory_transaction_type.activity_type')
        ->leftJoin('organization', 'organization.id', '=', 'inventory_management.org_id')
        ->join('org_site', 'org_site.id', '=', 'inventory_management.site_id')
        ->leftJoin('inventory_generic', 'inventory_generic.id', '=', 'inventory_management.inv_generic_id')
        ->leftJoin('inventory_brand', 'inventory_brand.id', '=', 'inventory_management.brand_id')
        ->where('inventory_transaction_activity.name', 'external transaction') 
        ->orderBy('inventory_management.id', 'desc');
        // ->get();

        // 3) Filter by user's org if needed
        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if ($sessionOrg != '0') {
            $ExternalTransactions->where('inventory_management.org_id', '=', $sessionOrg);
        }

        // 4) Return DataTables
        // return DataTables::of($ExternalTransactions)
        return DataTables::eloquent($ExternalTransactions)
             ->filter(function($query) use ($request) {
                if ($request->has('search') && $search = $request->search['value']) {
                    $query->where(function($q) use ($search) {
                        $q->where('inventory_management.id','like', "%{$search}%")
                        ->orWhere('inventory_transaction_type.name','like', "%{$search}%")
                        ->orWhere('organization.organization','like', "%{$search}%")
                        ->orWhere('org_site.name','like', "%{$search}%")
                        ->orWhere('inventory_management.transaction_qty','like', "%{$search}%")
                        ->orWhere('inventory_management.batch_no','like', "%{$search}%")
                        ->orWhere('inventory_generic.name','like', "%{$search}%")
                        ->orWhere('inventory_brand.name','like', "%{$search}%")
                        ->orWhere('inventory_management.ref_document_no','like', "%{$search}%")
                        ->orWhere('inventory_management.remarks','like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($row) {
                return $row->id;
            })
            ->addColumn('transaction_details', function ($row) {
                $SiteCode  = strtoupper(substr($row->siteName, 0, 3));
                $idStr     = str_pad($row->id, 5, "0", STR_PAD_LEFT);
                $code      =  $SiteCode . '-ET-' . $idStr;
            
                $issueDate = $row->effective_timestamp
                    ? \Carbon\Carbon::createFromTimestamp($row->effective_timestamp)->format('d F Y')
                    : 'N/A';
                // $issueDate   = Carbon::createFromTimestamp($row->effective_timestamp)->format('d-M-Y H:i');
            
                $referenceNumber = $row->ref_document_no ?: 'N/A';
                $siteName        = $row->siteName       ?: 'N/A';
                $remarks         = $row->remarks        ?: 'N/A';
            
                // Source & Destination Type Lookup from transaction_type
                $transactionType = InventoryTransactionType::select(
                    'source_location_type', 'destination_location_type'
                )->where('id', $row->transaction_type_id)->first();
            
                $sourceText = '';
                $destinationText = '';
            
                if ($transactionType) {
                    // Get Source
                    // dd($transactionType,$row->source_location_id,$transactionType->source_location_type);

                    // if ($row->source_location_id) {

                    if ($transactionType->source_location_type) {
                        $sourceType = DB::table('inventory_source_destination_type')
                            ->where('id', $transactionType->source_location_type)
                            ->value('name');
        
                        if ($sourceType) {
                            if (stripos($sourceType, 'vendor') !== false || stripos($sourceType, 'donor') !== false) {
                                $sourceName = DB::table('third_party')
                                    ->where('id', $row->source)
                                    ->value('person_name');
                                $sourceLabel = stripos($sourceType, 'vendor') !== false ? 'Source Vendor' : 'Source Donor';
                                $sourceText = "<b>{$sourceLabel}:</b> " . ($sourceName ?: 'N/A') . '<br>';
                            } elseif (stripos($sourceType, 'location') !== false) {
                                $sourceName = DB::table('service_location')
                                    ->where('id', $row->source)
                                    ->value('name');
                                $sourceText = "<b>Source Location:</b> " . ($sourceName ?: 'N/A') . '<br>';
                            }
                        }
                    }
                    // }
                   
                    // Get Destination
                    // if ($row->destination_location_id) {
                    if ($transactionType->destination_location_type) {
                        $destinationType = DB::table('inventory_source_destination_type')
                            ->where('id', $transactionType->destination_location_type)
                            ->value('name');
        
                        if ($destinationType) {
                            if (stripos($destinationType, 'vendor') !== false || stripos($destinationType, 'donor') !== false) {
                                $destinationName = DB::table('third_party')
                                    ->where('id', $row->destination)
                                    ->value('person_name');
                                $destinationLabel = stripos($destinationType, 'vendor') !== false ? 'Destination Vendor' : 'Destination Donor';
                                $destinationText = "<b>{$destinationLabel}:</b> " . ($destinationName ?: 'N/A') . '<br>';
                            } elseif (stripos($destinationType, 'location') !== false) {
                                $destinationName = DB::table('service_location')
                                    ->where('id', $row->destination)
                                    ->value('name');
                                $destinationText = "<b>Destination Location:</b> " . ($destinationName ?: 'N/A') . '<br>';
                            }
                        }
                    }
                    // }
                }
            
                return $code . '<br>'
                     . '<b>Ref #:</b> ' . $referenceNumber . '<br>'
                     . ucwords($row->TransactionTypeName) . '<br>'
                     . $sourceText
                     . $destinationText
                     . '<b>Site Name:</b> ' . $siteName . '<br>'
                     . '<b>Issue Date:</b> ' . $issueDate . '<br>'
                     . '<b>Remarks:</b> ' . $remarks;
            })
            ->addColumn('item_details', function ($row) {
                $brandIds     = array_filter(explode(',', (string) $row->brand_id));
                $genericIds   = array_filter(explode(',', (string) $row->inv_generic_id));
                $batchArray   = array_filter(explode(',', (string) $row->batch_no));
                $expiryArray  = array_filter(explode(',', (string) $row->expiry_date));
                $qtyArray     = array_filter(explode(',', (string) $row->transaction_qty));

                $brands   = InventoryBrand::whereIn('id', $brandIds)->pluck('name', 'id')->toArray();
                $generics = InventoryGeneric::whereIn('id', $genericIds)->pluck('name', 'id')->toArray();

                $count = max(count($brandIds), count($genericIds), count($batchArray), count($expiryArray), count($qtyArray));

                // ——— copied styles from your InventoryDetails table ———
                $html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
                    . '<thead style="background-color:#e2e8f0;color:#000;">'
                        . '<tr>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">S.No</th>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Brand</th>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Batch No</th>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Expiry Date</th>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Quantity</th>'
                        . '</tr>'
                    . '</thead>'
                    . '<tbody>';

                for ($i = 0; $i < $count; $i++) {
                    $brandId     = $brandIds[$i]   ?? null;
                    $genericId   = $genericIds[$i] ?? null;

                    $brandName   = $brandId && isset($brands[$brandId]) ? $brands[$brandId] : 'N/A';
                    $genericName = $genericId && isset($generics[$genericId]) ? $generics[$genericId] : 'N/A';

                    $batchNo = $batchArray[$i]   ?? 'N/A';
                    $expiry  = $expiryArray[$i]  ?? 'N/A';
                    $qty     = $qtyArray[$i]     ?? 'N/A';

                    if (! $genericId || ! $brandId || ! $batchNo) {
                        continue;
                    }

                    $balRow = InventoryBalance::where('generic_id', $genericId)
                    ->where('brand_id',   $brandId)
                    ->where('batch_no',   $batchNo)
                    ->orderBy('id', 'desc')
                    ->first();

                    $orgBal  = $balRow->org_balance  ?? 0;
                    $siteBal = $balRow->site_balance ?? 0;
                    $title   = "Org Balance: {$orgBal}\nSite Balance: {$siteBal}";


                    $formattedExpiry = is_numeric($expiry)
                        ? \Carbon\Carbon::createFromTimestamp($expiry)->format('d-M-Y')
                        : 'N/A';

                    $expiredText = '';
                    if (is_numeric($expiry) && Carbon::createFromTimestamp($expiry)
                            ->setTimezone('Asia/Karachi')->isPast()
                    ) {
                        $expiredText = '<br><span style="color: red; font-size: 12px;">Expired</span>';
                    }

                    $sno = $i + 1;
                    $bg  = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';

                    $html .= '<tr class="balance-row" data-org-balance="'.$orgBal.'" data-site-balance="'.$siteBal.'" style="background-color:'.$bg.';cursor:pointer;">'
                        . '<td style="padding:8px;border:1px solid #ccc;">'.$sno.'</td>'
                        . '<td style="padding:8px;border:1px solid #ccc;">'.$genericName.'</td>'
                        . '<td style="padding:8px;border:1px solid #ccc;">'.$brandName.'</td>'
                        . '<td style="padding:8px;border:1px solid #ccc;">'.$batchNo.'</td>'
                        . '<td style="padding:8px;border:1px solid #ccc;">'.$formattedExpiry.' '.$expiredText.'</td>'
                        . '<td style="padding:8px;border:1px solid #ccc;">'.$qty.'</td>'
                    . '</tr>';
                }

                $html .= '</tbody></table>';

                return $html;
            })

            // Mark columns containing HTML so DataTables doesn’t escape them
            ->rawColumns(['transaction_details', 'item_details'])

            // 6) Render
            ->make(true);
    }

    public function ShowIssueDispense()
    {
        $colName = 'issue_and_dispense';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $empId = $user->emp_id;
        
        $costcenters = DB::table('emp_cc as e')
        ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
        ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
        ->where('e.emp_id', $empId)
        ->where('ct.performing', 1)
        ->select('c.id', 'c.name')
        ->get();

        $RequisitionNonMandatory = DB::table('inventory_transaction_type AS itt')
        ->join('inventory_transaction_activity AS ita', 'ita.id', '=', 'itt.activity_type')
        ->where('ita.name', 'Issue & Dispense')
        ->where('itt.request_mandatory', 'n')
        ->exists();

        $MedicationRoutes = MedicationRoutes::select('id', 'name')->where('status', 1)->get();
        $MedicationFrequencies = MedicationFrequency::select('id', 'name')->where('status', 1)->get();

        return view('dashboard.material_management.issue_dispense', compact('user','RequisitionNonMandatory','costcenters','MedicationRoutes','MedicationFrequencies'));
    }

    public function GetIssueDispenseData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->issue_and_dispense)[1];
        if ($view == 0) {
            abort(403, 'Forbidden');
        }

        // $sharedFields = [
        //     'id', 'transaction_type_id', 'inv_location_id', 'status',
        //     'effective_timestamp', 'timestamp', 'last_updated', 'logid'
        // ];

        $sharedFields = [
            'id', 'transaction_type_id', 'status', 'org_id', 'site_id',
            'effective_timestamp', 'timestamp', 'last_updated', 'logid'
        ];

        $joinFields = [
            DB::raw('patient.name as patientName'),
            DB::raw('employee.name as Physician'),
            DB::raw('organization.organization as OrgName'),
            DB::raw('org_site.name as SiteName'),
            DB::raw('services.name as serviceName'),
            DB::raw('service_mode.name as serviceMode'),
            DB::raw('billingCC.name as billingCC'),
            DB::raw('service_group.name as serviceGroup'),
            DB::raw('service_type.name as serviceType'),
            DB::raw('inventory_transaction_type.name as TransactionType'),
            DB::raw('COALESCE(service_location.name, "N/A") as ServiceLocationName')
        ];

        // --- Medication Query ---
        $medication = DB::table('req_medication_consumption as rmc')
            // ->join('gender', 'gender.id', '=', 'rmc.gender_id')
            ->join('costcenter as billingCC', 'billingCC.id', '=', 'rmc.billing_cc')
            ->join('employee', 'employee.id', '=', 'rmc.responsible_physician')
            ->join('service_mode', 'service_mode.id', '=', 'rmc.service_mode_id')
            ->join('services', 'services.id', '=', 'rmc.service_id')
            ->join('organization', 'organization.id', '=', 'rmc.org_id')
            ->join('org_site', 'org_site.id', '=', 'rmc.site_id')
            ->join('service_group', 'service_group.id', '=', 'rmc.service_group_id')
            ->join('service_type', 'service_type.id', '=', 'rmc.service_type_id')
            ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'rmc.transaction_type_id')
            ->leftJoin('service_location', 'service_location.id', '=', 'rmc.inv_location_id')
            ->join('patient', 'patient.mr_code', '=', 'rmc.mr_code')
            ->select(array_merge(
                array_map(fn($col) => "rmc.$col", $sharedFields),
                [
                    DB::raw("CAST(rmc.code AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as referenceNumber"),
                    DB::raw("CAST(rmc.mr_code AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as mr_code"),
                    DB::raw("CAST(rmc.dose AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as dose"),
                    DB::raw("CAST(rmc.route_ids AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as route_ids"),
                    DB::raw("CAST(rmc.frequency_ids AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as frequency_ids"),
                    DB::raw("CAST(rmc.days AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as days"),
                    DB::raw("CAST(rmc.inv_generic_ids AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as inv_generic_ids"),
                    DB::raw("CAST(rmc.remarks AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as remarks")
                ],
                $joinFields,
                [DB::raw("CAST('medication' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as source")]
            ))->get();

        // --- Material Query ---
        $material = DB::table('material_consumption_requisition as mcr')
            ->leftJoin('patient', 'patient.mr_code', '=', 'mcr.mr_code')
            // ->leftJoin('gender', 'gender.id', '=', 'patient.gender_id')
            ->leftJoin('employee', 'employee.id', '=', 'mcr.physician_id')
            ->leftJoin('service_mode', 'service_mode.id', '=', 'mcr.service_mode_id')
            ->leftJoin('services', 'services.id', '=', 'mcr.service_id')
            ->leftJoin('service_group', 'service_group.id', '=', 'services.group_id')
            ->leftJoin('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->leftJoin('costcenter as billingCC', 'billingCC.id', '=', 'mcr.billing_cc')
            ->leftJoin('organization', 'organization.id', '=', 'mcr.org_id')
            ->leftJoin('org_site', 'org_site.id', '=', 'mcr.site_id')
            ->leftJoin('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'mcr.transaction_type_id')
            ->leftJoin('service_location', 'service_location.id', '=', 'mcr.inv_location_id')
            ->select(array_merge(
                array_map(fn($col) => "mcr.$col", $sharedFields),
                [
                    DB::raw("CAST(mcr.code AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as referenceNumber"),
                    DB::raw("CAST(mcr.mr_code AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as mr_code"),
                    DB::raw("CAST('' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as dose"),
                    DB::raw("CAST('' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as route_ids"),
                    DB::raw("CAST('' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as frequency_ids"),
                    DB::raw("CAST(mcr.qty AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as demand_qty"),
                    DB::raw("CAST(mcr.generic_id AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as inv_generic_ids"),
                    DB::raw("CAST(mcr.remarks AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as remarks")
                ],
                $joinFields,
                [DB::raw("CAST('material' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as source")]
            ))->get();

        $inventoryJoinFields = [
            DB::raw('patient.name as patientName'),
            DB::raw('employee.name as Physician'),
            DB::raw('organization.organization as OrgName'),
            DB::raw('org_site.name as SiteName'),
            DB::raw('services.name as serviceName'),
            DB::raw('service_mode.name as serviceMode'),
            DB::raw('billingCC.name as billingCC'),
            DB::raw('service_group.name as serviceGroup'),
            DB::raw('service_type.name as serviceType'),
            DB::raw('inventory_transaction_type.name as TransactionType'),
            DB::raw('"N/A" as ServiceLocationName') 
        ];

        $directIssueDIspense = DB::table('inventory_management as im')
            ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'im.transaction_type_id')
            ->join('inventory_transaction_activity as ita', 'ita.id', '=', 'inventory_transaction_type.activity_type')
            ->join('organization', 'organization.id', '=', 'im.org_id')
            ->join('org_site', 'org_site.id', '=', 'im.site_id')
            ->leftJoin('patient', 'patient.mr_code', '=', 'im.mr_code')
            ->leftJoin('employee', 'employee.id', '=', 'im.resp_physician')
            ->leftJoin('service_mode', 'service_mode.id', '=', 'im.service_mode_id')
            ->leftJoin('services', 'services.id', '=', 'im.service_id')
            ->leftJoin('service_group', 'service_group.id', '=', 'services.group_id')
            ->leftJoin('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->leftJoin('costcenter as billingCC', 'billingCC.id', '=', 'im.billing_cc')
                ->where(function($query) {
                $query->where('ita.name', 'like', '%issue%')
                    ->orWhere('ita.name', 'like', '%dispense%');
            })
            ->where(function($query) {
                $query->whereNull('im.ref_document_no')
                    ->orWhere(function($q) {
                        $q->where('im.ref_document_no', 'not like', '%-MTC-%')
                            ->where('im.ref_document_no', 'not like', '%-MDC-%');
                    });
            })
            ->select(array_merge(
                array_map(fn($col) => "im.$col", $sharedFields),
                [
                    // DB::raw("CAST('N/A' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as code"),
                    DB::raw("CAST(im.ref_document_no AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as referenceNumber"),
                    DB::raw("CAST(im.mr_code AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as mr_code"),
                    DB::raw("CAST('' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as dose"),
                    DB::raw("CAST('' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as route_ids"),
                    DB::raw("CAST('' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as frequency_ids"),
                    DB::raw("CAST(im.demand_qty AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as demand_qty"),
                    DB::raw("CAST(im.transaction_qty AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as transaction_qty"),
                    DB::raw("CAST(im.inv_generic_id AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as inv_generic_ids"),
                    DB::raw("CAST(im.remarks AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as remarks")
                ],
                $inventoryJoinFields, 
                [DB::raw("CAST('inventory' AS CHAR CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) as source")]
            ))->get();

            $combined = $medication->merge($material)->merge($directIssueDIspense);
            // $combined = $medication->merge($material);
            // return DataTables::query(DB::table(DB::raw("({$combined->toSql()}) as combined"))
            return DataTables::of(collect($combined))
            // ->mergeBindings($combined))
            ->addColumn('id_raw', fn($row) => $row->id)
            ->editColumn('id', function ($row) {
                $timestamp = Carbon::createFromTimestamp($row->timestamp)->format('l d F Y - h:i A');
                $effectiveDate = Carbon::createFromTimestamp($row->effective_timestamp)->format('l d F Y - h:i A');

                $RequisitionCode = $row->referenceNumber ?? 'N/A';

                return $RequisitionCode
                . '<hr class="mt-1 mb-2">'
                . '<b>Request For</b>: ' . ($row->TransactionType ?? 'N/A') . '<br>'
                . '<b>Requesting Location</b>: ' . ($row->ServiceLocationName ?? 'N/A') . '<br>'
                . '<b>Site</b>: ' . ($row->SiteName ?? 'N/A') . '<br>'
                . '<b>Request Date: </b>: ' . $timestamp . '<br>'
                . '<b>Effective Date: </b>: ' . $effectiveDate . '<br>'
                . '<b>Remarks</b>: ' . ($row->remarks ?: 'N/A');
            })
            ->editColumn('patientDetails', function ($row) {
                if (empty($row->mr_code)) return 'N/A';

                return '<b>MR#:</b> '.$row->mr_code.'<br>'.$row->patientName.'<hr class="mt-1 mb-2">'
                    .'<b>Service Mode</b>: '.$row->serviceMode.'<br>'
                    .'<b>Service Group</b>: '.$row->serviceGroup.'<br>'
                    .'<b>Service</b>: '.$row->serviceName.'<br>'
                    .'<b>Responsible Physician</b>: '.$row->Physician.'<br>'
                    .'<b>Billing CC</b>: '.$row->billingCC;
            })
            ->editColumn('InventoryDetails', function ($row) {
                $Rights = $this->rights;
                $respond = explode(',', $Rights->issue_and_dispense)[2];
                
                $tableRows = '';
                $genericIds = explode(',', $row->inv_generic_ids);
                $genericNames = InventoryGeneric::whereIn('id', $genericIds)->pluck('name', 'id')->toArray();

                $isDirectEntry = false;
                if ($row->source === 'inventory') {
                    if (!empty($row->referenceNumber)) {
                        if (str_contains($row->referenceNumber, '-MTC-') || str_contains($row->referenceNumber, '-MDC-')) {
                            $isDirectEntry = false;
                        } else {
                            $isDirectEntry = true;
                        }
                    } else {
                        $isDirectEntry = true;
                    }
                } else {
                    $isDirectEntry = false;
                }

                if ($row->source === 'medication') {
                    $genericIds = explode(',', $row->inv_generic_ids);
                    $dose = explode(',', $row->dose);
                    $routeIds = explode(',', $row->route_ids);
                    $routes = MedicationRoutes::whereIn('id', $routeIds)->pluck('name', 'id')->toArray();
                    $frequencyIds = explode(',', $row->frequency_ids);
                    $frequencies = MedicationFrequency::whereIn('id', $frequencyIds)->pluck('name', 'id')->toArray();
                    $days = explode(',', $row->days);
                
                    // Get responded entries from inventory_management
                    $respondedEntries = [];
                    if (!empty($row->referenceNumber)) {

                        $respondedEntries = DB::table('inventory_management')
                            ->where('ref_document_no', $row->referenceNumber)
                            ->groupBy('inv_generic_id')
                            ->select('inv_generic_id', DB::raw('SUM(transaction_qty) as total_qty'))
                            ->pluck('total_qty', 'inv_generic_id')
                            ->toArray();
                    }
                
                    $count = max(count($genericIds), count($dose), count($routeIds), count($frequencyIds), count($days));
                    for ($i = 0; $i < $count; $i++) {
                        $bg = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';
                        
                        $currentGenericId = $genericIds[$i] ?? '';
                        $isResponded = array_key_exists($currentGenericId, $respondedEntries);


                        $balances = ['orgBalance' => 'N/A', 'siteBalance' => 'N/A'];

                        if ($isDirectEntry) {
                            $balanceInfo = DB::table('inventory_management')
                                ->where('inv_generic_id', $currentGenericId)
                                ->select('brand_id', 'batch_no')
                                ->first();

                            if ($balanceInfo) {
                                $orgBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $balanceInfo->brand_id)
                                    ->where('batch_no', $balanceInfo->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('org_balance') ?? 'N/A';

                                $siteBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('site_id', $row->site_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $balanceInfo->brand_id)
                                    ->where('batch_no', $balanceInfo->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('site_balance') ?? 'N/A';

                                $balances = [
                                    'orgBalance' => $orgBalance,
                                    'siteBalance' => $siteBalance
                                ];
                            }
                        } else if (!empty($row->referenceNumber) && $isResponded) {
                            $respondedEntry = DB::table('inventory_management')
                                ->where('ref_document_no', $row->referenceNumber)
                                ->where('inv_generic_id', $currentGenericId)
                                ->select('brand_id', 'batch_no')
                                ->first();

                            if ($respondedEntry) {
                                $orgBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $respondedEntry->brand_id)
                                    ->where('batch_no', $respondedEntry->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('org_balance') ?? 'N/A';

                                $siteBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('site_id', $row->site_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $respondedEntry->brand_id)
                                    ->where('batch_no', $respondedEntry->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('site_balance') ?? 'N/A';

                                $balances = [
                                    'orgBalance' => $orgBalance,
                                    'siteBalance' => $siteBalance
                                ];
                            }
                        }


                        // if ($isDirectEntry) {
                        //     $balanceInfo = DB::table('inventory_management')
                        //         ->where('inv_generic_id', $currentGenericId)
                        //         ->select('brand_id', 'batch_no')
                        //         ->first();

                        //     if ($balanceInfo) {
                        //         $orgBalance = DB::table('inventory_balance')
                        //             ->where('org_id', $row->org_id)
                        //             ->where('generic_id', $currentGenericId)
                        //             ->where('brand_id', $balanceInfo->brand_id)
                        //             ->where('batch_no', $balanceInfo->batch_no)
                        //             ->orderBy('id', 'desc')
                        //             ->value('org_balance') ?? 'N/A';

                        //         $siteBalance = DB::table('inventory_balance')
                        //             ->where('org_id', $row->org_id)
                        //             ->where('site_id', $row->site_id)
                        //             ->where('generic_id', $currentGenericId)
                        //             ->where('brand_id', $balanceInfo->brand_id)
                        //             ->where('batch_no', $balanceInfo->batch_no)
                        //             ->orderBy('id', 'desc')
                        //             ->value('site_balance') ?? 'N/A';

                        //         $balances = [
                        //             'orgBalance' => $orgBalance,
                        //             'siteBalance' => $siteBalance
                        //         ];
                        //     }
                        // }
                        // else{
                        //     $balances = [
                        //         'orgBalance' => 'N/A',
                        //         'siteBalance' => 'N/A'
                        //     ];
                        // }


                        if ($isDirectEntry || $isResponded) {
                            $status = 'Completed';
                            $statusClass = 'success';
                            $actionBtn = 'N/A';
                        } else {
                            $status = 'Pending';
                            $statusClass = 'warning';
                            $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn" data-source="'. $row->source.'" data-id="'. $row->id.'" data-generic-id="' . $currentGenericId . '">Respond</a>';
                        }
                        $orgBal = 'N/A';
                        $siteBal = 'N/A';
                
                        $tableRows .= '<tr style="background-color:'.$bg.';" class="balance-row" data-org-balance="'.$balances['orgBalance'].'" data-site-balance="'.$balances['siteBalance'].'">'
                            .'<td style="padding:8px;border:1px solid #ccc;">'.($genericNames[$currentGenericId] ?? 'N/A').'</td>'
                            .'<td style="padding:8px;border:1px solid #ccc;">'.($dose[$i] ?? 'N/A').'</td>'
                            .'<td style="padding:8px;border:1px solid #ccc;">'.($routes[$routeIds[$i]] ?? 'N/A').'</td>'
                            .'<td style="padding:8px;border:1px solid #ccc;">'.($frequencies[$frequencyIds[$i]] ?? 'N/A').'</td>'
                            .'<td style="padding:8px;border:1px solid #ccc;">'.($days[$i] ?? 'N/A').'</td>';
                
                        if ($isResponded) {
                            $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$respondedEntries[$currentGenericId].'</td>';
                                $respondedEntry = DB::table('inventory_management')
                                    ->where('ref_document_no', $row->referenceNumber)
                                    ->where('inv_generic_id', $currentGenericId)
                                    ->select('brand_id', 'batch_no')
                                    ->first();

                                if ($respondedEntry) {
                                    $orgBalance = DB::table('inventory_balance')
                                        ->where('org_id', $row->org_id)
                                        ->where('generic_id', $currentGenericId)
                                        ->where('brand_id', $respondedEntry->brand_id)
                                        ->where('batch_no', $respondedEntry->batch_no)
                                        ->orderBy('id', 'desc')
                                        ->value('org_balance') ?? 'N/A';

                                    $siteBalance = DB::table('inventory_balance')
                                        ->where('org_id', $row->org_id)
                                        ->where('site_id', $row->site_id)
                                        ->where('generic_id', $currentGenericId)
                                        ->where('brand_id', $respondedEntry->brand_id)
                                        ->where('batch_no', $respondedEntry->batch_no)
                                        ->orderBy('id', 'desc')
                                        ->value('site_balance') ?? 'N/A';

                                    $balances = [
                                        'orgBalance' => $orgBalance,
                                        'siteBalance' => $siteBalance
                                    ];
                                }
                        }
                        if($respond != 1)
                        {
                            $actionBtn = '<code>Unauthorized Access</code>';
                        }
                
                        $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$actionBtn.'</td>
                            <td style="padding:8px;border:1px solid #ccc;">
                                <span class="label label-'.$statusClass.'">'.$status.'</span>
                            </td>
                            </tr>';
                    }
                
                    // Build table header
                    $tableHeader = '<tr>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic Name</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Dose</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Route</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Frequency</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Duration (Days)</th>';
                    
                    // Add transaction qty header if there are any responded entries
                    if (!empty($respondedEntries)) {
                        $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Transaction Qty</th>';
                    }
                
                    $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Action</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Status</th>'
                        .'</tr>';
                
                    return '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
                        .'<thead style="background-color:#e2e8f0;color:#000;">'
                        .$tableHeader
                        .'</thead><tbody>'.$tableRows.'</tbody></table>';
                }
                else {
                    $demandQty = !empty($row->demand_qty) ? explode(',', $row->demand_qty) : [];
                    $transactionQty = ($row->source === 'inventory' && !empty($row->transaction_qty)) ? explode(',', $row->transaction_qty) : null;
                    
                    // Get responded quantities from inventory_management for this reference
                    $respondedQtys = [];
                    if (!empty($row->referenceNumber)) {
                        // $respondedQtys = DB::table('inventory_management')
                        //     ->where('ref_document_no', $row->referenceNumber)
                        //     ->pluck('transaction_qty', 'inv_generic_id')
                        //     ->toArray();
                            $respondedQtys = DB::table('inventory_management')
                            ->where('ref_document_no', $row->referenceNumber)
                            ->groupBy('inv_generic_id')
                            ->select('inv_generic_id', DB::raw('SUM(transaction_qty) as total_qty'))
                            ->pluck('total_qty', 'inv_generic_id')
                            ->toArray();
                    }
                
                    for ($i = 0; $i < count($genericIds); $i++) {
                        $bg = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';
                        
                        $currentGenericId = $genericIds[$i];

                        // if ($isDirectEntry) {
                        //     $balanceInfo = DB::table('inventory_management')
                        //         ->where('inv_generic_id', $currentGenericId)
                        //         ->select('brand_id', 'batch_no')
                        //         ->first();

                        //     if ($balanceInfo) {
                        //         $orgBalance = DB::table('inventory_balance')
                        //             ->where('org_id', $row->org_id)
                        //             ->where('generic_id', $currentGenericId)
                        //             ->where('brand_id', $balanceInfo->brand_id)
                        //             ->where('batch_no', $balanceInfo->batch_no)
                        //             ->orderBy('id', 'desc')
                        //             ->value('org_balance') ?? 'N/A';

                        //         $siteBalance = DB::table('inventory_balance')
                        //             ->where('org_id', $row->org_id)
                        //             ->where('site_id', $row->site_id)
                        //             ->where('generic_id', $currentGenericId)
                        //             ->where('brand_id', $balanceInfo->brand_id)
                        //             ->where('batch_no', $balanceInfo->batch_no)
                        //             ->orderBy('id', 'desc')
                        //             ->value('site_balance') ?? 'N/A';

                        //         $balances = [
                        //             'orgBalance' => $orgBalance,
                        //             'siteBalance' => $siteBalance
                        //         ];
                        //     }
                        // }
                        // else{
                        //     $balances = [
                        //         'orgBalance' => 'N/A',
                        //         'siteBalance' => 'N/A'
                        //     ];
                        // }

                        $balances = ['orgBalance' => 'N/A', 'siteBalance' => 'N/A'];

                        if ($isDirectEntry) {
                            $balanceInfo = DB::table('inventory_management')
                                ->where('inv_generic_id', $currentGenericId)
                                ->select('brand_id', 'batch_no')
                                ->first();

                            if ($balanceInfo) {
                                $orgBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $balanceInfo->brand_id)
                                    ->where('batch_no', $balanceInfo->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('org_balance') ?? 'N/A';

                                $siteBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('site_id', $row->site_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $balanceInfo->brand_id)
                                    ->where('batch_no', $balanceInfo->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('site_balance') ?? 'N/A';

                                $balances = [
                                    'orgBalance' => $orgBalance,
                                    'siteBalance' => $siteBalance
                                ];
                            }
                        } else if (!empty($row->referenceNumber) && $respondedQtys) {
                            $respondedEntry = DB::table('inventory_management')
                                ->where('ref_document_no', $row->referenceNumber)
                                ->where('inv_generic_id', $currentGenericId)
                                ->select('brand_id', 'batch_no')
                                ->first();

                            if ($respondedEntry) {
                                $orgBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $respondedEntry->brand_id)
                                    ->where('batch_no', $respondedEntry->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('org_balance') ?? 'N/A';

                                $siteBalance = DB::table('inventory_balance')
                                    ->where('org_id', $row->org_id)
                                    ->where('site_id', $row->site_id)
                                    ->where('generic_id', $currentGenericId)
                                    ->where('brand_id', $respondedEntry->brand_id)
                                    ->where('batch_no', $respondedEntry->batch_no)
                                    ->orderBy('id', 'desc')
                                    ->value('site_balance') ?? 'N/A';

                                $balances = [
                                    'orgBalance' => $orgBalance,
                                    'siteBalance' => $siteBalance
                                ];
                            }
                        }


                        $currentDemandQty = isset($demandQty[$i]) ? floatval($demandQty[$i]) : 0;
                        $currentRespondedQty = isset($respondedQtys[$currentGenericId]) ? floatval($respondedQtys[$currentGenericId]) : 0;
                        // return $currentRespondedQty;
                        // Determine status based on responded quantities
                        if ($currentRespondedQty > 0) {
                            if ($currentRespondedQty >= $currentDemandQty) {
                                $status = 'Completed';
                                $statusClass = 'success';
                                $actionBtn = 'N/A';
                            } else {
                                $status = 'Partially Completed';
                                $statusClass = 'info';
                                $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn" data-source="'. $row->source.'" data-id="'. $row->id.'" data-generic-id="' . $currentGenericId . '">Respond</a>';
                            }
                        } else {
                            $status = 'Pending';
                            $statusClass = 'warning';
                            $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn" data-source="'. $row->source.'" data-id="'. $row->id.'" data-generic-id="' . $currentGenericId . '">Respond</a>';
                        }
                
                        // Override for direct entries
                        if ($isDirectEntry) {
                            $status = 'Completed';
                            $statusClass = 'success';
                            $actionBtn = 'N/A';
                        }
                
                        $tableRows .= '<tr style="background-color:'.$bg.';" class="balance-row" data-org-balance="'.$balances['orgBalance'].'" data-site-balance="'.$balances['siteBalance'].'">'
                            .'<td style="padding:8px;border:1px solid #ccc;">'.($genericNames[$currentGenericId] ?? 'N/A').'</td>'
                            .'<td style="padding:8px;border:1px solid #ccc;">'.($demandQty[$i] ?? 'N/A').'</td>';
                        
                        // Add transaction qty column only for inventory source
                        // if ($row->source === 'inventory' && $transactionQty !== null) {
                        if ($row->source === 'inventory' && $transactionQty !== null) {
                            $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.($transactionQty[$i] ?? 'N/A').'</td>';
                        }
                
                        // Add responded qty column if not inventory source
                        if ($row->source !== 'inventory' && $currentRespondedQty >= 0) {
                            $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$currentRespondedQty.'</td>';
                        }
                        // else{
                        //     $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">0</td>';
                        // }

                        if($respond != 1)
                        {
                            $actionBtn = '<code>Unauthorized Access</code>';
                        }
                
                        $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$actionBtn.'</td>
                            <td style="padding: 5px 15px;border: 1px solid #ccc;">
                                <span class="label label-'.$statusClass.'">'.$status.'</span>
                            </td>
                            </tr>';
                    }
                
                    // Build table header
                    $tableHeader = '<tr>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic Name</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Demand Qty</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Transaction Qty</th>';
                    
                    // Add transaction qty header only for inventory source
                    // if ($row->source === 'inventory') {
                    //     $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Transaction Qty</th>';
                    // }
                
                    // // Add responded qty header if not inventory source
                    // if ($row->source !== 'inventory' && !empty($respondedQtys)) {
                    //     $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Transaction Qty</th>';
                    // }
                
                    $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Action</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Status</th>'
                        .'</tr>';
                
                    return '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
                        .'<thead style="background-color:#e2e8f0;color:#000;">'
                        .$tableHeader
                        .'</thead><tbody>'.$tableRows.'</tbody></table>';   
                }
            })
        ->rawColumns(['id_raw', 'id', 'patientDetails', 'InventoryDetails'])
        ->make(true);
    }

    public function RespondIssueDispense(Request $r)
    {
        $rights = $this->rights;
        $respond = explode(',', $rights->issue_and_dispense)[2];
        if ($respond == 0) {
            abort(403, 'Forbidden');
        }

        $id        = $r->query('id');
        $genericId = $r->query('genericId');
        $source    = $r->query('source');  // “medication” or “material”

        if ($source === 'medication') {
            $med = RequisitionForMedicationConsumption::from('req_medication_consumption as r')
                ->select([
                    'r.*',
                    'o.organization as org_name',
                    's.name as site_name',
                    'p.name as patient_name',
                    'itt.name as transaction_type_name',
                    'sl.name as location_name',
                    'sm.name as service_mode_name',
                    'sv.name as service_name',
                    'e.name as physician_name',
                    'bcc.name as billing_cc_name',
                    'sg.name as service_group_name',
                    'st.name as service_type_name',
                    // 'ib.site_balance as maxQty'

                ])
                ->join('organization as o', 'o.id', '=', 'r.org_id')
                ->join('org_site as s', 's.id', '=', 'r.site_id')
                ->join('patient as p', 'p.mr_code', '=', 'r.mr_code')
                ->join('inventory_transaction_type as itt', 'itt.id', '=', 'r.transaction_type_id')
                ->join('service_location as sl', 'sl.id', '=', 'r.inv_location_id')
                ->join('service_mode as sm', 'sm.id', '=', 'r.service_mode_id')
                ->join('services as sv', 'sv.id', '=', 'r.service_id')
                ->join('employee as e', 'e.id', '=', 'r.responsible_physician')
                ->join('costcenter as bcc', 'bcc.id', '=', 'r.billing_cc')
                ->join('service_group as sg', 'sg.id', '=', 'r.service_group_id')
                ->join('service_type as st', 'st.id', '=', 'r.service_type_id')
                // ->leftJoin('inventory_management as im', 'im.ref_document_no', '=', 'r.code')
                // ->leftJoin(DB::raw('(SELECT * FROM inventory_balance ORDER BY id DESC LIMIT 1) as ib'), function($join) {
                //     $join->on('ib.generic_id', '=', 'im.inv_generic_id')
                //         ->on('ib.brand_id', '=', 'im.brand_id')
                //         ->on('ib.batch_no', '=', 'im.batch_no');
                // })
                ->where('r.id', $id)
                ->first();
                    
            if (! $med) {
                return response()->json(['error'=>'Medication record not found'], 404);
            }
            // dd($med);


            $gIds     = explode(',', $med->inv_generic_ids);
            $doses    = explode(',', $med->dose);
            $routes   = explode(',', $med->route_ids);
            $freqs    = explode(',', $med->frequency_ids);
            $days     = explode(',', $med->days);
            $i        = array_search($genericId, $gIds);

            $genericName   = InventoryGeneric::find($gIds[$i])->name              ?? '';
            $routeName     = MedicationRoutes::find($routes[$i])->name             ?? '';
            $frequencyName = MedicationFrequency::find($freqs[$i])->name          ?? '';
            $brandName     = '';

            return response()->json([
                'source'                  => 'medication',
                'code'                  => $med->code,
                'org_id'                  => $med->org_id,
                'org_name'                => $med->org_name,
                'site_id'                 => $med->site_id,
                'site_name'               => $med->site_name,
                'mr_code'                 => $med->mr_code,
                'patient_name'            => $med->patient_name,
                'transaction_type_id'     => $med->transaction_type_id,
                'transaction_type_name'   => $med->transaction_type_name,
                'inv_location_id'         => $med->inv_location_id,
                'location_name'           => $med->location_name,
                'service_id'              => $med->service_id,
                'service_name'            => $med->service_name,
                'service_mode_id'         => $med->service_mode_id,
                'service_mode_name'       => $med->service_mode_name,
                'physician_id'            => $med->responsible_physician,
                'physician_name'          => $med->physician_name,
                'billing_cc'              => $med->billing_cc,
                'billing_cc_name'         => $med->billing_cc_name,
                'service_group_id'        => $med->service_group_id,
                'service_group_name'      => $med->service_group_name,
                'service_type_id'         => $med->service_type_id,
                'service_type_name'       => $med->service_type_name,
                'generic_id'              => $gIds[$i]   ?? null,
                'generic_name'            => $genericName,
                'brand_name'              => $brandName,
                'dose'                    => $doses[$i]  ?? '',
                'route_id'                => $routes[$i] ?? '',
                'route_name'              => $routeName,
                'frequency_id'            => $freqs[$i]  ?? '',
                'frequency_name'          => $frequencyName,
                'days'                    => $days[$i]   ?? '',
            ]);
        }

        if ($source === 'material') {
                // $mat = MaterialConsumptionRequisition::from('material_consumption_requisition as m')
                // ->select([
                //     'm.*',
                //     'o.organization                as org_name',
                //     's.name                        as site_name',
                //     'p.name                        as patient_name',
                //     'itt.name                      as transaction_type_name',
                //     'sl.name                       as location_name',
                //     'sm.name                       as service_mode_name',
                //     'sv.name                       as service_name',
                //     'e.name                        as physician_name',
                //     'bcc.name                      as billing_cc_name',
                //     'sg.name                       as service_group_name',
                //     'st.name                       as service_type_name',
                //     'im.transaction_qty            as issuedQty',
                    
                //     'ib.site_balance              as maxQty'  // Added this line
                // ])
                // ->join('organization                as o',   'o.id',  '=', 'm.org_id')
                // ->join('org_site                    as s',   's.id',  '=', 'm.site_id')
                // ->leftJoin('patient                   as p',   'p.mr_code', '=', 'm.mr_code')
                // ->join('inventory_transaction_type  as itt', 'itt.id',     '=', 'm.transaction_type_id')
                // ->leftJoin('service_location            as sl',   'sl.id',  '=', 'm.inv_location_id')
                // ->leftJoin('service_mode                as sm',   'sm.id',  '=', 'm.service_mode_id')
                // ->leftJoin('services                    as sv',   'sv.id',  '=', 'm.service_id')
                // ->leftJoin('service_group               as sg',   'sg.id',  '=', 'sv.group_id')
                // ->leftJoin('service_type                as st',   'st.id',  '=', 'sg.type_id')
                // ->leftJoin('employee                    as e',   'e.id',  '=', 'm.physician_id')
                // ->leftJoin  ('costcenter                  as bcc', 'bcc.id',     '=', 'm.billing_cc')
                // ->leftJoin  ('inventory_management as im', 'im.ref_document_no',     '=', 'm.code')
                // ->leftJoin(DB::raw('(SELECT * FROM inventory_balance ORDER BY id DESC LIMIT 1) as ib'), function($join) {
                //     $join->on('ib.generic_id', '=', 'im.inv_generic_id')
                //         ->on('ib.brand_id', '=', 'im.brand_id')
                //         ->on('ib.batch_no', '=', 'im.batch_no');
                // })
                // ->where('m.id', $id)
                // ->first();
                
                $mat = MaterialConsumptionRequisition::from('material_consumption_requisition as m')
                ->select([
                    'm.id',
                    'm.code',
                    'm.org_id',
                    'm.site_id',
                    'm.mr_code',
                    'm.transaction_type_id',
                    'm.inv_location_id',
                    'm.service_mode_id',
                    'm.service_id',
                    'm.physician_id',
                    'm.billing_cc',
                    'm.generic_id',
                    'm.qty',
                    'm.status',
                    'o.organization                as org_name',
                    's.name                        as site_name',
                    'p.name                        as patient_name',
                    'itt.name                      as transaction_type_name',
                    'sl.name                       as location_name',
                    'sm.name                       as service_mode_name',
                    'sv.name                       as service_name',
                    'e.name                        as physician_name',
                    'bcc.name                      as billing_cc_name',
                    'sg.name                       as service_group_name',
                    'st.name                       as service_type_name',
                    DB::raw('COALESCE(SUM(im.transaction_qty), 0) as issuedQty'),
                    // 'ib.site_balance              as maxQty'
                ])
                ->join('organization                as o',   'o.id',  '=', 'm.org_id')
                ->join('org_site                    as s',   's.id',  '=', 'm.site_id')
                ->leftJoin('patient                   as p',   'p.mr_code', '=', 'm.mr_code')
                ->join('inventory_transaction_type  as itt', 'itt.id',     '=', 'm.transaction_type_id')
                ->leftJoin('service_location            as sl',   'sl.id',  '=', 'm.inv_location_id')
                ->leftJoin('service_mode                as sm',   'sm.id',  '=', 'm.service_mode_id')
                ->leftJoin('services                    as sv',   'sv.id',  '=', 'm.service_id')
                ->leftJoin('service_group               as sg',   'sg.id',  '=', 'sv.group_id')
                ->leftJoin('service_type                as st',   'st.id',  '=', 'sg.type_id')
                ->leftJoin('employee                    as e',   'e.id',  '=', 'm.physician_id')
                ->leftJoin('costcenter                  as bcc', 'bcc.id',     '=', 'm.billing_cc')
                ->leftJoin('inventory_management as im', function($join) use ($genericId) {
                    $join->on('im.ref_document_no', '=', 'm.code')
                         ->where('im.inv_generic_id', '=', DB::raw($genericId));
                })
                ->leftJoin(DB::raw('(
                    SELECT ib1.*
                    FROM inventory_balance ib1
                    INNER JOIN (
                        SELECT MAX(id) as max_id
                        FROM inventory_balance
                        GROUP BY generic_id, brand_id, batch_no
                    ) ib2 ON ib1.id = ib2.max_id
                ) as ib'), function($join) {
                    $join->on('ib.generic_id', '=', 'im.inv_generic_id')
                        ->on('ib.brand_id', '=', 'im.brand_id')
                        ->on('ib.batch_no', '=', 'im.batch_no');
                })
                ->where('m.id', $id)
                ->groupBy([
                    'm.id', 'm.code', 'm.org_id', 'm.site_id', 'm.mr_code',
                    'm.transaction_type_id', 'm.inv_location_id', 'm.service_mode_id',
                    'm.service_id', 'm.physician_id', 'm.billing_cc', 'm.generic_id',
                    'm.qty', 'm.status',
                    'o.organization', 's.name', 'p.name', 'itt.name', 'sl.name',
                    'sm.name', 'sv.name', 'e.name', 'bcc.name', 'sg.name', 'st.name',
                    'ib.site_balance'
                ])
                ->first();
                
            if (! $mat) {
                return response()->json(['error'=>'Material record not found'], 404);
            }

            $gIds = explode(',', $mat->generic_id);
            $qtys = explode(',', $mat->qty);
            $i    = array_search($genericId, $gIds);

            $genericName = InventoryGeneric::find($gIds[$i])->name ?? '';
            $originalQty = $qtys[$i] ?? 0;
            $issuedQty = $mat->issuedQty;
            $maxQty = $mat->maxQty;
            // dd($maxQty);

            $remainingQty = max(0, $originalQty - $issuedQty); 
            // dd($originalQty,$issuedQty,$remainingQty);



            return response()->json([
                'source'                 => 'material',
                'code'                  => $mat->code,
                'org_id'                 => $mat->org_id,
                'org_name'               => $mat->org_name,
                'site_id'                => $mat->site_id,
                'site_name'              => $mat->site_name,
                'mr_code'                => $mat->mr_code,
                'patient_name'           => $mat->patient_name,
                'transaction_type_id'    => $mat->transaction_type_id,
                'transaction_type_name'  => $mat->transaction_type_name,
                'inv_location_id'        => $mat->inv_location_id,
                'location_name'          => $mat->location_name,
                'service_mode_id'        => $mat->service_mode_id,
                'service_mode_name'      => $mat->service_mode_name,
                'service_id'             => $mat->service_id,
                'service_name'           => $mat->service_name,
                'service_group_name'     => $mat->service_group_name,
                'service_type_name'      => $mat->service_type_name,
                'physician_id'           => $mat->physician_id,
                'physician_name'         => $mat->physician_name,
                'billing_cc'             => $mat->billing_cc,
                'billing_cc_name'        => $mat->billing_cc_name,
                'generic_id'             => $gIds[$i]      ?? null,
                'generic_name'           => $genericName,
                'brand_id'               => null,         
                'brand_name'             => '',
                // 'demand_qty'             => $qtys[$i]      ?? '',
                'demand_qty'             => $remainingQty      ?? '0',
                'max_qty'             => $maxQty      ?? '0',
            ]);
        }

        return response()->json(['error'=>'Unknown source'], 400);
    }

    // public function GetBatchNo(Request $request)
    // {
    //     $orgId     = $request->query('orgId');
    //     $siteId    = $request->query('siteId');
    //     $brandId   = $request->query('brandId');
    //     $genericId = $request->query('genericId');

    //     if (! $orgId || ! $siteId || ! $brandId || ! $genericId) {
    //         return response()->json(null, 400);
    //     }

    //     // 1) find the last matching balance
    //     $balance = InventoryBalance::query()
    //         ->where('org_id',     $orgId)
    //         ->where('site_id',    $siteId)
    //         ->where('brand_id',   $brandId)
    //         ->where('generic_id', $genericId)
    //         ->orderByDesc('id')
    //         ->first(['batch_no','management_id','site_balance']);

    //     if (! $balance) {
    //         // no inventory at all
    //         return response()->json(null, 200);
    //     }

    //     $batchNo    = $balance->batch_no;
    //     $mgmtId     = $balance->management_id;
    //     $siteBalance = $balance->site_balance; // <-- Add this

    //     // // 2) load the management record
    //     $mgmt = InventoryManagement::query()
    //         ->find($mgmtId, [
    //             'inv_generic_id',
    //             'brand_id',
    //             'batch_no',
    //             'expiry_date',
    //         ]);

    //     if (! $mgmt) {
    //         // management row missing
    //         return response()->json([
    //             'batch_no'    => $batchNo,
    //             'expiry_date' => null,
    //             'site_balance' => $siteBalance, // <-- Add this
    //         ]);
    //     }

    //     // 3) explode the CSV fields into parallel arrays
    //     $gIds    = explode(',', $mgmt->inv_generic_id);
    //     $bIds    = explode(',', $mgmt->brand_id);
    //     $bNos    = explode(',', $mgmt->batch_no);
    //     $exps    = explode(',', $mgmt->expiry_date);

    //     $expiryDate = null;

    //     // 4) find the array index where everything matches
    //     foreach ($bNos as $i => $b) {
    //         if (
    //             isset($gIds[$i], $bIds[$i], $exps[$i])
    //             && $b === $batchNo
    //             && $gIds[$i] == $genericId
    //             && $bIds[$i] == $brandId
    //         ) {
    //             // convert UNIX timestamp (or whatever you're storing) to Y-m-d
    //             $expiryDate = date('Y-m-d', (int)$exps[$i]);
    //             break;
    //         }
    //     }

    //     return response()->json([
    //         'batch_no'    => $batchNo,
    //         'expiry_date' => $expiryDate,
    //         'site_balance' => $siteBalance, // <-- Add this
    //     ]);
    // }

    // public function GetBatchNo(Request $request)
    // {
    //     $orgId     = $request->query('orgId');
    //     $siteId    = $request->query('siteId');
    //     $brandId   = $request->query('brandId');
    //     $genericId = $request->query('genericId');

    //     if (! $orgId || ! $siteId || ! $brandId || ! $genericId) {
    //         return response()->json(null, 400);
    //     }

    //     // Step 1: Get all balances for that brand/generic/org/site
    //     $balances = InventoryBalance::query()
    //         ->where('org_id',     $orgId)
    //         ->where('site_id',    $siteId)
    //         ->where('brand_id',   $brandId)
    //         ->where('generic_id', $genericId)
    //         ->orderByDesc('id') 
    //         ->get(['batch_no','management_id','site_balance']);

    //     $nearestBatch = null;
    //     $nearestExpiry = null;
    //     $nearestBalance = null;

    //     foreach ($balances as $balance) {
    //         $mgmt = InventoryManagement::find($balance->management_id, [
    //             'inv_generic_id',
    //             'brand_id',
    //             'batch_no',
    //             'expiry_date',
    //         ]);

    //         if (! $mgmt) continue;

    //         $gIds = explode(',', $mgmt->inv_generic_id);
    //         $bIds = explode(',', $mgmt->brand_id);
    //         $bNos = explode(',', $mgmt->batch_no);
    //         $exps = explode(',', $mgmt->expiry_date);

    //         foreach ($bNos as $i => $b) {
    //             if (
    //                 isset($gIds[$i], $bIds[$i], $exps[$i])
    //                 && $b === $balance->batch_no
    //                 && $gIds[$i] == $genericId
    //                 && $bIds[$i] == $brandId
    //             ) {
    //                 $expTimestamp = (int) $exps[$i];
    //                 if ($expTimestamp <= 0) continue;

    //                 // Compare to find nearest expiry date
    //                 if (is_null($nearestExpiry) || $expTimestamp < $nearestExpiry) {
    //                     $nearestBatch = $b;
    //                     $nearestExpiry = $expTimestamp;
    //                     $nearestBalance = $balance->site_balance;
    //                 }
    //             }
    //         }
    //     }

    //     if (! $nearestBatch) {
    //         return response()->json(null, 200);
    //     }

    //     return response()->json([
    //         'batch_no'     => $nearestBatch,
    //         'expiry_date'  => date('Y-m-d', $nearestExpiry),
    //         'site_balance' => $nearestBalance,
    //     ]);
    // }

    // public function GetBatchNo(Request $request)
    // {
    //     $orgId     = $request->query('orgId');
    //     $siteId    = $request->query('siteId');
    //     $brandId   = $request->query('brandId');
    //     $genericId = $request->query('genericId');

    //     if (! $orgId || ! $siteId || ! $brandId || ! $genericId) {
    //         return response()->json(null, 400);
    //     }

    //     // Step 1: Get all balances for that brand/generic/org/site
    //     $balances = InventoryBalance::from(DB::raw('(
    //             SELECT * FROM inventory_balance
    //             WHERE site_balance > 0
    //             ORDER BY id DESC
    //         ) as ib'))
    //         ->where('org_id',     $orgId)
    //         ->where('site_id',    $siteId)
    //         ->where('brand_id',   $brandId)
    //         ->where('generic_id', $genericId)
    //         ->groupBy('batch_no')
    //         ->get(['batch_no','management_id','site_balance']);

    //     // $balances = InventoryBalance::query()
    //     // ->where('org_id',     $orgId)
    //     // ->where('site_id',    $siteId)
    //     // ->where('brand_id',   $brandId)
    //     // ->where('generic_id', $genericId)
    //     // ->where('site_balance', '>', 0) // ✅ Only include rows where balance > 0
    //     // ->orderByDesc('id')
    //     // ->get(['batch_no','management_id','site_balance']);


    //     $batchList = [];

    //     foreach ($balances as $balance) {
    //         $mgmt = InventoryManagement::find($balance->management_id, [
    //             'inv_generic_id',
    //             'brand_id',
    //             'batch_no',
    //             'expiry_date',
    //         ]);

    //         if (! $mgmt) continue;

    //         $gIds = explode(',', $mgmt->inv_generic_id);
    //         $bIds = explode(',', $mgmt->brand_id);
    //         $bNos = explode(',', $mgmt->batch_no);
    //         $exps = explode(',', $mgmt->expiry_date);

    //         foreach ($bNos as $i => $b) {
    //             if (
    //                 isset($gIds[$i], $bIds[$i], $exps[$i])
    //                 && $b === $balance->batch_no
    //                 && $gIds[$i] == $genericId
    //                 && $bIds[$i] == $brandId
    //             ) {
    //                 $expTimestamp = (int) $exps[$i];
    //                 if ($expTimestamp <= 0) continue;

    //                 $batchList[] = [
    //                     'batch_no'     => $b,
    //                     'expiry_date'  => date('Y-m-d', $expTimestamp),
    //                     'site_balance' => $balance->site_balance,
    //                 ];
    //             }
    //         }
    //     }

    //     if (empty($batchList)) {
    //         return response()->json([], 200);
    //     }

    //     // Sort batch list by expiry date
    //     usort($batchList, function ($a, $b) {
    //         return strtotime($a['expiry_date']) - strtotime($b['expiry_date']);
    //     });

    //     return response()->json($batchList);
    // }

   public function GetBatchNo(Request $request)
    {
        $orgId     = $request->query('orgId');
        $siteId    = $request->query('siteId');
        $brandId   = $request->query('brandId');
        $genericId = $request->query('genericId');

        if (! $orgId || ! $siteId || ! $brandId || ! $genericId) {
            return response()->json(null, 400);
        }

        // 1) Pick the latest 'id' per batch_no (no balance filter inside subquery).
        // 2) In the outer query, require site_balance > 0 to drop batches whose latest entry is zero.
        $balances = InventoryBalance::where('org_id',     $orgId)
            ->where('site_id',    $siteId)
            ->where('brand_id',   $brandId)
            ->where('generic_id', $genericId)
            ->whereRaw(
                'id IN (
                    SELECT MAX(id)
                    FROM inventory_balance
                    WHERE
                        org_id     = ?
                    AND site_id    = ?
                    AND brand_id   = ?
                    AND generic_id = ?
                    GROUP BY batch_no
                )',
                [$orgId, $siteId, $brandId, $genericId]
            )
            ->where('site_balance', '>', 0) 
            ->get(['batch_no', 'management_id', 'site_balance']);

        $batchList = [];

        foreach ($balances as $balance) {
            $mgmt = InventoryManagement::find($balance->management_id, [
                'inv_generic_id',
                'brand_id',
                'batch_no',
                'expiry_date',
            ]);
            if (! $mgmt) continue;

            $gIds = explode(',', $mgmt->inv_generic_id);
            $bIds = explode(',', $mgmt->brand_id);
            $bNos = explode(',', $mgmt->batch_no);
            $exps = explode(',', $mgmt->expiry_date);

            foreach ($bNos as $i => $b) {
                if (
                    isset($gIds[$i], $bIds[$i], $exps[$i])
                    && $b === $balance->batch_no
                    && $gIds[$i] == $genericId
                    && $bIds[$i] == $brandId
                ) {
                    $expTimestamp = (int) $exps[$i];
                    if ($expTimestamp <= 0) continue;

                    $batchList[] = [
                        'batch_no'     => $b,
                        'expiry_date'  => date('Y-m-d', $expTimestamp),
                        'site_balance' => $balance->site_balance,
                    ];
                }
            }
        }

        if (empty($batchList)) {
            return response()->json([], 200);
        }

        // Sort by expiry_date (earliest first)
        usort($batchList, function ($a, $b) {
            return strtotime($a['expiry_date']) - strtotime($b['expiry_date']);
        });
        // dd($batchList);

        return response()->json($batchList);
    }


    public function AddIssueDispense(IssueDispenseRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->issue_and_dispense)[0];
        if ($add == 0) {
            abort(403, 'Forbidden');
        }
        // Get validated data
        $validated = $request->validated();
        $itemCount = count($request->id_generic);
        $success = true;
        $message = '';

        $inventory = new InventoryManagement();
        // Required fields
        $inventory->transaction_type_id = $validated['id_transactiontype'];
        $inventory->org_id = $validated['id_org'];
        $inventory->site_id = $validated['id_site'];
        $inventory->source = $validated['id_source'];
        $inventory->destination = $validated['id_destination'];
        
        // Handle MR related fields
        if (isset($validated['id_mr']) && !empty($validated['id_mr'])) {
            $inventory->mr_code = $validated['id_mr'];
            
            // If MR exists, check for service details
            if (isset($validated['id_service']) && !empty($validated['id_service'])) {
                $inventory->service_id = $validated['id_service'];
                $inventory->service_mode_id = $validated['id_servicemode'] ?? null;
                $inventory->billing_cc = $validated['id_billingcc'] ?? null;
                $inventory->performing_cc = $validated['id_performing_cc'] ?? null;
                $inventory->resp_physician = $validated['id_physician'] ?? null;
            }
        }

        // Optional reference document
        $inventory->ref_document_no = $validated['id_reference_document'] ?? null;
        
        // Remarks field
        $inventory->remarks = $validated['id_remarks'] ?? null;

        // Handle item specific fields based on count
        if ($itemCount > 1) {
            // Multiple items - use comma separated values
            $inventory->inv_generic_id = implode(',', $validated['id_generic']);
            $inventory->brand_id = implode(',', $validated['id_brand']);
            $inventory->batch_no = implode(',', $validated['id_batch']);
            $inventory->demand_qty = implode(',', $validated['id_demand_qty']);
            
            // Format expiry dates
            $formattedDates = array_map(function($date) {
                return Carbon::createFromFormat('Y-m-d', $date)->timestamp;
            }, $validated['id_expiry']);
            $inventory->expiry_date = implode(',', $formattedDates);
            
            $inventory->transaction_qty = implode(',', $validated['id_qty']);
        } else {
            $inventory->inv_generic_id = $validated['id_generic'][0];
            $inventory->brand_id = $validated['id_brand'][0];
            $inventory->batch_no = $validated['id_batch'][0];
            $inventory->expiry_date = Carbon::createFromFormat('Y-m-d', $validated['id_expiry'][0])->timestamp;
            $inventory->transaction_qty = $validated['id_qty'][0];
            $inventory->demand_qty = $validated['id_demand_qty'][0];
        }
        
        $inventory->status = 1;
        $inventory->user_id = auth()->id();
        $inventory->logid = auth()->user()->username ?? auth()->id();
        $inventory->effective_timestamp = now()->timestamp;
        $inventory->timestamp = now()->timestamp;
        $inventory->last_updated = now()->timestamp;

        if (!$inventory->save()) {
            $success = false;
            $message = 'Failed to save inventory record';
        }

        // Get transaction type rule for balance calculation
        $rule = DB::table('inventory_transaction_type')
            ->select('applicable_location_to', 'source_action', 'destination_action')
            ->where('id', $validated['id_transactiontype'])
            ->first();

        // Process each item separately for inventory_balance
        for ($i = 0; $i < $itemCount; $i++) {
            $genId = $validated['id_generic'][$i];
            $brandId = $validated['id_brand'][$i];
            $batchNo = $validated['id_batch'][$i];
            $qty = (int)$validated['id_qty'][$i];
            $expTs = Carbon::createFromFormat('Y-m-d', $validated['id_expiry'][$i])->timestamp;

            // Get previous balances
            $prevOrgRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id', $brandId)
                ->where('batch_no', $batchNo)
                ->where('org_id', $validated['id_org'])
                ->orderBy('id', 'desc')
                ->first();
            $prevOrgBalance = $prevOrgRow ? $prevOrgRow->org_balance : 0;

            $prevSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id', $brandId)
                ->where('batch_no', $batchNo)
                ->where('org_id', $validated['id_org'])
                ->where('site_id', $validated['id_site'])
                ->orderBy('id', 'desc')
                ->first();
            $prevSiteBalance = $prevSiteRow ? $prevSiteRow->site_balance : 0;

            $newOrgBalance = $prevOrgBalance - $qty;
            $newSiteBalance = $prevSiteBalance - $qty;

            $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
            $remarkText = "Issue & Dispense Transaction by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSiteBalance}";

            // Create inventory balance record
            InventoryBalance::create([
                'management_id' => $inventory->id,
                'generic_id' => $genId,
                'brand_id' => $brandId,
                'batch_no' => $batchNo,
                'expiry_date' => $expTs,
                'org_id' => $validated['id_org'],
                'site_id' => $validated['id_site'],
                'org_balance' => $newOrgBalance,
                'site_balance' => $newSiteBalance,
                'remarks' => $remarkText,
                'timestamp' => now()->timestamp,
            ]);
        }

        if ($success) {
            return response()->json([
                'success' => 'Issue & Dispense records have been added successfully',
                'reload' => true
            ]);
        } else {
            return response()->json([
                'error' => $message,
                'reload' => false
            ]);
        }
    }


    // public function InventoryManagement()
    // {
    //     $colName = 'inventory_management';
    //     if (PermissionDenied($colName)) {
    //         abort(403); 
    //     }
    //     $user = auth()->user();
    //     $Categories = InventoryCategory::where('status', 1)->get();
    //     return view('dashboard.inventory-management', compact('user','Categories'));
    // }

    // public function AddInventoryManagement(InventoryManagementRequest $request)
    // {
    //     $rights = $this->rights;
    //     $add = explode(',', $rights->inventory_management)[0];
    //     if($add == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $TransactionTypeID = $request->input('im_transactiontype');
    //     $Organization = $request->input('im_org');
    //     $Site = $request->input('im_site');
    //     $BrandId = $request->input('im_brand');
    //     $BatchNo = $request->input('im_batch_no');
    //     $ExpiryDate = $request->input('im_expiry');
    //     $ExpiryDate = Carbon::createFromFormat('Y-m-d', $ExpiryDate)->timestamp;
    //     $Rate = $request->input('im_rate');
    //     $Qty = $request->input('im_qty');
    //     $ReferenceDocument = $request->input('im_reference_document');
    //     $Origin = $request->input('im_origin');
    //     $Destination = $request->input('im_destination');
    //     $Edt = $request->input('im_edt');

    //     $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
    //     $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
    //     $EffectDateTime->subMinute(1);
    //     if ($EffectDateTime->isPast()) {
    //         $status = 1; //Active
    //     } else {
    //         $status = 0; //Inactive
    //     }

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $last_updated = $this->currentDatetime;
    //     $timestamp = $this->currentDatetime;
    //     $logId = null;
    //     $InventoryExist = false;
    //     $transactionTypes = InventoryTransactionType::select('transaction_type', 'name')
    //     ->where('id', $TransactionTypeID)
    //     ->first();
    //     $transactionType = $transactionTypes->transaction_type;
    //     $transactionName = $transactionTypes->name;
    //     $OriginType = null;
    //     $DestinationType = null;
    //     $documentType = null;
    //     $Balance = InventoryBalance::where('org_id', $Organization)
    //     ->where('site_id', $Site)
    //     ->where('brand_id', $BrandId)
    //     ->first();
    //     if($transactionType == 'opening balance')
    //     {
    //         $Title = $transactionName.' added';
    //         $query = InventoryManagement::where('org_id', $Organization)
    //         ->where('site_id', $Site)
    //         ->where('brand_id', $BrandId);
    //         if ($EffectDateTime->isPast()) {
    //             $status = 1; 
    //             $query->where('status', 1);
    //         }
    //         $InventoryExist = $query->exists();
        
    //         if ($Balance) {
    //             $updatedOrgBalance = $Balance->org_balance + $Qty;
    //             $updatedSiteBalance = $Balance->site_balance + $Qty;
    //             $Balance->org_balance = $updatedOrgBalance; 
    //             $Balance->site_balance = $updatedSiteBalance;
    //             $Balance->last_updated = $this->currentDatetime; 
    //         } else {
    //             $Balance = new InventoryBalance();
    //             $Balance->org_id = $Organization;
    //             $Balance->site_id = $Site;
    //             $Balance->brand_id = $BrandId;
    //             $Balance->org_balance = $Qty; 
    //             $Balance->site_balance = $Qty;
    //             $Balance->status = $status;
    //             $Balance->last_updated = $this->currentDatetime;
    //         }
    //     }
    //     else if($transactionType == 'addition')
    //     {
    //         $Title = ucwords($transactionName).' added';
    //         $OriginType = 'vendor';
    //         $DestinationType = 'org_site';
    //         $documentType = 'open_text';
    //         if ($Balance) {
    //             $updatedOrgBalance = $Balance->org_balance + $Qty;
    //             $updatedSiteBalance = $Balance->site_balance + $Qty;
    //             $Balance->org_balance = $updatedOrgBalance; 
    //             $Balance->site_balance = $updatedSiteBalance;
    //             $Balance->last_updated = $this->currentDatetime; 
    //         } else {
    //             $Balance = new InventoryBalance();
    //             $Balance->org_id = $Organization;
    //             $Balance->site_id = $Site;
    //             $Balance->brand_id = $BrandId;
    //             $Balance->org_balance = $Qty; 
    //             $Balance->site_balance = $Qty;
    //             $Balance->status = $status;
    //             $Balance->last_updated = $this->currentDatetime;
    //         }
    //     }
    //     else if($transactionType == 'reduction')
    //     {
    //         $Title = ucwords($transactionName);
    //         $OriginType = 'org_site';
    //         $DestinationType = 'vendor';
    //         $documentType = 'inventory_management';
    //         if ($Balance) {
    //             if($Balance->site_balance < $Qty)
    //             {
    //                 return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //             }
    //             $updatedOrgBalance = $Balance->org_balance - $Qty;
    //             $updatedSiteBalance = $Balance->site_balance - $Qty;
    //             $Balance->org_balance = $updatedOrgBalance; 
    //             $Balance->site_balance = $updatedSiteBalance;
    //             $Balance->last_updated = $this->currentDatetime; 
    //         } else {
    //             return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //         }
    //     }
    //     else if($transactionType == 'transfer')
    //     {
    //         $Title = ucwords($transactionName).' completed';
    //         $OriginType = 'org_site';
    //         $DestinationType = 'org_site';
    //         if ($Balance) {
    //             $updatedOrgBalance = $Balance->org_balance - $Qty;
    //             $updatedSiteBalance = $Balance->site_balance - $Qty;
    //             $Balance->org_balance = $updatedOrgBalance; 
    //             $Balance->site_balance = $updatedSiteBalance;
    //             $Balance->last_updated = $this->currentDatetime; 
    //         } else {
    //             return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //         }

    //         $DestinationBalance = InventoryBalance::where('org_id', $Organization)
    //         ->where('site_id', $Destination)
    //         ->where('brand_id', $BrandId)
    //         ->first();
    //         if ($DestinationBalance) {
    //             $updatedOrgBalance = $DestinationBalance->org_balance + $Qty;
    //             $updatedSiteBalance = $DestinationBalance->site_balance + $Qty;
    //             $DestinationBalance->org_balance = $updatedOrgBalance; 
    //             $DestinationBalance->site_balance = $updatedSiteBalance;
    //             $DestinationBalance->last_updated = $this->currentDatetime; 
    //             $DestinationBalance->save();
    //         } else {
    //             $DestinationBalance = new InventoryBalance();
    //             $DestinationBalance->org_id = $Organization;
    //             $DestinationBalance->site_id = $Destination;
    //             $DestinationBalance->brand_id = $BrandId;
    //             $DestinationBalance->org_balance = $Qty; 
    //             $DestinationBalance->site_balance = $Qty;
    //             $DestinationBalance->status = $status;
    //             $DestinationBalance->last_updated = $this->currentDatetime;
    //             $DestinationBalance->save();
    //         }
    //     }
    //     else if($transactionType == 'general consumption')
    //     {
    //         $Title = ucwords($transactionName).' added';
    //         $OriginType = 'org_site';
    //         $documentType = 'material_consumption_requisition';
    //         if ($Balance) {
    //             if($Balance->site_balance < $Qty)
    //             {
    //                 return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //             }
    //             $updatedOrgBalance = $Balance->org_balance - $Qty;
    //             $updatedSiteBalance = $Balance->site_balance - $Qty;
    //             $Balance->org_balance = $updatedOrgBalance; 
    //             $Balance->site_balance = $updatedSiteBalance;
    //             $Balance->last_updated = $this->currentDatetime; 
    //         } else {
    //             return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //         }
            
    //     }
    //     else if($transactionType == 'patient consumption')
    //     {
    //         $Title = ucwords($transactionName).' completed';
    //         $OriginType = 'vendor';
    //         $DestinationType = 'patient';
    //         $documentType = 'material_consumption_requisition';
    //         if ($Balance) {
    //             if($Balance->site_balance < $Qty)
    //             {
    //                 return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //             }
    //             $updatedOrgBalance = $Balance->org_balance - $Qty;
    //             $updatedSiteBalance = $Balance->site_balance - $Qty;
    //             $Balance->org_balance = $updatedOrgBalance; 
    //             $Balance->site_balance = $updatedSiteBalance;
    //             $Balance->last_updated = $this->currentDatetime; 
    //         } else {
    //             return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //         }
    //     }
    //     else if($transactionType == 'reversal')
    //     {
    //         $Title = ucwords($transactionName);
    //         $InventoryManagement = InventoryManagement::select('from', 'from_type', 'to', 'to_type')
    //         ->where('id', $ReferenceDocument)
    //         ->first();
    //         $Destination = $InventoryManagement->from;
    //         $DestinationType = $InventoryManagement->from_type;
    //         $Origin = $InventoryManagement->to;
    //         $OriginType = $InventoryManagement->to_type;
    //         $documentType = 'inventory_management';

    //         $OriginBalance = InventoryBalance::where('org_id', $Organization)
    //         ->where('site_id', $Origin)
    //         ->where('brand_id', $BrandId)
    //         ->first();
    //         if ($OriginBalance) {
    //             $updatedOrgBalance = $OriginBalance->org_balance - $Qty;
    //             $updatedSiteBalance = $OriginBalance->site_balance - $Qty;
    //             $OriginBalance->org_balance = $updatedOrgBalance; 
    //             $OriginBalance->site_balance = $updatedSiteBalance;
    //             $OriginBalance->last_updated = $this->currentDatetime; 
    //             $OriginBalance->save();
    //         } 
    //         else {
    //             return response()->json(['info' => 'Insufficient Inventory available at the specified site.']);
    //         }
            
    //     }
    //     if ($InventoryExist) {
    //         return response()->json(['info' => 'Opening Balance already exists for this Organization, Site, and Brand.']);
    //     }
    //     else
    //     {
    //         $Inventory = new InventoryManagement();
    //         $Inventory->transaction_type_id = $TransactionTypeID;
    //         $Inventory->org_id = $Organization;
    //         $Inventory->site_id = $Site;
    //         $Inventory->brand_id = $BrandId;
    //         $Inventory->batch_no = $BatchNo;
    //         $Inventory->expiry_date = $ExpiryDate;
    //         $Inventory->rate = $Rate;
    //         $Inventory->qty = $Qty;
    //         $Inventory->document_no = $ReferenceDocument;
    //         $Inventory->document_type = $documentType;
    //         $Inventory->from = $Origin;
    //         $Inventory->from_type = $OriginType;
    //         $Inventory->to = $Destination;
    //         $Inventory->to_type = $DestinationType;
    //         $Inventory->status = $status;
    //         $Inventory->user_id = $sessionId;
    //         $Inventory->last_updated = $last_updated;
    //         $Inventory->timestamp = $timestamp;
    //         $Inventory->effective_timestamp = $Edt;
    //         $Inventory->save();

    //         if (empty($Inventory->id)) {
    //             return response()->json(['error' => 'Failed to Add Inventory.']);
    //         }

    //         $logs = Logs::create([
    //             'module' => 'inventory',
    //             'content' => "Inventory has been added by '{$sessionName}'",
    //             'event' => 'add',
    //             'timestamp' => $timestamp,
    //         ]);

    //         $logId = $logs->id;
    //         $Inventory->logid = $logs->id;
    //         $Inventory->save();
    //         $Balance->save();
    //         return response()->json(['success' => ''.$Title.' successfully']);
    //     }
    // }

    // public function GetPreviousTransactions(Request $request)
    // {
    //     if ($request->has('brandId'))
    //     {
    //         $brandId = $request->input('brandId');
    //         $PreviousTransactions = InventoryManagement::select('inventory_management.id', 'organization.code')
    //         ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'inventory_management.transaction_type_id')
    //         ->join('organization', 'organization.id', '=', 'inventory_management.org_id')
    //         ->where('inventory_management.brand_id', $brandId)
    //         ->whereNotIn('inventory_transaction_type.transaction_type', ['opening balance'])
    //         ->get();
    //     }
    //     return response()->json($PreviousTransactions);
    // }

    

    public function GetOrgItemGeneric(Request $request)
    {
        if ($request->has('orgId'))
        {
            $orgId = $request->input('orgId');
            $Generics = InventoryGeneric::where('status', 1)
            ->where('org_id', $orgId)
            ->get();
        }
        return response()->json($Generics);
    }

    public function GetGenericBrand(Request $request)
    {
        if ($request->has('genericId'))
        {
            $genericId = $request->input('genericId');
            $Generics = InventoryBrand::where('status', 1)
            ->where('generic_id', $genericId)
            ->get();
        }
        return response()->json($Generics);
    }

    // public function GetBatchExpiryRate(Request $request)
    // {
    //     if ($request->has('inventoryId'))
    //     {
    //         $inventoryId = $request->input('inventoryId');
    //         $Data = InventoryManagement::select('inventory_management.id','inventory_management.expiry_date',
    //         'inventory_management.rate','inventory_management.qty')
    //         ->where('inventory_management.id', $inventoryId)
    //         ->first();
    //     }
    //     if ($Data) {
    //         $expiryDate = Carbon::createFromTimestamp($Data->expiry_date);
    //         $Data->expiry_date = $expiryDate;
    //         return response()->json($Data);
    //     }
    // }

    // public function GetSiteRequisition(Request $request)
    // {
    //     $siteId = $request->input('siteId');
    //     $transactiontypeId = $request->input('transactiontypeId');
    //     $Requsitions = MaterialConsumptionRequisition::where('status', 1)
    //                     ->where('site_id', $siteId)
    //                     ->where('transaction_type_id', $transactiontypeId)
    //                     ->get();

    //     return response()->json($Requsitions);
    // }

    // public function GetInventoryManagementData(Request $request)
    // {
    //     $rights = $this->rights;
    //     $view = explode(',', $rights->inventory_management)[1];
    //     if($view == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $ManageInventories = InventoryManagement::select('inventory_management.*',
    //     'inventory_transaction_type.name as TransactionTypeName',
    //     'inventory_transaction_type.transaction_type as TransactionType',
    //     'organization.organization as orgName',
    //     'organization.code as orgCode',
    //     'org_site.name as siteName',
    //     'inventory_brand.name as brandName',
    //     'inventory_category.name as catName',
    //     'inventory_subcategory.name as subCatName',
    //     'inventory_type.name as invTypeName',
    //     'inventory_generic.name as genericName')
    //     ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'inventory_management.transaction_type_id')
    //     ->leftJoin('organization', 'organization.id', '=', 'inventory_management.org_id')
    //     ->join('org_site', 'org_site.id', '=', 'inventory_management.site_id')
    //     ->join('inventory_brand', 'inventory_brand.id', '=', 'inventory_management.brand_id')
    //     ->join('inventory_category', 'inventory_category.id', '=', 'inventory_brand.cat_id')
    //     ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_brand.sub_catid')
    //     ->join('inventory_type', 'inventory_type.id', '=', 'inventory_brand.type_id')
    //     ->join('inventory_generic', 'inventory_generic.id', '=', 'inventory_brand.generic_id')
    //     ->orderBy('inventory_management.id', 'desc');

    //     $session = auth()->user();
    //     $sessionOrg = $session->org_id;
    //     if($sessionOrg != '0')
    //     {
    //         $ManageInventories->where('inventory_management.org_id', '=', $sessionOrg);
    //     }
    //     $ManageInventories = $ManageInventories;
    //     // ->get()
    //     // return DataTables::of($ManageInventories)
    //     return DataTables::eloquent($ManageInventories)
    //         ->filter(function ($query) use ($request) {
    //             if ($request->has('search') && $request->search['value']) {
    //                 $search = $request->search['value'];
    //                 $query->where(function ($q) use ($search) {
    //                     $q->where('inventory_management.id', 'like', "%{$search}%")
    //                         ->orWhere('organization.organization', 'like', "%{$search}%")
    //                         ->orWhere('org_site.name', 'like', "%{$search}%")
    //                         ->orWhere('inventory_brand.name', 'like', "%{$search}%")
    //                         ->orWhere('inventory_category.name', 'like', "%{$search}%")
    //                         ->orWhere('inventory_transaction_type.name', 'like', "%{$search}%")
    //                         ->orWhere('inventory_management.document_no', 'like', "%{$search}%");
    //                 });
    //             }
    //         })
    //         ->addColumn('id_raw', function ($ManageInventory) {
    //             return $ManageInventory->id;  // Raw ID value
    //         })
    //         ->editColumn('id', function ($ManageInventory) {
    //             $session = auth()->user();
    //             $sessionName = $session->name;
    //             $sessionId = $session->id;
    //             $effectiveDate = Carbon::createFromTimestamp($ManageInventory->effective_timestamp)->format('l d F Y - h:i A');
    //             $timestamp = Carbon::createFromTimestamp($ManageInventory->timestamp)->format('l d F Y - h:i A');
    //             $lastUpdated = Carbon::createFromTimestamp($ManageInventory->last_updated)->format('l d F Y - h:i A');
    //             $createdByName = getUserNameById($ManageInventory->user_id);
    //             $createdInfo = "
    //                 <b>Created By:</b> " . ucwords($createdByName) . "  <br>
    //                 <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
    //                 <b>RecordedAt:</b> " . $timestamp ." <br>
    //                 <b>LastUpdated:</b> " . $lastUpdated;
                    
    //             $OrgCode = $ManageInventory->orgCode;
    //             // $OrgName = $ManageInventory->orgName;
    //             $SiteName = $ManageInventory->siteName;
    //             $TransactionTypeName = $ManageInventory->TransactionTypeName;
    //             // $TransactionType = $ManageInventory->TransactionType;
    //             // $Code = $OrgCode.'-00000'.$ManageInventory->id;

    //             $idStr = str_pad($ManageInventory->id, 5, "0", STR_PAD_LEFT);
    //             $ModuleCode = 'IVM';
    //             $Code = $ModuleCode.'-'.$OrgCode.'-'.$idStr;

    //             $documentType = $ManageInventory->document_type;
    //             $document = '';
    //             if(!is_null($documentType) && $documentType != 'open_text')
    //             {
    //                 $documentNo = $ManageInventory->document_no;
    //                 if($documentType == 'material_consumption_requisition')
    //                 {
    //                     $document = DB::table($documentType)->where('id', $documentNo)->value('remarks');
    //                     $document = $document.'(Requisition)';
    //                 }
    //                 elseif($documentType == 'inventory_management')
    //                 {
    //                     $document = InventoryManagement::select('inventory_management.id',
    //                     'organization.code as orgCode')
    //                     ->join('organization', 'organization.id', '=', 'inventory_management.org_id')
    //                     ->where('inventory_management.id', '=', $documentNo)
    //                     ->first();
    //                     $documentCode = $OrgCode.'-00000'.$documentNo;
    //                     $document = $documentCode.' (Inventory Management)';
    //                 }
    //             }
    //             else
    //             {
    //                 // var_dump('else');
    //                 $document = $ManageInventory->document_no ? $ManageInventory->document_no : 'N/A';
    //                 $document = $document;
    //             }

    //             $sessionOrg = $session->org_id;
    //             $orgName = '';
    //             if($sessionOrg == 0)
    //             {
    //                 $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($ManageInventory->orgName);
    //             }

    //             return $Code.$orgName .'<hr class="mt-1 mb-1">
    //                 <b>Site: </b>'.ucwords($SiteName).'<br><hr class="mt-1 mb-1">
    //                 <b>Description: </b>'.ucwords($TransactionTypeName).'<br>
    //                 <hr class="mt-1 mb-1">
    //                 <b>Ref. Document No:</b><br>
    //                 '.ucwords($document).'<br><br>'
    //                 . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
    //                 . '<i class="fa fa-toggle-right"></i> View Details'
    //                 . '</span>';
                    
    //         })
    //         ->editColumn('brand_details', function ($ManageInventory) {
    //             $expiryDate = Carbon::createFromTimestamp($ManageInventory->expiry_date)->format('d F Y');
    //             return '
    //             <b>Generic: </b>'.ucwords($ManageInventory->genericName).'<br>
    //             <b>Brand: </b>'.ucwords($ManageInventory->brandName).'<br>
    //             <b>Batch #: </b>'.ucwords($ManageInventory->batch_no).'<br>
    //             <b>Expiry Date: </b>'.$expiryDate.'<br>
    //             <b>Rate: </b>'.number_format($ManageInventory->rate,2)  .'<br>
    //             ';
    //         })
    //         ->editColumn('transaction_details', function ($ManageInventory) {
    //             $Orgin = $ManageInventory->from;
    //             if (!is_null($Orgin)) {
    //                 $OrginTable = $ManageInventory->from_type;
    //                 $originName = DB::table($OrginTable)->where('id', $Orgin)->value('name');
    //                 if($OrginTable == 'vendor')
    //                 {
    //                     $originName = $originName.' (Vendor)';
    //                 }
    //                 elseif($OrginTable == 'org_site')
    //                 {
    //                     $originName = $originName.' (Site)';
    //                 }
    //             }
    //             else{
    //                 $originName = 'N/A';
    //             }

    //             $Destination = $ManageInventory->to;
    //             if (!is_null($Destination)) {
    //                 $DestinationTable = $ManageInventory->to_type;
    //                 if($DestinationTable == 'patient')
    //                 {
    //                     $destinationName = DB::table($DestinationTable)->where('mr_code', $Destination)->value('name');
    //                     $destinationName = $destinationName.' (Patient)';
    //                 }
    //                 else{
    //                     $destinationName = DB::table($DestinationTable)->where('id', $Destination)->value('name');
    //                     if($DestinationTable == 'vendor')
    //                     {
    //                         $destinationName = $destinationName.' (Vendor)';
    //                     }
    //                     elseif($DestinationTable == 'org_site')
    //                     {
    //                         $destinationName = $destinationName.' (Site)';
    //                     }
    //                 }
    //             }
    //             else{
    //                 $destinationName = 'N/A';
    //             }
    //             $OrgId = $ManageInventory->org_id;
    //             $SiteId = $ManageInventory->site_id;
    //             $BrandId = $ManageInventory->brand_id;

    //             $siteBalance = InventoryBalance::where('site_id', $SiteId)
    //             ->where('brand_id', $BrandId)
    //             ->value('site_balance');

    //             $OrgBalance = InventoryBalance::where('org_id', $OrgId)
    //             ->where('brand_id', '=', $BrandId) 
    //             ->sum('org_balance');

    //             return '
    //             <b>Transaction Qty: </b>'.($ManageInventory->qty).'<br>
    //             <b>Origin: </b>'.ucwords($originName).'<br>
    //             <b>Destination: </b>'.ucwords($destinationName).'<br>
    //             <b>Org Balance: </b>'.$OrgBalance.'<br>
    //             <b>Site Balance: </b>'.$siteBalance.'<br>
    //             ';
    //         })
    //         ->addColumn('action', function ($ManageInventory) {
    //                 $ManageInventoryId = $ManageInventory->id;
    //                 $logId = $ManageInventory->logid;
    //                 $TransactionTypeName = $ManageInventory->TransactionTypeName;
    //                 $TransactionType = $ManageInventory->TransactionType;
    //                 $Rights = $this->rights;
    //                 $edit = explode(',', $Rights->inventory_management)[2];
    //                 $actionButtons = '';
    //                 if ($edit == 1) {
    //                     $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-manageinventory" data-manageinventory-id="' . $ManageInventoryId . '">'
    //                     . '<i class="fa fa-edit"></i> Edit'
    //                     . '</button>';
    //                 }
    //                 $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="' . $logId . '">'
    //                 . '<i class="fa fa-eye"></i> View Logs'
    //                 . '</button>';
    //                 return $ManageInventory->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                    
    //                 // if ($TransactionType === 'opening balance') {
    //                 //     return '<span class="font-weight-bold">' . $TransactionTypeName . ' is not editable</span>'
    //                 //         . '<br><br><button type="button" class="btn btn-outline-info logs-modal" data-log-id="' . $logId . '">'
    //                 //         . '<i class="fa fa-eye"></i> View Logs'
    //                 //         . '</button>';
    //                 // } else {
    //                 //     return $ManageInventory->status ? '<button type="button" class="btn btn-outline-danger mr-2 edit-manageinventory" data-manageinventory-id="' . $ManageInventoryId . '">'
    //                 //         . '<i class="fa fa-edit"></i> Edit'
    //                 //         . '</button>'
    //                 //         . '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="' . $logId . '">'
    //                 //         . '<i class="fa fa-eye"></i> View Logs'
    //                 //         . '</button>' :
    //                 //         '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
    //                 // }
    //         })
    //         ->editColumn('status', function ($ManageInventory) {
    //             return $ManageInventory->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Expired</span>';
    //         })
    //         ->rawColumns(['action', 'status', 'transaction_details','brand_details',
    //         'id'])
    //         ->make(true);
    // }

    // public function UpdateInventoryManagementModal($id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->inventory_management)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $ManageInventories = InventoryManagement::select('inventory_management.*',
    //     'inventory_transaction_type.name as TransactionTypeName',
    //     'inventory_transaction_type.transaction_type as TransactionType',
    //     'organization.organization as orgName',
    //     'organization.code as orgCode',
    //     'org_site.name as siteName',
    //     'inventory_brand.name as brandName',
    //     'inventory_category.name as catName',
    //     'inventory_subcategory.name as subCatName',
    //     'inventory_type.name as invTypeName',
    //     'inventory_generic.name as genericName')
    //     ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'inventory_management.transaction_type_id')
    //     ->join('organization', 'organization.id', '=', 'inventory_management.org_id')
    //     ->join('org_site', 'org_site.id', '=', 'inventory_management.site_id')
    //     ->join('inventory_brand', 'inventory_brand.id', '=', 'inventory_management.brand_id')
    //     ->join('inventory_category', 'inventory_category.id', '=', 'inventory_brand.cat_id')
    //     ->join('inventory_subcategory', 'inventory_subcategory.id', '=', 'inventory_brand.sub_catid')
    //     ->join('inventory_type', 'inventory_type.id', '=', 'inventory_brand.type_id')
    //     ->join('inventory_generic', 'inventory_generic.id', '=', 'inventory_brand.generic_id')
    //     ->where('inventory_management.id', '=', $id)
    //     ->first();

    //     $documentType = $ManageInventories->document_type;
    //     $document = 'N/A';
    //     $documentNo = $ManageInventories->document_no;

    //     if(!is_null($documentType) && $documentType != 'opex_text')
    //     {
    //         if($documentType == 'material_consumption_requisition')
    //         {
    //             $document = DB::table($documentType)->where('id', $documentNo)->value('remarks');
    //         }
    //         elseif($documentType == 'inventory_management')
    //         {
    //             $document = InventoryManagement::select('inventory_management.id',
    //             'organization.code as orgCode')
    //             ->join('organization', 'organization.id', '=', 'inventory_management.org_id')
    //             ->where('inventory_management.id', '=', $documentNo)
    //             ->first();
    //             $OrgCode = $document->orgCode;
    //             $Code = $OrgCode.'-00000'.$documentNo;
    //             $document = $Code;
    //         }
    //     }
    //     else
    //     {
    //         $document = $ManageInventories->document_no ? $ManageInventories->document_no : 'N/A';
    //     }

    //     $Orgin = $ManageInventories->from;
    //     if (!is_null($Orgin)) {
    //         $OrginTable = $ManageInventories->from_type;
    //         $originName = DB::table($OrginTable)->where('id', $Orgin)->value('name');
    //     }
    //     else{
    //         $originName = 'N/A';
    //     }
    //     $Destination = $ManageInventories->to;
    //     if (!is_null($Destination)) {
    //         $DestinationTable = $ManageInventories->to_type;
    //         if($DestinationTable == 'patient')
    //         {
    //             $destinationName = DB::table($DestinationTable)->where('mr_code', $Destination)->value('mr_code');
    //         }
    //         else{
    //             $destinationName = DB::table($DestinationTable)->where('id', $Destination)->value('name');
    //         }
    //     }
    //     else{
    //         $destinationName = 'N/A';
    //     }

    //     $effective_timestamp = $ManageInventories->effective_timestamp;
    //     $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
    //     $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');
    //     $expiryDate = Carbon::createFromTimestamp($ManageInventories->expiry_date);

    //     $data = [
    //         'id' => $id,
    //         'orgId' => $ManageInventories->org_id,
    //         'orgName' => ucwords($ManageInventories->orgName),
    //         'siteId' => $ManageInventories->site_id,
    //         'siteName' => ucwords($ManageInventories->siteName),
    //         'transactionTypeId' => ($ManageInventories->transaction_type_id),
    //         'TransactionTypeName' => ucwords($ManageInventories->TransactionTypeName),
    //         'TransactionType' => ($ManageInventories->TransactionType),
    //         'brandId' => ucwords($ManageInventories->brand_id),
    //         'brandName' => ucwords($ManageInventories->brandName),
    //         'batchNo' => ucwords($ManageInventories->batch_no),
    //         'expiryDate' => $expiryDate,
    //         'rate' => $ManageInventories->rate,
    //         'qty' => $ManageInventories->qty,
    //         'documentId' => $documentNo,
    //         'document' => $document,
    //         'document_type' => $ManageInventories->document_type,
    //         'OriginName' => $originName,
    //         'OriginId' => $Orgin,
    //         'DestinationName' => $destinationName,
    //         'DestinationId' => $Destination,
    //         'effective_timestamp' => $effective_timestamp,
    //     ];
    //     return response()->json($data);
    // }

    // public function UpdateInventoryManagement(Request $request, $id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->inventory_management)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $ManageInventory = InventoryManagement::findOrFail($id);
    //     $TransactionTypeId = $request->input('u_im_transactiontype');
    //     $Site = $request->input('u_im_site');
    //     $Brand = $request->input('u_im_brand');
    //     $BatchNo = $request->input('u_im_batch_no');
    //     $ExpiryDate = $request->input('u_im_expirydate');
    //     $ExpiryDate = Carbon::createFromFormat('Y-m-d', $ExpiryDate)->timestamp;
    //     $Rate = $request->input('u_im_rate');
    //     $Qty = $request->input('u_im_qty');
    //     $Document = $request->input('u_im_reference_document');
    //     $Origin = $request->input('u_im_origin');
    //     $Destination = $request->input('u_im_destination');
    //     $effective_date = $request->input('u_im_edt');
    //     $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
    //     $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
    //     $EffectDateTime->subMinute(1);

    //     if ($EffectDateTime->isPast()) {
    //         $status = 1; //Active
    //     } else {
    //          $status = 0; //Inactive
    //     }

    //     $transactionTypes = InventoryTransactionType::select('transaction_type', 'name')
    //     ->where('id', $TransactionTypeId)
    //     ->first();
    //     $transactionType = $transactionTypes->transaction_type;
    //     $transactionName = $transactionTypes->name;
    //     $OriginType = null;
    //     $DestinationType = null;
    //     $documentType = null;
    //     if($transactionType == 'opening balance')
    //     {
    //         $Title = $transactionName.' updated';
    //     }
    //     else if($transactionType == 'addition')
    //     {
    //         $Title = ucwords($transactionName).' updated';
    //         $OriginType = 'vendor';
    //         $DestinationType = 'org_site';
    //         $documentType = 'open_text';
    //     }
    //     else if($transactionType == 'reduction')
    //     {
    //         $Title = ucwords($transactionName);
    //         $OriginType = 'org_site';
    //         $DestinationType = 'vendor';
    //         $documentType = 'inventory_management';
    //     }
    //     else if($transactionType == 'transfer')
    //     {
    //         $Title = ucwords($transactionName).' updated';
    //         $OriginType = 'org_site';
    //         $DestinationType = 'org_site';
    //     }
    //     else if($transactionType == 'general consumption')
    //     {
    //         $Title = ucwords($transactionName).' updated';
    //         $OriginType = 'org_site';
    //         $documentType = 'material_consumption_requisition';
    //     }
    //     else if($transactionType == 'patient consumption')
    //     {
    //         $Title = ucwords($transactionName).' updated';
    //         $OriginType = 'vendor';
    //         $DestinationType = 'patient';
    //         $documentType = 'material_consumption_requisition';
    //     }
    //     else if($transactionType == 'reversal')
    //     {
    //         $Title = ucwords($transactionName);
    //         $InventoryManagement = InventoryManagement::select('from', 'from_type', 'to', 'to_type')
    //         ->where('id', $Document)
    //         ->first();
    //         $Destination = $InventoryManagement->from;
    //         $DestinationType = $InventoryManagement->from_type;
    //         $Origin = $InventoryManagement->to;
    //         $OriginType = $InventoryManagement->to_type;
    //         $documentType = 'inventory_management';
    //     }

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $ManageInventory->transaction_type_id = $TransactionTypeId;
    //     $orgID = $request->input('u_im_org');
    //     if (isset($orgID)) {
    //         $ManageInventory->org_id = $orgID;
    //     }  
    //     $ManageInventory->site_id = $Site;
    //     $ManageInventory->brand_id = $Brand;
    //     $ManageInventory->batch_no = $BatchNo;
    //     $ManageInventory->expiry_date = $ExpiryDate;
    //     $ManageInventory->rate = $Rate;
    //     $ManageInventory->qty = $Qty;
    //     $ManageInventory->document_no = $Document;
    //     $ManageInventory->document_type = $documentType;
    //     $ManageInventory->from = $Origin;
    //     $ManageInventory->from_type = $OriginType;
    //     $ManageInventory->to = $Destination;
    //     $ManageInventory->to_type = $DestinationType;
    //     $ManageInventory->status = $status;
    //     $ManageInventory->user_id = $sessionId;
    //     $ManageInventory->last_updated = $this->currentDatetime;
    //     $ManageInventory->effective_timestamp = $effective_date;
        
    //     $ManageInventory->save();

    //     if (empty($ManageInventory->id)) {
    //         return response()->json(['error' => 'Failed to update Inventory Details. Please try again']);
    //     }
    //     $logs = Logs::create([
    //         'module' => 'inventory',
    //         'content' => "Data has been updated by '{$sessionName}'",
    //         'event' => 'update',
    //         'timestamp' => $this->currentDatetime,
    //     ]);
    //     $ManageInventoryLog = InventoryManagement::where('id', $ManageInventory->id)->first();
    //     $logIds = $ManageInventoryLog->logid ? explode(',', $ManageInventoryLog->logid) : [];
    //     $logIds[] = $logs->id;
    //     $ManageInventoryLog->logid = implode(',', $logIds);
    //     $ManageInventoryLog->save();
    //     return response()->json(['success' => ''.$Title.' successfully']);
    // }

}