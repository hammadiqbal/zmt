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
use App\Http\Requests\RequisitionForMaterialTransferRequest;
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
use App\Http\Requests\MaterialTransferRequest;
use App\Http\Requests\ConsumptionRequest;
use App\Http\Requests\ReturnRequest;
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
use App\Models\RequisitionForMaterialTransfer;
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

            // New logging (insert)
            $newData = [
                'name' => $InventoryCategories->name,
                'org_id' => $InventoryCategories->org_id,
                'consumption_group' => $InventoryCategories->consumption_group,
                'consumption_method' => $InventoryCategories->consumption_method,
                'status' => $InventoryCategories->status,
                'effective_timestamp' => $InventoryCategories->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_category',
                'insert',
                [
                    'message' => "'{$InventoryCategory}' has been added",
                    'created_by' => $sessionName
                ],
                $InventoryCategories->id,
                null,
                $newData,
                $sessionId
            );
            $InventoryCategories->logid = $logId;
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
        if ($request->has('cg') && $request->cg != '' && $request->cg != 'Loading...') {
            $InventoryCategories->where('inventory_category.consumption_group', $request->cg);
        }
        if ($request->has('cm') && $request->cm != '' && $request->cm != 'Loading...') {
            $InventoryCategories->where('inventory_category.consumption_method', $request->cm);
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
            $InventoryCategories->effective_timestamp = 0;

        }
        $InventoryCategories->status = $UpdateStatus;
        $InventoryCategories->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status values
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_category',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventoryCategoryID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryCategoryLog = InventoryCategory::where('id', $InventoryCategoryID)->first();
        $logIds = $InventoryCategoryLog->logid ? explode(',', $InventoryCategoryLog->logid) : [];
        $logIds[] = $logId;
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

        // Capture old data
        $oldData = [
            'name' => $InventoryCategories->name,
            'org_id' => $InventoryCategories->org_id,
            'consumption_group' => $InventoryCategories->consumption_group,
            'consumption_method' => $InventoryCategories->consumption_method,
            'status' => $InventoryCategories->status,
            'effective_timestamp' => $InventoryCategories->effective_timestamp,
        ];

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
        // New logging (update)
        $newData = [
            'name' => $InventoryCategories->name,
            'org_id' => $InventoryCategories->org_id,
            'consumption_group' => $InventoryCategories->consumption_group,
            'consumption_method' => $InventoryCategories->consumption_method,
            'status' => $InventoryCategories->status,
            'effective_timestamp' => $InventoryCategories->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_category',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventoryCategories->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryCategoryLog = InventoryCategory::where('id', $InventoryCategories->id)->first();
        $logIds = $InventoryCategoryLog->logid ? explode(',', $InventoryCategoryLog->logid) : [];
        $logIds[] = $logId;
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
        $Categories = InventoryCategory::where('status', 1)->get();

        $user = auth()->user();
        return view('dashboard.inventory-subcategory', compact('user','Categories'));
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

            // New logging (insert)
            $newData = [
                'name' => $InventorySubCategories->name,
                'cat_id' => $InventorySubCategories->cat_id,
                'org_id' => $InventorySubCategories->org_id,
                'status' => $InventorySubCategories->status,
                'effective_timestamp' => $InventorySubCategories->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_subcategory',
                'insert',
                [
                    'message' => "'{$InventorySubCategory}' has been added",
                    'created_by' => $sessionName
                ],
                $InventorySubCategories->id,
                null,
                $newData,
                $sessionId
            );
            $InventorySubCategories->logid = $logId;
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
        if ($request->has('cat') && $request->cat != '' && $request->cat != 'Loading...') {
            $InventorySubCategories->where('inventory_subcategory.cat_id', $request->cat);
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
            $InventorySubCategories->effective_timestamp = 0;

        }
        $InventorySubCategories->status = $UpdateStatus;
        $InventorySubCategories->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_subcategory',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventorySubCategoryID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventorySubCategoryLog = InventorySubCategory::where('id', $InventorySubCategoryID)->first();
        $logIds = $InventorySubCategoryLog->logid ? explode(',', $InventorySubCategoryLog->logid) : [];
        $logIds[] = $logId;
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

        // Capture old data
        $oldData = [
            'name' => $InventorySubCategories->name,
            'cat_id' => $InventorySubCategories->cat_id,
            'org_id' => $InventorySubCategories->org_id,
            'status' => $InventorySubCategories->status,
            'effective_timestamp' => $InventorySubCategories->effective_timestamp,
        ];

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
        // New logging (update)
        $newData = [
            'name' => $InventorySubCategories->name,
            'cat_id' => $InventorySubCategories->cat_id,
            'org_id' => $InventorySubCategories->org_id,
            'status' => $InventorySubCategories->status,
            'effective_timestamp' => $InventorySubCategories->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_subcategory',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventorySubCategories->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventorySubCategoryLog = InventorySubCategory::where('id', $InventorySubCategories->id)->first();
        $logIds = $InventorySubCategoryLog->logid ? explode(',', $InventorySubCategoryLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'name' => $InventoryTypes->name,
                'cat_id' => $InventoryTypes->cat_id,
                'sub_catid' => $InventoryTypes->sub_catid,
                'org_id' => $InventoryTypes->org_id,
                'status' => $InventoryTypes->status,
                'effective_timestamp' => $InventoryTypes->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_type',
                'insert',
                [
                    'message' => "'{$InventoryType}' has been added",
                    'created_by' => $sessionName
                ],
                $InventoryTypes->id,
                null,
                $newData,
                $sessionId
            );
            $InventoryTypes->logid = $logId;
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
        ->join('organization', 'organization.id', '=', 'inventory_type.org_id');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryTypes->where('inventory_type.org_id', '=', $sessionOrg);
        }
        if ($request->has('cat') && $request->cat != '' && $request->cat != 'Loading...') {
            $InventoryTypes->where('inventory_type.cat_id', $request->cat);
        }
        if ($request->has('subcat') && $request->subcat != '' && $request->subcat != 'Loading...') {
            $InventoryTypes->where('inventory_type.sub_catid', $request->subcat);
        }
        $InventoryTypes = $InventoryTypes->orderBy('inventory_type.id', 'desc');;
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
            $InventoryTypes->effective_timestamp = 0;

        }
        $InventoryTypes->status = $UpdateStatus;
        $InventoryTypes->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_type',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventoryTypeID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryTypeLog = InventoryType::where('id', $InventoryTypeID)->first();
        $logIds = $InventoryTypeLog->logid ? explode(',', $InventoryTypeLog->logid) : [];
        $logIds[] = $logId;
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

        // Capture old data
        $oldData = [
            'name' => $InventoryTypes->name,
            'cat_id' => $InventoryTypes->cat_id,
            'sub_catid' => $InventoryTypes->sub_catid,
            'org_id' => $InventoryTypes->org_id,
            'status' => $InventoryTypes->status,
            'effective_timestamp' => $InventoryTypes->effective_timestamp,
        ];

        $InventoryTypes->name = $request->input('u_it_description');
        $InventoryTypes->cat_id = $request->input('u_it_catid');
        $InventoryTypes->sub_catid = $request->input('u_it_subcat');
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
        // New logging (update)
        $newData = [
            'name' => $InventoryTypes->name,
            'cat_id' => $InventoryTypes->cat_id,
            'sub_catid' => $InventoryTypes->sub_catid,
            'org_id' => $InventoryTypes->org_id,
            'status' => $InventoryTypes->status,
            'effective_timestamp' => $InventoryTypes->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_type',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventoryTypes->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryTypeLog = InventoryType::where('id', $InventoryTypes->id)->first();
        $logIds = $InventoryTypeLog->logid ? explode(',', $InventoryTypeLog->logid) : [];
        $logIds[] = $logId;
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
        // $SubCategories = InventorySubCategory::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        // $InventoryTypes = InventoryType::where('status', 1)->get();

        return view('dashboard.inventory-generic', compact('user','Categories','Organizations'));
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

            // New logging (insert)
            $newData = [
                'name' => $InventoryGenerics->name,
                'cat_id' => $InventoryGenerics->cat_id,
                'sub_catid' => $InventoryGenerics->sub_catid,
                'type_id' => $InventoryGenerics->type_id,
                'org_id' => $InventoryGenerics->org_id,
                'patient_mandatory' => $InventoryGenerics->patient_mandatory,
                'status' => $InventoryGenerics->status,
                'effective_timestamp' => $InventoryGenerics->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_generic',
                'insert',
                [
                    'message' => "'{$InventoryGeneric}' has been added",
                    'created_by' => $sessionName
                ],
                $InventoryGenerics->id,
                null,
                $newData,
                $sessionId
            );
            $InventoryGenerics->logid = $logId;
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
        ->join('organization', 'organization.id', '=', 'inventory_generic.org_id');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $InventoryGenerics->where('inventory_generic.org_id', '=', $sessionOrg);
        }

        if ($request->has('cat') && $request->cat != '' && $request->cat != 'Loading...') {
            $InventoryGenerics->where('inventory_generic.cat_id', $request->cat);
        }
        if ($request->has('subcat') && $request->subcat != '' && $request->subcat != 'Loading...') {
            $InventoryGenerics->where('inventory_generic.sub_catid', $request->subcat);
        }
        if ($request->has('type') && $request->type != '' && $request->type != 'Loading...') {
            $InventoryGenerics->where('inventory_generic.type_id', $request->type);
        }
        
        $InventoryGenerics = $InventoryGenerics->orderBy('inventory_generic.id', 'desc');
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
            $InventoryGenerics->effective_timestamp = 0;

        }
        $InventoryGenerics->status = $UpdateStatus;
        $InventoryGenerics->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_generic',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventoryGenericID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryGenericLog = InventoryGeneric::where('id', $InventoryGenericID)->first();
        $logIds = $InventoryGenericLog->logid ? explode(',', $InventoryGenericLog->logid) : [];
        $logIds[] = $logId;
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

        // Capture old data
        $oldData = [
            'name' => $InventoryGenerics->name,
            'cat_id' => $InventoryGenerics->cat_id,
            'sub_catid' => $InventoryGenerics->sub_catid,
            'type_id' => $InventoryGenerics->type_id,
            'org_id' => $InventoryGenerics->org_id,
            'patient_mandatory' => $InventoryGenerics->patient_mandatory,
            'status' => $InventoryGenerics->status,
            'effective_timestamp' => $InventoryGenerics->effective_timestamp,
        ];

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
        // New logging (update)
        $newData = [
            'name' => $InventoryGenerics->name,
            'cat_id' => $InventoryGenerics->cat_id,
            'sub_catid' => $InventoryGenerics->sub_catid,
            'type_id' => $InventoryGenerics->type_id,
            'org_id' => $InventoryGenerics->org_id,
            'patient_mandatory' => $InventoryGenerics->patient_mandatory,
            'status' => $InventoryGenerics->status,
            'effective_timestamp' => $InventoryGenerics->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_generic',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventoryGenerics->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryGenericLog = InventoryGeneric::where('id', $InventoryGenerics->id)->first();
        $logIds = $InventoryGenericLog->logid ? explode(',', $InventoryGenericLog->logid) : [];
        $logIds[] = $logId;
        $InventoryGenericLog->logid = implode(',', $logIds);
        $InventoryGenericLog->save();
        return response()->json(['success' => 'Inventory Generic updated successfully']);
    }

    public function GetSelectedInventoryType(Request $request)
    {
        $catId = $request->input('catId');
        $subcatId = $request->input('subcatId');
        // $orgId = $request->input('orgId');

        // $InventoryTypes = InventoryType::select('id', 'name')
        //     ->where('cat_id', $catId)
        //     ->where('sub_catid', $subcatId)
        //     ->where('org_id', $orgId)
        //     ->get();

        $orgId = $request->input('orgId');

        $InventoryTypes = InventoryType::select('id', 'name')
            ->where('cat_id', $catId)
            ->where('sub_catid', $subcatId);

        // Add condition for org_id only if $orgId is not null
        if ($orgId != null) {
            $InventoryTypes->where('org_id', $orgId);
        }

        $InventoryTypes = $InventoryTypes->get();


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
        else if ($condition == 'material_medicine') {
            $InventoryGenerics->where('inventory_generic.patient_mandatory', '=', 'y');
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
        $SubCategories = InventorySubCategory::where('status', 1)->get();
        $Types = InventoryType::where('status', 1)->get();
        $Generics = InventoryGeneric::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        $InventoryTypes = InventoryType::where('status', 1)->get();

        return view('dashboard.inventory-brand', compact('user','Categories','SubCategories','Types','Generics','Organizations','InventoryTypes'));
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

            // New logging (insert)
            $newData = [
                'name' => $InventoryBrands->name,
                'cat_id' => $InventoryBrands->cat_id,
                'sub_catid' => $InventoryBrands->sub_catid,
                'type_id' => $InventoryBrands->type_id,
                'generic_id' => $InventoryBrands->generic_id,
                'org_id' => $InventoryBrands->org_id,
                'status' => $InventoryBrands->status,
                'effective_timestamp' => $InventoryBrands->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_brand',
                'insert',
                [
                    'message' => "'{$InventoryBrand}' has been added",
                    'created_by' => $sessionName
                ],
                $InventoryBrands->id,
                null,
                $newData,
                $sessionId
            );
            $InventoryBrands->logid = $logId;
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

        if ($request->has('cat') && $request->cat != '' && $request->cat != 'Loading...') {
            $InventoryBrands->where('inventory_brand.cat_id', $request->cat);
        }
        if ($request->has('subcat') && $request->subcat != '' && $request->subcat != 'Loading...') {
            $InventoryBrands->where('inventory_brand.sub_catid', $request->subcat);
        }
        if ($request->has('type') && $request->type != '' && $request->type != 'Loading...') {
            $InventoryBrands->where('inventory_brand.type_id', $request->type);
        }
        if ($request->has('generic') && $request->generic != '' && $request->generic != 'Loading...') {
            $InventoryBrands->where('inventory_brand.generic_id', $request->generic);
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
            $InventoryBrands->effective_timestamp = 0;

        }
        $InventoryBrands->status = $UpdateStatus;
        $InventoryBrands->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_brand',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventoryBrandID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryBrandLog = InventoryBrand::where('id', $InventoryBrandID)->first();
        $logIds = $InventoryBrandLog->logid ? explode(',', $InventoryBrandLog->logid) : [];
        $logIds[] = $logId;
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

        // Capture old data
        $oldData = [
            'name' => $InventoryBrands->name,
            'cat_id' => $InventoryBrands->cat_id,
            'sub_catid' => $InventoryBrands->sub_catid,
            'type_id' => $InventoryBrands->type_id,
            'generic_id' => $InventoryBrands->generic_id,
            'org_id' => $InventoryBrands->org_id,
            'status' => $InventoryBrands->status,
            'effective_timestamp' => $InventoryBrands->effective_timestamp,
        ];

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
        // New logging (update)
        $newData = [
            'name' => $InventoryBrands->name,
            'cat_id' => $InventoryBrands->cat_id,
            'sub_catid' => $InventoryBrands->sub_catid,
            'type_id' => $InventoryBrands->type_id,
            'generic_id' => $InventoryBrands->generic_id,
            'org_id' => $InventoryBrands->org_id,
            'status' => $InventoryBrands->status,
            'effective_timestamp' => $InventoryBrands->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_brand',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventoryBrands->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryBrandLog = InventoryBrand::where('id', $InventoryBrands->id)->first();
        $logIds = $InventoryBrandLog->logid ? explode(',', $InventoryBrandLog->logid) : [];
        $logIds[] = $logId;
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
        // $requestLocationMandatoryStatus = $request->input('request_location_mandatory');
        $RequisitionEmpLocationCheck = $request->input('request_emp_location');
        $sourceLocationType = $request->input('source_location_type');
        $sourceAction = $request->input('source_action');
        $destinationLocationType = $request->input('destination_location_type');
        $destinationAction = $request->input('destination_action');
        $sourceLocations =  implode(',', $request->input('source_locations'));
        $destinationLocations =  implode(',', $request->input('destination_locations'));
        $empLocationCheck = $request->input('emp_location_check');
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
            // $InventoryTransactionTypes->request_location_mandatory = $requestLocationMandatoryStatus;
            $InventoryTransactionTypes->emp_location_mandatory_request = $RequisitionEmpLocationCheck;
            $InventoryTransactionTypes->source_location_type = $sourceLocationType;
            $InventoryTransactionTypes->source_action = $sourceAction;
            $InventoryTransactionTypes->destination_location_type = $destinationLocationType;
            $InventoryTransactionTypes->destination_action = $destinationAction;
            $InventoryTransactionTypes->source_location = $sourceLocations;
            $InventoryTransactionTypes->destination_location = $destinationLocations;
            $InventoryTransactionTypes->emp_location_source_destination = $empLocationCheck;
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

            // New logging (insert)
            $newData = [
                'name' => $InventoryTransactionTypes->name,
                'activity_type' => $InventoryTransactionTypes->activity_type,
                'request_mandatory' => $InventoryTransactionTypes->request_mandatory,
                'emp_location_check' => $InventoryTransactionTypes->emp_location_mandatory_request,
                'source_location_type' => $InventoryTransactionTypes->source_location_type,
                'source_action' => $InventoryTransactionTypes->source_action,
                'destination_location_type' => $InventoryTransactionTypes->destination_location_type,
                'destination_action' => $InventoryTransactionTypes->destination_action,
                'source_location' => $InventoryTransactionTypes->source_location,
                'destination_location' => $InventoryTransactionTypes->destination_location,
                'emp_location_source_destination' => $InventoryTransactionTypes->emp_location_source_destination,
                'transaction_expired_status' => $InventoryTransactionTypes->transaction_expired_status,
                'org_id' => $InventoryTransactionTypes->org_id,
                'status' => $InventoryTransactionTypes->status,
                'effective_timestamp' => $InventoryTransactionTypes->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_transaction_type',
                'insert',
                [
                    'message' => "'{$InventoryTransactionType}' has been added",
                    'created_by' => $sessionName
                ],
                $InventoryTransactionTypes->id,
                null,
                $newData,
                $sessionId
            );
            $InventoryTransactionTypes->logid = $logId;
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
                // $requestLocationMandatory = $InventoryTransactionType->request_location_mandatory === 'y' ? "Yes" : "No";

                $requestEmpLocationMandatory = $InventoryTransactionType->emp_location_mandatory_request === 's' ? 'Source' : ($InventoryTransactionType->emp_location_mandatory_request === 'd' ? 'Destination' : 'Not Applicable');
                // $empLocationMandatory = $InventoryTransactionType->emp_location_source_destination === 's' ? 'Source' : ($InventoryTransactionType->emp_location_source_destination === 'd' ? 'Destination' : 'Not Applicable');

                $transactionExpiredStatus = $InventoryTransactionType->transaction_expired_status === 'y' ? "Yes" : "No";

                return $Code.'<hr class="mt-1 mb-2">'.ucwords($InventoryTransactionTypeName)
                    . '<hr class="mt-1 mb-2">'
                    .  $orgName
                    .'<b>Activity Type: </b>'.ucwords($InventoryTransactionType->transactionActivity).'<br>'
                    .'<b>Request Mandatory: </b>'.$requestMandatory.'<br>'
                    // .'<b>Request Location Mandatory: </b>'.$requestLocationMandatory.'<br>'
                    .'<b>Emp Location Check (Requisition): </b>'.$requestEmpLocationMandatory.'<br>'
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

                return '<b>Source Type: </b>'.ucwords($sourceLocationType).'<br>'
                    .'<b>Source Action: </b>'.$sourceAction.'<br>'
                    .'<b>Destination Type: </b>'.$destinationLocationType.'<br>'
                    .'<b>Destination Action: </b>'.$destinationAction.'<br>';
            })
            ->addColumn('locationDetails', function ($InventoryTransactionType) {
                $source_locationIds = explode(',', $InventoryTransactionType->source_location);
                $sourceLocationNames = ServiceLocation::whereIn('id', $source_locationIds)
                ->pluck('name')
                ->toArray();
                $sourceLocationNames = implode(', ', $sourceLocationNames);

                $destination_locationIds = explode(',', $InventoryTransactionType->destination_location);
                $destinationLocationNames = ServiceLocation::whereIn('id', $destination_locationIds)
                ->pluck('name')
                ->toArray();
                $destinationLocationNames = implode(', ', $destinationLocationNames);

                $empLocationMandatory = $InventoryTransactionType->emp_location_source_destination === 's' ? 'Source' : ($InventoryTransactionType->emp_location_source_destination === 'd' ? 'Destination' : 'Not Applicable');


                // $AllocatedInventoryLocation = $InventoryTransactionType->serviceLocation;
                return '<b>Source Locations: </b>'.ucwords($sourceLocationNames).'<hr class="mt-1 mb-2">'
                .'<b>Destination Locations: </b>'.ucwords($destinationLocationNames).'<hr class="mt-1 mb-2">'
                .'<b>Applicable Emp Location: </b>'.ucwords($empLocationMandatory);
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
            $InventoryTransactionTypes->effective_timestamp = $CurrentTimestamp;


        }
        $InventoryTransactionTypes->status = $UpdateStatus;
        $InventoryTransactionTypes->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_transaction_type',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventoryTransactionTypeID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryTransactionTypeLog = InventoryTransactionType::where('id', $InventoryTransactionTypeID)->first();
        $logIds = $InventoryTransactionTypeLog->logid ? explode(',', $InventoryTransactionTypeLog->logid) : [];
        $logIds[] = $logId;
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

        $sourceLocationIds = explode(',', $InventoryTransactionTypes->source_location);
        $sourceLocationNames = DB::table('service_location')
            ->whereIn('id', $sourceLocationIds)
            ->pluck('name')
            ->toArray();
        $sourceLocationNames = implode(', ', $sourceLocationNames);

        $destinationLocationIds = explode(',', $InventoryTransactionTypes->destination_location);
        $destinationLocationNames = DB::table('service_location')
            ->whereIn('id', $destinationLocationIds)
            ->pluck('name')
            ->toArray();
        $destinationLocationNames = implode(', ', $destinationLocationNames);


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
            'requisitionEmpCheck' => ($InventoryTransactionTypes->emp_location_mandatory_request),
            'sourceLocationTypeId' => $InventoryTransactionTypes->source_location_type,
            'sourceLocationType' => ucwords($InventoryTransactionTypes->sourceLocationType),
            'sourceAction' => ($InventoryTransactionTypes->source_action),
            'destinationLocationTypeId' => $InventoryTransactionTypes->destination_location_type,
            'destinationLocationType' => ucwords($InventoryTransactionTypes->destinationLocationType),
            'destinationAction' => ($InventoryTransactionTypes->destination_action),
            'sourcelocationId' => $InventoryTransactionTypes->source_location,
            'sourceLocations' => ucwords($sourceLocationNames),
            'destinationlocationId' => $InventoryTransactionTypes->destination_location,
            'destinationLocations' => ucwords($destinationLocationNames),
            'empCheckSourceDestination' => $InventoryTransactionTypes->emp_location_source_destination,
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

        // Capture old data
        $oldData = [
            'name' => $InventoryTransactionTypes->name,
            'activity_type' => $InventoryTransactionTypes->activity_type,
            'request_mandatory' => $InventoryTransactionTypes->request_mandatory,
            'emp_location_check' => $InventoryTransactionTypes->emp_location_mandatory_request,
            'source_location_type' => $InventoryTransactionTypes->source_location_type,
            'source_action' => $InventoryTransactionTypes->source_action,
            'destination_location_type' => $InventoryTransactionTypes->destination_location_type,
            'destination_action' => $InventoryTransactionTypes->destination_action,
            'source_location' => $InventoryTransactionTypes->source_location,
            'destination_location' => $InventoryTransactionTypes->destination_location,
            'emp_location_source_destination' => $InventoryTransactionTypes->emp_location_source_destination,
            'transaction_expired_status' => $InventoryTransactionTypes->transaction_expired_status,
            'org_id' => $InventoryTransactionTypes->org_id,
            'status' => $InventoryTransactionTypes->status,
            'effective_timestamp' => $InventoryTransactionTypes->effective_timestamp,
        ];

        $orgID = $request->input('u_itt_org');
        if (isset($orgID)) {
            $InventoryTransactionTypes->org_id = $orgID;
        }
        $InventoryTransactionTypes->name = $request->input('u_description');
        $InventoryTransactionTypes->activity_type = $request->input('u_activity_type');
        $InventoryTransactionTypes->request_mandatory = $request->input('u_request_mandatory');
        $InventoryTransactionTypes->emp_location_mandatory_request = $request->input('u_request_emp_location');
        $InventoryTransactionTypes->source_location_type = $request->input('u_source_location_type');
        $InventoryTransactionTypes->source_action = $request->input('u_source_action');
        $InventoryTransactionTypes->destination_location_type = $request->input('u_destination_location_type');
        $InventoryTransactionTypes->destination_action = $request->input('u_destination_action');
        // $InventoryTransactionTypes->service_location_id = $request->input('u_inventory_location');
        $InventoryTransactionTypes->source_location = implode(',', $request->input('u_source_locations'));
        $InventoryTransactionTypes->destination_location = implode(',', $request->input('u_destination_locations'));
        $InventoryTransactionTypes->emp_location_source_destination = $request->input('u_emp_location_check');
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
        // New logging (update)
        $newData = [
            'name' => $InventoryTransactionTypes->name,
            'activity_type' => $InventoryTransactionTypes->activity_type,
            'request_mandatory' => $InventoryTransactionTypes->request_mandatory,
            'emp_location_check' => $InventoryTransactionTypes->emp_location_mandatory_request,
            'source_location_type' => $InventoryTransactionTypes->source_location_type,
            'source_action' => $InventoryTransactionTypes->source_action,
            'destination_location_type' => $InventoryTransactionTypes->destination_location_type,
            'destination_action' => $InventoryTransactionTypes->destination_action,
            'source_location' => $InventoryTransactionTypes->source_location,
            'destination_location' => $InventoryTransactionTypes->destination_location,
            'emp_location_source_destination' => $InventoryTransactionTypes->emp_location_source_destination,
            'transaction_expired_status' => $InventoryTransactionTypes->transaction_expired_status,
            'org_id' => $InventoryTransactionTypes->org_id,
            'status' => $InventoryTransactionTypes->status,
            'effective_timestamp' => $InventoryTransactionTypes->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_transaction_type',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventoryTransactionTypes->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryTransactionTypeLog = InventoryTransactionType::where('id', $InventoryTransactionTypes->id)->first();
        $logIds = $InventoryTransactionTypeLog->logid ? explode(',', $InventoryTransactionTypeLog->logid) : [];
        $logIds[] = $logId;
        $InventoryTransactionTypeLog->logid = implode(',', $logIds);
        $InventoryTransactionTypeLog->save();
        return response()->json(['success' => 'Inventory Transaction Type updated successfully']);
    }


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

            // New logging (insert)
            $newData = [
                'org_id' => $ThirdParty->org_id,
                'thirdparty_type' => $ThirdParty->type,
                'thirdparty_category' => $ThirdParty->category,
                'corporate_name' => $ThirdParty->corporate_name,
                'prefix_id' => $ThirdParty->prefix_id,
                'person_name' => $ThirdParty->person_name,
                'person_email' => $ThirdParty->person_email,
                'person_cell' => $ThirdParty->person_cell,
                'landline' => $ThirdParty->landline,
                'address' => $ThirdParty->address,
                'remarks' => $ThirdParty->remarks,
                'status' => $ThirdParty->status,
                'effective_timestamp' => $ThirdParty->effective_timestamp,
            ];
            $logId = createLog(
                'third_party',
                'insert',
                [
                    'message' => "'{$RegistrationType} -- {$PersonName}' has been added",
                    'created_by' => $sessionName
                ],
                $ThirdParty->id,
                null,
                $newData,
                $sessionId
            );
            $ThirdParty->logid = $logId;
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
            $ThirdParty->effective_timestamp = $CurrentTimestamp;

        }
        $ThirdParty->status = $UpdateStatus;
        $ThirdParty->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'third_party',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $ThirdPartyID,
            $oldData,
            $newData,
            $sessionId
        );
        $ThirdPartyLog = ThirdPartyRegistration::where('id', $ThirdPartyID)->first();
        $logIds = $ThirdPartyLog->logid ? explode(',', $ThirdPartyLog->logid) : [];
        $logIds[] = $logId;
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

        // Capture old data
        $oldData = [
            'org_id' => $ThirdParty->org_id,
            'thirdparty_type' => $ThirdParty->type,
            'thirdparty_category' => $ThirdParty->category,
            'corporate_name' => $ThirdParty->corporate_name,
            'prefix_id' => $ThirdParty->prefix_id,
            'person_name' => $ThirdParty->person_name,
            'person_email' => $ThirdParty->person_email,
            'person_cell' => $ThirdParty->person_cell,
            'landline' => $ThirdParty->landline,
            'address' => $ThirdParty->address,
            'remarks' => $ThirdParty->remarks,
            'status' => $ThirdParty->status,
            'effective_timestamp' => $ThirdParty->effective_timestamp,
        ];
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
        // New logging (update)
        $newData = [
            'org_id' => $ThirdParty->org_id,
            'thirdparty_type' => $ThirdParty->type,
            'thirdpartycategory' => $ThirdParty->category,
            'corporate_name' => $ThirdParty->corporate_name,
            'prefix_id' => $ThirdParty->prefix_id,
            'person_name' => $ThirdParty->person_name,
            'person_email' => $ThirdParty->person_email,
            'person_cell' => $ThirdParty->person_cell,
            'landline' => $ThirdParty->landline,
            'address' => $ThirdParty->address,
            'remarks' => $ThirdParty->remarks,
            'status' => $ThirdParty->status,
            'effective_timestamp' => $ThirdParty->effective_timestamp,
        ];
        $logId = createLog(
            'third_party',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $ThirdParty->id,
            $oldData,
            $newData,
            $sessionId
        );
        $ThirdPartyLog = ThirdPartyRegistration::where('id', $ThirdParty->id)->first();
        $logIds = $ThirdPartyLog->logid ? explode(',', $ThirdPartyLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'org_id' => $ConsumptionGroups->org_id,
                'description' => $ConsumptionGroups->description,
                'remarks' => $ConsumptionGroups->remarks,
                'status' => $ConsumptionGroups->status,
                'effective_timestamp' => $ConsumptionGroups->effective_timestamp,
            ];
            $logId = createLog(
                'consumption_group',
                'insert',
                [
                    'message' => "'{$Desc}' has been added",
                    'created_by' => $sessionName
                ],
                $ConsumptionGroups->id,
                null,
                $newData,
                $sessionId
            );
            $ConsumptionGroups->logid = $logId;
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
            $ConsumptionGroup->effective_timestamp = $CurrentTimestamp;

        }
        $ConsumptionGroup->status = $UpdateStatus;
        $ConsumptionGroup->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'consumption_group',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $ConsumptionGroupID,
            $oldData,
            $newData,
            $sessionId
        );
        $ConsumptionGroupLog = ConsumptionGroup::where('id', $ConsumptionGroupID)->first();
        $logIds = $ConsumptionGroupLog->logid ? explode(',', $ConsumptionGroupLog->logid) : [];
        $logIds[] = $logId;
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $ConsumptionGroup->org_id,
            'description' => $ConsumptionGroup->description,
            'remarks' => $ConsumptionGroup->remarks,
            'status' => $ConsumptionGroup->status,
            'effective_timestamp' => $ConsumptionGroup->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'org_id' => $ConsumptionGroup->org_id,
            'description' => $ConsumptionGroup->description,
            'remarks' => $ConsumptionGroup->remarks,
            'status' => $ConsumptionGroup->status,
            'effective_timestamp' => $ConsumptionGroup->effective_timestamp,
        ];
        $logId = createLog(
            'consumption_group',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $ConsumptionGroup->id,
            $oldData,
            $newData,
            $sessionId
        );
        $ConsumptionGroupLog = ConsumptionGroup::where('id', $ConsumptionGroup->id)->first();
        $logIds = $ConsumptionGroupLog->logid ? explode(',', $ConsumptionGroupLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'org_id' => $ConsumptionMethods->org_id,
                'description' => $ConsumptionMethods->description,
                'criteria' => $ConsumptionMethods->criteria,
                'consumption_group' => $ConsumptionMethods->group_id,
                'status' => $ConsumptionMethods->status,
                'effective_timestamp' => $ConsumptionMethods->effective_timestamp,
            ];
            $logId = createLog(
                'consumption_method',
                'insert',
                [
                    'message' => "'{$Desc}' has been added",
                    'created_by' => $sessionName
                ],
                $ConsumptionMethods->id,
                null,
                $newData,
                $sessionId
            );
            $ConsumptionMethods->logid = $logId;
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
            $ConsumptionMethod->effective_timestamp = $CurrentTimestamp;

        }
        $ConsumptionMethod->status = $UpdateStatus;
        $ConsumptionMethod->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'consumption_method',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $ConsumptionMethodID,
            $oldData,
            $newData,
            $sessionId
        );
        $ConsumptionMethodLog = ConsumptionMethod::where('id', $ConsumptionMethodID)->first();
        $logIds = $ConsumptionMethodLog->logid ? explode(',', $ConsumptionMethodLog->logid) : [];
        $logIds[] = $logId;
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $ConsumptionMethod->org_id,
            'description' => $ConsumptionMethod->description,
            'criteria' => $ConsumptionMethod->criteria,
            'consumption_group' => $ConsumptionMethod->group_id,
            'status' => $ConsumptionMethod->status,
            'effective_timestamp' => $ConsumptionMethod->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'org_id' => $ConsumptionMethod->org_id,
            'description' => $ConsumptionMethod->description,
            'criteria' => $ConsumptionMethod->criteria,
            'consumption_group' => $ConsumptionMethod->group_id,
            'status' => $ConsumptionMethod->status,
            'effective_timestamp' => $ConsumptionMethod->effective_timestamp,
        ];
        $logId = createLog(
            'consumption_method',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $ConsumptionMethod->id,
            $oldData,
            $newData,
            $sessionId
        );
        $ConsumptionMethodLog = ConsumptionMethod::where('id', $ConsumptionMethod->id)->first();
        $logIds = $ConsumptionMethodLog->logid ? explode(',', $ConsumptionMethodLog->logid) : [];
        $logIds[] = $logId;
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
        $Sites = Site::where('status', 1);
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Sites->whereIn('id', $sessionSiteIds);
            }
        }
        $Sites = $Sites->get();

        $Generics = InventoryGeneric::where('status', 1)->get();
        $Brands = InventoryBrand::where('status', 1)->get();
        return view('dashboard.stock_monitoring', compact('user','Organizations','Sites','Generics','Brands'));
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

            // New logging (insert)
            $newData = [
                'org_id' => $StockMonitoring->org_id,
                'site_id' => $StockMonitoring->site_id,
                'generic_id' => $StockMonitoring->item_generic_id,
                'brand_id' => $StockMonitoring->item_brand_id,
                'service_location_id' => $StockMonitoring->service_location_id,
                'min_stock' => $StockMonitoring->min_stock,
                'max_stock' => $StockMonitoring->max_stock,
                'monthly_consumption_ceiling' => $StockMonitoring->monthly_consumption_ceiling,
                'min_reorder_qty' => $StockMonitoring->min_reorder_qty,
                'primary_email' => $StockMonitoring->primary_email,
                'secondary_email' => $StockMonitoring->secondary_email,
                'status' => $StockMonitoring->status,
                'effective_timestamp' => $StockMonitoring->effective_timestamp,
            ];
            $logId = createLog(
                'stock_monitoring',
                'insert',
                [
                    'message' => 'Stock Monitoring has been added',
                    'created_by' => $sessionName
                ],
                $StockMonitoring->id,
                null,
                $newData,
                $sessionId
            );
            $StockMonitoring->logid = $logId;
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

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $StockMonitoringData->whereIn('org_site.id', $sessionSiteIds);
            }
        }

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $StockMonitoringData->where('stock_monitoring.org_id', '=', $sessionOrg);
        }
        if ($request->has('site') && $request->site != '' && $request->site != 'Loading...') {
            $StockMonitoringData->where('stock_monitoring.site_id', $request->site);
        }
        if ($request->has('generic') && $request->generic != '' && $request->generic != 'Loading...') {
            $StockMonitoringData->where('stock_monitoring.item_generic_id', $request->generic);
        }
        if ($request->has('brand') && $request->brand != '' && $request->brand != 'Loading...') {
            $StockMonitoringData->where('stock_monitoring.item_brand_id', $request->brand);
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
                '<b>Primary Email:</b> <br>'.($StockMonitoring->primary_email).'<hr class="mt-1 mb-2">'
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
            $StockMonitoring->effective_timestamp = 0;
        }
        $StockMonitoring->status = $UpdateStatus;
        $StockMonitoring->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'stock_monitoring',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $StockMonitoringID,
            $oldData,
            $newData,
            $sessionId
        );
        $StockMonitoringLog = StockMonitoring::where('id', $StockMonitoringID)->first();
        $logIds = $StockMonitoringLog->logid ? explode(',', $StockMonitoringLog->logid) : [];
        $logIds[] = $logId;
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
        ->leftJoin('inventory_brand', 'inventory_brand.id', '=', 'stock_monitoring.item_brand_id')
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $StockMonitoring->org_id,
            'site_id' => $StockMonitoring->site_id,
            'generic_id' => $StockMonitoring->item_generic_id,
            'brand_id' => $StockMonitoring->item_brand_id,
            'service_location_id' => $StockMonitoring->service_location_id,
            'min_stock' => $StockMonitoring->min_stock,
            'max_stock' => $StockMonitoring->max_stock,
            'monthly_consumption_ceiling' => $StockMonitoring->monthly_consumption_ceiling,
            'min_reorder_qty' => $StockMonitoring->min_reorder_qty,
            'primary_email' => $StockMonitoring->primary_email,
            'secondary_email' => $StockMonitoring->secondary_email,
            'status' => $StockMonitoring->status,
            'effective_timestamp' => $StockMonitoring->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'org_id' => $StockMonitoring->org_id,
            'site_id' => $StockMonitoring->site_id,
            'generic_id' => $StockMonitoring->item_generic_id,
            'brand_id' => $StockMonitoring->item_brand_id,
            'service_location_id' => $StockMonitoring->service_location_id,
            'min_stock' => $StockMonitoring->min_stock,
            'max_stock' => $StockMonitoring->max_stock,
            'monthly_consumption_ceiling' => $StockMonitoring->monthly_consumption_ceiling,
            'min_reorder_qty' => $StockMonitoring->min_reorder_qty,
            'primary_email' => $StockMonitoring->primary_email,
            'secondary_email' => $StockMonitoring->secondary_email,
            'status' => $StockMonitoring->status,
            'effective_timestamp' => $StockMonitoring->effective_timestamp,
        ];
        $logId = createLog(
            'stock_monitoring',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $StockMonitoring->id,
            $oldData,
            $newData,
            $sessionId
        );
        $StockMonitoringLog = StockMonitoring::where('id', $StockMonitoring->id)->first();
        $logIds = $StockMonitoringLog->logid ? explode(',', $StockMonitoringLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'org_id' => $InventorySourceDestinationType->org_id,
                'name' => $InventorySourceDestinationType->name,
                'third_party' => $InventorySourceDestinationType->third_party,
                'status' => $InventorySourceDestinationType->status,
                'effective_timestamp' => $InventorySourceDestinationType->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_source_destination_type',
                'insert',
                [
                    'message' => 'Inventory Source Destination Type has been added',
                    'created_by' => $sessionName
                ],
                $InventorySourceDestinationType->id,
                null,
                $newData,
                $sessionId
            );
            $InventorySourceDestinationType->logid = $logId;
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
            $InventorySourceDestinationType->effective_timestamp = 0;
            $statusLog = 'Inactive';

        }
        $InventorySourceDestinationType->status = $UpdateStatus;
        $InventorySourceDestinationType->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_source_destination_type',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventorySourceDestinationTypeID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventorySourceDestinationTypeLog = InventorySourceDestinationType::where('id', $InventorySourceDestinationTypeID)->first();
        $logIds = $InventorySourceDestinationTypeLog->logid ? explode(',', $InventorySourceDestinationTypeLog->logid) : [];
        $logIds[] = $logId;
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $InventorySourceDestinationType->org_id,
            'name' => $InventorySourceDestinationType->name,
            'third_party' => $InventorySourceDestinationType->third_party,
            'status' => $InventorySourceDestinationType->status,
            'effective_timestamp' => $InventorySourceDestinationType->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'org_id' => $InventorySourceDestinationType->org_id,
            'name' => $InventorySourceDestinationType->name,
            'third_party' => $InventorySourceDestinationType->third_party,
            'status' => $InventorySourceDestinationType->status,
            'effective_timestamp' => $InventorySourceDestinationType->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_source_destination_type',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventorySourceDestinationType->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventorySourceDestinationTypeLog = InventorySourceDestinationType::where('id', $InventorySourceDestinationType->id)->first();
        $logIds = $InventorySourceDestinationTypeLog->logid ? explode(',', $InventorySourceDestinationTypeLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'org_id' => $InventoryTransactionActivity->org_id,
                'name' => $InventoryTransactionActivity->name,
                'status' => $InventoryTransactionActivity->status,
                'effective_timestamp' => $InventoryTransactionActivity->effective_timestamp,
            ];
            $logId = createLog(
                'inventory_transaction_activity',
                'insert',
                [
                    'message' => 'Inventory Transaction Activity has been added',
                    'created_by' => $sessionName
                ],
                $InventoryTransactionActivity->id,
                null,
                $newData,
                $sessionId
            );
            $InventoryTransactionActivity->logid = $logId;
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
            $InventoryTransactionActivity->effective_timestamp = 0;
        }
        $InventoryTransactionActivity->status = $UpdateStatus;
        $InventoryTransactionActivity->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'inventory_transaction_activity',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $InventoryTransactionActivityID,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryTransactionActivityLog = InventoryTransactionActivity::where('id', $InventoryTransactionActivityID)->first();
        $logIds = $InventoryTransactionActivityLog->logid ? explode(',', $InventoryTransactionActivityLog->logid) : [];
        $logIds[] = $logId;
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $InventoryTransactionActivity->org_id,
            'name' => $InventoryTransactionActivity->name,
            'status' => $InventoryTransactionActivity->status,
            'effective_timestamp' => $InventoryTransactionActivity->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'org_id' => $InventoryTransactionActivity->org_id,
            'name' => $InventoryTransactionActivity->name,
            'status' => $InventoryTransactionActivity->status,
            'effective_timestamp' => $InventoryTransactionActivity->effective_timestamp,
        ];
        $logId = createLog(
            'inventory_transaction_activity',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $InventoryTransactionActivity->id,
            $oldData,
            $newData,
            $sessionId
        );
        $InventoryTransactionActivityLog = InventoryTransactionActivity::where('id', $InventoryTransactionActivity->id)->first();
        $logIds = $InventoryTransactionActivityLog->logid ? explode(',', $InventoryTransactionActivityLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'name' => $MedicationRoute->name,
                'org_id' => $MedicationRoute->org_id,
                'status' => $MedicationRoute->status,
                'effective_timestamp' => $MedicationRoute->effective_timestamp,
            ];
            $logId = createLog(
                'medication_routes',
                'insert',
                [
                    'message' => "'{$MedicationRouteDescription}' has been added",
                    'created_by' => $sessionName
                ],
                $MedicationRoute->id,
                null,
                $newData,
                $sessionId
            );
            $MedicationRoute->logid = $logId;
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
            $MedicationRoute->effective_timestamp = 0;
            $statusLog = 'Inactive';

        }
        $MedicationRoute->status = $UpdateStatus;
        $MedicationRoute->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'medication_routes',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $MedicationRouteID,
            $oldData,
            $newData,
            $sessionId
        );
        $MedicationRouteLog = MedicationRoutes::where('id', $MedicationRouteID)->first();
        $logIds = $MedicationRouteLog->logid ? explode(',', $MedicationRouteLog->logid) : [];
        $logIds[] = $logId;
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'name' => $MedicationRoute->name,
            'org_id' => $MedicationRoute->org_id,
            'status' => $MedicationRoute->status,
            'effective_timestamp' => $MedicationRoute->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'name' => $MedicationRoute->name,
            'org_id' => $MedicationRoute->org_id,
            'status' => $MedicationRoute->status,
            'effective_timestamp' => $MedicationRoute->effective_timestamp,
        ];
        $logId = createLog(
            'medication_routes',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $MedicationRoute->id,
            $oldData,
            $newData,
            $sessionId
        );
        $MedicationRouteLog = MedicationRoutes::where('id', $MedicationRoute->id)->first();
        $logIds = $MedicationRouteLog->logid ? explode(',', $MedicationRouteLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'name' => $MedicationFrequency->name,
                'org_id' => $MedicationFrequency->org_id,
                'status' => $MedicationFrequency->status,
                'effective_timestamp' => $MedicationFrequency->effective_timestamp,
            ];
            $logId = createLog(
                'medication_frequency',
                'insert',
                [
                    'message' => "'{$MedicationFrequencyDescription}' has been added",
                    'created_by' => $sessionName
                ],
                $MedicationFrequency->id,
                null,
                $newData,
                $sessionId
            );
            $MedicationFrequency->logid = $logId;
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
            $MedicationFrequency->effective_timestamp = 0;
        }
        $MedicationFrequency->status = $UpdateStatus;
        $MedicationFrequency->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'medication_frequency',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $MedicationFrequencyID,
            $oldData,
            $newData,
            $sessionId
        );
        $MedicationFrequencyLog = MedicationFrequency::where('id', $MedicationFrequencyID)->first();
        $logIds = $MedicationFrequencyLog->logid ? explode(',', $MedicationFrequencyLog->logid) : [];
        $logIds[] = $logId;
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'name' => $MedicationFrequency->name,
            'org_id' => $MedicationFrequency->org_id,
            'status' => $MedicationFrequency->status,
            'effective_timestamp' => $MedicationFrequency->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'name' => $MedicationFrequency->name,
            'org_id' => $MedicationFrequency->org_id,
            'status' => $MedicationFrequency->status,
            'effective_timestamp' => $MedicationFrequency->effective_timestamp,
        ];
        $logId = createLog(
            'medication_frequency',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $MedicationFrequency->id,
            $oldData,
            $newData,
            $sessionId
        );
        $MedicationFrequencyLog = MedicationFrequency::where('id', $MedicationFrequency->id)->first();
        $logIds = $MedicationFrequencyLog->logid ? explode(',', $MedicationFrequencyLog->logid) : [];
        $logIds[] = $logId;
        $MedicationFrequencyLog->logid = implode(',', $logIds);
        $MedicationFrequencyLog->save();
        return response()->json(['success' => 'Medication Frequency Details updated successfully']);
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
        if ($condition === 'consumption') {
            $query->join(
                'inventory_transaction_activity AS ita',
                'ita.id',
                '=',
                'itt.activity_type'
            )
            ->where('ita.name', 'LIKE', '%Consumption%');
        }
        if ($condition === 'material_transfer') {
            $query->join(
                'inventory_transaction_activity AS ita',
                'ita.id',
                '=',
                'itt.activity_type'
            )
            ->where('ita.name', 'LIKE', '%material transfer%');
        }

        if ($condition === 'inventory_return') {
            $query->join(
                'inventory_transaction_activity AS ita',
                'ita.id',
                '=',
                'itt.activity_type'
            )
            ->where('ita.name', 'LIKE', '%return%');
        }
        if ($request_mandatory === 'y') {
            $query->where('itt.request_mandatory', 'y');
        }
        elseif ($request_mandatory === 'n') {
            $query->where('itt.request_mandatory', 'n');
        }
         $query->where('itt.status', 1);
        $query->orderBy('itt.id', 'ASC');
        $TransactionTypes = $query->get();

        return response()->json($TransactionTypes);
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
        $SourceLocation = $request->input('mc_source_location');
        $DestinationLocation = $request->input('mc_destination_location');

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
        $MaterialConsumptionRequisition->source_location_id = $SourceLocation;
        $MaterialConsumptionRequisition->destination_location_id = $DestinationLocation;
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

        // New logging (insert)
        $newData = [
            'org_id' => $MaterialConsumptionRequisition->org_id,
            'site_id' => $MaterialConsumptionRequisition->site_id,
            'transaction_type_id' => $MaterialConsumptionRequisition->transaction_type_id,
            'source_location_id' => $MaterialConsumptionRequisition->source_location_id,
            'destination_location_id' => $MaterialConsumptionRequisition->destination_location_id,
            'mr_code' => $MaterialConsumptionRequisition->mr_code,
            'patient_age' => $MaterialConsumptionRequisition->patient_age,
            'patient_gender_id' => $MaterialConsumptionRequisition->patient_gender_id,
            'service_id' => $MaterialConsumptionRequisition->service_id,
            'service_mode_id' => $MaterialConsumptionRequisition->service_mode_id,
            'billing_cc' => $MaterialConsumptionRequisition->billing_cc,
            'physician_id' => $MaterialConsumptionRequisition->physician_id,
            'generic_id' => $MaterialConsumptionRequisition->generic_id,
            'qty' => $MaterialConsumptionRequisition->qty,
            'remarks' => $MaterialConsumptionRequisition->remarks,
            'status' => $MaterialConsumptionRequisition->status,
            'code' => $MaterialConsumptionRequisition->code,
            'effective_timestamp' => $MaterialConsumptionRequisition->effective_timestamp,
        ];
        $logId = createLog(
            'material_consumption_requisition',
            'insert',
            [
                'message' => 'Requisition has been added',
                'created_by' => $sessionName
            ],
            $MaterialConsumptionRequisition->id,
            null,
            $newData,
            $sessionId
        );
        $MaterialConsumptionRequisition->logid = $logId;
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
        ->leftJoin('service_location as source_location', 'source_location.id', '=', 'material_consumption_requisition.source_location_id')
        ->leftJoin('service_location as destination_location', 'destination_location.id', '=', 'material_consumption_requisition.destination_location_id')
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
            'source_location.name as sourceLocationName',
            'destination_location.name as destinationLocationName',
            'gender.name as Gender',
            'patient.name as patientName',
            'patient.mr_code as mr_code',
            'patient.dob as DOB',
            'employee.name as Physician',
            'services.name as serviceName',
            'service_mode.name as serviceModeName',
            'costcenter.name as CCName',
            'service_group.name as serviceGroupName'
        );

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $Requisitions->where('material_consumption_requisition.org_id', '=', $sessionOrg);
        }
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Requisitions->whereIn('org_site.id', $sessionSiteIds);
            }
        }
        $Requisitions = $Requisitions->orderBy('material_consumption_requisition.id', 'desc')->get();
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

                // Build location information
                $locationInfo = '';
                if (!empty($Requisition->sourceLocationName)) {
                    $locationInfo .= '<br><b>Source Location: </b>' . ucwords($Requisition->sourceLocationName);
                }
                if (!empty($Requisition->destinationLocationName)) {
                    $locationInfo .= '<br><b>Destination Location: </b>' . ucwords($Requisition->destinationLocationName);
                }
                if (empty($Requisition->sourceLocationName) && empty($Requisition->destinationLocationName)) {
                    $locationInfo = '<br><b>Location: </b>N/A';
                }

                return  $RequisitionCode
                        . '<hr class="mt-1 mb-2">'
                        .'<b>Transaction Type: </b> '.ucwords($Requisition->transactionType)
                        .'<br><b>Site: </b>'.ucwords($Requisition->siteName)
                        .$locationInfo
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
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Generic</th>
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
            $MaterialConsumptionRequisition->effective_timestamp = 0;
        }
        $MaterialConsumptionRequisition->status = $UpdateStatus;
        $MaterialConsumptionRequisition->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'material_consumption_requisition',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $MaterialConsumptionID,
            $oldData,
            $newData,
            $sessionId
        );
        $MaterialConsumptionRequisitionLog = MaterialConsumptionRequisition::where('id', $MaterialConsumptionID)->first();
        $logIds = $MaterialConsumptionRequisitionLog->logid ? explode(',', $MaterialConsumptionRequisitionLog->logid) : [];
        $logIds[] = $logId;
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
        ->leftJoin('service_location as source_location', 'source_location.id', '=', 'material_consumption_requisition.source_location_id')
        ->leftJoin('service_location as destination_location', 'destination_location.id', '=', 'material_consumption_requisition.destination_location_id')
        ->leftJoin('services', 'services.id', '=', 'material_consumption_requisition.service_id')
        ->select('material_consumption_requisition.*', 'inventory_transaction_type.name as transactionType',
        'organization.organization as orgName','org_site.name as siteName',
        'source_location.name as sourceLocationName','destination_location.name as destinationLocationName',
        'inventory_generic.name as invGeneric','services.name as serviceName',)
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
            'sourceLocationId' => ($Requisitions->source_location_id),
            'sourceLocationName' => ucwords($Requisitions->sourceLocationName),
            'destinationLocationId' => ($Requisitions->destination_location_id),
            'destinationLocationName' => ucwords($Requisitions->destinationLocationName),
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $MaterialConsumptionRequisition->org_id,
            'site_id' => $MaterialConsumptionRequisition->site_id,
            'transaction_type_id' => $MaterialConsumptionRequisition->transaction_type_id,
            'source_location_id' => $MaterialConsumptionRequisition->source_location_id,
            'destination_location_id' => $MaterialConsumptionRequisition->destination_location_id,
            'mr_code' => $MaterialConsumptionRequisition->mr_code,
            'patient_age' => $MaterialConsumptionRequisition->patient_age,
            'patient_gender_id' => $MaterialConsumptionRequisition->patient_gender_id,
            'service_id' => $MaterialConsumptionRequisition->service_id,
            'service_mode_id' => $MaterialConsumptionRequisition->service_mode_id,
            'billing_cc' => $MaterialConsumptionRequisition->billing_cc,
            'physician_id' => $MaterialConsumptionRequisition->physician_id,
            'generic_id' => $MaterialConsumptionRequisition->generic_id,
            'qty' => $MaterialConsumptionRequisition->qty,
            'remarks' => $MaterialConsumptionRequisition->remarks,
            'status' => $MaterialConsumptionRequisition->status,
            'code' => $MaterialConsumptionRequisition->code,
            'effective_timestamp' => $MaterialConsumptionRequisition->effective_timestamp,
        ];
        
        $orgID = $request->input('u_mc_org');
        if (isset($orgID)) {
            $MaterialConsumptionRequisition->org_id = $orgID;
        }
        $MaterialConsumptionRequisition->site_id = $request->input('u_mc_site');
        $MaterialConsumptionRequisition->transaction_type_id = $request->input('u_mc_transactionType');
        $PatientMR =  $request->input('u_mc_patient');
        $MaterialConsumptionRequisition->mr_code = $PatientMR;
        $MaterialConsumptionRequisition->source_location_id = $request->input('u_mc_source_location');
        $MaterialConsumptionRequisition->destination_location_id = $request->input('u_mc_destination_location');
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
        // New logging (update)
        $newData = [
            'org_id' => $MaterialConsumptionRequisition->org_id,
            'site_id' => $MaterialConsumptionRequisition->site_id,
            'transaction_type_id' => $MaterialConsumptionRequisition->transaction_type_id,
            'source_location_id' => $MaterialConsumptionRequisition->source_location_id,
            'destination_location_id' => $MaterialConsumptionRequisition->destination_location_id,
            'mr_code' => $MaterialConsumptionRequisition->mr_code,
            'patient_age' => $MaterialConsumptionRequisition->patient_age,
            'patient_gender_id' => $MaterialConsumptionRequisition->patient_gender_id,
            'service_id' => $MaterialConsumptionRequisition->service_id,
            'service_mode_id' => $MaterialConsumptionRequisition->service_mode_id,
            'billing_cc' => $MaterialConsumptionRequisition->billing_cc,
            'physician_id' => $MaterialConsumptionRequisition->physician_id,
            'generic_id' => $MaterialConsumptionRequisition->generic_id,
            'qty' => $MaterialConsumptionRequisition->qty,
            'remarks' => $MaterialConsumptionRequisition->remarks,
            'status' => $MaterialConsumptionRequisition->status,
            'code' => $MaterialConsumptionRequisition->code,
            'effective_timestamp' => $MaterialConsumptionRequisition->effective_timestamp,
        ];
        $logId = createLog(
            'material_consumption_requisition',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $MaterialConsumptionRequisition->id,
            $oldData,
            $newData,
            $sessionId
        );
        $MaterialConsumptionRequisitionLog = MaterialConsumptionRequisition::where('id', $MaterialConsumptionRequisition->id)->first();
        $logIds = $MaterialConsumptionRequisitionLog->logid ? explode(',', $MaterialConsumptionRequisitionLog->logid) : [];
        $logIds[] = $logId;
        $MaterialConsumptionRequisitionLog->logid = implode(',', $logIds);
        $MaterialConsumptionRequisitionLog->save();
        return response()->json(['success' => 'Requisition For Material Consumption updated successfully']);
    }

    public function RequisitionMaterialTransfers()
    {
        $colName = 'requisition_for_material_transfer';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $isEmployee = $user->is_employee;
        $empId      = $user->emp_id;
        $roleId     = $user->role_id;

        $Generics = InventoryGeneric::select('inventory_generic.id', 'inventory_generic.name')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_generic.cat_id')
        ->where('inventory_generic.status', 1)
        ->where('inventory_category.name', 'not like', 'Medicine%')
        ->get();
        return view('dashboard.req_material_transfer', compact('user','Generics'));
    }

    public function AddRequisitionMaterialTransfers(RequisitionForMaterialTransferRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->requisition_for_material_transfer)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = $request->input('rmt_org');
        $SourceSite = trim($request->input('rmt_source_site')) ?: null;
        $SourceLocation = trim($request->input('rmt_source_location')) ?: null;
        $DestinationSite = trim($request->input('rmt_destination_site')) ?: null;
        $DestinationLocation = trim($request->input('rmt_destination_location')) ?: null;
        $TransactionType = $request->input('rmt_transactiontype');
        $Remarks = trim($request->input('rmt_remarks'));
        $itemGeneric =  implode(',',($request->input('rmt_itemgeneric')));
        $Qty =  implode(',',($request->input('rmt_qty')));

        $status = 1;
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $MaterialTransferRequisition = RequisitionForMaterialTransfer::create([
            'org_id'              => $Organization,
            'source_site'         => $SourceSite,
            'source_location'     => $SourceLocation,
            'destination_site'    => $DestinationSite,
            'destination_location' => $DestinationLocation,
            'transaction_type_id' => $TransactionType,
            'generic_id'          => $itemGeneric,
            'qty'                 => $Qty,
            'remarks'             => $Remarks,
            'status'              => $status,
            'user_id'             => $sessionId,
            'last_updated'        => $last_updated,
            'timestamp'           => $timestamp,
            'effective_timestamp' => $this->currentDatetime,
        ]);

        if (!$MaterialTransferRequisition->id) {
            return response()->json(['error' => 'Failed to add Requisition For Material Transfer.']);
        }

        $TransactionType = InventoryTransactionType::find($TransactionType);
        $TransactionTypeName = $TransactionType ? $TransactionType->name : '';
        $Site = Site::find($SourceSite);
        $SiteName = $Site ? $Site->name : '';
        $idStr = str_pad($MaterialTransferRequisition->id, 5, "0", STR_PAD_LEFT);
        $firstSiteNameLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $SiteName))));
        // $RequisitionCode = 'RMT - '.$idStr;
        $RequisitionCode = $firstSiteNameLetters.'-RMT-'.$idStr;

        $MaterialTransferRequisition->code = $RequisitionCode;
        $MaterialTransferRequisition->save();



        // New logging (insert)
        $newData = [
            'org_id' => $MaterialTransferRequisition->org_id,
            'source_site' => $MaterialTransferRequisition->source_site,
            'destination_site' => $MaterialTransferRequisition->destination_site,
            'transaction_type_id' => $MaterialTransferRequisition->transaction_type_id,
            'source_location' => $MaterialTransferRequisition->source_location,
            'destination_location' => $MaterialTransferRequisition->destination_location,
            'generic_id' => $MaterialTransferRequisition->generic_id,
            'demand_qty' => $MaterialTransferRequisition->demand_qty,
            'remarks' => $MaterialTransferRequisition->remarks,
            'status' => $MaterialTransferRequisition->status,
            'code' => $MaterialTransferRequisition->code,
            'effective_timestamp' => $MaterialTransferRequisition->effective_timestamp,
        ];
        $logId = createLog(
            'material_transfer_requisition',
            'insert',
            [
                'message' => 'Requisition has been added',
                'created_by' => $sessionName
            ],
            $MaterialTransferRequisition->id,
            null,
            $newData,
            $sessionId
        );
        $MaterialTransferRequisition->logid = $logId;
        $MaterialTransferRequisition->save();
        return response()->json(['success' => 'Requisition For Material Transfer added successfully']);
    }

    public function GetRequisitionMaterialTransfersData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->requisition_for_material_transfer)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        // $Requisitions = DB::table('requisition_other_transaction')
        // ->join('organization', 'organization.id', '=', 'requisition_other_transaction.org_id')
        // ->join('org_site', 'org_site.id', '=', 'requisition_other_transaction.site_id')
        // ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'requisition_other_transaction.transaction_type_id')
        // ->join('service_location', 'service_location.id', '=', 'requisition_other_transaction.inv_location_id')
        // ->select(
        //     'requisition_other_transaction.*',
        //     'inventory_transaction_type.name as transactionType',
        //     'organization.organization as orgName',
        //     'org_site.name as siteName',
        //     'service_location.name as locationName'
        // );

        $Requisitions = DB::table('requisition_material_transfer')
        ->join('organization', 'organization.id', '=', 'requisition_material_transfer.org_id')
        ->leftJoin('org_site as source', 'source.id', '=', 'requisition_material_transfer.source_site')
        ->leftJoin('org_site as destination', 'destination.id', '=', 'requisition_material_transfer.destination_site')
        ->leftJoin('service_location as sourceLocation', 'sourceLocation.id', '=', 'requisition_material_transfer.source_location')
        ->leftJoin('service_location as destinationLocation', 'destinationLocation.id', '=', 'requisition_material_transfer.destination_location')
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'requisition_material_transfer.transaction_type_id')
        ->select(
            'requisition_material_transfer.*',
            'inventory_transaction_type.name as transactionType',
            'organization.organization as orgName',
            'source.name as sourceSiteName',
            'sourceLocation.name as SourceLocationName',
            'destinationLocation.name as DestinationLocationName',
            'destination.name as destinationSiteName'
        );

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $Requisitions->where('requisition_material_transfer.org_id', '=', $sessionOrg);
        }

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Requisitions->where(function($query) use ($sessionSiteIds) {
                    $query->whereIn('requisition_material_transfer.source_site', $sessionSiteIds)
                          ->orWhereIn('requisition_material_transfer.destination_site', $sessionSiteIds);
                });
            }
        }
        $Requisitions = $Requisitions->get();
        return DataTables::of($Requisitions)
            ->addColumn('id_raw', function ($Requisition) {
                return $Requisition->id;
            })
            ->editColumn('requisition_detail', function ($Requisition) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $effectiveDate = Carbon::createFromTimestamp($Requisition->effective_timestamp)->format('l d F Y');
                $timestamp = Carbon::createFromTimestamp($Requisition->timestamp)->format('l d F Y');
                $lastUpdated = Carbon::createFromTimestamp($Requisition->last_updated)->format('l d F Y');
                $orgName = $Requisition->orgName;
                $RequisitionCode = $Requisition->code;

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

                $sourceSite = '';
                $sourceLocation = '';
                $destinationSite = '';
                $destinationLocation = '';

                if (($Requisition->sourceSiteName) && ($Requisition->SourceLocationName))
                {
                    $sourceSite ='<br><b>Source Site: </b>'.ucwords($Requisition->sourceSiteName);
                    $sourceLocation ='<br><b>Source Location: </b>'.ucwords($Requisition->SourceLocationName);
                }

                if (($Requisition->destinationSiteName) && ($Requisition->DestinationLocationName))
                {
                    $destinationSite ='<br><b>Destination Site: </b>'.ucwords($Requisition->destinationSiteName);
                    $destinationLocation ='<br><b>Destination Location: </b>'.ucwords($Requisition->DestinationLocationName);
                }

                return  $RequisitionCode
                        . '<hr class="mt-1 mb-2">'
                        .'<b>Transaction Type: </b> '.ucwords($Requisition->transactionType)
                        .$sourceSite
                        .$sourceLocation
                        .$destinationSite
                        .$destinationLocation
                        .'<br><b>Request Date: </b>'.$timestamp
                        .'<br><b>Effective Date:</b>'.$effectiveDate
                        .'<br><b>Remarks: </b> ' . (!empty($Requisition->remarks) ? ucwords($Requisition->remarks) : 'N/A');
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
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Generic</th>
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
                    $edit = explode(',', $Rights->requisition_for_material_transfer)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-reqmaterialtransfer" data-rmt-id="'.$RequisitionId.'">'
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
                $updateStatus = explode(',', $rights->requisition_for_material_transfer)[3];
                return $updateStatus == 1 ? ($Requisition->status ? '<span class="label label-success rmt_status cursor-pointer" data-id="'.$Requisition->id.'" data-status="'.$Requisition->status.'">Active</span>' : '<span class="label label-danger rmt_status cursor-pointer" data-id="'.$Requisition->id.'" data-status="'.$Requisition->status.'">Inactive</span>') : ($Requisition->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','InventoryDetails',
            'requisition_detail'])
            ->make(true);
    }

    public function UpdateRequisitionMaterialTransferStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->requisition_for_material_transfer)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $RequisitionMaterialTransfer = RequisitionForMaterialTransfer::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $RequisitionMaterialTransfer->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $RequisitionMaterialTransfer->effective_timestamp = 0;
        }
        $RequisitionMaterialTransfer->status = $UpdateStatus;
        $RequisitionMaterialTransfer->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'material_transfer_requisition',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $ID,
            $oldData,
            $newData,
            $sessionId
        );
        $RequisitionMaterialTransferLog = RequisitionForMaterialTransfer::where('id', $ID)->first();
        $logIds = $RequisitionMaterialTransferLog->logid ? explode(',', $RequisitionMaterialTransferLog->logid) : [];
        $logIds[] = $logId;
        $RequisitionMaterialTransferLog->logid = implode(',', $logIds);
        $RequisitionMaterialTransferLog->save();

        $RequisitionMaterialTransfer->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateRequisitionMaterialTransferModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->requisition_for_material_transfer)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        // $Requisitions = DB::table('requisition_other_transaction')
        // ->join('organization', 'organization.id', '=', 'requisition_other_transaction.org_id')
        // ->join('org_site', 'org_site.id', '=', 'requisition_other_transaction.site_id')
        // ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'requisition_other_transaction.transaction_type_id')
        // ->join('service_location', 'service_location.id', '=', 'requisition_other_transaction.inv_location_id')
        // ->select(
        //     'requisition_other_transaction.*',
        //     'inventory_transaction_type.name as transactionType',
        //     'organization.organization as orgName',
        //     'org_site.name as siteName',
        //     'service_location.name as locationName'
        // )
        // ->where('requisition_other_transaction.id', '=', $id)
        // ->first();

        $Requisitions = DB::table('requisition_material_transfer')
        ->join('organization', 'organization.id', '=', 'requisition_material_transfer.org_id')
        ->join('org_site as source', 'source.id', '=', 'requisition_material_transfer.source_site')
        ->join('org_site as destination', 'destination.id', '=', 'requisition_material_transfer.destination_site')
        ->join('service_location as sourceLocation', 'sourceLocation.id', '=', 'requisition_material_transfer.source_location')
        ->join('service_location as destinationLocation', 'destinationLocation.id', '=', 'requisition_material_transfer.destination_location')
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'requisition_material_transfer.transaction_type_id')
        ->select(
            'requisition_material_transfer.*',
            'inventory_transaction_type.name as transactionType',
            'organization.organization as orgName',
            'source.name as sourceSiteName',
            'sourceLocation.name as SourceLocationName',
            'destinationLocation.name as DestinationLocationName',
            'destination.name as destinationSiteName'
        )
        ->where('requisition_material_transfer.id', '=', $id)
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
            'sourceSite' => ucwords($Requisitions->sourceSiteName),
            'sourcesiteId' => ($Requisitions->source_site),
            'destinationSite' => ucwords($Requisitions->destinationSiteName),
            'destinationsiteId' => ($Requisitions->destination_site),
            'transactionType' => ucwords($Requisitions->transactionType),
            'transactionTypeId' => ($Requisitions->transaction_type_id),
            'remarks' => ucwords($Requisitions->remarks),
            'SourceLocationId' => ($Requisitions->source_location),
            'SourcelocationName' => ucwords($Requisitions->SourceLocationName),
            'DestinationLocationId' => ($Requisitions->destination_location),
            'DestinationlocationName' => ucwords($Requisitions->DestinationLocationName),
            'genericIds' => $Requisitions->generic_id,
            'genericNames' => ucwords($Requisitions->genericNames),
            'Qty' => $Requisitions->qty,
        ];
        return response()->json($data);
    }

    public function UpdateRequisitionMaterialTransfer(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->requisition_for_material_transfer)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $MaterialTransferRequisition = RequisitionForMaterialTransfer::findOrFail($id);
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $MaterialTransferRequisition->org_id,
            'source_site' => $MaterialTransferRequisition->source_site,
            'source_location' => $MaterialTransferRequisition->source_location,
            'destination_site' => $MaterialTransferRequisition->destination_site,
            'destination_location' => $MaterialTransferRequisition->destination_location,
            'transaction_type_id' => $MaterialTransferRequisition->transaction_type_id,
            'generic_id' => $MaterialTransferRequisition->generic_id,
            'qty' => $MaterialTransferRequisition->qty,
            'remarks' => $MaterialTransferRequisition->remarks,
            'status' => $MaterialTransferRequisition->status,
            'effective_timestamp' => $MaterialTransferRequisition->effective_timestamp,
        ];
        
        $orgID = $request->input('u_rmt_org');
        if (isset($orgID)) {
            $MaterialTransferRequisition->org_id = $orgID;
        }
        $MaterialTransferRequisition->source_site = $request->input('u_rmt_source_site');
        $MaterialTransferRequisition->source_location = $request->input('u_rmt_source_location');

        $MaterialTransferRequisition->destination_site = $request->input('u_rmt_destination_site');
        $MaterialTransferRequisition->destination_location = $request->input('u_rmt_destination_location');

        $MaterialTransferRequisition->transaction_type_id = $request->input('u_rmt_transactiontype');
        $MaterialTransferRequisition->remarks = $request->input('u_rmt_remarks');

        $MaterialTransferRequisition->generic_id = implode(',',($request->input('u_rmt_itemgeneric')));
        $MaterialTransferRequisition->qty = implode(',',($request->input('u_rmt_qty')));

        $MaterialTransferRequisition->last_updated = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $MaterialTransferRequisition->save();

        if (empty($MaterialTransferRequisition->id)) {
            return response()->json(['error' => 'Failed to update Requisition For Material Transfer. Please try again']);
        }
        // New logging (update)
        $newData = [
            'org_id' => $MaterialTransferRequisition->org_id,
            'source_site' => $MaterialTransferRequisition->source_site,
            'source_location' => $MaterialTransferRequisition->source_location,
            'destination_site' => $MaterialTransferRequisition->destination_site,
            'destination_location' => $MaterialTransferRequisition->destination_location,
            'transaction_type_id' => $MaterialTransferRequisition->transaction_type_id,
            'generic_id' => $MaterialTransferRequisition->generic_id,
            'qty' => $MaterialTransferRequisition->qty,
            'remarks' => $MaterialTransferRequisition->remarks,
            'status' => $MaterialTransferRequisition->status,
            'effective_timestamp' => $MaterialTransferRequisition->effective_timestamp,
        ];
        $logId = createLog(
            'material_transfer_requisition',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $MaterialTransferRequisition->id,
            $oldData,
            $newData,
            $sessionId
        );
        $MaterialTransferRequisitionLog = RequisitionForMaterialTransfer::where('id', $MaterialTransferRequisition->id)->first();
        $logIds = $MaterialTransferRequisitionLog->logid ? explode(',', $MaterialTransferRequisitionLog->logid) : [];
        $logIds[] = $logId;
        $MaterialTransferRequisitionLog->logid = implode(',', $logIds);
        $MaterialTransferRequisitionLog->save();
        return response()->json(['success' => 'Requisition For Material Transfer updated successfully']);
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
        // $Vendors = ThirdPartyRegistration::where('org_id', $orgId)
        // ->where('status', 1)
        // ->where('type', 'v')
        // ->select('id', 'person_name')
        // ->get();

        $Vendors = DB::table('third_party as tp')
        ->join('prefix as p', 'p.id', '=', 'tp.prefix_id')
        ->where('tp.org_id', $orgId)
        ->where('tp.status', 1)
        ->where('tp.type', 'v')
        ->select([
            'tp.id',
            'tp.person_name',
            'tp.corporate_name as corporateName',
            'p.name as prefixName',
        ])
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
            $PO->brand_id = $Brand;
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

            // New logging (insert)
            $newData = [
                'org_id' => $PO->org_id,
                'site_id' => $PO->site_id,
                'vendor_id' => $PO->vendor_id,
                'inventory_brand_id' => $PO->inventory_brand_id,
                'demand_qty' => $PO->demand_qty,
                'amount' => $PO->amount,
                'discount' => $PO->discount,
                'remarks' => $PO->remarks,
                'status' => $PO->status,
                'effective_timestamp' => $PO->effective_timestamp,
            ];
            $logId = createLog(
                'purchase_order',
                'insert',
                [
                    'message' => 'Purchase Order has been added',
                    'created_by' => $sessionName
                ],
                $PO->id,
                null,
                $newData,
                $sessionId
            );

            $PO->logid = $logId;
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
        ->orderBy('purchase_order.id', 'desc');
        
        if($this->sessionUser->org_id != '0')
        {
            $PurchaseOrders->where('purchase_order.org_id', '=', $this->sessionUser->org_id);
        }

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $PurchaseOrders->whereIn('purchase_order.site_id', $sessionSiteIds);
            }
        }
        $PurchaseOrders = $PurchaseOrders->get();
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

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'purchase_order',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $POID,
            $oldData,
            $newData,
            $sessionId
        );
        $POLog = PurchaseOrder::where('id', $POID)->first();
        $logIds = $POLog->logid ? explode(',', $POLog->logid) : [];
        $logIds[] = $logId;
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

        // New logging (approve)
        $logId = createLog(
            'purchase_order',
            'approve',
            [
                'message' => 'Purchase Order Approved',
                'updated_by' => $sessionName
            ],
            $POID,
            null,
            ['approval' => 1, 'approved_by' => $userId, 'approved_timestamp' => $CurrentTimestamp],
            $session->id
        );
        $POLog = PurchaseOrder::where('id', $POID)->first();
        $logIds = $POLog->logid ? explode(',', $POLog->logid) : [];
        $logIds[] = $logId;
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
        'third_party.corporate_name as corporateName',
        'purchase_order.inventory_brand_id as brandIds','prefix.name as prefixName')
        ->join('organization', 'organization.id', '=', 'purchase_order.org_id')
        ->join('org_site', 'org_site.id', '=', 'purchase_order.site_id')
        ->join('third_party', 'third_party.id', '=', 'purchase_order.vendor_id')
        ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
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
            'prefixName' => ucwords($PurchaseOrders->prefixName),
            'corporateName' => ucwords($PurchaseOrders->corporateName),
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $PO->org_id,
            'site_id' => $PO->site_id,
            'vendor_id' => $PO->vendor_id,
            'brand_id' => $PO->inventory_brand_id,
            'demand_qty' => $PO->demand_qty,
            'amount' => $PO->amount,
            'discount' => $PO->discount,
            'remarks' => $PO->remarks,
            'status' => $PO->status,
            'effective_timestamp' => $PO->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'org_id' => $PO->org_id,
            'site_id' => $PO->site_id,
            'vendor_id' => $PO->vendor_id,
            'brand_id' => $PO->inventory_brand_id,
            'demand_qty' => $PO->demand_qty,
            'amount' => $PO->amount,
            'discount' => $PO->discount,
            'remarks' => $PO->remarks,
            'status' => $PO->status,
            'effective_timestamp' => $PO->effective_timestamp,
        ];
        $logId = createLog(
            'purchase_order',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $PO->id,
            $oldData,
            $newData,
            $sessionId
        );
        $PurchaseOrderLog = PurchaseOrder::where('id', $PO->id)->first();
        $logIds = $PurchaseOrderLog->logid ? explode(',', $PurchaseOrderLog->logid) : [];
        $logIds[] = $logId;
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

            // New logging (insert)
            $newData = [
                'org_id' => $WO->org_id,
                'site_id' => $WO->site_id,
                'vendor_id' => $WO->vendor_id,
                'particulars' => $WO->particulars,
                'amount' => $WO->amount,
                'discount' => $WO->discount,
                'remarks' => $WO->remarks,
                'status' => $WO->status,
                'effective_timestamp' => $WO->effective_timestamp,
            ];
            $logId = createLog(
                'work_order',
                'insert',
                [
                    'message' => 'Work Order has been added',
                    'created_by' => $sessionName
                ],
                $WO->id,
                null,
                $newData,
                $sessionId
            );

            $WO->logid = $logId;
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

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $WorkOrders->whereIn('work_order.site_id', $sessionSiteIds);
            }
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

        // New logging (status_change) — only status
        $oldData = ['status' => (int)$Status];
        $newData = ['status' => $UpdateStatus];
        $logId = createLog(
            'work_order',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $WOID,
            $oldData,
            $newData,
            $sessionId
        );
        $WOLog = WorkOrder::where('id', $WOID)->first();
        $logIds = $WOLog->logid ? explode(',', $WOLog->logid) : [];
        $logIds[] = $logId;
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

        // New logging (approve)
        $logId = createLog(
            'work_order',
            'approve',
            [
                'message' => 'Work Order Approved',
                'updated_by' => $sessionName
            ],
            $WOID,
            null,
            ['approval' => 1, 'approved_by' => $userId, 'approved_timestamp' => $CurrentTimestamp],
            $session->id
        );
        $WOLog = WorkOrder::where('id', $WOID)->first();
        $logIds = $WOLog->logid ? explode(',', $WOLog->logid) : [];
        $logIds[] = $logId;
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
        'third_party.person_name as vendorName',
        'third_party.corporate_name as corporateName',
        'prefix.name as prefixName')
        ->join('organization', 'organization.id', '=', 'work_order.org_id')
        ->join('org_site', 'org_site.id', '=', 'work_order.site_id')
        ->join('third_party', 'third_party.id', '=', 'work_order.vendor_id')
        ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
        ->where('work_order.id', '=', $id)
        ->first();

        // $PurchaseOrders = PurchaseOrder::select('purchase_order.*',
        // 'organization.organization as orgName',
        // 'org_site.name as siteName',
        // 'third_party.person_name as vendorName',
        // 'third_party.corporate_name as corporateName',
        // 'purchase_order.inventory_brand_id as brandIds','prefix.name as prefixName')
        // ->join('organization', 'organization.id', '=', 'purchase_order.org_id')
        // ->join('org_site', 'org_site.id', '=', 'purchase_order.site_id')
        // ->join('third_party', 'third_party.id', '=', 'purchase_order.vendor_id')
        // ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
        // ->where('purchase_order.id', '=', $id)
        // ->first();

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
            'corporateName' => ucwords($WorkOrders->corporateName),
            'prefixName' => ucwords($WorkOrders->prefixName),
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
        
        // Capture old data BEFORE modifying
        $oldData = [
            'org_id' => $WO->org_id,
            'site_id' => $WO->site_id,
            'vendor_id' => $WO->vendor_id,
            'particulars' => $WO->particulars,
            'amount' => $WO->amount,
            'discount' => $WO->discount,
            'remarks' => $WO->remarks,
            'status' => $WO->status,
            'effective_timestamp' => $WO->effective_timestamp,
        ];
        
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
        // New logging (update)
        $newData = [
            'org_id' => $WO->org_id,
            'site_id' => $WO->site_id,
            'vendor_id' => $WO->vendor_id,
            'particulars' => $WO->particulars,
            'amount' => $WO->amount,
            'discount' => $WO->discount,
            'remarks' => $WO->remarks,
            'status' => $WO->status,
            'effective_timestamp' => $WO->effective_timestamp,
        ];
        $logId = createLog(
            'work_order',
            'update',
            [
                'message' => 'Data has been updated',
                'updated_by' => $sessionName
            ],
            $WO->id,
            $oldData,
            $newData,
            $sessionId
        );
        $WorkOrderLog = WorkOrder::where('id', $WO->id)->first();
        $logIds = $WorkOrderLog->logid ? explode(',', $WorkOrderLog->logid) : [];
        $logIds[] = $logId;
        $WorkOrderLog->logid = implode(',', $logIds);
        $WorkOrderLog->save();
        return response()->json(['success' => 'Work Order Details updated successfully']);
    }
    
    public function GetTransactionTypeInventoryManagement(Request $request)
    {
        $siteId            = $request->input('siteId');
        $transactionTypeId = $request->input('transactionTypeId');
        $transactionType   = $request->input('transactionType'); // 'requisition', 'external', 'other'

        $user       = auth()->user();
        $roleId     = $user->role_id;
        $isEmployee = $user->is_employee;
        $empId      = $user->emp_id;

        // Updated select: use new columns introduced in inventory_transaction_type
        $TransactionType = InventoryTransactionType::select(
            'source_type.name as Source',
            'destination_type.name as Destination',
            'inventory_transaction_type.emp_location_mandatory_request',
            'inventory_transaction_type.emp_location_source_destination',
            'inventory_transaction_type.source_location',
            'inventory_transaction_type.destination_location',
            'inventory_transaction_type.transaction_expired_status',
            'inventory_transaction_type.source_action',
            'inventory_transaction_type.destination_action'
        )
        ->join('inventory_source_destination_type as source_type', 'source_type.id', '=', 'inventory_transaction_type.source_location_type')
        ->join('inventory_source_destination_type as destination_type', 'destination_type.id', '=', 'inventory_transaction_type.destination_location_type')
        ->where('inventory_transaction_type.id', '=', $transactionTypeId)
        ->first();
        // dd($TransactionType,$transactionTypeId);


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

        // Parse configured locations if provided on the transaction type
        $configuredSourceLocationIDs = [];
        if (!empty($TransactionType->source_location)) {
            $configuredSourceLocationIDs = array_values(array_filter(array_map(function ($v) {
                return (string) trim($v);
            }, explode(',', $TransactionType->source_location))));
        }

        $configuredDestinationLocationIDs = [];
        if (!empty($TransactionType->destination_location)) {
            $configuredDestinationLocationIDs = array_values(array_filter(array_map(function ($v) {
                return (string) trim($v);
            }, explode(',', $TransactionType->destination_location))));
        }


        // Helper to compute employee inventory locations by site using location_site mapping
        $getEmpServiceLocationIDs = function (int $empId, $siteId) {
            $allowed = [];
            $rows = DB::table('emp_inventory_location')
                ->where('emp_id', $empId)
                ->where('status', 1)
                ->get();

            foreach ($rows as $row) {
                $siteList = [];
                if (!empty($row->location_site)) {
                    $siteList = array_map('trim', explode(',', (string) $row->location_site));
                }

                $svcJson = $row->service_location_id;
                $svcDecoded = json_decode($svcJson, true);
                if (!is_array($svcDecoded)) {
                    $svcDecoded = [];
                }

                // Find matching index for given site and take the corresponding group of locations
                if (!empty($siteId)) {
                    foreach ($siteList as $idx => $siteVal) {
                        if ((string) $siteVal === (string) $siteId) {
                            $group = $svcDecoded[$idx] ?? [];
                            // $group may be scalar, array, or nested arrays; normalize to flat strings
                            $flat = [];
                            $walker = function ($arr) use (&$flat, &$walker) {
                                if (is_array($arr)) {
                                    foreach ($arr as $it) { $walker($it); }
                                } else {
                                    $flat[] = (string) $arr;
                                }
                            };
                            $walker($group);
                            $allowed = array_merge($allowed, $flat);
                        }
                    }
                }
            }

            return array_values(array_unique($allowed));
        };

        $empServiceLocationIDs = [];
        if ($roleId != 1 && $isEmployee == 1) {
            $empServiceLocationIDs = $getEmpServiceLocationIDs((int) $empId, $siteId);
        }


        // Determine which employee location filtering to use based on transaction type
        // For requisition transactions: use emp_location_mandatory_request
        // For external transactions and other transactions: use emp_location_source_destination
        $empLocSide = null;
        
        if ($transactionType === 'requisition') {
            // For requisition transactions, use emp_location_mandatory_request
            $empLocSide = strtolower((string) $TransactionType->emp_location_mandatory_request); // 's', 'd', 'n'
        } else {

            // For external transactions and other transactions (or when transactionType is not provided), use emp_location_source_destination
            $empLocSide = strtolower((string) $TransactionType->emp_location_source_destination); // 's', 'd', 'n'
        }
        
        // dd($empLocSide, $TransactionType->emp_location_source_destination, $TransactionType->emp_location_mandatory_request);

        // Prepare final arrays
        $sourceData      = [];
        $destinationData = [];

        // SOURCE options
        if (stripos($sourceName, 'location') !== false) {
            $srcQuery = DB::table('service_location')
                ->where('service_location.status', 1)
                ->where('service_location.name', 'not like', '%Not Applicable%')
                ->select('service_location.id', 'service_location.name')
                ->orderBy('service_location.name', 'asc');

            // Restrict to locations activated for the selected site, if provided
            if (!empty($siteId)) {
                $srcQuery->join('activated_location as al', 'al.location_id', '=', 'service_location.id')
                    ->where('al.status', 1)
                    ->where('al.site_id', $siteId)
                    ->distinct();
            }

            // Build final filter set for source locations
            $finalSourceIDs = null; // null means no restriction

            if (!empty($configuredSourceLocationIDs)) {
                $finalSourceIDs = $configuredSourceLocationIDs;
            }

            if ($empLocSide == 's') {
                // dd($empLocSide);
                $finalSourceIDs = is_null($finalSourceIDs)
                    ? $empServiceLocationIDs
                    : array_values(array_intersect($finalSourceIDs, $empServiceLocationIDs));
            }

            if (!is_null($finalSourceIDs)) {
                if (empty($finalSourceIDs)) {
                    $sourceData = collect();
                } else {
                    $srcQuery->whereIn('service_location.id', $finalSourceIDs);
                }
            }

            if (empty($sourceData)) {
                $sourceData = $srcQuery->get();
            }
        } 
        elseif ($sourceName === 'vendor') {
            $sourceData = DB::table('third_party')
                ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
                ->select('third_party.id', 'third_party.person_name','third_party.corporate_name','prefix.name as prefix')
                ->where('third_party.type', 'v')
                ->orderBy('third_party.person_name', 'asc')
                ->get();
        } 
        elseif ($sourceName === 'donor') {
            $sourceData = DB::table('third_party')
                ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
                ->select('third_party.id', 'third_party.person_name','third_party.corporate_name','prefix.name as prefix')
                ->where('third_party.type', 'd')
                ->orderBy('third_party.person_name', 'asc')
                ->get();
        } 
        elseif ($sourceName === 'patient') {
            $query = DB::table('patient_inout as pio')
                ->join('patient as p', 'p.mr_code', '=', 'pio.mr_code')
                ->select(
                    'pio.mr_code as id',
                    DB::raw("CONCAT(pio.mr_code, ' - ', p.name) as patient_name")
                )
                ->where('pio.status', 1)
                ->orderBy('p.name', 'asc');

            if (!empty($siteId)) {
                $query->where('pio.site_id', $siteId);
            }

            $sourceData = $query->get();
        }

        // DESTINATION options
        if (stripos($destinationName, 'location') !== false) {

            $destQuery = DB::table('service_location')
                ->where('service_location.status', 1)
                ->where('service_location.name', 'not like', '%Not Applicable%')
                ->select('service_location.id', 'service_location.name')
                ->orderBy('service_location.name', 'asc');

            if (!empty($siteId)) {
                $destQuery->join('activated_location as al', 'al.location_id', '=', 'service_location.id')
                    ->where('al.status', 1)
                    ->where('al.site_id', $siteId)
                    ->distinct();
            }

            $finalDestinationIDs = null; // null means no restriction

            if (!empty($configuredDestinationLocationIDs)) {
                $finalDestinationIDs = $configuredDestinationLocationIDs;
            }

            if ($empLocSide === 'd') {
            // if ($empLocSide === 'd' && !empty($empServiceLocationIDs)) {
                $finalDestinationIDs = is_null($finalDestinationIDs)
                    ? $empServiceLocationIDs
                    : array_values(array_intersect($finalDestinationIDs, $empServiceLocationIDs));
            }

            if (!is_null($finalDestinationIDs)) {
                if (empty($finalDestinationIDs)) {
                    $destinationData = collect();
                } else {
                    $destQuery->whereIn('service_location.id', $finalDestinationIDs);
                }
            }

            if (empty($destinationData)) {
                $destinationData = $destQuery->get();
            }
        } 
        elseif ($destinationName === 'vendor') {
            $destinationData = DB::table('third_party')
                ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
                ->select('third_party.id', 'third_party.person_name','third_party.corporate_name','prefix.name as prefix')
                ->where('third_party.type', 'v')
                ->orderBy('third_party.person_name', 'asc')
                ->get();
        } 
        elseif ($destinationName === 'donor') {
            $destinationData = DB::table('third_party')
                ->join('prefix', 'prefix.id', '=', 'third_party.prefix_id')
                ->select('third_party.id', 'third_party.person_name','third_party.corporate_name','prefix.name as prefix')
                ->where('third_party.type', 'd')
                ->orderBy('third_party.person_name', 'asc')
                ->get();
        } 
        elseif ($destinationName === 'patient') {
            $query = DB::table('patient_inout as pio')
                ->join('patient as p', 'p.mr_code', '=', 'pio.mr_code')
                ->select(
                    'pio.mr_code as id',
                    DB::raw("CONCAT(pio.mr_code, ' - ', p.name) as patient_name")
                )
                ->where('pio.status', 1)
                ->orderBy('p.name', 'asc');

            if (!empty($siteId)) {
                $query->where('pio.site_id', $siteId);
            }

            $destinationData = $query->get();
        }

        return response()->json([
            'Source'                   => $TransactionType->Source,
            'Destination'              => $TransactionType->Destination,
            'sourceData'               => $sourceData,
            'destinationData'          => $destinationData,
            'LocationMandatory'        => $TransactionType->emp_location_mandatory_request,
            'transaction_expired_status' => $TransactionType->transaction_expired_status,
            'source_action'            => $TransactionType->source_action,
            'destination_action'       => $TransactionType->destination_action,
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

        // $costcenters = DB::table('emp_cc as e')
        // ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
        // ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
        // ->where('e.emp_id', $empId)
        // ->where('ct.performing', 1)
        // ->select('c.id', 'c.name')
        // ->get();

        $costcenters = DB::table(function ($query) use ($empId) {
            $query->select('c.id', 'c.name')
                ->from('emp_cc as e')
                ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
                ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                ->where('e.emp_id', $empId)
                ->where('ct.performing', 1)

            ->unionAll(
                DB::table('employee as emp')
                    ->select('c.id', 'c.name')
                    ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, emp.cc_id)'), '>', DB::raw('0'))
                    ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                    ->where('emp.id', $empId)
                    ->where('ct.performing', 1)
            );
        }, 'combined')
        ->distinct()
        ->get();

        // dd($costcenters);

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

        DB::beginTransaction();

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

        // $ExternalTransaction->save();

        // if (empty($ExternalTransaction->id)) {
        //     return response()->json(['error' => 'Failed to Add External Transaction.']);
        // }
        if (!$ExternalTransaction->save()) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to Add External Transaction.']);
        }

        // Log creation
        // New logging (insert)
        $newData = [
            'org_id' => $ExternalTransaction->org_id,
            'site_id' => $ExternalTransaction->site_id,
            'transaction_type_id' => $ExternalTransaction->transaction_type_id,
            'source' => $ExternalTransaction->source,
            'destination' => $ExternalTransaction->destination,
            'remarks' => $ExternalTransaction->remarks ?? null,
            'status' => $ExternalTransaction->status,
            'effective_timestamp' => $ExternalTransaction->effective_timestamp,
        ];
        $logId = createLog(
            'external_transaction',
            'insert',
            [
                'message' => 'External Transaction has been added',
                'created_by' => $sessionName
            ],
            $ExternalTransaction->id,
            null,
            $newData,
            $sessionId
        );

        $ExternalTransaction->logid = $logId;
        $ExternalTransaction->save();

        // $rule = DB::table('inventory_transaction_type')
        // ->select('applicable_location_to','source_action','destination_action')
        // ->where('id', $TransactionTypeID)
        // ->first();

        $rule = DB::table('inventory_transaction_type')
        ->select('source_action', 'destination_action', 'source_location_type', 'destination_location_type')
        ->where('id', $TransactionTypeID)
        ->first();

        // Decide the site/org balance action without using removed column
        // If destination action is defined (a/s/r), prefer that; otherwise fall back to source action
        $useAction = (isset($rule->destination_action) && in_array($rule->destination_action, ['a','s','r']))
            ? $rule->destination_action
            : $rule->source_action;

        $sourceType = DB::table('inventory_source_destination_type')->where('id', $rule->source_location_type)->value('name');
        $destinationType = DB::table('inventory_source_destination_type')->where('id', $rule->destination_location_type)->value('name');
        $count = max(
            count($Generics),
            count($Brands),
            count($Batches),
            count($Quantities)
        );

        // for ($i = 0; $i < $count; $i++) {
        //     $genId   = $Generics[$i]   ?? null;
        //     $brandId = $Brands[$i]     ?? null;
        //     $batchNo = $Batches[$i]    ?? null;
        //     $qty     = (int) ($Quantities[$i] ?? 0);
        //     $expTs   = $timestamps[$i] ?? null;

        //     if (! $genId || ! $brandId || ! $batchNo) {
        //         continue;
        //     }

        //     $prevOrgRow = InventoryBalance::where('generic_id', $genId)
        //         ->where('brand_id',  $brandId)
        //         ->where('batch_no',  $batchNo)
        //         ->where('org_id',    $Organization)
        //         ->orderBy('id', 'desc')
        //         ->first();
        //     $prevOrgBalance = $prevOrgRow->org_balance ?? 0;

        //     $prevSiteRow = InventoryBalance::where('generic_id', $genId)
        //     ->where('brand_id',  $brandId)
        //     ->where('batch_no',  $batchNo)
        //     ->where('org_id',    $Organization)
        //     ->where('site_id',   $Site)
        //     ->orderBy('id', 'desc')
        //     ->first();
        //     $prevSiteBalance = $prevSiteRow->site_balance ?? 0;

        //     switch ($useAction) {
        //         case 'a':  // add
        //             $newOrgBalance  = $prevOrgBalance  + $qty;
        //             $newSiteBalance = $prevSiteBalance + $qty;
        //             break;
        //         case 's':  // subtract
        //         case 'r':  // reverse (treat like subtract)
        //             $newOrgBalance  = $prevOrgBalance  - $qty;
        //             $newSiteBalance = $prevSiteBalance - $qty;
        //             break;
        //         default:   // 'n' or no‐op
        //             $newOrgBalance  = $prevOrgBalance;
        //             $newSiteBalance = $prevSiteBalance;
        //     }

        //     $dateTime   = Carbon::createFromTimestamp($timestampNow)->format('d-M-Y H:i');
        //     $remarkText = "Transaction by {$session->name} on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSiteBalance}";

        //     InventoryBalance::create([
        //         'management_id' => $ExternalTransaction->id,
        //         'generic_id'    => $genId,
        //         'brand_id'      => $brandId,
        //         'batch_no'      => $batchNo,
        //         'expiry_date'   => $expTs,
        //         'org_id'        => $Organization,
        //         'site_id'       => $Site,
        //         'org_balance'   => $newOrgBalance,
        //         'site_balance'  => $newSiteBalance,
        //         'remarks'       => $remarkText,
        //         'timestamp'     => $timestampNow,
        //     ]);
        // }

        for ($i = 0; $i < $count; $i++) {
            $genId   = $Generics[$i]   ?? null;
            $brandId = $Brands[$i]     ?? null;
            $batchNo = $Batches[$i]    ?? null;
            $qty     = (int) ($Quantities[$i] ?? 0);
            $expTs   = $timestamps[$i] ?? null;

            if (! $genId || ! $brandId || ! $batchNo) {
                continue;
            }

            //  --- Calculate org and site balances ---
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

            // if (in_array($useAction, ['s', 'r'])) {
            //     if ($qty > $prevSiteBalance) {
            //         return response()->json([
            //             'info' => "Transaction failed: Available balance is $prevSiteBalance"
            //         ]);
            //     }
            // }
            if ($rule->destination_action === 's' || $rule->destination_action === 'r' || $rule->source_action === 's' || $rule->source_action === 'r') {
                if ($qty > $prevSiteBalance) {
                    DB::rollBack(); // Undo everything
                    return response()->json([
                        'info' => "Transaction failed: Available balance is $prevSiteBalance"
                    ]);
                }
            }


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

            // --- Both source and destination are locations ---
            if (strtolower($sourceType) === 'inventory location' && $Source && strtolower($destinationType) === 'inventory location' && $Destination) {
                // Source location row
                $prevSourceLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $Organization)
                    ->where('site_id',   $Site)
                    ->where('location_id', $Source)
                    ->orderBy('id', 'desc')
                    ->first();
                $prevSourceLocBalance = $prevSourceLocRow->location_balance ?? 0;

                if ($rule->source_action === 'a') {
                    $newSourceLocBalance = $prevSourceLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSourceLocBalance = $prevSourceLocBalance - $qty;
                } else {
                    $newSourceLocBalance = $prevSourceLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $ExternalTransaction->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $Organization,
                    'site_id'          => $Site,
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $Source,
                    'location_balance' => $newSourceLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => $timestampNow,
                ]);

                // Destination location row
                $prevDestLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $Organization)
                    ->where('site_id',   $Site)
                    ->where('location_id', $Destination)
                    ->orderBy('id', 'desc')
                    ->first();
                $prevDestLocBalance = $prevDestLocRow->location_balance ?? 0;

                if ($rule->destination_action === 'a') {
                    $newDestLocBalance = $prevDestLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newDestLocBalance = $prevDestLocBalance - $qty;
                } else {
                    $newDestLocBalance = $prevDestLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $ExternalTransaction->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $Organization,
                    'site_id'          => $Site,
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $Destination,
                    'location_balance' => $newDestLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => $timestampNow,
                ]);
            }
            // --- Only source is a location ---
            elseif (strtolower($sourceType) === 'inventory location' && $Source) {
                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $Organization)
                    ->where('site_id',   $Site)
                    ->where('location_id', $Source)
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;

                if ($rule->source_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $ExternalTransaction->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $Organization,
                    'site_id'          => $Site,
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $Source,
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => $timestampNow,
                ]);
            }
            // --- Only destination is a location ---
            elseif (strtolower($destinationType) === 'inventory location' && $Destination) {

                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $Organization)
                    ->where('site_id',   $Site)
                    ->where('location_id', $Destination)
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;

                if ($rule->destination_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }
                // dd($newLocBalance, $Destination)
                InventoryBalance::create([
                    'management_id'    => $ExternalTransaction->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $Organization,
                    'site_id'          => $Site,
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $Destination,
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => $timestampNow,
                ]);
            }
            // --- Neither is a location: just org/site balances, location fields null ---
            else {

                InventoryBalance::create([
                    'management_id'    => $ExternalTransaction->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $Organization,
                    'site_id'          => $Site,
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => null,
                    'location_balance' => null,
                    'remarks'          => $remarkText,
                    'timestamp'        => $timestampNow,
                ]);
            }

        }
        DB::commit();
        return response()->json(['success' => 'External Transaction added successfully']);
    }

    public function GetExternalTransactionData(Request $request)
    {
        $rights = $this->rights;
        $view   = explode(',', $rights->external_transaction)[1];
        if ($view == 0) {
            abort(403, 'Forbidden');
        }
        $ExternalTransactions = InventoryManagement::select(
            'inventory_management.id',
            'inventory_management.transaction_type_id',
            'inventory_management.org_id',
            'inventory_management.site_id',
            'inventory_management.inv_generic_id',
            'inventory_management.brand_id',
            'inventory_management.ref_document_no',
            'inventory_management.transaction_qty',
            'inventory_management.batch_no',
            'inventory_management.expiry_date',
            'inventory_management.effective_timestamp',
            'inventory_management.remarks',
            'inventory_management.source',
            'inventory_management.destination',
            'inventory_transaction_type.name as TransactionTypeName',
            'inventory_transaction_type.activity_type',
            'organization.organization as orgName',
            'organization.code as orgCode',
            'org_site.name as siteName',
            'sdt.name as source_type_name',
            'ddt.name as destination_type_name',
            'src_tp.person_name as source_vendor_donor',
            'src_loc.name as source_location_name',
            'dst_tp.person_name as destination_vendor_donor',
            'dst_loc.name as destination_location_name'
            
        )
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'inventory_management.transaction_type_id')
        ->join('inventory_transaction_activity', 'inventory_transaction_activity.id', '=', 'inventory_transaction_type.activity_type')
        ->leftJoin('organization', 'organization.id', '=', 'inventory_management.org_id')
        ->join('org_site', 'org_site.id', '=', 'inventory_management.site_id')
        ->leftJoin('inventory_source_destination_type as sdt', 'sdt.id', '=', 'inventory_transaction_type.source_location_type')
        ->leftJoin('inventory_source_destination_type as ddt', 'ddt.id', '=', 'inventory_transaction_type.destination_location_type')
        ->leftJoin('third_party as src_tp', 'src_tp.id', '=', 'inventory_management.source')
        ->leftJoin('service_location as src_loc', 'src_loc.id', '=', 'inventory_management.source')
        ->leftJoin('third_party as dst_tp', 'dst_tp.id', '=', 'inventory_management.destination')
        ->leftJoin('service_location as dst_loc', 'dst_loc.id', '=', 'inventory_management.destination')
        ->where('inventory_transaction_activity.id', '=', function ($query) {
            $query->select('id')
                ->from('inventory_transaction_activity')
                ->where('name', 'external transaction')
                ->limit(1);
        });


        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $ExternalTransactions->whereIn('org_site.id', $sessionSiteIds);
            }
        }
        $ExternalTransactions = $ExternalTransactions->orderBy('inventory_management.id', 'desc');

        $sessionOrg = $this->sessionUser->org_id;
        if ($sessionOrg != '0') {
            $ExternalTransactions->where('inventory_management.org_id', '=', $sessionOrg);
        }
        //  $ExternalTransactions->get();

        // return DataTables::of($ExternalTransactions)
        return DataTables::eloquent($ExternalTransactions)
            ->filter(function($query) use ($request) {
                if ($request->has('search') && $search = $request->search['value']) {
                    $query->where(function($q) use ($search) {
                        $q->where('inventory_management.id', 'like', "%{$search}%")
                        ->orWhere('inventory_transaction_type.name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('org_site.name', 'like', "%{$search}%")
                        ->orWhere('inventory_management.transaction_qty', 'like', "%{$search}%")
                        ->orWhere('inventory_management.batch_no', 'like', "%{$search}%")
                        ->orWhere('inventory_management.ref_document_no', 'like', "%{$search}%")
                        ->orWhere('inventory_management.remarks', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(UPPER(LEFT(org_site.name, 3)), '-ET-', LPAD(inventory_management.id, 5, '0')) LIKE ?", ["%{$search}%"])
                        ->orWhereExists(function($subQuery) use ($search) {
                            $subQuery->select(DB::raw(1))
                                ->from('inventory_generic')
                                ->whereRaw("FIND_IN_SET(inventory_generic.id, inventory_management.inv_generic_id) > 0")
                                ->where('inventory_generic.name', 'like', "%{$search}%");
                        })
                        ->orWhereExists(function($subQuery) use ($search) {
                            $subQuery->select(DB::raw(1))
                                ->from('inventory_brand')
                                ->whereRaw("FIND_IN_SET(inventory_brand.id, inventory_management.brand_id) > 0")
                                ->where('inventory_brand.name', 'like', "%{$search}%");
                        });
                    });
                }
            })
            ->addColumn('id_raw', function ($row) {
                return $row->id;
            })
            ->addColumn('transaction_details', function ($row) {
                $siteCode = strtoupper(substr((string) $row->siteName, 0, 3));
                $idStr    = str_pad((string) $row->id, 5, "0", STR_PAD_LEFT);
                $code     = $siteCode . '-ET-' . $idStr;

                $issueDate = $row->effective_timestamp
                    ? \Carbon\Carbon::createFromTimestamp((int)$row->effective_timestamp)->format('d F Y')
                    : 'N/A';

                $referenceNumber = $row->ref_document_no ?: 'N/A';
                $siteName        = $row->siteName       ?: 'N/A';
                $remarks         = $row->remarks        ?: 'N/A';

                // Pre-resolved type names from joins
                $sourceTypeName      = strtolower((string)($row->source_type_name ?? ''));
                $destinationTypeName = strtolower((string)($row->destination_type_name ?? ''));

                // Choose names based on type (vendor/donor vs location)
                $sourceText = '';
                if ($sourceTypeName !== '') {
                    if (str_contains($sourceTypeName, 'vendor') || str_contains($sourceTypeName, 'donor')) {
                        $label = str_contains($sourceTypeName, 'vendor') ? 'Source Vendor' : 'Source Donor';
                        $name  = $row->source_vendor_donor ?: 'N/A';
                        $sourceText = "<b>{$label}:</b> {$name}<br>";
                    } elseif (str_contains($sourceTypeName, 'location')) {
                        $name  = $row->source_location_name ?: 'N/A';
                        $sourceText = "<b>Source Location:</b> {$name}<br>";
                    }
                }

                $destinationText = '';
                if ($destinationTypeName !== '') {
                    if (str_contains($destinationTypeName, 'vendor') || str_contains($destinationTypeName, 'donor')) {
                        $label = str_contains($destinationTypeName, 'vendor') ? 'Destination Vendor' : 'Destination Donor';
                        $name  = $row->destination_vendor_donor ?: 'N/A';
                        $destinationText = "<b>{$label}:</b> {$name}<br>";
                    } elseif (str_contains($destinationTypeName, 'location')) {
                        $name  = $row->destination_location_name ?: 'N/A';
                        $destinationText = "<b>Destination Location:</b> {$name}<br>";
                    }
                }

                return $code . '<br>'
                    . '<b>Ref #:</b> ' . e($referenceNumber) . '<br>'
                    . e(ucwords($row->TransactionTypeName)) . '<br>'
                    . $sourceText
                    . $destinationText
                    . '<b>Site Name:</b> ' . e($siteName) . '<br>'
                    . '<b>Issue Date:</b> ' . e($issueDate) . '<br>'
                    . '<b>Remarks:</b> ' . e($remarks);
            })
            // ->addColumn('item_details', function ($row) {
            //     $brandIds     = array_filter(explode(',', (string) $row->brand_id));
            //     $genericIds   = array_filter(explode(',', (string) $row->inv_generic_id));
            //     $batchArray   = array_filter(explode(',', (string) $row->batch_no));
            //     $expiryArray  = array_filter(explode(',', (string) $row->expiry_date));
            //     $qtyArray     = array_filter(explode(',', (string) $row->transaction_qty));

            //     $brands   = InventoryBrand::whereIn('id', $brandIds)->pluck('name', 'id')->toArray();
            //     $generics = InventoryGeneric::whereIn('id', $genericIds)->pluck('name', 'id')->toArray();

            //     $count = max(count($brandIds), count($genericIds), count($batchArray), count($expiryArray), count($qtyArray));

            //     // ——— copied styles from your InventoryDetails table ———
            //     $html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
            //         . '<thead style="background-color:#e2e8f0;color:#000;">'
            //             . '<tr>'
            //                 . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">S.No</th>'
            //                 . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>'
            //                 . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Brand</th>'
            //                 . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Batch No</th>'
            //                 . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Expiry Date</th>'
            //                 . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Quantity</th>'
            //             . '</tr>'
            //         . '</thead>'
            //         . '<tbody>';

            //     for ($i = 0; $i < $count; $i++) {
            //         $brandId     = $brandIds[$i]   ?? null;
            //         $genericId   = $genericIds[$i] ?? null;

            //         $brandName   = $brandId && isset($brands[$brandId]) ? $brands[$brandId] : 'N/A';
            //         $genericName = $genericId && isset($generics[$genericId]) ? $generics[$genericId] : 'N/A';

            //         $batchNo = $batchArray[$i]   ?? 'N/A';
            //         $expiry  = $expiryArray[$i]  ?? 'N/A';
            //         $qty     = $qtyArray[$i]     ?? 'N/A';

            //         if (! $genericId || ! $brandId || ! $batchNo) {
            //             continue;
            //         }

            //         $orgbalRow = InventoryBalance::where('generic_id', $genericId)
            //         ->where('brand_id',   $brandId)
            //         ->where('batch_no',   $batchNo)
            //         ->where('org_id', $row->org_id)
            //         // ->where('site_id', $row->site_id)
            //         ->orderBy('id', 'desc')
            //         ->first();

            //         $sitebalRow = InventoryBalance::where('generic_id', $genericId)
            //         ->where('brand_id',   $brandId)
            //         ->where('batch_no',   $batchNo)
            //         ->where('org_id', $row->org_id)
            //         ->where('site_id', $row->site_id)
            //         ->orderBy('id', 'desc')
            //         ->first();

            //         $orgBal  = $orgbalRow->org_balance  ?? 0;
            //         $siteBal = $sitebalRow->site_balance ?? 0;

            //         $locationBalances = InventoryBalance::where('generic_id', $genericId)
            //         ->where('brand_id', $brandId)
            //         ->where('batch_no', $batchNo)
            //         ->where('org_id', $row->org_id)
            //         ->where('site_id', $row->site_id)
            //         ->whereNotNull('location_id')
            //         ->orderBy('id', 'desc')
            //         ->get()
            //         ->groupBy('location_id')
            //         ->filter(function ($records) {
            //             $latest = $records->first();
            //             return $latest && $latest->location_balance > 0;
            //         })
            //         ->map(function ($records, $locId) {
            //             $latest = $records->first();
            //             $locName = DB::table('service_location')->where('id', $locId)->value('name') ?? 'Unknown';
            //             return $locName . ': ' . ($latest->location_balance ?? 0);
            //         })
            //         ->values()
            //         ->toArray();

            //         $locationJson = htmlspecialchars(json_encode($locationBalances), ENT_QUOTES, 'UTF-8');

            //         $formattedExpiry = is_numeric($expiry)
            //             ? \Carbon\Carbon::createFromTimestamp($expiry)->format('d-M-Y')
            //             : 'N/A';

            //         $expiredText = '';
            //         if (is_numeric($expiry) && Carbon::createFromTimestamp($expiry)
            //                 ->setTimezone('Asia/Karachi')->isPast()
            //         ) {
            //             $expiredText = '<br><span style="color: red; font-size: 12px;">Expired</span>';
            //         }

            //         $sno = $i + 1;
            //         $bg  = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';

            //         $html .= '<tr class="balance-row" data-org-balance="'.$orgBal.'" data-site-balance="'.$siteBal.'" data-loc-balance="'.$locationJson.'" style="background-color:'.$bg.';cursor:pointer;">'
            //             . '<td style="padding:8px;border:1px solid #ccc;">'.$sno.'</td>'
            //             . '<td style="padding:8px;border:1px solid #ccc;">'.$genericName.'</td>'
            //             . '<td style="padding:8px;border:1px solid #ccc;">'.$brandName.'</td>'
            //             . '<td style="padding:8px;border:1px solid #ccc;">'.$batchNo.'</td>'
            //             . '<td style="padding:8px;border:1px solid #ccc;">'.$formattedExpiry.' '.$expiredText.'</td>'
            //             . '<td style="padding:8px;border:1px solid #ccc;">'.$qty.'</td>'
            //         . '</tr>';
            //     }

            //     $html .= '</tbody></table>';

            //     return $html;
            // })
            ->addColumn('item_details', function ($row) {
                // dd($row);
                // --- helpers ---
                $csv = function ($v) {
                    // keep indexes aligned; don't drop empties
                    $arr = explode(',', (string)$v);
                    return array_map(static function ($x) {
                        return trim($x);
                    }, $arr);
                };

                // --- parse CSVs (no array_filter here) ---
                $brandIds    = $csv($row->brand_id);
                $genericIds  = $csv($row->inv_generic_id);
                $batchArray  = $csv($row->batch_no);
                $expiryArray = $csv($row->expiry_date);
                $qtyArray    = $csv($row->transaction_qty);

                // --- caches for names across rows ---
                static $brandCache = [];
                static $genericCache = [];
                static $locationNameCache = [];

                // collect missing ids to fill caches
                $needBrandIds = array_values(array_diff(array_unique(array_filter($brandIds, fn($v)=>$v!=='')), array_keys($brandCache)));
                $needGenericIds = array_values(array_diff(array_unique(array_filter($genericIds, fn($v)=>$v!=='')), array_keys($genericCache)));

                if (!empty($needBrandIds)) {
                    InventoryBrand::whereIn('id', $needBrandIds)
                        ->pluck('name', 'id')
                        ->each(function ($name, $id) use (&$brandCache) {
                            $brandCache[$id] = $name;
                        });
                }
                if (!empty($needGenericIds)) {
                    InventoryGeneric::whereIn('id', $needGenericIds)
                        ->pluck('name', 'id')
                        ->each(function ($name, $id) use (&$genericCache) {
                            $genericCache[$id] = $name;
                        });
                }

                // --- build tuples present in this row (by index) ---
                $count = max(count($brandIds), count($genericIds), count($batchArray), count($expiryArray), count($qtyArray));

                $tuples = []; // key g|b|ba => ['g'=>int,'b'=>int,'ba'=>string]
                for ($i = 0; $i < $count; $i++) {
                    $g  = $genericIds[$i] ?? '';
                    $b  = $brandIds[$i]   ?? '';
                    $ba = $batchArray[$i] ?? '';

                    // require non-empty triplet to ask balances
                    if ($g !== '' && $b !== '' && $ba !== '') {
                        $g = (int)$g;
                        $b = (int)$b;
                        $tuples["{$g}|{$b}|{$ba}"] = ['g'=>$g,'b'=>$b,'ba'=>$ba];
                    }
                }

                // bulk-balance fetch (only if there are any tuples)
                $latestAnySiteMap = [];
                $latestThisSiteMap = [];
                $latestLocPerTuple = [];
                if (!empty($tuples)) {
                    $tupleG  = array_unique(array_column($tuples, 'g'));
                    $tupleB  = array_unique(array_column($tuples, 'b'));
                    $tupleBA = array_unique(array_column($tuples, 'ba'));

                    // latest overall per tuple → org_balance
                    $allBalances = InventoryBalance::whereIn('generic_id', $tupleG)
                        ->whereIn('brand_id', $tupleB)
                        ->whereIn('batch_no', $tupleBA)
                        ->where('org_id', $row->org_id)
                        ->orderBy('id', 'desc')
                        ->get();

                    foreach ($allBalances as $bal) {
                        $key = "{$bal->generic_id}|{$bal->brand_id}|{$bal->batch_no}";
                        if (!isset($latestAnySiteMap[$key])) {
                            $latestAnySiteMap[$key] = $bal; // first is latest
                        }
                        if ((int)$bal->site_id === (int)$row->site_id && !isset($latestThisSiteMap[$key])) {
                            $latestThisSiteMap[$key] = $bal;
                        }
                    }

                    // latest per location for current site (>0 later)
                    $locBalances = InventoryBalance::whereIn('generic_id', $tupleG)
                        ->whereIn('brand_id', $tupleB)
                        ->whereIn('batch_no', $tupleBA)
                        ->where('org_id', $row->org_id)
                        ->where('site_id', $row->site_id)
                        ->whereNotNull('location_id')
                        ->orderBy('id', 'desc')
                        ->get();

                    $allLocIds = [];
                    foreach ($locBalances->groupBy(fn($r)=>"{$r->generic_id}|{$r->brand_id}|{$r->batch_no}") as $tupleKey => $group) {
                        $byLoc = [];
                        foreach ($group as $r) {
                            $locId = (int)$r->location_id;
                            if (!isset($byLoc[$locId])) {
                                $byLoc[$locId] = $r; // latest by desc id
                                $allLocIds[$locId] = true;
                            }
                        }
                        $latestLocPerTuple[$tupleKey] = $byLoc;
                    }

                    $missLocIds = array_diff(array_keys($allLocIds), array_keys($locationNameCache));
                    if (!empty($missLocIds)) {
                        DB::table('service_location')
                            ->whereIn('id', $missLocIds)
                            ->pluck('name', 'id')
                            ->each(function ($name, $id) use (&$locationNameCache) {
                                $locationNameCache[(int)$id] = $name ?: 'Unknown';
                            });
                    }
                }

                // --- render table ---
                $html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
                    . '<thead style="background-color:#e2e8f0;color:#000;">'
                    .   '<tr>'
                    .     '<th style="padding:8px;border:1px solid #ccc;text-align:left;">S.No</th>'
                    .     '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>'
                    .     '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Brand</th>'
                    .     '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Batch No</th>'
                    .     '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Expiry Date</th>'
                    .     '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Quantity</th>'
                    .   '</tr>'
                    . '</thead><tbody>';

                $printed = false;

                for ($i = 0; $i < $count; $i++) {
                    $brandId   = $brandIds[$i]   ?? '';
                    $genericId = $genericIds[$i] ?? '';

                    $batchNo = $batchArray[$i]   ?? '';
                    $expiry  = $expiryArray[$i]  ?? '';
                    $qty     = $qtyArray[$i]     ?? '';

                    // skip only if the whole row is empty
                    if ($brandId === '' && $genericId === '' && $batchNo === '' && $expiry === '' && $qty === '') {
                        continue;
                    }

                    $brandName   = ($brandId !== '' && isset($brandCache[$brandId])) ? $brandCache[$brandId] : 'N/A';
                    $genericName = ($genericId !== '' && isset($genericCache[$genericId])) ? $genericCache[$genericId] : 'N/A';

                    // balances only if tuple is valid
                    $orgBal = 0; $siteBal = 0; $locBalancesOut = [];
                    if ($brandId !== '' && $genericId !== '' && $batchNo !== '') {
                        $tupleKey = ((int)$genericId) . '|' . ((int)$brandId) . '|' . $batchNo;

                        if (isset($latestAnySiteMap[$tupleKey])) {
                            $orgBal = (int)($latestAnySiteMap[$tupleKey]->org_balance ?? 0);
                        }
                        if (isset($latestThisSiteMap[$tupleKey])) {
                            $siteBal = (int)($latestThisSiteMap[$tupleKey]->site_balance ?? 0);
                        }
                        if (isset($latestLocPerTuple[$tupleKey])) {
                            foreach ($latestLocPerTuple[$tupleKey] as $locId => $rec) {
                                $lb = (int)($rec->location_balance ?? 0);
                                if ($lb > 0) {
                                    $locName = $locationNameCache[$locId] ?? 'Unknown';
                                    $locBalancesOut[] = $locName . ': ' . $lb;
                                }
                            }
                        }
                    }

                    $locationJson = htmlspecialchars(json_encode(array_values($locBalancesOut)), ENT_QUOTES, 'UTF-8');

                    $formattedExpiry = (is_numeric($expiry) && $expiry !== '')
                        ? \Carbon\Carbon::createFromTimestamp((int)$expiry)->format('d-M-Y')
                        : 'N/A';

                    $expiredText = '';
                    if (is_numeric($expiry) && $expiry !== '' &&
                        \Carbon\Carbon::createFromTimestamp((int)$expiry)->setTimezone('Asia/Karachi')->isPast()) {
                        $expiredText = '<br><span style="color: red; font-size: 12px;">Expired</span>';
                    }

                    $sno = $i + 1;
                    $bg  = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';

                    $html .= '<tr class="balance-row"'
                        .      ' data-org-balance="'.$orgBal.'"'
                        .      ' data-site-balance="'.$siteBal.'"'
                        .      ' data-loc-balance="'.$locationJson.'"'
                        .      ' style="background-color:'.$bg.';cursor:pointer;">'
                        .   '<td style="padding:8px;border:1px solid #ccc;">'.$sno.'</td>'
                        .   '<td style="padding:8px;border:1px solid #ccc;">'.e($genericName).'</td>'
                        .   '<td style="padding:8px;border:1px solid #ccc;">'.e($brandName).'</td>'
                        .   '<td style="padding:8px;border:1px solid #ccc;">'.e($batchNo === '' ? 'N/A' : $batchNo).'</td>'
                        .   '<td style="padding:8px;border:1px solid #ccc;">'.e($formattedExpiry).' '.$expiredText.'</td>'
                        .   '<td style="padding:8px;border:1px solid #ccc;">'.e($qty === '' ? 'N/A' : $qty).'</td>'
                        . '</tr>';

                    $printed = true;
                }

                if (!$printed) {
                    $html .= '<tr><td colspan="6" style="padding:10px;border:1px solid #ccc;text-align:center;color:#6b7280;">No items.</td></tr>';
                }

                $html .= '</tbody></table>';
                return $html;
            })
            ->rawColumns(['transaction_details', 'item_details'])
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

        // $costcenters = DB::table('emp_cc as e')
        // ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
        // ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
        // ->where('e.emp_id', $empId)
        // ->where('ct.performing', 1)
        // ->select('c.id', 'c.name')
        // ->get();

        $costcenters = DB::table(function ($query) use ($empId) {
            $query->select('c.id', 'c.name')
                ->from('emp_cc as e')
                ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
                ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                ->where('e.emp_id', $empId)
                ->where('ct.performing', 1)

            ->unionAll(
                DB::table('employee as emp')
                    ->select('c.id', 'c.name')
                    ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, emp.cc_id)'), '>', DB::raw('0'))
                    ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                    ->where('emp.id', $empId)
                    ->where('ct.performing', 1)
            );
        }, 'combined')
        ->distinct()
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


        // Common field definitions
        $sharedFields = ['id', 'transaction_type_id', 'status', 'org_id', 'site_id', 'effective_timestamp', 'timestamp', 'last_updated', 'logid'];
        
        $commonJoinFields = [
            'patient.name as patientName',
            'employee.name as Physician', 
            'organization.organization as OrgName',
            'org_site.name as SiteName',
            'services.name as serviceName',
            'service_mode.name as serviceMode',
            'billingCC.name as billingCC',
            'service_group.name as serviceGroup',
            'service_type.name as serviceType',
            'inventory_transaction_type.name as TransactionType'
        ];

        // Session site filtering helper
        $applySessionFilter = function($query) {
            if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
                $sessionSiteIds = $this->assignedSites;
                if(!empty($sessionSiteIds)) {
                    $query->whereIn('org_site.id', $sessionSiteIds);
                }
            }
            return $query;
        };

        // --- Medication Query ---
        $medication = DB::table('req_medication_consumption as rmc')
            ->join('costcenter as billingCC', 'billingCC.id', '=', 'rmc.billing_cc')
            ->join('employee', 'employee.id', '=', 'rmc.responsible_physician')
            ->join('service_mode', 'service_mode.id', '=', 'rmc.service_mode_id')
            ->join('services', 'services.id', '=', 'rmc.service_id')
            ->join('organization', 'organization.id', '=', 'rmc.org_id')
            ->join('org_site', 'org_site.id', '=', 'rmc.site_id')
            ->join('service_group', 'service_group.id', '=', 'rmc.service_group_id')
            ->join('service_type', 'service_type.id', '=', 'rmc.service_type_id')
            ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'rmc.transaction_type_id')
            ->leftJoin('service_location as source_location', 'source_location.id', '=', 'rmc.source_location_id')
            ->leftJoin('service_location as destination_location', 'destination_location.id', '=', 'rmc.destination_location_id')
            ->join('patient', 'patient.mr_code', '=', 'rmc.mr_code')
            ->select([
                'rmc.id',
                'rmc.transaction_type_id',
                'rmc.status',
                'rmc.org_id',
                'rmc.site_id',
                'rmc.effective_timestamp',
                'rmc.timestamp',
                'rmc.last_updated',
                'rmc.logid',
                'patient.name as patientName',
                'employee.name as Physician',
                'organization.organization as OrgName',
                'org_site.name as SiteName',
                'services.name as serviceName',
                'service_mode.name as serviceMode',
                'billingCC.name as billingCC',
                'service_group.name as serviceGroup',
                'service_type.name as serviceType',
                'inventory_transaction_type.name as TransactionType',
                DB::raw('COALESCE(source_location.name, "") as SourceLocationName'),
                DB::raw('COALESCE(destination_location.name, "") as DestinationLocationName'),
                'rmc.code as referenceNumber',
                'rmc.mr_code',
                'rmc.dose',
                'rmc.route_ids',
                'rmc.frequency_ids',
                'rmc.days',
                'rmc.inv_generic_ids',
                'rmc.remarks',
                DB::raw("'medication' as source")
            ]);

        $medication = $applySessionFilter($medication);

        // --- Material Query ---
        $material = DB::table('material_consumption_requisition as mcr')
            ->leftJoin('patient', 'patient.mr_code', '=', 'mcr.mr_code')
            ->leftJoin('employee', 'employee.id', '=', 'mcr.physician_id')
            ->leftJoin('service_mode', 'service_mode.id', '=', 'mcr.service_mode_id')
            ->leftJoin('services', 'services.id', '=', 'mcr.service_id')
            ->leftJoin('service_group', 'service_group.id', '=', 'services.group_id')
            ->leftJoin('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->leftJoin('costcenter as billingCC', 'billingCC.id', '=', 'mcr.billing_cc')
            ->leftJoin('organization', 'organization.id', '=', 'mcr.org_id')
            ->leftJoin('org_site', 'org_site.id', '=', 'mcr.site_id')
            ->leftJoin('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'mcr.transaction_type_id')
            ->leftJoin('service_location as source_location', 'source_location.id', '=', 'mcr.source_location_id')
            ->leftJoin('service_location as destination_location', 'destination_location.id', '=', 'mcr.destination_location_id')
            ->select([
                'mcr.id',
                'mcr.transaction_type_id',
                'mcr.status',
                'mcr.org_id',
                'mcr.site_id',
                'mcr.effective_timestamp',
                'mcr.timestamp',
                'mcr.last_updated',
                'mcr.logid',
                'patient.name as patientName',
                'employee.name as Physician',
                'organization.organization as OrgName',
                'org_site.name as SiteName',
                'services.name as serviceName',
                'service_mode.name as serviceMode',
                'billingCC.name as billingCC',
                'service_group.name as serviceGroup',
                'service_type.name as serviceType',
                'inventory_transaction_type.name as TransactionType',
                DB::raw('COALESCE(source_location.name, "") as SourceLocationName'),
                DB::raw('COALESCE(destination_location.name, "") as DestinationLocationName'),
                'mcr.code as referenceNumber',
                'mcr.mr_code',
                DB::raw("'' as dose"),
                DB::raw("'' as route_ids"),
                DB::raw("'' as frequency_ids"),
                DB::raw("'' as days"),
                'mcr.generic_id as inv_generic_ids',
                'mcr.remarks',
                DB::raw("'material' as source")
            ]);

        $material = $applySessionFilter($material);

        // --- Direct Issue/Dispense Query ---
        $directIssueDispense = DB::table('inventory_management as im')
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
            ->select([
                'im.id',
                'im.transaction_type_id',
                'im.status',
                'im.org_id',
                'im.site_id',
                'im.effective_timestamp',
                'im.timestamp',
                'im.last_updated',
                'im.logid',
                'patient.name as patientName',
                'employee.name as Physician',
                'organization.organization as OrgName',
                'org_site.name as SiteName',
                'services.name as serviceName',
                'service_mode.name as serviceMode',
                'billingCC.name as billingCC',
                'service_group.name as serviceGroup',
                'service_type.name as serviceType',
                'inventory_transaction_type.name as TransactionType',
                DB::raw("'' as SourceLocationName"),
                DB::raw("'' as DestinationLocationName"),
                'im.ref_document_no as referenceNumber',
                'im.mr_code',
                'im.dose',
                'im.route_id as route_ids',
                'im.frequency_id as frequency_ids',
                'im.duration as days',
                'im.inv_generic_id as inv_generic_ids',
                'im.remarks',
                DB::raw("'inventory' as source")
            ]);

        $directIssueDispense = $applySessionFilter($directIssueDispense);

        // Create UNION query for server-side processing
        // $unionQuery = $medication
        //     ->unionAll($material)
        //     ->unionAll($directIssueDispense)
        //     ->orderBy('timestamp', 'desc');
        $unionBase = $medication->unionAll($material)->unionAll($directIssueDispense);
        $unionQuery = DB::query()->fromSub($unionBase, 'u'); // alias "u" used in filters below

        return DataTables::query($unionQuery)
            // ->mergeBindings($combined))
            ->filter(function ($query) use ($request) {
                $from = $request->input('from_date');
                $to   = $request->input('to_date'); 
                $orgIds  = (array) $request->input('org_ids', []);
                $siteIds = (array) $request->input('site_ids', []);
                $ttIds   = (array) $request->input('transaction_type_ids', []);
                $sources = (array) $request->input('sources', []); 

                $statusEq = $request->input('status'); 

                $ref      = trim((string) $request->input('reference'));
                $mr       = trim((string) $request->input('mr_code'));
                $patient  = trim((string) $request->input('patient'));
                $phys     = trim((string) $request->input('physician'));
                $service  = trim((string) $request->input('service'));
                $svcMode  = trim((string) $request->input('service_mode'));
                $svcGroup = trim((string) $request->input('service_group'));
                $billing  = trim((string) $request->input('billing_cc'));
                $siteName = trim((string) $request->input('site_name'));
                $srcLoc   = trim((string) $request->input('source_location'));
                $dstLoc   = trim((string) $request->input('destination_location'));
                $remarks  = trim((string) $request->input('remarks'));
                $generic  = trim((string) $request->input('generic_name')); // optional dedicated generic filter

                if ($from) {
                    $query->where('u.timestamp', '>=', \Carbon\Carbon::parse($from)->startOfDay()->timestamp);
                }
                if ($to) {
                    $query->where('u.timestamp', '<=', \Carbon\Carbon::parse($to)->endOfDay()->timestamp);
                }
                if (!empty($orgIds))  { $query->whereIn('u.org_id', $orgIds); }
                if (!empty($siteIds)) { $query->whereIn('u.site_id', $siteIds); }
                if (!empty($ttIds))   { $query->whereIn('u.transaction_type_id', $ttIds); }
                if (!empty($sources)) { $query->whereIn('u.source', $sources); }
                if ($statusEq !== null && $statusEq !== '') {
                    $query->where('u.status', $statusEq);
                }

                if ($ref)      { $query->where('u.referenceNumber', 'like', "%{$ref}%"); }
                if ($mr)       { $query->where('u.mr_code', 'like', "%{$mr}%"); }
                if ($patient)  { $query->where('u.patientName', 'like', "%{$patient}%"); }
                if ($phys)     { $query->where('u.Physician', 'like', "%{$phys}%"); }
                if ($service)  { $query->where('u.serviceName', 'like', "%{$service}%"); }
                if ($svcMode)  { $query->where('u.serviceMode', 'like', "%{$svcMode}%"); }
                if ($svcGroup) { $query->where('u.serviceGroup', 'like', "%{$svcGroup}%"); }
                if ($billing)  { $query->where('u.billingCC', 'like', "%{$billing}%"); }
                if ($siteName) { $query->where('u.SiteName', 'like', "%{$siteName}%"); }
                if ($srcLoc)   { $query->where('u.SourceLocationName', 'like', "%{$srcLoc}%"); }
                if ($dstLoc)   { $query->where('u.DestinationLocationName', 'like', "%{$dstLoc}%"); }
                if ($remarks)  { $query->where('u.remarks', 'like', "%{$remarks}%"); }

                // Optional dedicated filter by Generic name (matches any ID inside inv_generic_ids)
                if ($generic) {
                    $query->where(function ($qq) use ($generic) {
                        $qq->whereRaw("
                            EXISTS (
                            SELECT 1
                            FROM inventory_generic ig
                            WHERE FIND_IN_SET(ig.id, u.inv_generic_ids)
                                AND ig.name LIKE ?
                            )", ["%{$generic}%"]
                        );
                    });
                }

                // -------- DataTables global search (search[value]) --------
                $global = trim((string) data_get($request->input('search'), 'value'));
                if ($global !== '') {
                    $g = "%{$global}%";
                    $query->where(function ($qq) use ($g) {
                        $qq->orWhere('u.referenceNumber', 'like', $g)
                        ->orWhere('u.patientName', 'like', $g)
                        ->orWhere('u.Physician', 'like', $g)
                        ->orWhere('u.OrgName', 'like', $g)
                        ->orWhere('u.SiteName', 'like', $g)
                        ->orWhere('u.serviceName', 'like', $g)
                        ->orWhere('u.serviceMode', 'like', $g)
                        ->orWhere('u.billingCC', 'like', $g)
                        ->orWhere('u.serviceGroup', 'like', $g)
                        ->orWhere('u.serviceType', 'like', $g)
                        ->orWhere('u.TransactionType', 'like', $g)
                        ->orWhere('u.SourceLocationName', 'like', $g)
                        ->orWhere('u.DestinationLocationName', 'like', $g)
                        ->orWhere('u.mr_code', 'like', $g)
                        ->orWhere('u.remarks', 'like', $g)
                        // Search by generic name too
                        ->orWhereRaw("
                                EXISTS (
                                SELECT 1 FROM inventory_generic ig
                                WHERE FIND_IN_SET(ig.id, u.inv_generic_ids)
                                    AND ig.name LIKE ?
                                )", [$g]
                            );
                    });
                }

                // -------- Column-specific search (DataTables columns[x][search][value]) --------
                // Map client-side column names → fields here if you’re sending column-specific search.
                $columns = (array) $request->input('columns', []);
                foreach ($columns as $col) {
                    $name  = data_get($col, 'data');           // expects your DataTables "data" key
                    $value = trim((string) data_get($col, 'search.value'));
                    if ($value === '') continue;

                    switch ($name) {
                        case 'referenceNumber':   $query->where('u.referenceNumber', 'like', "%{$value}%"); break;
                        case 'mr_code':           $query->where('u.mr_code', 'like', "%{$value}%"); break;
                        case 'patientName':       $query->where('u.patientName', 'like', "%{$value}%"); break;
                        case 'Physician':         $query->where('u.Physician', 'like', "%{$value}%"); break;
                        case 'SiteName':          $query->where('u.SiteName', 'like', "%{$value}%"); break;
                        case 'OrgName':           $query->where('u.OrgName', 'like', "%{$value}%"); break;
                        case 'serviceName':       $query->where('u.serviceName', 'like', "%{$value}%"); break;
                        case 'serviceMode':       $query->where('u.serviceMode', 'like', "%{$value}%"); break;
                        case 'serviceGroup':      $query->where('u.serviceGroup', 'like', "%{$value}%"); break;
                        case 'serviceType':       $query->where('u.serviceType', 'like', "%{$value}%"); break;
                        case 'TransactionType':   $query->where('u.TransactionType', 'like', "%{$value}%"); break;
                        case 'SourceLocationName':$query->where('u.SourceLocationName', 'like', "%{$value}%"); break;
                        case 'DestinationLocationName':
                                                $query->where('u.DestinationLocationName', 'like', "%{$value}%"); break;
                        case 'remarks':           $query->where('u.remarks', 'like', "%{$value}%"); break;
                        case 'source':            $query->where('u.source', $value); break;
                        case 'status':            $query->where('u.status', $value); break;
                        case 'transaction_type_id':
                                                $query->where('u.transaction_type_id', $value); break;
                        // Generic name column (if you add one in client):
                        case 'generic':
                            $g = "%{$value}%";
                            $query->whereRaw("
                                EXISTS (
                                SELECT 1 FROM inventory_generic ig
                                WHERE FIND_IN_SET(ig.id, u.inv_generic_ids)
                                    AND ig.name LIKE ?
                                )", [$g]
                            );
                            break;
                        default:
                            // ignore unmapped columns
                            break;
                    }
                }
            })
            ->addColumn('id_raw', fn($row) => $row->id)
            ->editColumn('id', function ($row) {
                // Optimize date formatting with single Carbon instance
                $carbon = Carbon::class;
                $timestamp = $carbon::createFromTimestamp($row->timestamp)->format('l d F Y - h:i A');
                $effectiveDate = $carbon::createFromTimestamp($row->effective_timestamp)->format('l d F Y - h:i A');

                // Build location info more efficiently
                $locationParts = [];
                if (!empty($row->SourceLocationName)) {
                    $locationParts[] = '<br><b>Source Location</b>: ' . ucwords($row->SourceLocationName);
                }
                if (!empty($row->DestinationLocationName)) {
                    $locationParts[] = '<br><b>Destination Location</b>: ' . ucwords($row->DestinationLocationName);
                }
                $locationInfo = implode('', $locationParts);

                // Use sprintf for better performance and readability
                return sprintf(
                    '%s<hr class="mt-1 mb-2"><b>Request For</b>: %s%s<br><b>Site</b>: %s<br><b>Request Date </b>: %s<br><b>Effective Date </b>: %s<br><b>Remarks</b>: %s',
                    $row->referenceNumber ?? 'N/A',
                    $row->TransactionType ?? 'N/A',
                    $locationInfo,
                    $row->SiteName ?? 'N/A',
                    $timestamp,
                    $effectiveDate,
                    $row->remarks ?: 'N/A'
                );
            })
            ->editColumn('patientDetails', function ($row) {
                if (empty($row->mr_code)) return 'N/A';

                // Use sprintf for better performance
                return sprintf(
                    '<b>MR#:</b> %s<br>%s<hr class="mt-1 mb-2"><b>Service Mode</b>: %s<br><b>Service Group</b>: %s<br><b>Service</b>: %s<br><b>Responsible Physician</b>: %s<br><b>Billing CC</b>: %s',
                    $row->mr_code,
                    ucwords($row->patientName),
                    $row->serviceMode,
                    $row->serviceGroup,
                    $row->serviceName,
                    ucwords($row->Physician),
                    $row->billingCC
                );
            })
            ->editColumn('InventoryDetails', function ($row) {
                // return '';
                static $rightsRespond = null;

                static $genericNameCache = [];         // [generic_id => name]
                static $routeNameCache = [];           // [route_id => name]
                static $frequencyNameCache = [];       // [frequency_id => name]
                static $brandNameCache = [];           // [brand_id => name]
                static $locationNameCache = [];        // [location_id => name]

                static $respondedByRefCache = [];      // [ref_document_no => [inv_generic_id => total_qty]]
                static $imRefMapCache = [];            // [ref_document_no => [generic_id => ['brand_id'=>..,'batch_no'=>..,'expiry_date'=>..]]]
                static $imFirstByGenericCache = [];    // [generic_id => ['brand_id'=>..,'batch_no'=>..]] for direct entries

                static $balanceCache = [];             // ["org_site|org|site|generic|brand|batch" => ['orgBalance'=>..,'siteBalance'=>..,'locBalance'=>json]]

                // ---- Rights: respond permission ----
                if ($rightsRespond === null) {
                    $Rights = $this->rights;
                    $rightsRespond = (int) explode(',', $Rights->issue_and_dispense)[2];
                }

                // ---- Small helpers (memoized fetchers) ----
                $getBrandName = function ($brandId) use (&$brandNameCache) {
                    if (!$brandId) return '';
                    if (!array_key_exists($brandId, $brandNameCache)) {
                        $brandNameCache[$brandId] = (string) (DB::table('inventory_brand')->where('id', $brandId)->value('name') ?? '');
                    }
                    return $brandNameCache[$brandId];
                };

                $getLocationName = function ($locId) use (&$locationNameCache) {
                    if (!$locId) return 'Unknown';
                    if (!array_key_exists($locId, $locationNameCache)) {
                        $locationNameCache[$locId] = (string) (DB::table('service_location')->where('id', $locId)->value('name') ?? 'Unknown');
                    }
                    return $locationNameCache[$locId];
                };

                $getRespondedMap = function ($refNo) use (&$respondedByRefCache) {
                    if (empty($refNo)) return [];
                    if (!array_key_exists($refNo, $respondedByRefCache)) {
                        $respondedByRefCache[$refNo] = DB::table('inventory_management as im')
                            ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
                            ->join('inventory_transaction_activity as ita', 'ita.id', '=', 'itt.activity_type')
                            ->where('im.ref_document_no', $refNo)
                            ->where(function ($q) {
                                $q->where('ita.name', 'like', '%issue%')
                                ->orWhere('ita.name', 'like', '%dispense%');
                            })
                            ->groupBy('im.inv_generic_id')
                            ->select('im.inv_generic_id', DB::raw('SUM(im.transaction_qty) as total_qty'))
                            ->pluck('total_qty', 'im.inv_generic_id')
                            ->toArray();
                    }
                    return $respondedByRefCache[$refNo];
                };

                $getIMRefMap = function ($refNo) use (&$imRefMapCache) {
                    if (empty($refNo)) return [];
                    if (!array_key_exists($refNo, $imRefMapCache)) {
                        $rows = DB::table('inventory_management')
                            ->where('ref_document_no', $refNo)
                            ->select('inv_generic_id', 'brand_id', 'batch_no', 'expiry_date')
                            ->get();
                        $map = [];
                        foreach ($rows as $r) {
                            $map[(int)$r->inv_generic_id] = [
                                'brand_id'    => $r->brand_id,
                                'batch_no'    => $r->batch_no,
                                'expiry_date' => $r->expiry_date,
                            ];
                        }
                        $imRefMapCache[$refNo] = $map;
                    }
                    return $imRefMapCache[$refNo];
                };

                $getIMFirstByGeneric = function ($genericId) use (&$imFirstByGenericCache) {
                    $gid = (int) $genericId;
                    if ($gid <= 0) return null;
                    if (!array_key_exists($gid, $imFirstByGenericCache)) {
                        $r = DB::table('inventory_management')
                            ->where('inv_generic_id', $gid)
                            ->select('brand_id', 'batch_no')
                            ->first();
                        $imFirstByGenericCache[$gid] = $r ? ['brand_id' => $r->brand_id, 'batch_no' => $r->batch_no] : null;
                    }
                    return $imFirstByGenericCache[$gid];
                };

                $getBalances = function ($orgId, $siteId, $genericId, $brandId, $batchNo, $preferredLocationName = null) use (&$balanceCache, $getLocationName) {
                    $key = "{$orgId}|{$siteId}|{$genericId}|{$brandId}|{$batchNo}|".mb_strtolower(trim((string)$preferredLocationName));
                    if (isset($balanceCache[$key])) {
                        return $balanceCache[$key];
                    }

                    $baseQuery = DB::table('inventory_balance')
                        ->where('org_id', $orgId)
                        ->where('generic_id', $genericId)
                        ->where('brand_id', $brandId)
                        ->where('batch_no', $batchNo)
                        ->orderBy('id', 'desc');

                    // 1) Org/Site balance: try NULL location_id; if absent, fall back to latest ANY row
                    $rowOrgSite = (clone $baseQuery)->whereNull('location_id')->first();
                    if (!$rowOrgSite) {
                        $rowOrgSite = (clone $baseQuery)->first();
                    }

                    $orgBal  = $rowOrgSite->org_balance  ?? 'N/A';
                    $siteBal = $rowOrgSite->site_balance ?? 'N/A';

                    // 2) Location balance: only the preferred location (by NAME), if provided
                    $locJson = '[]';
                    if ($preferredLocationName) {
                        $needle = mb_strtolower(trim($preferredLocationName));
                        $locRows = (clone $baseQuery)->whereNotNull('location_id')->get(); // desc by id

                        // pick latest row PER location_id, then match by name and require > 0
                        $seen = [];
                        foreach ($locRows as $r) {
                            $lid = (int) $r->location_id;
                            if (isset($seen[$lid])) continue;        // already took the latest for this loc
                            $seen[$lid] = true;

                            $locName = $getLocationName($lid);
                            if (mb_strtolower(trim($locName)) === $needle && ($r->location_balance ?? 0) > 0) {
                                $locJson = htmlspecialchars(json_encode([$locName . ': ' . ($r->location_balance ?? 0)]), ENT_QUOTES, 'UTF-8');
                                break;
                            }
                        }
                    }

                    return $balanceCache[$key] = [
                        'orgBalance' => $orgBal ?? 'N/A',
                        'siteBalance' => $siteBal ?? 'N/A',
                        'locBalance'  => $locJson, // JSON array with just the matched location (or empty [])
                    ];
                };

                // ---- Common prework per row ----
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
                }

                $isIssue = Str::contains(strtolower($row->TransactionType), 'issue');

                // Always need generic names (batch fetch + cache)
                $genericIds = array_filter(array_map('intval', explode(',', (string) $row->inv_generic_ids)));
                $missingGenericIds = array_values(array_diff($genericIds, array_keys($genericNameCache)));
                if ($missingGenericIds) {
                    $fetched = InventoryGeneric::whereIn('id', $missingGenericIds)->pluck('name', 'id')->toArray();
                    foreach ($fetched as $gid => $name) {
                        $genericNameCache[(int)$gid] = $name;
                    }
                    // Fill any truly missing ones with 'N/A' to avoid repeat queries
                    foreach ($missingGenericIds as $gid) {
                        if (!isset($genericNameCache[$gid])) $genericNameCache[$gid] = 'N/A';
                    }
                }

                // Medication-only dictionaries (routes/frequencies) — batch fetch + cache
                $routeIds = [];
                $frequencyIds = [];
                if (!empty($row->route_ids))     $routeIds = array_filter(array_map('intval', explode(',', (string) $row->route_ids)));
                if (!empty($row->frequency_ids)) $frequencyIds = array_filter(array_map('intval', explode(',', (string) $row->frequency_ids)));

                $missingRouteIds = array_values(array_diff($routeIds, array_keys($routeNameCache)));
                if ($missingRouteIds) {
                    $fetched = MedicationRoutes::whereIn('id', $missingRouteIds)->pluck('name', 'id')->toArray();
                    foreach ($fetched as $id => $name) $routeNameCache[(int)$id] = $name;
                    foreach ($missingRouteIds as $id) if (!isset($routeNameCache[$id])) $routeNameCache[$id] = 'N/A';
                }

                $missingFreqIds = array_values(array_diff($frequencyIds, array_keys($frequencyNameCache)));
                if ($missingFreqIds) {
                    $fetched = MedicationFrequency::whereIn('id', $missingFreqIds)->pluck('name', 'id')->toArray();
                    foreach ($fetched as $id => $name) $frequencyNameCache[(int)$id] = $name;
                    foreach ($missingFreqIds as $id) if (!isset($frequencyNameCache[$id])) $frequencyNameCache[$id] = 'N/A';
                }

                // Shared pieces for table building
                $tableRows = '';

                // -----------------------------------------------
                // MEDICATION BRANCH
                // -----------------------------------------------
                if ($row->source === 'medication') {
                    $dose        = !empty($row->dose)        ? explode(',', (string) $row->dose)        : [];
                    $routeIdsArr = !empty($row->route_ids)   ? array_map('intval', explode(',', (string) $row->route_ids))   : [];
                    $freqIdsArr  = !empty($row->frequency_ids)? array_map('intval', explode(',', (string) $row->frequency_ids)) : [];
                    $days        = !empty($row->days)        ? explode(',', (string) $row->days)        : [];

                    // Responded entries (per referenceNumber) — cached
                    $respondedEntries = [];
                    if (!empty($row->referenceNumber)) {
                        $respondedEntries = $getRespondedMap($row->referenceNumber);
                    }

                    $count = max(count($genericIds), count($dose), count($routeIdsArr), count($freqIdsArr), count($days));

                    // If we will need brand/batch/expiry by reference, fetch once
                    $refMap = [];
                    if (!$isDirectEntry && !empty($row->referenceNumber)) {
                        $refMap = $getIMRefMap($row->referenceNumber); // [generic_id => [brand_id,batch_no,expiry_date]]
                    }

                    for ($i = 0; $i < $count; $i++) {
                        $bg = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';

                        $currentGenericId = (int) ($genericIds[$i] ?? 0);
                        $isResponded = array_key_exists($currentGenericId, $respondedEntries);

                        // Balances
                        $balances = ['orgBalance' => 'N/A', 'siteBalance' => 'N/A', 'locBalance' => 'N/A'];
                        if ($isDirectEntry) {
                            $balanceInfo = $getIMFirstByGeneric($currentGenericId);
                            if ($balanceInfo) {
                                $b = $getBalances(
                                    $row->org_id, $row->site_id, $currentGenericId,
                                    $balanceInfo['brand_id'], $balanceInfo['batch_no'],
                                    $row->SourceLocationName ?: $row->DestinationLocationName
                                );
                                // $b = $getBalances($row->org_id, $row->site_id, $currentGenericId, $balanceInfo['brand_id'], $balanceInfo['batch_no']);
                                $balances = $b;
                            }
                        } elseif (!empty($row->referenceNumber) && $isResponded) {
                            $entry = $refMap[$currentGenericId] ?? null;
                            if ($entry) {
                                $b = $getBalances(
                                    $row->org_id, $row->site_id, $currentGenericId,
                                    $entry['brand_id'], $entry['batch_no'],
                                    $row->SourceLocationName ?: $row->DestinationLocationName
                                );
                                // $b = $getBalances($row->org_id, $row->site_id, $currentGenericId, $entry['brand_id'], $entry['batch_no']);
                                $balances = $b;
                            }
                        }

                        // Status / Action logic (unchanged)
                        if ($isDirectEntry || $isResponded) {
                            if ($isIssue) { $status = 'Issued'; $statusClass = 'megna'; }
                            else          { $status = 'Dispensed'; $statusClass = 'success'; }
                            $actionBtn = 'N/A';
                        } else {
                            $status = 'Pending';
                            $statusClass = 'warning';
                            $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn" data-source="'. $row->source.'" data-id="'. $row->id.'" data-generic-id="' . $currentGenericId . '">Respond</a>';
                        }

                        // Brand/Batch/Expiry
                        $brandName = '';
                        $batchNo   = '';
                        $expiryDate = '';
                        if (!$isDirectEntry && !empty($row->referenceNumber)) {
                            $itemData = $refMap[$currentGenericId] ?? null;
                            if ($itemData) {
                                $brandName = $getBrandName($itemData['brand_id']);
                                $batchNo   = $itemData['batch_no'];
                                $expiryDate = is_numeric($itemData['expiry_date'])
                                    ? \Carbon\Carbon::createFromTimestamp($itemData['expiry_date'])->format('d-M-Y')
                                    : $itemData['expiry_date'];
                            }
                        } else {
                            // direct entry uses row fields (same as original)
                            $brandName = $row->brandName ?? '';
                            $batchNo   = $row->batch_no;
                            $expiryDate = is_numeric($row->expiry_date)
                                ? \Carbon\Carbon::createFromTimestamp($row->expiry_date)->format('d-M-Y')
                                : $row->expiry_date;
                        }

                        // Enforce rights
                        if ($rightsRespond != 1) {
                            $actionBtn = '<code>Unauthorized Access</code>';
                        }

                        $tableRows .= '<tr style="background-color:'.$bg.';cursor:pointer;" class="balance-row"'
                                    .' data-expiry="'.$expiryDate.'"'
                                    .' data-brand="'.$brandName.'"'
                                    .' data-batch="'.$batchNo.'"'
                                    .' data-loc-balance="'.$balances['locBalance'].'"'
                                    .' data-org-balance="'.$balances['orgBalance'].'"'
                                    .' data-site-balance="'.$balances['siteBalance'].'">';

                        $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.($genericNameCache[$currentGenericId] ?? 'N/A').'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;">'.($dose[$i] ?? 'N/A').'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;">'.($routeNameCache[$routeIdsArr[$i] ?? 0] ?? 'N/A').'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;">'.($frequencyNameCache[$freqIdsArr[$i] ?? 0] ?? 'N/A').'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;">'.($days[$i] ?? 'N/A').'</td>'
                                    . '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.($respondedEntries[$currentGenericId] ?? '0').'</td>'
                                    . '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$actionBtn.'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;"><span class="label label-'.$statusClass.'">'.$status.'</span></td>'
                                    . '</tr>';
                    }

                    // Header (unchanged)
                    $tableHeader = '<tr>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Dose</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Route</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Frequency</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Duration (Days)</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Transaction Qty</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Action</th>'
                        .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Status</th>'
                        .'</tr>';

                    return '<table style="width:100%;border-collapse:collapse;font-size:13px;" class="table table-bordered">'
                        .'<thead style="background-color:#e2e8f0;color:#000;">'.$tableHeader.'</thead>'
                        .'<tbody>'.$tableRows.'</tbody></table>';
                }

                // -----------------------------------------------
                // MATERIAL / INVENTORY BRANCH (non-medication)
                // -----------------------------------------------
                $demandQty       = !empty($row->demand_qty) ? array_map('floatval', explode(',', (string) $row->demand_qty)) : [];
                $transactionQty  = ($row->source === 'inventory' && !empty($row->transaction_qty))
                    ? array_map('floatval', explode(',', (string) $row->transaction_qty))
                    : null;

                // For partial/complete status we need responded per ref
                $respondedQtys = [];
                if (!empty($row->referenceNumber)) {
                    $respondedQtys = $getRespondedMap($row->referenceNumber);
                }

                $doseArr       = !empty($row->dose)         ? explode(',', (string) $row->dose) : [];
                $routeIdsArr   = !empty($row->route_ids)    ? array_map('intval', explode(',', (string) $row->route_ids)) : [];
                $freqIdsArr    = !empty($row->frequency_ids)? array_map('intval', explode(',', (string) $row->frequency_ids)) : [];
                $daysArr       = !empty($row->days)         ? explode(',', (string) $row->days) : [];

                $batchNoArr    = !empty($row->batch_no)     ? explode(',', (string) $row->batch_no) : [];
                $brandIdArr    = !empty($row->brand_id)     ? array_map('intval', explode(',', (string) $row->brand_id)) : [];
                $expiryArr     = !empty($row->expiry_date)  ? explode(',', (string) $row->expiry_date) : [];

                $showMedicationFields = (count($doseArr) > 0 || count($routeIdsArr) > 0 || count($freqIdsArr) > 0 || count($daysArr) > 0);
                $showDemandQty        = (count($demandQty) > 0);

                // Ensure route/frequency dictionaries are cached for this row
                $missingRouteIds = array_values(array_diff($routeIdsArr, array_keys($routeNameCache)));
                if ($missingRouteIds) {
                    $fetched = MedicationRoutes::whereIn('id', $missingRouteIds)->pluck('name', 'id')->toArray();
                    foreach ($fetched as $id => $name) $routeNameCache[(int)$id] = $name;
                    foreach ($missingRouteIds as $id) if (!isset($routeNameCache[$id])) $routeNameCache[$id] = 'N/A';
                }
                $missingFreqIds = array_values(array_diff($freqIdsArr, array_keys($frequencyNameCache)));
                if ($missingFreqIds) {
                    $fetched = MedicationFrequency::whereIn('id', $missingFreqIds)->pluck('name', 'id')->toArray();
                    foreach ($fetched as $id => $name) $frequencyNameCache[(int)$id] = $name;
                    foreach ($missingFreqIds as $id) if (!isset($frequencyNameCache[$id])) $frequencyNameCache[$id] = 'N/A';
                }

                // Preload ref map if needed
                $refMap = [];
                if (!$isDirectEntry && !empty($row->referenceNumber)) {
                    $refMap = $getIMRefMap($row->referenceNumber);
                }

                $count = count($genericIds);
                for ($i = 0; $i < $count; $i++) {
                    $bg = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';
                    $currentGenericId = (int) $genericIds[$i];

                    // Balances (identical logic paths)
                    $balances = ['orgBalance' => 'N/A', 'siteBalance' => 'N/A', 'locBalance' => 'N/A'];
                    if ($isDirectEntry) {
                        $balanceInfo = $getIMFirstByGeneric($currentGenericId);
                        if ($balanceInfo) {
                            $b = $getBalances(
                                $row->org_id, $row->site_id, $currentGenericId,
                                $balanceInfo['brand_id'], $balanceInfo['batch_no'],
                                $row->SourceLocationName ?: $row->DestinationLocationName
                            );
                            // $b = $getBalances($row->org_id, $row->site_id, $currentGenericId, $balanceInfo['brand_id'], $balanceInfo['batch_no']);
                            $balances = $b;
                        }
                    } elseif (!empty($row->referenceNumber) && !empty($respondedQtys)) {
                        $entry = $refMap[$currentGenericId] ?? null;
                        if ($entry) {
                            $b = $getBalances(
                                $row->org_id, $row->site_id, $currentGenericId,
                                $entry['brand_id'], $entry['batch_no'],
                                $row->SourceLocationName ?: $row->DestinationLocationName
                            );

                            // $b = $getBalances($row->org_id, $row->site_id, $currentGenericId, $entry['brand_id'], $entry['batch_no']);
                            $balances = $b;
                        }
                    }

                    $currentDemandQty     = isset($demandQty[$i]) ? (float) $demandQty[$i] : 0.0;
                    $currentRespondedQty  = isset($respondedQtys[$currentGenericId]) ? (float) $respondedQtys[$currentGenericId] : 0.0;

                    // Status / Action (unchanged)
                    if ($currentRespondedQty > 0) {
                        if ($currentRespondedQty >= $currentDemandQty) {
                            if ($isIssue) { $status = 'Issued'; $statusClass = 'megna'; }
                            else          { $status = 'Dispensed'; $statusClass = 'success'; }
                            $actionBtn = 'N/A';
                        } else {
                            $status = $isIssue ? 'Partially Issued' : 'Partially Dispensed';
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
                        if ($isIssue) { $status = 'Issued'; $statusClass = 'megna'; }
                        else          { $status = 'Dispensed'; $statusClass = 'success'; }
                        $actionBtn = 'N/A';
                    }

                    // Brand/Batch/Expiry
                    $brandName = '';
                    $batchNo   = '';
                    $expiryDate = '';

                    if (!$isDirectEntry && !empty($row->referenceNumber)) {
                        $itemData = $refMap[$currentGenericId] ?? null;
                        if ($itemData) {
                            $brandName = $getBrandName($itemData['brand_id']);
                            $batchNo   = $itemData['batch_no'];
                            $expiryDate = is_numeric($itemData['expiry_date'])
                                ? \Carbon\Carbon::createFromTimestamp($itemData['expiry_date'])->format('d-M-Y')
                                : $itemData['expiry_date'];
                        }
                    } else {
                        $brandName = isset($brandIdArr[$i]) ? $getBrandName((int)$brandIdArr[$i]) : '';
                        $batchNo   = $batchNoArr[$i] ?? '';
                        $rawExpiry = $expiryArr[$i] ?? '';
                        $expiryDate = is_numeric($rawExpiry)
                            ? \Carbon\Carbon::createFromTimestamp((int)$rawExpiry)->format('d-M-Y')
                            : $rawExpiry;
                    }

                    // Enforce rights
                    if ($rightsRespond != 1) {
                        $actionBtn = '<code>Unauthorized Access</code>';
                    }

                    $tableRows .= '<tr style="background-color:'.$bg.'; cursor:pointer;" class="balance-row"'
                                .' data-expiry="'.$expiryDate.'"'
                                .' data-brand="'.$brandName.'"'
                                .' data-batch="'.$batchNo.'"'
                                .' data-loc-balance="'.$balances['locBalance'].'"'
                                .' data-org-balance="'.$balances['orgBalance'].'"'
                                .' data-site-balance="'.$balances['siteBalance'].'">';

                    $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.($genericNameCache[$currentGenericId] ?? 'N/A').'</td>';

                    if ($showMedicationFields) {
                        $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.($doseArr[$i] ?? 'N/A').'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;">'.($routeNameCache[$routeIdsArr[$i] ?? 0] ?? 'N/A').'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;">'.($frequencyNameCache[$freqIdsArr[$i] ?? 0] ?? 'N/A').'</td>'
                                    . '<td style="padding:8px;border:1px solid #ccc;">'.($daysArr[$i] ?? 'N/A').'</td>';
                    }

                    if ($showDemandQty) {
                        $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.($demandQty[$i] ?? 'N/A').'</td>';
                    }

                    // For inventory source, show its transactionQty column
                    if ($row->source === 'inventory' && $transactionQty !== null) {
                        $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.($transactionQty[$i] ?? 'N/A').'</td>';
                    }

                    // For non-inventory source, show responded qty
                    if ($row->source !== 'inventory' && $currentRespondedQty >= 0) {
                        $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$currentRespondedQty.'</td>';
                    }

                    $tableRows .= '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$actionBtn.'</td>'
                                . '<td style="padding: 5px 15px;border: 1px solid #ccc;"><span class="label label-'.$statusClass.'">'.$status.'</span></td>'
                                . '</tr>';
                }

                // Header (unchanged, just built once)
                $tableHeader = '<tr>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>';

                if ($showMedicationFields) {
                    $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Dose</th>'
                                . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Route</th>'
                                . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Frequency</th>'
                                . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Duration (Days)</th>';
                }
                if ($showDemandQty) {
                    $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Demand Qty</th>';
                }

                $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Transaction Qty</th>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Action</th>'
                            . '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Status</th>'
                            . '</tr>';

                return '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
                    .'<thead style="background-color:#e2e8f0;color:#000;">'.$tableHeader.'</thead>'
                    .'<tbody>'.$tableRows.'</tbody></table>';
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
                    'r.source_location_id',
                    'r.destination_location_id',
                    DB::raw('COALESCE(srcLoc.name, "") as source_location_name'),
                    DB::raw('COALESCE(destLoc.name, "") as destination_location_name'),
                    'sm.name as service_mode_name',
                    'sv.name as service_name',
                    'e.name as physician_name',
                    'bcc.name as billing_cc_name',
                    'sg.name as service_group_name',
                    'st.name as service_type_name',
                ])
                ->join('organization as o', 'o.id', '=', 'r.org_id')
                ->join('org_site as s', 's.id', '=', 'r.site_id')
                ->join('patient as p', 'p.mr_code', '=', 'r.mr_code')
                ->join('inventory_transaction_type as itt', 'itt.id', '=', 'r.transaction_type_id')
                ->leftJoin('service_location as srcLoc', 'srcLoc.id', '=', 'r.source_location_id')
                ->leftJoin('service_location as destLoc', 'destLoc.id', '=', 'r.destination_location_id')
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
                'source_location_id'      => $med->source_location_id,
                'source_location_name'    => $med->source_location_name,
                'destination_location_id' => $med->destination_location_id,
                'destination_location_name'=> $med->destination_location_name,
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

                $mat = MaterialConsumptionRequisition::from('material_consumption_requisition as m')
                ->select([
                    'm.id',
                    'm.code',
                    'm.org_id',
                    'm.site_id',
                    'm.mr_code',
                    'm.transaction_type_id',
                    'm.source_location_id',
                    'm.destination_location_id',
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
                    DB::raw('COALESCE(srcLoc.name, "") as source_location_name'),
                    DB::raw('COALESCE(destLoc.name, "") as destination_location_name'),
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
                ->leftJoin('service_location as srcLoc', 'srcLoc.id', '=', 'm.source_location_id')
                ->leftJoin('service_location as destLoc', 'destLoc.id', '=', 'm.destination_location_id')
                ->leftJoin('service_mode                as sm',   'sm.id',  '=', 'm.service_mode_id')
                ->leftJoin('services                    as sv',   'sv.id',  '=', 'm.service_id')
                ->leftJoin('service_group               as sg',   'sg.id',  '=', 'sv.group_id')
                ->leftJoin('service_type                as st',   'st.id',  '=', 'sg.type_id')
                ->leftJoin('employee                    as e',   'e.id',  '=', 'm.physician_id')
                ->leftJoin('costcenter                  as bcc', 'bcc.id',     '=', 'm.billing_cc')
                // ->leftJoin('inventory_management as im', function($join) use ($genericId) {
                //     $join->on('im.ref_document_no', '=', 'm.code')
                //          ->where('im.inv_generic_id', '=', DB::raw($genericId));
                // })
                // ->leftJoin('inventory_transaction_type as itt2', 'itt2.id', '=', 'im.transaction_type_id')
                // ->leftJoin('inventory_transaction_activity as ita', 'ita.id', '=', 'itt2.activity_type')
                // ->where(function($query) {
                //     $query->where('ita.name', 'like', '%issue%')
                //         ->orWhere('ita.name', 'like', '%dispense%');
                // })
                ->leftJoin('inventory_management as im', function($join) use ($genericId) {
                    $join->on('im.ref_document_no', '=', 'm.code')
                        ->where('im.inv_generic_id', '=', DB::raw($genericId));
                })
                ->leftJoin('inventory_transaction_type as itt2', 'itt2.id', '=', 'im.transaction_type_id')
                ->leftJoin('inventory_transaction_activity as ita', 'ita.id', '=', 'itt2.activity_type')
                ->where(function($query) {
                    $query->whereNull('im.id')
                        ->orWhere(function($q) {
                            $q->where(function($sub) {
                                $sub->where('ita.name', 'like', '%issue%')
                                ->orWhere('ita.name', 'like', '%dispense%');
                            });
                        });
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
                    'm.transaction_type_id', 'm.source_location_id', 'm.destination_location_id', 'm.service_mode_id',
                    'm.service_id', 'm.physician_id', 'm.billing_cc', 'm.generic_id',
                    'm.qty', 'm.status',
                    'o.organization', 's.name', 'p.name', 'itt.name', 'srcLoc.name', 'destLoc.name',
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
                'source_location_id'     => $mat->source_location_id,
                'source_location_name'   => $mat->source_location_name,
                'destination_location_id'=> $mat->destination_location_id,
                'destination_location_name'=> $mat->destination_location_name,
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

    public function GetBatchNoForReporting(Request $request)
    {
        $orgId     = $request->query('orgId');
        $siteId    = $request->query('siteId');
        $brandId   = $request->query('brandId');
        $genericId = $request->query('genericId');

        if (! $orgId || ! $siteId || ! $brandId || ! $genericId) {
            return response()->json(null, 400);
        }   
        
        // Build query directly from InventoryBalance table
        $query = InventoryBalance::where('org_id', $orgId)
            ->where('site_balance', '>', 0);
        
        // Add site_id condition
        if ($siteId !== '0101') {
            if (strpos($siteId, ',') !== false) {
                $siteIds = array_map('intval', explode(',', $siteId));
                $query->whereIn('site_id', $siteIds);
            } else {
                $query->where('site_id', intval($siteId));
            }
        }
        
        // Add brand_id condition
        if ($brandId !== '0101') {
            if (strpos($brandId, ',') !== false) {
                $brandIds = array_map('intval', explode(',', $brandId));
                $query->whereIn('brand_id', $brandIds);
            } else {
                $query->where('brand_id', intval($brandId));
            }
        }
        
        // Add generic_id condition
        if ($genericId !== '0101') {
            if (strpos($genericId, ',') !== false) {
                $genericIds = array_map('intval', explode(',', $genericId));
                $query->whereIn('generic_id', $genericIds);
            } else {
                $query->where('generic_id', intval($genericId));
            }
        }
        
        // Get distinct batch numbers with latest site_balance
        $batches = $query
            ->selectRaw('batch_no')
            ->groupBy('batch_no')
            ->orderBy('batch_no')
            ->get();
        
        $batchList = [];
        
        foreach ($batches as $batch) {
            $batchList[] = [
                'batch_no'     => $batch->batch_no,
            ];
        }

        return response()->json($batchList);
    }
    
    public function AddIssueDispense(IssueDispenseRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->issue_and_dispense)[0];
        $respond = explode(',', $rights->issue_and_dispense)[2];
        if ($add != 1 && $respond != 1) {
            abort(403, 'Forbidden');
        }
        // if ($add == 0) {
        //     abort(403, 'Forbidden');
        // }
        // Get validated data
        $validated = $request->validated();
        $itemCount = isset($validated['id_generic']) ? count($validated['id_generic']) : 0;
        $success = true;
        $message = '';

        $inventory = new InventoryManagement();
        // Required fields
        $inventory->transaction_type_id = $validated['id_transactiontype'];
        $inventory->org_id = $validated['id_org'];
        $inventory->site_id = $validated['id_site'];
        $inventory->source = $validated['id_source'];
        $inventory->destination = $validated['id_destination'];

        if (isset($validated['id_mr']) && !empty($validated['id_mr'])) {
            $inventory->mr_code = $validated['id_mr'];
            // $inventory->dose = $validated['id_duration'][0];
            // $inventory->frequency_id = $validated['id_frequency'][0];
            // $inventory->route_id = $validated['id_route'][0];
            // $inventory->duration = $validated['id_duration'][0];

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
            $inventory->inv_generic_id = implode(',', $validated['id_generic']);
            $inventory->brand_id = implode(',', $validated['id_brand']);
            $inventory->batch_no = implode(',', $validated['id_batch']);
            // if (empty($validated['id_mr'])) {
            //     $inventory->demand_qty = implode(',', $validated['id_demand_qty']);
            // }
            if (isset($validated['id_demand_qty']) && !empty($validated['id_demand_qty'])) {
                $inventory->demand_qty = implode(',', $validated['id_demand_qty']);
            }

            if (isset($validated['id_mr']) && !empty($validated['id_mr'])) {
                $inventory->dose = implode(',', $validated['id_dose']);
                $inventory->frequency_id = implode(',', $validated['id_frequency']);
                $inventory->route_id = implode(',', $validated['id_route']);
                $inventory->duration = implode(',', $validated['id_duration']);
            }

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

            // if (empty($validated['id_mr'])) {
            if (isset($validated['id_demand_qty']) && !empty($validated['id_demand_qty'])) {
                $inventory->demand_qty = $validated['id_demand_qty'][0];
            }

            if (isset($validated['id_mr']) && !empty($validated['id_mr'])) {
                $inventory->dose = $validated['id_dose'][0];
                $inventory->frequency_id = $validated['id_frequency'][0];
                $inventory->route_id = $validated['id_route'][0];
                $inventory->duration = $validated['id_duration'][0];
            }
        }

        $inventory->status = 1;
        $inventory->user_id = auth()->id();
        $inventory->effective_timestamp = now()->timestamp;
        $inventory->timestamp = now()->timestamp;
        $inventory->last_updated = now()->timestamp;

        $rule = DB::table('inventory_transaction_type')
        ->select('source_action', 'destination_action', 'source_location_type', 'destination_location_type')
        ->where('id', $validated['id_transactiontype'])
        ->first();

        // $transactionTypeName = DB::table('inventory_transaction_type')
        //     ->where('id', $validated['id_transactiontype'])
        //     ->value('name');

        // $isIssueOnly = Str::contains(strtolower($transactionTypeName), 'issue');

        // With applicable_location_to removed, prefer destination_action if defined, else fallback to source_action
        $useAction = (isset($rule->destination_action) && in_array($rule->destination_action, ['a','s','r']))
            ? $rule->destination_action
            : $rule->source_action;

        $sourceType = DB::table('inventory_source_destination_type')->where('id', $rule->source_location_type)->value('name');
        $destinationType = DB::table('inventory_source_destination_type')->where('id', $rule->destination_location_type)->value('name');

        foreach ($validated['id_generic'] as $i => $genId) {
            $brandId = $validated['id_brand'][$i];
            $batchNo = $validated['id_batch'][$i];
            $qty = (int) $validated['id_qty'][$i];
            if (strtolower($sourceType) === 'inventory location' && $validated['id_source'] && in_array($rule->source_action, ['s', 'r'])) {
                $sourceLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['id_org'])
                    ->where('site_id', $validated['id_site'])
                    ->where('location_id', $validated['id_source'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $sourceLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient source location balance. Available: $sourceLocBalance, Requested: $qty"
                    ]);
                }
            }

            if (strtolower($destinationType) === 'inventory location' && $validated['id_destination'] && in_array($rule->destination_action, ['s', 'r'])) {
                $destinationLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['id_org'])
                    ->where('site_id', $validated['id_site'])
                    ->where('location_id', $validated['id_destination'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $destinationLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient destination location balance. Available: $destinationLocBalance, Requested: $qty"
                    ]);
                }
            }
        }

        if (!$inventory->save()) {
            $success = false;
            $message = 'Failed to save inventory record';
        }

        // If no reference document was provided, generate a unique one using site initials and the new ID
        if (empty($inventory->ref_document_no)) {
            $siteName = DB::table('org_site')->where('id', $validated['id_site'])->value('name');
            $siteCode = strtoupper(substr($siteName ?? 'SITE', 0, 3));
            $idStr    = str_pad($inventory->id, 5, '0', STR_PAD_LEFT);
            $inventory->ref_document_no = $siteCode . '-ID-' . $idStr; // ID = Issue & Dispense
            $inventory->save();
        }

        // Get transaction type rule for balance calculation
        // $rule = DB::table('inventory_transaction_type')
        //     ->select('applicable_location_to', 'source_action', 'destination_action')
        //     ->where('id', $validated['id_transactiontype'])
        //     ->first();
        $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
        $remarkText = "Transaction Initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} ";

        // Process each item separately for inventory_balance
        // if (! $isIssueOnly) {
        for ($i = 0; $i < $itemCount; $i++) {
            $genId = $validated['id_generic'][$i];
            $brandId = $validated['id_brand'][$i];
            $batchNo = $validated['id_batch'][$i];
            $qty = (int)$validated['id_qty'][$i];

            // $expTs = Carbon::createFromFormat('Y-m-d', $validated['id_expiry'][$i])->timestamp;
            if (! $genId || ! $brandId || ! $batchNo) {
                continue;
            }

            $prevOrgRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['id_org'])
                ->orderBy('id', 'desc')
                ->first();
            $prevOrgBalance = $prevOrgRow->org_balance ?? 0;

            $prevSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['id_org'])
                ->where('site_id',   $validated['id_site'])
                ->orderBy('id', 'desc')
                ->first();
            $prevSiteBalance = $prevSiteRow->site_balance ?? 0;

            // Check if both source and destination are inventory locations
            if (strtolower($sourceType) === 'inventory location' && $validated['id_source'] && strtolower($destinationType) === 'inventory location' && $validated['id_destination']) {
                // Material Transfer: Don't modify org_balance, only modify site_balance
                $newOrgBalance  = $prevOrgBalance;  // No change in organization balance
                $newSiteBalance = $prevSiteBalance; // Default to current site balance
                // Adjust balances based on source and destination locations
                if ($rule->source_action === 'a') {
                    $newSiteBalance += $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSiteBalance -= $qty;
                }
                // Adjust destination location's balance similarly
                if ($rule->destination_action === 'a') {
                    $newSiteBalance += $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newSiteBalance -= $qty;
                }
            } else {
                // Other cases (already exists)
                // Default balance calculations
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
            }

            // $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
            $remarkText = "Transaction Initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSiteBalance}";
            if (strtolower($sourceType) === 'inventory location' && $validated['id_source'] && strtolower($destinationType) === 'inventory location' && $validated['id_destination']) {
                // Source location row
                $prevSourceLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['id_org'])
                    ->where('site_id',   $validated['id_site'])
                    ->where('location_id', $validated['id_source'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevSourceLocBalance = $prevSourceLocRow->location_balance ?? 0;

                // if (in_array($rule->source_action, ['s', 'r']) && $qty > $prevSourceLocBalance) {
                //     return response()->json([
                //         'info' => "Insufficient source location balance. Available: $prevSourceLocBalance, Requested: $qty"
                //     ]);
                // }

                if ($rule->source_action === 'a') {
                    $newSourceLocBalance = $prevSourceLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSourceLocBalance = $prevSourceLocBalance - $qty;
                } else {
                    $newSourceLocBalance = $prevSourceLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['id_org'],
                    'site_id'          => $validated['id_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['id_source'],
                    'location_balance' => $newSourceLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);

                // Destination location row
                $prevDestLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['id_org'])
                    ->where('site_id',   $validated['id_site'])
                    ->where('location_id', $validated['id_destination'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevDestLocBalance = $prevDestLocRow->location_balance ?? 0;

                // if (in_array($rule->destination_action, ['s', 'r']) && $qty > $prevDestLocBalance) {
                //     return response()->json([
                //         'info' => "Insufficient destination location balance. Available: $prevDestLocBalance, Requested: $qty"
                //     ]);
                // }

                if ($rule->destination_action === 'a') {
                    $newDestLocBalance = $prevDestLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newDestLocBalance = $prevDestLocBalance - $qty;
                } else {
                    $newDestLocBalance = $prevDestLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['id_org'],
                    'site_id'          => $validated['id_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['id_destination'],
                    'location_balance' => $newDestLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($sourceType) === 'inventory location' && $validated['id_source']) {
                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['id_org'])
                    ->where('site_id',   $validated['id_site'])
                    ->where('location_id', $validated['id_source'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;

                // if (in_array($rule->source_action, ['s', 'r']) && $qty > $prevLocBalance) {
                //     return response()->json([
                //         'info' => "Insufficient source location balance. Available: $prevLocBalance, Requested: $qty"
                //     ]);
                // }

                if ($rule->source_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['id_org'],
                    'site_id'          => $validated['id_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['id_source'],
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($destinationType) === 'inventory location' && $validated['id_destination']) {

                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['id_org'])
                    ->where('site_id',   $validated['id_site'])
                    ->where('location_id', $validated['id_destination'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;

                // if (in_array($rule->destination_action, ['s', 'r']) && $qty > $prevLocBalance) {
                //     return response()->json([
                //         'info' => "Insufficient destination location balance. Available: $prevLocBalance, Requested: $qty"
                //     ]);
                // }

                if ($rule->destination_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }

                // dd($newLocBalance, $Destination)
                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['id_org'],
                    'site_id'          => $validated['id_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['id_destination'],
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            else {
                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['id_org'],
                    'site_id'          => $validated['id_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => null,
                    'location_balance' => null,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }

            // Get previous balances
            // $prevOrgRow = InventoryBalance::where('generic_id', $genId)
            //     ->where('brand_id', $brandId)
            //     ->where('batch_no', $batchNo)
            //     ->where('org_id', $validated['id_org'])
            //     ->orderBy('id', 'desc')
            //     ->first();
            // $prevOrgBalance = $prevOrgRow ? $prevOrgRow->org_balance : 0;

            // $prevSiteRow = InventoryBalance::where('generic_id', $genId)
            //     ->where('brand_id', $brandId)
            //     ->where('batch_no', $batchNo)
            //     ->where('org_id', $validated['id_org'])
            //     ->where('site_id', $validated['id_site'])
            //     ->orderBy('id', 'desc')
            //     ->first();
            // $prevSiteBalance = $prevSiteRow ? $prevSiteRow->site_balance : 0;

            // $newOrgBalance = $prevOrgBalance - $qty;
            // $newSiteBalance = $prevSiteBalance - $qty;

            // $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
            // $remarkText = "Transaction Initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSiteBalance}";

            // Create inventory balance record
            // InventoryBalance::create([
            //     'management_id' => $inventory->id,
            //     'generic_id' => $genId,
            //     'brand_id' => $brandId,
            //     'batch_no' => $batchNo,
            //     // 'expiry_date' => $expTs,
            //     'org_id' => $validated['id_org'],
            //     'site_id' => $validated['id_site'],
            //     'org_balance' => $newOrgBalance,
            //     'site_balance' => $newSiteBalance,
            //     'remarks' => $remarkText,
            //     'timestamp' => now()->timestamp,
            // ]);
        }
        // }

        if ($success) {
            $logId = createLog(
                'inventory_management',
                'insert',
                [
                    'message' => $remarkText,
                    'created_by' => auth()->user()->name ?? 'system'
                ],
                $inventory->id ?? null,
                null,
                null,
                auth()->id() ?? 0
            );

            $inventory->logid = $logId;
            $inventory->save();

            return response()->json([
                'success' => 'Records have been added successfully',
                'reload' => true
            ]);
        } else {
            return response()->json([
                'error' => $message,
                'reload' => false
            ]);
        }
    }

    public function ShowMaterialTransfer()
    {
        $colName = 'material_transfer';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $empId = $user->emp_id;

        // $costcenters = DB::table('emp_cc as e')
        // ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
        // ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
        // ->where('e.emp_id', $empId)
        // ->where('ct.performing', 1)
        // ->select('c.id', 'c.name')
        // ->get();

        $costcenters = DB::table(function ($query) use ($empId) {
            $query->select('c.id', 'c.name')
                ->from('emp_cc as e')
                ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
                ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                ->where('e.emp_id', $empId)
                ->where('ct.performing', 1)

            ->unionAll(
                DB::table('employee as emp')
                    ->select('c.id', 'c.name')
                    ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, emp.cc_id)'), '>', DB::raw('0'))
                    ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                    ->where('emp.id', $empId)
                    ->where('ct.performing', 1)
            );
        }, 'combined')
        ->distinct()
        ->get();

        $RequisitionNonMandatory = DB::table('inventory_transaction_type AS itt')
        ->join('inventory_transaction_activity AS ita', 'ita.id', '=', 'itt.activity_type')
        ->where('ita.name', 'like', '%material transfer%')
        ->where('itt.request_mandatory', 'n')
        ->exists();


        return view('dashboard.material_management.material_transfer', compact('user','RequisitionNonMandatory','costcenters'));
    }

    public function GetMaterialTransferData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->material_transfer)[1];
        if ($view == 0) {
            abort(403, 'Forbidden');
        }

        // Fetch from requisition_other_transaction
        $requisitions = DB::table('requisition_material_transfer as rmt')
        ->join('organization', 'organization.id', '=', 'rmt.org_id')
        ->leftJoin('org_site as sourceSite', 'sourceSite.id', '=', 'rmt.source_site')
        ->leftJoin('org_site as destinationSite', 'destinationSite.id', '=', 'rmt.destination_site')
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'rmt.transaction_type_id')
        ->leftJoin('service_location as sourceLocation', 'sourceLocation.id', '=', 'rmt.source_location')
        ->leftJoin('service_location as destinationLocation', 'destinationLocation.id', '=', 'rmt.destination_location')
        // ->leftJoin('inventory_management', 'inventory_management.ref_document_no', '=', 'rmt.code')
        // ->leftJoin('inventory_brand', 'inventory_brand.id', '=', 'inventory_management.brand_id')
        ->select([
            // 'rmt.*',
            'rmt.id',
            // 'rmt.timestamp',
            // 'rmt.effective_timestamp',
            DB::raw("DATE_FORMAT(FROM_UNIXTIME(rmt.timestamp), '%W %e %M %Y - %h:%i %p') as req_at"),
            DB::raw("DATE_FORMAT(FROM_UNIXTIME(rmt.effective_timestamp), '%W %e %M %Y - %h:%i %p') as eff_at"),
            'rmt.org_id',
            'rmt.source_site',
            'rmt.destination_site',
            'rmt.source_location',
            'rmt.destination_location',
            'rmt.generic_id',
            DB::raw('rmt.qty as demand_qty'),
            'rmt.remarks',
            DB::raw('NULL as transaction_qty'),
            // 'rmt.qty as demand_qty',
            'organization.organization as orgName',
            'sourceSite.name as sourceSite',
            'destinationSite.name as destinationSite',
            'inventory_transaction_type.name as transactionType',
            'sourceLocation.name as sourceLocation',
            'destinationLocation.name as destinationLocation',
            DB::raw("CONVERT(rmt.code USING utf8mb4) COLLATE utf8mb4_unicode_ci as referenceNumber"),
            // DB::raw('COALESCE(inventory_brand.name, "") as brandName'), // Fetch brand name from the brand table
            // DB::raw('COALESCE(inventory_management.batch_no, "") as batch_no'),
            // DB::raw('COALESCE(inventory_management.expiry_date, "") as expiry_date'),
        ]);

        // Add session site condition for requisitions
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $requisitions->where(function($query) use ($sessionSiteIds) {
                    $query->whereIn('rmt.source_site', $sessionSiteIds)
                          ->orWhereIn('rmt.destination_site', $sessionSiteIds);
                });
            }
        }

        $requisitions = $requisitions->orderByDesc('rmt.id')->get();

        $inventoryMaterialTransfers = DB::table('inventory_management as im')
        ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
        ->join('inventory_transaction_activity as ita', 'ita.id', '=', 'itt.activity_type')
        ->join('organization', 'organization.id', '=', 'im.org_id')
        // ->join('org_site', 'org_site.id', '=', 'im.site_id')
        ->leftJoin('org_site as sourceSite', 'sourceSite.id', '=', 'im.site_id')
        ->leftJoin('org_site as destinationSite', 'destinationSite.id', '=', 'im.d_site_id')
        ->leftJoin('service_location as sourceLocation', 'sourceLocation.id', '=', 'im.source')
        ->leftJoin('service_location as destinationLocation', 'destinationLocation.id', '=', 'im.destination')
        ->join('inventory_brand', 'inventory_brand.id', '=', 'im.brand_id')
        // ->leftJoin('costcenter as billingCC', 'billingCC.id', '=', 'im.billing_cc')
        // ->leftJoin('service_location', 'service_location.id', '=', 'im.source')
        ->select([
            // 'im.*',
            'im.id',
            // 'im.timestamp',
            // 'im.effective_timestamp',
            DB::raw("DATE_FORMAT(FROM_UNIXTIME(im.timestamp), '%W %e %M %Y - %h:%i %p') as req_at"),
            DB::raw("DATE_FORMAT(FROM_UNIXTIME(im.effective_timestamp), '%W %e %M %Y - %h:%i %p') as eff_at"),
            'im.org_id',
            'im.remarks',
            'im.site_id as source_site',
            'im.d_site_id as destination_site',
            'im.source as source_location',
            'im.destination as destination_location',
            'im.inv_generic_id as generic_id',
            'im.transaction_qty',
            'im.expiry_date',
            'im.brand_id',
            'im.batch_no as batch',
            'im.batch_no as batch_no',
            'im.site_id as source_site',
            'im.d_site_id as destination_site',
            'im.inv_generic_id as generic_id',
            'im.batch_no as batch',
            DB::raw('im.demand_qty as demand_qty'),
            'organization.organization as orgName',
            'sourceSite.name as sourceSite',
            'destinationSite.name as destinationSite',
            // 'org_site.name as siteName',
            'sourceLocation.name as sourceLocation',
            'destinationLocation.name as destinationLocation',
            'inventory_brand.name as brandName',
            'itt.name as transactionType',
            DB::raw('"N/A" as locationName'),
            DB::raw("CONVERT(im.ref_document_no USING utf8mb4) COLLATE utf8mb4_unicode_ci as referenceNumber"),
        ])
        ->where(function($query) {
            $query->where('ita.name', 'like', '%material transfer%');
        })
        ->where(function($query) {
            $query->whereNull('im.ref_document_no')
                ->orWhere('im.ref_document_no', 'not like', '%-RMT-%');
        });

        // Add session site condition for inventory material transfers
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $inventoryMaterialTransfers->where(function($query) use ($sessionSiteIds) {
                    $query->whereIn('im.site_id', $sessionSiteIds)
                          ->orWhereIn('im.d_site_id', $sessionSiteIds);
                });
            }
        }

        $inventoryMaterialTransfers = $inventoryMaterialTransfers->orderByDesc('im.id')->get();
        $combined = $requisitions->merge($inventoryMaterialTransfers);

        return DataTables::of(collect($combined))
        ->addColumn('id_raw', fn($row) => $row->id)
        // ->editColumn('id', function ($row) {
        //     $timestamp = Carbon::createFromTimestamp($row->timestamp)->format('l d F Y - h:i A');
        //     // return $row->effective_timestamp;

        //     $effectiveDate = Carbon::createFromTimestamp($row->effective_timestamp)->format('l d F Y - h:i A');

        //     $RequisitionCode = $row->referenceNumber ?? 'N/A';

        //     $sourceSite = '';
        //     $sourceLocation = '';
        //     $destinationSite = '';
        //     $destinationLocation = '';

        //     if (($row->sourceSite) && ($row->sourceLocation))
        //     {
        //         $sourceSite ='<br><b>Source Site: </b>'.ucwords($row->sourceSite);
        //         $sourceLocation ='<br><b>Source Location: </b>'.ucwords($row->sourceLocation);
        //     }

        //     if (($row->destinationSite) && ($row->destinationLocation))
        //     {
        //         $destinationSite ='<br><b>Destination Site: </b>'.ucwords($row->destinationSite);
        //         $destinationLocation ='<br><b>Destination Location: </b>'.ucwords($row->destinationLocation);
        //     }

        //     return $RequisitionCode
        //     . '<hr class="mt-1 mb-2">'
        //     . '<b>Request For</b>: ' . ($row->transactionType ?? 'N/A') . '<br>'
        //                 .$sourceSite
        //                 .$sourceLocation
        //                 .$destinationSite
        //                 .$destinationLocation
        //     . '<br><b>Request Date </b>: ' . $timestamp . '<br>'
        //     . '<b>Effective Date </b>: ' . $effectiveDate . '<br>'
        //     . '<b>Remarks</b>: ' . ($row->remarks ?: 'N/A');
        // })
        ->editColumn('id', function ($row) {
            $timestamp     = $row->req_at ?? (is_numeric($row->timestamp) 
                                ? \Carbon\Carbon::createFromTimestamp((int)$row->timestamp)->format('l d F Y - h:i A') 
                                : 'N/A');

            $effectiveDate = $row->eff_at ?? (is_numeric($row->effective_timestamp) 
                                ? \Carbon\Carbon::createFromTimestamp((int)$row->effective_timestamp)->format('l d F Y - h:i A') 
                                : 'N/A');

            $ref   = e($row->referenceNumber ?? 'N/A');
            $type  = e($row->transactionType ?? 'N/A');
            $remarks = e($row->remarks ?? 'N/A');

            $srcBlock = '';
            if (!empty($row->sourceSite) && !empty($row->sourceLocation)) {
                $srcBlock =
                    '<br><b>Source Site: </b>' . e($row->sourceSite) .
                    '<br><b>Source Location: </b>' . e($row->sourceLocation);
            }

            $dstBlock = '';
            if (!empty($row->destinationSite) && !empty($row->destinationLocation)) {
                $dstBlock =
                    '<br><b>Destination Site: </b>' . e($row->destinationSite) .
                    '<br><b>Destination Location: </b>' . e($row->destinationLocation);
            }

            return $ref
                . '<hr class="mt-1 mb-2">'
                . '<b>Request For</b>: ' . $type . '<br>'
                . $srcBlock
                . $dstBlock
                . '<br><b>Request Date </b>: ' . e($timestamp) . '<br>'
                . '<b>Effective Date </b>: ' . e($effectiveDate) . '<br>'
                . '<b>Remarks</b>: ' . $remarks;
        })
        ->editColumn('InventoryDetails', function ($row) {
            $Rights  = $this->rights;
            $respond = explode(',', $Rights->material_transfer)[2];

            $tableRows = '';

            // --- parse generics / qtys (keep index alignment) ---
            $csv = function($v) {
                $a = explode(',', (string)$v);
                return array_map('trim', $a);
            };
            $genericIds    = $csv($row->generic_id);
            $demandQtyList = $csv($row->demand_qty);
            $trxQtyList    = $csv($row->transaction_qty);

            // generic names cache for the whole page (static)
            static $genericCache = [];
            $missGen = array_values(array_diff(array_unique(array_filter($genericIds, fn($x)=>$x!=='')), array_keys($genericCache)));
            if (!empty($missGen)) {
                InventoryGeneric::whereIn('id', $missGen)->pluck('name', 'id')
                    ->each(function($name,$id) use (&$genericCache){ $genericCache[$id]=$name; });
            }

            // Determine direct IM entry vs requisition flow
            $isDirectEntry = true;
            if (!empty($row->referenceNumber) && str_contains($row->referenceNumber, '-RMT-')) {
                $isDirectEntry = false;
            }

            // --- collect tuples (generic, brand, batch) we need balances for ---
            $tuples = [];              // key: "g|b|ba" => ['g'=>int,'b'=>int,'ba'=>string]
            $brandIdsUsed = [];        // for name pluck
            $batchDetailsPerGen = [];  // for tooltip details per generic

            // Arrays we need later in the per-row loop (declare up here)
            $brandIdsArr = $batchesArr = $expiryArr = [];

            if ($isDirectEntry) {
                // Parse aligned arrays for IM multi-item rows
                $brandIdsArr = $csv($row->brand_id);                     // e.g. ["18","449"]
                $batchesArr  = $csv($row->batch ?? $row->batch_no ?? ''); // e.g. ["1212212","CA250901TZ"]
                $expiryArr   = $csv($row->expiry_date);                  // e.g. ["1709259555","1821642755"]

                $len = max(count($genericIds), count($brandIdsArr), count($batchesArr), count($expiryArr));

                for ($i = 0; $i < $len; $i++) {
                    $g  = (int)($genericIds[$i]  ?? 0);
                    $b  = (int)($brandIdsArr[$i] ?? 0);
                    $ba = (string)($batchesArr[$i] ?? '');

                    if ($g && $b && $ba !== '') {
                        $key = "{$g}|{$b}|{$ba}";
                        $tuples[$key] = ['g'=>$g, 'b'=>$b, 'ba'=>$ba];
                        $brandIdsUsed[$b] = true;

                        // Prepare per-generic batch details (brand set after brand pluck)
                        $batchDetailsPerGen[$g][] = [
                            'brand'        => null, // fill later
                            'batch'        => $ba,
                            'expiry'       => (isset($expiryArr[$i]) && is_numeric($expiryArr[$i]))
                                            ? date('d-M-Y', (int)$expiryArr[$i]) : ($expiryArr[$i] ?? 'N/A'),
                            'respondedQty' => isset($trxQtyList[$i]) ? (float)$trxQtyList[$i] : 0,
                        ];
                    }
                }
            } else {
                // Requisition: gather all responded entries once for this reference
                $respondedEntriesDetails = DB::table('inventory_management')
                    ->where('ref_document_no', $row->referenceNumber)
                    ->whereIn('inv_generic_id', array_unique(array_filter($genericIds)))
                    ->select('inv_generic_id','brand_id','batch_no','expiry_date','transaction_qty')
                    ->orderByDesc('id')
                    ->get();

                // For status calculation
                $respondedQtys = $respondedEntriesDetails
                    ->groupBy('inv_generic_id')
                    ->map(fn($grp)=>$grp->sum('transaction_qty'))
                    ->toArray();

                // store back for later use in the loop
                $__respondedQtys = $respondedQtys;

                // collect tuples & per-generic tooltip batches
                foreach ($respondedEntriesDetails as $e) {
                    $g = (int)$e->inv_generic_id;
                    $b = (int)$e->brand_id;
                    $ba = (string)$e->batch_no;
                    if ($g && $b && $ba !== '') {
                        $tuples["$g|$b|$ba"] = ['g'=>$g,'b'=>$b,'ba'=>$ba];
                        $brandIdsUsed[$b] = true;
                        $batchDetailsPerGen[$g][] = [
                            'brand'        => null, // fill after brand pluck
                            'batch'        => $ba,
                            'expiry'       => ($e->expiry_date ? date('d-M-Y', is_numeric($e->expiry_date)?(int)$e->expiry_date:$e->expiry_date) : 'N/A'),
                            'respondedQty' => $e->transaction_qty ?? 0,
                        ];
                    }
                }
            }

            // --- Brand names in one shot ---
            static $brandCache = [];
            $missBrands = array_values(array_diff(array_keys($brandIdsUsed), array_keys($brandCache)));
            if (!empty($missBrands)) {
                DB::table('inventory_brand')->whereIn('id', $missBrands)
                    ->pluck('name','id')->each(function($name,$id) use (&$brandCache){ $brandCache[$id]=$name; });
            }
            // fill missing brand names in batchDetailsPerGen (requisition & direct entry)
            foreach ($batchDetailsPerGen as $g => &$rows) {
                foreach ($rows as &$r) {
                    // find brand from tuples for that g + batch
                    foreach ($tuples as $t) {
                        if ($t['g']==$g && $t['ba']===$r['batch']) {
                            $r['brand'] = $brandCache[$t['b']] ?? 'Unknown';
                        }
                    }
                }
            }
            unset($rows,$r);

            // --- balances in bulk (only if we have tuples) ---
            $orgBalMap = [];                 // key "g|b|ba" => org_balance
            $siteBalMapSrc = [];             // key "g|b|ba" => site_balance
            $siteBalMapDst = [];             // key "g|b|ba" => site_balance
            $locLatestSrc = [];              // key "g|b|ba" => [locId => balance]
            $locLatestDst = [];              // key "g|b|ba" => [locId => balance]

            if (!empty($tuples)) {
                $tupleG  = array_unique(array_column($tuples,'g'));
                $tupleB  = array_unique(array_column($tuples,'b'));
                $tupleBA = array_unique(array_column($tuples,'ba'));

                // (1) Latest overall per tuple for org/site
                $latestAnyIds = DB::table('inventory_balance')
                    ->where('org_id', $row->org_id)
                    ->whereIn('generic_id', $tupleG)
                    ->whereIn('brand_id',   $tupleB)
                    ->whereIn('batch_no',   $tupleBA)
                    ->select(DB::raw('MAX(id) as id'))
                    ->groupBy('generic_id','brand_id','batch_no')
                    ->pluck('id')
                    ->toArray();

                $latestThisSrcIds = [];
                $latestThisDstIds = [];
                if (!empty($row->source_site)) {
                    $latestThisSrcIds = DB::table('inventory_balance')
                        ->where('org_id', $row->org_id)
                        ->where('site_id', $row->source_site)
                        ->whereIn('generic_id', $tupleG)
                        ->whereIn('brand_id',   $tupleB)
                        ->whereIn('batch_no',   $tupleBA)
                        ->select(DB::raw('MAX(id) as id'))
                        ->groupBy('generic_id','brand_id','batch_no','site_id')
                        ->pluck('id')->toArray();
                }
                if (!empty($row->destination_site)) {
                    $latestThisDstIds = DB::table('inventory_balance')
                        ->where('org_id', $row->org_id)
                        ->where('site_id', $row->destination_site)
                        ->whereIn('generic_id', $tupleG)
                        ->whereIn('brand_id',   $tupleB)
                        ->whereIn('batch_no',   $tupleBA)
                        ->select(DB::raw('MAX(id) as id'))
                        ->groupBy('generic_id','brand_id','batch_no','site_id')
                        ->pluck('id')->toArray();
                }

                if (!empty($latestAnyIds)) {
                    DB::table('inventory_balance')->whereIn('id', $latestAnyIds)->get()->each(function($r) use (&$orgBalMap){
                        $key = "{$r->generic_id}|{$r->brand_id}|{$r->batch_no}";
                        $orgBalMap[$key] = (int)($r->org_balance ?? 0);
                    });
                }
                if (!empty($latestThisSrcIds)) {
                    DB::table('inventory_balance')->whereIn('id', $latestThisSrcIds)->get()->each(function($r) use (&$siteBalMapSrc){
                        $key = "{$r->generic_id}|{$r->brand_id}|{$r->batch_no}";
                        $siteBalMapSrc[$key] = (int)($r->site_balance ?? 0);
                    });
                }
                if (!empty($latestThisDstIds)) {
                    DB::table('inventory_balance')->whereIn('id', $latestThisDstIds)->get()->each(function($r) use (&$siteBalMapDst){
                        $key = "{$r->generic_id}|{$r->brand_id}|{$r->batch_no}";
                        $siteBalMapDst[$key] = (int)($r->site_balance ?? 0);
                    });
                }

                // (2) Latest per location (source & destination sites)
                $locRowsSrc = collect();
                $locRowsDst = collect();
                if (!empty($row->source_site)) {
                    $locRowsSrc = DB::table('inventory_balance')
                        ->where('org_id', $row->org_id)
                        ->where('site_id', $row->source_site)
                        ->whereNotNull('location_id')
                        ->whereIn('generic_id', $tupleG)
                        ->whereIn('brand_id',   $tupleB)
                        ->whereIn('batch_no',   $tupleBA)
                        ->orderByDesc('id')->get();
                }
                if (!empty($row->destination_site)) {
                    $locRowsDst = DB::table('inventory_balance')
                        ->where('org_id', $row->org_id)
                        ->where('site_id', $row->destination_site)
                        ->whereNotNull('location_id')
                        ->whereIn('generic_id', $tupleG)
                        ->whereIn('brand_id',   $tupleB)
                        ->whereIn('batch_no',   $tupleBA)
                        ->orderByDesc('id')->get();
                }

                // Reduce to latest per (tuple, location_id)
                $reduceLoc = function($rows) {
                    $out = []; // key tuple => [locId => balance]
                    foreach ($rows->groupBy(fn($r)=>"{$r->generic_id}|{$r->brand_id}|{$r->batch_no}") as $tupleKey => $grp) {
                        $seen = [];
                        foreach ($grp as $r) {
                            $loc = (int)$r->location_id;
                            if (!isset($seen[$loc])) {
                                $seen[$loc] = (int)($r->location_balance ?? 0);
                            }
                        }
                        $out[$tupleKey] = $seen;
                    }
                    return $out;
                };
                $locLatestSrc = $reduceLoc($locRowsSrc);
                $locLatestDst = $reduceLoc($locRowsDst);

                // location names in one shot
                static $locNameCache = [];
                $allLocIds = [];
                foreach ([$locLatestSrc,$locLatestDst] as $map) {
                    foreach ($map as $arr) { foreach (array_keys($arr) as $lid) $allLocIds[$lid]=true; }
                }
                $missLoc = array_values(array_diff(array_keys($allLocIds), array_keys($locNameCache)));
                if (!empty($missLoc)) {
                    DB::table('service_location')->whereIn('id', $missLoc)->pluck('name','id')
                        ->each(function($name,$id) use (&$locNameCache){ $locNameCache[(int)$id] = $name ?: 'Unknown'; });
                }

                // convert loc maps to tooltip strings (>0 only)
                $locToStrings = function($arr) use ($locNameCache) {
                    $out = [];
                    foreach ($arr as $locId=>$bal) {
                        if ($bal > 0) $out[] = ($locNameCache[$locId] ?? 'Unknown') . ': ' . $bal;
                    }
                    return $out;
                };

                // helper (kept for completeness; not strictly needed below after totals)
                $buildBalances = function($key) use ($orgBalMap,$siteBalMapSrc,$siteBalMapDst,$locLatestSrc,$locLatestDst,$locToStrings) {
                    $b = [
                        'orgBalance' => $orgBalMap[$key] ?? 'N/A'
                    ];
                    if (isset($siteBalMapSrc[$key]))  $b['sourceSiteBalance']      = $siteBalMapSrc[$key];
                    if (isset($siteBalMapDst[$key]))  $b['destinationSiteBalance'] = $siteBalMapDst[$key];
                    if (isset($locLatestSrc[$key]))   $b['sourceLocBalance']       = json_encode($locToStrings($locLatestSrc[$key]));
                    if (isset($locLatestDst[$key]))   $b['destinationLocBalance']  = json_encode($locToStrings($locLatestDst[$key]));
                    return $b;
                };
            }

            // --- render table rows (keeps your structure) ---
            for ($i=0,$n=count($genericIds); $i<$n; $i++) {
                $gId = (int)($genericIds[$i] ?? 0);
                if (!$gId) continue;

                $bg   = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';
                $dQty = isset($demandQtyList[$i]) ? (float)$demandQtyList[$i] : 0;
                $tQty = isset($trxQtyList[$i])    ? (float)$trxQtyList[$i]    : null;

                // Status / action
                $respondedQty = 0;
                if (!$isDirectEntry) {
                    $respondedQty = (float)($__respondedQtys[$gId] ?? 0);
                }
                if ($isDirectEntry) {
                    $status='Completed'; $statusClass='success'; $actionBtn='N/A';
                } else {
                    if ($respondedQty > 0) {
                        if ($respondedQty >= $dQty) { $status='Completed'; $statusClass='success'; $actionBtn='N/A'; }
                        else { $status='Partially Completed'; $statusClass='info'; $actionBtn='<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn" data-id="'.$row->id.'" data-generic-id="'.$gId.'">Respond</a>'; }
                    } else {
                        $status='Pending'; $statusClass='warning'; $actionBtn='<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn" data-id="'.$row->id.'" data-generic-id="'.$gId.'">Respond</a>';
                    }
                }
                if ($respond != 1) { $actionBtn = '<code>Unauthorized Access</code>'; }

                /* -------------------------
                FIXED: tooltip brand/batch/expiry for direct entries
                ------------------------- */
                $brandName  = '';
                $batchNo    = '';
                $expiryDate = '';
                $batchDetails = $batchDetailsPerGen[$gId] ?? [];

                if ($isDirectEntry) {
                    // Use values aligned with current index $i
                    $brandId   = isset($brandIdsArr[$i]) ? (int)$brandIdsArr[$i] : 0;
                    $brandName = $brandCache[$brandId] ?? '';

                    $batchNo   = (string)($batchesArr[$i] ?? '');
                    $expRaw    = $expiryArr[$i] ?? '';
                    $expiryDate = $expRaw === '' ? '' :
                        (is_numeric($expRaw) ? \Carbon\Carbon::createFromTimestamp((int)$expRaw)->format('d-M-Y') : (string)$expRaw);

                    // If no prebuilt details, at least push one
                    if (empty($batchDetails)) {
                        $batchDetails[] = [
                            'brand'        => $brandName,
                            'batch'        => $batchNo,
                            'expiry'       => $expiryDate,
                            'respondedQty' => (float)($trxQtyList[$i] ?? 0),
                        ];
                    }
                } else {
                    // Requisition: show first batch in simple attributes; tooltip shows all
                    if (!empty($batchDetails)) {
                        $first = $batchDetails[0];
                        $brandName  = (string)($first['brand']  ?? '');
                        $batchNo    = (string)($first['batch']  ?? '');
                        $expiryDate = (string)($first['expiry'] ?? '');
                    }
                }

                $batchDetailsJson = htmlspecialchars(json_encode($batchDetails), ENT_QUOTES, 'UTF-8');

                // balances: totals per generic across all its tuples
                $balances = ['orgBalance'=>'N/A'];
                if (!empty($tuples)) {

                    $sumOrg = $sumSrc = $sumDst = 0;
                    $srcLocAgg = []; // locId => sum
                    $dstLocAgg = [];

                    foreach ($tuples as $k => $t) {
                        if ($t['g'] !== $gId) continue;

                        $sumOrg += (int)($orgBalMap[$k] ?? 0);
                        if (isset($siteBalMapSrc[$k])) $sumSrc += (int)$siteBalMapSrc[$k];
                        if (isset($siteBalMapDst[$k])) $sumDst += (int)$siteBalMapDst[$k];

                        if (isset($locLatestSrc[$k])) {
                            foreach ($locLatestSrc[$k] as $lid => $bal) {
                                $srcLocAgg[$lid] = ($srcLocAgg[$lid] ?? 0) + (int)$bal;
                            }
                        }
                        if (isset($locLatestDst[$k])) {
                            foreach ($locLatestDst[$k] as $lid => $bal) {
                                $dstLocAgg[$lid] = ($dstLocAgg[$lid] ?? 0) + (int)$bal;
                            }
                        }
                    }

                    $balances = ['orgBalance' => ($sumOrg > 0 ? $sumOrg : 'N/A')];

                    if (!empty($row->source_site)) {
                        $balances['sourceSiteBalance'] = ($sumSrc > 0 ? $sumSrc : 'N/A');
                        $srcLocStrings = [];
                        foreach ($srcLocAgg as $lid => $bal) {
                            if ($bal > 0) $srcLocStrings[] = ($locNameCache[$lid] ?? 'Unknown') . ': ' . $bal;
                        }
                        $balances['sourceLocBalance'] = json_encode(array_values($srcLocStrings));
                    }

                    if (!empty($row->destination_site)) {
                        $balances['destinationSiteBalance'] = ($sumDst > 0 ? $sumDst : 'N/A');
                        $dstLocStrings = [];
                        foreach ($dstLocAgg as $lid => $bal) {
                            if ($bal > 0) $dstLocStrings[] = ($locNameCache[$lid] ?? 'Unknown') . ': ' . $bal;
                        }
                        $balances['destinationLocBalance'] = json_encode(array_values($dstLocStrings));
                    }
                }

                // Build TR with data-* attributes
                $tableRows .= '<tr style="background-color:'.$bg.'; cursor:pointer;" class="other-transaction-balance"'
                    .' data-expiry="'.e($expiryDate).'"'
                    .' data-brand="'.e($brandName).'"'
                    .' data-batch="'.e($batchNo).'"'
                    .' data-batch-details="'.$batchDetailsJson.'"';

                foreach ($balances as $key=>$val) {
                    if ($key === 'batchDetails') continue;
                    $dataKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $key));
                    $tableRows .= ' data-'.$dataKey.'="' . htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') . '"';
                }

                $tableRows .= '>'
                    . '<td style="padding:8px;border:1px solid #ccc;">'.e($genericCache[$gId] ?? 'N/A').'</td>'
                    . '<td style="padding:8px;border:1px solid #ccc;">'.$dQty.'</td>';

                if ($isDirectEntry) {
                    $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.(($trxQtyList[$i] ?? '') === '' ? 'N/A' : e($trxQtyList[$i])).'</td>';
                } else {
                    $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.e($respondedQty ?? 0).'</td>';
                }

                $actionCell = $respond != 1 ? '<code>Unauthorized Access</code>' : $actionBtn;
                $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.$actionCell.'</td>'
                            . '<td style="padding:8px;border:1px solid #ccc;"><span class="label label-'.e($statusClass).'">'.e($status).'</span></td>'
                            . '</tr>';
            }

            // Header (unchanged)
            $tableHeader = '<tr>'
                .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>'
                .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">DemandQty</th>'
                .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">TransactionQty</th>'
                .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Action</th>'
                .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Status</th>'
                .'</tr>';

            return '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
                .'<thead style="background-color:#e2e8f0;color:#000;">'.$tableHeader.'</thead>'
                .'<tbody>'.$tableRows.'</tbody></table>';
        })
        ->rawColumns(['id_raw', 'id', 'InventoryDetails'])
        ->make(true);
    }

    public function RespondMaterialTransfer(Request $r)
    {
        $rights = $this->rights;
        $respond = explode(',', $rights->material_transfer)[2];
        if ($respond == 0) {
            abort(403, 'Forbidden');
        }

        $id        = $r->query('id');
        $genericId = $r->query('genericId');


        $RMT = RequisitionForMaterialTransfer::from('requisition_material_transfer as rmt')
        ->select([
            'rmt.id',
            'rmt.code',
            'rmt.org_id',
            'rmt.source_site',
            'rmt.source_location',
            'rmt.destination_site',
            'rmt.destination_location',
            'rmt.transaction_type_id',
            'rmt.generic_id',
            'rmt.qty',
            'rmt.status',
            'o.organization                as org_name',
            'source.name                   as sourceSiteName',
            'destination.name              as destinationSiteName',
            'sourceLocation.name           as sourceLocationName',
            'destinationLocation.name      as destinationLocationName',
            'itt.name                      as transaction_type_name',
            'itt.source_action             as source_action',
            'itt.destination_action        as destination_action',
            DB::raw('COALESCE(SUM(im.transaction_qty), 0) as issuedQty'),
            // 'ib.site_balance              as maxQty'
        ])
        ->join('organization                as o',   'o.id',  '=', 'rmt.org_id')
        ->leftJoin('org_site                    as source',   'source.id',  '=', 'rmt.source_site')
        ->leftJoin('org_site                    as destination',   'destination.id',  '=', 'rmt.destination_site')
        ->leftJoin('service_location        as sourceLocation',   'sourceLocation.id',  '=', 'rmt.source_location')
        ->leftJoin('service_location        as destinationLocation',   'destinationLocation.id',  '=', 'rmt.destination_location')
        ->join('inventory_transaction_type  as itt', 'itt.id',     '=', 'rmt.transaction_type_id')
        ->leftJoin('inventory_management as im', function($join) use ($genericId) {
            $join->on('im.ref_document_no', '=', 'rmt.code')
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
        ->where('rmt.id', $id)
        ->groupBy([
            'rmt.id', 'rmt.code', 'rmt.org_id', 'rmt.source_site', 'rmt.destination_site',
            'rmt.transaction_type_id', 'rmt.source_location',  'rmt.destination_location',
            'rmt.generic_id',
            'rmt.qty', 'rmt.status',
            'o.organization', 'source.name', 'destination.name', 'itt.name',
            'itt.source_action', 'itt.destination_action',
            'sourceLocation.name','destinationLocation.name',
            'ib.site_balance'
        ])
        ->first();

        if (! $RMT) {
            return response()->json(['error'=>'Record not found'], 404);
        }

        $gIds = explode(',', $RMT->generic_id);
        $qtys = explode(',', $RMT->qty);
        $i    = array_search($genericId, $gIds);

        $genericName = InventoryGeneric::find($gIds[$i])->name ?? '';
        $originalQty = $qtys[$i] ?? 0;
        $issuedQty = $RMT->issuedQty;
        $maxQty = $RMT->maxQty;
        // dd($maxQty);
        $remainingQty = max(0, $originalQty - $issuedQty);
        // dd($originalQty,$issuedQty,$remainingQty);
        return response()->json([
            'code'                   => $RMT->code,
            'org_id'                 => $RMT->org_id,
            'org_name'               => $RMT->org_name,
            'source_action'          => $RMT->source_action,
            'destination_action'     => $RMT->destination_action,
            'source_site'            => $RMT->source_site,
            'sourceSiteName'         => $RMT->sourceSiteName,
            'destination_site'       => $RMT->destination_site,
            'destinationSiteName'    => $RMT->destinationSiteName,
            'transaction_type_id'    => $RMT->transaction_type_id,
            'transaction_type_name'  => $RMT->transaction_type_name,
            'source_location'        => $RMT->source_location,
            'sourceLocationName'     => $RMT->sourceLocationName,
            'destination_location'   => $RMT->destination_location,
            'destinationLocationName'=> $RMT->destinationLocationName,
            'generic_id'             => $gIds[$i]      ?? null,
            'generic_name'           => $genericName,
            'brand_id'               => null,
            'brand_name'             => '',
            // 'demand_qty'             => $qtys[$i]      ?? '',
            'demand_qty'             => $remainingQty      ?? '0',
            'max_qty'             => $maxQty      ?? '0',
        ]);

        return response()->json(['error'=>'Unknown source'], 400);
    }

    public function AddMaterialTransfer(MaterialTransferRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->material_transfer)[0];
        $respond = explode(',', $rights->material_transfer)[2];
        if ($add != 1 && $respond != 1) {
            abort(403, 'Forbidden');
        }

        // Get validated data
        $validated = $request->validated();

        $itemCount = count($request->mt_generic);

        $success = true;
        $message = '';

        $inventory = new InventoryManagement();
        // dd($validated);
        // Required fields
        $inventory->transaction_type_id = $validated['mt_transactiontype'];
        $inventory->org_id = $validated['mt_org'];
        $inventory->site_id = $validated['mt_source_site'];
        $inventory->source = $validated['mt_source_location'];
        $inventory->d_site_id = $validated['mt_destination_site'];
        $inventory->destination = $validated['mt_destination_location'];

        // Optional reference document
        $inventory->ref_document_no = $validated['mt_reference_document'] ?? null;

        // Remarks field
        $inventory->remarks = $validated['mt_remarks'] ?? null;

        // Handle item specific fields based on count
        if ($itemCount > 1) {
            $inventory->inv_generic_id = implode(',', $validated['mt_generic']);
            $inventory->brand_id = implode(',', $validated['mt_brand']);
            $inventory->batch_no = implode(',', $validated['mt_batch']);
            $inventory->demand_qty = implode(',', $validated['mt_demand_qty']);

            $formattedDates = array_map(function($date) {
                return Carbon::createFromFormat('Y-m-d', $date)->timestamp;
            }, $validated['mt_expiry']);
            $inventory->expiry_date = implode(',', $formattedDates);

            $inventory->transaction_qty = implode(',', $validated['mt_qty']);
        } else {
            $inventory->inv_generic_id = $validated['mt_generic'][0];
            $inventory->brand_id = $validated['mt_brand'][0];
            $inventory->batch_no = $validated['mt_batch'][0];
            $inventory->expiry_date = Carbon::createFromFormat('Y-m-d', $validated['mt_expiry'][0])->timestamp;
            $inventory->transaction_qty = $validated['mt_qty'][0];
            $inventory->demand_qty = $validated['mt_demand_qty'][0];
        }

        $inventory->status = 1;
        $inventory->user_id = auth()->id();
        $inventory->logid = auth()->user()->username ?? auth()->id();
        $inventory->effective_timestamp = now()->timestamp;
        $inventory->timestamp = now()->timestamp;
        $inventory->last_updated = now()->timestamp;

        // Updated: remove deprecated column 'applicable_location_to'
        $rule = DB::table('inventory_transaction_type')
        ->select('source_action', 'destination_action', 'source_location_type', 'destination_location_type')
        ->where('id', $validated['mt_transactiontype'])
        ->first();

        // Decide action safely without the removed column
        $useAction = (isset($rule->destination_action) && in_array($rule->destination_action, ['a','s','r']))
            ? $rule->destination_action
            : $rule->source_action;

        $sourceType = DB::table('inventory_source_destination_type')->where('id', $rule->source_location_type)->value('name');
        $destinationType = DB::table('inventory_source_destination_type')->where('id', $rule->destination_location_type)->value('name');

        foreach ($validated['mt_generic'] as $i => $genId) {
            $brandId = $validated['mt_brand'][$i];
            $batchNo = $validated['mt_batch'][$i];
            $qty = (int) $validated['mt_qty'][$i];
            // dd($rule->source_action, $rule->destination_action, $sourceType, $destinationType, $genId, $brandId, $batchNo, $qty);
            // dd($sourceType, $validated['mt_source'], $validated['mt_org'], $validated['mt_site'], $genId, $brandId, $batchNo, $qty);
            if (strtolower($sourceType) === 'inventory location' && $validated['mt_source_location'] && in_array($rule->source_action, ['s', 'r'])) {
                $sourceLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['mt_org'])
                    ->where('site_id', $validated['mt_source_site'])
                    ->where('location_id', $validated['mt_source_location'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $sourceLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient source location balance. Available: $sourceLocBalance, Requested: $qty"
                    ]);
                }
            }

            if (strtolower($destinationType) === 'inventory location' && $validated['mt_destination_location'] && in_array($rule->destination_action, ['s', 'r'])) {
                $destinationLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['mt_org'])
                    ->where('site_id', $validated['mt_destination_site'])
                    ->where('location_id', $validated['mt_destination_location'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $destinationLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient destination location balance. Available: $destinationLocBalance, Requested: $qty"
                    ]);
                }
            }
        }

        if (!$inventory->save()) {
            $success = false;
            $message = 'Failed to save inventory record';
        }

        // Process each item separately for inventory_balance
        for ($i = 0; $i < $itemCount; $i++) {
            $genId = $validated['mt_generic'][$i];
            $brandId = $validated['mt_brand'][$i];
            $batchNo = $validated['mt_batch'][$i];
            $qty = (int)$validated['mt_qty'][$i];

            // $expTs = Carbon::createFromFormat('Y-m-d', $validated['mt_expiry'][$i])->timestamp;
            if (! $genId || ! $brandId || ! $batchNo) {
                continue;
            }

            $prevOrgRow = InventoryBalance::where('generic_id', $genId)
            ->where('brand_id',  $brandId)
            ->where('batch_no',  $batchNo)
            ->where('org_id',    $validated['mt_org'])
            ->orderBy('id', 'desc')
            ->first();
            // $OrgBalance = $prevOrgRow->org_balance ?? 0;
            $prevOrgBalance = $prevOrgRow->org_balance ?? 0;
            $newOrgBalance  = $prevOrgBalance;

            $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');

            if (strtolower($sourceType) === 'inventory location' && $validated['mt_source_location'] && strtolower($destinationType) === 'inventory location' && $validated['mt_destination_location']) {

                $prevOrgRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['mt_org'])
                ->orderBy('id', 'desc')
                ->first();
                // $OrgBalance = $prevOrgRow->org_balance ?? 0;
                $prevOrgBalance = $prevOrgRow->org_balance ?? 0;
                $newOrgBalance  = $prevOrgBalance;

                $prevSourceSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['mt_org'])
                ->where('site_id',   $validated['mt_source_site'])
                ->orderBy('id', 'desc')
                ->first();
                $prevSourceSiteBalance = $prevSourceSiteRow->site_balance ?? 0;
                $newSourceSiteBalance = $prevSourceSiteBalance;

                $remarkText = "Material Transfer initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSourceSiteBalance}";

                // Source location row
                $prevSourceLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['mt_org'])
                    ->where('site_id',   $validated['mt_source_site'])
                    ->where('location_id', $validated['mt_source_location'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevSourceLocBalance = $prevSourceLocRow->location_balance ?? 0;

                // if (in_array($rule->source_action, ['s', 'r']) && $qty > $prevSourceLocBalance) {
                //     return response()->json([
                //         'info' => "Insufficient source location balance. Available: $prevSourceLocBalance, Requested: $qty"
                //     ]);
                // }


                if ($rule->source_action === 'a') {
                    $newSourceLocBalance = $prevSourceLocBalance + $qty;
                    $newSourceSiteBalance += $qty;
                    $newOrgBalance  = $newOrgBalance  + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSourceLocBalance = $prevSourceLocBalance - $qty;
                    $newSourceSiteBalance -= $qty;
                    //  dd($newOrgBalance, $newSourceSiteBalance, $newSourceLocBalance, $remarkText);

                    $newOrgBalance  = $newOrgBalance  - $qty;
                    //  dd($newOrgBalance, $newSourceSiteBalance, $newSourceLocBalance, $remarkText);
                } else {
                    $newSourceLocBalance = $prevSourceLocBalance;
                    $newSourceSiteBalance = $prevSourceSiteBalance;
                    $newOrgBalance  = $newOrgBalance;
                }
                // dd($newOrgBalance, $newSourceSiteBalance, $newSourceLocBalance, $remarkText);

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['mt_org'],
                    'site_id'          => $validated['mt_source_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSourceSiteBalance,
                    'location_id'      => $validated['mt_source_location'],
                    'location_balance' => $newSourceLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);

                // Destination location row


                $prevOrgRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['mt_org'])
                ->orderBy('id', 'desc')
                ->first();
                // $OrgBalance = $prevOrgRow->org_balance ?? 0;
                $prevOrgBalance = $prevOrgRow->org_balance ?? 0;
                $newOrgBalance  = $prevOrgBalance;

                $prevDestinationSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['mt_org'])
                ->where('site_id',   $validated['mt_destination_site'])
                ->orderBy('id', 'desc')
                ->first();
                $prevDestinatonSiteBalance = $prevDestinationSiteRow->site_balance ?? 0;
                $newDestinatioinSiteBalance = $prevDestinatonSiteBalance;
                $remarkText = "Material Transfer initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newDestinatioinSiteBalance}";


                $prevDestLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['mt_org'])
                    ->where('site_id',   $validated['mt_destination_site'])
                    ->where('location_id', $validated['mt_destination_location'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevDestLocBalance = $prevDestLocRow->location_balance ?? 0;

                if ($rule->destination_action === 'a') {
                    $newDestLocBalance = $prevDestLocBalance + $qty;
                    $newDestinatioinSiteBalance += $qty;
                    $newOrgBalance  = $newOrgBalance  + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newDestLocBalance = $prevDestLocBalance - $qty;
                    $newDestinatioinSiteBalance += $qty;
                    $newOrgBalance  = $newOrgBalance  - $qty;
                } else {
                    $newDestLocBalance = $prevDestLocBalance;
                    $newDestinatioinSiteBalance = $prevDestinatonSiteBalance;
                    $newOrgBalance  = $prevOrgBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['mt_org'],
                    'site_id'          => $validated['mt_destination_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newDestinatioinSiteBalance,
                    'location_id'      => $validated['mt_destination_location'],
                    'location_balance' => $newDestLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($sourceType) === 'inventory location' && $validated['mt_source_location']) {

                $prevSourceSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['mt_org'])
                ->where('site_id',   $validated['mt_source_site'])
                ->orderBy('id', 'desc')
                ->first();
                $prevSourceSiteBalance = $prevSourceSiteRow->site_balance ?? 0;
                $newSourceSiteBalance = $prevSourceSiteBalance;


                $remarkText = "Material Transfer initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSourceSiteBalance}";

                $prevSourceLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['mt_org'])
                    ->where('site_id',   $validated['mt_source_site'])
                    ->where('location_id', $validated['mt_source_location'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevSourceLocBalance = $prevSourceLocRow->location_balance ?? 0;

                // if (in_array($rule->source_action, ['s', 'r']) && $qty > $prevLocBalance) {
                //     return response()->json([
                //         'info' => "Insufficient source location balance. Available: $prevLocBalance, Requested: $qty"
                //     ]);
                // }

                if ($rule->source_action === 'a') {
                    $newSourceLocBalance = $prevSourceLocBalance + $qty;
                    $newSourceSiteBalance += $qty;
                    $newOrgBalance  = $prevOrgBalance  + $qty;

                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSourceLocBalance = $prevSourceLocBalance - $qty;
                    $newSourceSiteBalance -= $qty;
                    $newOrgBalance  = $prevOrgBalance  - $qty;
                } else {
                    $newSourceLocBalance = $prevSourceLocBalance;
                    $newSourceSiteBalance = $prevSourceSiteBalance;
                    $newOrgBalance  = $prevOrgBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['mt_org'],
                    'site_id'          => $validated['mt_source_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSourceSiteBalance,
                    'location_id'      => $validated['mt_source_location'],
                    'location_balance' => $newSourceLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($destinationType) === 'inventory location' && $validated['mt_destination_location']) {

                $prevDestinationSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['mt_org'])
                ->where('site_id',   $validated['mt_destination_site'])
                ->orderBy('id', 'desc')
                ->first();
                $prevDestinationSiteBalance = $prevDestinationSiteRow->site_balance ?? 0;
                $newDestinationSiteBalance = $prevDestinationSiteBalance;

                $remarkText = "Material Transfer initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newDestinatioinSiteBalance}";


                $prevDestinationLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['mt_org'])
                    ->where('site_id',   $validated['mt_destination_site'])
                    ->where('location_id', $validated['mt_destination_location'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevDestinationLocBalance = $prevDestinationLocRow->location_balance ?? 0;

                // if (in_array($rule->destination_action, ['s', 'r']) && $qty > $prevLocBalance) {
                //     return response()->json([
                //         'info' => "Insufficient destination location balance. Available: $prevLocBalance, Requested: $qty"
                //     ]);
                // }

                // if ($rule->destination_action === 'a') {
                //     $newLocBalance = $prevLocBalance + $qty;
                // } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                //     $newLocBalance = $prevLocBalance - $qty;
                // } else {
                //     $newLocBalance = $prevLocBalance;
                // }

                if ($rule->source_action === 'a') {
                    $newDestinationLocBalance = $prevDestinationLocBalance + $qty;
                    $newDestinationSiteBalance += $qty;
                    $newOrgBalance  = $prevOrgBalance  + $qty;

                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newDestinationLocBalance = $prevDestinationLocBalance - $qty;
                    $newDestinationSiteBalance -= $qty;
                    $newOrgBalance  = $prevOrgBalance  - $qty;
                } else {
                    $newDestinationLocBalance = $prevDestinationLocBalance;
                    $newDestinationSiteBalance = $prevDestinationSiteBalance;
                    $newOrgBalance  = $prevOrgBalance;
                }


                // dd($newLocBalance, $Destination)
                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['mt_org'],
                    'site_id'          => $validated['mt_destination_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newDestinationSiteBalance,
                    'location_id'      => $validated['mt_destination_location'],
                    'location_balance' => $newDestinationLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            else {
                $prevSourceSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['mt_org'])
                ->where('site_id',   $validated['mt_source_site'])
                ->orderBy('id', 'desc')
                ->first();
                $SourceSiteBalance = $prevSourceSiteRow->site_balance ?? 0;

                $remarkText = "Material Transfer initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$OrgBalance} | New Site Balance: {$SourceSiteBalance}";

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['mt_org'],
                    'site_id'          => $validated['mt_source_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $SourceSiteBalance,
                    'location_id'      => null,
                    'location_balance' => null,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }

        }

        if ($success) {
            return response()->json([
                'success' => 'Material Transfer records have been added successfully',
                'reload' => true
            ]);
        } else {
            return response()->json([
                'error' => $message,
                'reload' => false
            ]);
        }
    }

    public function ShowConsumedData()
    {
        $colName = 'consumption';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $empId = $user->emp_id;

        // $costcenters = DB::table('emp_cc as e')
        // ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
        // ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
        // ->where('e.emp_id', $empId)
        // ->where('ct.performing', 1)
        // ->select('c.id', 'c.name')
        // ->get();

         $costcenters = DB::table(function ($query) use ($empId) {
            $query->select('c.id', 'c.name')
                ->from('emp_cc as e')
                ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
                ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                ->where('e.emp_id', $empId)
                ->where('ct.performing', 1)

            ->unionAll(
                DB::table('employee as emp')
                    ->select('c.id', 'c.name')
                    ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, emp.cc_id)'), '>', DB::raw('0'))
                    ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                    ->where('emp.id', $empId)
                    ->where('ct.performing', 1)
            );
        }, 'combined')
        ->distinct()
        ->get();

        $RequisitionNonMandatory = DB::table('inventory_transaction_type AS itt')
        ->join('inventory_transaction_activity AS ita', 'ita.id', '=', 'itt.activity_type')
        ->where('ita.name', 'Issue & Dispense')
        ->where('itt.request_mandatory', 'n')
        ->exists();

        $MedicationRoutes = MedicationRoutes::select('id', 'name')->where('status', 1)->get();
        $MedicationFrequencies = MedicationFrequency::select('id', 'name')->where('status', 1)->get();

        return view('dashboard.material_management.consumption', compact('user','RequisitionNonMandatory','costcenters','MedicationRoutes','MedicationFrequencies'));
    }

    
    public function GetConsumptionData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->consumption)[1];
        if ($view == 0) abort(403, 'Forbidden');

        // -------------------------------------------------
        // Resolve activity IDs once (index-friendly)
        // -------------------------------------------------
        $issueDispenseActivityIds = DB::table('inventory_transaction_activity')
            ->where(function ($q) {
                $q->where('name', 'like', '%issue%')
                ->orWhere('name', 'like', '%dispense%');
            })
            ->pluck('id')->all();
        if (empty($issueDispenseActivityIds)) $issueDispenseActivityIds = [-1];

        // -------------------------------------------------
        // Base query: ISSUE/DISPENSE only (no ->get())
        // -------------------------------------------------
        $IssuedData = DB::table('inventory_management as im')
            ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
            ->join('inventory_source_destination_type as isdt_src', 'isdt_src.id', '=', 'itt.source_location_type')
            ->join('inventory_source_destination_type as isdt_dest', 'isdt_dest.id', '=', 'itt.destination_location_type')
            ->join('inventory_transaction_activity as ita', 'ita.id', '=', 'itt.activity_type')
            ->join('organization', 'organization.id', '=', 'im.org_id')
            ->join('org_site', 'org_site.id', '=', 'im.site_id')
            ->leftJoin('patient', 'patient.mr_code', '=', 'im.mr_code')
            ->leftJoin('employee', 'employee.id', '=', 'im.resp_physician')
            ->leftJoin('service_mode', 'service_mode.id', '=', 'im.service_mode_id')
            ->leftJoin('services', 'services.id', '=', 'im.service_id')
            ->leftJoin('service_group', 'service_group.id', '=', 'services.group_id')
            ->leftJoin('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->leftJoin('costcenter as billingCC', 'billingCC.id', '=', 'im.billing_cc')
            // index-friendly filters
            ->whereIn('ita.id', $issueDispenseActivityIds)
            ->where('itt.name', 'like', '%issue%') // keep if you truly need only "issue" types
            ->select([
                // im core
                'im.id','im.transaction_type_id','im.status','im.org_id','im.site_id',
                'im.effective_timestamp','im.timestamp','im.last_updated','im.logid',

                // im strings (no heavy CONVERT — rely on table collation)
                'im.dose','im.route_id','im.frequency_id','im.duration as days',
                'im.ref_document_no as referenceNumber','im.mr_code',
                'im.brand_id','im.batch_no','im.expiry_date','im.demand_qty',
                'im.transaction_qty','im.inv_generic_id as inv_generic_ids',
                'im.source as Source','im.destination as Destination','im.remarks',

                // location type names
                'isdt_src.name as sourceTypeName',
                'isdt_dest.name as destinationTypeName',

                // joined names
                DB::raw('patient.name as patientName'),
                DB::raw('employee.name as Physician'),
                DB::raw('organization.organization as OrgName'),
                DB::raw('org_site.name as SiteName'),
                DB::raw('services.name as serviceName'),
                DB::raw('service_mode.name as serviceMode'),
                DB::raw('billingCC.name as billingCC'),
                DB::raw('service_group.name as serviceGroup'),
                DB::raw('service_type.name as serviceType'),
                DB::raw('itt.name as TransactionType'),

                // fixed 'inventory' tag for consistency with your renderer
                DB::raw("'inventory' as source"),
            ]);

        // Session-site filter early (push-down)
        if ($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if (!empty($sessionSiteIds)) {
                $IssuedData->whereIn('org_site.id', $sessionSiteIds);
            }
        }

        // -------------------------------------------------
        // DataTables: server-side (no collection build)
        // -------------------------------------------------
        return DataTables::query($IssuedData)

            // ---------- Optional filters (same style as your other endpoints) ----------
            ->filter(function ($query) use ($request) {
                $from    = $request->input('from_date');
                $to      = $request->input('to_date');
                $orgIds  = (array) $request->input('org_ids', []);
                $siteIds = (array) $request->input('site_ids', []);
                $ttIds   = (array) $request->input('transaction_type_ids', []);

                $ref      = trim((string) $request->input('reference'));
                $mr       = trim((string) $request->input('mr_code'));
                $patient  = trim((string) $request->input('patient'));
                $phys     = trim((string) $request->input('physician'));
                $service  = trim((string) $request->input('service'));
                $svcMode  = trim((string) $request->input('service_mode'));
                $svcGroup = trim((string) $request->input('service_group'));
                $billing  = trim((string) $request->input('billing_cc'));
                $siteName = trim((string) $request->input('site_name'));
                $remarks  = trim((string) $request->input('remarks'));
                $srcType  = trim((string) $request->input('source_type'));      // matches isdt_src.name
                $dstType  = trim((string) $request->input('destination_type')); // matches isdt_dest.name
                $generic  = trim((string) $request->input('generic_name'));     // via inventory_generic & FIND_IN_SET

                if ($from) {
                    $query->where('im.timestamp', '>=', \Carbon\Carbon::parse($from)->startOfDay()->timestamp);
                }
                if ($to) {
                    $query->where('im.timestamp', '<=', \Carbon\Carbon::parse($to)->endOfDay()->timestamp);
                }
                if ($orgIds)  { $query->whereIn('im.org_id', $orgIds); }
                if ($siteIds) { $query->whereIn('im.site_id', $siteIds); }
                if ($ttIds)   { $query->whereIn('im.transaction_type_id', $ttIds); }

                if ($ref)      { $query->where('im.ref_document_no', 'like', "%{$ref}%"); }
                if ($mr)       { $query->where('im.mr_code', 'like', "%{$mr}%"); }
                if ($patient)  { $query->where('patient.name', 'like', "%{$patient}%"); }
                if ($phys)     { $query->where('employee.name', 'like', "%{$phys}%"); }
                if ($service)  { $query->where('services.name', 'like', "%{$service}%"); }
                if ($svcMode)  { $query->where('service_mode.name', 'like', "%{$svcMode}%"); }
                if ($svcGroup) { $query->where('service_group.name', 'like', "%{$svcGroup}%"); }
                if ($billing)  { $query->where('billingCC.name', 'like', "%{$billing}%"); }
                if ($siteName) { $query->where('org_site.name', 'like', "%{$siteName}%"); }
                if ($remarks)  { $query->where('im.remarks', 'like', "%{$remarks}%"); }
                if ($srcType)  { $query->where('isdt_src.name', 'like', "%{$srcType}%"); }
                if ($dstType)  { $query->where('isdt_dest.name', 'like', "%{$dstType}%"); }

                // Generic name filter across CSV inv_generic_id using EXISTS + FIND_IN_SET
                if ($generic) {
                    $g = "%{$generic}%";
                    $query->whereRaw("
                        EXISTS (
                        SELECT 1
                        FROM inventory_generic ig
                        WHERE ig.name LIKE ?
                            AND FIND_IN_SET(ig.id, im.inv_generic_id)
                        )", [$g]
                    );
                }

                // Global search
                $global = trim((string) data_get($request->input('search'), 'value'));
                if ($global !== '') {
                    $g = "%{$global}%";
                    $query->where(function ($qq) use ($g) {
                        $qq->orWhere('im.ref_document_no', 'like', $g)
                        ->orWhere('patient.name', 'like', $g)
                        ->orWhere('employee.name', 'like', $g)
                        ->orWhere('organization.organization', 'like', $g)
                        ->orWhere('org_site.name', 'like', $g)
                        ->orWhere('services.name', 'like', $g)
                        ->orWhere('service_mode.name', 'like', $g)
                        ->orWhere('billingCC.name', 'like', $g)
                        ->orWhere('service_group.name', 'like', $g)
                        ->orWhere('service_type.name', 'like', $g)
                        ->orWhere('itt.name', 'like', $g)
                        ->orWhere('isdt_src.name', 'like', $g)
                        ->orWhere('isdt_dest.name', 'like', $g)
                        ->orWhere('im.mr_code', 'like', $g)
                        ->orWhere('im.remarks', 'like', $g)
                        ->orWhereRaw("
                            EXISTS (
                                SELECT 1 FROM inventory_generic ig
                                WHERE FIND_IN_SET(ig.id, im.inv_generic_id)
                                AND ig.name LIKE ?
                            )", [$g]
                        );
                    });
                }
            })

            // Default order (latest first) if none provided
            ->order(function ($q) use ($request) {
                if (!count($request->input('order', []))) {
                    $q->orderBy('im.timestamp', 'desc');
                }
            })

            // ----------------- Your original renderers -----------------
            ->addColumn('id_raw', fn($row) => $row->id)

            ->editColumn('id', function ($row) {
                static $locNameCache = [];
                $getLocName = function ($id) use (&$locNameCache) {
                    $id = (int) $id;
                    if ($id <= 0) return null;
                    if (!array_key_exists($id, $locNameCache)) {
                        $locNameCache[$id] = DB::table('service_location')->where('id', $id)->value('name');
                    }
                    return $locNameCache[$id];
                };

                $timestamp     = \Carbon\Carbon::createFromTimestamp($row->timestamp)->format('l d F Y - h:i A');
                $effectiveDate = \Carbon\Carbon::createFromTimestamp($row->effective_timestamp)->format('l d F Y - h:i A');
                $RequisitionCode = empty($row->referenceNumber) ? 'Ref: N/A' : 'Ref: ' . $row->referenceNumber;

                $Location = '';
                if (!empty($row->sourceTypeName) && str_contains(mb_strtolower($row->sourceTypeName), 'location')) {
                    if ($srcName = $getLocName($row->Source)) {
                        $Location .= '<b>Source Location</b>: ' . ucwords($srcName) . '<br>';
                    }
                }
                if (!empty($row->destinationTypeName) && str_contains(mb_strtolower($row->destinationTypeName), 'location')) {
                    if ($dstName = $getLocName($row->Destination)) {
                        $Location .= '<b>Destination Location</b>: ' . ucwords($dstName) . '<br>';
                    }
                }

                return $RequisitionCode
                    . '<hr class="mt-1 mb-2">'
                    .  (ucwords($row->TransactionType) ?? 'N/A') . '<br>'
                    .  $Location
                    . '<b>Site</b>: ' . ($row->SiteName ?? 'N/A') . '<br>'
                    . '<b>Issue Date </b>: ' . $timestamp . '<br>'
                    . '<b>Effective Date </b>: ' . $effectiveDate . '<br>'
                    . '<b>Remarks</b>: ' . ($row->remarks ?: 'N/A');
            })

            ->editColumn('patientDetails', function ($row) {
                if (empty($row->mr_code)) return 'N/A';

                return '<b>MR#:</b> '.$row->mr_code.'<br>'.ucwords($row->patientName).'<hr class="mt-1 mb-2">'
                    .'<b>Service Mode</b>: '.$row->serviceMode.'<br>'
                    .'<b>Service Group</b>: '.$row->serviceGroup.'<br>'
                    .'<b>Service</b>: '.$row->serviceName.'<br>'
                    .'<b>Responsible Physician</b>: '.ucwords($row->Physician).'<br>'
                    .'<b>Billing CC</b>: '.$row->billingCC;
            })

            // Keep your existing right-column logic (it already caches per-request).
            ->editColumn('InventoryDetails', function ($row) {
                /* ---- paste your existing InventoryDetails block unchanged ---- */
                // (Using your exact code from the question.)
                // The big win comes from server-side pagination above;
                // your per-request caches keep this part efficient.
                // ---------------------------------------------------------------
                // --------- STATIC CACHES (request lifetime) ----------
                static $rightsRespond = null;

                static $genericNameCache = [];      // [generic_id => name]
                static $brandNameCache   = [];      // [brand_id => name]
                static $locNameCache     = [];      // [location_id => name]

                static $respondedByRefCache = [];   // [ref => [generic_id => SUM(transaction_qty)]] (consumption)
                static $imRefMapCache = [];        // [ref => [generic_id => ['brand_id','batch_no','expiry_date']]]
                static $balanceCache  = [];        // ["org|site|generic|brand|batch|locsKey" => ['orgBalance','siteBalance','locBalanceJson']]

                if ($rightsRespond === null) {
                    $Rights = $this->rights;
                    $rightsRespond = (int) explode(',', $Rights->consumption)[0];
                }

                $getBrandName = function ($brandId) use (&$brandNameCache) {
                    $brandId = (int) $brandId;
                    if ($brandId <= 0) return '';
                    if (!array_key_exists($brandId, $brandNameCache)) {
                        $brandNameCache[$brandId] = (string) (DB::table('inventory_brand')->where('id', $brandId)->value('name') ?? '');
                    }
                    return $brandNameCache[$brandId];
                };
                $getLocName = function ($id) use (&$locNameCache) {
                    $id = (int) $id;
                    if ($id <= 0) return null;
                    if (!array_key_exists($id, $locNameCache)) {
                        $locNameCache[$id] = DB::table('service_location')->where('id', $id)->value('name');
                    }
                    return $locNameCache[$id];
                };

                $preferredLocations = [];
                if (!empty($row->sourceTypeName) && str_contains(mb_strtolower($row->sourceTypeName), 'location')) {
                    $name = $getLocName($row->Source);
                    if ($name) $preferredLocations[] = $name;
                }
                if (!empty($row->destinationTypeName) && str_contains(mb_strtolower($row->destinationTypeName), 'location')) {
                    $name = $getLocName($row->Destination);
                    if ($name) $preferredLocations[] = $name;
                }
                $preferredLocations = array_values(array_unique(array_filter($preferredLocations)));

                $getRespondedConsumption = function ($ref) use (&$respondedByRefCache) {
                    if (empty($ref)) return [];
                    if (!array_key_exists($ref, $respondedByRefCache)) {
                        $respondedByRefCache[$ref] = DB::table('inventory_management as im')
                            ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
                            ->where('im.ref_document_no', $ref)
                            ->where('itt.name', 'like', '%consumption%')
                            ->groupBy('im.inv_generic_id')
                            ->select('im.inv_generic_id', DB::raw('SUM(im.transaction_qty) as total_qty'))
                            ->pluck('total_qty', 'im.inv_generic_id')
                            ->toArray();
                    }
                    return $respondedByRefCache[$ref];
                };

                $getIMRefConsumptionMap = function ($ref) use (&$imRefMapCache) {
                    if (empty($ref)) return [];
                    if (!array_key_exists($ref, $imRefMapCache)) {
                        $rows = DB::table('inventory_management as im')
                            ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
                            ->where('im.ref_document_no', $ref)
                            ->where('itt.name', 'like', '%consumption%')
                            ->select('im.inv_generic_id', 'im.brand_id', 'im.batch_no', 'im.expiry_date')
                            ->get();
                        $map = [];
                        foreach ($rows as $r) {
                            $map[(int) $r->inv_generic_id] = [
                                'brand_id'    => $r->brand_id,
                                'batch_no'    => $r->batch_no,
                                'expiry_date' => $r->expiry_date,
                            ];
                        }
                        $imRefMapCache[$ref] = $map;
                    }
                    return $imRefMapCache[$ref];
                };

                $getBalances = function ($orgId, $siteId, $genericId, $brandId = null, $batchNo = null, array $preferredLocationsArg = [])
                    use (&$balanceCache, $getLocName) {

                    $brandKey = ($brandId === null) ? '*' : $brandId;
                    $batchKey = ($batchNo === null) ? '*' : $batchNo;
                    $locKey   = implode('|', array_map(fn($n) => mb_strtolower(trim($n)), $preferredLocationsArg));
                    $cacheKey = "{$orgId}|{$siteId}|{$genericId}|{$brandKey}|{$batchKey}|{$locKey}";
                    if (isset($balanceCache[$cacheKey])) return $balanceCache[$cacheKey];

                    $result = ['orgBalance' => 'N/A', 'siteBalance' => 'N/A', 'locBalance' => '[]'];

                    if ($brandId !== null && $batchNo !== null) {
                        $orgBal = DB::table('inventory_balance')
                            ->where('org_id', $orgId)->where('generic_id', $genericId)
                            ->where('brand_id', $brandId)->where('batch_no', $batchNo)
                            ->orderBy('id', 'desc')->value('org_balance');

                        $siteBal = DB::table('inventory_balance')
                            ->where('org_id', $orgId)->where('site_id', $siteId)
                            ->where('generic_id', $genericId)->where('brand_id', $brandId)->where('batch_no', $batchNo)
                            ->orderBy('id', 'desc')->value('site_balance');

                        $rows = DB::table('inventory_balance')
                            ->where('org_id', $orgId)->where('site_id', $siteId)
                            ->where('generic_id', $genericId)->where('brand_id', $brandId)->where('batch_no', $batchNo)
                            ->whereNotNull('location_id')->orderBy('id', 'desc')->get();

                        $latestPerLoc = [];
                        foreach ($rows as $r) {
                            if (!isset($latestPerLoc[$r->location_id])) $latestPerLoc[$r->location_id] = $r;
                        }

                        $nameFilter = null;
                        if (!empty($preferredLocationsArg)) {
                            $nameFilter = [];
                            foreach ($preferredLocationsArg as $nm) {
                                $nm = mb_strtolower(trim($nm));
                                if ($nm !== '') $nameFilter[$nm] = true;
                            }
                        }

                        $locEntries = [];
                        foreach ($latestPerLoc as $locId => $r) {
                            $bal = (float)($r->location_balance ?? 0);
                            if ($bal <= 0) continue;
                            $locName = $getLocName($locId) ?? 'Unknown';
                            if ($nameFilter) {
                                $key = mb_strtolower(trim($locName));
                                if (!isset($nameFilter[$key])) continue;
                            }
                            $locEntries[] = $locName . ': ' . $bal;
                        }

                        $result = [
                            'orgBalance'  => $orgBal ?? 'N/A',
                            'siteBalance' => $siteBal ?? 'N/A',
                            'locBalance'  => htmlspecialchars(json_encode(array_values($locEntries)), ENT_QUOTES, 'UTF-8'),
                        ];
                    }
                    return $balanceCache[$cacheKey] = $result;
                };

                $tableRows = '';

                $genericIds = array_filter(array_map('intval', explode(',', (string) $row->inv_generic_ids)));
                // static $genericNameCache = [];
                $missing = array_values(array_diff($genericIds, array_keys($genericNameCache)));
                if ($missing) {
                    $fetched = InventoryGeneric::whereIn('id', $missing)->pluck('name', 'id')->toArray();
                    foreach ($fetched as $gid => $name) $genericNameCache[(int)$gid] = $name;
                    foreach ($missing as $gid) if (!isset($genericNameCache[$gid])) $genericNameCache[$gid] = 'N/A';
                }

                $dose         = !empty($row->dose)         ? explode(',', (string) $row->dose) : [];
                $routeIds     = !empty($row->route_id)     ? explode(',', (string) $row->route_id) : [];
                $frequencyIds = !empty($row->frequency_id) ? explode(',', (string) $row->frequency_id) : [];
                $days         = !empty($row->days)         ? explode(',', (string) $row->days) : [];

                $isMedication = (!empty($row->dose) || !empty($row->route_id) || !empty($row->frequency_id) || !empty($row->days));
                $count = max(count($genericIds), count($dose), count($routeIds), count($frequencyIds), count($days));

                $respondedEntriesDetails = !empty($row->referenceNumber) ? $getRespondedConsumption($row->referenceNumber) : [];
                $refMap = !empty($row->referenceNumber) ? $getIMRefConsumptionMap($row->referenceNumber) : [];

                for ($i = 0; $i < $count; $i++) {
                    $bg = $i % 2 === 0 ? '#f9f9f9' : '#ffffff';
                    $currentGenericId = (int) ($genericIds[$i] ?? 0);

                    $currentIssuedQty    = (float) ($row->transaction_qty ?? 0);
                    $currentRespondedQty = (float) ($respondedEntriesDetails[$currentGenericId] ?? 0);

                    $brandName  = '';
                    $batchNo    = '';
                    $expiryDate = '';
                    $balances   = ['orgBalance' => 'N/A', 'siteBalance' => 'N/A', 'locBalance' => '[]'];

                    if ($currentRespondedQty > 0 && !empty($row->referenceNumber)) {
                        $itemData = $refMap[$currentGenericId] ?? null;
                        if ($itemData) {
                            $brandName  = $getBrandName($itemData['brand_id']);
                            $batchNo    = $itemData['batch_no'];
                            $expiryDate = is_numeric($itemData['expiry_date'])
                                ? \Carbon\Carbon::createFromTimestamp($itemData['expiry_date'])->format('d-M-Y')
                                : $itemData['expiry_date'];

                            $balances = $getBalances(
                                $row->org_id, $row->site_id, $currentGenericId,
                                $itemData['brand_id'], $itemData['batch_no'],
                                $preferredLocations
                            );
                        }
                    } else {
                        $imLine = DB::table('inventory_management')
                            ->where('ref_document_no', $row->referenceNumber)
                            ->where('inv_generic_id', $currentGenericId)
                            ->select('brand_id', 'batch_no')
                            ->orderBy('id', 'desc')
                            ->first();

                        if ($imLine) {
                            $orgBalance = DB::table('inventory_balance')
                                ->where('org_id', $row->org_id)
                                ->where('generic_id', $currentGenericId)
                                ->where('brand_id', $imLine->brand_id)
                                ->where('batch_no', $imLine->batch_no)
                                ->orderBy('id', 'desc')->value('org_balance') ?? 'N/A';

                            $siteBalance = DB::table('inventory_balance')
                                ->where('org_id', $row->org_id)
                                ->where('site_id', $row->site_id)
                                ->where('generic_id', $currentGenericId)
                                ->where('brand_id', $imLine->brand_id)
                                ->where('batch_no', $imLine->batch_no)
                                ->orderBy('id', 'desc')->value('site_balance') ?? 'N/A';

                            $locLatest = InventoryBalance::where('org_id', $row->org_id)
                                ->where('site_id', $row->site_id)
                                ->where('generic_id', $currentGenericId)
                                ->where('brand_id', $imLine->brand_id)
                                ->where('batch_no', $imLine->batch_no)
                                ->whereNotNull('location_id')
                                ->orderBy('id', 'desc')
                                ->get()
                                ->groupBy('location_id')
                                ->map(fn($recs) => $recs->first());

                            $needles = array_map(fn($n) => mb_strtolower(trim($n)), $preferredLocations ?? []);
                            $locEntries = [];
                            foreach ($locLatest as $locId => $rec) {
                                $bal = (float)($rec->location_balance ?? 0);
                                if ($bal <= 0) continue;
                                $locName = DB::table('service_location')->where('id', $locId)->value('name') ?? 'Unknown';
                                if (!empty($needles)) {
                                    $key = mb_strtolower(trim($locName));
                                    if (!in_array($key, $needles, true)) continue;
                                }
                                $locEntries[] = $locName . ': ' . $bal;
                            }

                            $balances = [
                                'orgBalance'  => $orgBalance,
                                'siteBalance' => $siteBalance,
                                'locBalance'  => htmlspecialchars(json_encode(array_values($locEntries)), ENT_QUOTES, 'UTF-8'),
                            ];
                        }
                    }

                    if ($currentRespondedQty > 0) {
                        if ($currentRespondedQty == $currentIssuedQty) {
                            $status = 'Consumed'; $statusClass = 'success'; $actionBtn = 'N/A';
                        } else {
                            $status = 'Partially Consumed'; $statusClass = 'info';
                            $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn"
                                data-id="'. $row->id.'" data-generic-id="' . $currentGenericId . '" data-brand-id="' . $brandName . '"
                                data-batch-no="' . $batchNo . '" data-expiry="'.$expiryDate.'"
                                data-issue-qty="'.$row->transaction_qty.'">Respond</a>';
                        }
                    } else {
                        $status = 'Pending'; $statusClass = 'warning';
                        $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn"
                            data-id="'. $row->id.'" data-generic-id="' . $currentGenericId . '" data-brand-id=""
                            data-batch-no="" data-expiry=""
                            data-issue-qty="'.$row->transaction_qty.'">Respond</a>';
                    }
                    if ((int)explode(',', $this->rights->consumption)[0] != 1) {
                        $actionBtn = '<code>Unauthorized Access</code>';
                    }

                    $tableRows .= '<tr style="background-color:'.$bg.';cursor:pointer;" class="balance-row"
                                    data-expiry="'.$expiryDate.'"
                                    data-brand="'.$brandName.'"
                                    data-batch="'.$batchNo.'"
                                    data-loc-balance="'.$balances['locBalance'].'"
                                    data-org-balance="'.$balances['orgBalance'].'"
                                    data-site-balance="'.$balances['siteBalance'].'">';
                    $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.($genericNameCache[$currentGenericId] ?? 'N/A').'</td>';

                    if (!$isMedication) {
                        $tableRows .= '<td style="padding:8px;border:1px solid #ccc;">'.($row->demand_qty ?? 'N/A').'</td>';
                    }

                    $tableRows .= '<td style="padding: 5px 1;border: 1px solid #ccc;">'.($row->transaction_qty ?? 0).'</td>'
                            .  '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$currentRespondedQty.'</td>'
                            .  '<td style="padding: 5px 15px;border: 1px solid #ccc;">'.$actionBtn.'</td>'
                            .  '<td style="padding:8px;border:1px solid #ccc;"><span class="label label-'.$statusClass.'">'.$status.'</span></td>'
                            .  '</tr>';
                }

                $tableHeader = '<tr>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>';
                if (!$isMedication) {
                    $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Demand Qty</th>';
                }
                $tableHeader .= '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Issued Qty</th>'
                            .  '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Transaction Qty</th>'
                            .  '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Action</th>'
                            .  '<th style="padding:8px;border:1px solid #ccc;text-align:left;">Status</th>'
                            .  '</tr>';

                return '<table style="width:100%;border-collapse:collapse;font-size:13px;" class="table table-bordered">'
                    .'<thead style="background-color:#e2e8f0;color:#000;">'.$tableHeader.'</thead>'
                    .'<tbody>'.$tableRows.'</tbody></table>';
            })

            ->rawColumns(['id_raw','id','patientDetails','InventoryDetails'])
            ->make(true);
    }

    public function RespondConsumption(Request $r)
    {
        $rights = $this->rights;
        $respond = explode(',', $rights->consumption)[1];
        if ($respond == 0) {
            abort(403, 'Forbidden');
        }

        $id  = $r->query('id');

        $IssuedData = DB::table('inventory_management as im')
            ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
            ->join('inventory_source_destination_type as isdt', 'isdt.id', '=', 'itt.destination_location_type')
            ->join('inventory_transaction_activity as ita', 'ita.id', '=', 'itt.activity_type')
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
            ->where('itt.name', 'like', '%issue%')
            ->select(array_merge(
                array_map(fn($col) => "im.$col", [
                    'id', 'transaction_type_id', 'status', 'org_id', 'site_id',
                    'effective_timestamp', 'timestamp', 'last_updated', 'logid'
                ]),
                [
                    'im.*',
                    DB::raw('isdt.name as sourceDestinationName'),
                ],
                [
                    DB::raw('patient.name as patientName'),
                    DB::raw('employee.name as Physician'),
                    DB::raw('organization.organization as OrgName'),
                    DB::raw('org_site.name as SiteName'),
                    DB::raw('services.name as serviceName'),
                    DB::raw('service_mode.name as serviceMode'),
                    DB::raw('billingCC.name as billingCC'),
                    DB::raw('service_group.name as serviceGroup'),
                    DB::raw('service_type.name as serviceType'),
                    DB::raw('itt.name as TransactionType')
                ]
            ))
            ->where('im.id', $id)
            ->first();

            if (! $IssuedData) {
                return response()->json(['error'=>'Material record not found'], 404);
            }

            $genericId = $IssuedData->inv_generic_id;
            $brandid = $IssuedData->brand_id;
            $batchNo = $IssuedData->batch_no;
            $expiry = date('Y-m-d', $IssuedData->expiry_date);
            $issuedQty = $IssuedData->transaction_qty;
            $demandQty = $IssuedData->demand_qty;

            $Dose = $IssuedData->dose;
            $Route = $IssuedData->route_id;
            $Frequency = $IssuedData->frequency_id;
            $Days = $IssuedData->duration;

            $genericName = InventoryGeneric::find($genericId)->name ?? '';
            $brandName = InventoryBrand::find($brandid)->name ?? '';
            $routeName = MedicationRoutes::find($Route)->name ?? '';
            $frequencyName = MedicationFrequency::find($Frequency)->name ?? '';
            if (!empty($IssuedData->ref_document_no)) {
                $itemData = DB::table('inventory_management as im')
                ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
                ->where('ref_document_no', $IssuedData->ref_document_no)
                ->where('inv_generic_id', $genericId)
                ->where('itt.name', 'like', '%consumption%')
                ->select('transaction_qty')
                ->first();

                if($itemData) {
                    $issuedQty = $issuedQty - $itemData->transaction_qty;
                }
                else{
                    $issuedQty = $IssuedData->transaction_qty; // Default to the original issued quantity if no item data found
                }
            }

            return response()->json([
                'code'                  => $IssuedData->ref_document_no,
                'org_id'                 => $IssuedData->org_id,
                'org_name'               => $IssuedData->OrgName,
                'site_id'                => $IssuedData->site_id,
                'site_name'              => $IssuedData->SiteName,
                'mr_code'                => $IssuedData->mr_code,
                'patient_name'           => $IssuedData->patientName,
                'transaction_type_id'    => $IssuedData->transaction_type_id,
                'transaction_type_name'  => $IssuedData->TransactionType,
                // 'inv_location_id'        => $IssuedData->inv_location_id,
                // 'location_name'          => $IssuedData->location_name,
                'service_mode_id'        => $IssuedData->service_mode_id,
                'service_mode_name'      => $IssuedData->serviceMode,
                'service_id'             => $IssuedData->service_id,
                'service_name'           => $IssuedData->serviceName,
                'service_group_name'     => $IssuedData->serviceGroup,
                'service_type_name'      => $IssuedData->serviceType,
                'physician_id'           => $IssuedData->resp_physician,
                'physician_name'         => $IssuedData->Physician,
                'billing_cc'             => $IssuedData->billing_cc,
                'billing_cc_name'        => $IssuedData->billingCC,
                'generic_id'             => $genericId,
                'generic_name'           => $genericName,
                'brand_id'               => $brandid,
                'brand_name'             => $brandName,
                'expiry'                 => $expiry,
                'batchNo'                => $batchNo,
                'dose'                   => $Dose,
                'days'                   => $Days,
                'route_id'               => $Route,
                'route_name'             => $routeName,
                'frequency_id'           => $Frequency,
                'frequency_name'         => $frequencyName,
                'batchNo'                => $batchNo,
                'issue_qty'              => $issuedQty ?? '0',
                'demand_qty'             => $demandQty ?? '0',
            ]);
    }

    public function AddConsumption(ConsumptionRequest $request)
    {
        $rights = $this->rights;
        $respond = explode(',', $rights->consumption)[0];
        if ($respond == 0) {
            abort(403, 'Forbidden');
        }
        // Get validated data
        $validated = $request->validated();
        $itemCount = isset($validated['consumption_generic']) ? count($validated['consumption_generic']) : 0;
        $success = true;
        $message = '';

        $inventory = new InventoryManagement();
        // Required fields
        $inventory->transaction_type_id = $validated['consumption_transactiontype'];
        $inventory->org_id = $validated['consumption_org'];
        $inventory->site_id = $validated['consumption_site'];
        $inventory->source = $validated['consumption_source'];
        $inventory->destination = $validated['consumption_destination'];

        if (isset($validated['consumption_mr']) && !empty($validated['consumption_mr'])) {
            $inventory->mr_code = $validated['consumption_mr'];
            // If MR exists, check for service details
            if (isset($validated['consumption_service']) && !empty($validated['consumption_service'])) {
                $inventory->service_id = $validated['consumption_service'];
                $inventory->service_mode_id = $validated['consumption_servicemode'] ?? null;
                $inventory->billing_cc = $validated['consumption_billingcc'] ?? null;
                $inventory->performing_cc = $validated['consumption_performing_cc'] ?? null;
                $inventory->resp_physician = $validated['consumption_physician'] ?? null;
            }
        }

        // Optional reference document
        $inventory->ref_document_no = $validated['consumption_reference_document'] ?? null;
        // Remarks field
        $inventory->remarks = $validated['consumption_remarks'] ?? null;

        if ($itemCount > 1) {
            $inventory->inv_generic_id = implode(',', $validated['consumption_generic']);
            $inventory->brand_id = implode(',', $validated['consumption_brand']);
            $inventory->batch_no = implode(',', $validated['consumption_batch']);
            if (isset($validated['consumption_demand_qty']) && !empty($validated['consumption_demand_qty'])) {
                $inventory->demand_qty = implode(',', $validated['consumption_demand_qty']);
            }

            if (isset($validated['consumption_mr'], $validated['source_type']) && !empty($validated['consumption_mr']) && $validated['source_type'] === 'medication') {
                $inventory->dose = implode(',', $validated['consumption_dose']);
                $inventory->frequency_id = implode(',', $validated['consumption_frequency']);
                $inventory->route_id = implode(',', $validated['consumption_route']);
                $inventory->duration = implode(',', $validated['consumption_duration']);
            }

            $formattedDates = array_map(function($date) {
                return Carbon::createFromFormat('Y-m-d', $date)->timestamp;
            }, $validated['consumption_expiry']);
            $inventory->expiry_date = implode(',', $formattedDates);

            $inventory->transaction_qty = implode(',', $validated['consumption_qty']);
        } else {
            $inventory->inv_generic_id = $validated['consumption_generic'][0];
            $inventory->brand_id = $validated['consumption_brand'][0];
            $inventory->batch_no = $validated['consumption_batch'][0];
            $inventory->expiry_date = Carbon::createFromFormat('Y-m-d', $validated['consumption_expiry'][0])->timestamp;
            $inventory->transaction_qty = $validated['consumption_qty'][0];

            if (isset($validated['consumption_demand_qty']) && !empty($validated['consumption_demand_qty'])) {
                $inventory->demand_qty = $validated['consumption_demand_qty'][0];
            }

            if (isset($validated['consumption_mr'], $validated['source_type']) && !empty($validated['consumption_mr']) && $validated['source_type'] === 'medication') {
                $inventory->dose = $validated['consumption_dose'][0];
                $inventory->frequency_id = $validated['consumption_frequency'][0];
                $inventory->route_id = $validated['consumption_route'][0];
                $inventory->duration = $validated['consumption_duration'][0];
            }
        }

        $inventory->status = 1;
        $inventory->user_id = auth()->id();
        $inventory->effective_timestamp = now()->timestamp;
        $inventory->timestamp = now()->timestamp;
        $inventory->last_updated = now()->timestamp;

        $rule = DB::table('inventory_transaction_type')
        ->select('source_action', 'destination_action', 'source_location_type', 'destination_location_type')
        ->where('id', $validated['consumption_transactiontype'])
        ->first();

        // Prefer destination action if defined, else fallback to source action
        $useAction = (isset($rule->destination_action) && in_array($rule->destination_action, ['a','s','r']))
            ? $rule->destination_action
            : $rule->source_action;

        $sourceType = DB::table('inventory_source_destination_type')->where('id', $rule->source_location_type)->value('name');
        $destinationType = DB::table('inventory_source_destination_type')->where('id', $rule->destination_location_type)->value('name');

        foreach ($validated['consumption_generic'] as $i => $genId) {
            $brandId = $validated['consumption_brand'][$i];
            $batchNo = $validated['consumption_batch'][$i];
            $qty = (int) $validated['consumption_qty'][$i];
            if (strtolower($sourceType) === 'inventory location' && $validated['consumption_source'] && in_array($rule->source_action, ['s', 'r'])) {
                $sourceLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['consumption_org'])
                    ->where('site_id', $validated['consumption_site'])
                    ->where('location_id', $validated['consumption_source'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $sourceLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient source location balance. Available: $sourceLocBalance, Requested: $qty"
                    ]);
                }
            }

            if (strtolower($destinationType) === 'inventory location' && $validated['consumption_destination'] && in_array($rule->destination_action, ['s', 'r'])) {
                $destinationLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['consumption_org'])
                    ->where('site_id', $validated['consumption_site'])
                    ->where('location_id', $validated['consumption_destination'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $destinationLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient destination location balance. Available: $destinationLocBalance, Requested: $qty"
                    ]);
                }
            }
        }

        if (!$inventory->save()) {
            $success = false;
            $message = 'Failed to save inventory record';
        }

        $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
        $remarkText = "Consumed by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} ";

        // Process each item separately for inventory_balance
        // if (! $isIssueOnly) {
        for ($i = 0; $i < $itemCount; $i++) {
            $genId = $validated['consumption_generic'][$i];
            $brandId = $validated['consumption_brand'][$i];
            $batchNo = $validated['consumption_batch'][$i];
            $qty = (int)$validated['consumption_qty'][$i];

            // $expTs = Carbon::createFromFormat('Y-m-d', $validated['consumption_expiry'][$i])->timestamp;
            if (! $genId || ! $brandId || ! $batchNo) {
                continue;
            }

            $prevOrgRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['consumption_org'])
                ->orderBy('id', 'desc')
                ->first();
            $prevOrgBalance = $prevOrgRow->org_balance ?? 0;

            $prevSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['consumption_org'])
                ->where('site_id',   $validated['consumption_site'])
                ->orderBy('id', 'desc')
                ->first();
            $prevSiteBalance = $prevSiteRow->site_balance ?? 0;

            // Check if both source and destination are inventory locations
            if (strtolower($sourceType) === 'inventory location' && $validated['consumption_source'] && strtolower($destinationType) === 'inventory location' && $validated['consumption_destination']) {
                $newOrgBalance  = $prevOrgBalance;  // No change in organization balance
                $newSiteBalance = $prevSiteBalance; // Default to current site balance
                if ($rule->source_action === 'a') {
                    $newSiteBalance += $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSiteBalance -= $qty;
                }
                // Adjust destination location's balance similarly
                if ($rule->destination_action === 'a') {
                    $newSiteBalance += $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newSiteBalance -= $qty;
                }
            } else {
                // Other cases (already exists)
                // Default balance calculations
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
            }

            // $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
            $remarkText = "Transaction Initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSiteBalance}";
            if (strtolower($sourceType) === 'inventory location' && $validated['consumption_source'] && strtolower($destinationType) === 'inventory location' && $validated['consumption_destination']) {
                // Source location row
                $prevSourceLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['consumption_org'])
                    ->where('site_id',   $validated['consumption_site'])
                    ->where('location_id', $validated['consumption_source'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevSourceLocBalance = $prevSourceLocRow->location_balance ?? 0;

                if ($rule->source_action === 'a') {
                    $newSourceLocBalance = $prevSourceLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSourceLocBalance = $prevSourceLocBalance - $qty;
                } else {
                    $newSourceLocBalance = $prevSourceLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['consumption_org'],
                    'site_id'          => $validated['consumption_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['consumption_source'],
                    'location_balance' => $newSourceLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);

                // Destination location row
                $prevDestLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['consumption_org'])
                    ->where('site_id',   $validated['consumption_site'])
                    ->where('location_id', $validated['consumption_destination'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevDestLocBalance = $prevDestLocRow->location_balance ?? 0;


                if ($rule->destination_action === 'a') {
                    $newDestLocBalance = $prevDestLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newDestLocBalance = $prevDestLocBalance - $qty;
                } else {
                    $newDestLocBalance = $prevDestLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['consumption_org'],
                    'site_id'          => $validated['consumption_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['consumption_destination'],
                    'location_balance' => $newDestLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($sourceType) === 'inventory location' && $validated['consumption_source']) {
                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['consumption_org'])
                    ->where('site_id',   $validated['consumption_site'])
                    ->where('location_id', $validated['consumption_source'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;


                if ($rule->source_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['consumption_org'],
                    'site_id'          => $validated['consumption_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['consumption_source'],
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($destinationType) === 'inventory location' && $validated['consumption_destination']) {

                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['consumption_org'])
                    ->where('site_id',   $validated['consumption_site'])
                    ->where('location_id', $validated['consumption_destination'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;



                if ($rule->destination_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }

                // dd($newLocBalance, $Destination)
                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['consumption_org'],
                    'site_id'          => $validated['consumption_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['consumption_destination'],
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            else {
                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['consumption_org'],
                    'site_id'          => $validated['consumption_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => null,
                    'location_balance' => null,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }

        }

        if ($success) {
            $logId = createLog(
                'inventory_management',
                'insert',
                [
                    'message' => $remarkText,
                    'created_by' => auth()->user()->name ?? 'system'
                ],
                $inventory->id ?? null,
                null,
                null,
                auth()->id() ?? 0
            );

            $inventory->logid = $logId;
            $inventory->save();

            return response()->json([
                'success' => 'Records have been added successfully',
                'reload' => true
            ]);
        } else {
            return response()->json([
                'error' => $message,
                'reload' => false
            ]);
        }
    }

    public function ShowInventoryReturn()
    {
        $colName = 'inventory_return';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $empId = $user->emp_id;

        // $costcenters = DB::table('emp_cc as e')
        // ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
        // ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
        // ->where('e.emp_id', $empId)
        // ->where('ct.performing', 1)
        // ->select('c.id', 'c.name')
        // ->get();

        $costcenters = DB::table(function ($query) use ($empId) {
            $query->select('c.id', 'c.name')
                ->from('emp_cc as e')
                ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, e.cc_id)'), '>', DB::raw('0'))
                ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                ->where('e.emp_id', $empId)
                ->where('ct.performing', 1)

            ->unionAll(
                DB::table('employee as emp')
                    ->select('c.id', 'c.name')
                    ->join('costcenter as c', DB::raw('FIND_IN_SET(c.id, emp.cc_id)'), '>', DB::raw('0'))
                    ->join('cc_type as ct', 'c.cc_type', '=', 'ct.id')
                    ->where('emp.id', $empId)
                    ->where('ct.performing', 1)
            );
        }, 'combined')
        ->distinct()
        ->get();

        $RequisitionNonMandatory = DB::table('inventory_transaction_type AS itt')
        ->join('inventory_transaction_activity AS ita', 'ita.id', '=', 'itt.activity_type')
        ->where('ita.name', 'Issue & Dispense')
        ->where('itt.request_mandatory', 'n')
        ->exists();

        $MedicationRoutes = MedicationRoutes::select('id', 'name')->where('status', 1)->get();
        $MedicationFrequencies = MedicationFrequency::select('id', 'name')->where('status', 1)->get();

        return view('dashboard.material_management.return', compact('user','RequisitionNonMandatory','costcenters','MedicationRoutes','MedicationFrequencies'));
    }

    
    public function GetReturnData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->inventory_return)[1];
        if ($view == 0) abort(403, 'Forbidden');

        // --------------------------------------------
        // 1) Resolve activity-type ID sets (one time)
        // --------------------------------------------
        // These plucks are cheap and done once; they let MySQL/Postgres use indexes vs LIKE scans.
        $issueDispenseActivityIds = DB::table('inventory_transaction_activity')
            ->where(function ($q) {
                $q->where('name', 'like', '%issue%')
                ->orWhere('name', 'like', '%dispense%');
            })
            ->pluck('id')
            ->all();

        $consumptionActivityIds = DB::table('inventory_transaction_activity')
            ->where('name', 'like', '%consumption%')
            ->pluck('id')
            ->all();

        $returnActivityIds = DB::table('inventory_transaction_activity')
            ->where('name', 'like', '%return%')
            ->pluck('id')
            ->all();

        // Protect against empty IN() which some DBs dislike
        if (empty($issueDispenseActivityIds)) $issueDispenseActivityIds = [-1];
        if (empty($consumptionActivityIds))   $consumptionActivityIds   = [-1];
        if (empty($returnActivityIds))        $returnActivityIds        = [-1];

        // -------------------------------------------------------
        // 2) Base: all ISSUE/DISPENSE rows we want to show/return
        // -------------------------------------------------------
        $issuedDispensed = DB::table('inventory_management as im')
            ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
            ->join('inventory_source_destination_type as isdt', 'isdt.id', '=', 'itt.destination_location_type')
            ->join('inventory_transaction_activity as ita', 'ita.id', '=', 'itt.activity_type')
            ->join('organization', 'organization.id', '=', 'im.org_id')
            ->join('org_site', 'org_site.id', '=', 'im.site_id')
            ->leftJoin('patient', 'patient.mr_code', '=', 'im.mr_code')
            ->leftJoin('employee', 'employee.id', '=', 'im.resp_physician')
            ->leftJoin('service_mode', 'service_mode.id', '=', 'im.service_mode_id')
            ->leftJoin('services', 'services.id', '=', 'im.service_id')
            ->leftJoin('service_group', 'service_group.id', '=', 'services.group_id')
            ->leftJoin('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->leftJoin('costcenter as billingCC', 'billingCC.id', '=', 'im.billing_cc')
            // Fast, indexable condition:
            ->whereIn('ita.id', $issueDispenseActivityIds)
            ->select([
                'im.*',
                'itt.name as transaction_type',
                'isdt.name as sourceDestinationName',
                'ita.name as activity_type',
                DB::raw('patient.name as patientName'),
                DB::raw('employee.name as Physician'),
                DB::raw('organization.organization as OrgName'),
                DB::raw('org_site.name as SiteName'),
                DB::raw('services.name as serviceName'),
                DB::raw('service_mode.name as serviceMode'),
                DB::raw('billingCC.name as billingCC'),
                DB::raw('service_group.name as serviceGroup'),
                DB::raw('service_type.name as serviceType'),
            ]);

        // Session site restriction (push down early)
        if ($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if (!empty($sessionSiteIds)) {
                $issuedDispensed->whereIn('org_site.id', $sessionSiteIds);
            }
        }

        // ------------------------------------------------------------------
        // 3) Pre-aggregate CONSUMPTION and RETURN once and join (no N×subq)
        // ------------------------------------------------------------------
        // Keys: (ref_document_no, inv_generic_id, batch_no, brand_id)
        $consumptionAgg = DB::table('inventory_management as imc')
            ->join('inventory_transaction_type as ittc', 'ittc.id', '=', 'imc.transaction_type_id')
            ->join('inventory_transaction_activity as itac', 'itac.id', '=', 'ittc.activity_type')
            ->whereIn('itac.id', $consumptionActivityIds)
            ->groupBy('imc.ref_document_no', 'imc.inv_generic_id', 'imc.batch_no', 'imc.brand_id')
            ->select([
                'imc.ref_document_no',
                'imc.inv_generic_id',
                'imc.batch_no',
                'imc.brand_id',
                DB::raw('COALESCE(SUM(imc.transaction_qty),0) as consumed_qty'),
            ]);

        $returnAgg = DB::table('inventory_management as imr')
            ->join('inventory_transaction_type as ittr', 'ittr.id', '=', 'imr.transaction_type_id')
            ->join('inventory_transaction_activity as itar', 'itar.id', '=', 'ittr.activity_type')
            ->whereIn('itar.id', $returnActivityIds)
            ->groupBy('imr.ref_document_no', 'imr.inv_generic_id', 'imr.batch_no', 'imr.brand_id')
            ->select([
                'imr.ref_document_no',
                'imr.inv_generic_id',
                'imr.batch_no',
                'imr.brand_id',
                DB::raw('COALESCE(SUM(imr.transaction_qty),0) as returned_qty'),
            ]);

        // Join the aggregates to the issue/dispense rows
        $base = DB::query()
            ->fromSub($issuedDispensed, 'im')
            ->leftJoinSub($consumptionAgg, 'c', function ($j) {
                $j->on('c.ref_document_no', '=', 'im.ref_document_no')
                ->on('c.inv_generic_id',   '=', 'im.inv_generic_id')
                ->on('c.batch_no',         '=', 'im.batch_no')
                ->on('c.brand_id',         '=', 'im.brand_id');
            })
            ->leftJoinSub($returnAgg, 'r', function ($j) {
                $j->on('r.ref_document_no', '=', 'im.ref_document_no')
                ->on('r.inv_generic_id',   '=', 'im.inv_generic_id')
                ->on('r.batch_no',         '=', 'im.batch_no')
                ->on('r.brand_id',         '=', 'im.brand_id');
            })
            ->select([
                'im.*',
                DB::raw('COALESCE(c.consumed_qty,0) as consumed_qty'),
                DB::raw('COALESCE(r.returned_qty,0)  as returned_qty'),
                DB::raw('(im.transaction_qty - COALESCE(c.consumed_qty,0)) as remaining_qty'),
            ]);

        // Only keep rows that still have something to return
        $finalQuery = DB::query()
            ->fromSub($base, 'u')
            ->where('u.remaining_qty', '>', 0);

        // -----------------------------------------
        // 4) DataTables (filters + ordering + view)
        // -----------------------------------------
        return DataTables::query($finalQuery)

            // --------- FILTERS (explicit + global + per-column) ----------
            ->filter(function ($query) use ($request) {
                // Explicit params
                $from    = $request->input('from_date');
                $to      = $request->input('to_date');
                $orgIds  = (array) $request->input('org_ids', []);
                $siteIds = (array) $request->input('site_ids', []);
                $ttIds   = (array) $request->input('transaction_type_ids', []);

                $ref      = trim((string) $request->input('reference'));
                $mr       = trim((string) $request->input('mr_code'));
                $patient  = trim((string) $request->input('patient'));
                $phys     = trim((string) $request->input('physician'));
                $service  = trim((string) $request->input('service'));
                $svcMode  = trim((string) $request->input('service_mode'));
                $svcGroup = trim((string) $request->input('service_group'));
                $billing  = trim((string) $request->input('billing_cc'));
                $siteName = trim((string) $request->input('site_name'));
                $remarks  = trim((string) $request->input('remarks'));
                $srcDst   = trim((string) $request->input('location_type')); // matches sourceDestinationName
                $generic  = trim((string) $request->input('generic_name'));  // via inventory_generic.name

                if ($from) {
                    $query->where('u.timestamp', '>=', \Carbon\Carbon::parse($from)->startOfDay()->timestamp);
                }
                if ($to) {
                    $query->where('u.timestamp', '<=', \Carbon\Carbon::parse($to)->endOfDay()->timestamp);
                }
                if ($orgIds)  { $query->whereIn('u.org_id', $orgIds); }
                if ($siteIds) { $query->whereIn('u.site_id', $siteIds); }
                if ($ttIds)   { $query->whereIn('u.transaction_type_id', $ttIds); }

                if ($ref)      { $query->where('u.ref_document_no', 'like', "%{$ref}%"); }
                if ($mr)       { $query->where('u.mr_code', 'like', "%{$mr}%"); }
                if ($patient)  { $query->where('u.patientName', 'like', "%{$patient}%"); }
                if ($phys)     { $query->where('u.Physician', 'like', "%{$phys}%"); }
                if ($service)  { $query->where('u.serviceName', 'like', "%{$service}%"); }
                if ($svcMode)  { $query->where('u.serviceMode', 'like', "%{$svcMode}%"); }
                if ($svcGroup) { $query->where('u.serviceGroup', 'like', "%{$svcGroup}%"); }
                if ($billing)  { $query->where('u.billingCC', 'like', "%{$billing}%"); }
                if ($siteName) { $query->where('u.SiteName', 'like', "%{$siteName}%"); }
                if ($remarks)  { $query->where('u.remarks', 'like', "%{$remarks}%"); }
                if ($srcDst)   { $query->where('u.sourceDestinationName', 'like', "%{$srcDst}%"); }

                if ($generic) {
                    $g = "%{$generic}%";
                    $query->whereRaw("
                        EXISTS (
                            SELECT 1
                            FROM inventory_generic ig
                            WHERE ig.id = u.inv_generic_id
                            AND ig.name LIKE ?
                        )", [$g]
                    );
                }

                // DataTables global search
                $global = trim((string) data_get($request->input('search'), 'value'));
                if ($global !== '') {
                    $g = "%{$global}%";
                    $query->where(function ($qq) use ($g) {
                        $qq->orWhere('u.ref_document_no', 'like', $g)
                        ->orWhere('u.patientName', 'like', $g)
                        ->orWhere('u.Physician', 'like', $g)
                        ->orWhere('u.OrgName', 'like', $g)
                        ->orWhere('u.SiteName', 'like', $g)
                        ->orWhere('u.serviceName', 'like', $g)
                        ->orWhere('u.serviceMode', 'like', $g)
                        ->orWhere('u.billingCC', 'like', $g)
                        ->orWhere('u.serviceGroup', 'like', $g)
                        ->orWhere('u.serviceType', 'like', $g)
                        ->orWhere('u.transaction_type', 'like', $g)
                        ->orWhere('u.sourceDestinationName', 'like', $g)
                        ->orWhere('u.mr_code', 'like', $g)
                        ->orWhere('u.remarks', 'like', $g)
                        ->orWhereRaw("
                                EXISTS (
                                SELECT 1 FROM inventory_generic ig
                                WHERE ig.id = u.inv_generic_id
                                    AND ig.name LIKE ?
                                )", [$g]
                        );
                    });
                }

                // Column-specific search
                foreach ((array)$request->input('columns', []) as $col) {
                    $name  = data_get($col, 'data');
                    $value = trim((string) data_get($col, 'search.value'));
                    if ($value === '') continue;

                    switch ($name) {
                        case 'ref_document_no':  $query->where('u.ref_document_no', 'like', "%{$value}%"); break;
                        case 'mr_code':          $query->where('u.mr_code', 'like', "%{$value}%"); break;
                        case 'patientName':      $query->where('u.patientName', 'like', "%{$value}%"); break;
                        case 'Physician':        $query->where('u.Physician', 'like', "%{$value}%"); break;
                        case 'SiteName':         $query->where('u.SiteName', 'like', "%{$value}%"); break;
                        case 'OrgName':          $query->where('u.OrgName', 'like', "%{$value}%"); break;
                        case 'serviceName':      $query->where('u.serviceName', 'like', "%{$value}%"); break;
                        case 'serviceMode':      $query->where('u.serviceMode', 'like', "%{$value}%"); break;
                        case 'serviceGroup':     $query->where('u.serviceGroup', 'like', "%{$value}%"); break;
                        case 'serviceType':      $query->where('u.serviceType', 'like', "%{$value}%"); break;
                        case 'transaction_type': $query->where('u.transaction_type', 'like', "%{$value}%"); break;
                        case 'sourceDestinationName':
                                                $query->where('u.sourceDestinationName', 'like', "%{$value}%"); break;
                        case 'remarks':          $query->where('u.remarks', 'like', "%{$value}%"); break;
                        case 'generic':
                            $g = "%{$value}%";
                            $query->whereRaw("
                                EXISTS (
                                SELECT 1 FROM inventory_generic ig
                                WHERE ig.id = u.inv_generic_id
                                    AND ig.name LIKE ?
                                )", [$g]
                            );
                            break;
                        default:
                            break;
                    }
                }
            })

            // Default order (latest first) if DT doesn't send one
            ->order(function ($q) use ($request) {
                if (!count($request->input('order', []))) {
                    $q->orderBy('u.timestamp', 'desc');
                }
            })

            // ---------- Your existing renderers ----------
            ->addColumn('return_raw', fn($row) => $row->id)
            ->addColumn('id_raw', fn($row) => $row->id)

            ->editColumn('id', function ($row) {
                static $locNameCache = [];
                $getLocName = function ($id) use (&$locNameCache) {
                    $id = (int)$id;
                    if ($id <= 0) return null;
                    if (!array_key_exists($id, $locNameCache)) {
                        $locNameCache[$id] = DB::table('service_location')->where('id', $id)->value('name');
                    }
                    return $locNameCache[$id];
                };

                $timestamp     = \Carbon\Carbon::createFromTimestamp($row->timestamp)->format('l d F Y - h:i A');
                $effectiveDate = \Carbon\Carbon::createFromTimestamp($row->effective_timestamp)->format('l d F Y - h:i A');
                $RequisitionCode = 'Ref: ' . ($row->ref_document_no ?? 'N/A');

                $locationHtml = '';
                if (!empty($row->sourceDestinationName) && str_contains(strtolower($row->sourceDestinationName), 'location')) {
                    $destName = $getLocName($row->destination) ?? 'N/A';
                    $locationHtml = '<b>Location</b>: ' . ucwords($destName) . '<br>';
                }

                return sprintf(
                    '%s<hr class="mt-1 mb-2">%s<br>%s<b>Site</b>: %s<br><b>Issue Date </b>: %s<br><b>Effective Date </b>: %s<br><b>Remarks</b>: %s',
                    $RequisitionCode,
                    ucwords($row->transaction_type ?? 'N/A'),
                    $locationHtml,
                    $row->SiteName ?? 'N/A',
                    $timestamp,
                    $effectiveDate,
                    $row->remarks ?: 'N/A'
                );
            })

            ->editColumn('patientDetails', function ($row) {
                if (empty($row->mr_code)) return 'N/A';
                return sprintf(
                    '<b>MR#:</b> %s<br>%s<hr class="mt-1 mb-2"><b>Service Mode</b>: %s<br><b>Service Group</b>: %s<br><b>Service</b>: %s<br><b>Responsible Physician</b>: %s<br><b>Billing CC</b>: %s',
                    $row->mr_code,
                    ucwords($row->patientName),
                    $row->serviceMode,
                    $row->serviceGroup,
                    $row->serviceName,
                    ucwords($row->Physician),
                    $row->billingCC
                );
            })

            ->editColumn('InventoryDetails', function ($row) {
                // (your existing right-column renderer unchanged)
                // ---- Static caches across rows (per request) ----
                static $rightsRespond = null;
                static $genericNameCache = [];  // [id => name]
                static $brandNameCache   = [];  // [id => name]
                static $locNameCache     = [];  // [id => name]
                static $balanceCache     = [];  // ["org|site|gen|brand|batch" => ['org','site','locJson']]

                if ($rightsRespond === null) {
                    $Rights = $this->rights;
                    $rightsRespond = (int) explode(',', $Rights->inventory_return)[0];
                }

                $getGenericName = function ($id) use (&$genericNameCache) {
                    $id = (int)$id;
                    if ($id <= 0) return 'N/A';
                    if (!array_key_exists($id, $genericNameCache)) {
                        $genericNameCache[$id] = InventoryGeneric::where('id', $id)->value('name') ?? 'N/A';
                    }
                    return $genericNameCache[$id];
                };
                $getBrandName = function ($id) use (&$brandNameCache) {
                    $id = (int)$id;
                    if ($id <= 0) return '';
                    if (!array_key_exists($id, $brandNameCache)) {
                        $brandNameCache[$id] = DB::table('inventory_brand')->where('id', $id)->value('name') ?? '';
                    }
                    return $brandNameCache[$id];
                };
                $getLocName = function ($id) use (&$locNameCache) {
                    $id = (int)$id;
                    if ($id <= 0) return null;
                    if (!array_key_exists($id, $locNameCache)) {
                        $locNameCache[$id] = DB::table('service_location')->where('id', $id)->value('name');
                    }
                    return $locNameCache[$id];
                };

                $getBalances = function ($orgId, $siteId, $genericId, $brandId, $batchNo) use (&$balanceCache, $getLocName) {
                    $key = "{$orgId}|{$siteId}|{$genericId}|{$brandId}|{$batchNo}";
                    if (isset($balanceCache[$key])) return $balanceCache[$key];

                    $orgBal = DB::table('inventory_balance')
                        ->where('org_id', $orgId)
                        ->where('generic_id', $genericId)
                        ->where('brand_id', $brandId)
                        ->where('batch_no', $batchNo)
                        ->orderBy('id', 'desc')
                        ->value('org_balance') ?? 'N/A';

                    $siteBal = DB::table('inventory_balance')
                        ->where('org_id', $orgId)
                        ->where('site_id', $siteId)
                        ->where('generic_id', $genericId)
                        ->where('brand_id', $brandId)
                        ->where('batch_no', $batchNo)
                        ->orderBy('id', 'desc')
                        ->value('site_balance') ?? 'N/A';

                    $locRows = DB::table('inventory_balance')
                        ->where('org_id', $orgId)
                        ->where('site_id', $siteId)
                        ->where('generic_id', $genericId)
                        ->where('brand_id', $brandId)
                        ->where('batch_no', $batchNo)
                        ->whereNotNull('location_id')
                        ->orderBy('id', 'desc')
                        ->get();

                    $latestPerLoc = [];
                    foreach ($locRows as $r) {
                        if (!isset($latestPerLoc[$r->location_id])) $latestPerLoc[$r->location_id] = $r; // first = latest
                    }

                    $locEntries = [];
                    foreach ($latestPerLoc as $locId => $r) {
                        $bal = (float)($r->location_balance ?? 0);
                        if ($bal <= 0) continue;
                        $name = $getLocName($locId) ?? 'Unknown';
                        $locEntries[] = $name . ': ' . $bal;
                    }

                    return $balanceCache[$key] = [
                        'orgBalance'  => $orgBal,
                        'siteBalance' => $siteBal,
                        'locBalance'  => htmlspecialchars(json_encode(array_values($locEntries)), ENT_QUOTES, 'UTF-8'),
                    ];
                };

                $genericId  = (int)$row->inv_generic_id;
                $brandId    = (int)$row->brand_id;
                $batchNo    = $row->batch_no;
                $brandName  = $getBrandName($brandId);
                $expiryDate = is_numeric($row->expiry_date)
                    ? \Carbon\Carbon::createFromTimestamp($row->expiry_date)->format('d-M-Y')
                    : $row->expiry_date;

                $balances = $getBalances($row->org_id, $row->site_id, $genericId, $brandId, $batchNo);

                $returnQty     = (float)($row->returned_qty ?? 0);
                $remainingQty  = (float)($row->remaining_qty ?? 0);
                $issuedQty     = (float)($row->transaction_qty ?? 0);
                $availableForReturn = $remainingQty - $returnQty;

                if ($returnQty > 0) {
                    if ($returnQty == $remainingQty) {
                        $status = 'Returned'; $statusClass = 'success'; $actionBtn = 'N/A';
                    } else {
                        $status = 'Partially Returned'; $statusClass = 'info';
                        $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn"
                            data-id="'. $row->id .'"
                            data-generic-id="'. $genericId .'"
                            data-brand-id="'. $brandName .'"
                            data-batch-no="'. $batchNo .'"
                            data-expiry="'. $expiryDate .'"
                            data-issue-qty="'. $remainingQty .'">Return</a>';
                    }
                } else {
                    $status = 'Available for Return'; $statusClass = 'inverse';
                    $actionBtn = '<a href="javascript:void(0);" class="btn btn-sm btn-primary respond-btn"
                        data-id="'. $row->id .'"
                        data-generic-id="'. $genericId .'"
                        data-brand-id="'. $brandName .'"
                        data-batch-no="'. $batchNo .'"
                        data-expiry="'. $expiryDate .'"
                        data-issue-qty="'. $remainingQty .'">Return</a>';
                }
                if ($rightsRespond != 1) $actionBtn = '<code>Unauthorized Access</code>';

                $rowHtml = sprintf(
                    '<tr style="background-color:#f9f9f9;cursor:pointer;" class="balance-row"
                        data-expiry="%s"
                        data-brand="%s"
                        data-batch="%s"
                        data-loc-balance="%s"
                        data-org-balance="%s"
                        data-site-balance="%s">
                        <td style="padding:8px;border:1px solid #ccc;">%s</td>
                        <td style="padding:8px;border:1px solid #ccc;">%s</td>
                        <td style="padding:8px;border:1px solid #ccc;">%s</td>
                        <td style="padding:8px;border:1px solid #ccc;">%s</td>
                        <td style="padding:5px 15px;border:1px solid #ccc;">%s</td>
                        <td style="padding:8px;border:1px solid #ccc;"><span class="label label-%s">%s</span></td>
                    </tr>',
                    e($expiryDate),
                    e($brandName),
                    e($batchNo),
                    $balances['locBalance'],
                    e($balances['orgBalance']),
                    e($balances['siteBalance']),
                    e($getGenericName($genericId)),
                    $issuedQty,
                    $returnQty,
                    $availableForReturn,
                    $actionBtn,
                    $statusClass,
                    $status
                );

                $header = '<tr>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Generic</th>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Issued Qty</th>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Return Qty</th>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Available for Return</th>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Action</th>'
                    .'<th style="padding:8px;border:1px solid #ccc;text-align:left;">Status</th>'
                    .'</tr>';

                return '<table style="width:100%;border-collapse:collapse;font-size:13px;" class="table table-bordered">'
                    .'<thead style="background-color:#e2e8f0;color:#000;">'.$header.'</thead>'
                    .'<tbody>'.$rowHtml.'</tbody></table>';
            })

            ->rawColumns(['id_raw','id','patientDetails','InventoryDetails'])
            ->make(true);
    }


    public function RespondReturn(Request $r)
    {
        $rights = $this->rights;
        $respond = explode(',', $rights->inventory_return)[0];
        if ($respond == 0) {
            abort(403, 'Forbidden');
        }

        $id  = $r->query('id');

        $IssuedData = DB::table('inventory_management as im')
            ->join('inventory_transaction_type as itt', 'itt.id', '=', 'im.transaction_type_id')
            ->join('inventory_source_destination_type as isdt', 'isdt.id', '=', 'itt.destination_location_type')
            ->join('inventory_transaction_activity as ita', 'ita.id', '=', 'itt.activity_type')
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
            ->where('im.id', $id)
            ->select(
                'im.*',
                'isdt.name as sourceDestinationName',
                'itt.name as TransactionType',
                'patient.name as patientName',
                'employee.name as Physician',
                'organization.organization as OrgName',
                'org_site.name as SiteName',
                'services.name as serviceName',
                'service_mode.name as serviceMode',
                'billingCC.name as billingCC',
                'service_group.name as serviceGroup',
                'service_type.name as serviceType'
            )
            ->first();

        if (! $IssuedData) {
            return response()->json(['error'=>'Material record not found'], 404);
        }

        // Calculate remaining quantity available for return
        $consumedQty = DB::table('inventory_management as im2')
            ->join('inventory_transaction_type as itt2', 'itt2.id', '=', 'im2.transaction_type_id')
            ->join('inventory_transaction_activity as ita2', 'ita2.id', '=', 'itt2.activity_type')
            ->where('im2.ref_document_no', $IssuedData->ref_document_no)
            ->where('im2.inv_generic_id', $IssuedData->inv_generic_id)
            ->where('im2.batch_no', $IssuedData->batch_no)
            ->where('im2.brand_id', $IssuedData->brand_id)
            ->where('ita2.name', 'like', '%consumption%')
            ->sum('im2.transaction_qty');

        $returnedQty = DB::table('inventory_management as im3')
            ->join('inventory_transaction_type as itt3', 'itt3.id', '=', 'im3.transaction_type_id')
            ->join('inventory_transaction_activity as ita3', 'ita3.id', '=', 'itt3.activity_type')
            ->where('im3.ref_document_no', $IssuedData->ref_document_no)
            ->where('im3.inv_generic_id', $IssuedData->inv_generic_id)
            ->where('im3.batch_no', $IssuedData->batch_no)
            ->where('im3.brand_id', $IssuedData->brand_id)
            ->where('ita3.name', 'like', '%return%')
            ->sum('im3.transaction_qty');

        $availableQty = $IssuedData->transaction_qty - ($consumedQty + $returnedQty);

        // Prepare data for modal
        $genericName = InventoryGeneric::find($IssuedData->inv_generic_id)->name ?? '';
        $brandName = InventoryBrand::find($IssuedData->brand_id)->name ?? '';
        $routeName = MedicationRoutes::find($IssuedData->route_id)->name ?? '';
        $frequencyName = MedicationFrequency::find($IssuedData->frequency_id)->name ?? '';
        $expiry = is_numeric($IssuedData->expiry_date) ? date('Y-m-d', $IssuedData->expiry_date) : $IssuedData->expiry_date;

        return response()->json([
            'code'                  => $IssuedData->ref_document_no,
            'org_id'                => $IssuedData->org_id,
            'org_name'              => $IssuedData->OrgName,
            'site_id'               => $IssuedData->site_id,
            'site_name'             => $IssuedData->SiteName,
            'mr_code'               => $IssuedData->mr_code,
            'patient_name'          => $IssuedData->patientName,
            'transaction_type_id'   => $IssuedData->transaction_type_id,
            'transaction_type_name' => $IssuedData->TransactionType,
            'service_mode_id'       => $IssuedData->service_mode_id,
            'service_mode_name'     => $IssuedData->serviceMode,
            'service_id'            => $IssuedData->service_id,
            'service_name'          => $IssuedData->serviceName,
            'service_group_name'    => $IssuedData->serviceGroup,
            'service_type_name'     => $IssuedData->serviceType,
            'physician_id'          => $IssuedData->resp_physician,
            'physician_name'        => $IssuedData->Physician,
            'billing_cc'            => $IssuedData->billing_cc,
            'billing_cc_name'       => $IssuedData->billingCC,
            'generic_id'            => $IssuedData->inv_generic_id,
            'generic_name'          => $genericName,
            'brand_id'              => $IssuedData->brand_id,
            'brand_name'            => $brandName,
            'expiry'                => $expiry,
            'batchNo'               => $IssuedData->batch_no,
            'dose'                  => $IssuedData->dose,
            'days'                  => $IssuedData->duration,
            'route_id'              => $IssuedData->route_id,
            'route_name'            => $routeName,
            'frequency_id'          => $IssuedData->frequency_id,
            'frequency_name'        => $frequencyName,
            'issue_qty'             => $availableQty > 0 ? $availableQty : 0,
            'demand_qty'            => $IssuedData->demand_qty ?? '0',
            'remarks'               => $IssuedData->remarks ?? '',
        ]);
    }

    public function AddReturn(ReturnRequest $request)
    {
        $rights = $this->rights;
        $respond = explode(',', $rights->inventory_return)[0];
        if ($respond == 0) {
            abort(403, 'Forbidden');
        }
        // Get validated data
        $validated = $request->validated();
        $itemCount = isset($validated['return_generic']) ? count($validated['return_generic']) : 0;
        $success = true;
        $message = '';

        $inventory = new InventoryManagement();
        // Required fields
        $inventory->transaction_type_id = $validated['return_transactiontype'];
        $inventory->org_id = $validated['return_org'];
        $inventory->site_id = $validated['return_site'];
        $inventory->source = $validated['return_source'];
        $inventory->destination = $validated['return_destination'];

        if (isset($validated['return_mr']) && !empty($validated['return_mr'])) {
            $inventory->mr_code = $validated['return_mr'];
            // If MR exists, check for service details
            if (isset($validated['return_service']) && !empty($validated['return_service'])) {
                $inventory->service_id = $validated['return_service'];
                $inventory->service_mode_id = $validated['return_servicemode'] ?? null;
                $inventory->billing_cc = $validated['return_billingcc'] ?? null;
                $inventory->performing_cc = $validated['return_performing_cc'] ?? null;
                $inventory->resp_physician = $validated['return_physician'] ?? null;
            }
        }

        // Optional reference document
        $inventory->ref_document_no = $validated['return_reference_document'] ?? null;
        // Remarks field
        $inventory->remarks = $validated['return_remarks'] ?? null;

        if ($itemCount > 1) {
            $inventory->inv_generic_id = implode(',', $validated['return_generic']);
            $inventory->brand_id = implode(',', $validated['return_brand']);
            $inventory->batch_no = implode(',', $validated['return_batch']);
            if (isset($validated['return_demand_qty']) && !empty($validated['return_demand_qty'])) {
                $inventory->demand_qty = implode(',', $validated['return_demand_qty']);
            }


            if (isset($validated['return_mr'], $validated['source_type']) && !empty($validated['return_mr']) && $validated['source_type'] === 'medication') {
                $inventory->dose = implode(',', $validated['return_dose']);
                $inventory->frequency_id = implode(',', $validated['return_frequency']);
                $inventory->route_id = implode(',', $validated['return_route']);
                $inventory->duration = implode(',', $validated['return_duration']);
            }


            $formattedDates = array_map(function($date) {
                return Carbon::createFromFormat('Y-m-d', $date)->timestamp;
            }, $validated['return_expiry']);
            $inventory->expiry_date = implode(',', $formattedDates);

            $inventory->transaction_qty = implode(',', $validated['return_qty']);
        } else {
            $inventory->inv_generic_id = $validated['return_generic'][0];
            $inventory->brand_id = $validated['return_brand'][0];
            $inventory->batch_no = $validated['return_batch'][0];
            $inventory->expiry_date = Carbon::createFromFormat('Y-m-d', $validated['return_expiry'][0])->timestamp;
            $inventory->transaction_qty = $validated['return_qty'][0];

            if (isset($validated['return_demand_qty']) && !empty($validated['return_demand_qty'])) {
                $inventory->demand_qty = $validated['return_demand_qty'][0];
            }

            if (isset($validated['return_mr'], $validated['source_type']) && !empty($validated['return_mr']) && $validated['source_type'] === 'medication') {
                $inventory->dose = $validated['return_dose'][0];
                $inventory->frequency_id = $validated['return_frequency'][0];
                $inventory->route_id = $validated['return_route'][0];
                $inventory->duration = $validated['return_duration'][0];
            }
        }

        $inventory->status = 1;
        $inventory->user_id = auth()->id();
        $inventory->effective_timestamp = now()->timestamp;
        $inventory->timestamp = now()->timestamp;
        $inventory->last_updated = now()->timestamp;

        $rule = DB::table('inventory_transaction_type')
        ->select('source_action', 'destination_action', 'source_location_type', 'destination_location_type')
        ->where('id', $validated['return_transactiontype'])
        ->first();

        // Prefer destination action if defined; otherwise fallback to source action
        $useAction = (isset($rule->destination_action) && in_array($rule->destination_action, ['a','s','r']))
            ? $rule->destination_action
            : $rule->source_action;

        $sourceType = DB::table('inventory_source_destination_type')->where('id', $rule->source_location_type)->value('name');
        $destinationType = DB::table('inventory_source_destination_type')->where('id', $rule->destination_location_type)->value('name');

        foreach ($validated['return_generic'] as $i => $genId) {
            $brandId = $validated['return_brand'][$i];
            $batchNo = $validated['return_batch'][$i];
            $qty = (int) $validated['return_qty'][$i];
            if (strtolower($sourceType) === 'inventory location' && $validated['return_source'] && in_array($rule->source_action, ['s', 'r'])) {
                $sourceLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['return_org'])
                    ->where('site_id', $validated['return_site'])
                    ->where('location_id', $validated['return_source'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $sourceLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient source location balance. Available: $sourceLocBalance, Requested: $qty"
                    ]);
                }
            }

            if (strtolower($destinationType) === 'inventory location' && $validated['return_destination'] && in_array($rule->destination_action, ['s', 'r'])) {
                $destinationLocBalance = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id', $brandId)
                    ->where('batch_no', $batchNo)
                    ->where('org_id', $validated['return_org'])
                    ->where('site_id', $validated['return_site'])
                    ->where('location_id', $validated['return_destination'])
                    ->orderBy('id', 'desc')
                    ->value('location_balance') ?? 0;

                if ($qty > $destinationLocBalance) {
                    return response()->json([
                        'msg' => "Insufficient destination location balance. Available: $destinationLocBalance, Requested: $qty"
                    ]);
                }
            }
        }

        if (!$inventory->save()) {
            $success = false;
            $message = 'Failed to save inventory record';
        }

        // Auto-generate a reference number if not provided
        if (empty($inventory->ref_document_no)) {
            $siteName = DB::table('org_site')->where('id', $validated['return_site'])->value('name');
            $siteCode = strtoupper(substr($siteName ?? 'SITE', 0, 3));
            $idStr    = str_pad($inventory->id, 5, '0', STR_PAD_LEFT);
            $inventory->ref_document_no = $siteCode . '-RT-' . $idStr; // RT = Return
            $inventory->save();
        }

        $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
        $remarkText = "Consumed by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} ";

        // Process each item separately for inventory_balance
        for ($i = 0; $i < $itemCount; $i++) {
            $genId = $validated['return_generic'][$i];
            $brandId = $validated['return_brand'][$i];
            $batchNo = $validated['return_batch'][$i];
            $qty = (int)$validated['return_qty'][$i];

            if (! $genId || ! $brandId || ! $batchNo) {
                continue;
            }

            $prevOrgRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['return_org'])
                ->orderBy('id', 'desc')
                ->first();
            $prevOrgBalance = $prevOrgRow->org_balance ?? 0;

            $prevSiteRow = InventoryBalance::where('generic_id', $genId)
                ->where('brand_id',  $brandId)
                ->where('batch_no',  $batchNo)
                ->where('org_id',    $validated['return_org'])
                ->where('site_id',   $validated['return_site'])
                ->orderBy('id', 'desc')
                ->first();
            $prevSiteBalance = $prevSiteRow->site_balance ?? 0;

            // Check if both source and destination are inventory locations
            if (strtolower($sourceType) === 'inventory location' && $validated['return_source'] && strtolower($destinationType) === 'inventory location' && $validated['return_destination']) {
                $newOrgBalance  = $prevOrgBalance;  // No change in organization balance
                $newSiteBalance = $prevSiteBalance; // Default to current site balance
                if ($rule->source_action === 'a') {
                    $newSiteBalance += $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSiteBalance -= $qty;
                }
                // Adjust destination location's balance similarly
                if ($rule->destination_action === 'a') {
                    $newSiteBalance += $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newSiteBalance -= $qty;
                }
            } else {
                // Other cases (already exists)
                // Default balance calculations
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
            }

            // $dateTime = Carbon::createFromTimestamp(now()->timestamp)->format('d-M-Y H:i');
            $remarkText = "Transaction Initiated by " . auth()->user()->name . " on {$dateTime} | Batch: {$batchNo} | Qty: {$qty} | New Org Balance: {$newOrgBalance} | New Site Balance: {$newSiteBalance}";
            if (strtolower($sourceType) === 'inventory location' && $validated['return_source'] && strtolower($destinationType) === 'inventory location' && $validated['return_destination']) {
                // Source location row
                $prevSourceLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['return_org'])
                    ->where('site_id',   $validated['return_site'])
                    ->where('location_id', $validated['return_source'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevSourceLocBalance = $prevSourceLocRow->location_balance ?? 0;

                if ($rule->source_action === 'a') {
                    $newSourceLocBalance = $prevSourceLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newSourceLocBalance = $prevSourceLocBalance - $qty;
                } else {
                    $newSourceLocBalance = $prevSourceLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['return_org'],
                    'site_id'          => $validated['return_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['return_source'],
                    'location_balance' => $newSourceLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);

                // Destination location row
                $prevDestLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['return_org'])
                    ->where('site_id',   $validated['return_site'])
                    ->where('location_id', $validated['return_destination'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevDestLocBalance = $prevDestLocRow->location_balance ?? 0;


                if ($rule->destination_action === 'a') {
                    $newDestLocBalance = $prevDestLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newDestLocBalance = $prevDestLocBalance - $qty;
                } else {
                    $newDestLocBalance = $prevDestLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['return_org'],
                    'site_id'          => $validated['return_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['return_destination'],
                    'location_balance' => $newDestLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($sourceType) === 'inventory location' && $validated['return_source']) {
                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['return_org'])
                    ->where('site_id',   $validated['return_site'])
                    ->where('location_id', $validated['return_source'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;


                if ($rule->source_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->source_action === 's' || $rule->source_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }

                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['return_org'],
                    'site_id'          => $validated['return_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['return_source'],
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            elseif (strtolower($destinationType) === 'inventory location' && $validated['return_destination']) {

                $prevLocRow = InventoryBalance::where('generic_id', $genId)
                    ->where('brand_id',  $brandId)
                    ->where('batch_no',  $batchNo)
                    ->where('org_id',    $validated['return_org'])
                    ->where('site_id',   $validated['return_site'])
                    ->where('location_id', $validated['return_destination'])
                    ->orderBy('id', 'desc')
                    ->first();
                $prevLocBalance = $prevLocRow->location_balance ?? 0;



                if ($rule->destination_action === 'a') {
                    $newLocBalance = $prevLocBalance + $qty;
                } elseif ($rule->destination_action === 's' || $rule->destination_action === 'r') {
                    $newLocBalance = $prevLocBalance - $qty;
                } else {
                    $newLocBalance = $prevLocBalance;
                }

                // dd($newLocBalance, $Destination)
                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['return_org'],
                    'site_id'          => $validated['return_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => $validated['return_destination'],
                    'location_balance' => $newLocBalance,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }
            else {
                InventoryBalance::create([
                    'management_id'    => $inventory->id,
                    'generic_id'       => $genId,
                    'brand_id'         => $brandId,
                    'batch_no'         => $batchNo,
                    'org_id'           => $validated['return_org'],
                    'site_id'          => $validated['return_site'],
                    'org_balance'      => $newOrgBalance,
                    'site_balance'     => $newSiteBalance,
                    'location_id'      => null,
                    'location_balance' => null,
                    'remarks'          => $remarkText,
                    'timestamp'        => now()->timestamp,
                ]);
            }

        }

        if ($success) {
            $logId = createLog(
                'inventory_management',
                'insert',
                [
                    'message' => $remarkText,
                    'created_by' => auth()->user()->name ?? 'system'
                ],
                $inventory->id ?? null,
                null,
                null,
                auth()->id() ?? 0
            );

            $inventory->logid = $logId;
            $inventory->save();

            return response()->json([
                'success' => 'Records have been added successfully',
                'reload' => true
            ]);
        } else {
            return response()->json([
                'error' => $message,
                'reload' => false
            ]);
        }
    }
    
    public function GetOrgItemGeneric(Request $request)
    {
        if ($request->has('orgId'))
        {
            $orgId = $request->input('orgId');
            $condition = $request->input('condition');
            //   dd($condition);
     
            // $Generics = InventoryGeneric::where('status', 1)
            // ->where('org_id', $orgId)
            // ->get();

            // dd($condition);
            $Generics = InventoryGeneric::select('inventory_generic.id', 'inventory_generic.name')
            ->join('inventory_category', 'inventory_category.id', '=', 'inventory_generic.cat_id')
            ->where('inventory_generic.status', 1)
            ->where('inventory_generic.org_id', $orgId);

            if ($condition != null && $condition === 'material') {
                $Generics->where('inventory_category.name', 'not like', 'Medicine%');
            } elseif ($condition != null && $condition === 'material_medicine') {
                $Generics->where('inventory_generic.patient_mandatory', '=', 'y');
            }
           $Generics = $Generics->get();    

        }
        return response()->json($Generics);
    }

    public function GetGenericBrand(Request $request)
    {
        if ($request->has('genericId'))
        {
            $genericId = $request->input('genericId');
            // Handle comma-separated generic IDs
            if (strpos($genericId, ',') !== false) {
                $genericIds = explode(',', $genericId);
                
                // Check if "0101" (All Generics) is in the array
                if (in_array('0101', $genericIds)) {
                    $Brands = InventoryBrand::where('status', 1)->select('id', 'name')->get();
                } else {
                    // Filter out any non-numeric values and convert to integers
                    $numericGenericIds = array_map('intval', array_filter($genericIds, 'is_numeric'));
                    $Brands = InventoryBrand::where('status', 1)
                        ->whereIn('generic_id', $numericGenericIds)
                        ->select('id', 'name')
                        ->get();
                }
            } 
            else if ($genericId === '0101') {
                $Brands = InventoryBrand::where('status', 1)->select('id', 'name')->get();
            } 
            else {
                $Brands = InventoryBrand::where('status', 1)
                    ->where('generic_id', intval($genericId))
                    ->select('id', 'name')
                    ->get();
            }
            
            return response()->json($Brands);
        }
        return response()->json([]);
    }
}