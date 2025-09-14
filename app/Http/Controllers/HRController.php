<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\GenderRequest;
use App\Http\Requests\EmpStatusRequest;
use App\Http\Requests\EmpWorkingStatusRequest;
use App\Http\Requests\EmpQualificationLevelRequest;
use App\Http\Requests\EmpQualificationRequest;
use App\Http\Requests\EmpCadreRequest;
use App\Http\Requests\EmpPositionRequest;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\EmpSalaryRequest;
use App\Http\Requests\EmpMedicalLicenseRequest;
use App\Http\Requests\EmployeeCCRequest;
use App\Http\Requests\EmpServiceAllocationRequest;
use App\Http\Requests\EmployeeLocationAllocationRequest;
use App\Http\Requests\EmployeeDocumentRequest;
use App\Http\Requests\PrefixSetupRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;
use App\Models\EmployeeGender;
use App\Models\PrefixSetup;
use App\Models\EmployeeStatus;
use App\Models\EmployeeWorkingStatus;
use App\Models\EmployeeQualificationLevel;
use App\Models\EmployeeCadre;
use App\Models\EmployeePosition;
use App\Models\Organization;
use App\Models\Province;
use App\Models\Employee;
use App\Models\Division;
use App\Models\District;
use App\Models\Site;
use App\Models\CostCenter;
use App\Models\EmployeeSalary;
use App\Models\EmployeeQualification;
use App\Models\EmployeeMedicalLicense;
use App\Models\EmployeeCC;
use App\Models\Service;
use App\Models\Users;
use App\Models\EmployeeServiceAllocation;
use App\Models\FinancialPayrollAddition;
use App\Models\FinancialPayrollDeduction;
use App\Models\EmployeeLocationAllocation;
use App\Models\EmployeeDocuments;
use App\Mail\EmployeeRegistration;
use App\Mail\EmpEmailUpdate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class HRController extends Controller
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

    public function GetSelectedGender(Request $request)
    {
        $genderID = $request->input('genderID');

        $Gender = EmployeeGender::whereNotIn('id', [$genderID])
                     ->where('status', 1)
                     ->get();

        return response()->json($Gender);
    }

    public function EmployeeGender()
    {
        $colName = 'gender_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.gender', compact('user'));
    }

    public function AddGender(GenderRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->gender_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $GenderName = trim($request->input('gender_name'));
        $Edt = $request->input('eg_edt');
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

        $GenderExists = EmployeeGender::where('name', $GenderName)
        ->exists();
        if ($GenderExists) {
            return response()->json(['info' => 'Gender already exists.']);
        }
        else
        {
            $Gender = new EmployeeGender();
            $Gender->name = $GenderName;
            $Gender->status = $status;
            $Gender->user_id = $sessionId;
            $Gender->last_updated = $last_updated;
            $Gender->timestamp = $timestamp;
            $Gender->effective_timestamp = $Edt;
            $Gender->save();

            if (empty($Gender->id)) {
                return response()->json(['error' => 'Failed to create Gender.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "'{$GenderName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Gender->logid = $logs->id;
            $Gender->save();
            return response()->json(['success' => 'Gender created successfully']);
        }

    }

    public function GetEmployeeGenderData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->gender_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeGenders = EmployeeGender::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($EmployeeGenders)
        return DataTables::eloquent($EmployeeGenders)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeeGender) {
                return $EmployeeGender->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeeGender) {
                $session = auth()->user();
                $sessionName = $session->name;
                $EmployeeGenderName = $EmployeeGender->name;
                $idStr = str_pad($EmployeeGender->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($EmployeeGender->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeeGender->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeeGender->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeeGender->user_id);

                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'GDR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $EmployeeGenderName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($EmployeeGender) {
                    $EmployeeGenderId = $EmployeeGender->id;
                    $logId = $EmployeeGender->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->gender_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-gender" data-gender-id="'.$EmployeeGenderId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeeGender->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($EmployeeGender) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->gender_setup)[3];
                return $updateStatus == 1 ? ($EmployeeGender->status ? '<span class="label label-success gender_status cursor-pointer" data-id="'.$EmployeeGender->id.'" data-status="'.$EmployeeGender->status.'">Active</span>' : '<span class="label label-danger gender_status cursor-pointer" data-id="'.$EmployeeGender->id.'" data-status="'.$EmployeeGender->status.'">Inactive</span>') : ($EmployeeGender->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateEmployeeGenderStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->gender_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $GenderID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Gender = EmployeeGender::find($GenderID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Gender->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Gender->effective_timestamp = 0;

        }
        $Gender->status = $UpdateStatus;
        $Gender->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $GenderLog = EmployeeGender::where('id', $GenderID)->first();
        $logIds = $GenderLog->logid ? explode(',', $GenderLog->logid) : [];
        $logIds[] = $logs->id;
        $GenderLog->logid = implode(',', $logIds);
        $GenderLog->save();

        $Gender->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateGenderModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->gender_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Gender = EmployeeGender::find($id);
        $GenderName = ucwords($Gender->name);
        $effective_timestamp = $Gender->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $GenderName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateGender(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->gender_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Gender = EmployeeGender::findOrFail($id);

        $Gender->name = $request->input('u_eg');
        $effective_date = $request->input('u_eg_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $Gender->effective_timestamp = $effective_date;
        $Gender->last_updated = $this->currentDatetime;
        $Gender->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Gender->save();

        if (empty($Gender->id)) {
            return response()->json(['error' => 'Failed to update Gender. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $GenderLog = EmployeeGender::where('id', $Gender->id)->first();
        $logIds = $GenderLog->logid ? explode(',', $GenderLog->logid) : [];
        $logIds[] = $logs->id;
        $GenderLog->logid = implode(',', $logIds);
        $GenderLog->save();
        return response()->json(['success' => 'Gender updated successfully']);
    }

    public function PrefixSetup()
    {
        $colName = 'prefix_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.prefix_setup', compact('user'));
    }

    public function AddPrefix(PrefixSetupRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->prefix_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $PrefixName = trim($request->input('prefix_name'));
        $Edt = $request->input('prefix_edt');
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

        $PrefixExists = PrefixSetup::where('name', $PrefixName)
        ->exists();
        if ($PrefixExists) {
            return response()->json(['info' => 'Prefix already exists.']);
        }
        else
        {
            $Prefix = new PrefixSetup();
            $Prefix->name = $PrefixName;
            $Prefix->status = $status;
            $Prefix->user_id = $sessionId;
            $Prefix->last_updated = $last_updated;
            $Prefix->timestamp = $timestamp;
            $Prefix->effective_timestamp = $Edt;
            $Prefix->save();

            if (empty($Prefix->id)) {
                return response()->json(['error' => 'Failed to create Prefix.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "'{$PrefixName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Prefix->logid = $logs->id;
            $Prefix->save();
            return response()->json(['success' => 'Prefix created successfully']);
        }

    }

    public function GetPrefixData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->prefix_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Prefixes = PrefixSetup::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($EmployeeGenders)
        return DataTables::eloquent($Prefixes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Prefix) {
                return $Prefix->id;  // Raw ID value
            })
            ->editColumn('id', function ($Prefix) {
                $session = auth()->user();
                $sessionName = $session->name;
                $PrefixName = ucfirst($Prefix->name);
                $effectiveDate = Carbon::createFromTimestamp($Prefix->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Prefix->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Prefix->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($Prefix->user_id);

                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                return $PrefixName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($Prefix) {
                    $PrefixId = $Prefix->id;
                    $logId = $Prefix->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->prefix_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-prefix" data-prefix-id="'.$PrefixId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $Prefix->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($Prefix) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->prefix_setup)[3];
                return $updateStatus == 1 ? ($Prefix->status ? '<span class="label label-success prefix_status cursor-pointer" data-id="'.$Prefix->id.'" data-status="'.$Prefix->status.'">Active</span>' : '<span class="label label-danger prefix_status cursor-pointer" data-id="'.$Prefix->id.'" data-status="'.$Prefix->status.'">Inactive</span>') : ($Prefix->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdatePrefixStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->prefix_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $PrefixID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Prefix = PrefixSetup::find($PrefixID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Prefix->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Prefix->effective_timestamp = 0;

        }
        $Prefix->status = $UpdateStatus;
        $Prefix->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PrefixLog = PrefixSetup::where('id', $PrefixID)->first();
        $logIds = $PrefixLog->logid ? explode(',', $PrefixLog->logid) : [];
        $logIds[] = $logs->id;
        $PrefixLog->logid = implode(',', $logIds);
        $PrefixLog->save();

        $Prefix->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdatePrefixModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->prefix_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Prefix = PrefixSetup::find($id);
        $PrefixName = ucwords($Prefix->name);
        $effective_timestamp = $Prefix->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $PrefixName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdatePrefix(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->prefix_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Prefix = PrefixSetup::findOrFail($id);

        $Prefix->name = $request->input('u_prefix');
        $effective_date = $request->input('u_effective_timestamp');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $Prefix->effective_timestamp = $effective_date;
        $Prefix->last_updated = $this->currentDatetime;
        $Prefix->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Prefix->save();

        if (empty($Prefix->id)) {
            return response()->json(['error' => 'Failed to update Prefix. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PrefixLog = PrefixSetup::where('id', $Prefix->id)->first();
        $logIds = $PrefixLog->logid ? explode(',', $PrefixLog->logid) : [];
        $logIds[] = $logs->id;
        $PrefixLog->logid = implode(',', $logIds);
        $PrefixLog->save();
        return response()->json(['success' => 'Prefix updated successfully']);
    }

    // public function AddPrefix(PrefixSetupRequest $request)
    // {
    //     $rights = $this->rights;
    //     $add = explode(',', $rights->prefix_setup)[0];
    //     if($add == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }

    //     $prefix_name = trim($request->input('prefix_name'));
    //     $effective_date = $request->input('effective_timestamp');
    //     $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
    //     $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
    //     $EffectDateTime->subMinute(1);

    //     if ($EffectDateTime->isPast()) {
    //         $status = 1; // Active
    //     } else {
    //         $status = 0; // Inactive
    //     }

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $last_updated = $this->currentDatetime;
    //     $timestamp = $this->currentDatetime;

    //     // Create log entry
    //     $logs = Logs::create([
    //         'module' => 'hr',
    //         'content' => "Prefix has been inserted by '{$sessionName}'",
    //         'event' => 'insert',
    //         'timestamp' => $timestamp,
    //     ]);

    //     $prefix = PrefixSetup::create([
    //         'name' => $prefix_name,
    //         'user_id' => $sessionId,
    //         'logid' => $logs->id,
    //         'status' => $status,
    //         'effective_timestamp' => $effective_date,
    //         'timestamp' => $timestamp,
    //         'last_updated' => $last_updated
    //     ]);

    //     if (empty($prefix->id)) {
    //         return response()->json(['error' => 'Failed to add prefix. Please try again']);
    //     }

    //     return response()->json(['success' => 'Prefix added successfully']);
    // }

    // public function GetPrefixData(Request $request)
    // {
    //     $rights = $this->rights;
    //     $view = explode(',', $rights->prefix_setup)[1];
    //     if($view == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }

    //     $data = PrefixSetup::select('*');

    //     return DataTables::of($data)
    //         ->addIndexColumn()
    //         ->addColumn('action', function($row) use ($rights){
    //             $btn = '';
    //             $edit = explode(',', $rights->prefix_setup)[2];
    //             $updateStatus = explode(',', $rights->prefix_setup)[3];

    //             if($edit == 1) {
    //                 $btn .= '<button type="button" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit-prefix"><i class="fas fa-edit"></i></button> ';
    //             }

    //             if($updateStatus == 1) {
    //                 if($row->status == 1) {
    //                     $btn .= '<button type="button" data-id="'.$row->id.'" data-status="0" class="btn btn-danger btn-sm update-status"><i class="fas fa-times"></i></button>';
    //                 } else {
    //                     $btn .= '<button type="button" data-id="'.$row->id.'" data-status="1" class="btn btn-success btn-sm update-status"><i class="fas fa-check"></i></button>';
    //                 }
    //             }

    //             return $btn;
    //         })
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }

    // public function UpdatePrefixStatus(Request $request)
    // {
    //     $rights = $this->rights;
    //     $updateStatus = explode(',', $rights->prefix_setup)[3];
    //     if($updateStatus == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }

    //     $id = $request->input('id');
    //     $status = $request->input('status');

    //     $prefix = PrefixSetup::find($id);
    //     if(!$prefix) {
    //         return response()->json(['error' => 'Prefix not found']);
    //     }

    //     $session = auth()->user();
    //     $sessionName = $session->name;

    //     $prefix->status = $status;
    //     $prefix->last_updated = $this->currentDatetime;

    //     if($prefix->save()) {
    //         $logs = Logs::create([
    //             'module' => 'hr',
    //             'content' => "Status has been updated by '{$sessionName}'",
    //             'event' => 'update',
    //             'timestamp' => $this->currentDatetime,
    //         ]);

    //         $logIds = $prefix->logid ? explode(',', $prefix->logid) : [];
    //         $logIds[] = $logs->id;
    //         $prefix->logid = implode(',', $logIds);
    //         $prefix->save();

    //         return response()->json(['success' => 'Status updated successfully']);
    //     }

    //     return response()->json(['error' => 'Failed to update status']);
    // }

    // public function UpdatePrefixModal($id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->prefix_setup)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }

    //     $prefix = PrefixSetup::find($id);
    //     $prefixName = ucwords($prefix->name);
    //     $effective_timestamp = $prefix->effective_timestamp;
    //     $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
    //     $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

    //     $data = [
    //         'id' => $id,
    //         'name' => $prefixName,
    //         'effective_timestamp' => $effective_timestamp,
    //     ];

    //     return response()->json($data);
    // }

    // public function UpdatePrefix(Request $request, $id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->prefix_setup)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }

    //     $prefix = PrefixSetup::findOrFail($id);

    //     $prefix->name = $request->input('u_prefix');
    //     $effective_date = $request->input('u_effective_timestamp');
    //     $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
    //     $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
    //     $EffectDateTime->subMinute(1);

    //     if ($EffectDateTime->isPast()) {
    //         $status = 1; // Active
    //     } else {
    //         $status = 0; // Inactive
    //     }

    //     $prefix->effective_timestamp = $effective_date;
    //     $prefix->status = $status;
    //     $prefix->last_updated = $this->currentDatetime;

    //     if($prefix->save()) {
    //         $session = auth()->user();
    //         $sessionName = $session->name;

    //         $logs = Logs::create([
    //             'module' => 'hr',
    //             'content' => "Data has been updated by '{$sessionName}'",
    //             'event' => 'update',
    //             'timestamp' => $this->currentDatetime,
    //         ]);

    //         $logIds = $prefix->logid ? explode(',', $prefix->logid) : [];
    //         $logIds[] = $logs->id;
    //         $prefix->logid = implode(',', $logIds);
    //         $prefix->save();

    //         return response()->json(['success' => 'Prefix updated successfully']);
    //     }

    //     return response()->json(['error' => 'Failed to update prefix']);
    // }

    public function GetSelectedEmpStatus(Request $request)
    {
        $empstatusID = $request->input('empstatusid');
        $empStatus = EmployeeStatus::whereNotIn('id', [$empstatusID])
                     ->where('status', 1)
                     ->get();

        return response()->json($empStatus);
    }

    public function EmployeeStatus()
    {
        $colName = 'employee_status_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.emp-status', compact('user'));
    }

    public function AddEmployeeStatus(EmpStatusRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_status_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $empStatus = trim($request->input('empStatus_name'));
        $Edt = $request->input('es_edt');
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

        $EmpStatusExists = EmployeeStatus::where('name', $empStatus)
        ->exists();
        if ($EmpStatusExists) {
            return response()->json(['info' => 'Employee Status already exists.']);
        }
        else
        {
            $EmpStatus = new EmployeeStatus();
            $EmpStatus->name = $empStatus;
            $EmpStatus->status = $status;
            $EmpStatus->user_id = $sessionId;
            $EmpStatus->last_updated = $last_updated;
            $EmpStatus->timestamp = $timestamp;
            $EmpStatus->effective_timestamp = $Edt;
            $EmpStatus->save();

            if (empty($EmpStatus->id)) {
                return response()->json(['error' => 'Failed to create Employee Status.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "'{$empStatus}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $EmpStatus->logid = $logs->id;
            $EmpStatus->save();
            return response()->json(['success' => 'Employee Status created successfully']);
        }

    }

    public function GetEmployeeStatusData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_status_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeStatuses = EmployeeStatus::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($EmployeeStatuses)
        return DataTables::eloquent($EmployeeStatuses)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('timestamp', 'like', "%{$search}%")
                            ->orWhere('last_updated', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeeStatus) {
                return $EmployeeStatus->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeeStatus) {
                $session = auth()->user();
                $sessionName = $session->name;
                $EmployeeStatusName = $EmployeeStatus->name;
                $idStr = str_pad($EmployeeStatus->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($EmployeeStatus->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeeStatus->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeeStatus->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeeStatus->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'ESS';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $EmployeeStatusName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($EmployeeStatus) {
                    $EmployeeStatusId = $EmployeeStatus->id;
                    $logId = $EmployeeStatus->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->employee_status_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-empStatus" data-empStatus-id="'.$EmployeeStatusId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeeStatus->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($EmployeeStatus) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->employee_status_setup)[3];
                return $updateStatus == 1 ? ($EmployeeStatus->status ? '<span class="label label-success emp_status cursor-pointer" data-id="'.$EmployeeStatus->id.'" data-status="'.$EmployeeStatus->status.'">Active</span>' : '<span class="label label-danger emp_status cursor-pointer" data-id="'.$EmployeeStatus->id.'" data-status="'.$EmployeeStatus->status.'">Inactive</span>') : ($EmployeeStatus->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateEmployeeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->employee_status_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $empStatusID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $EmpStatus = EmployeeStatus::find($empStatusID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $EmpStatus->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $EmpStatus->effective_timestamp = 0;

        }
        $EmpStatus->status = $UpdateStatus;
        $EmpStatus->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmpStatusLog = EmployeeStatus::where('id', $empStatusID)->first();
        $logIds = $EmpStatusLog->logid ? explode(',', $EmpStatusLog->logid) : [];
        $logIds[] = $logs->id;
        $EmpStatusLog->logid = implode(',', $logIds);
        $EmpStatusLog->save();

        $EmpStatus->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateEmployeeStatusModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_status_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $empStatus = EmployeeStatus::find($id);
        $EmpStatusName = ucwords($empStatus->name);
        $effective_timestamp = $empStatus->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $EmpStatusName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateEmployeeStatusData(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_status_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $empStatus = EmployeeStatus::findOrFail($id);

        $empStatus->name = $request->input('u_es');
        $effective_date = $request->input('u_es_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $empStatus->effective_timestamp = $effective_date;
        $empStatus->last_updated = $this->currentDatetime;
        $empStatus->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $empStatus->save();

        if (empty($empStatus->id)) {
            return response()->json(['error' => 'Failed to update Employee Status. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $empStatusLog = EmployeeStatus::where('id', $empStatus->id)->first();
        $logIds = $empStatusLog->logid ? explode(',', $empStatusLog->logid) : [];
        $logIds[] = $logs->id;
        $empStatusLog->logid = implode(',', $logIds);
        $empStatusLog->save();
        return response()->json(['success' => 'Employee Status updated successfully']);
    }

    public function GetSelectedEmpWorkingStatus(Request $request)
    {
        $workingstatusID = $request->input('workingstatusid');
        $empWorkingStatus = EmployeeWorkingStatus::whereNotIn('id', [$workingstatusID])
                     ->where('status', 1)
                     ->get();

        return response()->json($empWorkingStatus);
    }

    public function EmployeeWorkingStatus()
    {
        $colName = 'employee_working_status_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.emp-working-status', compact('user'));
    }

    public function AddEmployeeWorkingStatus(EmpWorkingStatusRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_working_status_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $WorkingStatus = trim($request->input('workingStatus'));
        $jobContinue = $request->input('jobcontinue');
        if ($jobContinue == 'on') {
            $jobContinue = 1;
        }
        else {
            $jobContinue = 0;
        }
        $Edt = $request->input('ews_edt');
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

        $EmpWorkinStatusExists = EmployeeWorkingStatus::where('name', $WorkingStatus)
        ->exists();
        if ($EmpWorkinStatusExists) {
            return response()->json(['info' => 'Employee Working Status already exists.']);
        }
        else
        {
            $EmpWorkingStatus = new EmployeeWorkingStatus();
            $EmpWorkingStatus->name = $WorkingStatus;
            $EmpWorkingStatus->job_continue = $jobContinue;
            $EmpWorkingStatus->status = $status;
            $EmpWorkingStatus->user_id = $sessionId;
            $EmpWorkingStatus->last_updated = $last_updated;
            $EmpWorkingStatus->timestamp = $timestamp;
            $EmpWorkingStatus->effective_timestamp = $Edt;
            $EmpWorkingStatus->save();

            if (empty($EmpWorkingStatus->id)) {
                return response()->json(['error' => 'Failed to create Employee Working Status.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "'{$WorkingStatus}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $EmpWorkingStatus->logid = $logs->id;
            $EmpWorkingStatus->save();
            return response()->json(['success' => 'Employee Working Status created successfully']);
        }

    }

    public function GetEmployeeWorkingStatusData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_working_status_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeWorkingStatuses = EmployeeWorkingStatus::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($EmployeeWorkingStatuses)
        return DataTables::eloquent($EmployeeWorkingStatuses)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('timestamp', 'like', "%{$search}%")
                            ->orWhere('last_updated', 'like', "%{$search}%")
                            ->orWhereRaw('CASE WHEN job_continue = 1 THEN "Yes" ELSE "No" END LIKE ?', ["%{$search}%"]);
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeeWorkingStatus) {
                return $EmployeeWorkingStatus->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeeWorkingStatus) {
                $session = auth()->user();
                $sessionName = $session->name;
                $EmployeeWorkingStatusName = $EmployeeWorkingStatus->name;
                $idStr = str_pad($EmployeeWorkingStatus->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($EmployeeWorkingStatus->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeeWorkingStatus->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeeWorkingStatus->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeeWorkingStatus->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'EWS';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $EmployeeWorkingStatusName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('jobContinuation', function ($EmployeeWorkingStatus) {
                    return $EmployeeWorkingStatus->job_continue ? '<code style="font-size:15px">Yes</code>' : '<code style="font-size:15px">No</code>';
            })
            ->addColumn('action', function ($EmployeeWorkingStatus) {
                    $EmployeeWorkingStatusId = $EmployeeWorkingStatus->id;
                    $logId = $EmployeeWorkingStatus->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->employee_working_status_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-workingStatus" data-workingStatus-id="'.$EmployeeWorkingStatusId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeeWorkingStatus->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($EmployeeWorkingStatus) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->employee_working_status_setup)[3];
                return $updateStatus == 1 ? ($EmployeeWorkingStatus->status ? '<span class="label label-success working_status cursor-pointer" data-id="'.$EmployeeWorkingStatus->id.'" data-status="'.$EmployeeWorkingStatus->status.'">Active</span>' : '<span class="label label-danger working_status cursor-pointer" data-id="'.$EmployeeWorkingStatus->id.'" data-status="'.$EmployeeWorkingStatus->status.'">Inactive</span>') : ($EmployeeWorkingStatus->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','jobContinuation',
            'id'])
            ->make(true);
    }

    public function UpdateEmployeeWorkingStatus(Request $request)
    {
        $rights = $this->rights;
        $updateStatus = explode(',', $rights->employee_working_status_setup)[3];
        if($updateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $workingStatusID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $WorkingStatus = EmployeeWorkingStatus::find($workingStatusID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $WorkingStatus->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $WorkingStatus->effective_timestamp = 0;

        }
        $WorkingStatus->status = $UpdateStatus;
        $WorkingStatus->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $WorkingStatusLog = EmployeeWorkingStatus::where('id', $workingStatusID)->first();
        $logIds = $WorkingStatusLog->logid ? explode(',', $WorkingStatusLog->logid) : [];
        $logIds[] = $logs->id;
        $WorkingStatusLog->logid = implode(',', $logIds);
        $WorkingStatusLog->save();

        $WorkingStatus->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateEmployeeWorkingStatusModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_working_status_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $workingStatus = EmployeeWorkingStatus::find($id);
        $WorkingStatusName = ucwords($workingStatus->name);
        $effective_timestamp = $workingStatus->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $WorkingStatusName,
            'jobContinue' => $workingStatus->job_continue,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateEmployeeWorkingStatusData(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_working_status_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $workingStatus = EmployeeWorkingStatus::findOrFail($id);

        $workingStatus->name = $request->input('u_ews');
        $jobContinue = $request->input('u_jobcontinue');
        if ($jobContinue  == 'on') {
            $workingStatus->job_continue  = 1;
        }
        else {
            $workingStatus->job_continue  = 0;
        }
        $effective_date = $request->input('u_ews_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $workingStatus->effective_timestamp = $effective_date;
        $workingStatus->last_updated = $this->currentDatetime;
        $workingStatus->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $workingStatus->save();

        if (empty($workingStatus->id)) {
            return response()->json(['error' => 'Failed to update Employee Working Status. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $WorkingStatusLog = EmployeeWorkingStatus::where('id', $workingStatus->id)->first();
        $logIds = $WorkingStatusLog->logid ? explode(',', $WorkingStatusLog->logid) : [];
        $logIds[] = $logs->id;
        $WorkingStatusLog->logid = implode(',', $logIds);
        $WorkingStatusLog->save();
        return response()->json(['success' => 'Employee Working Status updated successfully']);
    }

    public function GetSelectedQualification(Request $request)
    {
        $qualificationID = $request->input('qualificationid');
        $qualificationID = explode(',', $qualificationID);
        $Qualification = EmployeeQualificationLevel::whereNotIn('id', $qualificationID)
                     ->where('status', 1)
                     ->get();
        return response()->json($Qualification);
    }

    public function EmployeeQualificationLevel()
    {
        $colName = 'qualification_level_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.emp-qualification-level', compact('user'));
    }

    public function AddEmployeeQualificationLevel(EmpQualificationLevelRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->qualification_level_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $empQualification = trim($request->input('empQualification'));
        $Edt = $request->input('eql_edt');
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

        $empQualificationExists = EmployeeQualificationLevel::where('name', $empQualification)
        ->exists();
        if ($empQualificationExists) {
            return response()->json(['info' => 'Employee Qualification Level already exists.']);
        }
        else
        {
            $EmpQualification = new EmployeeQualificationLevel();
            $EmpQualification->name = $empQualification;
            $EmpQualification->status = $status;
            $EmpQualification->user_id = $sessionId;
            $EmpQualification->last_updated = $last_updated;
            $EmpQualification->timestamp = $timestamp;
            $EmpQualification->effective_timestamp = $Edt;
            $EmpQualification->save();

            if (empty($EmpQualification->id)) {
                return response()->json(['error' => 'Failed to create Employee Qualification Level.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "'{$empQualification}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $EmpQualification->logid = $logs->id;
            $EmpQualification->save();
            return response()->json(['success' => 'Employee Qualification Level created successfully']);
        }

    }

    public function GetEmployeeQualificationLevelData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->qualification_level_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeQualificationLevels = EmployeeQualificationLevel::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($EmployeeQualificationLevels)
        return DataTables::eloquent($EmployeeQualificationLevels)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('timestamp', 'like', "%{$search}%")
                            ->orWhere('last_updated', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeeQualificationLevel) {
                return $EmployeeQualificationLevel->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeeQualificationLevel) {
                $session = auth()->user();
                $sessionName = $session->name;
                $EmployeeQualificationLevelName = $EmployeeQualificationLevel->name;
                $idStr = str_pad($EmployeeQualificationLevel->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($EmployeeQualificationLevel->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeeQualificationLevel->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeeQualificationLevel->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeeQualificationLevel->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'EQL';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $EmployeeQualificationLevelName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;


                return $Code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($EmployeeQualificationLevel) {
                    $EmployeeQualificationLevelId = $EmployeeQualificationLevel->id;
                    $logId = $EmployeeQualificationLevel->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->qualification_level_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-empQualification" data-empQualification-id="'.$EmployeeQualificationLevelId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeeQualificationLevel->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($EmployeeQualificationLevel) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->qualification_level_setup)[3];
                return $updateStatus == 1 ? ($EmployeeQualificationLevel->status ? '<span class="label label-success empQualification_status cursor-pointer" data-id="'.$EmployeeQualificationLevel->id.'" data-status="'.$EmployeeQualificationLevel->status.'">Active</span>' : '<span class="label label-danger empQualification_status cursor-pointer" data-id="'.$EmployeeQualificationLevel->id.'" data-status="'.$EmployeeQualificationLevel->status.'">Inactive</span>') : ($EmployeeQualificationLevel->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateEmployeeQualificationLevelStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->qualification_level_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $empQualificationID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $EmployeeQualification = EmployeeQualificationLevel::find($empQualificationID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $EmployeeQualification->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $EmployeeQualification->effective_timestamp = 0;

        }
        $EmployeeQualification->status = $UpdateStatus;
        $EmployeeQualification->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeQualificationLog = EmployeeQualificationLevel::where('id', $empQualificationID)->first();
        $logIds = $EmployeeQualificationLog->logid ? explode(',', $EmployeeQualificationLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeQualificationLog->logid = implode(',', $logIds);
        $EmployeeQualificationLog->save();

        $EmployeeQualification->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateEmployeeQualificationLevelModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->qualification_level_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeQualification = EmployeeQualificationLevel::find($id);
        $EmployeeQualificationName = ucwords($EmployeeQualification->name);
        $effective_timestamp = $EmployeeQualification->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $EmployeeQualificationName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateEmployeeQualificationLevelData(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->qualification_level_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeQualification = EmployeeQualificationLevel::findOrFail($id);

        $EmployeeQualification->name = $request->input('u_eql');
        $effective_date = $request->input('u_eql_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $EmployeeQualification->effective_timestamp = $effective_date;
        $EmployeeQualification->last_updated = $this->currentDatetime;
        $EmployeeQualification->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $EmployeeQualification->save();

        if (empty($EmployeeQualification->id)) {
            return response()->json(['error' => 'Failed to update Employee Qualification Level. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeQualificationLog = EmployeeQualificationLevel::where('id', $EmployeeQualification->id)->first();
        $logIds = $EmployeeQualification->logid ? explode(',', $EmployeeQualification->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeQualificationLog->logid = implode(',', $logIds);
        $EmployeeQualificationLog->save();
        return response()->json(['success' => 'Employee Qualification Level updated successfully']);
    }

    public function EmployeeCadre()
    {
        $colName = 'cadre_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.emp-cadre', compact('user','Organizations'));
    }

    public function AddEmployeeCadre(EmpCadreRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->cadre_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $empCadre = trim($request->input('empCadre'));
        $cadre_org = ($request->input('cadre_org'));
        // $cadre_site = ($request->input('cadre_site'));
        $Edt = $request->input('es_edt');
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

        $empCadreExists = EmployeeCadre::where('name', $empCadre)
        ->Where('org_id', $cadre_org)
        // ->Where('site_id', $cadre_site)
        ->exists();

        if ($empCadreExists) {
            return response()->json(['info' => 'Employee Qualification Level already exists.']);
        }
        else
        {
            $EmpCadre= new EmployeeCadre();
            $EmpCadre->name = $empCadre;
            $EmpCadre->org_id = $cadre_org;
            // $EmpCadre->site_id = $cadre_site;
            $EmpCadre->status = $status;
            $EmpCadre->user_id = $sessionId;
            $EmpCadre->last_updated = $last_updated;
            $EmpCadre->timestamp = $timestamp;
            $EmpCadre->effective_timestamp = $Edt;
            $EmpCadre->save();

            if (empty($EmpCadre->id)) {
                return response()->json(['error' => 'Failed to create Employee Cadre.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "'{$empCadre}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $EmpCadre->logid = $logs->id;
            $EmpCadre->save();
            return response()->json(['success' => 'Employee Cadre created successfully']);
        }

    }

    public function GetEmployeeCadreData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->cadre_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeCadres = EmployeeCadre::select('emp_cadre.*', 'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'emp_cadre.org_id')
        ->orderBy('emp_cadre.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $EmployeeCadres->where('emp_cadre.org_id', '=', $sessionOrg);
        }
        $EmployeeCadres = $EmployeeCadres;
        // ->get()
        // return DataTables::of($EmployeeCadres)
        return DataTables::eloquent($EmployeeCadres)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('emp_cadre.name', 'like', "%{$search}%")
                            ->orWhere('emp_cadre.id', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('emp_cadre.status', 'like', "%{$search}%")
                            ->orWhere('emp_cadre.effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('emp_cadre.timestamp', 'like', "%{$search}%")
                            ->orWhere('emp_cadre.last_updated', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeeCadre) {
                return $EmployeeCadre->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeeCadre) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $EmployeeCadreName = $EmployeeCadre->name;
                $firstFourLetters = substr(str_replace(' ', '', strtoupper($EmployeeCadreName)), 0, 3);
                $idStr = str_pad($EmployeeCadre->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($EmployeeCadre->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeeCadre->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeeCadre->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeeCadre->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'ECD';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $EmployeeCadreName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgId = $EmployeeCadre->org_id;
                    $orgName = Organization::where('id', $orgId)->value('organization');
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($EmployeeCadre) {
                    $EmployeeCadreId = $EmployeeCadre->id;
                    $logId = $EmployeeCadre->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->cadre_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-empCadre" data-empcadre-id="'.$EmployeeCadreId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeeCadre->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($EmployeeCadre) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->cadre_setup)[3];
                return $updateStatus == 1 ? ($EmployeeCadre->status ? '<span class="label label-success cadre_setup cursor-pointer" data-id="'.$EmployeeCadre->id.'" data-status="'.$EmployeeCadre->status.'">Active</span>' : '<span class="label label-danger cadre_setup cursor-pointer" data-id="'.$EmployeeCadre->id.'" data-status="'.$EmployeeCadre->status.'">Inactive</span>') : ($EmployeeCadre->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateEmployeeCadreStatus(Request $request)
    {
        $rights = $this->rights;
        $updateStatus = explode(',', $rights->cadre_setup)[3];
        if($updateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $empCadreID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $EmployeeCadre = EmployeeCadre::find($empCadreID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $EmployeeCadre->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $EmployeeCadre->effective_timestamp = 0;

        }
        $EmployeeCadre->status = $UpdateStatus;
        $EmployeeCadre->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeCadreLog = EmployeeCadre::where('id', $empCadreID)->first();
        $logIds = $EmployeeCadreLog->logid ? explode(',', $EmployeeCadreLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeCadreLog->logid = implode(',', $logIds);
        $EmployeeCadreLog->save();

        $EmployeeCadre->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateEmployeeCadreModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cadre_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeCadre = EmployeeCadre::select('emp_cadre.*', 'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'emp_cadre.org_id')
        ->where('emp_cadre.id', $id)
        ->first();

        $EmployeeCadreName = ucwords($EmployeeCadre->name);
        $OrgName = ucwords($EmployeeCadre->orgName);
        $SiteName = ucwords($EmployeeCadre->siteName);
        $effective_timestamp = $EmployeeCadre->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $EmployeeCadreName,
            'orgId' => $EmployeeCadre->org_id,
            'orgName' => $OrgName,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateEmployeeCadreData(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->cadre_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeCadre = EmployeeCadre::findOrFail($id);
        $orgID = $request->input('u_cadreOrg');
        if (isset($orgID)) {
            $EmployeeCadre->org_id = $orgID;
        }
        $EmployeeCadre->name = $request->input('u_ec');
        $effective_date = $request->input('u_ec_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $EmployeeCadre->effective_timestamp = $effective_date;
        $EmployeeCadre->last_updated = $this->currentDatetime;
        $EmployeeCadre->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $EmployeeCadre->save();

        if (empty($EmployeeCadre->id)) {
            return response()->json(['error' => 'Failed to update Employee Cadre. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeCadreLog = EmployeeCadre::where('id', $EmployeeCadre->id)->first();
        $logIds = $EmployeeCadre->logid ? explode(',', $EmployeeCadre->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeCadreLog->logid = implode(',', $logIds);
        $EmployeeCadreLog->save();
        return response()->json(['success' => 'Employee Cadre updated successfully']);
    }

    public function GetSelectedPosition(Request $request)
    {
        $orgId = $request->input('orgId');
        $Position = EmployeePosition::where('org_id', $orgId)
                     ->where('status', 1)
                     ->get();

        return response()->json($Position);
    }

    public function EmployeePosition()
    {
        $colName = 'position_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $empCadres = EmployeeCadre::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.emp-position', compact('user','empCadres','Organizations'));
    }

    public function AddEmployeePosition(EmpPositionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->position_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $empPosition = trim($request->input('empPosition'));
        $Edt = $request->input('ep_edt');
        $EmpCadre = $request->input('emp-cadre');
        $positionOrg = $request->input('positionOrg');
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

        $empPositionExists = EmployeePosition::where('name', $empPosition)
        ->Where('org_id', $positionOrg)
        ->exists();
        if ($empPositionExists) {
            return response()->json(['info' => 'Employee Position already exists.']);
        }
        else
        {
            $EmpPosition= new EmployeePosition();
            $EmpPosition->name = $empPosition;
            $EmpPosition->org_id = $positionOrg;
            $EmpPosition->cadre_id = $EmpCadre;
            $EmpPosition->status = $status;
            $EmpPosition->user_id = $sessionId;
            $EmpPosition->last_updated = $last_updated;
            $EmpPosition->timestamp = $timestamp;
            $EmpPosition->effective_timestamp = $Edt;
            $EmpPosition->save();

            if (empty($EmpPosition->id)) {
                return response()->json(['error' => 'Failed to create Employee Position.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "'{$empPosition}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $EmpPosition->logid = $logs->id;
            $EmpPosition->save();
            return response()->json(['success' => 'Employee Position created successfully']);
        }

    }

    public function GetEmployeePositionData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->position_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeePositions = EmployeePosition::select('emp_position.*', 'emp_cadre.name as empcadre',
        'organization.organization as orgName',)
        ->join('emp_cadre', 'emp_cadre.id', '=', 'emp_position.cadre_id')
        ->join('organization', 'organization.id', '=', 'emp_position.org_id')
        ->orderBy('emp_position.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $EmployeePositions->where('emp_position.org_id', '=', $sessionOrg);
        }
        if ($request->has('cadre') && $request->cadre != '' && $request->cadre != 'Loading...') {
            $EmployeePositions->where('emp_position.cadre_id', $request->cadre);
        }

        $EmployeePositions = $EmployeePositions;
        // ->get()

        // return DataTables::of($EmployeePositions)
        return DataTables::eloquent($EmployeePositions)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('emp_position.name', 'like', "%{$search}%")
                            ->orWhere('emp_position.id', 'like', "%{$search}%")
                            ->orWhere('emp_position.status', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('emp_cadre.name', 'like', "%{$search}%")
                            ->orWhere('emp_position.effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('emp_position.timestamp', 'like', "%{$search}%")
                            ->orWhere('emp_position.last_updated', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeePosition) {
                return $EmployeePosition->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeePosition) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $EmployeePositionName = ucwords($EmployeePosition->name);
                // $firstFourLetters = substr(str_replace(' ', '', strtoupper($EmployeePositionName)), 0, 3);
                // $words = explode(' ', $EmployeePositionName);
                // $acronym = '';

                // foreach ($words as $word) {
                //     $acronym .= strtoupper($word[0]);
                // }
                $idStr = str_pad($EmployeePosition->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($EmployeePosition->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeePosition->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeePosition->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeePosition->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'EPO';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $EmployeePositionName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgId = $EmployeePosition->org_id;
                    $orgName = Organization::where('id', $orgId)->value('organization');
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($EmployeePosition) {
                    $EmployeePositionId = $EmployeePosition->id;
                    $logId = $EmployeePosition->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->position_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-empPosition" data-empposition-id="'.$EmployeePositionId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeePosition->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($EmployeePosition) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->position_setup)[3];
                return $updateStatus == 1 ? ($EmployeePosition->status ? '<span class="label label-success empPosition_status cursor-pointer" data-id="'.$EmployeePosition->id.'" data-status="'.$EmployeePosition->status.'">Active</span>' : '<span class="label label-danger empPosition_status cursor-pointer" data-id="'.$EmployeePosition->id.'" data-status="'.$EmployeePosition->status.'">Inactive</span>') : ($EmployeePosition->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateEmployeePositionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->position_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $empPositionID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $EmployeePosition = EmployeePosition::find($empPositionID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $EmployeePosition->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $EmployeePosition->effective_timestamp = 0;

        }
        $EmployeePosition->status = $UpdateStatus;
        $EmployeePosition->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeePositionLog = EmployeePosition::where('id', $empPositionID)->first();
        $logIds = $EmployeePositionLog->logid ? explode(',', $EmployeePositionLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeePositionLog->logid = implode(',', $logIds);
        $EmployeePositionLog->save();

        $EmployeePosition->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateEmployeePositionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->position_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeePosition = EmployeePosition::select('emp_position.*', 'emp_cadre.name as empcadre',
        'organization.organization as orgName',)
        ->join('emp_cadre', 'emp_cadre.id', '=', 'emp_position.cadre_id')
        ->join('organization', 'organization.id', '=', 'emp_position.org_id')
        ->where('emp_position.id', $id)
        ->first();

        $EmployeePositionName = ucwords($EmployeePosition->name);
        $cadre = ucwords($EmployeePosition->empcadre);
        $orgName = ucwords($EmployeePosition->orgName);
        $siteName = ucwords($EmployeePosition->siteName);
        $cadreid = $EmployeePosition->cadre_id;
        $effective_timestamp = $EmployeePosition->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $EmployeePositionName,
            'orgName' => $orgName,
            'orgId' => $EmployeePosition->org_id,
            'cadre' => $cadre,
            'cadreid' => $cadreid,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateEmployeePositionData(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->position_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeePosition = EmployeePosition::findOrFail($id);
        $orgID = $request->input('u_positionOrg');
        if (isset($orgID)) {
            $EmployeePosition->org_id = $orgID;
        }
        $EmployeePosition->name = $request->input('u_ep');
        $effective_date = $request->input('u_ep_edt');
        $EmployeePosition->cadre_id = $request->input('u_cadre');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $EmployeePosition->effective_timestamp = $effective_date;
        $EmployeePosition->last_updated = $this->currentDatetime;
        $EmployeePosition->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $EmployeePosition->save();

        if (empty($EmployeePosition->id)) {
            return response()->json(['error' => 'Failed to update Employee Position. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeePositionLog = EmployeePosition::where('id', $EmployeePosition->id)->first();
        $logIds = $EmployeePosition->logid ? explode(',', $EmployeePosition->logid) : [];
        $logIds[] = $logs->id;
        $EmployeePositionLog->logid = implode(',', $logIds);
        $EmployeePositionLog->save();
        return response()->json(['success' => 'Employee Position updated successfully']);
    }

    public function GetSelectedCadre(Request $request)
    {
        $orgId = $request->input('orgId');
        $EmployeeCadre = EmployeeCadre::where('org_id', $orgId)
                     ->where('status', 1)
                     ->get();

        return response()->json($EmployeeCadre);
    }

    public function GetSelectedEmployee(Request $request)
    {
        $EmployeeID = $request->input('employeeid');
        $Employee = Employee::whereNotIn('id', [$EmployeeID])
                     ->where('status', 1)
                     ->get();

        return response()->json($Employee);
    }

    public function GetOrganizationEmployees(Request $request)
    {
        $orgId = $request->input('orgid');
        $Employee = Employee::where('org_id', $orgId)
                     ->where('status', 1)
                     ->whereNotIn('id', Users::select('emp_id'))
                     ->get();

        if ($Employee->isEmpty()) {
            return response()->json(null);
        }

        // $filteredEmployees = $Employee->filter(function ($employee) {
        //     return !Users::where('emp_id', $employee->id)->exists();
        // });

        // if ($filteredEmployees->isEmpty()) {
        //     return response()->json(null);
        // }
        return response()->json($Employee);
    }

    public function GetEmployeeDetails(Request $request)
    {
        $empId = $request->input('empId');
        // $Employee = Employee::where('id', $empId)
        //              ->get();

        $Employee = Employee::select('employee.*','costcenter.name as ccName',
        'emp_position.name as positionName','organization.organization as orgName',
        'org_site.name as siteName')
        ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
        ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
        ->join('organization', 'organization.id', '=', 'employee.org_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->where('employee.id', $empId)
        ->get();

        return response()->json($Employee);
    }

    public function GetQualificationEmployee(Request $request)
    {
        $siteId = $request->input('siteId');
        $Employees = Employee::leftJoin('emp_qualification', 'employee.id', '=', 'emp_qualification.emp_id')
                    ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
                     ->whereNull('emp_qualification.emp_id')
                     ->where('employee.status', 1)
                     ->where('employee.site_id', $siteId)
                     ->get(['employee.*','prefix.name as prefix']);
                     if($Employees->count() > 0) {
                        return response()->json($Employees);
                    }
    }

    public function ViewEmployee()
    {
        $colName = 'employee_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Genders = EmployeeGender::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        $Cadres = EmployeeCadre::where('status', 1)->get();
        $Positions = EmployeePosition::where('status', 1)->get();
        $QualificationLevels = EmployeeQualificationLevel::where('status', 1)->get();
        $EmpStatuses = EmployeeStatus::where('status', 1)->get();
        $EmpWorkingStatuses = EmployeeWorkingStatus::where('status', 1)->where('job_continue', 1)->get();
        $Provinces = Province::where('status', 1)->get();
        $Employees = Employee::where('status', 1)->get();
        $Prefixes = PrefixSetup::where('status', 1)->get();

        return view('dashboard.employee', compact('user','Prefixes','Genders','Organizations','Cadres','Positions','QualificationLevels','EmpStatuses','EmpWorkingStatuses','Provinces','Employees'));
    }

    public function AddEmployee(EmployeeRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Name = trim($request->input('emp_name'));
        $Guardian = trim($request->input('emp_guardian_name'));
        $GuardianRelation = trim($request->input('emp_guardian_relation'));
        $NextOfKin = trim($request->input('emp_next_of_kin'));
        $NextOfKinRelation = trim($request->input('emp_nextofkin_relation'));
        $oldCode = trim($request->input('emp_oldcode'));
        $Gender = ($request->input('emp_gender'));
        $Language = strtolower($request->input('emp_language'));
        $Religion = ($request->input('emp_religion'));
        $maritalStatus = ($request->input('emp_marital_status'));
        $Gender = ($request->input('emp_gender'));
        $Org = ($request->input('emp_org'));
        $Site = ($request->input('emp_site'));
        $CostCenter = ($request->input('emp_cc'));
        $Prefix = ($request->input('emp_prefix'));
        $Cadre = ($request->input('emp_cadre'));
        $Position = ($request->input('emp_position'));
        $weekHrs = $request->input('emp_weekHrs');
        // $startTime = $request->input('start_time');
        // $endTime = $request->input('end_time');
        // $weekHrs = $startTime.' To '.$endTime;
        $ReportTo = ($request->input('emp_reportto'));
        $QualificationLevel = ($request->input('emp_qual_lvl'));
        $EmployeeStatus = ($request->input('emp_status'));
        $WorkingStatus = ($request->input('emp_working_status'));
        $Province = ($request->input('emp_province'));
        $Division = ($request->input('emp_division'));
        $District = ($request->input('emp_district'));
        $mobileNo = ($request->input('emp_cell'));
        $AdditionalMobileNo = ($request->input('emp_additionalcell'));
        $CNIC = trim($request->input('emp_cnic'));
        $CNICExpiry = $request->input('cnic_expiry');
        $CNICExpiry = Carbon::createFromFormat('Y-m-d', $CNICExpiry)->timestamp;

        $Landline = ($request->input('emp_landline'));
        $Email = trim($request->input('emp_email'));
        $Address = trim($request->input('emp_address'));
        $MailingAddress = trim($request->input('mailing_address'));
        $Image = $request->file('emp_img');

        if($ReportTo != '0')
        {
            $Manager = Employee::find($ReportTo)->name;
        }
        else
        {
            $Manager = '';
        }

        $empDOJ = $request->input('emp_doj');
        $empDOJ = Carbon::createFromFormat('Y-m-d', $empDOJ)->timestamp;

        $empDOB = $request->input('emp_dob');
        $empDOB = Carbon::createFromFormat('Y-m-d', $empDOB)->timestamp;

        $empEdt = $request->input('emp_edt');
        $empEdt = Carbon::createFromFormat('l d F Y - h:i A', $empEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($empEdt)->setTimezone('Asia/Karachi');

        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
            $emailStatus = 'Acive';
        } else {
            $status = 0;
            $emailStatus = 'Inactive';

        }

        $ImgFileName = $Image->getClientOriginalName();

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $EmployeeExists = Employee::when(!empty($Email), function ($query) use ($Email) {
            return $query->where('email', $Email);
        })
        ->orWhere('cnic', $CNIC)
        ->exists();

        if ($EmployeeExists) {
            return response()->json(['info' => 'Employee already exists.']);
        }
        if(!empty($Email)){
            $userExists = Users::where('email', $Email)->exists();
            if ($userExists) {
                return response()->json(['info' => 'This email is already associated with a registered user.']);
            }
        }


        $Employee = new Employee();
        $Employee->name = $Name;
        $Employee->prefix_id = $Prefix;
        $Employee->guardian_name = $Guardian;
        $Employee->guardian_relation = $GuardianRelation;
        $Employee->next_of_kin = $NextOfKin;
        $Employee->next_of_kin_relation = $NextOfKinRelation;
        $Employee->old_code = $oldCode;
        $Employee->gender_id = $Gender;
        $Employee->language = $Language;
        $Employee->religion = $Religion;
        $Employee->marital_status = $maritalStatus;
        $Employee->dob = $empDOB;
        $Employee->org_id = $Org;
        $Employee->site_id = $Site;
        $Employee->cc_id = $CostCenter;
        $Employee->cadre_id = $Cadre;
        $Employee->position_id = $Position;
        $Employee->week_hrs = $weekHrs;
        $Employee->report_to = $ReportTo;
        $Employee->q_level_id = $QualificationLevel;
        $Employee->emp_status_id = $EmployeeStatus;
        $Employee->work_status_id = $WorkingStatus;
        $Employee->joinig_date = $empDOJ;
        $Employee->address = $Address;
        $Employee->mailing_address = $MailingAddress;
        $Employee->province_id = $Province;
        $Employee->division_id = $Division;
        $Employee->district_id = $District;
        $Employee->cnic = $CNIC;
        $Employee->cnic_expiry = $CNICExpiry;
        $Employee->mobile_no = $mobileNo;
        $Employee->additional_mobile_no = $AdditionalMobileNo;
        $Employee->landline = $Landline;
        $Employee->email = $Email;
        $Employee->image = $ImgFileName;
        $Employee->status = $status;
        $Employee->user_id = $sessionId;
        $Employee->last_updated = $last_updated;
        $Employee->timestamp = $timestamp;
        $Employee->effective_timestamp = $empEdt;
        $provinceName = Province::find($Province)->name;
        $divisionName = Division::find($Division)->name;
        $districtName = District::find($District)->name;
        $genderName = EmployeeGender::find($Gender)->name;
        $orgName = Organization::find($Org)->organization;
        $siteName = Site::find($Site)->name;
        $ccName = CostCenter::find($CostCenter)->name;
        $cadreName = EmployeeCadre::find($Cadre)->name;
        $positionName = EmployeePosition::find($Position)->name;

        $QualLevelName = EmployeeQualificationLevel::find($QualificationLevel)->name;
        $employeeStatusName = EmployeeStatus::find($EmployeeStatus)->name;
        $empWorkingStatusName = EmployeeWorkingStatus::find($WorkingStatus)->name;

        if(!empty($Email)){
            try {
                $emailTimestamp = Carbon::createFromTimestamp($timestamp);
                $emailTimestamp = $emailTimestamp->format('l d F Y - h:i A');
                $emailEdt = $request->input('emp_edt');
                $emailDOJ = $request->input('emp_doj');
                $emailDOB = $request->input('emp_dob');

                Mail::to($Email)->send(new EmployeeRegistration($Name, $oldCode, $genderName,
                $orgName, $siteName, $ccName, $cadreName, $positionName, $weekHrs,
                $Manager, $QualLevelName, $employeeStatusName, $empWorkingStatusName,
                $provinceName, $divisionName, $districtName, $mobileNo,
                $CNIC, $Landline, $Email, $Address,
                $emailDOJ, $emailDOB, $emailEdt,
                $emailStatus, $emailTimestamp));

                $Employee->save();
                $ImgFileName = $Employee->id . '_' . $ImgFileName;
                $Image->move(public_path('assets/emp'), $ImgFileName);
            }
            catch (TransportExceptionInterface $ex)
            {
                return response()->json(['info' => 'There is an issue with email. Please try again!.']);
            }
        }
        else{
            $Employee->save();
            $ImgFileName = $Employee->id . '_' . $ImgFileName;
            $Image->move(public_path('assets/emp'), $ImgFileName);
        }
        if (empty($Employee->id)) {
            return response()->json(['error' => 'Failed to create Employee.']);
        }

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "'{$Name}' has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $Employee->logid = $logs->id;
        $Employee->save();
        return response()->json(['success' => 'Employee created successfully']);

    }

    public function GetEmployeeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionOrg = $session->org_id;


        $Employees = Employee::select('employee.*',
        'organization.organization as org_name',
        'organization.code as orgCode',
        'emp_working_status.name as workingStatus',
        'gender.name as genderName',
        'org_site.name as siteName',
        'emp_cadre.name as cadreName',
        'emp_position.name as positionName',
        'emp_status.name as empStatusName',
        'province.name as provinceName',
        'division.name as divisionName',
        'district.name as districtName',
        'prefix.name as Prefix',
        'costcenter.name as cc_name')
        ->leftJoin('organization', 'organization.id', '=', 'employee.org_id')
        ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
        ->join('gender', 'gender.id', '=', 'employee.gender_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->join('emp_cadre', 'emp_cadre.id', '=', 'employee.cadre_id')
        ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
        ->join('emp_status', 'emp_status.id', '=', 'employee.emp_status_id')
        ->join('province', 'province.id', '=', 'employee.province_id')
        ->join('division', 'division.id', '=', 'employee.division_id')
        ->join('district', 'district.id', '=', 'employee.district_id')
        ->join('emp_working_status', 'emp_working_status.id', '=', 'employee.work_status_id')
        ->join('prefix', 'prefix.id', '=', 'employee.prefix_id');

        if($sessionOrg != '0')
        {
            $Employees->where('employee.org_id', '=', $sessionOrg);
        }

        $Employees = $Employees;
        // ->get()
        // return DataTables::of($Employees)
        return DataTables::eloquent($Employees)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('employee.name', 'like', "%{$search}%")
                            ->orWhere('employee.email', 'like', "%{$search}%")
                            ->orWhere('employee.mobile_no', 'like', "%{$search}%")
                            ->orWhere('employee.id', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('emp_cadre.name', 'like', "%{$search}%")
                            ->orWhere('emp_position.name', 'like', "%{$search}%")
                            ->orWhere('emp_status.name', 'like', "%{$search}%")
                            ->orWhere('gender.name', 'like', "%{$search}%")
                            ->orWhere('province.name', 'like', "%{$search}%")
                            ->orWhere('division.name', 'like', "%{$search}%")
                            ->orWhere('district.name', 'like', "%{$search}%")
                            ->orWhere('costcenter.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Employee) {
                return $Employee->id;
            })
            ->editColumn('id', function ($Employee) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $empName = $Employee->Prefix.' '.ucwords($Employee->name);
                $genderName = ucwords($Employee->genderName);
                $effectiveDate = Carbon::createFromTimestamp($Employee->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Employee->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Employee->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($Employee->user_id);

                $createdInfo = "<b>Created By:</b> " . ucwords($createdByName) . " <br> <b>Effective Date&amp;Time:</b> "
                    . $effectiveDate . " <br><b>RecordedAt:</b> " . $timestamp ." <br><b>LastUpdated:</b>
                    " . $lastUpdated;

                $idStr = str_pad($Employee->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = $Employee->orgCode;
                $Code = $ModuleCode.'-'.$idStr;

                $completionPercentage = $this->EmployeeProfileCompletion($Employee->id);

                $cnicExpiryDate = Carbon::createFromTimestamp($Employee->cnic_expiry);
                $isExpired = $cnicExpiryDate->isPast() ? '<span class="label label-danger blinking">CNIC Expired</span><hr class="mt-1 mb-2">' : null;

                return
                    $isExpired
                    . '<b>Emp Code</b>: '.$Code.'<hr class="m-1">'
                    .$empName.'<hr class="m-1">'
                    .$genderName.'<br>'
                    . ' <hr class="mt-1 mb-2">
                        <div class="card mb-3">
                            <div class="card-body p-2">
                                <div class="row p-t-1 p-b-1">
                                    <div class="col-5 p-r-0">
                                        <h2 class="font-light">'.$completionPercentage.'%</h2>
                                        <h6 class="text-muted">Profile Completion</h6></div>
                                        <div class="col-6 text-right align-self-center" style="position: sticky;">
                                            <div data-label="'.$completionPercentage.'%" class="css-bar m-b-0 css-bar-danger css-bar-'.$completionPercentage.'"><i class="mdi mdi-account-circle"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    '
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span> <hr class="mt-1 mb-2">';
            })
            ->editColumn('placement', function ($Employee) {
                $siteName = ucwords($Employee->siteName);
                $cadreName = ucwords($Employee->cadreName);
                $positionName = ucwords($Employee->positionName);
                $HeadCountCC = ucwords($Employee->cc_name);

                return '<b>Site </b>: '.$siteName.'<hr class="m-1">'
                    .'<b>Head Count CC </b>: '.$HeadCountCC.'<hr class="m-1">'
                    .'<b>Cadre </b>: '.$cadreName.'<hr class="m-1">'
                    .'<b>Position </b>: '.$positionName.'<hr class="m-1">
                    ';
            })
            ->editColumn('workStatus', function ($Employee) {
                $empDOJ = $Employee->joinig_date;
                $empDOJ = Carbon::createFromTimestamp($empDOJ);
                $empDOJ = $empDOJ->format('d-M-y');
                $joiningDate = '<b>DOJ:</b> <code class="p-0">'.$empDOJ.'</code>';
                $workingStatus = ucwords($Employee->workingStatus);
                $empStatus = ucwords($Employee->empStatusName);

                return $joiningDate.'<hr class="m-1">'
                    .'<b>Working Status</b>: '.$workingStatus.'<hr class="m-1">'
                    .'<b>Employee Status </b>: '.$empStatus.'<hr class="m-1">'
                    ;
            })
            ->editColumn('contactDetails', function ($Employee) {
                $email = ($Employee->email);
                if(empty($email))
                {
                    $email = 'N/A';
                }

                $mobileNo = ($Employee->mobile_no);
                $Address = ucwords($Employee->address);
                $MailingAddress = ucwords($Employee->mailing_address);
                $provinceName = ucwords($Employee->provinceName);
                $divisionName = ucwords($Employee->divisionName);
                $districtName = ucwords($Employee->districtName);

                return '<b>Cell #</b>: '.$mobileNo.'<hr class="m-1">'
                    .'<b>Email</b>: '.$email.'<hr class="m-1">'
                    .'<b>Present Address</b>: '.$Address.'<hr class="m-1">'
                    .'<b>Mailing Address</b>: '.$MailingAddress.'<hr class="m-1">'
                    .'<b>Province</b>: '.$provinceName.'<hr class="m-1">'
                    .'<b>Division</b>: '.$divisionName.'<hr class="m-1">'
                    .'<b>District</b>: '.$districtName.'<hr class="m-1">'
                    ;
            })
            ->addColumn('action', function ($Employee) {
                    $EmployeeId = $Employee->id;
                    $logId = $Employee->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->employee_setup)[2];
                    $view = explode(',', $Rights->employee_setup)[1];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 mt-1 edit-employee" data-employee-id="'.$EmployeeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal mt-1" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    if ($view == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-secondary mt-1 employee-detail" data-emp-id="'.$EmployeeId.'">'
                        . '<i class="fa fa-plus-circle"></i> View All Details'
                        . '</button>';

                    }


                    return $Employee->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($Employee) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->employee_setup)[3];
                return $updateStatus == 1 ? ($Employee->status ? '<span class="label label-success employee_status cursor-pointer" data-id="'.$Employee->id.'" data-status="'.$Employee->status.'">Active</span>' : '<span class="label label-danger employee_status cursor-pointer" data-id="'.$Employee->id.'" data-status="'.$Employee->status.'">Inactive</span>') : ($Employee->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id','placement','workStatus','contactDetails'])
            ->make(true);
    }


    public function EmployeeProfileCompletion($employeeId)
    {
        $tables = [
            'emp_service_allocation',
            'emp_cc',
            'emp_salary',
            'emp_medical_license',
            'emp_qualification'
        ];

        $count = 0;

        foreach ($tables as $table) {
            $exists = DB::table($table)->where('emp_id', $employeeId)->exists();
            if ($exists) {
                $count++;
            }
        }

        return ($count / count($tables)) * 100; // Calculate percentage
    }


    public function UpdateEmployeeDetailStatus(Request $request)
    {
        $rights = $this->rights;
        $updaetStatus = explode(',', $rights->employee_setup)[3];
        if($updaetStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Employee = Employee::find($EmployeeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Employee->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Employee->effective_timestamp = 0;

        }
        $Employee->status = $UpdateStatus;
        $Employee->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeLog = Employee::where('id', $EmployeeID)->first();
        $logIds = $EmployeeLog->logid ? explode(',', $EmployeeLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeLog->logid = implode(',', $logIds);
        $EmployeeLog->save();

        $Employee->save();
        return response()->json(['success' => true, 200]);
    }

    public function EmployeeDetailModal($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Employee = Employee::select('employee.*',
        'gender.name as gender_name',
        'organization.organization as org_name',
        'org_site.name as site_name',
        'emp_cadre.name as cadre_name',
        'emp_position.name as position_name',
        'emp_qualification_level.name as qualification_name',
        'emp_status.name as empstatus_name',
        'emp_working_status.name as empworking_name',
        'costcenter.name as cc_name',
        'province.name as province_name',
        'division.name as division_name',
        'prefix.name as Prefix',
        'district.name as district_name')
        ->join('gender', 'gender.id', '=', 'employee.gender_id')
        ->join('organization', 'organization.id', '=', 'employee.org_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
        ->join('emp_cadre', 'emp_cadre.id', '=', 'employee.cadre_id')
        ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
        ->join('emp_qualification_level', 'emp_qualification_level.id', '=', 'employee.q_level_id')
        ->join('emp_status', 'emp_status.id', '=', 'employee.emp_status_id')
        ->join('emp_working_status', 'emp_working_status.id', '=', 'employee.work_status_id')
        ->join('province', 'province.id', '=', 'employee.province_id')
        ->join('division', 'division.id', '=', 'employee.division_id')
        ->join('district', 'district.id', '=', 'employee.district_id')
        ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
        ->find($id);

        $empName = $Employee->name;
        $GuardianName = $Employee->guardian_name;
        $GuardianRelation = $Employee->guardian_relation;
        $NextOfKin = $Employee->next_of_kin;
        $NextOfKinRelation = $Employee->next_of_kin_relation;
        $OldCode = $Employee->old_code;
        $Gender = $Employee->gender_name;
        $Language = $Employee->language;
        $Religion = $Employee->religion;
        $MaritalStatus = $Employee->marital_status;
        $Organization = $Employee->org_name;
        $Site = $Employee->site_name;
        $Cadre = $Employee->cadre_name;
        $Position = $Employee->position_name;
        $Qualification = $Employee->qualification_name;
        $EmpStatus = $Employee->empstatus_name;
        $WorkingStatus = $Employee->empworking_name;
        $CostCenter = $Employee->cc_name;
        $Province = $Employee->province_name;
        $Division = $Employee->division_name;
        $District = $Employee->district_name;
        $DateOfBirth = $Employee->dob;
        $DateOfBirth = date("j-M-Y", $DateOfBirth);
        $WeekHrs = $Employee->week_hrs;
        $Manager = $Employee->report_to;
        $JoiningDate = $Employee->joinig_date;
        $JoiningDate = date("j-M-Y", $JoiningDate);
        $Address = $Employee->address;
        $MailingAddress = $Employee->mailing_address;
        $CNIC = $Employee->cnic;

        $CNICExpiry = $Employee->cnic_expiry;
        $CNICExpiry = date("j-M-Y", $CNICExpiry);

        $MobileNo = $Employee->mobile_no;
        $AdditionalMobileNo = $Employee->additional_mobile_no;
        $Landline = $Employee->landline;
        $Email = $Employee->email;

        $Image = $Employee->image;
        $Image = $id.'_'.$Image;
        $ImgPath = 'assets/emp/' . $Image;
        $Image = asset($ImgPath);

        if($Email == '')
        {
            $Email = 'N/A';
        }

        if($OldCode == '')
        {
            $OldCode = 'N/A';
        }
        if($Landline === null)
        {
            $contactNo = $MobileNo;
        }
        else {
            $contactNo = $MobileNo.' / '.$Landline;
        }
        if(empty($AdditionalMobileNo))
        {
            $AdditionalMobileNo = 'N/A';
        }
        else {
            $AdditionalMobileNo = $AdditionalMobileNo;
        }
        if($Manager == 0)
        {
            $Manager = 'N/A';
        }
        else {
            $Manager = Employee::find($Manager)->name;
        }


        $data = [
            'empName' => ucwords($empName),
            'Prefix' => ucwords($Employee->Prefix),
            'GuardianName' => ucwords($GuardianName),
            'GuardianRelation' => ucwords($GuardianRelation),
            'NextOfKin' => ucwords($NextOfKin),
            'NextOfKinRelation' => ucwords($NextOfKinRelation),
            'OldCode' => ucwords($OldCode),
            'Gender' => ucwords($Gender),
            'Language' => ucwords($Language),
            'Religion' => ucwords($Religion),
            'MaritalStatus' => ucwords($MaritalStatus),
            'Organization' => ucwords($Organization),
            'Site' => ucwords($Site),
            'Cadre' => ucwords($Cadre),
            'Position' => ucwords($Position),
            'Qualification' => ucwords($Qualification),
            'EmpStatus' => ucwords($EmpStatus),
            'WorkingStatus' => ucwords($WorkingStatus),
            'CostCenter' => ucwords($CostCenter),
            'Province' => ucwords($Province),
            'Division' => ucwords($Division),
            'District' => ucwords($District),
            'DateOfBirth' => $DateOfBirth,
            'WeekHrs' => ucwords($WeekHrs.' Hours a week'),
            'Manager' => ucwords($Manager),
            'JoiningDate' => $JoiningDate,
            'Address' => ucwords($Address),
            'MailingAddress' => ucwords($MailingAddress),
            'cnic' => $CNIC,
            'cnicExpiry' => $CNICExpiry,
            'contact' => $contactNo,
            'Additionalcontact' => $AdditionalMobileNo,
            'Email' => $Email,
            'Image' => $Image,
        ];
        return response()->json($data);
    }

    public function UpdateEmployeeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Employee = Employee::select('employee.*',
        'gender.name as gender_name',
        'organization.organization as org_name',
        'org_site.name as site_name',
        'emp_cadre.name as cadre_name',
        'emp_position.name as position_name',
        'emp_qualification_level.name as qualification_name',
        'emp_status.name as empstatus_name',
        'emp_working_status.name as empworking_name',
        'emp_working_status.job_continue as jobContinue',
        'costcenter.name as cc_name',
        'province.name as province_name',
        'division.name as division_name',
        'district.name as district_name')
        ->join('gender', 'gender.id', '=', 'employee.gender_id')
        ->join('organization', 'organization.id', '=', 'employee.org_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
        ->join('emp_cadre', 'emp_cadre.id', '=', 'employee.cadre_id')
        ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
        ->join('emp_qualification_level', 'emp_qualification_level.id', '=', 'employee.q_level_id')
        ->join('emp_status', 'emp_status.id', '=', 'employee.emp_status_id')
        ->join('emp_working_status', 'emp_working_status.id', '=', 'employee.work_status_id')
        ->join('province', 'province.id', '=', 'employee.province_id')
        ->join('division', 'division.id', '=', 'employee.division_id')
        ->join('district', 'district.id', '=', 'employee.district_id')
        ->find($id);

        $empName = $Employee->name;
        $GuardianName = $Employee->guardian_name;
        $GuardianRelation = $Employee->guardian_relation;
        $empNextOfKin = $Employee->next_of_kin;
        $empNextOfKinRelation = $Employee->next_of_kin_relation;
        $OldCode = $Employee->old_code;
        $Gender = $Employee->gender_name;
        $GenderID = $Employee->gender_id;
        $language = $Employee->language;
        $Religion = $Employee->religion;
        $MaritalStatus = $Employee->marital_status;
        $Organization = $Employee->org_name;
        $OrganizationID = $Employee->org_id;
        $Site = $Employee->site_name;
        $SiteID = $Employee->site_id;
        $Cadre = $Employee->cadre_name;
        $CadreID = $Employee->cadre_id;
        $Position = $Employee->position_name;
        $PositionID = $Employee->position_id;
        $Qualification = $Employee->qualification_name;
        $QualificationID = $Employee->q_level_id;
        $EmpStatus = $Employee->empstatus_name;
        $EmpStatusID = $Employee->emp_status_id;
        $WorkingStatus = $Employee->empworking_name;
        $WorkingStatusID = $Employee->work_status_id;
        $CostCenter = $Employee->cc_name;
        $CostCenterID = $Employee->cc_id;
        $Province = $Employee->province_name;
        $ProvinceID = $Employee->province_id;
        $Division = $Employee->division_name;
        $DivisionID = $Employee->division_id;
        $District = $Employee->district_name;
        $DistrictID = $Employee->district_id;
        $WeekHrs = $Employee->week_hrs;
        $Manager = $Employee->report_to;
        $ManagerID = $Employee->report_to;
        $Address = $Employee->address;
        $MailingAddress = $Employee->mailing_address;
        $CNIC = $Employee->cnic;
        $MobileNo = $Employee->mobile_no;
        $AdditionalMobileNo = $Employee->additional_mobile_no;
        $Landline = $Employee->landline;
        $Email = $Employee->email;
        $WeekHrs = $Employee->week_hrs;

        $Image = $Employee->image;
        $Image = $id.'_'.$Image;
        $ImgPath = 'assets/emp/' . $Image;
        $Image = asset($ImgPath);

        $effective_timestamp = $Employee->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $empDOB = $Employee->dob;
        $empDOB = Carbon::createFromTimestamp($empDOB);

        $CNICExpiry = $Employee->cnic_expiry;
        $CNICExpiry = Carbon::createFromTimestamp($CNICExpiry);

        $empDOL = $Employee->leaving_date;
        $empDOL = Carbon::createFromTimestamp($empDOL);

        $empDOJ = $Employee->joinig_date;
        $empDOJ = Carbon::createFromTimestamp($empDOJ);

        if($OldCode == '')
        {
            $OldCode = '';
        }

        if($Manager == 0)
        {
            $Manager = 'N/A';
        }
        else {
            $Manager = Employee::find($Manager)->name;
        }

        $data = [
            'id' => $id,
            'empName' => ucwords($empName),
            'Prefix' => ($Employee->prefix_id),
            'GuardianName' => ucwords($GuardianName),
            'GuardianRelation' => ucwords($GuardianRelation),
            'empNextOfKin' => ucwords($empNextOfKin),
            'empNextOfKinRelation' => ucwords($empNextOfKinRelation),
            'OldCode' => ucwords($OldCode),
            'Gender' => ucwords($Gender),
            'GenderID' => ($GenderID),
            'language' => ucwords($language),
            'Religion' => ucwords($Religion),
            'MaritalStatus' => ucwords($MaritalStatus),
            'Organization' => ucwords($Organization),
            'OrganizationID' => ($OrganizationID),
            'Site' => ucwords($Site),
            'SiteID' => ($SiteID),
            'Cadre' => ucwords($Cadre),
            'CadreID' => ($CadreID),
            'Position' => ucwords($Position),
            'PositionID' => ($PositionID),
            'Qualification' => ucwords($Qualification),
            'QualificationID' => ($QualificationID),
            'EmpStatus' => ucwords($EmpStatus),
            'EmpStatusID' => ($EmpStatusID),
            'WorkingStatus' => ucwords($WorkingStatus),
            'WorkingStatusID' => ($WorkingStatusID),
            'CostCenter' => ucwords($CostCenter),
            'CostCenterID' => ($CostCenterID),
            'Province' => ucwords($Province),
            'ProvinceID' => ($ProvinceID),
            'Division' => ucwords($Division),
            'DivisionID' => ($DivisionID),
            'District' => ucwords($District),
            'DistrictID' => ($DistrictID),
            'WeekHrs' => ucwords($WeekHrs),
            'Manager' => ucwords($Manager),
            'ManagerID' => ucwords($ManagerID),
            'Address' => ucwords($Address),
            'MailingAddress' => ucwords($MailingAddress),
            'jobContinue' => $Employee->jobContinue,
            'cnic' => $CNIC,
            'cnicExpiry' => $CNICExpiry,
            'cell' => $MobileNo,
            'AdditionalCell' => $AdditionalMobileNo,
            'landline' => $Landline,
            'Email' => $Email,
            'Image' => $Image,
            'effective_timestamp' => $effective_timestamp,
            'empDOB' => $empDOB,
            'empDOJ' => $empDOJ,
            'empDOL' => $empDOL,
        ];
        return response()->json($data);
    }

    public function UpdateEmployee(Request $request, $id)
    {
        // dd($request);
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Employee = Employee::findOrFail($id);
        $oldEmail = $Employee->email;


        $EmployeeName = trim($request->input('u_emp_name'));
        $Employee->name = $EmployeeName;
        $Employee->guardian_name = trim($request->input('u_guardian_name'));
        $Employee->prefix_id = trim($request->input('u_emp_prefix'));
        $Employee->guardian_relation = trim($request->input('u_guardian_relation'));
        $Employee->next_of_kin = trim($request->input('u_emp_nextofkin'));
        $Employee->next_of_kin_relation = trim($request->input('u_nextofkin_relation'));
        $Employee->old_code = $request->input('u_emp_code');
        $weekHrs = $request->input('u_emp_weekHrs');
        $Employee->week_hrs = $weekHrs;
        $Employee->gender_id = $request->input('u_emp_gender');
        $Employee->language = $request->input('u_emp_language');
        $Employee->religion = $request->input('u_emp_religion');
        $Employee->marital_status = $request->input('u_emp_marital_status');
        $orgID = $request->input('u_emp_org');
        if (isset($orgID)) {
            $Employee->org_id = $orgID;
        }
        $Employee->site_id = $request->input('u_emp_site');
        $Employee->cc_id = $request->input('u_emp_cc');
        $Employee->cadre_id = $request->input('u_emp_cadre');
        $Employee->position_id = $request->input('u_emp_position');
        $Employee->report_to = $request->input('u_emp_reportto');
        $Employee->q_level_id = $request->input('u_qualification');
        $Employee->emp_status_id = $request->input('u_emp_status');
        $Employee->work_status_id = $request->input('u_working_status');
        $Employee->province_id = $request->input('u_emp_province');
        $Employee->division_id = $request->input('u_emp_division');
        $Employee->district_id = $request->input('u_emp_district');
        $Employee->cnic = $request->input('u_emp_cnic');

        $Employee->mobile_no = trim($request->input('u_emp_cell'));
        $Employee->additional_mobile_no = trim($request->input('u_emp_additional_cell'));
        $Employee->landline = $request->input('u_emp_landline');

        $newEmail = $request->input('u_emp_email');
        $Employee->email = $newEmail;
        $Employee->address = $request->input('u_emp_address');
        $Employee->mailing_address = $request->input('u_emp_mailingaddress');
        $empImg = $request->file('u_empImg');


        if(!empty($newEmail)){
            $EmployeeExists = Employee::where('email', $newEmail)
            ->where('id', '!=', $id)  // Exclude current employee
            ->exists();
            if ($EmployeeExists) {
                return response()->json(['info' => 'Employee already exists.']);
            }

            $userExists = Users::where('email', $newEmail)
            ->where('emp_id', '!=', $id)
            ->exists();
            if ($userExists) {
                return response()->json(['info' => 'This email is already associated with a registered user.']);
            }
        }

        // if (isset($empImg)) {
        //     $oldImagePath = public_path('assets/emp/' . $id . '_' .$Employee->image);
        //     if (File::exists($oldImagePath)) {
        //         File::delete($oldImagePath);
        //     }
        //     $ImgName = $empImg->getClientOriginalName();
        //     $Employee->image = $ImgName;
        //     $ImgName = $id . '_' . $ImgName;
        //     // $orglogo->storeAs('public/assets/organization', $logoFileName);
        //     $empImg->move(public_path('assets/emp'), $ImgName);
        // }


        $empDOB = $request->input('u_emp_dob');
        $empDOB = Carbon::createFromFormat('Y-m-d', $empDOB)->timestamp;
        $Employee->dob = $empDOB;

        $cnicExpiry = $request->input('u_cnic_expiry');
        $cnicExpiry = Carbon::createFromFormat('Y-m-d', $cnicExpiry)->timestamp;
        $Employee->cnic_expiry = $cnicExpiry;

        $effective_date = $request->input('u_emp_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');

        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $empDOL = $request->input('u_emp_dol');

        if(!empty($empDOL)) {
            $empDOL = Carbon::createFromFormat('Y-m-d', $empDOL)->timestamp;
            $Employee->leaving_date = $empDOL;
            $status = 0;
        }
        else {
            $Employee->leaving_date = 0;
        }

        $empDOJ = $request->input('u_emp_doj');
        $empDOJ = Carbon::createFromFormat('Y-m-d', $empDOJ)->timestamp;
        $Employee->joinig_date = $empDOJ;



        $Employee->effective_timestamp = $effective_date;
        $Employee->last_updated = $this->currentDatetime;
        $Employee->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $emailPasswordUpdated = false;
        if($oldEmail != $newEmail)
        {
            $userStatus = Users::where('emp_id', $id)->first();
            if ($userStatus) {
                try {
                    $pwd = Str::random(8);
                    $password = Hash::make($pwd);

                    Mail::to($newEmail)->send(new EmpEmailUpdate($oldEmail, $newEmail, $EmployeeName,$pwd,$sessionName));

                    $userStatus->email  = $newEmail;
                    $userStatus->password = $password;
                    $userStatus->save();
                    DB::table('sessions')->where('user_id', $userStatus->id)->delete();

                    $emailPasswordUpdated = true;


                }
                catch (TransportExceptionInterface $ex)
                {
                    return response()->json(['info' => 'There is an issue with updated email. Please try again!.']);
                }
                // return response()->json(['info' => 'This email is already associated with a registered user.']);
            }

        }

        if (isset($empImg)) {
            $oldImagePath = public_path('assets/emp/' . $id . '_' .$Employee->image);
            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }
            $ImgName = $empImg->getClientOriginalName();
            $Employee->image = $ImgName;
            $ImgName = $id . '_' . $ImgName;
            // $orglogo->storeAs('public/assets/organization', $logoFileName);
            $empImg->move(public_path('assets/emp'), $ImgName);
        }
        $Employee->save();

        // $Employee->save();
        if (empty($Employee->id)) {
            return response()->json(['error' => 'Failed to update Employee. Please try again']);
        }
        // $data = "Data has been updated by '{$sessionName}'";
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeLog = Employee::where('id', $id)->first();
        $logIds = $EmployeeLog->logid ? explode(',', $EmployeeLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeLog->logid = implode(',', $logIds);
        $EmployeeLog->save();

        $successMessage = '<b>Employee details updated successfully.</b>';

        if ($emailPasswordUpdated) {
            $successMessage .= '<br>The email address of this employee has been updated, and a new password has been emailed to the employee for login.';
        }
        return response()->json(['success' => $successMessage]);
    }

    public function GetSalaryEmployee(Request $request)
    {
        $siteId = $request->input('siteId');
        $Employees = Employee::leftJoin('emp_salary', 'employee.id', '=', 'emp_salary.emp_id')
                    ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
                    ->whereNull('emp_salary.emp_id')
                    ->where('employee.status', 1)
                    ->where('employee.site_id', $siteId)
                    ->get(['employee.*','prefix.name as prefix']);
                    if($Employees->count() > 0) {
                        return response()->json($Employees);
                    }
    }

    public function EmployeeSalary()
    {
        $colName = 'employee_salary_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        // $Employees = Employee::where('status', 1)->get();
        // $Employees = Employee::leftJoin('emp_salary', 'employee.id', '=', 'emp_salary.emp_id')
        //              ->whereNull('emp_salary.emp_id')
        //              ->where('employee.status', 1)
        //              ->get(['employee.*']);

        $payrollAdditions = FinancialPayrollAddition::where('status', 1)->orderBy('id')->get();
        $payrollDeductions = FinancialPayrollDeduction::where('status', 1)->orderBy('id')->get();


        return view('dashboard.emp_salary', compact('user', 'payrollAdditions', 'payrollDeductions'));
        // return view('dashboard.emp_salary', compact('user'));
        // return view('dashboard.emp_salary', compact('user','Employees'));
    }

    public function AddEmployeeSalary(EmpSalaryRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_salary_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $payrollAdditions = FinancialPayrollAddition::where('status', 1)->get();
        $payrollDeductions = FinancialPayrollDeduction::where('status', 1)->get();

        // Prepare arrays to store addition and deduction values
        $additions = [];
        $deductions = [];
        $totalAdditions = 0;
        $totalDeductions = 0;

        // Collect addition values from the request
        foreach ($payrollAdditions as $pa) {
            $columnName = strtolower(str_replace(' ', '_', $pa->name));
            if ($request->has($columnName)) {
                $value = floatval($request->input($columnName));
                $totalAdditions += $value;
                $additions[] = encrypt($request->input($columnName));
            }
        }

        foreach ($payrollDeductions as $pd) {
            $columnName = strtolower(str_replace(' ', '_', $pd->name));
            if ($request->has($columnName)) {
                $value = floatval($request->input($columnName));
                $totalDeductions += $value;
                $deductions[] = encrypt($request->input($columnName));
            }
        }

        $empId = ($request->input('emp-id'));
        $empRemarks = trim($request->input('salary_remarks'));
        // $empSalary = encrypt(trim($request->input('empSalary')));

        $Edt = $request->input('salary_edt');
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

        $totalSalary = $totalAdditions - $totalDeductions;

        $empSalaryExists = EmployeeSalary::where('emp_id', $empId)
        ->exists();
        if ($empSalaryExists) {
            return response()->json(["info" => "This employee's salary is already on record."]);
        }
        else
        {
            $EmpSalary= new EmployeeSalary();
            $EmpSalary->emp_id = $empId;
            $EmpSalary->additions = implode(',', $additions);
            $EmpSalary->deductions = implode(',', $deductions);
            $EmpSalary->remarks = $empRemarks;
            $EmpSalary->status = $status;
            $EmpSalary->user_id = $sessionId;
            $EmpSalary->last_updated = $last_updated;
            $EmpSalary->timestamp = $timestamp;
            $EmpSalary->effective_timestamp = $Edt;
            $EmpSalary->save();
            // $employeesalary = decrypt($empSalary);

            if (empty($EmpSalary->id)) {
                return response()->json(['error' => 'Failed to Add Employee Salary! Please Try Again.']);
            }

            $logs = Logs::create([
                'module' => 'salary',
                'content' => "Employee Salary Rs '{$totalSalary}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $EmpSalary->logid = $logs->id;
            $EmpSalary->save();
            return response()->json(['success' => 'Employee Salary added successfully']);
        }

    }

    public function GetEmployeeSalaryData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_salary_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeSalaries = EmployeeSalary::select('emp_salary.*', 'employee.name as empName',
        'organization.organization as orgName','org_site.name as siteName')
        ->join('employee', 'employee.id', '=', 'emp_salary.emp_id')
        ->leftjoin('organization', 'organization.id', '=', 'employee.org_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->orderBy('employee.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $EmployeeSalaries->where('employee.org_id', '=', $sessionOrg);
        }
        $EmployeeSalaries = $EmployeeSalaries;
        // ->get()
        // return DataTables::of($EmployeeSalaries)
        return DataTables::eloquent($EmployeeSalaries)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('employee.name', 'like', "%{$search}%")
                            // ->orWhere('emp_salary.id', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%");
                            // ->orWhere('emp_salary.additions', 'like', "%{$search}%")
                            // ->orWhere('emp_salary.deductions', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeeSalary) {
                return $EmployeeSalary->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeeSalary) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $EmployeeName = ucwords($EmployeeSalary->empName);

                $createdByName = getUserNameById($EmployeeSalary->user_id);
                $effectiveDate = Carbon::createFromTimestamp($EmployeeSalary->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeeSalary->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeeSalary->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeeSalary->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;
            $sessionOrg = $session->org_id;
            $orgName = '';
            if($sessionOrg == 0)
            {
                $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($EmployeeSalary->orgName);
            }
            $additions = $EmployeeSalary->additions;
            $additionIds = explode(',', $additions);
            $totalAdditions = 0;
            foreach ($additionIds as $additionId) {
                $value = decrypt(trim($additionId));
                $totalAdditions += $value;
            }

            $deductions = $EmployeeSalary->deductions;
            $deductionIds = explode(',', $deductions);
            $totalDeductions = 0;
            foreach ($deductionIds as $deductionId) {
                $value = decrypt(trim($deductionId));
                $totalDeductions += $value;
            }

            // Total salary calculation
            $totalSalary = $totalAdditions - $totalDeductions;
            $totalSalary = 'Rs ' . number_format($totalSalary, 2);
                return $EmployeeName
                    . '<hr class="mt-1 mb-2"><span style="background-color:#4b4d4e;padding:6px;color:white;"> <b>Salary</b>: '
                    .$totalSalary.'</span>'
                    .$orgName
                    . '<hr class="mt-1 mb-2"> <b>Site</b>: '
                    . ucwords($EmployeeSalary->siteName)
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            // ->addColumn('empSalary', function ($EmployeeSalary) {
            //     $EmployeeSalary = decrypt($EmployeeSalary->salary);
            //     $EmployeeSalary = 'Rs '.number_format($EmployeeSalary,2);
            //     return $EmployeeSalary ;  // Raw ID value
            // })
            ->addColumn('action', function ($EmployeeSalary) {
                    $EmployeeSalaryId = $EmployeeSalary->id;
                    $logId = $EmployeeSalary->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->employee_salary_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-empSalary" data-empsalary-id="'.$EmployeeSalaryId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeeSalary->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->addColumn('additions', function ($EmployeeSalary) {
                $payrollAdditions = FinancialPayrollAddition::where('status', 1)->orderBy('id')->get();

                $additions = $EmployeeSalary->additions;
                $additionIds = explode(',', $additions);
                $formattedAdditions = [];

                // Start the table structure
                $formattedAdditions[] = '<table>';
                // $formattedAdditions[] = '<thead><tr><th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Name</th><th  style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Amount</th></tr></thead>';
                $formattedAdditions[] = '<tbody>';

                foreach ($payrollAdditions as $key => $addition) {
                    if (isset($additionIds[$key])) {
                        $value = decrypt($additionIds[$key]);
                        $formattedAdditions[] = '<tr>';
                        $formattedAdditions[] = '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"><b>' . $addition->name . '</b></td>';
                        $formattedAdditions[] = '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Rs ' . number_format($value, 2) . '</td>';
                        $formattedAdditions[] = '</tr>';
                    }
                }

                // Close the table structure
                $formattedAdditions[] = '</tbody>';
                $formattedAdditions[] = '</table>';

                return implode('', $formattedAdditions);
            })
            ->addColumn('deductions', function ($EmployeeSalary) {
                $payrollDeductions = FinancialPayrollDeduction::where('status', 1)->orderBy('id')->get();

                $deductions = $EmployeeSalary->deductions;
                $deductionIds = explode(',', $deductions);
                $formattedDeductions = [];

                $formattedDeductions[] = '<table>';
                $formattedDeductions[] = '<tbody>';
                foreach ($payrollDeductions as $key => $deduction) {
                    if (isset($deductionIds[$key])) {
                        $value = decrypt($deductionIds[$key]);
                        $formattedDeductions[] = '<tr>';
                        $formattedDeductions[] = '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"><b>' . $deduction->name . '</b></td>';
                        $formattedDeductions[] = '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;width:120px;">Rs ' . number_format($value, 2) . '</td>';
                        $formattedDeductions[] = '</tr>';
                    }
                }
                $formattedDeductions[] = '</tbody>';
                $formattedDeductions[] = '</table>';

                return implode('', $formattedDeductions);
            })
            ->editColumn('status', function ($EmployeeSalary) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->employee_salary_setup)[3];
                return $updateStatus == 1 ? ($EmployeeSalary->status ? '<span class="label label-success empsalary_status cursor-pointer" data-id="'.$EmployeeSalary->id.'" data-status="'.$EmployeeSalary->status.'">Active</span>' : '<span class="label label-danger empsalary_status cursor-pointer" data-id="'.$EmployeeSalary->id.'" data-status="'.$EmployeeSalary->status.'">Inactive</span>') : ($EmployeeSalary->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status', 'deductions', 'additions',
            'id'])
            ->make(true);
    }

    public function UpdateEmployeeSalaryStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->employee_salary_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $salaryId = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $EmployeeSalary = EmployeeSalary::find($salaryId);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $EmployeeSalary->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $EmployeeSalary->effective_timestamp = 0;

        }
        $EmployeeSalary->status = $UpdateStatus;
        $EmployeeSalary->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeSalaryLog = EmployeeSalary::where('id', $salaryId)->first();
        $logIds = $EmployeeSalaryLog->logid ? explode(',', $EmployeeSalaryLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeSalaryLog->logid = implode(',', $logIds);
        $EmployeeSalaryLog->save();

        $EmployeeSalary->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateEmployeeSalaryModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_salary_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeSalary = EmployeeSalary::find($id);
        $effective_timestamp = $EmployeeSalary->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');
        foreach (explode(',', $EmployeeSalary->additions) as $encryptedValue) {
            try {
                $additions[] = decrypt($encryptedValue);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $additions[] = 0;
            }
        }
        foreach (explode(',', $EmployeeSalary->deductions) as $encryptedValue) {
            try {
                $deductions[] = decrypt($encryptedValue);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $deductions[] = 0;
            }
        }
        $payrollAdditions = FinancialPayrollAddition::pluck('name')->toArray();
        $payrollDeductions = FinancialPayrollDeduction::pluck('name')->toArray();

        if (count($payrollAdditions) !== count($additions)) {
            $additions = array_pad($additions, count($payrollAdditions), 0);
        }
        if (count($payrollDeductions) !== count($deductions)) {
            $deductions = array_pad($deductions, count($payrollDeductions), 0);
        }

        $data = [
            'id' => $id,
            'effective_timestamp' => $effective_timestamp,
            'additions' => array_combine(
                array_map(function ($name) {
                    return 'u_' . strtolower(str_replace(' ', '_', $name));
                }, $payrollAdditions),
                $additions
            ),
            'deductions' => array_combine(
                array_map(function ($name) {
                    return 'u_' . strtolower(str_replace(' ', '_', $name));
                }, $payrollDeductions),
                $deductions
            ),
        ];
        return response()->json($data);
    }

    // public function UpdateEmployeeSalary(Request $request, $id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->employee_salary_setup)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $EmployeeSalary = EmployeeSalary::findOrFail($id);

    //     $EmployeeOldSalary = decrypt($EmployeeSalary->salary);
    //     $EmployeeOldSalary = number_format($EmployeeOldSalary,2);

    //     $EmployeeNewSalary = $request->input('uempSalary');
    //     $EmployeeNewSalary = number_format($EmployeeNewSalary,2);

    //     $EmployeeSalary->salary = encrypt(trim($request->input('uempSalary')));
    //     $effective_date = $request->input('usalary_edt');
    //     $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
    //     $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
    //     $EffectDateTime->subMinute(1);

    //     if ($EffectDateTime->isPast()) {
    //         $status = 1; //Active
    //     } else {
    //          $status = 0; //Inactive
    //     }

    //     $EmployeeSalary->effective_timestamp = $effective_date;
    //     $EmployeeSalary->last_updated = $this->currentDatetime;
    //     $EmployeeSalary->status = $status;

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $EmployeeSalary->save();

    //     if (empty($EmployeeSalary->id)) {
    //         return response()->json(['error' => 'Failed to update Employee Salary. Please try again']);
    //     }
    //     $logs = Logs::create([
    //         'module' => 'salary',
    //         'content' => "The salary was revised from Rs {$EmployeeOldSalary} To Rs
    //                      {$EmployeeNewSalary} by '{$sessionName}'",
    //         'event' => 'update',
    //         'timestamp' => $this->currentDatetime,
    //     ]);
    //     $EmployeeSalaryLog = EmployeeSalary::where('id', $EmployeeSalary->id)->first();
    //     $logIds = $EmployeeSalary->logid ? explode(',', $EmployeeSalary->logid) : [];
    //     $logIds[] = $logs->id;
    //     $EmployeeSalaryLog->logid = implode(',', $logIds);
    //     $EmployeeSalaryLog->save();
    //     return response()->json(['success' => 'Employee Salary updated successfully']);
    // }

    public function UpdateEmployeeSalary(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_salary_setup)[2];
        if ($edit == 0) {
            abort(403, 'Forbidden');
        }
        $EmployeeSalary = EmployeeSalary::findOrFail($id);
        // Fetch additions and deductions names
        $payrollAdditions = FinancialPayrollAddition::where('status', 1)->get();
        $payrollDeductions = FinancialPayrollDeduction::where('status', 1)->get();
        // Fetch existing encrypted additions and deductions
        $currentAdditions = $EmployeeSalary->additions;
        $currentDeductions = $EmployeeSalary->deductions;
        $totaloldAdditions = 0;
        $totaloldDeductions = 0;
        // Decrypt current additions
        if ($currentAdditions) {
            $additionIds = explode(',', $currentAdditions);
            foreach ($additionIds as $additionId) {
                if ($additionId) {
                    try {
                        $value = decrypt(trim($additionId));
                        $totaloldAdditions += (float) $value;
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        $totaloldAdditions +=0;
                    }
                }
            }
        }

        // Decrypt current deductions
        if ($currentDeductions) {
            $deductionIds = explode(',', $currentDeductions);
            foreach ($deductionIds as $deductionId) {
                if ($deductionId) {
                    try {
                        $value = decrypt(trim($deductionId));
                        $totaloldDeductions += (float) $value;
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        $totaloldDeductions +=0;
                    }
                }
            }
        }
        $EmployeeOldSalary = $totaloldAdditions - $totaloldDeductions;
        $EmployeeOldSalary = number_format($EmployeeOldSalary, 2);

        // Initialize arrays to hold new additions and deductions
        $newAdditions = [];
        $newDeductions = [];

        // Calculate total additions and deductions from input values
        $totalAdditions = 0;
        $totalDeductions = 0;

        foreach ($payrollAdditions as $addition) {
            $inputName = 'u_' . strtolower(str_replace(' ', '_', $addition->name));
            $value = $request->input($inputName);
            // if ($value) {
                $newAdditions[] = encrypt(trim($value));
                $totalAdditions += (float) $value;
            // }
        }

        foreach ($payrollDeductions as $deduction) {
            $inputName = 'u_' . strtolower(str_replace(' ', '_', $deduction->name));
            $value = $request->input($inputName);
            // if ($value) {
                $newDeductions[] = encrypt(trim($value));
                $totalDeductions += (float) $value;
            // }
        }


        // Calculate old salary
        $EmployeeNewSalary = $totalAdditions - $totalDeductions;
        $EmployeeNewSalary = number_format($EmployeeNewSalary, 2);

        // Get new salary from the request
        // $EmployeeNewSalary = number_format(trim($request->input('uempSalary')), 2);
        // $EmployeeSalary->salary = encrypt(trim($request->input('uempSalary')));

        // Handle effective date
        $effective_date = $request->input('usalary_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        $status = $EffectDateTime->isPast() ? 1 : 0; // 1 for Active, 0 for Inactive

        // Update employee salary record
        $EmployeeSalary->effective_timestamp = $effective_date;
        $EmployeeSalary->last_updated = $this->currentDatetime;
        $EmployeeSalary->status = $status;
        $EmployeeSalary->additions = implode(',', $newAdditions);
        $EmployeeSalary->deductions = implode(',', $newDeductions);

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $EmployeeSalary->save();

        if (empty($EmployeeSalary->id)) {
            return response()->json(['error' => 'Failed to update Employee Salary. Please try again']);
        }

        // Log the changes
        $logs = Logs::create([
            'module' => 'salary',
            'content' => "The salary was revised from Rs {$EmployeeOldSalary} to Rs {$EmployeeNewSalary}. Total Additions: Rs " . number_format($totalAdditions, 2) . ", Total Deductions: Rs " . number_format($totalDeductions, 2) . " by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);

        $EmployeeSalaryLog = EmployeeSalary::where('id', $EmployeeSalary->id)->first();
        $logIds = $EmployeeSalary->logid ? explode(',', $EmployeeSalary->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeSalaryLog->logid = implode(',', $logIds);
        $EmployeeSalaryLog->save();

        return response()->json(['success' => 'Employee Salary updated successfully']);
    }


    public function EmployeeQualification()
    {
        $colName = 'employee_qualification_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $QualificationLevels = EmployeeQualificationLevel::where('status', 1)->get();
        // $Employees = Employee::where('status', 1)->get();
        $Employees = Employee::where('employee.status', 1)
        ->join('emp_qualification', 'employee.id', '=', 'emp_qualification.emp_id','')
        ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
        ->distinct()
        ->get(['employee.*','prefix.name as prefix']);

        $EmployeeQualificationCount = EmployeeQualification::count();
        return view('dashboard.emp-qualifications', compact('user','QualificationLevels','Employees','EmployeeQualificationCount'));
    }

    public function AddQualificationSetup(EmpQualificationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_qualification_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $empId = $request->input('emp-id');
        $QualificationLevel = $request->input('emp-ql');
        $QualificationLevels = is_array($QualificationLevel) ? implode(',', $QualificationLevel) : '';


        $QualificationDate = $request->input('q_date');
        $QualificationDates = [];

        foreach ($QualificationDate as $qd) {
            $QualificationDates[] = Carbon::createFromFormat('Y-m-d', $qd)->timestamp;
        }
        $QualificationDates = is_array($QualificationDates) ? implode(',', $QualificationDates) : '';

        $Qualification = $request->input('qualification');
        $Qualifications = is_array($Qualification) ? implode(',', $Qualification) : '';


        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $employeeQualificationExists = EmployeeQualification::where('emp_id', $empId)
        ->exists();

        if ($employeeQualificationExists) {
            return response()->json(["info" => "This employee's Qualification is already on record."]);
        }
        else
        {
            $EmpQualification= new EmployeeQualification();

            $EmpQualification->emp_id = $empId;
            $EmpQualification->levelid = $QualificationLevels;
            $EmpQualification->qualification_date = $QualificationDates;
            $EmpQualification->name = $Qualifications;

            $EmpQualification->user_id = $sessionId;
            $EmpQualification->last_updated = $last_updated;
            $EmpQualification->timestamp = $timestamp;
            $EmpQualification->save();

            if (empty($EmpQualification->id)) {
                return response()->json(['error' => 'Failed to Add Employee Qualification! Please Try Again.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "Qualification has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $currentLogIds = DB::table('employee')->where('id', $empId)->value('logid');

            $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
            DB::table('employee')->where('id', $empId)->update(['logid' => $newLogIds]);

            return response()->json(['success' => 'Employee Qualification added successfully']);
        }

    }

    public function ViewQualificationSetup($id)
    {
        $rights = $this->rights;

        $view = explode(',', $rights->employee_qualification_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Employee = DB::table('employee')
        ->join('emp_qualification', 'employee.id', '=', 'emp_qualification.emp_id')
        ->join('organization', 'organization.id', '=', 'employee.org_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
        ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
        ->leftJoin('emp_qualification_level', DB::raw("FIND_IN_SET(emp_qualification_level.id, emp_qualification.levelid)"), ">", DB::raw("'0'"))
        ->where('employee.id', $id)
        ->select(
            'employee.id',
            'employee.name',
            'organization.organization as orgName',
            'org_site.name as siteName',
            'emp_position.name as positionName',
            'costcenter.name as headCountCC',
            'emp_qualification.levelid as qualificationLevelid',
            'emp_qualification.qualification_date as qualificationDate',
            'emp_qualification.name as qualificationName',
            DB::raw('GROUP_CONCAT(emp_qualification_level.name ORDER BY FIND_IN_SET(emp_qualification_level.id, emp_qualification.levelid)) as qualificationLevelNames')
        )
        ->groupBy('employee.id', 'employee.name', 'organization.organization', 'org_site.name',
            'costcenter.name','emp_position.name','emp_qualification.levelid', 'emp_qualification.qualification_date',
            'emp_qualification.name')
        ->first();

        $qualificationDates = $Employee->qualificationDate;

        $qualificationDates = explode(',', $qualificationDates);

        $formattedQualificationDates = array_map(function($qualificationDates) {
            return date('Y-m-d', $qualificationDates);
        }, $qualificationDates);
        $qualificationDates = implode(',', $formattedQualificationDates);
        $data = [
            'id' => $Employee->id,
            'empName' => $Employee->name,
            'orgName' => $Employee->orgName,
            'siteName' => $Employee->siteName,
            'positionName' => $Employee->positionName,
            'headCountCC' => $Employee->headCountCC,
            'empqualificationLevel' => trim($Employee->qualificationLevelNames),
            'empqualificationLevelId' => trim($Employee->qualificationLevelid),
            'empqualificationDate' => $qualificationDates,
            'empqualificationName' => trim($Employee->qualificationName),
            'rights' => $rights->employee_qualification_setup,
        ];
        return response()->json($data);
    }

    public function UpdateQualificationSetup(Request $request)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_qualification_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $empId = $request->input('empId');
        $EmpQualificationSetup = EmployeeQualification::where('emp_id', $empId)->firstOrFail();

        // Get all the data
        $QualificationLevel = $request->input('u_ql');
        $QualificationDate = $request->input('u_qd');
        $Qualification = $request->input('u_qualificationDescription');

        // Filter out rows where any data is missing
        $filteredData = [];
        for ($i = 0; $i < count($QualificationLevel); $i++) {
            if (!empty($QualificationLevel[$i]) && !empty($QualificationDate[$i]) && !empty($Qualification[$i])) {
                $filteredData[] = [
                    'u_ql' => $QualificationLevel[$i],
                    'u_qd' => Carbon::createFromFormat('Y-m-d', $QualificationDate[$i])->timestamp,
                    'u_qualificationDescription' => $Qualification[$i],
                ];
            }
        }

         // Convert filtered data back to CSV strings
        $QualificationLevels = implode(',', array_column($filteredData, 'u_ql'));
        $QualificationDates = implode(',', array_column($filteredData, 'u_qd'));
        $Qualifications = implode(',', array_column($filteredData, 'u_qualificationDescription'));

        $EmpQualificationSetup->levelid = $QualificationLevels;
        $EmpQualificationSetup->qualification_date = $QualificationDates;
        $EmpQualificationSetup->name = $Qualifications;
        $EmpQualificationSetup->last_updated = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $EmpQualificationSetup->save();

        if (empty($EmpQualificationSetup->id)) {
            return response()->json(['error' => 'Failed to update Employee Qualification. Please try again']);
        }

        $logs = Logs::create([
                'module' => 'hr',
                'content' => "Qualification has been updated by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $this->currentDatetime,
            ]);
        $logId = $logs->id;
        $currentLogIds = DB::table('employee')->where('id', $empId)->value('logid');

        $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
        DB::table('employee')->where('id', $empId)->update(['logid' => $newLogIds]);

        return response()->json(['success' => 'Employee Qualification updated successfully']);
    }

    public function EmployeeDocuments()
    {
        $colName = 'employee_documents';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $sessionOrg = $user->org_id;

        $Employees = Employee::where('status', 1)
        ->get(['employee.*']);

        if($sessionOrg != '0')
        {
            $Employees->where('employee.org_id', '=', $sessionOrg);
        }

        $Organizations = Organization::where('status', 1)->get();

        return view('dashboard.emp-documents', compact('user','Employees','Organizations'));
    }

    public function AddEmployeeDocuments(EmployeeDocumentRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_documents)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }

        $Desc = trim($request->input('document_desc'));
        $orgId = $request->input('ed_org');
        $siteId = $request->input('ed-site');
        $empId = $request->input('empid-document');
        // $documents = $request->input('emp_documents');

        $empEdt = $request->input('ed_edt');
        $empEdt = Carbon::createFromFormat('l d F Y - h:i A', $empEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($empEdt)->setTimezone('Asia/Karachi');

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

        $employeeDocumentExists = EmployeeDocuments::where('emp_id', $empId)
        ->exists();

        if ($employeeDocumentExists) {
            return response()->json(["info" => "This employee's Documents is already on record."]);
        }
        else
        {
            $EmpDocuments = new EmployeeDocuments();
            $EmpDocuments->document_desc = $Desc;
            $EmpDocuments->org_id = $orgId;
            $EmpDocuments->site_id = $siteId;
            $EmpDocuments->emp_id = $empId;
            $EmpDocuments->status = $status;
            $EmpDocuments->user_id = $sessionId;
            $EmpDocuments->effective_timestamp = $empEdt;
            $EmpDocuments->last_updated = $last_updated;
            $EmpDocuments->timestamp = $timestamp;
            $EmpDocuments->save();

            if (empty($EmpDocuments->id)) {
                return response()->json(['error' => 'Failed to Add Employee Documents! Please Try Again.']);
            }

            $filePaths = [];
            if ($request->hasFile('emp_documents')) {
                $uploadedFiles = $request->file('emp_documents');
                $lastId = $EmpDocuments->id;
                foreach ($uploadedFiles as $file) {
                    $insertFileName = time() . '_' . $file->getClientOriginalName();
                    $uniqueFileName = $lastId . '_' .time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('assets/emp/documents'), $uniqueFileName);
                    $filePaths[] = $insertFileName;
                }
            }

            $fileAttachments = implode(',', $filePaths);
            $EmpDocuments->documents = $fileAttachments;
            $EmpDocuments->save();

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "Employee Documents has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $EmpDocuments->logid = $logs->id;
            $EmpDocuments->save();

            return response()->json(['success' => 'Employee Documents added successfully']);
        }

    }

    public function GetDocumentEmployees(Request $request)
    {
        $siteId = $request->input('siteId');
        $Employees = Employee::leftJoin('emp_documents', 'employee.id', '=', 'emp_documents.emp_id')
                    ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
                    ->whereNull('emp_documents.emp_id')
                    ->where('employee.status', 1)
                    ->where('employee.site_id', $siteId)
                    ->get(['employee.*','prefix.name as prefix']);
                    if($Employees->count() > 0) {
                        return response()->json($Employees);
                    }
    }

    public function ViewEmployeeDocuments()
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_documents)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $user = auth()->user();

        $EmployeeDocuments = EmployeeDocuments::select('emp_documents.*',
        'employee.name as empName', 'prefix.name as prefixName', 'org_site.name as siteName')
        ->join('employee', 'employee.id', '=', 'emp_documents.emp_id')
        ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->orderBy('emp_documents.id', 'desc');

        return DataTables::eloquent($EmployeeDocuments)
            ->addColumn('id_raw', function ($EmployeeDocument) {
                return $EmployeeDocument->id;
            })
            ->addColumn('empDetails', function ($EmployeeDocument) {
                $empName = $EmployeeDocument->prefixName.' '.$EmployeeDocument->empName;
                return $empName."<hr class='mt-1 mb-1'><b>Site:</b> " . ucwords($EmployeeDocument->siteName) . "  <br>";
            })
            ->addColumn('desc', function ($EmployeeDocument) {
                $documentDesc = ucwords($EmployeeDocument->document_desc);
                return $documentDesc;
            })
            ->editColumn('documents', function ($EmployeeDocument) {
                $id = $EmployeeDocument->id;
                $attachmentPath = $EmployeeDocument->documents;

                $actionButtons = '<button type="button" data-id="' . $id . '" data-path="' . $attachmentPath . '" class="btn waves-effect waves-light btn-sm btn-primary downloadempDocuments">'
                    . '<i class="fa fa-download"></i> Download Documents'
                    . '</button>';
                return $actionButtons;
            })
            ->addColumn('action', function ($EmployeeDocument) {
                $EmployeeDocumentId = $EmployeeDocument->id;
                $logId = $EmployeeDocument->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->employee_documents)[2];
                $actionButtons = '';

                if ($edit == 1) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-empDocument" data-ed-id="'.$EmployeeDocumentId.'">'
                    . '<i class="fa fa-edit"></i> Edit'
                    . '</button>';
                }

                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';

                return $EmployeeDocument->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($EmployeeDocument) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->employee_documents)[3];
                return $updateStatus == 1
                    ? ($EmployeeDocument->status
                        ? '<span class="label label-success ed_status cursor-pointer" data-id="'.$EmployeeDocument->id.'" data-status="'.$EmployeeDocument->status.'">Active</span>'
                        : '<span class="label label-danger ed_status cursor-pointer" data-id="'.$EmployeeDocument->id.'" data-status="'.$EmployeeDocument->status.'">Inactive</span>'
                    )
                    : ($EmployeeDocument->status
                        ? '<span class="label label-success">Active</span>'
                        : '<span class="label label-danger">Inactive</span>'
                    );
            })

            ->rawColumns(['id_raw','empDetails','desc','documents','action','status'])
            ->make(true);
    }

    public function UpdateEmployeeDocumentStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->employee_documents)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $EmployeeDocument = EmployeeDocuments::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $EmployeeDocument->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $EmployeeDocument->effective_timestamp = 0;

        }
        $EmployeeDocument->status = $UpdateStatus;
        $EmployeeDocument->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeDocumentLog = EmployeeDocuments::where('id', $ID)->first();
        $logIds = $EmployeeDocumentLog->logid ? explode(',', $EmployeeDocumentLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeDocumentLog->logid = implode(',', $logIds);
        $EmployeeDocumentLog->save();

        $EmployeeDocument->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateEmployeeDocumentModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_documents)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        // $EmployeeDocument = EmployeeDocuments::select('emp_documents.*',)
        // ->where('emp_documents.id', '=', $id)
        // ->first();
        $EmployeeDocument = EmployeeDocuments::where('id', $id)->firstOrFail();


        $Desc = ucwords($EmployeeDocument->document_desc);
        $effective_timestamp = $EmployeeDocument->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'desc' => $Desc,
            'documents' => $EmployeeDocument->documents,
            'effective_timestamp' => $effective_timestamp,
        ];


        return response()->json($data);
    }

    public function saveEmployeeDocuments(Request $request)
    {
        $id = $request->input('ued-id');
        $employeeDocument = EmployeeDocuments::findOrFail($id);

        // Remove selected documents
        if ($request->has('removed_documents')) {
            $removedDocs = explode(',', $request->input('removed_documents'));
            foreach ($removedDocs as $doc) {
                $filePath = public_path('assets/emp/documents/'.$id.'_'.$doc);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $existingDocuments = $employeeDocument->documents
            ? explode(',', $employeeDocument->documents)
            : [];
            $removedDocs = array_map('trim', $removedDocs);
            $existingDocuments = array_map('trim', $existingDocuments);
            $updatedDocuments = array_diff($existingDocuments, $removedDocs);
            $employeeDocument->documents = implode(',', $updatedDocuments);
        }

        // Add new documents
        if ($request->hasFile('u_emp_documents')) {
            $newDocs = [];
            foreach ($request->file('u_emp_documents') as $file) {
                $uniqueFileName = time() . '_' . $file->getClientOriginalName();
                $uniqueFileNameWithId = $id. '_'. time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/emp/documents'), $uniqueFileNameWithId);
                $newDocs[] = $uniqueFileName;
            }
            $existingDocuments = $employeeDocument->documents
            ? explode(',', $employeeDocument->documents)
            : [];

            $mergedDocuments = array_merge($existingDocuments, $newDocs);
            $employeeDocument->documents = implode(',', $mergedDocuments);
            // $employeeDocument->documents = array_merge($employeeDocument->documents, $newDocs);
        }

        $employeeDocument->document_desc = $request->input('u_document_desc');
        $effective_date = $request->input('u_ed_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
             $status = 0;
        }
        $employeeDocument->effective_timestamp = $effective_date;
        $employeeDocument->last_updated = $this->currentDatetime;
        $employeeDocument->status = $status;

        $employeeDocument->save();

        return response()->json(['success' => true]);
    }



    public function GetMedicalLicenseEmployee(Request $request)
    {
        $siteId = $request->input('siteId');
        $Employees = Employee::leftJoin('emp_medical_license', 'employee.id', '=', 'emp_medical_license.emp_id')
                     ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
                     ->whereNull('emp_medical_license.emp_id')
                     ->where('employee.status', 1)
                     ->where('employee.site_id', $siteId)
                     ->get(['employee.*','prefix.name as prefix']);
                     if($Employees->count() > 0) {
                        return response()->json($Employees);
                    }
    }

    public function EmployeeMedicalLicense()
    {
        $colName = 'employee_medical_license_setup';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        // $Employees = Employee::where('status', 1)->get();
        $Employees = Employee::where('employee.status', 1)
        ->join('emp_medical_license', 'employee.id', '=', 'emp_medical_license.emp_id','')
        ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
        ->distinct()
        ->get(['employee.*', 'prefix.name as prefix']);

        $EmployeeMedicalLicenseCount = EmployeeMedicalLicense::count();
        return view('dashboard.emp-medical-license', compact('user','Employees','EmployeeMedicalLicenseCount'));
    }

    public function AddMedicalLicense(EmpMedicalLicenseRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_medical_license_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $empId = $request->input('emp-id');
        $medicalLicense = $request->input('medicalLicense');
        $medicalLicense = is_array($medicalLicense) ? implode(',', $medicalLicense) : '';

        $refNo = $request->input('ref_no');
        $refNo = is_array($refNo) ? implode(',', $refNo) : '';

        $ExpireDate = $request->input('expire_date');
        $ExpireDates = [];
        $Statuses = [];
        $currentTimestamp = Carbon::now()->timestamp;
        foreach ($ExpireDate as $ed) {
            $timestamp = Carbon::createFromFormat('Y-m-d', $ed)->timestamp;
            $ExpireDates[] = $timestamp;
            $status = ($timestamp > $currentTimestamp) ? 1 : 0;
            $Statuses[] = $status;
            // $ExpireDates[] = Carbon::createFromFormat('Y-m-d', $ed)->timestamp;
        }

        $ExpireDates = is_array($ExpireDates) ? implode(',', $ExpireDates) : '';

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $empMedicalLicenseExists = EmployeeMedicalLicense::where('emp_id', $empId)
        ->exists();

        if ($empMedicalLicenseExists) {
            return response()->json(["info" => "This employee's Medical License is already on record."]);
        }
        else
        {
            $EmployeeMedicalLicense= new EmployeeMedicalLicense();

            $EmployeeMedicalLicense->emp_id = $empId;
            $EmployeeMedicalLicense->name = $medicalLicense;
            $EmployeeMedicalLicense->expire_date = $ExpireDates;
            $EmployeeMedicalLicense->ref_no = $refNo;

            $EmployeeMedicalLicense->user_id = $sessionId;
            $EmployeeMedicalLicense->status = implode(',', $Statuses);
            $EmployeeMedicalLicense->last_updated = $last_updated;
            $EmployeeMedicalLicense->timestamp = $timestamp;
            $EmployeeMedicalLicense->save();

            if (empty($EmployeeMedicalLicense->id)) {
                return response()->json(['error' => 'Failed to Add Employee Medical License! Please Try Again.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "Medical License has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $currentLogIds = DB::table('employee')->where('id', $empId)->value('logid');

            $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
            DB::table('employee')->where('id', $empId)->update(['logid' => $newLogIds]);

            return response()->json(['success' => 'Employee Medical License added successfully']);
        }

    }

    public function ViewMedicalLicense($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_medical_license_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $Employee = DB::table('employee')
        ->join('emp_medical_license', 'employee.id', '=', 'emp_medical_license.emp_id')
        ->join('organization', 'organization.id', '=', 'employee.org_id')
        ->join('org_site', 'org_site.id', '=', 'employee.site_id')
        ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
        ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
        ->where('employee.id', $id)
        ->select(
        'employee.id',
        'employee.name',
        'organization.organization as orgName',
        'org_site.name as siteName',
        'emp_medical_license.name as medicaLicenseName',
        'emp_medical_license.ref_no as medicaLicenseRefNo',
        'emp_medical_license.expire_date as medicaLicenseExpiry',
        'emp_medical_license.status as medicalLicenseStatus',
        'emp_position.name as positionName',
        'costcenter.name as headCountCC'
        )
        ->groupBy(
            'employee.id',
            'employee.name',
            'organization.organization',
            'org_site.name',
            'emp_position.name',
            'costcenter.name',
            'emp_medical_license.name',
            'emp_medical_license.ref_no',
            'emp_medical_license.expire_date',
            'emp_medical_license.status',
        )
        ->first();

        $medicaLicenseExpiry = $Employee->medicaLicenseExpiry;
        $medicaLicenseExpiry = explode(',', $medicaLicenseExpiry);
        $medicaLicenseExpiryDates = array_map(function($medicaLicenseExpiry) {
            return date('Y-m-d', $medicaLicenseExpiry);
        }, $medicaLicenseExpiry);
        $medicaLicenseExpiry = implode(',', $medicaLicenseExpiryDates);

        $data = [
            'id' => $Employee->id,
            'empName' => $Employee->name,
            'orgName' => $Employee->orgName,
            'siteName' => $Employee->siteName,
            'positionName' => $Employee->positionName,
            'headCountCC' => $Employee->headCountCC,
            'medicalLicense' => trim($Employee->medicaLicenseName),
            'refNo' => trim($Employee->medicaLicenseRefNo),
            'expiryDate' => $medicaLicenseExpiry,
            'status' => $Employee->medicalLicenseStatus,
            'rights' => $rights->employee_medical_license_setup,
        ];

        return response()->json($data);
    }

    public function UpdateMedicalLicense(Request $request)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_medical_license_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $empId = $request->input('empId');
        $EmpMedicalLicense = EmployeeMedicalLicense::where('emp_id', $empId)->firstOrFail();

        $MedicalLicense = $request->input('u_medicalLicense');
        $RefNo = $request->input('u_refNo');
        $ExpireDate = $request->input('u_ed');
        $effective_date = $request->input('u_org_edt');

        // Filter out rows where any data is missing
        $filteredData = [];
        $currentDate = Carbon::now();

        for ($i = 0; $i < count($MedicalLicense); $i++) {
            if (!empty($MedicalLicense[$i]) && !empty($MedicalLicense[$i]) && !empty($MedicalLicense[$i])) {
                $expiryDateInstance = Carbon::createFromFormat('Y-m-d', $ExpireDate[$i]);
                $status = ($expiryDateInstance->lt($currentDate)) ? 0 : 1; // If the expiry date is less than the current date, set status to 0, else 1
                $filteredData[] = [
                    'u_medicalLicense' => $MedicalLicense[$i],
                    'u_ed' => $expiryDateInstance->timestamp,
                    'u_refNo' => $RefNo[$i],
                    'status' => $status
                ];
            }
        }

        // Convert filtered data back to CSV strings
        $MedicalLicense = implode(',', array_column($filteredData, 'u_medicalLicense'));
        $RefNo = implode(',', array_column($filteredData, 'u_refNo'));
        $ExpireDate = implode(',', array_column($filteredData, 'u_ed'));
        $Status = implode(',', array_column($filteredData, 'status'));


        $EmpMedicalLicense->name = $MedicalLicense;
        $EmpMedicalLicense->ref_no = $RefNo;
        $EmpMedicalLicense->expire_date = $ExpireDate;
        $EmpMedicalLicense->status = $Status;
        $EmpMedicalLicense->last_updated = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $EmpMedicalLicense->save();

        if (empty($EmpMedicalLicense->id)) {
            return response()->json(['error' => 'Failed to update Employee Medical License. Please try again']);
        }

        $logs = Logs::create([
                'module' => 'hr',
                'content' => "Employee Medical License has been updated by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $this->currentDatetime,
            ]);
        $logId = $logs->id;
        $currentLogIds = DB::table('employee')->where('id', $empId)->value('logid');

        $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
        DB::table('employee')->where('id', $empId)->update(['logid' => $newLogIds]);

        return response()->json(['success' => 'Employee Medical License updated successfully']);
    }

    public function EmployeeCostCenter()
    {
        $colName = 'employee_cost_center_allocation';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        // $Employees = Employee::where('employee.status', 1)
        // ->join('emp_cc', 'employee.id', '=', 'emp_cc.emp_id','')
        // ->distinct()
        // ->get(['employee.*']);

        $Employees = Employee::join('prefix', 'prefix.id', '=', 'employee.prefix_id')
            ->join('emp_cc', 'employee.id', '=', 'emp_cc.emp_id','')
            ->distinct()
            ->where('employee.status', 1)
            ->get(['employee.id','employee.name','prefix.name as prefix']);

        $EmployeeCCCount = EmployeeCC::count();
        return view('dashboard.emp-cc', compact('user','Employees','EmployeeCCCount','Organizations'));
    }

    public function GetCCEmployee(Request $request)
    {
        $siteId = $request->input('siteId');
        $Employees = Employee::leftJoin('emp_cc', 'employee.id', '=', 'emp_cc.emp_id')
                      ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
                     ->whereNull('emp_cc.emp_id')
                     ->where('employee.status', 1)
                     ->where('employee.site_id', $siteId)
                     ->get(['employee.*','prefix.name as prefix']);
                     if($Employees->count() > 0) {
                        return response()->json($Employees);
                    }
    }

    public function GetPhysicians(Request $request)
    {
        $siteId = $request->input('siteId');

        // $Physicians = Employee::where('employee.site_id', $siteId)
        // ->where('employee.status', 1)
        // ->get(['employee.*']);
        $Physicians = Employee::join('prefix', 'prefix.id', '=', 'employee.prefix_id')
        ->where('employee.site_id', $siteId)
        ->where('employee.status', 1)
        ->whereRaw('LOWER(prefix.name) LIKE ?', ['%dr.%'])
        ->get([
            'employee.id',
            'employee.name',
            'prefix.name as prefix'
        ]);

        return response()->json($Physicians);
    }

    public function GetServiceEmployee(Request $request)
    {
        $siteId = $request->input('siteId');
        $Employees = Employee::leftJoin('emp_service_allocation', 'employee.id', '=', 'emp_service_allocation.emp_id')
                    ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
                    ->whereNull('emp_service_allocation.emp_id')
                    ->where('employee.status', 1)
                    ->where('employee.site_id', $siteId)
                    ->get(['employee.id','employee.name','prefix.name as prefix']);
                    if($Employees->count() > 0) {
                        return response()->json($Employees);
                    }
    }

    public function GetEmployeeForLocation(Request $request)
    {
        $siteId = $request->input('siteId');
        $Employees = Employee::leftJoin('emp_inventory_location', 'employee.id', '=', 'emp_inventory_location.emp_id')
                    ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
                    ->whereNull('emp_inventory_location.emp_id')
                    ->where('employee.status', 1)
                    ->where('employee.site_id', $siteId)
                    ->get(['employee.id','employee.name','prefix.name as prefix']);
        if($Employees->count() > 0) {
            return response()->json($Employees);
        }
    }

    public function AddEmployeeCostCenter(EmployeeCCRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_cost_center_allocation)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $empId = $request->input('emp-id');
        $Organization = $request->input('empcc_org');
        // $Organization = is_array($Organization) ? implode(',', $Organization) : '';


        $HeadCountSite = $request->input('emp_headcountsite');

        $Site = $request->input('empcc_site');
        $Site = is_array($Site) ? implode(',', $Site) : '';
        // $Site = is_array($Site) ? implode(',', $Site) : '';

        $CostCenter = $request->input('emp_costcenter');
        $CostCenter = is_array($CostCenter) ? implode(',', $CostCenter) : '';

        $CostCenterPercent = $request->input('cc_percent');
        $totalPercentage = array_sum(array_map('intval', $CostCenterPercent));

        if ($totalPercentage != 100) {
            return response()->json(["info" => "The total percentage for the Cost Center should be exactly 100."]);
        }
        $CostCenterPercent = is_array($CostCenterPercent) ? implode(',', $CostCenterPercent) : '';

        $Edt = $request->input('empCC-ed');
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

        $empCCExists = EmployeeCC::where('emp_id', $empId)
        ->exists();

        if ($empCCExists) {
            return response()->json(["info" => "The Cost Center for this employee has already been assigned."]);
        }
        else
        {
            $EmployeeCC= new EmployeeCC();

            $EmployeeCC->emp_id = $empId;
            $EmployeeCC->org_id = $Organization;
            $EmployeeCC->headcount_site_id = $HeadCountSite;
            $EmployeeCC->site_id = $Site;
            $EmployeeCC->cc_id = $CostCenter;
            $EmployeeCC->percentage = $CostCenterPercent;
            $EmployeeCC->status = $status;
            $EmployeeCC->user_id = $sessionId;
            $EmployeeCC->effective_timestamp = $Edt;
            $EmployeeCC->last_updated = $last_updated;
            $EmployeeCC->timestamp = $timestamp;
            $EmployeeCC->save();

            if (empty($EmployeeCC->id)) {
                return response()->json(['error' => 'Failed to Allocate Cost Center to this Employee! Please Try Again.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "Cost Center has been allocated by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $currentLogIds = DB::table('employee')->where('id', $empId)->value('logid');

            $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
            DB::table('employee')->where('id', $empId)->update(['logid' => $newLogIds]);

            return response()->json(['success' => 'The Cost Center has been successfully assigned to the employee.']);
        }

    }

    public function ViewEmployeeCostCenter($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_cost_center_allocation)[1];
        $add = explode(',', $rights->employee_cost_center_allocation)[0];
        if($view == 0 || $add == 0)
        {
            abort(403, 'Forbidden');
        }

        $Employee = DB::table('employee')
        ->join('emp_cc', 'employee.id', '=', 'emp_cc.emp_id')
        ->join('organization', 'organization.id', '=', 'emp_cc.org_id')
        ->join('org_site', 'org_site.id', '=', 'emp_cc.headcount_site_id')
        ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
        ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
        // ->join('org_site as headcount_site', 'headcount_site.id', '=', 'emp_cc.headcount_site_id')
        ->where('employee.id', $id)
        ->select(
            'employee.id',
            'employee.name',
            'emp_cc.org_id',
            'organization.organization as orgName',
            'org_site.name as siteName',
            'emp_cc.headcount_site_id',
            'emp_cc.site_id',
            'emp_cc.cc_id',
            'costcenter.name as headCountCC',
            'emp_position.name as positionName',
            'emp_cc.percentage as CCPercentage',
            'emp_cc.effective_timestamp as edt',
            'emp_cc.status as status',
        )
        ->first();

        $org_id = $Employee->org_id;
        $headcount_site_id = $Employee->headcount_site_id;
        $siteName = $Employee->siteName;
        $orgName = $Employee->orgName;
        $cc_ids = explode(',', $Employee->cc_id);
        $site_ids = explode(',', $Employee->site_id);

        // $organizationData = DB::table('organization')->whereIn('id', $org_ids)->get()->keyBy('id');
        // $organizations = array_map(function ($id) use ($organizationData) {
        //     return $organizationData[$id]->organization;
        // }, $org_ids);
        // $organizations = implode(',', $organizations);

        // $sitesData = DB::table('org_site')->whereIn('id', $site_ids)->get()->keyBy('id');
        // $sites = array_map(function ($id) use ($sitesData) {
        //     return $sitesData[$id]->name;
        // }, $site_ids);
        // $sites = implode(',', $sites);

        $costcenterData = DB::table('costcenter')->whereIn('id', $cc_ids)->get()->keyBy('id');
        $costcenters = array_map(function ($id) use ($costcenterData) {
            return $costcenterData[$id]->name;
        }, $cc_ids);
        $costcenters = implode(',', $costcenters);

        $SiteData = DB::table('org_site')->whereIn('id', $site_ids)->get()->keyBy('id');
        $Sites = array_map(function ($id) use ($SiteData) {
            return $SiteData[$id]->name;
        }, $site_ids);
        $Sites = implode(',', $Sites);

        // $effective_timestamp = explode(',', $Employee->edt);
        // $effective_timestamps = [];
        // foreach ($effective_timestamp as $timestamp) {
        //     $formattedTime = Carbon::createFromTimestamp($timestamp)->format('l d F Y - h:i A');
        //     $effective_timestamps[] = $formattedTime;
        // }
        // $effective_timestamps = implode(',', $effective_timestamps);

        $effective_timestamp = $Employee->edt;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $Employee->id,
            'empName' => $Employee->name,
            'orgID' => $Employee->org_id,
            'orgName' => $orgName,
            'heacCountSiteId' => $Employee->headcount_site_id,
            'HeadCountSite' => $siteName,
            'positionName' => $Employee->positionName,
            'siteID' => $Employee->site_id,
            'siteName' => $Sites,
            'headCountCC' => $Employee->headCountCC,
            'ccID' => $Employee->cc_id,
            'CostCenter' => $costcenters,
            'CCPercentage' => $Employee->CCPercentage,
            'status' => $Employee->status,
            'effective_timestamp' => $effective_timestamp,
            'rights' => $rights->employee_cost_center_allocation,
        ];

        return response()->json($data);
    }

    public function UpdateEmployeeCC(Request $request)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_cost_center_allocation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $empId = $request->input('empId');

        $EmpCostCenter = EmployeeCC::where('emp_id', $empId)->firstOrFail();
        $CostCenter = $request->input('u_empcc');
        $HeadCountSite = $request->input('u_empcc_site');
        $Percent = $request->input('u_ecc_percent');
        $totalPercentage = array_sum(array_map('intval', $Percent));

        if ($totalPercentage != 100) {
            return response()->json(["info" => "The total percentage for the Cost Center should be exactly 100."]);
        }

        $Edt = $request->input('u_cced');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
            $status = 0;
        }

        // $CCEffectiveDateTimes = [];
        // $Statuses = [];
        // $currentTimestamp = Carbon::now()->timestamp;
        // foreach ($CCEffectiveDateTime as $ed) {
        //     $timestamp = Carbon::createFromFormat('l d F Y - h:i A', $ed)->timestamp;
        //     $EffectDateTime = Carbon::createFromTimestamp($timestamp)->setTimezone('Asia/Karachi');
        //     $EffectDateTime->subMinute(1);
        //     if ($EffectDateTime->isPast()) {
        //         $status = 1; //Active
        //     } else {
        //         $status = 0; //Inactive
        //     }
        //     $CCEffectiveDateTimes[] = $timestamp;
        //     $Statuses[] = $status;
        // }

        // Filter out rows where any data is missing
        $filteredData = [];
        for ($i = 0; $i < count($CostCenter); $i++) {
            if (!empty($CostCenter[$i]) && !empty($Percent[$i]) && !empty($HeadCountSite[$i])) {
                $filteredData[] = [
                    'u_empcc_site' => $HeadCountSite[$i],
                    'u_empcc' => $CostCenter[$i],
                    'u_ecc_percent' => $Percent[$i]
                ];
            }
        }
        $Sites = implode(',', array_column($filteredData, 'u_empcc_site'));
        $CostCenters = implode(',', array_column($filteredData, 'u_empcc'));
        $Percentage = implode(',', array_column($filteredData, 'u_ecc_percent'));

        // $CCEffectiveDateTimes = is_array($CCEffectiveDateTimes) ? implode(',', $CCEffectiveDateTimes) : '';
        $EmpCostCenter->site_id = $Sites;
        $EmpCostCenter->cc_id = $CostCenters;
        $EmpCostCenter->percentage = $Percentage;
        $EmpCostCenter->effective_timestamp = $Edt;
        $EmpCostCenter->status = $status;
        $EmpCostCenter->last_updated = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;

        $EmpCostCenter->save();

        if (empty($EmpCostCenter->id)) {
            return response()->json(['error' => 'Failed to update Employee Cost Center. Please try again']);
        }

        $logs = Logs::create([
                'module' => 'hr',
                'content' => "Employee Cost Center has been updated by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $this->currentDatetime,
            ]);
        $logId = $logs->id;
        $currentLogIds = DB::table('employee')->where('id', $empId)->value('logid');

        $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
        DB::table('employee')->where('id', $empId)->update(['logid' => $newLogIds]);

        return response()->json(['success' => 'Employee Cost Center updated successfully']);
    }


    public function EmployeeServiceAllocation()
    {
        $colName = 'employee_services_allocation';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        $Services = Service::where('status', 1)->get();
        return view('dashboard.emp-service-allocation', compact('Services','user','Organizations'));
    }

    public function AllocateEmployeeService(EmpServiceAllocationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_services_allocation)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Employee = trim($request->input('emp_sa'));
        $Organization = trim($request->input('org_sa'));
        $Site = trim($request->input('site_sa'));
        $Service = $request->input('service_sa');
        $ServiceIds = is_array($Service) ? implode(',', $Service) : '';
        $Edt = $request->input('sa_edt');
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

        $EmployeeServiceAllocationExists = EmployeeServiceAllocation::where('org_id', $Organization)
        ->where('emp_id', $Employee)
        ->where('site_id', $Site)
        ->exists();

        if ($EmployeeServiceAllocationExists) {
            return response()->json(['info' => 'Service already Allocated to this Employee.']);
        }
        else
        {
            $EmployeeServiceAllocation = new EmployeeServiceAllocation();
            $EmployeeServiceAllocation->emp_id = $Employee;
            $EmployeeServiceAllocation->org_id = $Organization;
            $EmployeeServiceAllocation->site_id = $Site;
            $EmployeeServiceAllocation->service_id = $ServiceIds;
            $EmployeeServiceAllocation->status = $status;
            $EmployeeServiceAllocation->user_id = $sessionId;
            $EmployeeServiceAllocation->last_updated = $last_updated;
            $EmployeeServiceAllocation->timestamp = $timestamp;
            $EmployeeServiceAllocation->effective_timestamp = $Edt;
            $EmployeeServiceAllocation->save();

            if (empty($EmployeeServiceAllocation->id)) {
                return response()->json(['error' => 'Failed to Allocate Service to Employee.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "Service Allocated by '{$sessionName}'",
                'event' => 'activate',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $EmployeeServiceAllocation->logid = $logs->id;
            $EmployeeServiceAllocation->save();
            return response()->json(['success' => 'Service Allocated successfully']);
        }
    }

    public function GetAllocatedService(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_services_allocation)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeServiceAllocations = EmployeeServiceAllocation::select('emp_service_allocation.*',
        'employee.name as empName','organization.organization as orgName','org_site.name as siteName',
        'prefix.name as PrefixName',)
        ->join('employee', 'employee.id', '=', 'emp_service_allocation.emp_id')
        ->join('organization', 'organization.id', '=', 'emp_service_allocation.org_id')
        ->join('org_site', 'org_site.id', '=', 'emp_service_allocation.site_id')
        ->join('prefix', 'prefix.id', '=', 'employee.prefix_id')
        ->orderBy('employee.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $EmployeeServiceAllocations->where('emp_service_allocation.org_id', '=', $sessionOrg);
        }
        $EmployeeServiceAllocations = $EmployeeServiceAllocations;
        // ->get()
        // return DataTables::of($EmployeeServiceAllocations)
        return DataTables::eloquent($EmployeeServiceAllocations)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('employee.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('emp_service_allocation.id', 'like', "%{$search}%")
                            ->orWhereRaw("EXISTS (
                                SELECT 1 FROM services
                                JOIN service_group ON services.group_id = service_group.id
                                JOIN service_type ON service_group.type_id = service_type.id
                                WHERE FIND_IN_SET(services.id, emp_service_allocation.service_id)
                                AND (services.name LIKE '%{$search}%' OR service_group.name LIKE '%{$search}%' OR service_type.name LIKE '%{$search}%')
                            )");
                    });
                }
            })
            ->addColumn('id_raw', function ($EmployeeServiceAllocation) {
                return $EmployeeServiceAllocation->id;  // Raw ID value
            })
            ->editColumn('id', function ($EmployeeServiceAllocation) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $EmployeeName = ucwords($EmployeeServiceAllocation->empName);

                $effectiveDate = Carbon::createFromTimestamp($EmployeeServiceAllocation->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($EmployeeServiceAllocation->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($EmployeeServiceAllocation->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($EmployeeServiceAllocation->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($EmployeeServiceAllocation->orgName);
                }
                $siteName = $EmployeeServiceAllocation->siteName;
                $PrefixName = $EmployeeServiceAllocation->PrefixName;

                return $PrefixName.' '.$EmployeeName.$orgName
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Site: </b>'.$siteName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('services', function ($EmployeeServiceAllocation) {
                $serviceIds = explode(',', $EmployeeServiceAllocation->service_id);
                $services = DB::table('services')
                    ->join('service_group', 'services.group_id', '=', 'service_group.id')
                    ->join('service_type', 'service_group.type_id', '=', 'service_type.id')
                    ->whereIn('services.id', $serviceIds)
                    ->select('services.name as serviceName', 'service_group.name as serviceGroupName', 'service_type.name as serviceTypeName')
                    ->get();

                $tableId = 'services-table-' . $EmployeeServiceAllocation->id;

                $formattedData = '<table id="'.$tableId.'" class="nested-services-table table table-bordered" style="width: 100%;">';
                $formattedData .= '<thead><tr>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Service Name</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Service Group</th>';
                $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Service Type</th>';
                $formattedData .= '</tr></thead><tbody>';

                foreach ($services as $service) {
                    $formattedData .= '<tr>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($service->serviceName) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($service->serviceGroupName) . '</td>';
                    $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($service->serviceTypeName) . '</td>';
                    $formattedData .= '</tr>';
                }
                $formattedData .= '</tbody></table>';

                return $formattedData;
            })
            ->addColumn('action', function ($EmployeeServiceAllocation) {
                    $EmployeeServiceAllocationId = $EmployeeServiceAllocation->id;
                    $logId = $EmployeeServiceAllocation->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->employee_services_allocation)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-serviceallocation" data-serviceallocation-id="'.$EmployeeServiceAllocationId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $EmployeeServiceAllocation->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($EmployeeServiceAllocation) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->employee_services_allocation)[3];
                return $updateStatus == 1 ? ($EmployeeServiceAllocation->status ? '<span class="label label-success serviceallocation_status cursor-pointer" data-id="'.$EmployeeServiceAllocation->id.'" data-status="'.$EmployeeServiceAllocation->status.'">Active</span>' : '<span class="label label-danger serviceallocation_status cursor-pointer" data-id="'.$EmployeeServiceAllocation->id.'" data-status="'.$EmployeeServiceAllocation->status.'">Inactive</span>') : ($EmployeeServiceAllocation->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status', 'services',
            'id'])
            ->make(true);
    }

    public function UpdateAllocatedServiceStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->employee_services_allocation)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeServiceAllocationID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $EmployeeServiceAllocation = EmployeeServiceAllocation::find($EmployeeServiceAllocationID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $EmployeeServiceAllocation->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $EmployeeServiceAllocation->effective_timestamp = 0;

        }
        $EmployeeServiceAllocation->status = $UpdateStatus;
        $EmployeeServiceAllocation->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeServiceAllocationLog = EmployeeServiceAllocation::where('id', $EmployeeServiceAllocationID)->first();
        $logIds = $EmployeeServiceAllocationLog->logid ? explode(',', $EmployeeServiceAllocationLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeServiceAllocationLog->logid = implode(',', $logIds);
        $EmployeeServiceAllocationLog->save();

        $EmployeeServiceAllocation->save();
        return response()->json(['success' => true, 200]);
    }

    public function ServiceAllocationModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_services_allocation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $EmployeeServiceAllocation = EmployeeServiceAllocation::select('emp_service_allocation.*',
        'employee.name as empName', 'organization.organization as orgName', 'org_site.name as siteName')
        ->join('employee', 'employee.id', '=', 'emp_service_allocation.emp_id')
        ->join('organization', 'organization.id', '=', 'emp_service_allocation.org_id')
        ->join('org_site', 'org_site.id', '=', 'emp_service_allocation.site_id')
        ->where('emp_service_allocation.id', $id)
        ->first();

        $serviceIds = explode(',', $EmployeeServiceAllocation->service_id);
        $serviceNames = DB::table('services')
            ->whereIn('id', $serviceIds)
            ->pluck('name')
            ->toArray();

        $serviceNames = implode(', ', $serviceNames);


        $orgName = ucwords($EmployeeServiceAllocation->orgName);
        $empName = ucwords($EmployeeServiceAllocation->empName);
        $siteName = ucwords($EmployeeServiceAllocation->siteName);
        $serviceName = ucwords($EmployeeServiceAllocation->serviceName);

        $effective_timestamp = $EmployeeServiceAllocation->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'empName' => $empName,
            'empID' => $EmployeeServiceAllocation->emp_id,
            'orgName' => $orgName,
            'orgID' => $EmployeeServiceAllocation->org_id,
            'siteName' => $siteName,
            'siteId' => $EmployeeServiceAllocation->site_id,
            'serviceNames' => $serviceNames,
            'serviceId' => $EmployeeServiceAllocation->service_id,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateAllocatedService(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_services_allocation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $EmployeeServiceAllocation = EmployeeServiceAllocation::findOrFail($id);

        $ServiceIDs = $request->input('u_saservice');
        $ServiceIds = is_array($ServiceIDs) ? implode(',', $ServiceIDs) : '';
        $EmployeeServiceAllocation->service_id = $ServiceIds;

        $effective_date = $request->input('usa_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
             $status = 0;
        }

        $EmployeeServiceAllocation->effective_timestamp = $effective_date;
        $EmployeeServiceAllocation->last_updated = $this->currentDatetime;
        $EmployeeServiceAllocation->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $EmployeeServiceAllocation->save();

        if (empty($EmployeeServiceAllocation->id)) {
            return response()->json(['error' => 'Employee Service Allocation Failed. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'hr',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $EmployeeServiceAllocationLog = EmployeeServiceAllocation::where('id', $EmployeeServiceAllocation->id)->first();
        $logIds = $EmployeeServiceAllocationLog->logid ? explode(',', $EmployeeServiceAllocationLog->logid) : [];
        $logIds[] = $logs->id;
        $EmployeeServiceAllocationLog->logid = implode(',', $logIds);
        $EmployeeServiceAllocationLog->save();
        return response()->json(['success' => 'Service Allocated updated successfully']);
    }

    public function EmployeeLocationAllocation()
    {
        $colName = 'employee_inventory_location_allocation';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        // $Services = Service::where('status', 1)->get();
        $Employees = Employee::where('employee.status', 1)
        ->join('emp_inventory_location', 'employee.id', '=', 'emp_inventory_location.emp_id','')
        ->distinct()
        ->get(['employee.*']);
        $EmployeeLocationCount = EmployeeLocationAllocation::count();

        return view('dashboard.emp-location-allocation', compact('user','EmployeeLocationCount','Employees'));
    }

    public function AllocateEmployeeServiceLocation(EmployeeLocationAllocationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->employee_inventory_location_allocation)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Employee = trim($request->input('emp_ela'));
        $Organization = trim($request->input('org_ela'));
        $Site = trim($request->input('site_ela'));
        $InventorySites = $request->input('invSite');
        $LocationArray = $request->input('location_ela');


        $serviceLocations = [];
        if (is_array($InventorySites) && is_array($LocationArray)) {
            foreach ($LocationArray as $index => $locations) {
                $serviceLocations[] = isset($locations) ? explode(',', $locations) : [];
            }
        }
        $ServiceIds = json_encode($serviceLocations);
        $InventorySiteIds = is_array($InventorySites) ? implode(',', $InventorySites) : '';

        $Edt = $request->input('ela_edt');
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

        $EmployeeLocationAllocationExists = EmployeeLocationAllocation::where('emp_id', $Employee)
        ->exists();

        if ($EmployeeLocationAllocationExists) {
            return response()->json(['info' => 'Inventory location already allocated to this Employee.']);
        }
        else
        {
            $EmployeeInvLocationAllocation = new EmployeeLocationAllocation();
            $EmployeeInvLocationAllocation->org_id = $Organization;
            $EmployeeInvLocationAllocation->site_id = $Site;
            $EmployeeInvLocationAllocation->emp_id = $Employee;
            $EmployeeInvLocationAllocation->location_site = $InventorySiteIds;
            $EmployeeInvLocationAllocation->service_location_id = $ServiceIds;
            $EmployeeInvLocationAllocation->status = $status;
            $EmployeeInvLocationAllocation->user_id = $sessionId;
            $EmployeeInvLocationAllocation->last_updated = $last_updated;
            $EmployeeInvLocationAllocation->timestamp = $timestamp;
            $EmployeeInvLocationAllocation->effective_timestamp = $Edt;
            $EmployeeInvLocationAllocation->save();

            if (empty($EmployeeInvLocationAllocation->id)) {
                return response()->json(['error' => 'Failed to Allocate Inventory location to Employee.']);
            }

            $logs = Logs::create([
                'module' => 'hr',
                'content' => "Inventory Location Allocated by '{$sessionName}'",
                'event' => 'activate',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $currentLogIds = DB::table('employee')->where('id', $Employee)->value('logid');
            $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
            DB::table('employee')->where('id', $Employee)->update(['logid' => $newLogIds]);

            // $logId = $logs->id;
            // $EmployeeInvLocationAllocation->logid = $logs->id;
            $EmployeeInvLocationAllocation->save();
            return response()->json(['success' => 'Inventory location Allocated successfully']);
        }
    }

    public function ViewEmployeeAllocatedLocation($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->employee_inventory_location_allocation)[1];
        if ($view == 0) {
            abort(403, 'Forbidden');
        }

        $Employee = DB::table('employee')
            ->join('emp_inventory_location', 'employee.id', '=', 'emp_inventory_location.emp_id')
            ->join('organization', 'organization.id', '=', 'emp_inventory_location.org_id')
            ->join('org_site', 'org_site.id', '=', 'emp_inventory_location.site_id')
            ->join('emp_position', 'emp_position.id', '=', 'employee.position_id')
            ->join('costcenter', 'costcenter.id', '=', 'employee.cc_id')
            ->where('employee.id', $id)
            ->select(
                'employee.id',
                'employee.name',
                'emp_inventory_location.org_id',
                'organization.organization as orgName',
                'org_site.name as siteName',
                'emp_inventory_location.site_id',
                'emp_inventory_location.location_site',
                'emp_inventory_location.service_location_id',
                'emp_inventory_location.effective_timestamp as edt',
                'emp_inventory_location.status as status',
                'emp_position.name as positionName',
                'costcenter.name as headCountCC'
            )
            ->first();

        $org_id = $Employee->org_id;
        $site_id = $Employee->site_id;
        $siteName = $Employee->siteName;
        $orgName = $Employee->orgName;
        $LocationSiteIds = explode(',', $Employee->location_site);
        $LocationIdsArray = json_decode($Employee->service_location_id, true);

        $siteNames = DB::table('org_site')
        ->whereIn('id', $LocationSiteIds)
        ->pluck('name', 'id');
        $locations = DB::table('service_location')
            ->whereIn('id', collect($LocationIdsArray)->flatten()->toArray())
            ->pluck('name', 'id');

        $siteLocationMapping = [];
        foreach ($LocationSiteIds as $index => $siteId) {
            $locationNames = [];
            $locationIds = [];
            $locationSiteName = $siteNames[$siteId] ?? 'N/A';

            if (isset($LocationIdsArray[$index])) {
                foreach ($LocationIdsArray[$index] as $locationId) {
                    $locationNames[] = $locations[$locationId] ?? 'N/A';
                    $locationIds[] = $locationId;

                }
            }
            $siteLocationMapping[] = [
                'org_id' => $org_id,
                'siteId' => $siteId,
                'locationSiteName' => $locationSiteName,
                'serviceLocations' => implode(', ', $locationNames),
                'serviceLocationIds' => implode(',', $locationIds),
            ];
        }

        $effective_timestamp = Carbon::createFromTimestamp($Employee->edt)->format('l d F Y - h:i A');
        $data = [
            'id' => $Employee->id,
            'empName' => $Employee->name,
            'orgID' => $Employee->org_id,
            'orgName' => $orgName,
            'siteID' => $Employee->site_id,
            'siteName' => $siteName,
            'positionName' => $Employee->positionName,
            'headCountCC' => $Employee->headCountCC,
            'LocationSiteId' => $Employee->location_site,
            'LocationSites' => $siteLocationMapping,
            'status' => $Employee->status,
            'effective_timestamp' => $effective_timestamp,
            'rights' => $rights->employee_inventory_location_allocation,
        ];

        return response()->json($data);
    }

    public function UpdateALlocatedEmployeeLocation(Request $request)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->employee_inventory_location_allocation)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $empId = $request->input('empId');

        $EmpLocationAllocation = EmployeeLocationAllocation::where('emp_id', $empId)->firstOrFail();

        $InventorySites = $request->input('uinvSite');
        $LocationArray = $request->input('ulocation_ela');

        if (is_array($InventorySites) && is_array($LocationArray)) {
            foreach ($InventorySites as $index => $siteId) {
                $locations = $LocationArray[$index] ?? null;

                // Site provided but no location
                if (!empty($siteId) && (empty($locations) || $locations === ',')) {
                    return response()->json([
                        'error' => "Row " . ($index + 1) . ": Location is required."
                    ]);
                }

                // Location provided but no site
                if (empty($siteId) && !empty($locations)) {
                    return response()->json([
                        'error' => "Row " . ($index + 1) . ": Site is required."
                    ]);
                }
            }
        }

        $serviceLocations = [];
        if (is_array($InventorySites) && is_array($LocationArray)) {
            foreach ($LocationArray as $index => $locations) {
                $serviceLocations[] = isset($locations) ? explode(',', $locations) : [];
            }
        }
        $ServiceIds = json_encode($serviceLocations);
        $InventorySiteIds = is_array($InventorySites) ? implode(',', $InventorySites) : '';

        $Edt = $request->input('u_ela_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1;
        } else {
            $status = 0;
        }

        $EmpLocationAllocation->location_site = $InventorySiteIds;
        $EmpLocationAllocation->service_location_id = $ServiceIds;
        $EmpLocationAllocation->effective_timestamp = $Edt;
        $EmpLocationAllocation->status = $status;
        $EmpLocationAllocation->last_updated = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;

        $EmpLocationAllocation->save();

        if (empty($EmpLocationAllocation->id)) {
            return response()->json(['error' => '"Failed to update Inventory Location Allocation. Please try again."']);
        }

        $logs = Logs::create([
                'module' => 'hr',
                'content' => "Employee Location Allocation has been updated by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $this->currentDatetime,
            ]);
        $logId = $logs->id;
        $currentLogIds = DB::table('employee')->where('id', $empId)->value('logid');

        $newLogIds = empty($currentLogIds) ? $logId : $currentLogIds . ',' . $logId;
        DB::table('employee')->where('id', $empId)->update(['logid' => $newLogIds]);

        return response()->json(['success' => 'Employee Inventory Location Allocation updated successfully']);
    }


    // public function GetAllocatedEmpLocation()
    // {
    //     $rights = $this->rights;
    //     $view = explode(',', $rights->employee_inventory_location_allocation)[1];
    //     if($view == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $EmployeeLocationAllocations = EmployeeLocationAllocation::select('emp_inventory_location.*',
    //     'employee.name as empName','organization.organization as orgName','org_site.name as siteName')
    //     ->join('employee', 'employee.id', '=', 'emp_inventory_location.emp_id')
    //     ->join('organization', 'organization.id', '=', 'emp_inventory_location.org_id')
    //     ->join('org_site', 'org_site.id', '=', 'emp_inventory_location.site_id')
    //     ->orderBy('employee.id', 'desc');

    //     $session = auth()->user();
    //     $sessionOrg = $session->org_id;
    //     if($sessionOrg != '0')
    //     {
    //         $EmployeeLocationAllocations->where('emp_inventory_location.org_id', '=', $sessionOrg);
    //     }
    //     $EmployeeLocationAllocations = $EmployeeLocationAllocations->get();

    //     return DataTables::of($EmployeeLocationAllocations)
    //         ->addColumn('id_raw', function ($EmployeeLocationAllocation) {
    //             return $EmployeeLocationAllocation->id;  // Raw ID value
    //         })
    //         ->editColumn('id', function ($EmployeeLocationAllocation) {
    //             $session = auth()->user();
    //             $sessionName = $session->name;
    //             $sessionId = $session->id;
    //             $EmployeeName = ucwords($EmployeeLocationAllocation->empName);

    //             $effectiveDate = Carbon::createFromTimestamp($EmployeeLocationAllocation->effective_timestamp)->format('l d F Y - h:i A');
    //             $timestamp = Carbon::createFromTimestamp($EmployeeLocationAllocation->timestamp)->format('l d F Y - h:i A');
    //             $lastUpdated = Carbon::createFromTimestamp($EmployeeLocationAllocation->last_updated)->format('l d F Y - h:i A');
    //             $createdByName = getUserNameById($EmployeeLocationAllocation->user_id);
    //             $createdInfo = "
    //                     <b>Created By:</b> " . ucwords($createdByName) . "  <br>
    //                     <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
    //                     <b>RecordedAt:</b> " . $timestamp ." <br>
    //                     <b>LastUpdated:</b> " . $lastUpdated;

    //             $sessionOrg = $session->org_id;
    //             $orgName = '';
    //             if($sessionOrg == 0)
    //             {
    //                 $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($EmployeeLocationAllocation->orgName);
    //             }
    //             $siteName = $EmployeeLocationAllocation->siteName;

    //             return $EmployeeName.$orgName
    //                 . '<hr class="mt-1 mb-2">'
    //                 .'<b>Site: </b>'.$siteName
    //                 . '<hr class="mt-1 mb-2">'
    //                 . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
    //                 . '<i class="fa fa-toggle-right"></i> View Details'
    //                 . '</span>';
    //         })
    //         ->editColumn('allocatioLocations', function ($EmployeeLocationAllocation) {
    //             $locationSiteIds = explode(',', $EmployeeLocationAllocation->location_site);
    //             $allocatedLocationIds = collect(json_decode($EmployeeLocationAllocation->service_location_id, true))
    //                 ->flatten()
    //                 ->toArray();
    //             $sites = DB::table('org_site')
    //                 ->whereIn('id', $locationSiteIds)
    //                 ->pluck('name', 'id');
    //             $locations = DB::table('service_location')
    //                 ->whereIn('id', $allocatedLocationIds)
    //                 ->pluck('name', 'id');
    //             $formattedData = '<table style="border-collapse: collapse; width: 100%;">';
    //             $formattedData .= '<tr>';
    //             $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Site</th>';
    //             $formattedData .= '<th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Service Locations</th>';
    //             $formattedData .= '</tr>';
    //             foreach ($locationSiteIds as $index => $siteId) {
    //                 $formattedData .= '<tr>';
    //                 $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . ucwords($sites[$siteId] ?? 'N/A') . '</td>';
    //                 $siteLocations = json_decode($EmployeeLocationAllocation->service_location_id, true)[$index] ?? [];
    //                 $formattedData .= '<td style="padding: 5px 15px 5px 5px;border: 1px solid grey;"><ul style="margin: 0; padding-left: 20px;">';
    //                 foreach ($siteLocations as $locationId) {
    //                     $formattedData .= '<li>' . ucwords($locations[$locationId] ?? 'N/A') . '</li>';
    //                 }
    //                 $formattedData .= '</ul></td>';
    //                 $formattedData .= '</tr>';
    //             }
    //             $formattedData .= '</table>';
    //             return $formattedData;
    //         })
    //         ->addColumn('action', function ($EmployeeLocationAllocation) {
    //                 $EmployeeLocationAllocationId = $EmployeeLocationAllocation->id;
    //                 $logId = $EmployeeLocationAllocation->logid;
    //                 $Rights = $this->rights;
    //                 $edit = explode(',', $Rights->employee_inventory_location_allocation)[2];
    //                 $actionButtons = '';
    //                 if ($edit == 1) {
    //                     $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-serviceallocation" data-serviceallocation-id="'.$EmployeeLocationAllocationId.'">'
    //                     . '<i class="fa fa-edit"></i> Edit'
    //                     . '</button>';
    //                 }
    //                 $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
    //                 . '<i class="fa fa-eye"></i> View Logs'
    //                 . '</button>';

    //                 return $EmployeeLocationAllocation->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

    //         })
    //         ->editColumn('status', function ($EmployeeLocationAllocation) {
    //             $rights = $this->rights;
    //             $updateStatus = explode(',', $rights->employee_inventory_location_allocation)[3];
    //             return $updateStatus == 1 ? ($EmployeeLocationAllocation->status ? '<span class="label label-success emplocation_status cursor-pointer" data-id="'.$EmployeeLocationAllocation->id.'" data-status="'.$EmployeeLocationAllocation->status.'">Active</span>' : '<span class="label label-danger emplocation_status cursor-pointer" data-id="'.$EmployeeLocationAllocation->id.'" data-status="'.$EmployeeLocationAllocation->status.'">Inactive</span>') : ($EmployeeLocationAllocation->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

    //         })
    //         ->rawColumns(['action', 'status', 'allocatioLocations',
    //         'id'])
    //         ->make(true);
    // }

    // public function UpdateAllocatedEmpLocationStatus(Request $request)
    // {
    //     $rights = $this->rights;
    //     $UpdateStatus = explode(',', $rights->employee_inventory_location_allocation)[3];
    //     if($UpdateStatus == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $EmployeeLocationAllocationID = $request->input('id');
    //     $Status = $request->input('status');
    //     $CurrentTimestamp = $this->currentDatetime;
    //     $EmployeeLocationAllocation = EmployeeLocationAllocation::find($EmployeeLocationAllocationID);

    //     if($Status == 0)
    //     {
    //         $UpdateStatus = 1;
    //         $statusLog = 'Active';
    //         $EmployeeLocationAllocation->effective_timestamp = $CurrentTimestamp;
    //     }
    //     else{
    //         $UpdateStatus = 0;
    //         $statusLog = 'Inactive';

    //     }
    //     $EmployeeLocationAllocation->status = $UpdateStatus;
    //     $EmployeeLocationAllocation->last_updated = $CurrentTimestamp;

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $logs = Logs::create([
    //         'module' => 'hr',
    //         'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
    //         'event' => 'update',
    //         'timestamp' => $this->currentDatetime,
    //     ]);
    //     $EmployeeLocationAllocationLog = EmployeeLocationAllocation::where('id', $EmployeeLocationAllocationID)->first();
    //     $logIds = $EmployeeLocationAllocationLog->logid ? explode(',', $EmployeeLocationAllocationLog->logid) : [];
    //     $logIds[] = $logs->id;
    //     $EmployeeLocationAllocationLog->logid = implode(',', $logIds);
    //     $EmployeeLocationAllocationLog->save();

    //     $EmployeeLocationAllocation->save();
    //     return response()->json(['success' => true, 200]);
    // }

}
