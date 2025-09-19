<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\UserRole;
use App\Models\Users;
use App\Models\Organization;
use App\Models\Logs;
use App\Models\Modules;
use App\Models\Employee;
use App\Models\Rights;
use App\Http\Requests\AddRoleRequest;
use App\Http\Requests\AddUserRequest;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\UserRegistration;
use App\Mail\UserEmailUpdate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UserController extends Controller
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

    public function Home()
    {
        $user = auth()->user();
        $orgID = $user->org_id;
        $banner = Organization::where('id', $orgID)->value('banner');
        $bannerPath = 'assets/org/' .$orgID.'_'.$banner;
        $banner = asset($bannerPath);
        return view('dashboard.home', compact('user','banner'));
    }

    public function viewRoles()
    {
        $colName = 'user_roles';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $roles = UserRole::all();
        return view('dashboard.role', compact('roles','user'));
    }
    public function GetRolesData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->user_roles)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $roles = UserRole::select(['id', 'role', 'remarks', 'status','logid',
        'effective_timestamp','user_id','last_updated','timestamp'])
        ->where('id', '!=', '1')
        ->orderBy('id', 'desc');
        // ->get();

        // return DataTables::of($roles)
        return DataTables::eloquent($roles)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('effective_timestamp', 'like', "%{$search}%")
                        ->orWhere('last_updated', 'like', "%{$search}%")
                        ->orWhere('timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($role) {
                return $role->id;
            })
            ->editColumn('id', function ($role) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $roleName = $role->role;
                $ModuleCode = 'URR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $roleName))));
                $idStr = str_pad($role->id, 4, "0", STR_PAD_LEFT); // Pad the id with leading zeros
                $effectiveDate = Carbon::createFromTimestamp($role->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($role->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($role->last_updated)->format('l d F Y - h:i A');
                $RoleCode = $ModuleCode.'-'.$firstLetters.'-'.$idStr;
                $createdByName = getUserNameById($role->user_id);
                $createdInfo = "<b>Created By:</b> " . ucwords($createdByName) . "  <br> <b>Effective Date&amp;Time:</b> "
                    . $effectiveDate . " <br><b>RecordedAt:</b> " . $timestamp ." <br><b>LastUpdated:</b>
                    " . $lastUpdated;

                return $RoleCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($role) {
                $roleId = $role->id;
                $logId = $role->logid;
                $sessionRights = $this->rights;
                $edit = explode(',', $sessionRights->user_roles)[2];
                $add = explode(',', $sessionRights->user_roles)[0];
                $updateRights = explode(',', $sessionRights->user_roles)[5];
                $asignRights = explode(',', $sessionRights->user_roles)[4];

                $actionButtons = '';

                $rights = DB::table('rights')->where('role_id', $roleId)->first();
                if ($rights) {
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-role" data-role-id="'.$roleId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    
                    
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    if ($updateRights == 1) {
                        $actionButtons .= '<a href="' . route('update-rights-setup', ['id' => $role->id]) . '"><button type="button" class="btn btn-outline-secondary mt-2">'
                        . '<i class="fa fa-check"></i> Update Rights'
                        . '</button></a>';
                    }

                    return $role->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                
                } else {
                    if ($asignRights == 1)
                    {
                        return  $role->status ? '<a href="' . route('rights-setup', ['id' => $role->id]) . '"><button type="button" class="btn waves-effect waves-light btn-block btn-info" style="width:70%"><i class="fa fa-key"></i> Assign Rights</button></a>' : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                    }
                    else{
                        return '<code>Permission Denied</code>';
                    }
                }
            })
            ->editColumn('status', function ($role) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->user_roles)[3];
                return $updateStatus == 1 ? ($role->status ? '<span class="label label-success role_status cursor-pointer" data-id="'.$role->id.'" data-status="'.$role->status.'">Active</span>' : '<span class="label label-danger role_status cursor-pointer" data-id="'.$role->id.'" data-status="'.$role->status.'">Inactive</span>') : ($role->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function AddRole(AddRoleRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->user_roles)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        
        $roleName = strtolower(trim($request->input('role_name')));
        $remarks = trim($request->input('role_remarks'));
        $roleEdt = $request->input('role_edt');
        $roleEdt = Carbon::createFromFormat('l d F Y - h:i A', $roleEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($roleEdt)->setTimezone('Asia/Karachi');
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

        $roleExists = UserRole::where('role', $roleName)->exists();
        if ($roleExists) {
            return response()->json(['info' => 'Role already exists. Please write a different name.']);
        }
        else
        {
            $UserRole = new UserRole();
            $UserRole->role = $roleName;
            $UserRole->remarks = $remarks;
            $UserRole->status = $status;
            $UserRole->effective_timestamp = $roleEdt;
            $UserRole->user_id = $sessionId;
            $UserRole->last_updated = $last_updated;
            $UserRole->timestamp = $timestamp;
            $UserRole->save();
            if (empty($UserRole->id)) {
                return response()->json(['error' => 'Failed to create role.']);
            }
            $logs = Logs::create([
                'module' => 'role',
                'content' => "'{$roleName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $UserRole->logid = $logs->id;
            $UserRole->save();

            $InsertedID = UserRole::find($UserRole->id);
            return response()->json(['success' => 'Role created successfully']);
        }
    }
    
    public function UpdateRoleStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->user_roles)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $RoleID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $role = UserRole::find($RoleID);
        // $TableName = (new UserRole())->getTable();

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $role->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $role->effective_timestamp = 0;
        }
        // Find the role by ID
        $role->status = $UpdateStatus;
        $role->last_updated = $CurrentTimestamp;

        $logs = Logs::create([
            'module' => 'role',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $userRole = UserRole::where('id', $RoleID)->first();
        $logIds = $userRole->logid ? explode(',', $userRole->logid) : [];
        $logIds[] = $logs->id;
        $userRole->logid = implode(',', $logIds);
        $userRole->save();

        $role->save();
        return response()->json(['success' => true, 200]);

    }

    public function UpdateRoleModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->user_roles)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $role = UserRole::find($id);
        $remarks = $role->remarks;
        $effective_timestamp = $role->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');
        $role_name = $role->role;

        $data = [
            'id' => $id,
            'remarks' => $remarks,
            'effective_timestamp' => $effective_timestamp,
            'role' => $role_name,
        ];

        return response()->json($data);
    }

    public function UpdateRole(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->user_roles)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $role = UserRole::findOrFail($id);
        // Update the role with the new values
        $roleName = $role->role;
        $role->role = $request->input('u_role');
        $role->remarks = $request->input('u_remarks');
        $effective_date = $request->input('u_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // $role->effective_timestamp = $effective_date->timestamp;
        $role->effective_timestamp = $effective_date;
        $role->last_updated = $this->currentDatetime;
        $role->status = $status;

        $role->save();

        if (empty($role->id)) {
            return response()->json(['error' => 'Failed to update role. Please try again']);
        }
        else{
            $logs = Logs::create([
                'module' => 'role',
                'content' => "Data has been updated by '{$sessionName}'",
                'event' => 'update',
                'timestamp' => $this->currentDatetime,
            ]);
            $userRole = UserRole::where('id', $id)->first();
            $logIds = $userRole->logid ? explode(',', $userRole->logid) : [];
            $logIds[] = $logs->id;
            $userRole->logid = implode(',', $logIds);
            $userRole->save();

            return response()->json(['success' => 'Role updated successfully']);
        }

    }

    public function MyProfile()
    {
        $user = auth()->user();
        $roleid = $user->role_id;
        $roleData = UserRole::find($roleid);
        $orgid = $user->org_id;

        if($orgid != 0)
        {
            $orgData = Organization::find($orgid);
        }
        else {
            $orgData = '';
        }
        $effective_timestamp = $user->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');
        $timestamp = $user->timestamp;
        $timestamp = Carbon::createFromTimestamp($timestamp);
        $timestamp = $timestamp->format('l d F Y - h:i A');
        $last_updated = $user->last_updated;
        $last_updated = Carbon::createFromTimestamp($last_updated);
        $last_updated = $last_updated->format('l d F Y - h:i A');


        return view('dashboard.profile', compact('roleData','orgData', 'user', 'effective_timestamp','timestamp','last_updated'));
    }

    public function UpdateProfile(Request $request, $id)
    {
        $Users = Users::findOrFail($id);
        $userImg = $request->file('userImg');

        if (isset($userImg)) {
            $imgFileName = $userImg->getClientOriginalName();
            $Users->image = $imgFileName;
            $imgFileName = $id . '_' . $imgFileName;
            $userImg->move(public_path('assets/users'), $imgFileName);

        }
        $Users->last_updated = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Users->save();

        if (empty($Users->id)) {
            return response()->json(['error' => 'Failed to update image. Please try again']);
        }
        // $data = "Data has been updated by '{$sessionName}'";
        $logs = Logs::create([
            'module' => 'user',
            'content' => "Profile Picture has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);

        $UserLog = Users::where('id', $id)->first();
        $logIds = $UserLog->logid ? explode(',', $UserLog->logid) : [];
        $logIds[] = $logs->id;
        $UserLog->logid = implode(',', $logIds);
        $UserLog->save();

        return response()->json(['success' => 'Profile Picture updated successfully']);


    }

    public function ViewRights($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->user_roles)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $user = auth()->user();
        $role = UserRole::find($id);
        $modules = Modules::all();
        $RightsExist = DB::table('rights')->where('role_id', $id)->first();
        if ($RightsExist) {
            $message = "Rights are already given to that role. Please click on Update Role to view and update the Rights of any Role.";
            $redirectUrl = route('user-roles');
            return response("<script>alert('$message'); window.location.href = '$redirectUrl';</script>");
        }

        return view('dashboard.rightsetup', compact('role','modules','user'));
    }

    public function AssignRights(Request $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->user_roles)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $roleId = $request->input('role_id');
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $dataToInsert = [
            'role_id' => $roleId,
            'user_id' => $sessionId,
            'timestamp' => $timestamp,
            'last_updated' => $last_updated,
        ];
        foreach ($request->except('role_id') as $checkboxName => $checkboxValues) {
            $commaseparatedValues = implode(',', $checkboxValues);
            $dataToInsert[$checkboxName] = $commaseparatedValues;
        }
        $Rights = Rights::create($dataToInsert);

        $logs = Logs::create([
            'module' => 'user',
            'content' => "Rights has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);

        $Rights->logid = $logs->id;
        $Rights->save();
        return response()->json(['success' => 'Rights Assigned successfully']);

    }

    public function UpdateRightsSetup($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->user_roles)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $role = UserRole::find($id);
        $user = auth()->user();
        $modules = Modules::all();
        $rights = DB::table('rights')->where('role_id', $id)->get();

        foreach ($rights as $right) {
            $rightsId = $right->id;
        }

        return view('dashboard.updaterights', compact('rightsId','user','rights','role','modules'));

    }

    public function UpdateRights(Request $request)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->user_roles)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $rights = $this->rights;
        $edit = explode(',', $rights->user_roles)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $roleId = $request->input('role_id');
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;
        $timestamp = $this->currentDatetime;
        $existingRights = Rights::where('role_id', $roleId)->first();

        if ($existingRights) {
            $updatedData = [
                'role_id' => $roleId,
                'last_updated' => $timestamp,
            ];
            foreach ($request->except('role_id') as $columnName => $columnValue) {
                $updatedData[$columnName] = implode(',', $columnValue);
            }
            $existingRights->update($updatedData);

            $logs = Logs::create([
                'module' => 'user',
                'content' => "Rights has been updated by '{$sessionName}'",
                'event' => 'update',
                'timestamp' => $timestamp,
            ]);

            $existingRights->logid = $logs->id;
            $existingRights->save();

            return response()->json(['success' => 'Rights updated successfully']);
        } else {
            return response()->json(['error' => 'No rights found for the specified role'], 404);
        }
    }

    public function UpdateSelectedRole(Request $request)
    {
        $roleId = $request->input('roleId');
        $roles = UserRole::whereNotIn('id', [$roleId, 1])
                 ->where('status', 1)
                 ->get();
        return response()->json($roles);
    }
    public function viewUser()
    {
        // $session = auth()->user();
        $session =  $this->sessionUser;
        $sessionOrg = $session->org_id;
        $colName = 'user_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $allroles = UserRole::select('role.*', 'rights.role_id')
        ->join('rights', 'rights.role_id', '=', 'role.id')
        ->where('role.status', 1)
        ->where('role.id', '!=', '1')
        ->orderBy('role.id', 'desc')
        ->get();
        $organizations = Organization::where('status', 1)
                ->orderBy('id', 'desc')
                ->get();
        return view('dashboard.user', compact('allroles','organizations'));
    }
    public function GetUserData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->user_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        // $users = DB::table('user')
        // ->select('user.*', 'role.role as rolename', 'employee.name as empname',
        // 'org_site.name as sitename')
        // ->join('role', 'role.id', '=', 'user.role_id')
        // ->leftJoin('employee', 'employee.id', '=', 'user.emp_id')
        // ->leftJoin('org_site', 'org_site.id', '=', 'employee.site_id')
        // ->where('role.id', '!=', '1');

        $users = Users::select('user.*', 'role.role as rolename', 'employee.name as empname', 'org_site.name as sitename')
        ->join('role', 'role.id', '=', 'user.role_id')
        ->leftJoin('employee', 'employee.id', '=', 'user.emp_id')
        ->leftJoin('org_site', 'org_site.id', '=', 'employee.site_id')
        ->where('role.id', '!=', '1')
        ->orderBy('user.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $users->where('user.org_id', '=', $sessionOrg);
        }
        
        // Check if session user is employee and add site filtering
        if($session->is_employee == 1 && $session->site_enabled == 0) {
            $sessionSiteIds = session('sites', []);
            if(!empty($sessionSiteIds)) {
                $users->whereIn('employee.site_id', $sessionSiteIds);
            }
        }
        
        $users = $users;
        // ->get();
        // return DataTables::of($users)
        return DataTables::eloquent($users)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('user.name', 'like', "%{$search}%")
                            ->orWhere('user.email', 'like', "%{$search}%")
                            ->orWhere('role.role', 'like', "%{$search}%")
                            ->orWhere('employee.name', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($user) {
                return $user->id;
            })
            ->editColumn('id', function ($user) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgId = $user->org_id;
                    $orgName = Organization::where('id', $orgId)->value('organization');
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($orgName);
                }
                
                $rights = $this->rights;
                $view = explode(',', $rights->user_setup)[1];
                if($view == 0)
                {
                    abort(403, 'Forbidden');
                }
                $EmploymentStatus = $user->is_employee ? '<hr class="mt-1 mb-1"><b>Employment Status:</b> <code class="p-0">Yes</code>' : '<hr class="mt-1 mb-1"><b>Employment Status:</b> <code class="p-0">No</code>';
                $ModuleCode = 'USR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $user->name))));
                $idStr = str_pad($user->id, 4, "0", STR_PAD_LEFT); // Pad the id with leading zeros
                $effectiveDate = Carbon::createFromTimestamp($user->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($user->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($user->last_updated)->format('l d F Y - h:i A');
                $UserCode = $ModuleCode.'-'.$firstLetters.'-'.$idStr;
                $createdByName = getUserNameById($user->user_id);
                $createdInfo = "<b>Created By:</b> " . ucwords($createdByName) . " <br> <b>Effective Date&amp;Time:</b> "
                    . $effectiveDate . " <br><b>RecordedAt:</b> " . $timestamp ." <br><b>LastUpdated:</b>
                    " . $lastUpdated;

                $siteEnabled = $user->site_enabled ? '<hr class="mt-1 mb-1"><b>Sites Access:</b> <code class="p-0">Yes</code>' : '<hr class="mt-1 mb-1"><b>Sites Access:</b> <code class="p-0">No</code>';

                return $UserCode.$orgName
                    . (isset($user->sitename) && !is_null($user->sitename) ? '<br> <b>Site:</b> '.ucwords($user->sitename) : '')
                    .''.$EmploymentStatus.''
                    .''.$siteEnabled.''
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($user) {
                    $userId = $user->id;
                    $logId = $user->logid;
                    $rights = $this->rights;
                    $edit = explode(',', $rights->user_setup)[2];
                    $actionButtons = '';

                    if ($edit == 1) {
                        // Add edit button if user has edit permission
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-user" data-user-id="'.$userId.'">'
                            . '<i class="fa fa-edit"></i> Edit'
                            . '</button>';
                    }
                
                    // Add view logs button
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                        . '<i class="fa fa-eye"></i> View Logs'
                        . '</button>';

                    return $user->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($user) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->user_setup)[3];
                return $updateStatus == 1 ? ($user->status ? '<span class="label label-success user_status cursor-pointer" data-id="'.$user->id.'" data-status="'.$user->status.'">Active</span>' : '<span class="label label-danger user_status cursor-pointer" data-id="'.$user->id.'" data-status="'.$user->status.'">Inactive</span>') : ($user->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }
    public function AddUser(AddUserRequest $request)
    {
        $rights = $this->rights;
        $userSetup = explode(',', $rights->user_setup);
        $add = $userSetup[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $isEmployee = trim($request->input('isEmployee'));
        $siteEnabled = trim($request->input('siteStatus'));
        $username = strtolower(trim($request->input('username')));
        $useremail = strtolower(trim($request->input('useremail')));
        $userRoleId = trim($request->input('userRole'));
        $userOrgId = trim($request->input('userOrg'));
        $userEmpId = trim($request->input('userEmp'));
        $userEdt = $request->input('userEdt');
        

        $userExists = Users::where('email', $useremail)->exists();
        if ($userExists) {
            return response()->json(['info' => 'User already exists. Please try with different email.']);
        }

        if ($isEmployee == 'on') {
            $errors = [];
            if($userEmpId == '')
            {
                $errors['userEmp'] = 'Please select an Employee';
            }
            if (!empty($errors)) {
                return response()->json($errors);
            }
            $isEmployee = 1;
            $userEmpId = trim($request->input('userEmp'));
            $employee = DB::table('employee')
                    ->select('employee.*','org_site.name as siteName')
                    ->join('org_site', 'employee.site_id', '=', 'org_site.id')
                    ->where('employee.id', '=', $userEmpId)
                    ->first();
            $empName = $employee->name;
            $siteName = $employee->siteName;
        }
        else {
            if($username == '')
            {
                $errors['username'] = 'Please enter a User Name';
            }
            if($useremail == '')
            {
                $errors['useremail'] = 'Please enter a valid Email Address';
            }

            if (!empty($errors)) {
                return response()->json($errors);
            }

            $isEmployee = 0;
            $userEmpId = 0;
            $empName = '';
            $siteName = '';
      
            $employeeExists = Employee::where('email', $useremail)->exists();
            if ($employeeExists) {
                return response()->json(['info' => 'This email is already linked to an employee. Please use a different one.']);
            }
        }

        $orgName = Organization::where('id', $userOrgId)->value('organization');
        $userEdt = Carbon::createFromFormat('l d F Y - h:i A', $userEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($userEdt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
            $emailStatus = 'Active';
        } else {
            $status = 0; //Inactive
            $emailStatus = 'Inactive';
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;



        $pwd = Str::random(8);
        $password = Hash::make($pwd);

        $Users = new Users();
        $Users->name = $username;
        $Users->email = $useremail;
        $Users->password = $password;
        $Users->role_id = $userRoleId;
        $Users->is_employee = $isEmployee;
        $Users->site_enabled = $siteEnabled;
        $Users->org_id = $userOrgId;
        $Users->user_id = $sessionId;
        $Users->emp_id = $userEmpId;
        $Users->status = $status;
        $Users->effective_timestamp = $userEdt;
        $Users->timestamp = $timestamp;
        $Users->last_updated = $last_updated;

        $roleName = UserRole::find($userRoleId)->role;
        $emailTimestamp = Carbon::createFromTimestamp($timestamp);
        $emailTimestamp = $emailTimestamp->format('l d F Y - h:i A');
        $emailEdt = $request->input('userEdt');
        try {

            Mail::to($useremail)->send(new UserRegistration($username, $useremail, $pwd,
                $roleName, $orgName, $siteName, $empName, $emailStatus,
                $emailEdt, $emailTimestamp));
            $Users->save();
        }
        catch (TransportExceptionInterface $ex)
        {
            return response()->json(['info' => 'There is an issue with email. Please try again!.']);
        }

        if (empty($Users->id)) {
            return response()->json(['error' => 'Failed to create User.']);
        }
        $logs = Logs::create([
            'module' => 'user',
            'content' => "'{$username}' has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $Users->logid = $logs->id;
        $Users->save();
        return response()->json(['success' => 'User created successfully']);
    }
    public function UpdateUserStatus(Request $request)
    {
        $rights = $this->rights;
        $updateStatus = explode(',', $rights->user_setup)[3];
        if($updateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $userId = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $user = Users::find($userId);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $user->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $user->effective_timestamp = 0;
        }
        // Find the role by ID
        $user->status = $UpdateStatus;
        $user->last_updated = $CurrentTimestamp;

        $logs = Logs::create([
            'module' => 'user',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $Users = Users::where('id', $userId)->first();
        $logIds = $Users->logid ? explode(',', $Users->logid) : [];
        $logIds[] = $logs->id;
        $Users->logid = implode(',', $logIds);
        $Users->save();

        $user->save();
        return response()->json(['success' => true, 200]);

    }
    public function UpdateUserModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->user_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $user = DB::table('user')
        ->select('user.*', 'role.role as rolename', 'employee.name as empname',
        'org_site.name as sitename')
        ->join('role', 'role.id', '=', 'user.role_id')
        ->leftJoin('employee', 'employee.id', '=', 'user.emp_id')
        ->leftJoin('org_site', 'org_site.id', '=', 'employee.site_id')
        ->where('user.id', '=', $id)
        ->first();

        $name = $user->name;
        $email = $user->email;
        $siteEnabled = $user->site_enabled;
        $roleId = $user->role_id;
        $roleName = ucwords($user->rolename);
        $isEmployee = $user->is_employee;
        $orgId = $user->org_id;

        $orgName = Organization::where('id', $orgId)->value('organization');

        if($isEmployee === 0)
        {
            $empId = 0;
            $empName = '';
        }
        else {
            $empId = $user->emp_id;
            $empName = $user->empname;
        }
        $effective_timestamp = $user->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'name' => ucwords($name),
            'email' => $email,
            'siteEnabled' => $siteEnabled,
            'rolename' => $roleName,
            'orgName' => $orgName,
            'orgId' => $orgId,
            'roleId' => $roleId,
            'empName' => $empName,
            'empId' => $empId,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }
    public function UpdateUser(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->user_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        
        $user = Users::findOrFail($id);
        $userOrg = $request->input('user_org');

        $oldEmail = $user->email;
        $newEmail = $request->input('user_email');
        $user->name = $request->input('user_name');
        $user->email = $request->input('user_email');
        $user->role_id = $request->input('user_role');
        if (isset($userOrg)) {
            $user->org_id = $userOrg;
        }       
        $user->emp_id = $request->input('user_emp');
        $siteEnabled = $request->input('u_siteStatus');
        //  ($siteEnabled == 'on') ? 1 : 0
        $user->site_enabled =$siteEnabled;
        $effective_date = $request->input('user_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        $userName = $request->input('user_name');

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $user->effective_timestamp = $effective_date;
        $user->last_updated = $this->currentDatetime;
        $user->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        if($oldEmail != $newEmail)
        {
            try {
                Mail::to($newEmail)->send(new UserEmailUpdate($oldEmail, $newEmail, $userName));
            }
            catch (TransportExceptionInterface $ex)
            {
                return response()->json(['info' => 'There is an issue with email. Please try again!.']);
            }
        }

        $user->save();

        if (empty($user->id)) {
            return response()->json(['error' => 'Failed to update User. Please try again']);
        }

        $logsContent = [
                ["content" => "Update email notification has been sent to '{$newEmail}'", "event" => "email notification"],
                ["content" => "Data has been updated by '{$sessionName}'", "event" => "update"],
            ];

        $logIds = [];
        foreach ($logsContent as $logData) {
            $logs = Logs::create([
                'module' => 'user',
                'content' => $logData['content'],
                'event' => $logData['event'],
                'timestamp' => $this->currentDatetime,
            ]);
            $logIds[] = $logs->id;
        }

        $UserLog = Users::where('id', $user->id)->first();
        $existingLogIds = $UserLog->logid ? explode(',', $UserLog->logid) : [];
        $logIds = array_merge($existingLogIds, $logIds);
        $UserLog->logid = implode(',', $logIds);
        $UserLog->save();

        return response()->json(['success' => 'User updated successfully']);
    }

}
