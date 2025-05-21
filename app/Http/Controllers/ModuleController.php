<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Modules;
use App\Http\Requests\ModuleRequest;


class ModuleController extends Controller
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

    public function ShowModules()
    {
        $colName = 'modules';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        return view('dashboard.module', compact('user'));
    }

    public function AddModule(ModuleRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->modules)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Parent = trim($request->input('parent_module'));
        if ($Parent === '__null__') {
            $Parent = null;
        }
        $Name = trim(ucwords($request->input('module_name')));
        $moduleName = strtolower(str_replace(' ', '-', $Name));

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $ModuleExists = Modules::where('name', $Name)
        ->Where('parent', $Parent)
        ->exists();
        if ($ModuleExists) {
            return response()->json(['info' => 'This Module is already exists.']);
        }
        else
        {
            $Module = new Modules();
            $Module->name = $moduleName;
            $Module->parent = $Parent;
            $Module->user_id = $sessionId;
            $Module->last_updated = $last_updated;
            $Module->timestamp = $timestamp;
            $Module->save();

            if (empty($Module->id)) {
                return response()->json(['error' => 'Failed to create Module.']);
            }

            $logs = Logs::create([
                'module' => 'module',
                'content' => "Module Name '{$Name}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Module->logid = $logs->id;
            $Module->save();
            return response()->json(['success' => 'Module created successfully']);
        }

    }

    public function GetModuleData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->modules)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ModuleData = Modules::select('*')->orderBy('id', 'asc');
        // ->get()
        // return DataTables::of($ModuleData)
        return DataTables::eloquent($ModuleData)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $search = str_replace(' ', '-', $search);
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('parent', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Module) {
                return $Module->id;  
            })
            ->editColumn('id', function ($Module) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;

                $timestamp = Carbon::createFromTimestamp($Module->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Module->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($Module->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;
                $Code = $Module->id;

                return $Code.'<hr class="mt-1 mb-2"><span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                        . '<i class="fa fa-toggle-right"></i> View Details'
                        . '</span>';
            })
            ->addColumn('name', function ($Module) {
                $name = $Module->name;
                $moduleName = ucwords(str_replace('-', ' ', $name));
                return $moduleName;
            })
            ->addColumn('action', function ($Module) {
                    $logId = $Module->logid;
                    return '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">
                    <i class="fa fa-eye"></i> View Logs
                    </button>';
            })
           
            ->rawColumns(['action', 'id','name'])
            ->make(true);
    }
}
