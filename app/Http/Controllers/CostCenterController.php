<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CostCenterTypeRequest;
use App\Http\Requests\CCActivationRequest;
use App\Http\Requests\CostCenterRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Logs;
use App\Models\CCType;
use App\Models\CostCenter;
use App\Models\Service;
use App\Models\ServiceMode;
use App\Models\Organization;
use App\Models\Site;
use App\Models\ActivateCC;

class CostCenterController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
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

    public function CostCenterType()
    {
        $colName = 'cost_center_types';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.costcentertype', compact('user'));
    }

    public function AddCostCenterType(CostCenterTypeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->cost_center_types)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $CCtype = trim($request->input('cc_type'));
        $OrderingCC = trim($request->input('ordering_cc'));
        $PerformingCC = trim($request->input('performing_cc'));
        $remarks = trim($request->input('cc_remarks'));
        $CCTEdt = $request->input('cct_edt');
        $CCTEdt = Carbon::createFromFormat('l d F Y - h:i A', $CCTEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($CCTEdt)->setTimezone('Asia/Karachi');
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

        $CCTExists = CCType::where('type', $CCtype)
        ->exists();
        if ($CCTExists) {
            return response()->json(['info' => 'Cost Center Type already exists.']);
        }
        else
        {
            $CostCenterType = new CCType();
            $CostCenterType->type = $CCtype;
            $CostCenterType->remarks = $remarks;
            $CostCenterType->ordering = $OrderingCC;
            $CostCenterType->performing = $PerformingCC;
            $CostCenterType->status = $status;
            $CostCenterType->user_id = $sessionId;
            $CostCenterType->last_updated = $last_updated;
            $CostCenterType->timestamp = $timestamp;
            $CostCenterType->effective_timestamp = $CCTEdt;
            $CostCenterType->save();

            if (empty($CostCenterType->id)) {
                return response()->json(['error' => 'Failed to create Cost Center Type.']);
            }

            $logs = Logs::create([
                'module' => 'cost center',
                'content' => "'{$CCtype}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $CostCenterType->logid = $logs->id;
            $CostCenterType->save();
            return response()->json(['success' => 'Cost Center Type created successfully']);
        }

    }

    public function GetCostCenterTypeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->cost_center_types)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $CCTypes = CCType::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($CCTypes)
        return DataTables::eloquent($CCTypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('type', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('remarks', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($CCType) {
                return $CCType->id;  // Raw ID value
            })
            ->editColumn('id', function ($CCType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $CCTypeName = $CCType->type;
                $idStr = str_pad($CCType->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($CCType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($CCType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($CCType->last_updated)->format('l d F Y - h:i A');
                $OrderingCC = $CCType->ordering ? "<span class='badge badge-info'>Enabled</span>" : "<span class='badge badge-danger'>Disabled</span>" ;
                $PerformingCC = $CCType->performing ? "<span class='badge badge-info'>Enabled</span>" : "<span class='badge badge-danger'>Disabled</span>" ;
                $createdByName = getUserNameById($CCType->user_id);
                $createdInfo = "
                        <b>OrderingCC:</b> " . $OrderingCC . " <br>
                        <b>PerformingCC:</b> " . $PerformingCC . " <br>
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'CCT';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $CCTypeName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($CCType) {
                    $CCTypeId = $CCType->id;
                    $logId = $CCType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->cost_center_types)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-cctype" data-cctype-id="'.$CCTypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $CCType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($CCType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->cost_center_types)[3];
                return $updateStatus == 1 ? ($CCType->status ? '<span class="label label-success ccType_status cursor-pointer" data-id="'.$CCType->id.'" data-status="'.$CCType->status.'">Active</span>' : '<span class="label label-danger ccType_status cursor-pointer" data-id="'.$CCType->id.'" data-status="'.$CCType->status.'">Inactive</span>') : ($CCType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateCCTypeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->cost_center_types)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $CCTypeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $CCType = CCType::find($CCTypeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $CCType->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $CCType->effective_timestamp = 0;
        }
        // Find the role by ID
        $CCType->status = $UpdateStatus;
        $CCType->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'cost center',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $CCTypeLog = CCType::where('id', $CCTypeID)->first();
        $logIds = $CCTypeLog->logid ? explode(',', $CCTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $CCTypeLog->logid = implode(',', $logIds);
        $CCTypeLog->save();

        $CCType->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateCCTypeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cost_center_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $CCType = CCType::find($id);
        $CCTypeName = ucwords($CCType->type);
        $remarks = ucfirst($CCType->remarks);
        $orderingid = $CCType->ordering;
        $ordering = $orderingid ? "Enabled" : "Disabled";
        $performingid = $CCType->performing;
        $performing = $performingid ? "Enabled" : "Disabled";
        $effective_timestamp = $CCType->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'type' => $CCTypeName,
            'remarks' => $remarks,
            'ordering' => $ordering,
            'performing' => $performing,
            'orderingid' => $orderingid,
            'performingid' => $performingid,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateCCType(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cost_center_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $CCType = CCType::findOrFail($id);
        $CCType->type = $request->input('u_ccType');
        $CCType->ordering = $request->input('u_ordering');
        $CCType->performing = $request->input('u_performing');
        $CCType->remarks = $request->input('u_ccremarks');
        $effective_date = $request->input('cct_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $CCType->effective_timestamp = $effective_date;
        $CCType->last_updated = $this->currentDatetime;
        $CCType->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $CCType->save();

        if (empty($CCType->id)) {
            return response()->json(['error' => 'Failed to update Cost Center Type. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'cost center',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $CCTypeLog = CCType::where('id', $CCType->id)->first();
        $logIds = $CCTypeLog->logid ? explode(',', $CCTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $CCTypeLog->logid = implode(',', $logIds);
        $CCTypeLog->save();
        return response()->json(['success' => 'Cost Center Type updated successfully']);
    }

    public function CostCenter()
    {
        $colName = 'cost_center_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }

        $ccTypes = CCType::where('status', 1)->get();
        $user = auth()->user();
        return view('dashboard.costcenter', compact('user','ccTypes'));
    }

    public function AddCostCenter(CostCenterRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->cost_center_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $ccName = trim($request->input('cost_center'));
        $Code = trim(strtolower($request->input('cc_code')));
        $ccType = trim($request->input('cc_type'));
        $CCEdt = $request->input('cc_edt');
        $CCEdt = Carbon::createFromFormat('l d F Y - h:i A', $CCEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($CCEdt)->setTimezone('Asia/Karachi');
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

        $CCExists = CostCenter::where(function($query) use ($ccName, $Code) {
            $query->where('name', $ccName)
                  ->orWhere('code', $Code);
        })
        ->where('status', '1')
        ->exists();

        if ($CCExists) {
            return response()->json(['info' => 'Cost Center already exists.']);
        }
        else
        {
            $CostCenter = new CostCenter();
            $CostCenter->code = $Code;
            $CostCenter->name = $ccName;
            $CostCenter->cc_type = $ccType;
            $CostCenter->status = $status;
            $CostCenter->user_id = $sessionId;
            $CostCenter->last_updated = $last_updated;
            $CostCenter->timestamp = $timestamp;
            $CostCenter->effective_timestamp = $CCEdt;
            $CostCenter->save();

            if (empty($CostCenter->id)) {
                return response()->json(['error' => 'Failed to create Cost Center.']);
            }

            $logs = Logs::create([
                'module' => 'cost center',
                'content' => "'{$ccName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $CostCenter->logid = $logs->id;
            $CostCenter->save();
            return response()->json(['success' => 'Cost Center created successfully']);
        }

    }

    public function GetCostCenterData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->cost_center_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $CostCenters = CostCenter::select('costcenter.*', 'cc_type.type',
        'cc_type.ordering as orderingStatus','cc_type.performing as performingStatus')
        ->join('cc_type', 'cc_type.id', '=', 'costcenter.cc_type')
        ->orderBy('costcenter.id', 'desc');

        if ($request->has('cc_type') && $request->cc_type != '' && $request->cc_type != 'Loading...') {
            $CostCenters->where('costcenter.cc_type', $request->cc_type);
        }
        // ->get();

        // return DataTables::of($CostCenters)
        return DataTables::eloquent($CostCenters)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('costcenter.name', 'like', "%{$search}%")
                            ->orWhere('costcenter.id', 'like', "%{$search}%")
                            ->orWhere('costcenter.status', 'like', "%{$search}%")
                            ->orWhere('cc_type.type', 'like', "%{$search}%")
                            ->orWhere('costcenter.code', 'like', "%{$search}%")
                            ->orWhere('cc_type.ordering', 'like', "%{$search}%")
                            ->orWhere('cc_type.performing', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($CostCenter) {
                return $CostCenter->id;  // Raw ID value
            })
            ->editColumn('id', function ($CostCenter) {
                $session = auth()->user();
                $sessionName = $session->name;
                $CostCenterName = $CostCenter->name;
                $words = explode(' ', $CostCenterName);
                $code = '';

                foreach ($words as $word) {
                    $firstLetter = substr($word, 0, 1);
                    $lastLetter = substr($word, -1);
                    $code .= $firstLetter . $lastLetter;

                    if (strlen($code) >= 6) {
                        $code = substr($code, 0, 6);
                        break;
                    }
                }
                $idStr = str_pad($CostCenter->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($CostCenter->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($CostCenter->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($CostCenter->last_updated)->format('l d F Y - h:i A');
                $OrderingCC = $CostCenter->orderingStatus ? "<span class='badge badge-info'>Enabled</span>" : "<span class='badge badge-danger'>Disabled</span>" ;
                $PerformingCC = $CostCenter->performingStatus ? "<span class='badge badge-info'>Enabled</span>" : "<span class='badge badge-danger'>Disabled</span>" ;
                $createdByName = getUserNameById($CostCenter->user_id);

                $createdInfo = "
                        <b>OrderingCC:</b> " . $OrderingCC . " <br>
                        <b>PerformingCC:</b> " . $PerformingCC . " <br>
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $Code = strtoupper($CostCenter->code);

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($CostCenter) {
                    $CCId = $CostCenter->id;
                    $logId = $CostCenter->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->cost_center_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-costcenter" data-costcenter-id="'.$CCId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $CostCenter->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($CostCenter) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->cost_center_setup)[3];
                return $updateStatus == 1 ? ($CostCenter->status ? '<span class="label label-success cc_status cursor-pointer" data-id="'.$CostCenter->id.'" data-status="'.$CostCenter->status.'">Active</span>' : '<span class="label label-danger cc_status cursor-pointer" data-id="'.$CostCenter->id.'" data-status="'.$CostCenter->status.'">Inactive</span>') : ($CostCenter->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateCostCenterStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->cost_center_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $CostCenterID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $CostCenter = CostCenter::find($CostCenterID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $CostCenter->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $CostCenter->effective_timestamp = 0;

        }
        // Find the role by ID
        $CostCenter->status = $UpdateStatus;
        $CostCenter->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'cost center',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $CostCenterLog = CostCenter::where('id', $CostCenterID)->first();
        $logIds = $CostCenterLog->logid ? explode(',', $CostCenterLog->logid) : [];
        $logIds[] = $logs->id;
        $CostCenterLog->logid = implode(',', $logIds);
        $CostCenterLog->save();

        $CostCenter->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateCostCenterModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cost_center_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $CostCenterData = CostCenter::select('costcenter.*', 'cc_type.type')
        ->join('cc_type', 'cc_type.id', '=', 'costcenter.cc_type')
        ->where('costcenter.id', '=', $id) // Replace 'column' and 'value' with your desired condition
        ->orderBy('costcenter.id', 'desc')
        ->first();
        $CostCenter = ucwords($CostCenterData->name);
        $ccType = ucfirst($CostCenterData->type);
        $ccTypeId = ucfirst($CostCenterData->cc_type);
        $effective_timestamp = $CostCenterData->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'typeid' => $ccTypeId,
            'name' => $CostCenter,
            'ccType' => $ccType,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function GetSelectedCCType(Request $request)
    {
        $ccTypeId = $request->input('ccTypeId');
        $ccTypeIds = explode(',', $ccTypeId);

        $ccType = CCType::whereNotIn('id', $ccTypeIds)
                     ->where('status', 1)
                     ->get();

        return response()->json($ccType);
    }


    public function GetSelectedCC(Request $request)
    {
        $siteId = $request->input('siteId');

        $CostCenters = CostCenter::select('costcenter.*', 'activated_cc.cc_id',
                'activated_cc.status as activated_status', 'activated_cc.site_id')
                ->join('activated_cc', 'activated_cc.cc_id', '=', 'costcenter.id')
                ->where('costcenter.status', 1)
                ->where('activated_cc.status', 1)
                ->where('activated_cc.site_id', $siteId);
        if ($request->has('ccID'))
        {
            $ccID = $request->input('ccID');
            $CostCenters->where('costcenter.id', '!=', $ccID);
        }
        $CostCenters = $CostCenters->get();
        return response()->json($CostCenters);
    }

    public function GetOrderingPerformingCC(Request $request)
    {
        $siteId = $request->input('siteId');
        $ServiceIds = $request->input('ServiceIds');
    
        $CostCenters = CostCenter::select(
                'costcenter.id', 
                'costcenter.name', 
                'activated_cc.cc_id', 
                'activated_cc.status as activated_status', 
                'activated_cc.site_id',
                'cc_type.performing', 
                'cc_type.ordering'
            )
            ->join('activated_cc', 'activated_cc.cc_id', '=', 'costcenter.id')
            ->join('cc_type', 'cc_type.id', '=', 'costcenter.cc_type')  
            ->where('costcenter.status', 1)
            ->where('activated_cc.status', 1)
            ->where('activated_cc.site_id', $siteId)
            ->get();

        $Services = Service::select(
                'services.id',
                'services.name',
                'service_group.name as servicegroupName',
                'service_type.name as servicetypeName'
            )
            ->join('service_group', 'service_group.id', '=', 'services.group_id')
            ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
            ->whereIn('services.id',  explode(',', $ServiceIds))
            ->where('services.status', 1)
            ->get();

        $ServiceModes = ServiceMode::select(
                'service_mode.id',
                'service_mode.name',
            )
            ->where('billing_mode', 'direct billing')
            ->where('status', 1)
            ->get();
    
        // Return both Cost Centers and Services data
        return response()->json([
            'CostCenters' => $CostCenters,
            'Services' => $Services,
            'ServiceModes' => $ServiceModes
        ]);
    }

    public function GetOrderingCC(Request $request)
    {
        $siteId = $request->input('siteId');
    
        $CostCenters = CostCenter::select(
                'costcenter.id', 
                'costcenter.name', 
            )
            ->join('activated_cc', 'activated_cc.cc_id', '=', 'costcenter.id')
            ->join('cc_type', 'cc_type.id', '=', 'costcenter.cc_type')  
            ->where('costcenter.status', 1)
            ->where('cc_type.ordering', 1)
            ->where('activated_cc.site_id', $siteId)
            ->get();
        
        return response()->json($CostCenters);
    }

    public function GetPerformingCC(Request $request)
    {
        $siteId = $request->input('siteId');
    
        $CostCenters = CostCenter::select(
                'costcenter.id', 
                'costcenter.name', 
            )
            ->join('activated_cc', 'activated_cc.cc_id', '=', 'costcenter.id')
            ->join('cc_type', 'cc_type.id', '=', 'costcenter.cc_type')  
            ->where('costcenter.status', 1)
            ->where('cc_type.performing', 1)
            ->where('activated_cc.site_id', $siteId)
            ->get();
        
        return response()->json($CostCenters);
    }

    public function UpdateCostCenter(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cost_center_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $CostCenters = CostCenter::findOrFail($id);
        $CostCenters->name = $request->input('cc_name');
        $CostCenters->cc_type = $request->input('u_ccType');
        $effective_date = $request->input('u_cc_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $CostCenters->effective_timestamp = $effective_date;
        $CostCenters->last_updated = $this->currentDatetime;
        $CostCenters->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $CostCenters->save();

        if (empty($CostCenters->id)) {
            return response()->json(['error' => 'Failed to update Cost Center. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'cost center',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $CostCenterLog = CostCenter::where('id', $CostCenters->id)->first();
        $logIds = $CostCenterLog->logid ? explode(',', $CostCenterLog->logid) : [];
        $logIds[] = $logs->id;
        $CostCenterLog->logid = implode(',', $logIds);
        $CostCenterLog->save();
        return response()->json(['success' => 'Cost Center updated successfully']);
    }
    public function CostCenterActivation()
    {
        $colName = 'cost_center_activation';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        // $CostCenters = CostCenter::where('status', 1)->get();
        $CostCenters = CostCenter::where('costcenter.status', 1)
        ->join('cc_type', 'costcenter.cc_type', '=', 'cc_type.id')
        ->select('costcenter.*', 
            DB::raw("CASE 
                        WHEN cc_type.ordering = 1 AND cc_type.performing = 1 THEN 'Direct Billing Clinical'
                        WHEN cc_type.ordering = 0 AND cc_type.performing = 1 THEN 'Indirect Billing Clinical'
                        WHEN cc_type.ordering = 0 AND cc_type.performing = 0 THEN 'Indirect Billing Non-Clinical'
                        ELSE 'Other' 
                    END as category"))
        ->orderBy('category', 'asc') // Order by category to simplify display
        ->get();

        $RawCostCenters = CostCenter::where('status', 1)->get();
        $CCTypes = CCType::where('status', 1)->get();
        $Sites = Site::where('status', 1)->get();

        return view('dashboard.cc-activation', compact('user','Organizations','CostCenters','RawCostCenters','Sites','CCTypes'));
    }

    public function GetNotActivatedCC(Request $request)
    {
        $colName = 'cost_center_activation';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $siteId = $request->input('siteId');
        $CostCenters = CostCenter::where('costcenter.status', 1)
        ->join('cc_type', 'costcenter.cc_type', '=', 'cc_type.id')
        ->leftJoin('activated_cc', function ($join) use ($siteId) {
            $join->on('costcenter.id', '=', 'activated_cc.cc_id')
                 ->where('activated_cc.site_id', '=', $siteId);
        })
        ->whereNull('activated_cc.cc_id') 
        ->select('costcenter.*', 
            DB::raw("CASE 
                        WHEN cc_type.ordering = 1 AND cc_type.performing = 1 THEN 'Direct Billing Clinical'
                        WHEN cc_type.ordering = 0 AND cc_type.performing = 1 THEN 'Indirect Billing Clinical'
                        WHEN cc_type.ordering = 0 AND cc_type.performing = 0 THEN 'Indirect Billing Non-Clinical'
                        ELSE 'Other' 
                    END as category"))
        ->orderBy('category', 'asc') 
        ->get();
        
        return response()->json($CostCenters);
    }

    public function GetAllActivatedCC(Request $request)
    {
        $colName = 'cost_center_activation';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $siteId = $request->input('siteId');
        $CostCenters = CostCenter::where('costcenter.status', 1)
        ->join('cc_type', 'costcenter.cc_type', '=', 'cc_type.id')
        ->leftJoin('activated_cc', function ($join) use ($siteId) {
            $join->on('costcenter.id', '=', 'activated_cc.cc_id')
                 ->where('activated_cc.site_id', '=', $siteId);
        })
        ->whereNull('activated_cc.cc_id') 
        ->select('costcenter.*', 
            DB::raw("CASE 
                        WHEN cc_type.ordering = 1 AND cc_type.performing = 1 THEN 'Direct Billing Clinical'
                        WHEN cc_type.ordering = 0 AND cc_type.performing = 1 THEN 'Indirect Billing Clinical'
                        WHEN cc_type.ordering = 0 AND cc_type.performing = 0 THEN 'Indirect Billing Non-Clinical'
                        ELSE 'Other' 
                    END as category"))
        ->orderBy('category', 'asc') 
        ->get();
        
        return response()->json($CostCenters);
    }
    public function ActivateCostCenter(CCActivationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->cost_center_activation)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $ccOrg = trim($request->input('cc_org'));
        $ccSite = trim($request->input('cc_site'));
        $ccNames = $request->input('cc_name')[0]; 
        $ccNamesArray = explode(',', $ccNames);

        $Edt = $request->input('a_cc_edt');
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

        // $totalCount = 0; // Count of newly activated cost centers
        // $activatedCount = 0; // Count of newly activated cost centers
        // $alreadyActivatedCount = 0; // Count of already activated cost centers

        foreach ($ccNamesArray as $ccName) {
            // $totalCount++;
            $ACCExists = ActivateCC::where('org_id', $ccOrg)
            ->where('site_id', $ccSite)
            ->where('cc_id',$ccName)
            ->exists();
            if ($ACCExists) {
                // $alreadyActivatedCount++;
                return response()->json(['info' => 'Cost Center already Activated.']);
            }
            else
            {
                $ActivateCC = new ActivateCC();
                $ActivateCC->org_id = $ccOrg;
                $ActivateCC->site_id = $ccSite;
                $ActivateCC->cc_id = $ccName;
                $ActivateCC->status = $status;
                $ActivateCC->user_id = $sessionId;
                $ActivateCC->last_updated = $last_updated;
                $ActivateCC->timestamp = $timestamp;
                $ActivateCC->effective_timestamp = $Edt;
                $ActivateCC->save();

                if (empty($ActivateCC->id)) {
                    return response()->json(['error' => 'Failed to Activate Cost Center.']);
                }

                $logs = Logs::create([
                    'module' => 'cost center',
                    'content' => "Cost Center activated by '{$sessionName}'",
                    'event' => 'activate',
                    'timestamp' => $timestamp,
                ]);
                $logId = $logs->id;
                $ActivateCC->logid = $logs->id;
                $ActivateCC->save();
                // $activatedCount++;
            }
        }
        return response()->json(['success' => 'Cost Centers Activated successfully']);
    }

    public function GetActivatedCCData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->cost_center_activation)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatedCC = ActivateCC::select('activated_cc.*', 'organization.organization as orgName',
         'org_site.name as siteName','costcenter.name as ccName','cc_type.type as cctypeName')
        ->leftJoin('organization', 'organization.id', '=', 'activated_cc.org_id')
        ->join('org_site', 'org_site.id', '=', 'activated_cc.site_id')
        ->join('costcenter', 'costcenter.id', '=', 'activated_cc.cc_id')
        ->join('cc_type', 'cc_type.id', '=', 'costcenter.cc_type')
        ->orderBy('activated_cc.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ActivatedCC->where('activated_cc.org_id', '=', $sessionOrg);
        }

        if ($request->has('site') && $request->site != '' && $request->site != 'Loading...') {
            $ActivatedCC->where('activated_cc.site_id', $request->site);
        } 
        
        if ($request->has('costcenter') && $request->costcenter != '' && $request->costcenter != 'Loading...') {
            $ActivatedCC->where('activated_cc.cc_id', $request->costcenter);
        } 
        
        if ($request->has('cc_type') && $request->cc_type != '' && $request->cc_type != 'Loading...') {
            $ActivatedCC->where('costcenter.cc_type', $request->cc_type);
        } 

        $ActivatedCC = $ActivatedCC;
        // ->get()

        // return DataTables::of($ActivatedCC)
        return DataTables::eloquent($ActivatedCC)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('activated_cc.id', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('costcenter.name', 'like', "%{$search}%")
                            ->orWhere('cc_type.type', 'like', "%{$search}%")
                            ->orWhere('activated_cc.status', 'like', "%{$search}%")
                            ->orWhere('activated_cc.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ActivateCC) {
                return $ActivateCC->id;  // Raw ID value
            })
            ->editColumn('id', function ($ActivateCC) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgId = $ActivateCC->org_id;
                    $orgName = Organization::where('id', $orgId)->value('organization');
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($orgName);
                }

                $idStr = str_pad($ActivateCC->id, 5, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($ActivateCC->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ActivateCC->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ActivateCC->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ActivateCC->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'CCA';
                $cleanName = preg_replace('/[^A-Za-z0-9 ]/', '', $ActivateCC->ccName); // Remove special characters
                $firstLetters = strtoupper(implode('', array_map(fn($word) => substr($word, 0, 1), explode(' ', $cleanName))));
                // $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $ActivateCC->ccName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($ActivateCC) {
                    $ActivateCCId = $ActivateCC->id;
                    $logId = $ActivateCC->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->cost_center_activation)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-activatecc" data-activatecc-id="'.$ActivateCCId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    
                    return $ActivateCC->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($ActivateCC) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->cost_center_activation)[3];
                return $updateStatus == 1 ? ($ActivateCC->status ? '<span class="label label-success activatecc cursor-pointer" data-id="'.$ActivateCC->id.'" data-status="'.$ActivateCC->status.'">Active</span>' : '<span class="label label-danger activatecc cursor-pointer" data-id="'.$ActivateCC->id.'" data-status="'.$ActivateCC->status.'">Inactive</span>') : ($ActivateCC->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateActivatedCCStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->cost_center_activation)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivateCCID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ActivatedCC = ActivateCC::find($ActivateCCID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ActivatedCC->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ActivatedCC->effective_timestamp = 0;
        }
        // Find the role by ID
        $ActivatedCC->status = $UpdateStatus;
        $ActivatedCC->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'cost center',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivateCCLog = ActivateCC::where('id', $ActivateCCID)->first();
        $logIds = $ActivateCCLog->logid ? explode(',', $ActivateCCLog->logid) : [];
        $logIds[] = $logs->id;
        $ActivateCCLog->logid = implode(',', $logIds);
        $ActivateCCLog->save();

        $ActivatedCC->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateActivatedCCModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cost_center_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatedCCData = ActivateCC::select('activated_cc.*', 'organization.organization as orgName',
         'org_site.name as siteName','costcenter.name as ccName')
        ->join('organization', 'organization.id', '=', 'activated_cc.org_id')
        ->join('org_site', 'org_site.id', '=', 'activated_cc.site_id')
        ->join('costcenter', 'costcenter.id', '=', 'activated_cc.cc_id')
        ->where('activated_cc.id', $id)
        ->first();


        $orgName = ucwords($ActivatedCCData->orgName);
        $siteName = ucwords($ActivatedCCData->siteName);
        $ccName = ucwords($ActivatedCCData->ccName);

        $effective_timestamp = $ActivatedCCData->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgName' => $orgName,
            'orgID' => $ActivatedCCData->org_id,
            'siteName' => $siteName,
            'siteId' => $ActivatedCCData->site_id,
            'ccName' => $ccName,
            'ccID' => $ActivatedCCData->cc_id,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function GetSelectedCostCenter(Request $request)
    {
        $CostCenterId = $request->input('ccID');
        $siteId = $request->input('siteId');
        $CostCenterIds = explode(',', $CostCenterId);


        $CostCenter = CostCenter::whereNotIn('costcenter.id', $CostCenterIds)
        ->where('costcenter.status', 1)
        ->leftJoin('activated_cc', function ($join) use ($siteId) {
            $join->on('costcenter.id', '=', 'activated_cc.cc_id')
                ->where('activated_cc.site_id', '=', $siteId);
        })
        ->whereNull('activated_cc.cc_id')
        ->select('costcenter.*')
        ->get();
        return response()->json($CostCenter);
    }

    public function UpdateActivatedCC(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cost_center_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatedCC = ActivateCC::findOrFail($id);
        $orgID = $request->input('u_ccorg');

        if (isset($orgID)) {
            $ActivatedCC->org_id = $orgID;
        }    
        $ActivatedCC->site_id = $request->input('u_ccsite');
        $ActivatedCC->cc_id = $request->input('u_costcenter');
        $effective_date = $request->input('uacc_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ActivatedCC->effective_timestamp = $effective_date;
        $ActivatedCC->last_updated = $this->currentDatetime;
        $ActivatedCC->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ActivatedCC->save();

        if (empty($ActivatedCC->id)) {
            return response()->json(['error' => 'Cost Center Activation Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'cost center',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivatedCCLog = ActivateCC::where('id', $ActivatedCC->id)->first();
        $logIds = $ActivatedCCLog->logid ? explode(',', $ActivatedCCLog->logid) : [];
        $logIds[] = $logs->id;
        $ActivatedCCLog->logid = implode(',', $logIds);
        $ActivatedCCLog->save();
        return response()->json(['success' => 'Cost Center Activation updated successfully']);
    }

}
