<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\KPIGroupRequest;
use App\Http\Requests\KPIDimensionRequest;
use App\Http\Requests\KPITypeRequest;
use App\Http\Requests\KPIRequest;
use App\Http\Requests\KPIActivationRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KPIGroups;
use App\Models\KPIDimensions;
use App\Models\KPITypes;
use App\Models\KPI;
use App\Models\Organization;
use App\Models\CostCenter;
use App\Models\ServiceMode;
use App\Models\Service;
use App\Models\KPIActivation;

class KeyPerformanceIndicatorController extends Controller
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

    public function KPIGroup()
    {
        $colName = 'kpi_group';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.kpigroup', compact('user'));
    }

    public function AddKPIGroup(KPIGroupRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->kpi_group)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $KGName = trim($request->input('kg_name'));
        $Code = trim(strtolower($request->input('kg_code')));
        $KGEdt = $request->input('kg_edt');
        $KGEdt = Carbon::createFromFormat('l d F Y - h:i A', $KGEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($KGEdt)->setTimezone('Asia/Karachi');
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

        $KGExists = KPIGroups::where(function($query) use ($KGName, $Code) {
            $query->where('name', $KGName)
                  ->orWhere('code', $Code);
        })
        ->where('status', '1')
        ->exists();


        if ($KGExists) {
            return response()->json(['info' => 'KPI Group already exists.']);
        }
        else
        {
            $KPIGroup = new KPIGroups();
            $KPIGroup->code = $Code;
            $KPIGroup->name = $KGName;
            $KPIGroup->status = $status;
            $KPIGroup->user_id = $sessionId;
            $KPIGroup->last_updated = $last_updated;
            $KPIGroup->timestamp = $timestamp;
            $KPIGroup->effective_timestamp = $KGEdt;
            $KPIGroup->save();

            if (empty($KPIGroup->id)) {
                return response()->json(['error' => 'Failed to create KPI Group.']);
            }

            $logs = Logs::create([
                'module' => 'kpi',
                'content' => "'{$KGName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $KPIGroup->logid = $logs->id;
            $KPIGroup->save();
            return response()->json(['success' => 'KPI Group created successfully']);
        }

    }

    public function GetKPIGroupData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->kpi_group)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIGroups = KPIGroups::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($KPIGroups)
        return DataTables::eloquent($KPIGroups)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($KPIGroup) {
                return $KPIGroup->id;  // Raw ID value
            })
            ->editColumn('id', function ($KPIGroup) {
                $session = auth()->user();
                $sessionName = $session->name;
                $effectiveDate = Carbon::createFromTimestamp($KPIGroup->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($KPIGroup->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($KPIGroup->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($KPIGroup->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $Code = strtoupper($KPIGroup->code);

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($KPIGroup) {
                    $KPIGroupId = $KPIGroup->id;
                    $logId = $KPIGroup->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->kpi_group)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-kpigroup" data-kpigroup-id="'.$KPIGroupId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $KPIGroup->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($KPIGroup) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->kpi_group)[3];
                return $updateStatus == 1 ? ($KPIGroup->status ? '<span class="label label-success kpigroup_status cursor-pointer" data-id="'.$KPIGroup->id.'" data-status="'.$KPIGroup->status.'">Active</span>' : '<span class="label label-danger kpigroup_status cursor-pointer" data-id="'.$KPIGroup->id.'" data-status="'.$KPIGroup->status.'">Inactive</span>') : ($KPIGroup->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateKPIGroupStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->kpi_group)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIGroupID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $KPIGroup = KPIGroups::find($KPIGroupID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $KPIGroup->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $KPIGroup->status = $UpdateStatus;
        $KPIGroup->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPIGroupLog = KPIGroups::where('id', $KPIGroupID)->first();
        $logIds = $KPIGroupLog->logid ? explode(',', $KPIGroupLog->logid) : [];
        $logIds[] = $logs->id;
        $KPIGroupLog->logid = implode(',', $logIds);
        $KPIGroupLog->save();

        $KPIGroup->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateKPIGroupModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_group)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIGroup = KPIGroups::find($id);
        $KPIGroupName = ucwords($KPIGroup->name);
        $effective_timestamp = $KPIGroup->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $KPIGroupName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateKPIGroup(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_group)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $KPIGroups = KPIGroups::findOrFail($id);
        $KPIGroups->name = $request->input('u_kg');
        $effective_date = $request->input('u_kg_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $KPIGroups->effective_timestamp = $effective_date;
        $KPIGroups->last_updated = $this->currentDatetime;
        $KPIGroups->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $KPIGroups->save();

        if (empty($KPIGroups->id)) {
            return response()->json(['error' => 'Failed to update KPI Group. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPIGroupLog = KPIGroups::where('id', $KPIGroups->id)->first();
        $logIds = $KPIGroupLog->logid ? explode(',', $KPIGroupLog->logid) : [];
        $logIds[] = $logs->id;
        $KPIGroupLog->logid = implode(',', $logIds);
        $KPIGroupLog->save();
        return response()->json(['success' => 'KPI Group updated successfully']);
    }

    public function KPIDimension()
    {
        $colName = 'kpi_dimension';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.kpidimension', compact('user'));
    }

    public function AddKPIDimension(KPIDimensionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->kpi_dimension)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $KDName = trim($request->input('kd_name'));
        $Code = trim(strtolower($request->input('kd_code')));
        $KDEdt = $request->input('kd_edt');
        $KDEdt = Carbon::createFromFormat('l d F Y - h:i A', $KDEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($KDEdt)->setTimezone('Asia/Karachi');
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

        $KDExists = KPIDimensions::where(function($query) use ($KDName, $Code) {
            $query->where('name', $KDName)
                  ->orWhere('code', $Code);
        })
        ->where('status', '1')
        ->exists();

        if ($KDExists) {
            return response()->json(['info' => 'KPI Dimension already exists.']);
        }
        else
        {
            $KPIDimension = new KPIDimensions();
            $KPIDimension->code = $Code;
            $KPIDimension->name = $KDName;
            $KPIDimension->status = $status;
            $KPIDimension->user_id = $sessionId;
            $KPIDimension->last_updated = $last_updated;
            $KPIDimension->timestamp = $timestamp;
            $KPIDimension->effective_timestamp = $KDEdt;
            $KPIDimension->save();

            if (empty($KPIDimension->id)) {
                return response()->json(['error' => 'Failed to create KPI Dimension.']);
            }

            $logs = Logs::create([
                'module' => 'kpi',
                'content' => "'{$KDName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $KPIDimension->logid = $logs->id;
            $KPIDimension->save();
            return response()->json(['success' => 'KPI Dimension created successfully']);
        }

    }

    public function GetKPIDimensionData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->kpi_dimension)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIDimensions = KPIDimensions::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($KPIDimensions)
        return DataTables::eloquent($KPIDimensions)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($KPIDimension) {
                return $KPIDimension->id;  // Raw ID value
            })
            ->editColumn('id', function ($KPIDimension) {
                $session = auth()->user();
                $sessionName = $session->name;
                $effectiveDate = Carbon::createFromTimestamp($KPIDimension->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($KPIDimension->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($KPIDimension->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($KPIDimension->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;
        
                $Code = strtoupper($KPIDimension->code);

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($KPIDimension) {
                    $KPIDimensionId = $KPIDimension->id;
                    $logId = $KPIDimension->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->kpi_dimension)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-kpidimension" data-kpidimension-id="'.$KPIDimensionId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $KPIDimension->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($KPIDimension) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->kpi_dimension)[3];
                return $updateStatus == 1 ? ($KPIDimension->status ? '<span class="label label-success kpidimension_status cursor-pointer" data-id="'.$KPIDimension->id.'" data-status="'.$KPIDimension->status.'">Active</span>' : '<span class="label label-danger kpidimension_status cursor-pointer" data-id="'.$KPIDimension->id.'" data-status="'.$KPIDimension->status.'">Inactive</span>') : ($KPIDimension->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateKPIDimensionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->kpi_dimension)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIDimensionID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $KPIDimension = KPIDimensions::find($KPIDimensionID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $KPIDimension->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $KPIDimension->status = $UpdateStatus;
        $KPIDimension->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPIDimensionLog = KPIDimensions::where('id', $KPIDimensionID)->first();
        $logIds = $KPIDimensionLog->logid ? explode(',', $KPIDimensionLog->logid) : [];
        $logIds[] = $logs->id;
        $KPIDimensionLog->logid = implode(',', $logIds);
        $KPIDimensionLog->save();

        $KPIDimension->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateKPIDimensionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_dimension)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIDimension = KPIDimensions::find($id);
        $KPIDimensionName = ucwords($KPIDimension->name);
        $effective_timestamp = $KPIDimension->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $KPIDimensionName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateKPIDimension(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_dimension)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIDimensions = KPIDimensions::findOrFail($id);
        $KPIDimensions->name = $request->input('u_kd');
        $effective_date = $request->input('u_kd_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $KPIDimensions->effective_timestamp = $effective_date;
        $KPIDimensions->last_updated = $this->currentDatetime;
        $KPIDimensions->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $KPIDimensions->save();

        if (empty($KPIDimensions->id)) {
            return response()->json(['error' => 'Failed to update KPI Dimension. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPIDimensionLog = KPIDimensions::where('id', $KPIDimensions->id)->first();
        $logIds = $KPIDimensionLog->logid ? explode(',', $KPIDimensionLog->logid) : [];
        $logIds[] = $logs->id;
        $KPIDimensionLog->logid = implode(',', $logIds);
        $KPIDimensionLog->save();
        return response()->json(['success' => 'KPI Dimension updated successfully']);
    }

    public function KPIType()
    {
        $colName = 'kpi_types';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $KPIGroups = KPIGroups::where('status', 1)->get();
        $KPIDimensions = KPIDimensions::where('status', 1)->get();
        return view('dashboard.kpitype', compact('user','KPIGroups','KPIDimensions'));
    }

    public function AddKPIType(KPITypeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->kpi_types)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $KTName = addslashes(trim($request->input('kt_name')));
        $Code = trim(strtolower($request->input('kt_code')));
        $KTGroup = $request->input('kt_group');
        $KTDimension = $request->input('kt_dimension');
        $KTEdt = $request->input('kt_edt');
        $KTEdt = Carbon::createFromFormat('l d F Y - h:i A', $KTEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($KTEdt)->setTimezone('Asia/Karachi');
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
    
        $KTExists = KPITypes::where(function($query) use ($KTName, $Code) {
            $query->where('name', $KTName)
                  ->orWhere('code', $Code);
        })
        ->where('status', '1')
        ->exists();

        if ($KTExists) {
            return response()->json(['info' => 'KPI Type already exists.']);
        }
        else
        {
            $KPIType = new KPITypes();
            $KPIType->code = $Code;
            $KPIType->name = $KTName;
            $KPIType->group_id = $KTGroup;
            $KPIType->dimension_id = $KTDimension;
            $KPIType->status = $status;
            $KPIType->user_id = $sessionId;
            $KPIType->last_updated = $last_updated;
            $KPIType->timestamp = $timestamp;
            $KPIType->effective_timestamp = $KTEdt;
            $KPIType->save();

            if (empty($KPIType->id)) {
                return response()->json(['error' => 'Failed to create KPI Type.']);
            }

            $logs = Logs::create([
                'module' => 'kpi',
                'content' => "'{$KTName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $KPIType->logid = $logs->id;
            $KPIType->save();
            return response()->json(['success' => 'KPI Type created successfully']);
        }

    }

    public function GetKPITypeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->kpi_types)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPITypes = KPITypes::select('kpi_type.*', 'kpi_group.name as group_name', 'kpi_dimension.name as dimension_name')
        ->join('kpi_group', 'kpi_group.id', '=', 'kpi_type.group_id')
        ->join('kpi_dimension', 'kpi_dimension.id', '=', 'kpi_type.dimension_id')
        ->orderBy('kpi_type.id', 'desc');
        // ->get();
        // return DataTables::of($KPITypes)
        return DataTables::eloquent($KPITypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('kpi_type.code', 'like', "%{$search}%")
                            ->orWhere('kpi_type.name', 'like', "%{$search}%")
                            ->orWhere('kpi_group.name', 'like', "%{$search}%")
                            ->orWhere('kpi_dimension.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($KPIType) {
                return $KPIType->id;  // Raw ID value
            })
            ->editColumn('id', function ($KPIType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $effectiveDate = Carbon::createFromTimestamp($KPIType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($KPIType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($KPIType->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($KPIType->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $Code = strtoupper($KPIType->code);
                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($KPIType) {
                    $KPITypeId = $KPIType->id;
                    $logId = $KPIType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->kpi_types)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-kpitype" data-kpitype-id="'.$KPITypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $KPIType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($KPIType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->kpi_types)[3];
                return $updateStatus == 1 ? ($KPIType->status ? '<span class="label label-success kpitype_status cursor-pointer" data-id="'.$KPIType->id.'" data-status="'.$KPIType->status.'">Active</span>' : '<span class="label label-danger kpitype_status cursor-pointer" data-id="'.$KPIType->id.'" data-status="'.$KPIType->status.'">Inactive</span>') : ($KPIType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateKPITypeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->kpi_types)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPITypeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $KPIType = KPITypes::find($KPITypeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $KPIType->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $KPIType->status = $UpdateStatus;
        $KPIType->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPITypeLog = KPITypes::where('id', $KPITypeID)->first();
        $logIds = $KPITypeLog->logid ? explode(',', $KPITypeLog->logid) : [];
        $logIds[] = $logs->id;
        $KPITypeLog->logid = implode(',', $logIds);
        $KPITypeLog->save();

        $KPIType->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateKPITypeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPITypes = KPITypes::select('kpi_type.*', 'kpi_group.name as group_name',
         'kpi_dimension.name as dimension_name')
        ->join('kpi_group', 'kpi_group.id', '=', 'kpi_type.group_id')
        ->join('kpi_dimension', 'kpi_dimension.id', '=', 'kpi_type.dimension_id')
        ->where('kpi_type.id', $id)
        ->first();

        $KPITypeName = ucwords($KPITypes->name);
        $KPIGroup = ucwords($KPITypes->group_name);
        $KPIDimension = ucwords($KPITypes->dimension_name);
        $KPIGroupID = ucwords($KPITypes->group_id);
        $KPIDimensionID = ucwords($KPITypes->dimension_id);
        $effective_timestamp = $KPITypes->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $KPITypeName,
            'group_id' => $KPIGroupID,
            'group' => $KPIGroup,
            'dimension' => $KPIDimension,
            'dimension_id' => $KPIDimensionID,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function GetKPIGroup(Request $request)
    {
        $groupId = $request->input('groupId');
        $Groups = KPIGroups::whereNotIn('id', [$groupId])
                     ->where('status', 1)
                     ->get();

        return response()->json($Groups);
    }

    public function GetKPIdimension(Request $request)
    {
        $dimensionId = $request->input('dimensionId');
        $Dimensions = KPIDimensions::whereNotIn('id', [$dimensionId])
                     ->where('status', 1)
                     ->get();

        return response()->json($Dimensions);
    }

    public function UpdateKPIType(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPITypes = KPITypes::findOrFail($id);
        $KPITypes->name = $request->input('u_kt');
        $KPITypes->group_id = $request->input('u_kt_group');
        $KPITypes->dimension_id = $request->input('u_kt_dimension');
        $effective_date = $request->input('u_kt_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $KPITypes->effective_timestamp = $effective_date;
        $KPITypes->last_updated = $this->currentDatetime;
        $KPITypes->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $KPITypes->save();

        if (empty($KPITypes->id)) {
            return response()->json(['error' => 'Failed to update KPI Type. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPITypeLog = KPITypes::where('id', $KPITypes->id)->first();
        $logIds = $KPITypeLog->logid ? explode(',', $KPITypeLog->logid) : [];
        $logIds[] = $logs->id;
        $KPITypeLog->logid = implode(',', $logIds);
        $KPITypeLog->save();
        return response()->json(['success' => 'KPI Type updated successfully']);
    }

    public function ShowKPI()
    {
        $colName = 'kpi_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $KPITypes = KPITypes::where('status', 1)->get();
        return view('dashboard.kpi', compact('user','KPITypes'));
    }

    public function AddKPI(KPIRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->kpi_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIName = addslashes(trim($request->input('kpi_name')));
        $KPIType = $request->input('kpi_type');
        $KPIEdt = $request->input('kpi_edt');
        $KPIEdt = Carbon::createFromFormat('l d F Y - h:i A', $KPIEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($KPIEdt)->setTimezone('Asia/Karachi');
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

        $KPIExists = KPI::where('name', $KPIName)
        ->exists();
        if ($KPIExists) {
            return response()->json(['info' => 'KPI already exists.']);
        }
        else
        {
            $KPI = new KPI();
            $KPI->name = $KPIName;
            $KPI->type_id = $KPIType;
            $KPI->status = $status;
            $KPI->user_id = $sessionId;
            $KPI->last_updated = $last_updated;
            $KPI->timestamp = $timestamp;
            $KPI->effective_timestamp = $KPIEdt;
            $KPI->save();

            if (empty($KPI->id)) {
                return response()->json(['error' => 'Failed to create KPI.']);
            }

            $logs = Logs::create([
                'module' => 'kpi',
                'content' => "'{$KPIName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $KPI->logid = $logs->id;
            $KPI->save();
            return response()->json(['success' => 'KPI created successfully']);
        }

    }

    public function GetKPIData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->kpi_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIs = KPI::select('kpi.*', 'kpi_type.name as type_name',
        'kpi_group.name as group_name','kpi_dimension.name as dimension_name')
        ->join('kpi_type', 'kpi_type.id', '=', 'kpi.type_id')
        ->join('kpi_group', 'kpi_group.id', '=', 'kpi_type.group_id')
        ->join('kpi_dimension', 'kpi_dimension.id', '=', 'kpi_type.dimension_id')
        ->orderBy('kpi.id', 'desc');
        // ->get();
        // return DataTables::of($KPIs)
        return DataTables::eloquent($KPIs)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('kpi.name', 'like', "%{$search}%")
                            ->orWhere('kpi_type.name', 'like', "%{$search}%")
                            ->orWhere('kpi_group.name', 'like', "%{$search}%")
                            ->orWhere('kpi_dimension.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($KPI) {
                return $KPI->id;  // Raw ID value
            })
            ->editColumn('id', function ($KPI) {
                $session = auth()->user();
                $sessionName = $session->name;
                $KPIName = $KPI->name;
                $idStr = str_pad($KPI->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($KPI->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($KPI->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($KPI->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($KPI->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'KPI';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $KPIName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($KPI) {
                    $KPIId = $KPI->id;
                    $logId = $KPI->logid;

                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->kpi_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-kpi" data-kpi-id="'.$KPIId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $KPI->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($KPI) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->kpi_setup)[3];
                return $updateStatus == 1 ? ($KPI->status ? '<span class="label label-success kpi_status cursor-pointer" data-id="'.$KPI->id.'" data-status="'.$KPI->status.'">Active</span>' : '<span class="label label-danger kpi_status cursor-pointer" data-id="'.$KPI->id.'" data-status="'.$KPI->status.'">Inactive</span>') : ($KPI->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateKPIStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->kpi_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $KPI = KPI::find($KPIID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $KPI->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        $KPI->status = $UpdateStatus;
        $KPI->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPILog = KPI::where('id', $KPIID)->first();
        $logIds = $KPILog->logid ? explode(',', $KPILog->logid) : [];
        $logIds[] = $logs->id;
        $KPILog->logid = implode(',', $logIds);
        $KPILog->save();

        $KPI->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateKPIModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPI = KPI::select('kpi.*', 'kpi_type.name as type_name')
        ->join('kpi_type', 'kpi_type.id', '=', 'kpi.type_id')
        ->where('kpi.id', $id)
        ->first();

        $KPIName = ucwords($KPI->name);
        $KPIType = ucwords($KPI->type_name);
        $KPITypeID = ucwords($KPI->type_id);
        $effective_timestamp = $KPI->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $KPIName,
            'type_id' => $KPITypeID,
            'type_name' => $KPIType,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function GetKPITypes(Request $request)
    {
        $typeId = $request->input('typeId');
        $Types = KPITypes::whereNotIn('id', [$typeId])
                     ->where('status', 1)
                     ->get();

        return response()->json($Types);
    }

    public function UpdateKPI(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPI = KPI::findOrFail($id);
        $KPI->name = $request->input('u_kpi');
        $KPI->type_id = $request->input('u_kpi_type');
        $effective_date = $request->input('u_kpi_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $KPI->effective_timestamp = $effective_date;
        $KPI->last_updated = $this->currentDatetime;
        $KPI->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $KPI->save();

        $KPIExists = KPI::where('name', $KPI->name)
        ->exists();

        if ($KPIExists) {
            return response()->json(['info' => 'KPI already exists.']);
        }

        if (empty($KPI->id)) {
            return response()->json(['error' => 'Failed to update KPI. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $KPILog = KPI::where('id', $KPI->id)->first();
        $logIds = $KPILog->logid ? explode(',', $KPILog->logid) : [];
        $logIds[] = $logs->id;
        $KPILog->logid = implode(',', $logIds);
        $KPILog->save();
        return response()->json(['success' => 'KPI updated successfully']);
    }

    public function KPIactivation()
    {
        $colName = 'kpi_activation';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $KPIs = KPI::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        $Services = Service::where('status', 1)->get();
        $ServiceModes = ServiceMode::where('status', 1)->get();
        return view('dashboard.kpi-activation', compact('user','KPIs','Organizations','ServiceModes','Services'));
    }
    public function ActivateKPI(KPIActivationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->kpi_activation)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $kpiID = trim($request->input('act_kpi'));
        $OrgId = trim($request->input('act_kpi_org'));
        $SiteID = trim($request->input('act_kpi_site'));
        $CCId = trim($request->input('act_kpi_cc'));
        $Edt = $request->input('a_kpi_edt');
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

        $KPIActivationExists = KPIActivation::where('org_id', $OrgId)
        ->where('kpi_id', $kpiID)
        ->where('site_id', $SiteID)
        ->where('cc_id',$CCId)
        ->exists();

        if ($KPIActivationExists) {
            return response()->json(['info' => 'KPI already Activated.']);
        }
        else
        {
            $KPIActivation = new KPIActivation();
            $KPIActivation->kpi_id = $kpiID;
            $KPIActivation->org_id = $OrgId;
            $KPIActivation->site_id = $SiteID;
            $KPIActivation->cc_id = $CCId;
            $KPIActivation->status = $status;
            $KPIActivation->user_id = $sessionId;
            $KPIActivation->last_updated = $last_updated;
            $KPIActivation->timestamp = $timestamp;
            $KPIActivation->effective_timestamp = $Edt;
            $KPIActivation->save();

            if (empty($KPIActivation->id)) {
                return response()->json(['error' => 'Failed to Activate KPI.']);
            }

            $logs = Logs::create([
                'module' => 'kpi',
                'content' => "kpi activated by '{$sessionName}'",
                'event' => 'activate',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $KPIActivation->logid = $logs->id;
            $KPIActivation->save();
            return response()->json(['success' => 'KPI Activated successfully']);
        }

    }

    public function GetActivatedKPIData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->kpi_activation)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIActivations = KPIActivation::select('activated_kpi.*',
        'kpi.name as kpiName',
        'organization.organization as orgName',
        'org_site.name as siteName','costcenter.name as ccName')
        ->join('kpi', 'kpi.id', '=', 'activated_kpi.kpi_id')
        ->join('organization', 'organization.id', '=', 'activated_kpi.org_id')
        ->join('org_site', 'org_site.id', '=', 'activated_kpi.site_id')
        ->join('costcenter', 'costcenter.id', '=', 'activated_kpi.cc_id')
        ->orderBy('activated_kpi.id', 'desc');
        
        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $KPIActivations->where('activated_kpi.org_id', '=', $sessionOrg);
        }
        $KPIActivations = $KPIActivations;
        // ->get()
        // return DataTables::of($KPIActivations)
        return DataTables::eloquent($KPIActivations)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('kpi.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('costcenter.name', 'like', "%{$search}%")
                            ->orWhere('activated_kpi.id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($KPIActivation) {
                return $KPIActivation->id;  // Raw ID value
            })
            ->editColumn('id', function ($KPIActivation) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;

                $idStr = str_pad($KPIActivation->id, 6, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($KPIActivation->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($KPIActivation->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($KPIActivation->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($KPIActivation->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $kpiName = ucwords($KPIActivation->kpiName);

                $ModuleCode = 'KPA';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $KPIActivation->kpiName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgId = $KPIActivation->org_id;
                    $orgName = Organization::where('id', $orgId)->value('organization');
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>'
                    . '<hr class="mt-1 mb-2">';
            })
            ->addColumn('action', function ($KPIActivation) {
                    $KPIActivationId = $KPIActivation->id;
                    $logId = $KPIActivation->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->kpi_activation)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-activatekpi" data-activatekpi-id="'.$KPIActivationId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $KPIActivation->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
           
            })
            ->editColumn('status', function ($KPIActivation) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->kpi_activation)[3];
                return $updateStatus == 1 ? ($KPIActivation->status ? '<span class="label label-success activatekpi cursor-pointer" data-id="'.$KPIActivation->id.'" data-status="'.$KPIActivation->status.'">Active</span>' : '<span class="label label-danger activatekpi cursor-pointer" data-id="'.$KPIActivation->id.'" data-status="'.$KPIActivation->status.'">Inactive</span>') : ($KPIActivation->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateActivatedKPIStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->kpi_activation)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatekpiID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ActivateKPI = KPIActivation::find($ActivatekpiID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ActivateKPI->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        // Find the role by ID
        $ActivateKPI->status = $UpdateStatus;
        $ActivateKPI->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivatekpiLog = KPIActivation::where('id', $ActivatekpiID)->first();
        $logIds = $ActivatekpiLog->logid ? explode(',', $ActivatekpiLog->logid) : [];
        $logIds[] = $logs->id;
        $ActivatekpiLog->logid = implode(',', $logIds);
        $ActivatekpiLog->save();

        $ActivateKPI->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateActivatedKPIModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $KPIActivations = KPIActivation::select('activated_kpi.*',
        'kpi.name as kpiName',
        'organization.organization as orgName',
        'org_site.name as siteName','costcenter.name as ccName')
        ->join('kpi', 'kpi.id', '=', 'activated_kpi.kpi_id')
        ->join('organization', 'organization.id', '=', 'activated_kpi.org_id')
        ->join('org_site', 'org_site.id', '=', 'activated_kpi.site_id')
        ->join('costcenter', 'costcenter.id', '=', 'activated_kpi.cc_id')
        ->where('activated_kpi.id', $id)
        ->first();

        $kpiName = ucwords($KPIActivations->kpiName);
        $orgName = ucwords($KPIActivations->orgName);
        $siteName = ucwords($KPIActivations->siteName);
        $ccName = ucwords($KPIActivations->ccName);

        $effective_timestamp = $KPIActivations->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'kpiName' => $kpiName,
            'kpiID' => $KPIActivations->kpi_id,
            'orgName' => $orgName,
            'orgID' => $KPIActivations->org_id,
            'siteName' => $siteName,
            'siteId' => $KPIActivations->site_id,
            'ccName' => $ccName,
            'ccID' => $KPIActivations->cc_id,
            'serviceID' => $KPIActivations->service_id,
            'servicemodeIDs' => $KPIActivations->servicemode_ids,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function GetSelectedKPI(Request $request)
    {
        $kpiID = $request->input('kpiID');
        $kpis = KPI::whereNotIn('id', [$kpiID])
                     ->where('status', 1)
                     ->get();
        return response()->json($kpis);
    }

    public function UpdateActivatedKPI(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->kpi_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatedKPI = KPIActivation::findOrFail($id);
        $orgID = $request->input('u_korg');
        if (isset($orgID)) {
            $ActivatedKPI->org_id = $orgID;
        } 
        $ActivatedKPI->kpi_id = $request->input('u_kpi');
        $ActivatedKPI->site_id = $request->input('u_ksite');
        $ActivatedKPI->cc_id = $request->input('u_kcc');
        $effective_date = $request->input('uk_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ActivatedKPI->effective_timestamp = $effective_date;
        $ActivatedKPI->last_updated = $this->currentDatetime;
        $ActivatedKPI->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ActivatedKPI->save();

        if (empty($ActivatedKPI->id)) {
            return response()->json(['error' => 'KPI Activation Update Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'kpi',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivatedKPILog = KPIActivation::where('id', $ActivatedKPI->id)->first();
        $logIds = $ActivatedKPILog->logid ? explode(',', $ActivatedKPILog->logid) : [];
        $logIds[] = $logs->id;
        $ActivatedKPILog->logid = implode(',', $logIds);
        $ActivatedKPILog->save();
        return response()->json(['success' => 'KPI Activation updated successfully']);
    }
}
