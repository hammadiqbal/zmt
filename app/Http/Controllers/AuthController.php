<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Logs;
use App\Models\Users;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\pwdUpdate;
use App\Mail\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    private $assignedSites;

    public function __construct()
    {
        $this->currentDatetime = Carbon::now('Asia/Karachi')->timestamp;
    }

    public function viewLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $user = auth()->user();
        $CurrentTimeStamp = Carbon::createFromTimestamp($this->currentDatetime)->format('l d F Y - h:i A');
        return view('auth', compact('user','CurrentTimeStamp'));
    }

    public function AuthAdmin()
    {
        $user = auth()->user();

        return view('dashboard.index', compact('user'));
    }

    public function AuthOrg()
    {
        return view('organization.index');
    }

    public function Auth(AuthRequest $request)
    {
        if (Auth::guard('web')->check()) {
            return response()->json(['loggedin' => route('dashboard')]);
        }

        $credentials = $request->only('userlgn', 'userpwd');
        // $userField = filter_var($credentials['userlgn'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $userField = 'email'; 
        $credentials['password'] = $credentials['userpwd'];
        $credentials['remember'] = $request->has('remember');

        if (Auth::guard('web')->attempt([$userField => $credentials['userlgn'], 'password' => $credentials['password']], $credentials['remember'] )) {
            $user = Users::select([
                'user.id',
                'user.name',
                'user.email',
                'user.password',
                'user.role_id',
                'user.org_id',
                'user.is_employee',
                'user.site_enabled',
                'user.emp_id',
                'user.user_id',
                'user.image',
                'user.status',
                'user.effective_timestamp',
                'user.timestamp',
                'user.last_updated',
                'organization.organization as orgName', 
                'organization.banner as banner',
                'organization.logo as logo' 
            ])
            ->leftJoin('organization', 'user.org_id', '=', 'organization.id')
            ->where('user.' . $userField, $credentials['userlgn']) 
            ->where('user.status', 1)
            ->first();

            if (!empty($user)) {
                $role = DB::table('role')
                        ->select('role', 'remarks') 
                        ->where('id', $user->role_id) 
                        ->first();
                $rights = DB::table('rights')
                            ->select('rights.*')
                            ->where('rights.role_id', $user->role_id) 
                            ->first();
                // Check if user is an employee and collect site IDs from all tables
                $employeeSiteIds = [];
                if ($user->is_employee == 1) {
                    // 1. Get site_id from employee table (single value)
                    $employeeSites = DB::table('employee')
                        ->select('site_id')
                        ->where('id', $user->emp_id)
                        ->whereNotNull('site_id')
                        ->get();
                    
                    foreach ($employeeSites as $site) {
                        if (!empty($site->site_id)) {
                            $employeeSiteIds[] = $site->site_id;
                        }
                    }
                    
                    // 2. Get site_id from emp_cc table (can be comma-separated)
                    $empCcSites = DB::table('emp_cc')
                        ->select('site_id')
                        ->where('emp_id', $user->emp_id)
                        ->whereNotNull('site_id')
                        ->get();
                    
                    foreach ($empCcSites as $site) {
                        if (!empty($site->site_id)) {
                            // Handle comma-separated values
                            $siteIds = explode(',', $site->site_id);
                            foreach ($siteIds as $siteId) {
                                $siteId = trim($siteId);
                                if (!empty($siteId)) {
                                    $employeeSiteIds[] = $siteId;
                                }
                            }
                        }
                    }
                    
                    // 3. Get site_id from emp_service_allocation table (single value)
                    $serviceAllocationSites = DB::table('emp_service_allocation')
                        ->select('site_id')
                        ->where('emp_id', $user->emp_id)
                        ->whereNotNull('site_id')
                        ->get();
                    
                    foreach ($serviceAllocationSites as $site) {
                        if (!empty($site->site_id)) {
                            $employeeSiteIds[] = $site->site_id;
                        }
                    }
                    
                    $inventoryLocationSites = DB::table('emp_inventory_location')
                        ->select('location_site')
                        ->where('emp_id', $user->emp_id)
                        ->whereNotNull('location_site')
                        ->get();
                    
                    foreach ($inventoryLocationSites as $site) {
                        if (!empty($site->location_site)) {
                            $siteIds = explode(',', $site->location_site);
                            foreach ($siteIds as $siteId) {
                                $siteId = trim($siteId);
                                if (!empty($siteId)) {
                                    $employeeSiteIds[] = $siteId;
                                }
                            }
                        }
                    }
                    
                    // Remove duplicates and convert to integers
                    $employeeSiteIds = array_unique(array_map('intval', $employeeSiteIds));
                    $employeeSiteIds = array_values($employeeSiteIds); // Re-index array
                }

                session()->put([
                    'user' => $user,
                    'role' => $role,
                    'rights' => $rights,
                    'sites' => $employeeSiteIds,
                ]);

                $logs = Logs::create([
                    'module' => 'login',
                    'content' => "'{$user->name}' has successfully logged in.",
                    'event' => 'login',
                    'timestamp' => $this->currentDatetime,
                ]);
                $Users = Users::where('id', $user->id)->first();
                $logIds = $Users->logid ? explode(',', $Users->logid) : [];
                $logIds[] = $logs->id;
                $Users->logid = implode(',', $logIds);
                $Users->save();

                return response()->json(['success' => route('dashboard')]);
            }
            else {
                return response()->json(['info' => 'Your Status is currently InActive. Please contact the system administrator for further details.']);
            }
        } else {
            return response()->json(['error' => 'Invalid credentials! Please try again.']);
        }
    }


    public function UpdatePwd(Request $request)
    {
        $request->validate([
        'u_pwd' => 'required|min:8',
        'u_c_pwd' => 'required|same:u_pwd',
        ], [
            'u_pwd.required' => 'This field is required.',
            'u_pwd.min' => 'Password must be at least 8 characters.',
            'u_c_pwd.required' => 'This field is required.',
            'u_c_pwd.same' => 'The confirm password must match the new password.',
        ]);
        $session = auth()->user();
        if ($session !== null) {
            $sessionName = $session->name;
            $sessionId = $session->id;
            $user = Users::where('id', $sessionId)->first();
            $sessionEmail = $session->email;
            $user->password = Hash::make($request->input('u_pwd'));
        } else {

            $sessionId = $request->input('userId');
            $user = Users::where('id', $sessionId)->first();
            $sessionName = $user->name;
            $sessionEmail = $user->email;
            $user->password_reset_token = null;
            $user->password = Hash::make($request->input('u_pwd'));

        }

        $CurrentTimestamp = $this->currentDatetime;


        $logs = Logs::create([
            'module' => 'profile',
            'content' => "Password Updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);

        $Users = Users::where('id', $sessionId)->first();
        $logIds = $Users->logid ? explode(',', $Users->logid) : [];
        $logIds[] = $logs->id;
        $Users->logid = implode(',', $logIds);
        $Users->save();

        $timestamp = $this->currentDatetime;
        $emailTimestamp = Carbon::createFromTimestamp($timestamp);
        $emailTimestamp = $emailTimestamp->format('l d F Y - h:i A');

        Mail::to($sessionEmail)->send(new pwdUpdate($sessionName, $emailTimestamp));
        $Users->save();

        $user->save();
        return response()->json(['success' => 'Password updated succesfully.']);
    }

    public function logout(Request $request)
    {
        $session = auth()->user();
        if ($session !== null) {
            $sessionName = $session->name;
            $sessionId = $session->id;
            $logs = Logs::create([
                'module' => 'logout',
                'content' => "'{$session->name}' has successfully logged out.",
                'event' => 'logout',
                'timestamp' => $this->currentDatetime,
            ]);
            $Users = Users::where('id', $session->id)->first();
            $logIds = $Users->logid ? explode(',', $Users->logid) : [];
            $logIds[] = $logs->id;
            $Users->logid = implode(',', $logIds);
            $Users->save();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return redirect('/');
    }

    public function ForgetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']
        , [
            'email.required' => 'This field is required.',
            'email.email' => 'Invalid email address.',
        ]);
        $email = $request->input('email');
        $user = Users::where('email', $email)->first();
        if ($user) {
            $token = Str::random(60);
            $user->password_reset_token = $token;
            $user->save();
            $userName = $user->name;
            Mail::to($user->email)->send(new ResetPasswordNotification($token,$userName));
            return response()->json(['success' => 'Password reset link has been sent to your email.']);
        }

        else
        {
            return response()->json(['error' => 'Invalid Email.']);
        }

    }
    public function ResetPassword()
    {
        $token = request('token');
        if (! $token) {
            abort(404);
        }
        $resetUser = Users::where('password_reset_token', $token)->first();
        if (! $resetUser) {
            abort(404);
        }
        return view('reset', compact('resetUser'));
    }
}
