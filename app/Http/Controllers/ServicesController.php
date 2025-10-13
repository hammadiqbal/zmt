<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ServiceModeRequest;
use App\Http\Requests\ServiceTypeRequest;
use App\Http\Requests\ServiceUnitRequest;
use App\Http\Requests\ServiceGroupRequest;
use App\Http\Requests\ServiceActivationRequest;
use App\Http\Requests\ServiceLocationRequest;
use App\Http\Requests\ServiceLocationSchedulingRequest;
use App\Http\Requests\ServiceRequest;
use App\Http\Requests\ServiceBookingRequest;
use App\Http\Requests\ServiceRequisitionRequest;
use App\Http\Requests\SLActivationRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Logs;
use App\Models\ServiceMode;
use App\Models\ServiceType;
use App\Models\ServiceUnit;
use App\Models\ServiceGroup;
use App\Models\Organization;
use App\Models\CostCenter;
use App\Models\Service;
use App\Models\ServiceActivation;
use App\Models\ServiceLocation;
use App\Models\ServiceBooking;
use App\Models\Employee;
use App\Models\PatientRegistration;
use App\Models\ServiceLocationScheduling;
use App\Models\ServiceRequisitionSetup;
use App\Models\RequisitionForEPI;
use App\Models\ActivatedLocations;
use App\Models\PatientArrivalDeparture;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;



class ServicesController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    private $assignedSites;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
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

    public function ServiceMode()
    {
        $colName = 'service_modes';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.servicemode', compact('user'));
    }

    public function AddServiceMode(ServiceModeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_modes)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $SMName = trim($request->input('sm_name'));
        $Code = trim(strtolower($request->input('sm_code')));
        $BillingMode = trim($request->input('billing_mode'));
        $SMEdt = $request->input('sm_edt');
        $SMEdt = Carbon::createFromFormat('l d F Y - h:i A', $SMEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($SMEdt)->setTimezone('Asia/Karachi');
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

        $SMExists = ServiceMode::where(function($query) use ($SMName, $Code) {
            $query->where('name', $SMName)
                  ->orWhere('code', $Code);
        })
        ->where('status', '1')
        ->exists();

        if ($SMExists) {
            return response()->json(['info' => 'Service mode already exists.']);
        }
        else
        {
            $ServiceMode = new ServiceMode();
            $ServiceMode->name = $SMName;
            $ServiceMode->code = $Code;
            $ServiceMode->billing_mode = $BillingMode;
            $ServiceMode->status = $status;
            $ServiceMode->user_id = $sessionId;
            $ServiceMode->last_updated = $last_updated;
            $ServiceMode->timestamp = $timestamp;
            $ServiceMode->effective_timestamp = $SMEdt;
            $ServiceMode->save();

            if (empty($ServiceMode->id)) {
                return response()->json(['error' => 'Failed to create Service Mode.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$SMName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ServiceMode->logid = $logs->id;
            $ServiceMode->save();
            return response()->json(['success' => 'Service mode created successfully']);
        }

    }

    public function GetServiceModeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_modes)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceModes = ServiceMode::select('*')->orderBy('id', 'desc');
        if ($request->has('billingmode') && $request->billingmode != '' && $request->billingmode != 'Loading...') {
            $ServiceModes->where('service_mode.billing_mode', $request->billingmode);
        }
        $ServiceModes = $ServiceModes;

        // ->get()
        // return DataTables::of($ServiceModes)
        return DataTables::eloquent($ServiceModes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('logid', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('timestamp', 'like', "%{$search}%")
                            ->orWhere('last_updated', 'like', "%{$search}%")
                            ->orWhere('user_id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ServiceMode) {
                return $ServiceMode->id;  // Raw ID value
            })
            ->editColumn('id', function ($ServiceMode) {
                $session = auth()->user();
                $sessionName = $session->name;
                $ServiceModeCode = strtoupper($ServiceMode->code);
                $effectiveDate = Carbon::createFromTimestamp($ServiceMode->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ServiceMode->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ServiceMode->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ServiceMode->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                return $ServiceModeCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($ServiceMode) {
                    $ServiceModeId = $ServiceMode->id;
                    $logId = $ServiceMode->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_modes)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-servicemode" data-servicemode-id="'.$ServiceModeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ServiceMode->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ServiceMode) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_modes)[3];
                return $updateStatus == 1 ? ($ServiceMode->status ? '<span class="label label-success servicemode_status cursor-pointer" data-id="'.$ServiceMode->id.'" data-status="'.$ServiceMode->status.'">Active</span>' : '<span class="label label-danger servicemode_status cursor-pointer" data-id="'.$ServiceMode->id.'" data-status="'.$ServiceMode->status.'">Inactive</span>') : ($ServiceMode->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateServiceModeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_modes)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceModeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ServiceModes = ServiceMode::find($ServiceModeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ServiceModes->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ServiceModes->effective_timestamp = 0;
        }
        $ServiceModes->status = $UpdateStatus;
        $ServiceModes->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceModeLog = ServiceMode::where('id', $ServiceModeID)->first();
        $logIds = $ServiceModeLog->logid ? explode(',', $ServiceModeLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceModeLog->logid = implode(',', $logIds);
        $ServiceModeLog->save();

        $ServiceModes->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateServiceModeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_modes)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceModes = ServiceMode::find($id);
        $ServiceModeName = ucwords($ServiceModes->name);
        $BillingMode = ucwords($ServiceModes->billing_mode);
        $billing_mode = ($ServiceModes->billing_mode);
        $effective_timestamp = $ServiceModes->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $ServiceModeName,
            'billingmode' => $BillingMode,
            'billing_mode' => $billing_mode,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateServiceMode(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_modes)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceModes = ServiceMode::findOrFail($id);

        $ServiceModes->name = $request->input('u_sm');
        $ServiceModes->billing_mode = $request->input('u_billing_mode');
        $effective_date = $request->input('u_sm_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ServiceModes->effective_timestamp = $effective_date;
        $ServiceModes->last_updated = $this->currentDatetime;
        $ServiceModes->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceModes->save();

        if (empty($ServiceModes->id)) {
            return response()->json(['error' => 'Failed to update Service Mode. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceModeLog = ServiceMode::where('id', $ServiceModes->id)->first();
        $logIds = $ServiceModeLog->logid ? explode(',', $ServiceModeLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceModeLog->logid = implode(',', $logIds);
        $ServiceModeLog->save();
        return response()->json(['success' => 'Service Mode updated successfully']);
    }

    public function ServiceType()
    {
        $colName = 'service_types';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.servicetype', compact('user'));
    }

    public function AddServiceType(ServiceTypeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_types)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $STName = trim($request->input('st_name'));
        $STCode = trim(strtolower($request->input('st_code')));
        $STEdt = $request->input('st_edt');
        $STEdt = Carbon::createFromFormat('l d F Y - h:i A', $STEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($STEdt)->setTimezone('Asia/Karachi');
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

        $STExists = ServiceType::where(function($query) use ($STName, $STCode) {
            $query->where('name', $STName)
                  ->orWhere('code', $STCode);
        })
        ->where('status', '1')
        ->exists();

        if ($STExists) {
            return response()->json(['info' => 'Service type already exists.']);
        }
        else
        {
            $ServiceType = new ServiceType();
            $ServiceType->name = $STName;
            $ServiceType->code = $STCode;
            $ServiceType->status = $status;
            $ServiceType->user_id = $sessionId;
            $ServiceType->last_updated = $last_updated;
            $ServiceType->timestamp = $timestamp;
            $ServiceType->effective_timestamp = $STEdt;
            $ServiceType->save();

            if (empty($ServiceType->id)) {
                return response()->json(['error' => 'Failed to create Service type.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$STName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ServiceType->logid = $logs->id;
            $ServiceType->save();
            return response()->json(['success' => 'Service type created successfully']);
        }

    }

    public function GetServiceTypeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_types)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceTypes = ServiceType::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($ServiceTypes)
        return DataTables::eloquent($ServiceTypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('timestamp', 'like', "%{$search}%")
                            ->orWhere('last_updated', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ServiceType) {
                return $ServiceType->id;  // Raw ID value
            })
            ->editColumn('id', function ($ServiceType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $effectiveDate = Carbon::createFromTimestamp($ServiceType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ServiceType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ServiceType->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ServiceType->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ServiceTypeCode = strtoupper($ServiceType->code);

                return $ServiceTypeCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($ServiceType) {
                    $ServiceTypeId = $ServiceType->id;
                    $logId = $ServiceType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_types)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-servicetype" data-servicetype-id="'.$ServiceTypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ServiceType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ServiceType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_types)[3];
                return $updateStatus == 1 ? ($ServiceType->status ? '<span class="label label-success servicetype_status cursor-pointer" data-id="'.$ServiceType->id.'" data-status="'.$ServiceType->status.'">Active</span>' : '<span class="label label-danger servicetype_status cursor-pointer" data-id="'.$ServiceType->id.'" data-status="'.$ServiceType->status.'">Inactive</span>') : ($ServiceType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateServiceTypeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_types)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceTypeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ServiceTypes = ServiceType::find($ServiceTypeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ServiceTypes->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ServiceTypes->effective_timestamp = 0;

        }
        $ServiceTypes->status = $UpdateStatus;
        $ServiceTypes->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceTypeLog = ServiceType::where('id', $ServiceTypeID)->first();
        $logIds = $ServiceTypeLog->logid ? explode(',', $ServiceTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceTypeLog->logid = implode(',', $logIds);
        $ServiceTypeLog->save();

        $ServiceTypes->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateServiceTypeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceTypes = ServiceType::find($id);
        $ServiceTypeName = ucwords($ServiceTypes->name);
        $effective_timestamp = $ServiceTypes->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $ServiceTypeName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateServiceType(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceTypes = ServiceType::findOrFail($id);

        $ServiceTypes->name = $request->input('u_st');
        $effective_date = $request->input('u_st_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ServiceTypes->effective_timestamp = $effective_date;
        $ServiceTypes->last_updated = $this->currentDatetime;
        $ServiceTypes->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceTypes->save();

        if (empty($ServiceTypes->id)) {
            return response()->json(['error' => 'Failed to update Service Type. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceTypeLog = ServiceType::where('id', $ServiceTypes->id)->first();
        $logIds = $ServiceTypeLog->logid ? explode(',', $ServiceTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceTypeLog->logid = implode(',', $logIds);
        $ServiceTypeLog->save();
        return response()->json(['success' => 'Service Type updated successfully']);
    }

    public function ServiceUnit()
    {
        $colName = 'service_units';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.serviceunit', compact('user'));
    }

    public function AddServiceUnit(ServiceUnitRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_units)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Name = trim($request->input('su_name'));
        $SUEdt = $request->input('su_edt');
        $SUEdt = Carbon::createFromFormat('l d F Y - h:i A', $SUEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($SUEdt)->setTimezone('Asia/Karachi');
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

        $SUExists = ServiceUnit::where('name', $Name)->where('status', '1')
        ->exists();
        if ($SUExists) {
            return response()->json(['info' => 'Service Unit already exists.']);
        }
        else
        {
            $ServiceUnit = new ServiceUnit();
            $ServiceUnit->name = $Name;
            $ServiceUnit->status = $status;
            $ServiceUnit->user_id = $sessionId;
            $ServiceUnit->last_updated = $last_updated;
            $ServiceUnit->timestamp = $timestamp;
            $ServiceUnit->effective_timestamp = $SUEdt;
            $ServiceUnit->save();

            if (empty($ServiceUnit->id)) {
                return response()->json(['error' => 'Failed to create Service unit.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$Name}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ServiceUnit->logid = $logs->id;
            $ServiceUnit->save();
            return response()->json(['success' => 'Service unit created successfully']);
        }

    }

    public function GetServiceUnitData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_units)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceUnits = ServiceUnit::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($ServiceUnits)
        return DataTables::eloquent($ServiceUnits)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('timestamp', 'like', "%{$search}%")
                            ->orWhere('last_updated', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ServiceUnit) {
                return $ServiceUnit->id;  // Raw ID value
            })
            ->editColumn('id', function ($ServiceUnit) {
                $session = auth()->user();
                $sessionName = $session->name;
                $effectiveDate = Carbon::createFromTimestamp($ServiceUnit->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ServiceUnit->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ServiceUnit->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ServiceUnit->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $Code = strtoupper($ServiceUnit->id);

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($ServiceUnit) {
                    $ServiceUnitId = $ServiceUnit->id;
                    $logId = $ServiceUnit->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_units)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-serviceunit" data-serviceunit-id="'.$ServiceUnitId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ServiceUnit->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ServiceUnit) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_units)[3];
                return $updateStatus == 1 ? ($ServiceUnit->status ? '<span class="label label-success serviceunit_status cursor-pointer" data-id="'.$ServiceUnit->id.'" data-status="'.$ServiceUnit->status.'">Active</span>' : '<span class="label label-danger serviceunit_status cursor-pointer" data-id="'.$ServiceUnit->id.'" data-status="'.$ServiceUnit->status.'">Inactive</span>') : ($ServiceUnit->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateServiceUnitStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_units)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceUnitID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ServiceUnits = ServiceUnit::find($ServiceUnitID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ServiceUnits->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ServiceUnits->effective_timestamp = 0;
        }
        $ServiceUnits->status = $UpdateStatus;
        $ServiceUnits->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceUnitLog = ServiceType::where('id', $ServiceUnitID)->first();
        $logIds = $ServiceUnitLog->logid ? explode(',', $ServiceUnitLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceUnitLog->logid = implode(',', $logIds);
        $ServiceUnitLog->save();

        $ServiceUnits->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateServiceUnitModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_units)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceUnits = ServiceUnit::find($id);
        $ServiceUnitName = ucwords($ServiceUnits->name);
        $effective_timestamp = $ServiceUnits->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $ServiceUnitName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateServiceUnit(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_units)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceUnits = ServiceUnit::findOrFail($id);

        $ServiceUnits->name = $request->input('u_su');
        $effective_date = $request->input('u_su_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ServiceUnits->effective_timestamp = $effective_date;
        $ServiceUnits->last_updated = $this->currentDatetime;
        $ServiceUnits->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceUnits->save();

        if (empty($ServiceUnits->id)) {
            return response()->json(['error' => 'Failed to update Service Unit. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceUnitsLog = ServiceUnit::where('id', $ServiceUnits->id)->first();
        $logIds = $ServiceUnitsLog->logid ? explode(',', $ServiceUnitsLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceUnitsLog->logid = implode(',', $logIds);
        $ServiceUnitsLog->save();
        return response()->json(['success' => 'Service Unit updated successfully']);
    }

    public function ServiceGroup()
    {
        $colName = 'service_groups';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $serviceTypes = ServiceType::where('status', 1)->get();
        return view('dashboard.servicegroup', compact('user','serviceTypes'));
    }

    public function AddServiceGroup(ServiceGroupRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_groups)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $SGName = trim($request->input('sg_name'));
        $SGCode = trim(strtolower($request->input('sg_code')));
        $SGtype = $request->input('sg_type');
        $SGEdt = $request->input('sg_edt');
        $SGEdt = Carbon::createFromFormat('l d F Y - h:i A', $SGEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($SGEdt)->setTimezone('Asia/Karachi');
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

        $SGExists = ServiceGroup::where(function($query) use ($SGName, $SGCode) {
            $query->where('name', $SGName)
                  ->orWhere('code', $SGCode);
        })
        ->where('status', '1')
        ->exists();

        if ($SGExists) {
            return response()->json(['info' => 'Service group already exists.']);
        }
        else
        {
            $ServiceGroup = new ServiceGroup();
            $ServiceGroup->name = $SGName;
            $ServiceGroup->code = $SGCode;
            $ServiceGroup->type_id = $SGtype;
            $ServiceGroup->status = $status;
            $ServiceGroup->user_id = $sessionId;
            $ServiceGroup->last_updated = $last_updated;
            $ServiceGroup->timestamp = $timestamp;
            $ServiceGroup->effective_timestamp = $SGEdt;
            $ServiceGroup->save();

            if (empty($ServiceGroup->id)) {
                return response()->json(['error' => 'Failed to create Service Group.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$SGName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ServiceGroup->logid = $logs->id;
            $ServiceGroup->save();
            return response()->json(['success' => 'Service Group created successfully']);
        }

    }

    public function GetServiceGroupData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_groups)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceGroups = ServiceGroup::select('service_group.*', 'service_type.name as type_name')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->orderBy('service_group.id', 'desc');

        if ($request->has('type') && $request->type != '' && $request->type != 'Loading...') {
            $ServiceGroups->where('service_group.type_id', $request->type);
        }
        // ->get();


        // return DataTables::of($ServiceGroups)
        return DataTables::eloquent($ServiceGroups)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('service_group.id', 'like', "%{$search}%")
                            ->orWhere('service_group.name', 'like', "%{$search}%")
                            ->orWhere('service_group.code', 'like', "%{$search}%")
                            ->orWhere('service_group.status', 'like', "%{$search}%")
                            ->orWhere('service_group.effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('service_group.timestamp', 'like', "%{$search}%")
                            ->orWhere('service_group.last_updated', 'like', "%{$search}%")
                            ->orWhere('service_type.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ServiceGroup) {
                return $ServiceGroup->id;
            })
            ->editColumn('id', function ($ServiceGroup) {
                $session = auth()->user();
                $sessionName = $session->name;
                $effectiveDate = Carbon::createFromTimestamp($ServiceGroup->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ServiceGroup->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ServiceGroup->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ServiceGroup->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ServiceGroupCode = strtoupper($ServiceGroup->code);

                return $ServiceGroupCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($ServiceGroup) {
                    $ServiceGroupId = $ServiceGroup->id;
                    $logId = $ServiceGroup->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_groups)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-servicegroup" data-servicegroup-id="'.$ServiceGroupId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ServiceGroup->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ServiceGroup) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_groups)[3];
                return $updateStatus == 1 ? ($ServiceGroup->status ? '<span class="label label-success servicegroup_status cursor-pointer" data-id="'.$ServiceGroup->id.'" data-status="'.$ServiceGroup->status.'">Active</span>' : '<span class="label label-danger servicegroup_status cursor-pointer" data-id="'.$ServiceGroup->id.'" data-status="'.$ServiceGroup->status.'">Inactive</span>') : ($ServiceGroup->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateServiceGroupStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_groups)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceGroupID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ServiceGroups = ServiceGroup::find($ServiceGroupID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ServiceGroups->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ServiceGroups->effective_timestamp = 0;
        }
        $ServiceGroups->status = $UpdateStatus;
        $ServiceGroups->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceGroupLog = ServiceGroup::where('id', $ServiceGroupID)->first();
        $logIds = $ServiceGroupLog->logid ? explode(',', $ServiceGroupLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceGroupLog->logid = implode(',', $logIds);
        $ServiceGroupLog->save();

        $ServiceGroups->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateServiceGroupModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_groups)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceGroups = ServiceGroup::select('service_group.*', 'service_type.name as type_name')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->where('service_group.id', '=', $id)
        ->orderBy('service_group.id', 'desc')
        ->first();
        $ServiceGroup = ucwords($ServiceGroups->name);
        $ServiceGroupType = ucfirst($ServiceGroups->type_name);
        $ServiceGroupId = ucfirst($ServiceGroups->type_id);
        $effective_timestamp = $ServiceGroups->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'typeid' => $ServiceGroupId,
            'name' => $ServiceGroup,
            'serviceType' => $ServiceGroupType,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function GetSelectedServiceType(Request $request)
    {
        $serviceTypeId = $request->input('serviceTypeId');
        $serviceType = ServiceType::whereNotIn('id', [$serviceTypeId])
                     ->where('status', 1)
                     ->get();

        return response()->json($serviceType);
    }

    public function UpdateServiceGroup(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_groups)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ServiceGroups = ServiceGroup::findOrFail($id);
        $ServiceGroups->name = $request->input('u_sg');
        $ServiceGroups->type_id = $request->input('u_sg_type');
        $effective_date = $request->input('u_sg_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ServiceGroups->effective_timestamp = $effective_date;
        $ServiceGroups->last_updated = $this->currentDatetime;
        $ServiceGroups->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceGroups->save();

        if (empty($ServiceGroups->id)) {
            return response()->json(['error' => 'Failed to update Service Group. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceGroupLog = ServiceGroup::where('id', $ServiceGroups->id)->first();
        $logIds = $ServiceGroupLog->logid ? explode(',', $ServiceGroupLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceGroupLog->logid = implode(',', $logIds);
        $ServiceGroupLog->save();
        return response()->json(['success' => 'Service Group updated successfully']);
    }

    public function ShowServices()
    {
        $colName = 'service_code_directory_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $serviceGroups = ServiceGroup::where('status', 1)->get();
        $serviceTypes = ServiceType::where('status', 1)->get();
        $serviceUnits = ServiceUnit::where('status', 1)->get();
        return view('dashboard.service', compact('user','serviceGroups','serviceUnits','serviceTypes'));
    }

    public function AddServices(ServiceRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_code_directory_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Service = trim($request->input('services'));
        $GroupId = trim($request->input('s_group'));
        $Charge = $request->input('s_charge');
        $Unit = $request->input('s_unit');
        $Edt = $request->input('s_edt');
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

        $ServiceExists = Service::where('name', $Service)
        ->where('group_id', $GroupId)
        ->exists();
        if ($ServiceExists) {
            return response()->json(['info' => 'Service already exists.']);
        }
        else
        {
            $Services = new Service();
            $Services->name = $Service;
            $Services->group_id = $GroupId;
            $Services->charge = $Charge;
            $Services->unit_id = $Unit;
            $Services->status = $status;
            $Services->user_id = $sessionId;
            $Services->last_updated = $last_updated;
            $Services->timestamp = $timestamp;
            $Services->effective_timestamp = $Edt;
            $Services->save();

            if (empty($Services->id)) {
                return response()->json(['error' => 'Failed to create Service.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$Service}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Services->logid = $logs->id;
            $Services->save();
            return response()->json(['success' => 'Service created successfully']);
        }

    }

    public function GetServiceData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_code_directory_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Services = Service::select('services.*', 'service_group.name as group_name',
        'service_type.name as type_name','service_unit.name as unit_name')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_unit', 'service_unit.id', '=', 'services.unit_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->orderBy('services.id', 'desc');

        if ($request->has('type') && $request->type != '' && $request->type != 'Loading...') {
            $Services->where('service_group.type_id', $request->type);
        }
        if ($request->has('group') && $request->group != '' && $request->group != 'Loading...') {
            $Services->where('services.group_id', $request->group);
        }
        if ($request->has('unit') && $request->unit != '' && $request->unit != 'Loading...') {
            $Services->where('services.unit_id', $request->unit);
        }

        // ->get();


        // return DataTables::of($Services)
        return DataTables::eloquent($Services)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('services.id', 'like', "%{$search}%")
                            ->orWhere('services.name', 'like', "%{$search}%")
                            ->orWhere('services.charge', 'like', "%{$search}%")
                            ->orWhere('services.status', 'like', "%{$search}%")
                            ->orWhere('services.effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('services.timestamp', 'like', "%{$search}%")
                            ->orWhere('services.last_updated', 'like', "%{$search}%")
                            ->orWhere('service_group.name', 'like', "%{$search}%")
                            ->orWhere('service_type.name', 'like', "%{$search}%")
                            ->orWhere('service_unit.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Service) {
                return $Service->id;  // Raw ID value
            })
            ->editColumn('id', function ($Service) {
                $session = auth()->user();
                $sessionName = $session->name;
                $ServiceName = $Service->name;

                $Charge = $Service->charge ? "<span class='badge badge-success'>Yes</span>" : "<span class='badge badge-danger'>No</span>" ;
                $idStr = str_pad($Service->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($Service->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Service->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Service->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($Service->user_id);
                $createdInfo = "
                        <b>Is Chargeable:</b> " . $Charge . "  <br>
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'SCD';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $ServiceName))));
                $ServiceGroupCode = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $ServiceGroupCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($Service) {
                    $ServiceId = $Service->id;
                    $logId = $Service->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_code_directory_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-services" data-services-id="'.$ServiceId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $Service->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($Service) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_code_directory_setup)[3];
                return $updateStatus == 1 ? ($Service->status ? '<span class="label label-success services_status cursor-pointer" data-id="'.$Service->id.'" data-status="'.$Service->status.'">Active</span>' : '<span class="label label-danger services_status cursor-pointer" data-id="'.$Service->id.'" data-status="'.$Service->status.'">Inactive</span>') : ($Service->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateServiceStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_code_directory_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Services = Service::find($ServiceID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Services->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Services->effective_timestamp = 0;
        }
        $Services->status = $UpdateStatus;
        $Services->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServicesLog = Service::where('id', $ServiceID)->first();
        $logIds = $ServicesLog->logid ? explode(',', $ServicesLog->logid) : [];
        $logIds[] = $logs->id;
        $ServicesLog->logid = implode(',', $logIds);
        $ServicesLog->save();

        $Services->save();
        return response()->json(['success' => true, 200]);
    }
    public function UpdateServiceModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_code_directory_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Services = Service::select('services.*', 'service_group.name as group_name',
        'service_unit.name as unit_name')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_unit', 'service_unit.id', '=', 'services.unit_id')
        ->where('services.id', $id)
        ->first();

        $Service = ucwords($Services->name);
        $unitID = $Services->unit_id;
        $unitName = $Services->unit_name;
        $GroupID = $Services->group_id;
        $GroupName = $Services->group_name;
        $ServiceCharge = $Services->charge;
        $effective_timestamp = $Services->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $Service,
            'unit_id' => $unitID,
            'unit_name' => $unitName,
            'group_id' => $GroupID,
            'group_name' => $GroupName,
            'charge' => $ServiceCharge,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function GetSelectedServiceGroups(Request $request)
    {
        $serviceGroupId = $request->input('serviceGroupId');
        $serviceGroups = ServiceGroup::whereNotIn('id', [$serviceGroupId])
                     ->where('status', 1)
                     ->get();

        return response()->json($serviceGroups);
    }

    public function UpdateServices(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_code_directory_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $Services = Service::findOrFail($id);
        $Services->name = $request->input('u_service');
        $Services->group_id = $request->input('u_s_group');
        $Services->charge = $request->input('u_s_charge');
        $Services->unit_id = $request->input('u_s_unit');
        $effective_date = $request->input('u_s_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $Services->effective_timestamp = $effective_date;
        $Services->last_updated = $this->currentDatetime;
        $Services->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Services->save();

        if (empty($Services->id)) {
            return response()->json(['error' => 'Failed to update Service. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServicesLog = Service::where('id', $Services->id)->first();
        $logIds = $ServicesLog->logid ? explode(',', $ServicesLog->logid) : [];
        $logIds[] = $logs->id;
        $ServicesLog->logid = implode(',', $logIds);
        $ServicesLog->save();
        return response()->json(['success' => 'Service updated successfully']);
    }

    public function ServiceActivation()
    {
        $colName = 'service_activation';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        $ServiceModes = ServiceMode::select(
            'service_mode.id',
            'service_mode.name',
        )
        ->where('billing_mode', 'direct billing')
        ->where('status', 1)
        ->get();

        $Sites = Site::where('status', 1);
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Sites->whereIn('id', $sessionSiteIds);
            }
        }
        $Sites = $Sites->get();

        $CostCenters = CostCenter::where('status', 1)->get();
        $ServiceTypes = ServiceType::where('status', 1)->get();
        $ServiceGroups = ServiceGroup::where('status', 1)->get();
        $RawServiceModes = ServiceMode::where('status', 1)->get();


        return view('dashboard.service-activation', compact('user','Organizations','ServiceModes','Sites','CostCenters','ServiceTypes','ServiceGroups','RawServiceModes'));
    }

    public function ActivateService(ServiceActivationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_activation)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }

        $OrgId = trim($request->input('act_s_org'));
        $SiteID = trim($request->input('act_s_site'));
        $ServiceIds = explode(',', $request->input('act_s_service')[0]);
        $PerformingCCArray = json_decode($request->input('act_s_performingcc')[0], true);
        $BillingCCArray = json_decode($request->input('act_s_billingcc')[0], true);
        $ServiceModeArray = json_decode($request->input('act_s_mode')[0], true);
        $Edt = $request->input('a_service_edt');
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

        foreach ($ServiceIds as $serviceId) {
            $BillingCC = implode(',', $BillingCCArray[$serviceId]);
            $PerformingCC = implode(',', $PerformingCCArray[$serviceId]);
            $ServiceModeIds = implode(',', $ServiceModeArray[$serviceId]);

            $ActivateServiceExists = ServiceActivation::where('service_id', $serviceId)
                ->where('org_id', $BillingCC)
                ->where('site_id', $PerformingCC)
                ->exists();

            if ($ActivateServiceExists) {
                return response()->json(['info' => 'Service already activated on the selected site.']);
                // dd($ActivateService);
                continue;
            }

            $ActivateService = new ServiceActivation();
            $ActivateService->org_id = $OrgId;
            $ActivateService->site_id = $SiteID;
            $ActivateService->service_id = $serviceId;
            $ActivateService->ordering_cc_ids = $BillingCC;
            $ActivateService->performing_cc_ids = $PerformingCC;
            $ActivateService->servicemode_ids = $ServiceModeIds;
            $ActivateService->status = $status;
            $ActivateService->user_id = $sessionId;
            $ActivateService->last_updated = $last_updated;
            $ActivateService->timestamp = $timestamp;
            $ActivateService->effective_timestamp = $Edt;
            $ActivateService->save();

            if (empty($ActivateService->id)) {
                return response()->json(['error' => 'Failed to Activate Service.']);
            }


            $logs = Logs::create([
                'module' => 'services',
                'content' => "Service activated by '{$sessionName}'",
                'event' => 'activate',
                'timestamp' => $timestamp,
            ]);
            $ActivateService->logid = $logs->id;


            $existsInRequisition = ServiceRequisitionSetup::where('org_id', $OrgId)
            ->where('service_id', $serviceId)
            ->exists();

            if (!$existsInRequisition) {
                $ServiceRequisition = new ServiceRequisitionSetup();
                $ServiceRequisition->org_id = $OrgId;
                $ServiceRequisition->service_id = $serviceId;
                $ServiceRequisition->mandatory = 1;
                $ServiceRequisition->status = 1;
                $ServiceRequisition->user_id = $sessionId;
                $ServiceRequisition->last_updated = $last_updated;
                $ServiceRequisition->timestamp = $timestamp;
                $ServiceRequisition->effective_timestamp = $Edt;
                $ServiceRequisition->save();
            }

            $ActivateService->save();
        }



        return response()->json(['success' => 'Services Activated successfully']);
    }

    public function GetActivatedServiceData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_activation)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceActivations = ServiceActivation::select('activated_service.*',
        'organization.organization as orgName',
        'org_site.name as siteName',
        'services.name as serviceName',
        'service_group.name as serviceGroupName',
        'service_type.name as serviceTypeName')
        ->join('organization', 'organization.id', '=', 'activated_service.org_id')
        ->join('org_site', 'org_site.id', '=', 'activated_service.site_id')
        ->join('services', 'services.id', '=', 'activated_service.service_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->orderBy('activated_service.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $ServiceActivations->whereIn('org_site.id', $sessionSiteIds);
            }
        }

        if($sessionOrg != '0')
        {
            $ServiceActivations->where('activated_service.org_id', '=', $sessionOrg);
        }

        if ($request->has('site') && $request->site != '' && $request->site != 'Loading...') {
            $ServiceActivations->where('activated_service.site_id', $request->site);
        }

        if ($request->has('costcenter') && $request->costcenter != '' && $request->costcenter != 'Loading...') {
            $ServiceActivations->where(function($query) use ($request) {
                $query->whereRaw("FIND_IN_SET(?, activated_service.ordering_cc_ids)", [$request->costcenter])
                      ->orWhereRaw("FIND_IN_SET(?, activated_service.performing_cc_ids)", [$request->costcenter]);
            });
        }

        if ($request->has('service_type') && $request->service_type != '' && $request->service_type != 'Loading...') {
            $ServiceActivations->where('service_type.id', $request->service_type);
        }

        if ($request->has('service_group') && $request->service_group != '' && $request->service_group != 'Loading...') {
            $ServiceActivations->where('service_group.id', $request->service_group);
        }

        if ($request->has('service_mode') && $request->service_mode != '' && $request->service_mode != 'Loading...') {
            $ServiceActivations->whereRaw("FIND_IN_SET(?, activated_service.servicemode_ids)", [$request->service_mode]);
        }

        $ServiceActivations = $ServiceActivations;
        // ->get();
        // return DataTables::of($ServiceActivations)
        return DataTables::eloquent($ServiceActivations)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('activated_service.id', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('org_site.name', 'like', "%{$search}%")
                        ->orWhere('services.name', 'like', "%{$search}%")
                        ->orWhere('service_group.name', 'like', "%{$search}%")
                        ->orWhere('service_type.name', 'like', "%{$search}%")
                        ->orWhere('activated_service.effective_timestamp', 'like', "%{$search}%")
                        ->orWhere('activated_service.timestamp', 'like', "%{$search}%")
                        ->orWhere('activated_service.last_updated', 'like', "%{$search}%")
                        ->orWhere('activated_service.status', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ServiceActivation) {
                return $ServiceActivation->id;  // Raw ID value
            })
            ->editColumn('id', function ($ServiceActivation) {
                $session = auth()->user();
                $effectiveDate = Carbon::createFromTimestamp($ServiceActivation->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ServiceActivation->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ServiceActivation->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ServiceActivation->user_id);
                $siteName = $ServiceActivation->siteName;
                $session = auth()->user();
                $sessionOrg = $session->org_id;
                // return $sessionOrg;
                $orgName = '';
                if($sessionOrg != '0')
                {
                    $orgName = $ServiceActivation->orgName;
                    $orgName = '<b>Organization: </b>' . ucwords($orgName) . '<br>';
                }

                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;


                $serviceName = $ServiceActivation->serviceName;
                $serviceGroupName = $ServiceActivation->serviceGroupName;
                $serviceTypeName = $ServiceActivation->serviceTypeName;

                return $serviceTypeName.'<br>'.$serviceGroupName.'<br>'.$serviceName.'<hr class="mt-1 mb-2">'
                    .$orgName
                    .' <b>Site: </b>' . ucwords($siteName) . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('billingCC', function ($ServiceActivation) {
                $BillingCCIds = $ServiceActivation->ordering_cc_ids;
                $idsArray = explode(',', $BillingCCIds);

                $costCenterNames = DB::table('costcenter')
                    ->whereIn('id', $idsArray)
                    ->pluck('name');

                $costCenterNames = $costCenterNames->implode('<hr class="mt-1 mb-1">');
                return $costCenterNames;
            })
            ->editColumn('performingCC', function ($ServiceActivation) {
                $PerformingCCIds = $ServiceActivation->performing_cc_ids;
                $idsArray = explode(',', $PerformingCCIds);

                $costCenterNames = DB::table('costcenter')
                    ->whereIn('id', $idsArray)
                    ->pluck('name');

                $costCenterNames = $costCenterNames->implode('<hr class="mt-1 mb-1">');
                return $costCenterNames;
            })
            ->editColumn('ServiceModes', function ($ServiceActivation) {
                $ServiceModesIDs = $ServiceActivation->servicemode_ids;
                $idsArray = explode(',', $ServiceModesIDs);

                // $serviceModes = DB::table('service_mode')
                //     ->leftJoin('activated_service_rate', 'service_mode.id', '=', 'activated_service_rate.service_mode_id')
                //     ->join('activated_service', 'activated_service_rate.activated_service_id', '=', 'activated_service.id')
                //     ->whereIn('service_mode.id', $idsArray)
                //     ->select(
                //         'service_mode.name as service_mode_name',
                //         'activated_service_rate.cost_price',
                //         'activated_service_rate.sell_price'
                //     )
                //     ->get();

                $serviceModes = DB::table('service_mode')
                ->leftJoin('activated_service_rate', function($join) use ($ServiceActivation) {
                    $join->on('service_mode.id', '=', 'activated_service_rate.service_mode_id')
                        ->where('activated_service_rate.activated_service_id', '=', $ServiceActivation->id);
                })
                ->whereIn('service_mode.id', explode(',', $ServiceActivation->servicemode_ids))
                ->select(
                    'service_mode.name as service_mode_name',
                    'activated_service_rate.cost_price',
                    'activated_service_rate.sell_price'
                )
                ->get();

                $serviceModeNames = $serviceModes->map(function($mode) {
                // return  $mode->cost_price.$mode->cost_price;

                // $costPrice = $mode->cost_price ? 'Unit Cost: ' . $mode->cost_price : '';
                // $sellPrice = $mode->sell_price ? 'Billed Amount: ' . $mode->sell_price : '';
                // if (!$costPrice && !$sellPrice) {
                //     $popoverContent = 'N/A';  // Show N/A if both are missing
                // } else {
                //     $popoverContent = ($costPrice ? $costPrice : 'Unit Cost: N/A') . ($sellPrice ? "<br>" . $sellPrice : '');
                // }

                $costPrice = ($mode->cost_price === null || $mode->cost_price === '') 
                    ? '' 
                    : 'Unit Cost: Rs ' . number_format($mode->cost_price,2);

                $sellPrice = ($mode->sell_price === null || $mode->sell_price === '') 
                    ? '' 
                    : 'Billed Amount: Rs ' . number_format($mode->sell_price,2);

                if ($costPrice === '' && $sellPrice === '') {
                    // If both are missing (null/empty)
                    $popoverContent = 'N/A';
                } else {
                    // Always show both with fallback N/A
                    $costDisplay = $costPrice !== '' ? $costPrice : 'Unit Cost: N/A';
                    $sellDisplay = $sellPrice !== '' ? $sellPrice : 'Billed Amount: N/A';

                    $popoverContent = $costDisplay . "<br>" . $sellDisplay;
                }


                    // $popoverContent = $costPrice . "<br>" . $sellPrice;

                    $trigger = '<span class="label label-warning popoverTrigger mt-1 mb-1" style="cursor: pointer; color:black" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $popoverContent .'">'
                             . '<i class="fa fa-money"></i> &nbsp;View Rates'
                             . '</span>';

                    return $mode->service_mode_name . '<br>' . $trigger;
                })->implode('<hr class="mt-1 mb-1">');

                return $serviceModeNames;
            })
            ->addColumn('action', function ($ServiceActivation) {
                    $ServiceActivationId = $ServiceActivation->id;
                    $logId = $ServiceActivation->logid;

                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_activation)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-activateservice" data-activateservice-id="'.$ServiceActivationId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ServiceActivation->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ServiceActivation) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_activation)[3];
                return $updateStatus == 1 ? ($ServiceActivation->status ? '<span class="label label-success activateservice cursor-pointer" data-id="'.$ServiceActivation->id.'" data-status="'.$ServiceActivation->status.'">Active</span>' : '<span class="label label-danger activateservice cursor-pointer" data-id="'.$ServiceActivation->id.'" data-status="'.$ServiceActivation->status.'">Inactive</span>') : ($ServiceActivation->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','billingCC','performingCC','ServiceModes',
            'id'])
            ->make(true);
    }

    public function UpdateActivatedServiceStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_activation)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivateServiceID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ActivateService = ServiceActivation::find($ActivateServiceID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ActivateService->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ActivateService->effective_timestamp = 0;
        }
        // Find the role by ID
        $ActivateService->status = $UpdateStatus;
        $ActivateService->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivateServiceLog = ServiceActivation::where('id', $ActivateServiceID)->first();
        $logIds = $ActivateServiceLog->logid ? explode(',', $ActivateServiceLog->logid) : [];
        $logIds[] = $logs->id;
        $ActivateServiceLog->logid = implode(',', $logIds);
        $ActivateServiceLog->save();

        $ActivateService->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateActivatedServiceModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ServiceActivations = ServiceActivation::select('activated_service.*',
            'services.name as serviceName')
            ->join('org_site', 'org_site.id', '=', 'activated_service.site_id')
            ->join('services', 'services.id', '=', 'activated_service.service_id')
            ->where('activated_service.id', $id)
            ->first();

        $serviceName = ucwords($ServiceActivations->serviceName);

        // Fetching names for ordering_cc_ids
        $orderingCCIds = explode(',', $ServiceActivations->ordering_cc_ids);
        $orderingCCNames = DB::table('costcenter')
            ->whereIn('id', $orderingCCIds)
            ->pluck('name')
            ->toArray();
        $orderingCCNames = implode(', ', $orderingCCNames);

        // Fetching names for performing_cc_ids
        $performingCCIds = explode(',', $ServiceActivations->performing_cc_ids);
        $performingCCNames = DB::table('costcenter')
            ->whereIn('id', $performingCCIds)
            ->pluck('name')
            ->toArray();
        $performingCCNames = implode(', ', $performingCCNames);

        // Format the effective timestamp
        $effective_timestamp = $ServiceActivations->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        // Fetching service mode names
        $servicemodeIds = explode(',', $ServiceActivations->servicemode_ids);
        $serviceModeNames = DB::table('service_mode')
            ->whereIn('id', $servicemodeIds)
            ->pluck('name')
            ->toArray();
        $serviceModeNames = implode(', ', $serviceModeNames);

        // Preparing data for the response
        $data = [
            'id' => $id,
            'siteId' => $ServiceActivations->site_id,
            'orderingCCNames' => $orderingCCNames,
            'orderingCCIDs' => $ServiceActivations->ordering_cc_ids,
            'performingCCNames' => $performingCCNames,
            'performingCCIDs' => $ServiceActivations->performing_cc_ids,
            'serviceName' => $serviceName,
            'serviceID' => $ServiceActivations->service_id,
            'servicemodeIDs' => $ServiceActivations->servicemode_ids,
            'serviceModeNames' => $serviceModeNames,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }


    public function GetSelectedServices(Request $request)
    {
        $serviceID = $request->input('serviceID');
        $siteID = $request->input('site_id');

        $query = Service::select('services.*',
        'service_group.name as servicegroupName',
        'service_type.name as servicetypeName')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->whereNotIn('services.id', [$serviceID])
        ->where('services.status', '1');

        if ($siteID) {
        $query->leftJoin('activated_service', function($join) use ($siteID) {
            $join->on('activated_service.service_id', '=', 'services.id')
                ->where('activated_service.site_id', '=', $siteID);
        })
        ->whereNull('activated_service.service_id');
        }
        $services = $query->get();
        return response()->json($services);
    }

    public function UpdateActivatedService(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatedService = ServiceActivation::findOrFail($id);
        $ActivatedService->service_id = $request->input('u_service');
        $OrderingCCId = $request->input('ubillingcc_id');
        $OrderingCCIds = is_array($OrderingCCId) ? implode(',', $OrderingCCId) : '';
        $ActivatedService->ordering_cc_ids = $OrderingCCIds;
        $PerformingCCId = $request->input('uperformingcc_id');
        $PerformingCCIds = is_array($PerformingCCId) ? implode(',', $PerformingCCId) : '';
        $ActivatedService->performing_cc_ids = $PerformingCCIds;
        $ServiceModeId = $request->input('u_ssm');
        $ServiceModeIds = is_array($ServiceModeId) ? implode(',', $ServiceModeId) : '';
        $ActivatedService->servicemode_ids = $ServiceModeIds;
        $effective_date = $request->input('us_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ActivatedService->effective_timestamp = $effective_date;
        $ActivatedService->last_updated = $this->currentDatetime;
        $ActivatedService->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;

        $ActivatedService->save();

        if (empty($ActivatedService->id)) {
            return response()->json(['error' => 'Service Activation Update Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivatedServiceLog = ServiceActivation::where('id', $ActivatedService->id)->first();
        $logIds = $ActivatedServiceLog->logid ? explode(',', $ActivatedServiceLog->logid) : [];
        $logIds[] = $logs->id;
        $ActivatedServiceLog->logid = implode(',', $logIds);
        $ActivatedServiceLog->save();
        return response()->json(['success' => 'Service Activation updated successfully']);
    }

    public function GetServices(Request $request)
    {
        $siteId = $request->input('siteId');

        $Services = Service::select('services.*', 'activated_service.service_id',
        'activated_service.status as activated_status', 'activated_service.site_id')
        ->join('activated_service', 'activated_service.service_id', '=', 'services.id')
        ->join('service_requisition_setup', 'service_requisition_setup.service_id', '=', 'activated_service.service_id') // Changed to INNER JOIN
        ->where('services.status', 1)
        ->where('activated_service.status', 1)
        ->where('activated_service.site_id', $siteId)
        ->where('service_requisition_setup.mandatory', 0)
        ->distinct('activated_service.service_id')
        ->get();

        return response()->json($Services);
    }

    public function GetEPIServices(Request $request)
    {
        $siteId = $request->input('siteId');
        $action = $request->input('action');

        $Services = Service::select('services.*', 'activated_service.service_id',
        'activated_service.status as activated_status', 'activated_service.site_id')
        ->join('activated_service', 'activated_service.service_id', '=', 'services.id')
        //->join('service_requisition_setup', 'service_requisition_setup.service_id', '=', 'activated_service.service_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->where('services.status', 1)
        ->where('activated_service.status', 1)
        ->where('activated_service.site_id', $siteId)
        //->where('service_requisition_setup.mandatory', 0)
        ->distinct('activated_service.service_id');

        if ($action == 'e') {
            $Services = $Services->where('service_type.code', '=', 'e');
        }
        elseif ($action == 'i') {
            //$Services = $Services->whereIn('service_type.code', ['i', 'p']);
            $Services = $Services->where('service_type.code', '=', 'i');
        }
        elseif ($action == 'p') {
            // $Services = $Services->whereIn('service_type.code', ['i', 'p']);
            $Services = $Services->where('service_type.code', '=', 'p');
        }

        $Services = $Services->get();

        // ->get();

        return response()->json($Services);
    }

    public function GetMRServiceData(Request $request)
    {
        $mr = trim($request->input('mr'));

        $Services = Service::select('services.*')
        ->distinct()
        ->join('patient_inout', 'patient_inout.service_id', '=', 'services.id')
        ->where('services.status', 1)
        ->where('patient_inout.status', 1)
        ->where('patient_inout.mr_code', $mr)
        ->get();


        return response()->json($Services);
    }
    public function GetServiceDetailsIssueDispense(Request $request)
    {
        $serviceId = trim($request->input('serviceId'));
        $mr = trim($request->input('mrId'));

        $serviceDetails = Service::query()
            ->select([
                'patient_inout.service_mode_id as ServiceModeId',
                'service_mode.name as ServiceMode',
                'patient_inout.emp_id as PhysicianId',
                'employee.name as Physician',
                'patient_inout.billing_cc as BillingCCId',
                'costcenter.name as BillingCC',
                'service_group.name as ServiceGroup',
                'service_type.name  as ServiceType',
            ])
            ->join('patient_inout', function($join) use ($mr, $serviceId) {
                $join->on('patient_inout.service_id', '=', 'services.id')
                    ->where('patient_inout.mr_code',     $mr)
                    ->where('patient_inout.status',      1)
                    ->where('patient_inout.service_id',  $serviceId);
            })
            ->join('service_mode', 'service_mode.id', '=', 'patient_inout.service_mode_id')
            ->join('costcenter', 'costcenter.id', '=', 'patient_inout.billing_cc')
            ->join('employee', 'employee.id', '=', 'patient_inout.emp_id')
            ->join('service_group', 'service_group.id', '=', 'services.group_id')
            ->join('service_type',  'service_type.id',  '=', 'service_group.type_id')
            ->where('patient_inout.status', 1)
            ->distinct()
            ->get();
;
        return response()->json($serviceDetails);
    }

    public function AllocateServiceToEmp(Request $request)
    {
        $empId = $request->input('empId');
        $siteId = $request->input('siteId');
        // $Services = Service::select(
        //     'services.id',
        //     'services.name',
        //     'activated_service.service_id',
        //     'activated_service.status as activated_status',
        //     'activated_service.site_id',
        //     'emp_cc.cc_id',
        //     DB::raw('GROUP_CONCAT(DISTINCT billing_cc.name ORDER BY billing_cc.name ASC SEPARATOR ", ") as BillingCCNames'),
        //     DB::raw('GROUP_CONCAT(DISTINCT performing_cc.name ORDER BY performing_cc.name ASC SEPARATOR ", ") as PerformingCCNames'),
        //     'service_type.name as ServiceTypeName',
        //     'service_type.code as ServiceTypeCode',
        //     'service_group.name as ServiceGroupName',
        //     DB::raw('GROUP_CONCAT(DISTINCT service_mode.name ORDER BY service_mode.name ASC) as ServiceModeNames')
        // )
        // ->join('activated_service', 'activated_service.service_id', '=', 'services.id')
        // ->join('service_group', 'service_group.id', '=', 'services.group_id')
        // ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        // ->join('emp_cc', function($join) use ($empId) {
        //     $join->on('emp_cc.emp_id', '=', DB::raw($empId));
        // })
        // ->join('costcenter as billing_cc', function($join) {
        //     $join->on(DB::raw('FIND_IN_SET(billing_cc.id, activated_service.ordering_cc_ids)'), '>', DB::raw('0'));
        // })
        // ->join('costcenter as performing_cc', function($join) {
        //     $join->on(DB::raw('FIND_IN_SET(performing_cc.id, activated_service.performing_cc_ids)'), '>', DB::raw('0'));
        // })
        // ->leftJoin('service_mode', function($join) {
        //     $join->on(DB::raw('FIND_IN_SET(service_mode.id, activated_service.servicemode_ids)'), '>', DB::raw('0'));
        // })
        // ->where('services.status', 1)
        // ->where('activated_service.status', 1)
        // ->where('activated_service.site_id', $siteId)
        // ->whereRaw("
        //         EXISTS (
        //             SELECT 1
        //             FROM emp_cc
        //             WHERE emp_cc.emp_id = $empId

        //         )
        //         AND (
        //             FIND_IN_SET(SUBSTRING_INDEX(emp_cc.cc_id, ',', 1), activated_service.ordering_cc_ids) > 0
        //             OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 2), ',', -1), activated_service.ordering_cc_ids) > 0
        //             OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 3), ',', -1), activated_service.ordering_cc_ids) > 0
        //             -- You can extend this for more comma-separated values if needed
        //         )
        //     ")
        //     ->groupBy(
        //     'services.id',
        //     'services.name',
        //     'activated_service.service_id',
        //     'activated_service.status',
        //     'activated_service.site_id',
        //     'emp_cc.cc_id',
        //     'service_type.name',
        //     'service_type.code',
        //     'service_group.name'
        // )
        // ->get();
        //   AND (
        //                 FIND_IN_SET(SUBSTRING_INDEX(emp_cc.cc_id, ',', 1), activated_service.ordering_cc_ids) > 0
        //                 OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 2), ',', -1), activated_service.ordering_cc_ids) > 0
        //                 OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 3), ',', -1), activated_service.ordering_cc_ids) > 0
        //                 -- You can extend this for more comma-separated values if needed
        //             )

        $Services = Service::select(
                'services.id',
                'services.name',
                'activated_service.service_id',
                'activated_service.status as activated_status',
                'activated_service.site_id',
                'emp_cc.cc_id',
                DB::raw('GROUP_CONCAT(DISTINCT billing_cc.name ORDER BY billing_cc.name ASC SEPARATOR ", ") as BillingCCNames'),
                DB::raw('GROUP_CONCAT(DISTINCT performing_cc.name ORDER BY performing_cc.name ASC SEPARATOR ", ") as PerformingCCNames'),
                'service_type.name as ServiceTypeName',
                'service_type.code as ServiceTypeCode',
                'service_group.name as ServiceGroupName',
                DB::raw('GROUP_CONCAT(DISTINCT service_mode.name ORDER BY service_mode.name ASC) as ServiceModeNames')
                )
                ->join('activated_service', 'activated_service.service_id', '=', 'services.id')
                ->join('service_group', 'service_group.id', '=', 'services.group_id')
                ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
                ->join('emp_cc', function ($join) use ($empId) {
                $join->on('emp_cc.emp_id', '=', DB::raw($empId));
                })
                ->join('costcenter as billing_cc', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(billing_cc.id, activated_service.ordering_cc_ids)'), '>', DB::raw('0'));
                })
                ->join('costcenter as performing_cc', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(performing_cc.id, activated_service.performing_cc_ids)'), '>', DB::raw('0'));
                })
                ->leftJoin('service_mode', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(service_mode.id, activated_service.servicemode_ids)'), '>', DB::raw('0'));
                })
                ->where('services.status', 1)
                ->where('activated_service.status', 1)
                ->where('activated_service.site_id', $siteId)
                ->where(function ($query) use ($empId) {
                $query->whereRaw("EXISTS (SELECT 1 FROM emp_cc WHERE emp_cc.emp_id = $empId)")
                ->where(function ($subQuery) {
                    $subQuery->where(function ($q) {
                        $q->whereIn('service_type.code', ['e', 'p'])
                            ->whereRaw("(
                                FIND_IN_SET(SUBSTRING_INDEX(emp_cc.cc_id, ',', 1), activated_service.performing_cc_ids) > 0
                                OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 2), ',', -1), activated_service.performing_cc_ids) > 0
                                OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 3), ',', -1), activated_service.performing_cc_ids) > 0
                            )");
                    })->orWhere(function ($q) {
                        $q->where('service_type.code', 'i')
                            ->whereRaw("(
                                FIND_IN_SET(SUBSTRING_INDEX(emp_cc.cc_id, ',', 1), activated_service.ordering_cc_ids) > 0
                                OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 2), ',', -1), activated_service.ordering_cc_ids) > 0
                                OR FIND_IN_SET(SUBSTRING_INDEX(SUBSTRING_INDEX(emp_cc.cc_id, ',', 3), ',', -1), activated_service.ordering_cc_ids) > 0
                            )");
                    })->orWhere(function ($q) {
                        $q->whereNotIn('service_type.code', ['e', 'p', 'i']);
                    });
                });
                })
                ->groupBy(
                'services.id',
                'services.name',
                'activated_service.service_id',
                'activated_service.status',
                'activated_service.site_id',
                'emp_cc.cc_id',
                'service_type.name',
                'service_type.code',
                'service_group.name'
                )
                ->get();
        return response()->json($Services);
    }

    // public function AllocateServiceToEmp(Request $request)
    // {
    //     $empId = $request->input('empId');
    //     $siteId = $request->input('siteId');

    //     $Services = Service::select(
    //         'services.id',
    //         'services.name',
    //         'activated_service.service_id',
    //         'activated_service.status as activated_status',
    //         'activated_service.site_id',
    //         // 'emp_cc.cc_id',
    //         DB::raw('GROUP_CONCAT(DISTINCT billing_cc.name ORDER BY billing_cc.name ASC SEPARATOR ", ") as BillingCCNames'),
    //         DB::raw('GROUP_CONCAT(DISTINCT performing_cc.name ORDER BY performing_cc.name ASC SEPARATOR ", ") as PerformingCCNames'),
    //         'service_type.name as ServiceTypeName',
    //         'service_type.code as ServiceTypeCode',
    //         'service_group.name as ServiceGroupName',
    //         DB::raw('GROUP_CONCAT(DISTINCT service_mode.name ORDER BY service_mode.name ASC) as ServiceModeNames')
    //     )
    //     ->join('activated_service', 'activated_service.service_id', '=', 'services.id')
    //     ->join('service_group', 'service_group.id', '=', 'services.group_id')
    //     ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
    //     // ->join('emp_cc', function($join) use ($empId) {
    //     //     $join->on('emp_cc.emp_id', '=', DB::raw($empId));
    //     // })
    //     ->join('costcenter as billing_cc', function($join) {
    //         $join->on(DB::raw('FIND_IN_SET(billing_cc.id, activated_service.ordering_cc_ids)'), '>', DB::raw('0'));
    //     })
    //     ->join('costcenter as performing_cc', function($join) {
    //         $join->on(DB::raw('FIND_IN_SET(performing_cc.id, activated_service.performing_cc_ids)'), '>', DB::raw('0'));
    //     })
    //     ->whereRaw('EXISTS (SELECT 1 FROM emp_cc WHERE emp_cc.emp_id = ? AND FIND_IN_SET(emp_cc.cc_id, activated_service.ordering_cc_ids))', [$empId])
    //     ->leftJoin('service_mode', function($join) {
    //         $join->on(DB::raw('FIND_IN_SET(service_mode.id, activated_service.servicemode_ids)'), '>', DB::raw('0'));
    //     })
    //     ->where('services.status', 1)
    //     ->where('activated_service.status', 1)
    //     ->where('activated_service.site_id', $siteId)
    //     ->groupBy(
    //         'services.id',
    //         'services.name',
    //         'activated_service.service_id',
    //         'activated_service.status',
    //         'activated_service.site_id',
    //         // 'emp_cc.cc_id',
    //         'service_type.name',
    //         'service_type.code',
    //         'service_group.name'
    //     )
    //     ->get();

    //     return response()->json($Services);
    // }

    public function GetServiceModes(Request $request)
    {
        $siteId = $request->input('siteId');
        $serviceId = $request->input('serviceId');
        // dd($siteId, $serviceId);

        $ServiceModes = ServiceMode::select('service_mode.*','activated_service_rate.sell_price')
        ->join('activated_service', function ($join) use ($siteId, $serviceId) {
            $join->whereRaw('FIND_IN_SET(service_mode.id, activated_service.servicemode_ids)')
                ->where('activated_service.site_id', $siteId)
                ->where('activated_service.service_id', $serviceId)
                ->where('activated_service.status', 1);
        })
        ->leftJoin('activated_service_rate', 'activated_service_rate.activated_service_id', '=', 'activated_service.id')
        ->distinct()
        ->get();

        return response()->json($ServiceModes);
    }

    public function GetServiceCostCenters(Request $request)
    {
        $siteId = $request->input('siteId');
        $serviceId = $request->input('serviceId');

        $BillingCC = CostCenter::select('costcenter.*', 'activated_service.service_id',
        'activated_service.ordering_cc_ids',
        'activated_service.status as activated_status', 'activated_service.site_id')
        ->join('activated_service', function($join) {
            $join->on(DB::raw('FIND_IN_SET(costcenter.id, activated_service.ordering_cc_ids)'), '>', DB::raw('0'));
        })
        ->where('costcenter.status', 1)
        ->where('activated_service.status', 1)
        ->where('activated_service.site_id', $siteId)
        ->where('activated_service.service_id', $serviceId)
        ->get();


        return response()->json($BillingCC);
    }

    public function ShowServiceLocation()
    {
        $colName = 'service_location_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.service-location', compact('user','Organizations'));
    }

    public function AddServiceLocation(ServiceLocationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_location_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceLocation = trim($request->input('service_location'));
        $Org = $request->input('sl_org');
        // $Site = $request->input('sl_site');
        $InvStatus = $request->input('inv_status');
        $Edt = $request->input('sl_edt');
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

        $ServiceLocationExists = ServiceLocation::where('name', $ServiceLocation)
        ->where('org_id', $Org)
        // ->where('site_id', $Site)
        ->exists();

        if ($ServiceLocationExists) {
            return response()->json(['info' => 'Service location already exists.']);
        }
        else
        {
            $ServiceLocations = new ServiceLocation();
            $ServiceLocations->name = $ServiceLocation;
            $ServiceLocations->org_id = $Org;
            // $ServiceLocations->site_id = $Site;
            $ServiceLocations->inventory_status = $InvStatus;
            $ServiceLocations->status = $status;
            $ServiceLocations->user_id = $sessionId;
            $ServiceLocations->last_updated = $last_updated;
            $ServiceLocations->timestamp = $timestamp;
            $ServiceLocations->effective_timestamp = $Edt;
            $ServiceLocations->save();

            if (empty($ServiceLocations->id)) {
                return response()->json(['error' => 'Failed to create Service location.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$ServiceLocation}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ServiceLocations->logid = $logs->id;
            $ServiceLocations->save();
            return response()->json(['success' => 'Service location created successfully']);
        }
    }

    public function ViewServiceLocation(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_location_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceLocations = ServiceLocation::select('service_location.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'service_location.org_id')
        ->orderBy('service_location.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ServiceLocations->where('service_location.org_id', '=', $sessionOrg);
        }
        $ServiceLocations = $ServiceLocations;
        // ->get()
        // return DataTables::of($ServiceLocations)
        return DataTables::eloquent($ServiceLocations)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->Where('service_location.name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('service_location.effective_timestamp', 'like', "%{$search}%")
                        ->orWhere('service_location.timestamp', 'like', "%{$search}%")
                        ->orWhere('service_location.last_updated', 'like', "%{$search}%")
                        ->orWhere('service_location.user_id', 'like', "%{$search}%")
                        ->orWhere('service_location.status', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ServiceLocation) {
                return $ServiceLocation->id;  // Raw ID value
            })
            ->editColumn('id', function ($ServiceLocation) {
                $session = auth()->user();
                $sessionName = $session->name;
                $ServiceLocationName = $ServiceLocation->name;
                $idStr = str_pad($ServiceLocation->id, 5, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($ServiceLocation->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ServiceLocation->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ServiceLocation->last_updated)->format('l d F Y - h:i A');
                $inventoryStatus = $ServiceLocation->inventory_status == 0 ? "<span class='label label-danger'>No</span>" : "<span class='label label-success'>Yes</span>";
                $createdByName = getUserNameById($ServiceLocation->user_id);

                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'FDS';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $ServiceLocationName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($ServiceLocation->orgName);
                }

                return $Code.$orgName.'<hr class="mt-1 mb-2"><b>Inventory Location</b>: '.$inventoryStatus
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($ServiceLocation) {
                    $ServiceLocationId = $ServiceLocation->id;
                    $logId = $ServiceLocation->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_location_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-servicelocation" data-servicelocation-id="'.$ServiceLocationId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ServiceLocation->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($ServiceLocation) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_location_setup)[3];
                return $updateStatus == 1 ? ($ServiceLocation->status ? '<span class="label label-success servicelocation cursor-pointer" data-id="'.$ServiceLocation->id.'" data-status="'.$ServiceLocation->status.'">Active</span>' : '<span class="label label-danger servicelocation cursor-pointer" data-id="'.$ServiceLocation->id.'" data-status="'.$ServiceLocation->status.'">Inactive</span>') : ($ServiceLocation->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateServiceLocationStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_location_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $LocationID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ServiceLocation = ServiceLocation::find($LocationID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ServiceLocation->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ServiceLocation->effective_timestamp = 0;
        }
        // Find the role by ID
        $ServiceLocation->status = $UpdateStatus;
        $ServiceLocation->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceLocationLog = ServiceLocation::where('id', $LocationID)->first();
        $logIds = $ServiceLocationLog->logid ? explode(',', $ServiceLocationLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceLocationLog->logid = implode(',', $logIds);
        $ServiceLocationLog->save();

        $ServiceLocation->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateServiceLocationModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_location_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceLocations = ServiceLocation::select('service_location.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'service_location.org_id')
        ->where('service_location.id', $id)
        ->first();

        $Location = ucwords($ServiceLocations->name);
        $orgName = ucwords($ServiceLocations->orgName);
        $inventoryStatus = $ServiceLocations->inventory_status == 0 ? "No" : "Yes";
        $inventoryStatusId = $ServiceLocations->inventory_status;

        $effective_timestamp = $ServiceLocations->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'location' => $Location,
            'inventoryStatus' => $inventoryStatus,
            'inventoryStatusId' => $inventoryStatusId,
            'orgName' => $orgName,
            'orgID' => $ServiceLocations->org_id,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateServiceLocation(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_location_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ServiceLocations = ServiceLocation::findOrFail($id);
        $ServiceLocations->name = $request->input('u_sl');
        $orgID = $request->input('u_slorg');
        if (isset($orgID)) {
            $ServiceLocations->org_id = $orgID;
        }
        // $ServiceLocations->site_id = $request->input('u_slsite');
        $ServiceLocations->inventory_status = $request->input('u_invstatus');
        $effective_date = $request->input('usl_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ServiceLocations->effective_timestamp = $effective_date;
        $ServiceLocations->last_updated = $this->currentDatetime;
        $ServiceLocations->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceLocations->save();

        if (empty($ServiceLocations->id)) {
            return response()->json(['error' => 'Service Location Update Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceLocationLog = ServiceLocation::where('id', $ServiceLocations->id)->first();
        $logIds = $ServiceLocationLog->logid ? explode(',', $ServiceLocationLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceLocationLog->logid = implode(',', $logIds);
        $ServiceLocationLog->save();
        return response()->json(['success' => 'Service Location updated successfully']);
    }

    public function ServiceLocationActivation()
    {
        $colName = 'service_location_activation';
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

        return view('dashboard.service-location-activation', compact('user','Organizations','Sites'));
    }

    public function GetNotActivatedServiceLocation(Request $request)
    {
        $colName = 'service_location_activation';
        if (PermissionDenied($colName)) {
            abort(403);
        }

        $siteId = $request->input('siteId');
        $locationID = $request->input('locationID');

        $serviceLocations = ServiceLocation::where('service_location.status', 1)
            ->leftJoin('activated_location', function ($join) use ($siteId) {
                $join->on('service_location.id', '=', 'activated_location.location_id')
                    ->where('activated_location.site_id', '=', $siteId);
            })
            ->where(function($query) use ($locationID) {
                if ($locationID) {
                    $query->where('service_location.id', '!=', $locationID);
                }
            })
            ->whereNull('activated_location.location_id')
            ->select('service_location.*',
                DB::raw("CASE
                            WHEN service_location.inventory_status = 1 THEN 'Inventory Locations'
                            ELSE 'Non-Inventory Locations'
                        END as category"))
            ->orderBy('category', 'asc')
            ->get();

        return response()->json($serviceLocations);
    }

    public function GetAllServiceLocations(Request $request)
    {
        $serviceLocations = ServiceLocation::where('status', 1)
        ->select('service_location.*',
            DB::raw("CASE
                        WHEN service_location.inventory_status = 1 THEN 'Inventory Locations'
                        ELSE 'Non-Inventory Locations'
                    END as category"))
        ->orderBy('category', 'asc')
        ->get();


        return response()->json($serviceLocations);
    }

    // public function GetActivatedServiceLocation(Request $request)
    // {
    //     // $colName = 'service_location_activation';
    //     // if (PermissionDenied($colName)) {
    //     //     abort(403);
    //     // }
    //     $user       = auth()->user();
    //     $roleId     = $user->role_id;
    //     $isEmployee = $user->is_employee;
    //     $empId      = $user->emp_id;

    //     $siteId = $request->input('siteId');
    //     $inventoryStatus = $request->input('inventoryStatus');
    //     $empCheck = $request->input('empCheck', true);

    //     $query = ServiceLocation::where('service_location.status', 1)
    //     ->join('activated_location', function ($join) use ($siteId) {
    //         $join->on('service_location.id', '=', 'activated_location.location_id')
    //             ->where('activated_location.site_id', '=', $siteId)
    //             ->where('activated_location.status', '=', 1);
    //     });

    //     if ($inventoryStatus === 'true' || $inventoryStatus === true) {
    //         $query->where('service_location.inventory_status', 1);
    //     }

    //     if ($empCheck === 'true' || $empCheck === true) {
    //         // dd($empCheck);

    //         if ($roleId != 1 && $isEmployee == 1) {

    //             $empInv = DB::table('emp_inventory_location')
    //                 ->where('emp_id', $empId)
    //                 ->where('status', 1)
    //                 ->whereRaw('FIND_IN_SET(?, REPLACE(location_site, " ", ""))', [$siteId])
    //                 ->orderByDesc('id')
    //                 ->first();
    //                 // dd($empInv);

    //             $empServiceLocationIDs = [];

    //             if ($empInv && !empty($empInv->service_location_id)) {
    //                 // location_site is CSV aligned with service_location_id outer array
    //                 $sites = array_map('trim', explode(',', (string)$empInv->location_site));
    //                 $groups = json_decode($empInv->service_location_id, true);

    //                 if (is_array($groups)) {
    //                     // find the index of the current siteId within location_site
    //                     $idx = array_search((string)$siteId, array_map('strval', $sites), true);

    //                     if ($idx !== false && isset($groups[$idx]) && is_array($groups[$idx])) {
    //                         // use only the group for this site
    //                         $empServiceLocationIDs = array_map('strval', $groups[$idx]);
    //                     } else {
    //                         // fallback: no match -> empty set (or choose to flatten all if that's desired)
    //                         $empServiceLocationIDs = [];
    //                     }
    //                 }
    //             }

    //             if (!empty($empServiceLocationIDs)) {
    //                 $query->whereIn('service_location.id', $empServiceLocationIDs);
    //             } else {
    //                 // prevent returning everything when no IDs match
    //                 $query->whereRaw('1=0');
    //             }
    //         }
    //     }

    //     $query = $query->get();
    //     return response()->json($query);
    // }

    public function GetActivatedServiceLocation(Request $request)
    {
        // $colName = 'service_location_activation';
        // if (PermissionDenied($colName)) {
        //     abort(403);
        // }
        $user       = auth()->user();
        $roleId     = $user->role_id;
        $isEmployee = $user->is_employee;
        $empId      = $user->emp_id;

        $siteId = $request->input('siteId');
        $inventoryStatus = $request->input('inventoryStatus');
        $empCheck = $request->input('empCheck', true);

        $query = ServiceLocation::where('service_location.status', 1)
        ->join('activated_location', function ($join) use ($siteId) {
            $join->on('service_location.id', '=', 'activated_location.location_id')
                ->where('activated_location.site_id', '=', $siteId)
                ->where('activated_location.status', '=', 1);
        });

        if ($inventoryStatus === 'true' || $inventoryStatus === true) {
            $query->where('service_location.inventory_status', 1);
        }

        // if ($empCheck === 'true' || $empCheck === true) {
        //     if ($roleId != 1 && $isEmployee == 1) {
        //         // $empInv = DB::table('emp_inventory_location')
        //         //     ->where('location_site', $siteId)
        //         //     ->where('emp_id', $empId)
        //         //     ->where('status', 1)
        //         //     ->first();
        //         $empInv = DB::table('emp_inventory_location')
        //         ->where('emp_id', $empId)
        //         ->where('status', 1)
        //         ->whereRaw('FIND_IN_SET(?, REPLACE(location_site, " ", ""))', [$siteId])
        //         ->orderByDesc('id')   // optional, if you want the latest
        //         ->first();
        //             dd($siteId,$empInv);

        //         $empServiceLocationIDs = [];
        //         if ($empInv && !empty($empInv->service_location_id)) {
        //             $decoded = json_decode($empInv->service_location_id, true);
        //             if (is_array($decoded)) {
        //                 $flattened = [];
        //                 array_walk_recursive($decoded, function($val) use (&$flattened) {
        //                     $flattened[] = (string) $val;
        //                 });
        //                 $empServiceLocationIDs = $flattened;
        //             }
        //         }
        //         $query->whereIn('service_location.id', $empServiceLocationIDs);
        //     }
        // }
        if ($empCheck === 'true' || $empCheck === true) {
            // dd($empCheck);

            if ($roleId != 1 && $isEmployee == 1) {

                $empInv = DB::table('emp_inventory_location')
                    ->where('emp_id', $empId)
                    ->where('status', 1)
                    ->whereRaw('FIND_IN_SET(?, REPLACE(location_site, " ", ""))', [$siteId])
                    ->orderByDesc('id')
                    ->first();
                    // dd($empInv);

                $empServiceLocationIDs = [];

                if ($empInv && !empty($empInv->service_location_id)) {
                    // location_site is CSV aligned with service_location_id outer array
                    $sites = array_map('trim', explode(',', (string)$empInv->location_site));
                    $groups = json_decode($empInv->service_location_id, true);

                    if (is_array($groups)) {
                        // find the index of the current siteId within location_site
                        $idx = array_search((string)$siteId, array_map('strval', $sites), true);

                        if ($idx !== false && isset($groups[$idx]) && is_array($groups[$idx])) {
                            // use only the group for this site
                            $empServiceLocationIDs = array_map('strval', $groups[$idx]);
                        } else {
                            // fallback: no match -> empty set (or choose to flatten all if that's desired)
                            $empServiceLocationIDs = [];
                        }
                    }
                }

                if (!empty($empServiceLocationIDs)) {
                    $query->whereIn('service_location.id', $empServiceLocationIDs);
                } else {
                    // prevent returning everything when no IDs match
                    $query->whereRaw('1=0');
                }
            }
        }

        $query = $query->get();
        return response()->json($query);
    }

    public function GetInventoryReportLocations(Request $request)
    {
        $siteIds = $request->input('siteIds', []);
        
        if (is_string($siteIds)) {
            $siteIds = explode(',', $siteIds);
        }

        if (empty($siteIds) || in_array('0101', $siteIds)) {
            $query = ServiceLocation::where('service_location.status', 1)
                ->join('activated_location', function ($join) {
                    $join->on('service_location.id', '=', 'activated_location.location_id')
                        ->where('activated_location.status', '=', 1);
                })
                ->select('service_location.id', 'service_location.name')
                ->distinct();
        } else {
            // Handle multiple site IDs
            $siteIdArray = [];
            foreach ($siteIds as $siteId) {
                if (strpos($siteId, ',') !== false) {
                    $siteIdArray = array_merge($siteIdArray, array_map('intval', explode(',', $siteId)));
                } else {
                    $siteIdArray[] = intval($siteId);
                }
            }
            
            $query = ServiceLocation::where('service_location.status', 1)
                ->join('activated_location', function ($join) use ($siteIdArray) {
                    $join->on('service_location.id', '=', 'activated_location.location_id')
                        ->whereIn('activated_location.site_id', $siteIdArray)
                        ->where('activated_location.status', '=', 1);
                })
                ->select('service_location.id', 'service_location.name')
                ->distinct();
        }

        $query->where('service_location.inventory_status', 1);

        $locations = $query->orderBy('service_location.name')->get();
        return response()->json($locations);
    }

    public function ActivateServiceLocation(SLActivationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_location_activation)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Org = trim($request->input('sl_org'));
        $Site = trim($request->input('sl_site'));
        $Locations = $request->input('sl_name')[0];
        $LocationsArray = explode(',', $Locations);

        $Edt = $request->input('a_sl_edt');
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

        foreach ($LocationsArray as $LocationName) {
            // $totalCount++;
            $ActivatedLocationExists = ActivatedLocations::where('org_id', $Org)
            ->where('site_id', $Site)
            ->where('location_id',$LocationName)
            ->exists();

            if ($ActivatedLocationExists) {
                // $alreadyActivatedCount++;
                return response()->json(['info' => 'Service Location already Activated.']);
            }
            else
            {
                $ActivatedLocation = new ActivatedLocations();
                $ActivatedLocation->org_id = $Org;
                $ActivatedLocation->site_id = $Site;
                $ActivatedLocation->location_id = $LocationName;
                $ActivatedLocation->status = $status;
                $ActivatedLocation->user_id = $sessionId;
                $ActivatedLocation->last_updated = $last_updated;
                $ActivatedLocation->timestamp = $timestamp;
                $ActivatedLocation->effective_timestamp = $Edt;
                $ActivatedLocation->save();

                if (empty($ActivatedLocation->id)) {
                    return response()->json(['error' => 'Failed to Activate Service Location.']);
                }

                $logs = Logs::create([
                    'module' => 'service',
                    'content' => "Service Location activated by '{$sessionName}'",
                    'event' => 'activate',
                    'timestamp' => $timestamp,
                ]);
                $logId = $logs->id;
                $ActivatedLocation->logid = $logs->id;
                $ActivatedLocation->save();
                // $activatedCount++;
            }
        }
        return response()->json(['success' => 'Service Location Activated successfully']);
    }

    public function GetActivatedSLData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_location_activation)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatedLocations = ActivatedLocations::select('activated_location.*', 'organization.organization as orgName',
         'org_site.name as siteName','service_location.name as locationName','service_location.inventory_status as inventoryStatus')
        ->leftJoin('organization', 'organization.id', '=', 'activated_location.org_id')
        ->join('org_site', 'org_site.id', '=', 'activated_location.site_id')
        ->join('service_location', 'service_location.id', '=', 'activated_location.location_id')
        ->orderBy('activated_location.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $ActivatedLocations->whereIn('org_site.id', $sessionSiteIds);
            }
        }
        if($sessionOrg != '0')
        {
            $ActivatedLocations->where('activated_location.org_id', '=', $sessionOrg);
        }
        if ($request->has('site') && $request->site != '' && $request->site != 'Loading...') {
            $ActivatedLocations->where('activated_location.site_id', $request->site);
        }
        $ActivatedLocations = $ActivatedLocations;
        // ->get()

        // return DataTables::of($ActivatedCC)
        return DataTables::eloquent($ActivatedLocations)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('activated_location.id', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('service_location.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ActivatedLocation) {
                return $ActivatedLocation->id;  // Raw ID value
            })
            ->editColumn('id', function ($ActivatedLocation) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($ActivatedLocation->orgName);
                }

                $effectiveDate = Carbon::createFromTimestamp($ActivatedLocation->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ActivatedLocation->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ActivatedLocation->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ActivatedLocation->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                if ($ActivatedLocation->inventoryStatus == 1) {
                    $inventoryStatus =  '<span class="badge badge-success p-1">Enabled</span>';
                } else {
                    $inventoryStatus =  '<span class="p-1 badge badge-danger">Disabled</span>';
                }

                return ucwords($ActivatedLocation->locationName).$orgName
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Inventory Status:</b>  '. $inventoryStatus
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($ActivatedLocation) {
                    $ActivatedLocationId = $ActivatedLocation->id;
                    $logId = $ActivatedLocation->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_location_activation)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-activatesl" data-activatesl-id="'.$ActivatedLocationId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ActivatedLocation->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($ActivatedLocation) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_location_activation)[3];
                return $updateStatus == 1 ? ($ActivatedLocation->status ? '<span class="label label-success activatesl cursor-pointer" data-id="'.$ActivatedLocation->id.'" data-status="'.$ActivatedLocation->status.'">Active</span>' : '<span class="label label-danger activatesl cursor-pointer" data-id="'.$ActivatedLocation->id.'" data-status="'.$ActivatedLocation->status.'">Inactive</span>') : ($ActivatedLocation->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateActivatedServiceLocationStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_location_activation)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivateSLID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ActivatedLocation = ActivatedLocations::find($ActivateSLID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ActivatedLocation->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ActivatedLocation->effective_timestamp = 0;
        }
        // Find the role by ID
        $ActivatedLocation->status = $UpdateStatus;
        $ActivatedLocation->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivatedLocationLog = ActivatedLocations::where('id', $ActivateSLID)->first();
        $logIds = $ActivatedLocationLog->logid ? explode(',', $ActivatedLocationLog->logid) : [];
        $logIds[] = $logs->id;
        $ActivatedLocationLog->logid = implode(',', $logIds);
        $ActivatedLocationLog->save();

        $ActivatedLocation->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateActivatedServiceLocationModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_location_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ActivatedLocations = ActivatedLocations::select('activated_location.*', 'organization.organization as orgName',
        'org_site.name as siteName','service_location.name as locationName','service_location.inventory_status as inventoryStatus')
       ->leftJoin('organization', 'organization.id', '=', 'activated_location.org_id')
       ->join('org_site', 'org_site.id', '=', 'activated_location.site_id')
       ->join('service_location', 'service_location.id', '=', 'activated_location.location_id')
       ->where('activated_location.id', $id)
       ->first();


        $orgName = ucwords($ActivatedLocations->orgName);
        $siteName = ucwords($ActivatedLocations->siteName);
        $locationName = ucwords($ActivatedLocations->locationName);

        $effective_timestamp = $ActivatedLocations->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'orgName' => $orgName,
            'orgID' => $ActivatedLocations->org_id,
            'siteName' => $siteName,
            'siteId' => $ActivatedLocations->site_id,
            'locationName' => $locationName,
            'locationID' => $ActivatedLocations->location_id,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateActivatedServiceLocation(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_location_activation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ActivatedLocation = ActivatedLocations::findOrFail($id);

        $orgID = $request->input('u_slorg');

        if (isset($orgID)) {
            $ActivatedLocation->org_id = $orgID;
        }
        $ActivatedLocation->site_id = $request->input('u_slsite');
        $ActivatedLocation->location_id = $request->input('ua_servicelocation');
        $effective_date = $request->input('usl_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ActivatedLocation->effective_timestamp = $effective_date;
        $ActivatedLocation->last_updated = $this->currentDatetime;
        $ActivatedLocation->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ActivatedLocation->save();

        if (empty($ActivatedLocation->id)) {
            return response()->json(['error' => 'Service Location Activation Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ActivatedLocationLog = ActivatedLocations::where('id', $ActivatedLocation->id)->first();
        $logIds = $ActivatedLocationLog->logid ? explode(',', $ActivatedLocationLog->logid) : [];
        $logIds[] = $logs->id;
        $ActivatedLocationLog->logid = implode(',', $logIds);
        $ActivatedLocationLog->save();
        return response()->json(['success' => 'Service Location Activation updated successfully']);
    }

    public function ShowServiceLocationScheduling()
    {
        $colName = 'service_location_scheduling';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        // $Employees = Employee::where('status', 1)->get();
        $Employees = Employee::join('prefix', 'prefix.id', '=', 'employee.prefix_id')
        ->where('employee.status', 1)
        ->whereRaw('LOWER(prefix.name) LIKE ?', ['%dr.%']);
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Employees->whereIn('employee.site_id', $sessionSiteIds);
            }
        }
        $Employees = $Employees->get([
            'employee.id',
            'employee.name',
            'prefix.name as prefix'
        ]);
        
        return view('dashboard.service-location-scheduling', compact('Employees','user','Organizations'));
    }

    public function GetServiceLocation(Request $request)
    {
        $orgId = $request->input('orgId');
        $ServiceLocation = ServiceLocation::where('status', 1);
        if ($orgId !== 'null') {
            $ServiceLocation->where('org_id', $orgId);
        }
        $ServiceLocation = $ServiceLocation->get();
        return response()->json($ServiceLocation);
    }

    public function GetServiceSchedule(Request $request)
    {
        $siteId = $request->input('siteId');
        $locationId = $request->input('locationId');
        $ServiceLocationScheduling = ServiceLocationScheduling::where('service_location_id', $locationId)
        ->where('status', 1);

        if ($siteId !== 'null') {
            $ServiceLocationScheduling->where('site_id', $siteId);
        }
        $ServiceLocationScheduling = $ServiceLocationScheduling->get();
        // dd($ServiceLocationScheduling);
        return response()->json($ServiceLocationScheduling);
    }

    public function AddServiceLocationScheduling(ServiceLocationSchedulingRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_location_scheduling)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $LocationSchedule = trim($request->input('service_schedule'));
        $Org = $request->input('ss_org');
        $Site = $request->input('ss_site');
        $ServiceLocation = $request->input('ss_location');

        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        $startTimestamp = Carbon::createFromFormat('h:i A', $startTime)->timestamp;
        $endTimestamp = Carbon::createFromFormat('h:i A', $endTime)->timestamp;

        $schedulePattern = strtolower(implode(', ', array_map('trim', explode(',', $request->input('ss_pattern')))));

        // $Schedule = $request->input('schedule_datetime');
        // list($startDateTime, $endDateTime) = explode(' - ', $Schedule);
        // $startTimestamp = Carbon::createFromFormat('m/d/Y h:i A', $startDateTime)->timestamp;
        // $endTimestamp = Carbon::createFromFormat('m/d/Y h:i A', $endDateTime)->timestamp;

        // $schedulePattern = strtolower($request->input('ss_pattern'));
        $totalPatient = $request->input('total_patient');
        $newPatient = $request->input('new_patient');
        $followupPatient = $request->input('followup_patient');
        $routinePatient = $request->input('routine_patient');
        $urgentPatient = $request->input('urgent_patient');
        if($totalPatient != '')
        {
            $AllNewFollowUpPatient = $newPatient + $followupPatient;
            $AllRoutineUrgentPatient = $routinePatient + $urgentPatient;
            if ($totalPatient != $AllNewFollowUpPatient) {
                return response()->json(["error" => "The sum of New and Follow-up Patients should equal the Total Patients."]);
            }
            if ($totalPatient != $AllRoutineUrgentPatient) {
                return response()->json(["error" => "The sum of Routine and Urgent Patients should equal the Total Patients."]);
            }
        }
        $Emp = $request->input('ss_emp');

        $Edt = $request->input('ss_edt');
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

        $LocationSchedulingExists = ServiceLocationScheduling::where('name', $LocationSchedule)
        ->where('org_id', $Org)
        ->where('site_id', $Site)
        ->where('service_location_id', $ServiceLocation)
        ->where('start_timestamp', $startTimestamp)
        ->where('end_timestamp', $endTimestamp)
        ->exists();

        if ($LocationSchedulingExists) {
            return response()->json(['info' => 'Service location Schedule already exists.']);
        }
        else
        {
            $LocationScheduling = new ServiceLocationScheduling();
            $LocationScheduling->name = $LocationSchedule;
            $LocationScheduling->org_id = $Org;
            $LocationScheduling->site_id = $Site;
            $LocationScheduling->service_location_id = $ServiceLocation;
            $LocationScheduling->start_timestamp = $startTimestamp;
            $LocationScheduling->end_timestamp = $endTimestamp;
            $LocationScheduling->schedule_pattern = $schedulePattern;
            $LocationScheduling->total_patient_limit = $totalPatient;
            $LocationScheduling->new_patient_limit = $newPatient;
            $LocationScheduling->followup_patient_limit = $followupPatient;
            $LocationScheduling->routine_patient_limit = $routinePatient;
            $LocationScheduling->urgent_patient_limit = $urgentPatient;
            $LocationScheduling->emp_id = $Emp;
            $LocationScheduling->status = $status;
            $LocationScheduling->user_id = $sessionId;
            $LocationScheduling->last_updated = $last_updated;
            $LocationScheduling->timestamp = $timestamp;
            $LocationScheduling->effective_timestamp = $Edt;
            $LocationScheduling->save();

            if (empty($LocationScheduling->id)) {
                return response()->json(['error' => 'Failed to create Service location Schedule.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$LocationSchedule}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $LocationScheduling->logid = $logs->id;
            $LocationScheduling->save();
            return response()->json(['success' => 'Service location Schedule created successfully']);
        }
    }

    public function ViewServiceLocationScheduling(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_location_scheduling)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $LocationSchedulings = ServiceLocationScheduling::select('service_location_scheduling.*',
        'organization.organization as orgName',
        'org_site.name as siteName','service_location.name as locationName',
        'employee.name as empName')
        ->join('organization', 'organization.id', '=', 'service_location_scheduling.org_id')
        ->join('org_site', 'org_site.id', '=', 'service_location_scheduling.site_id')
        ->join('service_location', 'service_location.id', '=', 'service_location_scheduling.service_location_id')
        ->leftJoin('employee', 'employee.id', '=', 'service_location_scheduling.emp_id');

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $LocationSchedulings->whereIn('service_location_scheduling.site_id', $sessionSiteIds);
            }
        }
        $LocationSchedulings = $LocationSchedulings->orderBy('service_location_scheduling.id', 'desc');
        

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $LocationSchedulings->where('service_location_scheduling.org_id', '=', $sessionOrg);
        }
        $LocationSchedulings = $LocationSchedulings;
        // ->get()
        // return DataTables::of($LocationSchedulings)
        return DataTables::eloquent($LocationSchedulings)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->Where('service_location_scheduling.name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('org_site.name', 'like', "%{$search}%")
                        ->orWhere('service_location.name', 'like', "%{$search}%")
                        ->orWhere('employee.name', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.schedule_pattern', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.total_patient_limit', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.new_patient_limit', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.followup_patient_limit', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.routine_patient_limit', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.urgent_patient_limit', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.effective_timestamp', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.timestamp', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.last_updated', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.user_id', 'like', "%{$search}%")
                        ->orWhere('service_location_scheduling.status', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($LocationScheduling) {
                return $LocationScheduling->id;  // Raw ID value
            })
            ->editColumn('id', function ($LocationScheduling) {
                $session = auth()->user();
                $LocationSchedulingName = $LocationScheduling->name;
                $effectiveDate = Carbon::createFromTimestamp($LocationScheduling->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($LocationScheduling->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($LocationScheduling->last_updated)->format('l d F Y - h:i A');

                if($LocationScheduling->effective_timestamp != 0)
                {
                    $effectiveDateTime = "<b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>";
                }
                else{
                    $effectiveDateTime = '';
                }
                $createdByName = getUserNameById($LocationScheduling->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        $effectiveDateTime
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $isEmp = $session->is_employee;
                $orgName = '';
                if($isEmp)
                {
                    $orgName = ' / '.ucwords($LocationScheduling->orgName);
                }

                $empName = isset($LocationScheduling->empName) ? $LocationScheduling->empName : 'N/A';
                $siteOrg = ucwords($LocationScheduling->siteName).$orgName;

                return ucwords($LocationSchedulingName)
                    . '<hr class="mt-1 mb-1">'
                    . $empName .'</b><br>'
                    . '<hr class="mt-1 mb-1">'
                    . $siteOrg
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('timingnvenue', function ($LocationScheduling) {
                $StartTime = Carbon::createFromTimestamp($LocationScheduling->start_timestamp)->format('h:i A');
                $EndTime = Carbon::createFromTimestamp($LocationScheduling->end_timestamp)->format('h:i A');
                $LocationName = ucwords($LocationScheduling->locationName);
                $DayTime = '<b>Start Time: </b> '.$StartTime . ' <br> <b>End Time: </b> ' .$EndTime. ' <br> <b>Pattern: </b>' .ucwords($LocationScheduling->schedule_pattern).'<br> <b>Location: </b>' .$LocationName;
                return $DayTime;
            })
            ->addColumn('otherdetails', function ($LocationScheduling) {
                $TotalPatientLimit = isset($LocationScheduling->total_patient_limit) ? $LocationScheduling->total_patient_limit : 'N/A';
                $NewPatientLimit = isset($LocationScheduling->new_patient_limit) ? $LocationScheduling->new_patient_limit : 'N/A';
                $FollowUpPatientLimit = isset($LocationScheduling->followup_patient_limit) ? $LocationScheduling->followup_patient_limit : 'N/A';
                $RoutinePatientLimit = isset($LocationScheduling->routine_patient_limit) ? $LocationScheduling->routine_patient_limit : 'N/A';
                $UrgentPatientLimit = isset($LocationScheduling->urgent_patient_limit) ? $LocationScheduling->urgent_patient_limit : 'N/A';

                $Data = "
                Total Patient Limit By: <b> " . $TotalPatientLimit . " </b><br>
                New Patient limit: <b> " . $NewPatientLimit . "</b> <br>
                Follow Up Patient Limit: <b> " . $FollowUpPatientLimit ."</b> <br>
                Routine Patient Limit: <b> " . $RoutinePatientLimit ."</b> <br>
                Urgent Patient Limit: <b> " . $UrgentPatientLimit ."</b> <br>";

                return $Data;

            })
            ->addColumn('action', function ($LocationScheduling) {
                    $LocationSchedulingId = $LocationScheduling->id;
                    $logId = $LocationScheduling->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_location_scheduling)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-locationscheduling" data-locationscheduling-id="'.$LocationSchedulingId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $LocationScheduling->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($LocationScheduling) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_location_scheduling)[3];
                return $updateStatus == 1 ? ($LocationScheduling->status ? '<span class="label label-success locationscheduling cursor-pointer" data-id="'.$LocationScheduling->id.'" data-status="'.$LocationScheduling->status.'">Active</span>' : '<span class="label label-danger locationscheduling cursor-pointer" data-id="'.$LocationScheduling->id.'" data-status="'.$LocationScheduling->status.'">Inactive</span>') : ($LocationScheduling->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','timingnvenue',
            'id','otherdetails'])
            ->make(true);
    }

    public function UpdateServiceLocationSchedulingStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_location_scheduling)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $LocationSchedulingID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $LocationScheduling = ServiceLocationScheduling::find($LocationSchedulingID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $LocationScheduling->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $LocationScheduling->effective_timestamp = 0;
        }
        // Find the role by ID
        $LocationScheduling->status = $UpdateStatus;
        $LocationScheduling->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $LocationSchedulingLog = ServiceLocationScheduling::where('id', $LocationSchedulingID)->first();
        $logIds = $LocationSchedulingLog->logid ? explode(',', $LocationSchedulingLog->logid) : [];
        $logIds[] = $logs->id;
        $LocationSchedulingLog->logid = implode(',', $logIds);
        $LocationSchedulingLog->save();

        $LocationScheduling->save();
        return response()->json(['success' => true, 200]);
    }

    // public function UpdateServiceLocationSchedulingModal($id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->service_location_scheduling)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $LocationScheduling = ServiceLocationScheduling::select('service_location_scheduling.*',
    //     'organization.organization as orgName',
    //     'org_site.name as siteName', 'service_location.name as locationName',
    //     'employee.name as empName')
    //     ->join('organization', 'organization.id', '=', 'service_location_scheduling.org_id')
    //     ->join('org_site', 'org_site.id', '=', 'service_location_scheduling.site_id')
    //     ->join('service_location', 'service_location.id', '=', 'service_location_scheduling.service_location_id')
    //     ->leftJoin('employee', 'employee.id', '=', 'service_location_scheduling.emp_id')
    //     ->where('service_location_scheduling.id', $id)
    //     ->first();

    //     $LocationSchedulingName = ucwords($LocationScheduling->name);
    //     $orgName = ucwords($LocationScheduling->orgName);
    //     $siteName = ucwords($LocationScheduling->siteName);
    //     $locationName = ucwords($LocationScheduling->locationName);
    //     $empName = isset($LocationScheduling->empName) ? ucwords($LocationScheduling->empName) : null;

    //     $effective_timestamp = $LocationScheduling->effective_timestamp;
    //     $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
    //     $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

    //     // $startdateTime = $LocationScheduling->start_timestamp;
    //     // $enddateTime = $LocationScheduling->end_timestamp;


    //     $data = [
    //         'id' => $id,
    //         'empName' => $empName,
    //         'emp' => $LocationScheduling->emp_id,
    //         'name' => $LocationSchedulingName,
    //         'orgName' => $orgName,
    //         'orgID' => $LocationScheduling->org_id,
    //         'siteName' => $siteName,
    //         'siteId' => $LocationScheduling->site_id,
    //         'locationId' => $LocationScheduling->service_location_id,
    //         'locationName' => $locationName,
    //         'TotalPatientLimit' => $LocationScheduling->total_patient_limit,
    //         'NewPatientLimit' => $LocationScheduling->new_patient_limit,
    //         'FollowUpPatientLimit' => $LocationScheduling->followup_patient_limit,
    //         'RoutinePatientLimit' => $LocationScheduling->routine_patient_limit,
    //         'UrgentPatientLimit' => $LocationScheduling->urgent_patient_limit,
    //         'schedulePattern' => ($LocationScheduling->schedule_pattern),
    //         'startdateTime' => $startdateTime,
    //         'enddateTime' => $enddateTime,
    //         'effective_timestamp' => $effective_timestamp,
    //     ];
    //     return response()->json($data);
    // }

    public function UpdateServiceLocationSchedulingModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_location_scheduling)[2];
        if ($edit == 0) {
            abort(403, 'Forbidden');
        }

        $LocationScheduling = ServiceLocationScheduling::select(
            'service_location_scheduling.*',
            'organization.organization as orgName',
            'org_site.name as siteName',
            'service_location.name as locationName',
            'employee.name as empName'
        )
            ->join('organization', 'organization.id', '=', 'service_location_scheduling.org_id')
            ->join('org_site', 'org_site.id', '=', 'service_location_scheduling.site_id')
            ->join('service_location', 'service_location.id', '=', 'service_location_scheduling.service_location_id')
            ->leftJoin('employee', 'employee.id', '=', 'service_location_scheduling.emp_id')
            ->where('service_location_scheduling.id', $id)
            ->first();

        $LocationSchedulingName = ucwords($LocationScheduling->name);
        $orgName = ucwords($LocationScheduling->orgName);
        $siteName = ucwords($LocationScheduling->siteName);
        $locationName = ucwords($LocationScheduling->locationName);
        $empName = isset($LocationScheduling->empName) ? ucwords($LocationScheduling->empName) : null;

        $effective_timestamp = Carbon::createFromTimestamp($LocationScheduling->effective_timestamp)
            ->format('l d F Y - h:i A');

        // New: Format Start/End Time separately
        $startTime = Carbon::createFromTimestamp($LocationScheduling->start_timestamp)->format('h:i A');
        $endTime = Carbon::createFromTimestamp($LocationScheduling->end_timestamp)->format('h:i A');

        $data = [
            'id' => $id,
            'empName' => $empName,
            'emp' => $LocationScheduling->emp_id,
            'name' => $LocationSchedulingName,
            'orgName' => $orgName,
            'orgID' => $LocationScheduling->org_id,
            'siteName' => $siteName,
            'siteId' => $LocationScheduling->site_id,
            'locationId' => $LocationScheduling->service_location_id,
            'locationName' => $locationName,
            'TotalPatientLimit' => $LocationScheduling->total_patient_limit,
            'NewPatientLimit' => $LocationScheduling->new_patient_limit,
            'FollowUpPatientLimit' => $LocationScheduling->followup_patient_limit,
            'RoutinePatientLimit' => $LocationScheduling->routine_patient_limit,
            'UrgentPatientLimit' => $LocationScheduling->urgent_patient_limit,
            'schedulePattern' => $LocationScheduling->schedule_pattern, // already comma-separated
            'startTime' => $startTime,
            'endTime' => $endTime,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }


    public function UpdateServiceLocationSchedule(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_location_scheduling)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $LocationSchedule = ServiceLocationScheduling::findOrFail($id);
        $LocationSchedule->name = $request->input('u_service_schedule');
        $orgID = $request->input('u_ssorg');
        if (isset($orgID)) {
            $LocationSchedule->org_id = $orgID;
        }
        $LocationSchedule->site_id = $request->input('u_sssite');
        $LocationSchedule->service_location_id = $request->input('u_sslocation');

        // ✅ Handle separate start and end time fields
        $startTime = $request->input('u_start_time'); // e.g., "12:58 AM"
        $endTime = $request->input('u_end_time');     // e.g., "04:58 PM"

        // Convert to timestamps
        $startTimestamp = Carbon::createFromFormat('h:i A', $startTime)->timestamp;
        $endTimestamp = Carbon::createFromFormat('h:i A', $endTime)->timestamp;

        $LocationSchedule->start_timestamp = $startTimestamp;
        $LocationSchedule->end_timestamp = $endTimestamp;

        // ✅ Handle comma-separated schedule pattern
        $LocationSchedule->schedule_pattern = strtolower($request->input('u_sspattern'));

        // $Schedule = $request->input('u_schedule_datetime');
        // list($startDateTime, $endDateTime) = explode(' - ', $Schedule);

        // $startTimestamp = Carbon::createFromFormat('m/d/Y h:i A', $startDateTime)->timestamp;
        // $endTimestamp = Carbon::createFromFormat('m/d/Y h:i A', $endDateTime)->timestamp;

        // $LocationSchedule->start_timestamp =  $startTimestamp;
        // $LocationSchedule->end_timestamp = $endTimestamp;

        // $LocationSchedule->schedule_pattern = strtolower($request->input('u_sspattern'));

        $LocationSchedule->total_patient_limit = $request->input('u_total_patient');
        $LocationSchedule->new_patient_limit = $request->input('u_new_patient');
        $LocationSchedule->followup_patient_limit = $request->input('u_followup_patient');
        $LocationSchedule->routine_patient_limit = $request->input('u_routine_patient');
        $LocationSchedule->urgent_patient_limit = $request->input('u_urgent_patient');

        $totalPatient = $request->input('u_total_patient');
        $newPatient = $request->input('u_new_patient');
        $followupPatient = $request->input('u_followup_patient');
        $routinePatient = $request->input('u_routine_patient');
        $urgentPatient = $request->input('u_urgent_patient');
        if($totalPatient != '')
        {
            $AllNewFollowUpPatient = $newPatient + $followupPatient;
            $AllRoutineUrgentPatient = $routinePatient + $urgentPatient;
            if ($totalPatient != $AllNewFollowUpPatient) {
                return response()->json(["error" => "The sum of New and Follow-up Patients should equal the Total Patients."]);
            }
            if ($totalPatient != $AllRoutineUrgentPatient) {
                return response()->json(["error" => "The sum of Routine and Urgent Patients should equal the Total Patients."]);
            }
        }

        $LocationSchedule->emp_id = $request->input('u_ssemp');
        $effective_date = $request->input('uss_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $LocationSchedule->effective_timestamp = $effective_date;
        $LocationSchedule->last_updated = $this->currentDatetime;
        $LocationSchedule->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $LocationSchedule->save();

        if (empty($LocationSchedule->id)) {
            return response()->json(['error' => 'Service Location Schedule Update Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $LocationScheduleLog = ServiceLocationScheduling::where('id', $LocationSchedule->id)->first();
        $logIds = $LocationScheduleLog->logid ? explode(',', $LocationScheduleLog->logid) : [];
        $logIds[] = $logs->id;
        $LocationScheduleLog->logid = implode(',', $logIds);
        $LocationScheduleLog->save();
        return response()->json(['success' => 'Service Location Schedule updated successfully']);
    }

    public function ShowServiceBooking()
    {
        $colName = 'services_booking_for_patients';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::select('id', 'organization')->where('status', 1)->get();
        // $Patients = PatientRegistration::select('patient.mr_code')
        // ->where('patient.status', 1)
        // ->leftJoin('service_booking', function($join) {
        //     $join->on('patient.mr_code', '=', DB::raw('service_booking.mr_code collate utf8mb4_unicode_ci'))
        //         ->where('service_booking.status', 1);
        // })
        // ->whereNull('service_booking.mr_code')
        // ->get();

        return view('dashboard.service-booking', compact('user','Organizations'));
    }

    public function AddServiceBooking(ServiceBookingRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->services_booking_for_patients)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Org = $request->input('sb_org');
        $Site = $request->input('sb_site');
        $ServiceLocation = $request->input('sb_location');
        $LocationSchedule = $request->input('sb_schedule');
        $Physician = $request->input('sb_emp');
        $patientStatus = $request->input('sbp_status');
        $patientPriority = $request->input('sbp_priority');
        $MRCode = $request->input('sb_mr');
        $Service = $request->input('sb_service');
        $ServiceMode = $request->input('sb_serviceMode');
        $BillingCC = $request->input('sb_billingCC');
        $Remarks = $request->input('sb_remarks');

        $ServiceSchedule = ServiceLocationScheduling::where('id', $LocationSchedule)
        ->select('start_timestamp', 'end_timestamp')
        ->first();
        $serviceStartTime = '';
        $serviceEndTime = '';
        if($ServiceSchedule)
        {
            $serviceStartTime = $ServiceSchedule->start_timestamp;
            $serviceEndTime = $ServiceSchedule->end_timestamp;
        }

        $Edt = $request->input('sb_edt');
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

        // $ServiceBookingExists = ServiceBooking::where('org_id', $Org)
        // ->where('site_id', $Site)
        // ->where('service_location_id', $ServiceLocation)
        // ->where('schedule_id', $LocationSchedule)
        // ->where('emp_id', $Physician)
        // ->where('mr_code', $MRCode)
        // ->where('patient_status', $patientStatus)
        // ->where('patient_priority', $patientPriority)
        // ->where('remarks', $Remarks)
        // ->where('status', 1)
        // ->exists();

        $common = [
            ['emp_id',         $Physician],
            ['mr_code',        $MRCode],
            ['service_id',     $Service],
            ['service_mode_id',$ServiceMode],
            ['billing_cc',     $BillingCC],
        ];

        $existsInBooking = ServiceBooking::where($common)
        ->where('status', 1)
        ->exists();

        $existsInArrival = PatientArrivalDeparture::where($common)
        ->where('status', 1)
        ->exists();

        $existsInRequisition = RequisitionForEPI::where($common)
        ->where('status', 1)
        ->select('action')
        ->first();

        if ($existsInBooking || $existsInArrival || $existsInRequisition) {
            if ($existsInBooking) {
                $msg = 'Service Booking already exists for this MR#, service, service mode, billing CC & physician';
            }
            elseif ($existsInArrival) {
                $msg = 'Arrival already recorded for this MR#, service, service mode, billing CC & physician';
            }
            else {
                $map = [
                    'i' => 'Investigation',
                    'e' => 'Encounter',
                    'p' => 'Procedure',
                ];
                $type = $map[strtolower($existsInRequisition->action)] ?? 'Request';
                $msg  = "{$type} already requested for MR#, service, service mode, billing CC & physician.";
                $msg = 'Requisition for EPI';
            }

            return response()->json([
                'info' => $msg
            ]);
        }
        // $ServiceBookingExists = ServiceBooking::where('emp_id', $Physician)
        // ->where('mr_code', $MRCode)
        // ->where('service_id', $Service)
        // ->where('service_mode_id', $ServiceMode)
        // ->where('billing_cc', $BillingCC)
        // ->where('status', 1)
        // ->exists();

        // if ($ServiceBookingExists) {
        //     return response()->json(['info' => 'Service Booking Details already exists.']);
        // }
        else
        {
            $Booking = new ServiceBooking();
            $Booking->remarks = $Remarks;
            $Booking->org_id = $Org;
            $Booking->site_id = $Site;
            $Booking->service_location_id = $ServiceLocation;

            $Booking->service_id = $Service;
            $Booking->service_mode_id = $ServiceMode;
            $Booking->billing_cc = $BillingCC;

            $Booking->schedule_id = $LocationSchedule;
            $Booking->service_starttime = $serviceStartTime;
            $Booking->service_endtime = $serviceEndTime;
            $Booking->emp_id = $Physician;
            $Booking->mr_code = $MRCode;
            $Booking->patient_status = $patientStatus;
            $Booking->patient_priority = $patientPriority;
            $Booking->status = $status;
            $Booking->user_id = $sessionId;
            $Booking->last_updated = $last_updated;
            $Booking->timestamp = $timestamp;
            $Booking->effective_timestamp = $Edt;
            $Booking->save();

            if (empty($Booking->id)) {
                return response()->json(['error' => 'Unable to Book Service! Please Try Again.']);
            }

            $logs = Logs::create([
                'module' => 'services',
                'content' => "'{$Remarks}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Booking->logid = $logs->id;
            $Booking->save();
            return response()->json(['success' => 'Service Booking created successfully']);
        }
    }

    public function ViewServiceBooking(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->services_booking_for_patients)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        // Get session user for organization filtering
        $session = auth()->user();
        $sessionOrg = $session->org_id;
        
        // Get DataTables server-side parameters
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $draw = $request->input('draw', 1);
        
        
        $query = DB::table(DB::raw("(
            SELECT mr_code, emp_id, billing_cc, service_id, service_mode_id
            FROM (
                SELECT mr_code, emp_id, billing_cc, service_id, service_mode_id FROM service_booking
                UNION ALL
                SELECT mr_code, emp_id, billing_cc, service_id, service_mode_id FROM patient_inout
                UNION ALL
                SELECT mr_code, COALESCE(emp_id, 0) as emp_id, billing_cc, service_id, service_mode_id FROM req_epi
            ) all_records
        ) combined"))
        ->select([
            'combined.mr_code',
            'combined.emp_id',
            'combined.billing_cc',
            'combined.service_id',
            'combined.service_mode_id',
            'p.name as patientName',
            'g.name as genderName',
            'p.dob as patientDOB',
            'p.cnic as patientCNIC',
            'p.cell_no as patientCellNo',
            'p.email as patientEmail',
            'p.address as patientAddress',
            'prov.name as provinceName',
            'div_table.name as divisionName',
            'dist.name as districtName',
            'sb.id as serviceBookingId',
            'sb.service_starttime',
            'sb.service_endtime',
            'sb.patient_status as BookingPatientStatus',
            'sb.patient_priority as BookingPatientPriority',
            'sb.remarks as sb_remarks',
            'sb.effective_timestamp',
            'sb.timestamp',
            'sb.last_updated',
            'sb.logid',
            'sb.user_id',
            'sb.status',
            'pi.id as patientInOutId',
            'pi.service_start_time as patientArrivalTime',
            'pi.service_end_time as patientEndTime',
            'pi.status as patientArrivalStatus',
            'pi.remarks as pi_remarks',
            's.name as ServiceName',
            's.id as ServiceId',
            'sm.name as serviceModeName',
            'sm.id as serviceModeId',
            'cc.name as billingCC',
            'cc.id as billingCCId',
            'sl.name as locationName',
            'sl.id as locationId',
            'sls.name as LocationSchedule',
            'sls.id as LocationScheduleId',
            'sls.start_timestamp',
            'sls.end_timestamp',
            'sls.schedule_pattern',
            'e.name as empName',
            'e.id as empId',
            'o.organization as orgName',
            'o.id as orgId',
            'os.name as siteName',
            'os.id as siteId',
            'pcc.name as performingCC',
            'asr.sell_price as sellPrice',
            'req.effective_timestamp as reqEffectiveTimestamp',
            'req.remarks as req_remarks',
            DB::raw('COALESCE(pi.remarks, sb.remarks, req.remarks) as Remarks')
        ])
        ->distinct() // Add DISTINCT to eliminate duplicates from ASR JOIN
        ->join('patient as p', 'p.mr_code', '=', 'combined.mr_code')
        ->join('gender as g', 'g.id', '=', 'p.gender_id')
        ->leftJoin('province as prov', 'prov.id', '=', 'p.province_id')
        ->join('division as div_table', 'div_table.id', '=', 'p.division_id')
        ->join('district as dist', 'dist.id', '=', 'p.district_id')
        ->leftJoin('service_booking as sb', function($join) {
            $join->on('sb.mr_code', '=', 'combined.mr_code')
                ->on('sb.service_id', '=', 'combined.service_id')
                ->on('sb.service_mode_id', '=', 'combined.service_mode_id')
                ->on('sb.billing_cc', '=', 'combined.billing_cc')
                ->on('sb.emp_id', '=', 'combined.emp_id');
        })
        ->leftJoin('patient_inout as pi', function($join) {
            $join->on('pi.mr_code', '=', 'combined.mr_code')
                ->on('pi.service_id', '=', 'combined.service_id')
                ->on('pi.service_mode_id', '=', 'combined.service_mode_id')
                ->on('pi.billing_cc', '=', 'combined.billing_cc')
                ->on('pi.emp_id', '=', 'combined.emp_id');
        })
        ->leftJoin('req_epi as req', function($join) {
            $join->on('req.mr_code', '=', 'combined.mr_code')
                ->on('req.service_id', '=', 'combined.service_id')
                ->on('req.service_mode_id', '=', 'combined.service_mode_id')
                ->on('req.billing_cc', '=', 'combined.billing_cc')
                ->whereRaw('(req.emp_id = combined.emp_id OR (req.emp_id IS NULL AND combined.emp_id = 0))');
        })
        ->join('services as s', 's.id', '=', 'combined.service_id')
        ->join('service_mode as sm', 'sm.id', '=', 'combined.service_mode_id')
        ->join('costcenter as cc', 'cc.id', '=', 'combined.billing_cc')
        ->leftJoin('employee as e', function($join) {
            $join->on('e.id', '=', 'combined.emp_id')
                ->where('combined.emp_id', '!=', 0);
        })
        ->leftJoin('organization as o', 'o.id', '=', DB::raw('COALESCE(sb.org_id, pi.org_id, req.org_id)'))
        ->leftJoin('org_site as os', 'os.id', '=', DB::raw('COALESCE(sb.site_id, pi.site_id, req.site_id)'))
        ->leftJoin('service_location as sl', 'sl.id', '=', 'sb.service_location_id')
        ->leftJoin('service_location_scheduling as sls', 'sls.id', '=', 'sb.schedule_id')
        ->leftJoin('costcenter as pcc', 'pcc.id', '=', 'e.cc_id')
        ->leftJoin('activated_service as as1', function($join) {
            $join->on('as1.service_id', '=', 'combined.service_id')
                ->on('as1.site_id', '=', DB::raw('COALESCE(sb.site_id, pi.site_id, req.site_id)'))
                ->where('as1.status', '=', 1);
        })
        ->leftJoin('activated_service_rate as asr', function($join) {
            $join->on('asr.activated_service_id', '=', 'as1.id')
                ->on('asr.service_mode_id', '=', 'combined.service_mode_id');
        })
        ->where('p.status', 1)
        ->orderBy('combined.mr_code', 'desc');
    
        // Apply additional filters
        if($sessionOrg != '0') {
            $query->where('p.org_id', $sessionOrg);
        }
        if ($request->has('site_id') && $request->site_id != '' && $request->site_id != 'Loading...') {
            $query->where('p.site_id', $request->site_id);
        }
        if ($request->has('mr_no') && $request->mr_no != '' && $request->mr_no != 'Loading...') {
            $query->where('p.mr_code', $request->mr_no);
        }
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $query->whereIn('p.site_id', $sessionSiteIds);
            }
        }

        // Check if site or MR is selected
        $siteSelected = $request->has('site_id') && $request->site_id != '' && $request->site_id != 'Loading...';
        $mrSelected = $request->has('mr_no') && $request->mr_no != '' && $request->mr_no != 'Loading...';
        
        // Apply date filtering if a specific date filter is chosen (regardless of site/MR selection)
        if ($request->has('date_filter') && $request->date_filter != '') {
            $dateFilter = $request->date_filter;
            
        
            $today = Carbon::today()->setTimezone('Asia/Karachi');
            
            switch ($dateFilter) {
                case 'today':
                    $startDate = Carbon::today()->setTimezone('Asia/Karachi')->startOfDay()->timestamp;
                    $endDate = Carbon::today()->setTimezone('Asia/Karachi')->endOfDay()->timestamp;
                    break;
                case 'yesterday':
                    $startDate = Carbon::today()->setTimezone('Asia/Karachi')->subDay()->startOfDay()->timestamp;
                    $endDate = Carbon::today()->setTimezone('Asia/Karachi')->subDay()->endOfDay()->timestamp;
                    break;
                case 'this_week':
                    $startDate = Carbon::today()->setTimezone('Asia/Karachi')->startOfWeek()->startOfDay()->timestamp;
                    $endDate = Carbon::today()->setTimezone('Asia/Karachi')->endOfWeek()->endOfDay()->timestamp;
                    break;
                case 'last_week':
                    $startDate = Carbon::today()->setTimezone('Asia/Karachi')->subWeek()->startOfWeek()->startOfDay()->timestamp;
                    $endDate = Carbon::today()->setTimezone('Asia/Karachi')->subWeek()->endOfWeek()->endOfDay()->timestamp;
                    break;
                case 'this_month':
                    $startDate = Carbon::today()->setTimezone('Asia/Karachi')->startOfMonth()->startOfDay()->timestamp;
                    $endDate = Carbon::today()->setTimezone('Asia/Karachi')->endOfMonth()->endOfDay()->timestamp;
                    break;
                case 'last_month':
                    // Alternative approach: Use specific month calculation with Asia/Karachi timezone
                    $currentYear = Carbon::now()->setTimezone('Asia/Karachi')->year;
                    $currentMonth = Carbon::now()->setTimezone('Asia/Karachi')->month;
                    $lastMonthYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
                    $lastMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
                    
                    $startDate = Carbon::create($lastMonthYear, $lastMonth, 1, 0, 0, 0, 'Asia/Karachi')->timestamp;
                    $endDate = Carbon::create($lastMonthYear, $lastMonth, 1, 0, 0, 0, 'Asia/Karachi')->endOfMonth()->timestamp;
                    break;
                case 'this_year':
                    $currentYear = Carbon::today()->setTimezone('Asia/Karachi')->year;
                    $startDate = Carbon::create($currentYear, 1, 1, 0, 0, 0, 'Asia/Karachi')->startOfYear()->timestamp;
                    $endDate = Carbon::create($currentYear, 12, 31, 23, 59, 59, 'Asia/Karachi')->endOfYear()->timestamp;
                    break;
                case 'last_year':
                    $currentYear = Carbon::today()->setTimezone('Asia/Karachi')->year;
                    $lastYear = $currentYear - 1;
                    $startDate = Carbon::create($lastYear, 1, 1, 0, 0, 0, 'Asia/Karachi')->startOfYear()->timestamp;
                    $endDate = Carbon::create($lastYear, 12, 31, 23, 59, 59, 'Asia/Karachi')->endOfYear()->timestamp;
                    break;
                default:
                    // Default to today
                    $startDate = Carbon::today()->setTimezone('Asia/Karachi')->startOfDay()->timestamp;
                    $endDate = Carbon::today()->setTimezone('Asia/Karachi')->endOfDay()->timestamp;
                    break;
            }
            
            // Apply date conditions - check each table's timestamp field (date only)
            $query->where(function($q) use ($startDate, $endDate) {
                $q->where(function($subQ) use ($startDate, $endDate) {
                    // Filter by patient_inout.service_start_time (date only)
                    $subQ->whereNotNull('pi.id')
                         ->whereRaw('DATE(FROM_UNIXTIME(pi.service_start_time)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
                })->orWhere(function($subQ) use ($startDate, $endDate) {
                    // Filter by service_booking.service_starttime (date only) - ONLY if no patient_inout record exists
                    $subQ->whereNotNull('sb.id')
                         ->whereNull('pi.id')
                         ->whereRaw('DATE(FROM_UNIXTIME(sb.service_starttime)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
                })->orWhere(function($subQ) use ($startDate, $endDate) {
                    // Filter by req_epi.effective_timestamp (date only) - ONLY if no patient_inout or service_booking record exists
                    $subQ->whereNotNull('req.id')
                         ->whereNull('pi.id')
                         ->whereNull('sb.id')
                         ->whereRaw('DATE(FROM_UNIXTIME(req.effective_timestamp)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
                });
            });
        } elseif (!($siteSelected || $mrSelected)) {
            // If neither site nor MR is selected, apply today's filter by default
            $startDate = Carbon::today()->setTimezone('Asia/Karachi')->startOfDay()->timestamp;
            $endDate = Carbon::today()->setTimezone('Asia/Karachi')->endOfDay()->timestamp;
            
            // Apply date conditions - check each table's timestamp field (date only)
            $query->where(function($q) use ($startDate, $endDate) {
                $q->where(function($subQ) use ($startDate, $endDate) {
                    $subQ->whereNotNull('pi.id')
                         ->whereRaw('DATE(FROM_UNIXTIME(pi.service_start_time)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
                })->orWhere(function($subQ) use ($startDate, $endDate) {
                    $subQ->whereNotNull('sb.id')
                         ->whereNull('pi.id')
                         ->whereRaw('DATE(FROM_UNIXTIME(sb.service_starttime)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
                })->orWhere(function($subQ) use ($startDate, $endDate) {
                    $subQ->whereNotNull('req.id')
                         ->whereNull('pi.id')
                         ->whereNull('sb.id')
                         ->whereRaw('DATE(FROM_UNIXTIME(req.effective_timestamp)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
                });
            });
        } 
        // Debug: Log pagination parameters

        return DataTables::query($query)
            ->addColumn('id_raw', function ($ServiceBooking) {
                return $ServiceBooking->serviceBookingId ?? $ServiceBooking->patientInOutId ?? null;
            })
            ->editColumn('id', function ($ServiceBooking) {
                $effectiveDate = $ServiceBooking->effective_timestamp
                    ? Carbon::createFromTimestamp($ServiceBooking->effective_timestamp)->format('l d F Y - h:i A')
                    : 'N/A';

                $timestamp = $ServiceBooking->timestamp
                    ? Carbon::createFromTimestamp($ServiceBooking->timestamp)->format('l d F Y - h:i A')
                    : 'N/A';

                $lastUpdated = $ServiceBooking->last_updated
                    ? Carbon::createFromTimestamp($ServiceBooking->last_updated)->format('l d F Y - h:i A')
                    : 'N/A';

                $createdByName = getUserNameById($ServiceBooking->user_id ?? null);
                $createdInfo = "
                    <b>Created By:</b> " . ucwords((string)$createdByName) . "  <br>
                    <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                    <b>RecordedAt:</b> " . $timestamp . " <br>
                    <b>LastUpdated:</b> " . $lastUpdated;

                $mrCode = $ServiceBooking->mr_code ?? 'N/A';
                $PatientName = ucwords((string)($ServiceBooking->patientName ?? ''));
                $Gender = ucwords((string)($ServiceBooking->genderName ?? ''));
                $DOB = $ServiceBooking->patientDOB
                    ? Carbon::createFromTimestamp($ServiceBooking->patientDOB)->format('d F Y')
                    : 'N/A';

                $cnic = !empty($ServiceBooking->patientCNIC) ? $ServiceBooking->patientCNIC : 'N/A';
                $Email = !empty($ServiceBooking->patientEmail) ? $ServiceBooking->patientEmail : 'N/A';
                $CellNo = $ServiceBooking->patientCellNo ?? 'N/A';
                $Address = ucwords((string)($ServiceBooking->patientAddress ?? ''));
                $Province = ucwords((string)($ServiceBooking->provinceName ?? ''));
                $District = ucwords((string)($ServiceBooking->districtName ?? ''));
                $Division = ucwords((string)($ServiceBooking->divisionName ?? ''));

                return $mrCode
                    . '<hr class="mt-1 mb-2">'
                    . $PatientName
                    . '<br>' . $Gender
                    . '<br><b>DOB: </b>' . $DOB
                    . '<br><b>CNIC: </b>' . $cnic
                    . '<br><b>Cell No: </b>' . $CellNo
                    . '<br><b>Email: </b>' . $Email
                    . '<br><b>Address: </b>' . $Address
                    . '<hr class="mt-1 mb-2">'
                    . '<b>District: </b>' . $District
                    . '<br><b>Division: </b>' . $Division
                    . '<br><b>Province: </b>' . $Province
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body" data-toggle="popover" data-placement="right" data-html="true" data-content="' . $createdInfo . '">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('serviceBooking', function ($ServiceBooking) {
                $BookingId = $ServiceBooking->serviceBookingId ?? null;
                $session = auth()->user();

                if ($BookingId) {
                    $StartTime = $ServiceBooking->service_starttime
                    ? Carbon::createFromTimestamp($ServiceBooking->service_starttime)->format('h:i A')
                    : 'N/A';

                    $EndTime = $ServiceBooking->service_endtime
                    ? Carbon::createFromTimestamp($ServiceBooking->service_endtime)->format('h:i A')
                    : 'N/A';

                    $DayTime = '<b>Start Time: </b> ' . $StartTime . ' <br> <b>End Time: </b> ' . $EndTime;
                    
                    $empName = ucwords((string)($ServiceBooking->empName ?? ''));
                    $ServiceName = ucwords((string)($ServiceBooking->ServiceName ?? ''));
                    $Remarks = !empty($ServiceBooking->Remarks) ? ucwords((string)$ServiceBooking->Remarks) : 'N/A';

                    $Location = ucwords((string)($ServiceBooking->locationName ?? ''));
                    $Pattern = ucwords((string)($ServiceBooking->schedule_pattern ?? ''));
                    $PatientStatus = ucwords((string)($ServiceBooking->BookingPatientStatus ?? ''));
                    $PatientPriority = ucwords((string)($ServiceBooking->BookingPatientPriority ?? ''));

                    $sessionOrg = $session->org_id ?? '0';
                    $orgName = '';
                    if ($sessionOrg == '0') {
                        $orgName = ' / ' . ucwords((string)($ServiceBooking->orgName ?? ''));
                    }

                    $siteOrg = ucwords((string)($ServiceBooking->siteName ?? '')) . $orgName;

                    return $ServiceName
                        . '<hr class="mt-1 mb-2">'
                        . $empName
                        . '<br>' . $siteOrg
                        . '<hr class="mt-1 mb-2">'
                        . $DayTime
                        . '<br><b>Pattern & Location: </b>' . $Pattern . ' & ' . $Location
                        . '<br><b>Patient Status: </b>' . $PatientStatus
                        . '<br><b>Patient Priority: </b>' . $PatientPriority
                        . '<br><b>Remarks: </b>' . $Remarks;
                } else {
                    $ArrivalId = $ServiceBooking->patientInOutId ?? null;

                    if ($ArrivalId) {
                        return '<h5><b>Unbooked</b></h5><hr class="mt-1 mb-2">';
                    }

                    // Handle scheduling fallback
                    $orgId = $ServiceBooking->orgId ?? '';
                    $orgName = ucwords((string)($ServiceBooking->orgName ?? ''));
                    $siteId = $ServiceBooking->siteId ?? '';
                    $siteName = ucwords((string)($ServiceBooking->siteName ?? ''));
                    $mrCode = $ServiceBooking->mr_code ?? '';
                    $ServiceMode = ucwords((string)($ServiceBooking->serviceModeName ?? ''));
                    $ServiceModeId = $ServiceBooking->serviceModeId ?? '';
                    $empName = ucwords((string)($ServiceBooking->empName ?? ''));
                    $empId = $ServiceBooking->empId ?? '';
                    $Service = ucwords((string)($ServiceBooking->ServiceName ?? ''));
                    $ServiceId = $ServiceBooking->ServiceId ?? '';
                    $BillingCC = ucwords((string)($ServiceBooking->billingCC ?? ''));
                    $BillingCCId = $ServiceBooking->billingCCId ?? '';

                    return '<h5><b>Unbooked</b></h5><hr class="mt-1 mb-2">
                        <span class="text-underline add-servicebooking"
                            data-mr="' . htmlspecialchars($mrCode) . '"
                            data-orgname="' . htmlspecialchars($orgName) . '"
                            data-orgid="' . htmlspecialchars($orgId) . '"
                            data-sitename="' . htmlspecialchars($siteName) . '"
                            data-siteid="' . htmlspecialchars($siteId) . '"
                            data-servicemode="' . htmlspecialchars($ServiceMode) . '"
                            data-servicemodeid="' . htmlspecialchars($ServiceModeId) . '"
                            data-empname="' . htmlspecialchars($empName) . '"
                            data-empid="' . htmlspecialchars($empId) . '"
                            data-service="' . htmlspecialchars($Service) . '"
                            data-serviceid="' . htmlspecialchars($ServiceId) . '"
                            data-billingcc="' . htmlspecialchars($BillingCC) . '"
                            data-billingccid="' . htmlspecialchars($BillingCCId) . '"
                            style="cursor:pointer; color: #fb3a3a;font-weight: 500;">
                            Click here to Schedule Service
                        </span>';
                }
            })
            ->editColumn('serviceDetails', function ($ServiceBooking) {
                $sellPrice = number_format((float)($ServiceBooking->sellPrice ?? 0), 2);

                $orgId = $ServiceBooking->orgId ?? '';
                $orgName = ucwords((string)($ServiceBooking->orgName ?? ''));

                $Remarks = $ServiceBooking->Remarks ?? '';

                $siteId = $ServiceBooking->siteId ?? '';
                $siteName = ucwords((string)($ServiceBooking->siteName ?? ''));

                $ServiceMode = ucwords((string)($ServiceBooking->serviceModeName ?? ''));
                $ServiceModeId = $ServiceBooking->serviceModeId ?? '';

                $locationName = ucwords((string)($ServiceBooking->locationName ?? ''));
                $locationId = $ServiceBooking->locationId ?? '';

                $LocationSchedule = ucwords((string)($ServiceBooking->LocationSchedule ?? ''));
                $LocationScheduleId = $ServiceBooking->LocationScheduleId ?? '';

                $ScheduleStartTime = $ServiceBooking->start_timestamp
                    ? Carbon::createFromTimestamp($ServiceBooking->start_timestamp)->format('h:i A')
                    : 'N/A';

                $ScheduleEndTime = $ServiceBooking->end_timestamp
                    ? Carbon::createFromTimestamp($ServiceBooking->end_timestamp)->format('h:i A')
                    : 'N/A';
                    
                $Pattern = ucwords(($ServiceBooking->schedule_pattern ?? 'N/A'));

                $empName = ucwords((string)($ServiceBooking->empName ?? 'N/A'));
                $empId = $ServiceBooking->empId ?? '';

                $Service = ucwords((string)($ServiceBooking->ServiceName ?? ''));
                $ServiceId = $ServiceBooking->ServiceId ?? '';

                $BillingCC = ucwords((string)($ServiceBooking->billingCC ?? ''));
                $BillingCCId = $ServiceBooking->billingCCId ?? '';

                $ShowServiceDetails = $ServiceMode . '<br>' . $Service . '<br> <b>Specialty: </b>' . $BillingCC . '<br><b>Responsible Person:</b> ' . $empName;

                $ArrivalId = $ServiceBooking->patientInOutId ?? null;

                if ($ArrivalId) {
                    $patientArrivalTime = $ServiceBooking->patientArrivalTime
                        ? Carbon::createFromTimestamp($ServiceBooking->patientArrivalTime)->format('l d F Y - h:i A')
                        : 'N/A';

                    $Status = $ServiceBooking->patientArrivalStatus ?? null;

                    if ($Status == 0) {
                        $serviceEndTime = $ServiceBooking->patientEndTime
                            ? Carbon::createFromTimestamp($ServiceBooking->patientEndTime)->format('l d F Y - h:i A')
                            : 'N/A';

                        $patientEndTime = '<hr class="mt-2 mb-1"><h6><b>Service Performed By</b></h6>'
                            . ucwords((string)($ServiceBooking->performingCC ?? ''))
                            . '<br>' . $empName
                            . '<br><b>Service End Time: </b>' . $serviceEndTime;
                    } else {
                        $patientEndTime = '<hr class="mt-2 mb-2"><h6><b>Service not yet completed</b></h6>';
                    }

                    return $ShowServiceDetails
                        . '<hr class="mt-1 mb-1">'
                        . '<b>Patient Arrived At: </b>' . $patientArrivalTime
                        . $patientEndTime;
                } else {
                    $mrCode = $ServiceBooking->mr_code ?? '';
                    $otherArrivals = PatientArrivalDeparture::where('mr_code', $mrCode)
                        ->where('service_mode_id', '!=', $ServiceModeId)
                        ->where('status', 1)
                        ->get();

                    if ($otherArrivals->isNotEmpty()) {
                        $otherServiceModeid = $otherArrivals->pluck('service_mode_id');
                        $ServiceModes = ServiceMode::whereIn('id', $otherServiceModeid)
                        ->pluck('name')
                        ->toArray();

                        $ServiceModeCount = count($ServiceModes);
                        if ($ServiceModeCount > 1) {
                            // remove last element
                            $last = array_pop($ServiceModes);
                            // join the rest with commas, then append "and $last"
                            $modeList = implode(', ', $ServiceModes) . ' and ' . $last;
                        } else {
                            // 0 or 1 item
                            $modeList = $ServiceModes[0] ?? 'Unknown';
                        }
                        $serviceWord  = $ServiceModeCount > 1 ? 'services' : 'service';
                        $demonstrative = $ServiceModeCount > 1 ? 'those'   : 'that';

                        $message = " Please end {$demonstrative} {$serviceWord} first.";

                        return $ShowServiceDetails
                        . '<hr class="mt-1 mb-2">'
                        . '<h6>Patient already arrived in "<b>'
                        . e($modeList)
                        . '</b>'.$message;

                    } else {
                        $PatientStatusVal = $ServiceBooking->BookingPatientStatus ?? '';
                        $PatientStatus = ucwords((string)$PatientStatusVal);
                        $PatientPriorityVal = $ServiceBooking->BookingPatientPriority ?? '';
                        $PatientPriority = ucwords((string)$PatientPriorityVal);

                        return $ShowServiceDetails . '<hr class="mt-1 mb-2"><h6><b>Patient Not Yet Arrived</b></h6>
                        <a href="' . route('patient-inout', [
                                'mr' => encrypt($mrCode),
                                'billedamount' => encrypt($sellPrice),
                                'orgname' => encrypt($orgName),
                                'orgid' => encrypt($orgId),
                                'sitename' => encrypt($siteName),
                                'siteid' => encrypt($siteId),
                                'servicemode' => encrypt($ServiceMode),
                                'smid' => encrypt($ServiceModeId),
                                'empname' => encrypt($empName),
                                'eid' => encrypt($empId),
                                'service' => encrypt($Service),
                                'sid' => encrypt($ServiceId),
                                'billingcc' => encrypt($BillingCC),
                                'bcid' => encrypt($BillingCCId),
                                'patientstatusval' => encrypt($PatientStatusVal),
                                'patientstatus' => encrypt($PatientStatus),
                                'patientpriorityval' => encrypt($PatientPriorityVal),
                                'patientpriority' => encrypt($PatientPriority),
                                'locationname' => encrypt($locationName),
                                'locationid' => encrypt($locationId),
                                'schedulename' => encrypt($LocationSchedule),
                                'scheduleid' => encrypt($LocationScheduleId),
                                'scheduleStartTime' => encrypt($ScheduleStartTime),
                                'scheduleEndTime' => encrypt($ScheduleEndTime),
                                'pattern' => encrypt($Pattern),
                                'remarks' => encrypt($Remarks)
                            ]) . '" target="_blank">
                            <span class="text-underline" style="cursor:pointer; color: #fb3a3a;font-weight: 500;">
                                Confirm Patient Arrival
                            </span></a>';
                    }
                }
            })
            ->addColumn('action', function ($ServiceBooking) {
                $ServiceBookingId = $ServiceBooking->serviceBookingId ?? null;
                $logId = $ServiceBooking->logid ?? null;
                $status = $ServiceBooking->status ?? null;

                $Rights = $this->rights;
                $rightsString = $Rights->services_booking_for_patients ?? '';
                $permissions = explode(',', $rightsString);
                $edit = $permissions[2] ?? 0;

                $actionButtons = '';

                if ((int)$edit === 1 && $ServiceBookingId) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-servicebooking" data-servicebooking-id="' . $ServiceBookingId . '">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                }

                if ($logId) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="' . $logId . '">'
                        . '<i class="fa fa-eye"></i> View Logs'
                        . '</button>';
                }

                return ($status === 1 || $status === '1')
                    ? $actionButtons
                    : '<span class="font-weight-bold">Status must be Active or Service must be booked to perform any action.</span>';
            })
            ->editColumn('status', function ($ServiceBooking) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->services_booking_for_patients)[3];

                return $updateStatus == 1
                ? (
                    $ServiceBooking->status === null
                        ? 'N/A'
                        : (
                            $ServiceBooking->status === 1
                                ? '<span class="label label-success servicebooking cursor-pointer" data-id="' . $ServiceBooking->serviceBookingId . '" data-status="' . $ServiceBooking->status . '">Active</span>'
                                : '<span class="label label-danger servicebooking cursor-pointer" data-id="' . $ServiceBooking->serviceBookingId . '" data-status="' . $ServiceBooking->status . '">Inactive</span>'
                          )
                  )
                : (
                    $ServiceBooking->status === null
                        ? 'N/A'
                        : (
                            $ServiceBooking->status === 1
                                ? '<span class="label label-success">Active</span>'
                                : '<span class="label label-danger">Inactive</span>'
                          )
                  );

            })->rawColumns(['action', 'status','serviceBooking','serviceDetails',
            'id'])
            ->make(true);
            
        // Debug: Log final response
        \Log::info('=== DATATABLES RESPONSE ===');
        \Log::info('Draw: ' . $draw . ', Server-side processing enabled');
        \Log::info('=== END DATATABLES DEBUG ===');
    }

    public function UpdateServiceBookingStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->services_booking_for_patients)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceBookingID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ServiceBooking = ServiceBooking::find($ServiceBookingID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ServiceBooking->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ServiceBooking->effective_timestamp = 0;
        }
        $ServiceBooking->status = $UpdateStatus;
        $ServiceBooking->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceBookingLog = ServiceBooking::where('id', $ServiceBookingID)->first();
        $logIds = $ServiceBookingLog->logid ? explode(',', $ServiceBookingLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceBookingLog->logid = implode(',', $logIds);
        $ServiceBookingLog->save();

        $ServiceBooking->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateServiceBookingModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->services_booking_for_patients)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceBookings = ServiceBooking::select('service_booking.*',
        'organization.organization as orgName',
        'org_site.name as siteName','service_location.name as locationName',
        'service_location_scheduling.name as LocationSchedule',
        'service_location_scheduling.start_timestamp','service_location_scheduling.end_timestamp',
        'service_location_scheduling.schedule_pattern','employee.name as empName','employee.id as empId',
        'services.name as serviceName','service_mode.name as servicemodeName','costcenter.name as CCName')
        ->join('organization', 'organization.id', '=', 'service_booking.org_id')
        ->join('org_site', 'org_site.id', '=', 'service_booking.site_id')
        ->join('employee', 'employee.id', '=', 'service_booking.emp_id')
        ->join('service_location', 'service_location.id', '=', 'service_booking.service_location_id')
        ->join('service_location_scheduling', 'service_location_scheduling.id', '=', 'service_booking.schedule_id')
        ->join('services', 'services.id', '=', 'service_booking.service_id')
        ->join('service_mode', 'service_mode.id', '=', 'service_booking.service_mode_id')
        ->join('costcenter', 'costcenter.id', '=', 'service_booking.billing_cc')

        ->where('service_booking.id', $id)
        ->first();

        $Remarks = ucwords($ServiceBookings->remarks);
        $orgName = ucwords($ServiceBookings->orgName);
        $siteName = ucwords($ServiceBookings->siteName);
        $locationName = ucwords($ServiceBookings->locationName);
        $LocationSchedule = ucwords($ServiceBookings->LocationSchedule);
        $MRno = ($ServiceBookings->mr_code);
        $PatientStatus = ($ServiceBookings->patient_status);
        $PatientPriority = ($ServiceBookings->patient_priority);
        $empName = ($ServiceBookings->empName);


        $effective_timestamp = $ServiceBookings->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $startdateTime = $ServiceBookings->start_timestamp;
        $enddateTime = $ServiceBookings->end_timestamp;

        $data = [
            'id' => $id,
            'serviceName' => ucwords($ServiceBookings->serviceName),
            'serviceID' => $ServiceBookings->service_id,
            'servicemodeName' => $ServiceBookings->servicemodeName,
            'serviceModeID' => $ServiceBookings->service_mode_id,
            'CCName' => ucwords($ServiceBookings->CCName),
            'CCid' => $ServiceBookings->billing_cc,
            'remarks' => $Remarks,
            'orgName' => $orgName,
            'orgID' => $ServiceBookings->org_id,
            'siteName' => $siteName,
            'siteId' => $ServiceBookings->site_id,
            'locationId' => $ServiceBookings->service_location_id,
            'locationName' => $locationName,
            'locationScheduleId' => $ServiceBookings->schedule_id,
            'locationScheduleName' => $LocationSchedule,
            'MRno' => $MRno,
            'PatientStatus' => ($PatientStatus),
            'PatientPriority' => ($PatientPriority),
            'empName' => ($empName),
            'empId' => $ServiceBookings->empId,
            'schedulePattern' => ucfirst($ServiceBookings->schedule_pattern),
            'startdateTime' => $startdateTime,
            'enddateTime' => $enddateTime,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateServiceBooking(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->services_booking_for_patients)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceBookings = ServiceBooking::findOrFail($id);
        $ServiceBookings->remarks = $request->input('u_sb_remarks');
        $orgID = $request->input('u_sb_org');
        if (isset($orgID)) {
            $ServiceBookings->org_id = $orgID;
        }
        $ServiceBookings->site_id = $request->input('u_sb_site');
        $ServiceBookings->service_location_id = $request->input('u_sb_location');
        $ServiceBookings->schedule_id = $request->input('u_sb_schedule');
        $ServiceBookings->emp_id = $request->input('u_sb_emp');
        $ServiceBookings->service_id = $request->input('u_sb_service');
        $ServiceBookings->service_mode_id = $request->input('u_sb_sm');
        $ServiceBookings->billing_cc = $request->input('u_sb_cc');
        $ServiceBookings->mr_code = $request->input('u_sb_mr');
        $ServiceBookings->patient_status = $request->input('u_sbp_status');
        $ServiceBookings->patient_priority = $request->input('u_sbp_priority');
        $effective_date = $request->input('u_sb_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ServiceBookings->effective_timestamp = $effective_date;
        $ServiceBookings->last_updated = $this->currentDatetime;
        $ServiceBookings->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceBookings->save();

        if (empty($ServiceBookings->id)) {
            return response()->json(['error' => 'Service Booking Update Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceBookingLog = ServiceBooking::where('id', $ServiceBookings->id)->first();
        $logIds = $ServiceBookingLog->logid ? explode(',', $ServiceBookingLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceBookingLog->logid = implode(',', $logIds);
        $ServiceBookingLog->save();
        return response()->json(['success' => 'Service Booking updated successfully']);
    }


    public function ShowServiceRequsitionSetup()
    {
        $colName = 'service_requisition_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->select('id', 'organization')->get();
        // $Services = Service::where('status', 1)
        // ->select('id', 'name')
        // ->get();
        $Services = Service::where('status', 1)
        ->whereNotIn('id', function ($query) {
            $query->select('service_id')->from('service_requisition_setup');
        })
        ->select('id', 'name')
        ->get();

        return view('dashboard.service-requisition-setup', compact('user', 'Services', 'Organizations'));
    }

    // public function AddServiceRequisition(ServiceRequisitionRequest $request)
    // {
    //     $rights = $this->rights;
    //     $add = explode(',', $rights->service_requisition_setup)[0];
    //     if($add == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $Org = $request->input('sr_org');
    //     $Service = trim($request->input('sr_service'));
    //     $RequisitionStatus = $request->input('sr_status');
    //     $Description = trim($request->input('sr_description'));
    //     $Edt = $request->input('sr_edt');
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

    //     $ServiceRequisitionExists = ServiceRequisitionSetup::where('service_id', $Service)
    //     ->where('org_id', $Org)
    //     ->exists();

    //     if ($ServiceRequisitionExists) {
    //         return response()->json(['info' => 'Service Requisition already exists.']);
    //     }
    //     else
    //     {
    //         $ServiceRequisition = new ServiceRequisitionSetup();
    //         $ServiceRequisition->org_id = $Org;
    //         $ServiceRequisition->service_id = $Service;
    //         $ServiceRequisition->mandatory = $RequisitionStatus;
    //         $ServiceRequisition->description = $Description;
    //         $ServiceRequisition->status = $status;
    //         $ServiceRequisition->user_id = $sessionId;
    //         $ServiceRequisition->last_updated = $last_updated;
    //         $ServiceRequisition->timestamp = $timestamp;
    //         $ServiceRequisition->effective_timestamp = $Edt;
    //         $ServiceRequisition->save();

    //         if (empty($ServiceRequisition->id)) {
    //             return response()->json(['error' => 'Failed to create Service Requisition.']);
    //         }

    //         $ServiceName = Service::where('id', $Service)
    //         ->select('id', 'name')
    //         ->get();
    //         if($RequisitionStatus == 1)
    //         {
    //             $logMsg = "'Service request has been set as mandatory for {$ServiceName} by {$sessionName}'";
    //         }
    //         else{
    //             $logMsg = "'Service request has not been marked as mandatory for {$ServiceName} by {$sessionName}.'";
    //         }

    //         $logs = Logs::create([
    //             'module' => 'services',
    //             'content' => "'Service request has been set as mandatory for {$ServiceName} by {$sessionName}'",
    //             'event' => 'add',
    //             'timestamp' => $timestamp,
    //         ]);
    //         $logId = $logs->id;
    //         $ServiceRequisition->logid = $logs->id;
    //         $ServiceRequisition->save();
    //         return response()->json(['success' => 'Service Requisition added successfully']);
    //     }
    // }

    public function ViewServiceRequisition(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_requisition_setup)[0];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceRequisitions = ServiceRequisitionSetup::select('service_requisition_setup.*',
        'organization.organization as orgName','services.name as serviceName')
        ->join('organization', 'organization.id', '=', 'service_requisition_setup.org_id')
        ->join('services', 'services.id', '=', 'service_requisition_setup.service_id')
        ->orderBy('service_requisition_setup.id', 'desc');


        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ServiceRequisitions->where('service_requisition_setup.org_id', '=', $sessionOrg);
        }
        $ServiceRequisitions = $ServiceRequisitions;
        // ->get()
        // return DataTables::of($ServiceRequisitions)
        return DataTables::eloquent($ServiceRequisitions)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('service_requisition_setup.id', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('services.name', 'like', "%{$search}%")
                        ->orWhere('service_requisition_setup.description', 'like', "%{$search}%")
                        ->orWhere('service_requisition_setup.timestamp', 'like', "%{$search}%")
                        ->orWhere('service_requisition_setup.effective_timestamp', 'like', "%{$search}%")
                        ->orWhere('service_requisition_setup.last_updated', 'like', "%{$search}%")
                        ->orWhere('service_requisition_setup.mandatory', 'like', "%{$search}%")
                        ->orWhere('service_requisition_setup.status', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ServiceRequisition) {
                return $ServiceRequisition->id;
            })
            ->editColumn('id', function ($ServiceRequisition) {
                $session = auth()->user();
                $sessionName = $session->name;
                $ServiceName = $ServiceRequisition->serviceName;
                $effectiveDate = Carbon::createFromTimestamp($ServiceRequisition->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ServiceRequisition->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ServiceRequisition->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ServiceRequisition->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;


                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($ServiceRequisition->orgName);
                }

                return $ServiceName.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })

            ->editColumn('mandatory', function ($ServiceRequisition) {
                $MandatoryStatus = $ServiceRequisition->mandatory == 0 ? "<span class='label label-danger' style='font-size: 15px;'>No</span>" : "<span class='label label-success' style='font-size: 15px;'>Yes</span>";
                return $MandatoryStatus;
            })
            ->editColumn('desc', function ($ServiceRequisition) {
                $Description = ucwords($ServiceRequisition->description);
                if(empty($Description))
                {
                    $Description = 'N/A';
                }
                return $Description;
            })
            ->addColumn('action', function ($ServiceRequisition) {
                    $ServiceRequisitionId = $ServiceRequisition->id;
                    $logId = $ServiceRequisition->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->service_requisition_setup)[1];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-servicerequisition" data-servicerequisition-id="'.$ServiceRequisitionId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $ServiceRequisition->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($ServiceRequisition) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->service_requisition_setup)[2];
                return $updateStatus == 1 ? ($ServiceRequisition->status ? '<span class="label label-success servicerequisition cursor-pointer" data-id="'.$ServiceRequisition->id.'" data-status="'.$ServiceRequisition->status.'">Active</span>' : '<span class="label label-danger servicerequisition cursor-pointer" data-id="'.$ServiceRequisition->id.'" data-status="'.$ServiceRequisition->status.'">Inactive</span>') : ($ServiceRequisition->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status', 'mandatory', 'desc',
            'id'])
            ->make(true);
    }

    public function UpdateServiceRequisitionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->service_requisition_setup)[2];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ServiceRequisition = ServiceRequisitionSetup::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ServiceRequisition->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ServiceRequisition->effective_timestamp = 0;
        }
        $ServiceRequisition->status = $UpdateStatus;
        $ServiceRequisition->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'services',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceRequisitionLog = ServiceRequisitionSetup::where('id', $ID)->first();
        $logIds = $ServiceRequisitionLog->logid ? explode(',', $ServiceRequisitionLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceRequisitionLog->logid = implode(',', $logIds);
        $ServiceRequisitionLog->save();

        $ServiceRequisition->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateServiceRequisitionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_requisition_setup)[1];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ServiceRequisitions = ServiceRequisitionSetup::select('service_requisition_setup.*',
        'organization.organization as orgName','services.name as serviceName')
        ->join('organization', 'organization.id', '=', 'service_requisition_setup.org_id')
        ->join('services', 'services.id', '=', 'service_requisition_setup.service_id')
        ->where('service_requisition_setup.id', $id)
        ->first();

        $serviceName = ucwords($ServiceRequisitions->serviceName);
        $orgName = ucwords($ServiceRequisitions->orgName);
        $ServiceRequestStatus = $ServiceRequisitions->mandatory == 0 ? "No" : "Yes";
        $ServiceRequestStatusId = $ServiceRequisitions->mandatory;

        $Description = ucwords($ServiceRequisitions->description);
        $effective_timestamp = $ServiceRequisitions->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'serviceId' => $ServiceRequisitions->service_id,
            'serviceName' => $serviceName,
            'orgID' => $ServiceRequisitions->org_id,
            'orgName' => $orgName,
            'ServiceRequestStatus' => $ServiceRequestStatus,
            'ServiceRequestStatusId' => $ServiceRequestStatusId,
            'Description' => $Description,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateServiceRequisition(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_requisition_setup)[1];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ServiceRequisition = ServiceRequisitionSetup::findOrFail($id);
        $orgID = $request->input('u_srorg');
        if (isset($orgID)) {
            $ServiceRequisition->org_id = $orgID;
        }
        $ServiceRequisition->service_id = $request->input('usr_service');
        $ServiceRequisition->mandatory = $request->input('usr_status');
        $ServiceRequisition->description = strtolower($request->input('usr_description'));
        $effective_date = $request->input('usr_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ServiceRequisition->effective_timestamp = $effective_date;
        $ServiceRequisition->last_updated = $this->currentDatetime;
        $ServiceRequisition->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceRequisition->save();

        if (empty($ServiceRequisition->id)) {
            return response()->json(['error' => 'Service Requisition Details Update Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'services',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceRequisitionLog = ServiceRequisitionSetup::where('id', $ServiceRequisition->id)->first();
        $logIds = $ServiceRequisitionLog->logid ? explode(',', $ServiceRequisitionLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceRequisitionLog->logid = implode(',', $logIds);
        $ServiceRequisitionLog->save();
        return response()->json(['success' => 'Service Requisition details updated successfully']);
    }
}
