<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProvinceRequest;
use App\Http\Requests\DivisionRequest;
use App\Http\Requests\DistrictRequest;
use App\Models\Province;
use App\Models\District;
use App\Models\Division;
use App\Models\Logs;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;



class TerritoryController extends Controller
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

    public function viewProvince()
    {
        $colName = 'province';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();

        return view('dashboard.province', compact('user'));
    }

    public function AddProvince(ProvinceRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->province)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $province = trim($request->input('province'));
        $pedt = $request->input('pedt');

        $pedt = Carbon::createFromFormat('l d F Y - h:i A', $pedt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($pedt)->setTimezone('Asia/Karachi');
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

        $ProvinceExist = Province::where('name', $province)->exists();
        if ($ProvinceExist) {
            return response()->json(['info' => 'Province already exists. Please write a different province.']);
        }
        else
        {
            $Province = new Province();
            $Province->name = $province;
            $Province->status = $status;
            $Province->user_id = $sessionId;
            $Province->effective_timestamp = $pedt;
            $Province->timestamp = $timestamp;
            $Province->last_updated = $last_updated;
            $Province->save();
            $ProvinceName = ucfirst($province);
            if (empty($Province->id)) {
                return response()->json(['error' => 'Failed to create province.']);
            }

            $logs = Logs::create([
                'module' => 'province',
                'content' => "'{$ProvinceName}' added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $this->currentDatetime,
            ]);

            $provinceLog = Province::where('id', $Province->id)->first();
            $logIds = $provinceLog->logid ? explode(',', $provinceLog->logid) : [];
            $logIds[] = $logs->id;
            $provinceLog->logid = implode(',', $logIds);
            $provinceLog->save();

            $InsertedID = Province::find($Province->id);
            return response()->json(['success' => 'Province created successfully']);
        }


    }

    public function viewDivision()
    {
        $colName = 'divisions';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();

        $currentDate = $this->currentDatetime;
        $ProvinceData = Province::where('status', 1)->select('id', 'name')->get();        
        // ->where('status', 1)->get();
        // $ProvinceData = [];
        // foreach ($provinces as $province)
        // {
        //     $province_id = $province->id;
        //     $province_name = $province->name;
        //     $ProvinceData[] = [
        //         'province_id' => $province_id,
        //         'province_name' => ucfirst($province_name),
        //     ];

        // }

        return view('dashboard.division', compact('ProvinceData','user'));
    }

    public function AddDivision(DivisionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->divisions)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        // $data = $request->all();
        $division = trim($request->input('division'));
        $province = $request->input('province');
        $dedt = $request->input('dedt');
        $dedt = Carbon::createFromFormat('l d F Y - h:i A', $dedt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($dedt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }


        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $DivisionExist = Division::where('name', $division)->exists();
        if ($DivisionExist) {
            return response()->json(['info' => 'Division already exists. Please write a different province.']);
        }
        else
        {
            $Division = new Division();
            $Division->name = $division;
            $Division->province_id = $province;
            $Division->status = $status;
            $Division->user_id = $sessionId;
            $Division->effective_timestamp = $dedt;
            $Division->timestamp = $timestamp;
            $Division->last_updated = $last_updated;
            $DivisionName = ucfirst($division);

            $Division->save();
            if (empty($Division->id)) {
                return response()->json(['error' => 'Failed to create division.']);
            }

            $logs = Logs::create([
                'module' => 'division',
                'content' => "'{$DivisionName}' added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $this->currentDatetime,
            ]);

            $divisionLog = Division::where('id', $Division->id)->first();
            $logIds = $divisionLog->logid ? explode(',', $divisionLog->logid) : [];
            $logIds[] = $logs->id;
            $divisionLog->logid = implode(',', $logIds);
            $divisionLog->save();
            $InsertedID = Division::find($Division->id);
            return response()->json(['success' => 'Division created successfully']);
        }


    }

    public function viewDistrict()
    {
        $colName = 'districts';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();

        $currentDate = $this->currentDatetime;
        $ProvinceData = Province::where('status', 1)->select('id', 'name')->get();        

        // $provinces = Province::where('effective_timestamp', '<=', $currentDate)
        // ->where('status', 1)->get();
        // $ProvinceData = [];
        // foreach ($provinces as $province)
        // {
        //     $province_id = $province->id;
        //     $province_name = $province->name;
        //     $ProvinceData[] = [
        //         'province_id' => $province_id,
        //         'province_name' => ucfirst($province_name),
        //     ];

        // }

        $DivisionData = Division::where('status', 1)->select('id', 'name')->get();        

        // $divisions = Division::where('effective_timestamp', '<=', $currentDate)
        // ->where('status', 1)->get();
        // $DivisionData = [];
        // foreach ($divisions as $division)
        // {
        //     $division_id = $division->id;
        //     $division_name = $division->name;
        //     $DivisionData[] = [
        //         'division_id' => $division_id,
        //         'division_name' => ucfirst($division_name),
        //     ];
        // }
        return view('dashboard.district', compact('ProvinceData', 'DivisionData','user'));
    }

    public function AddDistrict(DistrictRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->districts)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        // $data = $request->all();
        $district = trim($request->input('district'));
        $province = $request->input('province');
        $division = $request->input('division');
        $dt_edt = $request->input('dt_edt');
        $dt_edt = Carbon::createFromFormat('l d F Y - h:i A', $dt_edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($dt_edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $DistrictExist = District::where('name', $district)->exists();
        if ($DistrictExist) {
            return response()->json(['info' => 'District already exists. Please write a different province.']);
        }
        else
        {
            $District = new District();
            $District->name = $district;
            $District->province_id = $province;
            $District->division_id = $division;
            $District->status = $status;
            $District->user_id = $sessionId;
            $District->effective_timestamp = $dt_edt;
            $District->timestamp = $timestamp;
            $District->last_updated = $last_updated;
            $DistrictName = ucfirst($district);

            $District->save();
            if (empty($District->id)) {
                return response()->json(['error' => 'Failed to create District.']);
            }
            $logs = Logs::create([
                'module' => 'distrcit',
                'content' => "'{$DistrictName}' added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $this->currentDatetime,
            ]);

            $districtLog = District::where('id', $District->id)->first();
            $logIds = $districtLog->logid ? explode(',', $districtLog->logid) : [];
            $logIds[] = $logs->id;
            $districtLog->logid = implode(',', $logIds);
            $districtLog->save();

            $InsertedID = District::find($District->id);
            return response()->json(['success' => 'District created successfully']);
        }


    }

    public function GetSelectedDivisions(Request $request)
    {
        $provinceId = $request->input('provinceId');
        $divisions = Division::where('province_id', $provinceId)
        ->where('status', 1)
        ->get();

        return response()->json($divisions);
    }

    public function GetProvinceData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->province)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Provinces = Province::select('*')->orderBy('id', 'desc');
      
        // ->get()
        // return DataTables::of($Provinces)
        return DataTables::eloquent($Provinces)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('last_updated', 'like', "%{$search}%")
                            ->orWhere('timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Province) {
                return $Province->id;  // Raw ID value
            })
            ->editColumn('id', function ($Province) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $ProvinceName = $Province->name;
                $ModuleCode = 'PRO';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $ProvinceName))));
                $idStr = str_pad($Province->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($Province->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Province->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Province->last_updated)->format('l d F Y - h:i A');
                $ProvinceCode = $ModuleCode.'-'.$firstLetters.'-'.$idStr;
                $createdByName = getUserNameById($Province->user_id);
                $createdInfo = "<b>Created By:</b>" . ucwords($createdByName) . " <br> <b>Effective Date&amp;Time:</b> "
                    . $effectiveDate . " <br><b>RecordedAt:</b> " . $timestamp ." <br><b>LastUpdated:</b>
                    " . $lastUpdated;

                return $ProvinceCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($Province) {
                    $provinceId = $Province->id;
                    $logId = $Province->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->province)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-province" data-province-id="'.$provinceId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $Province->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                   
            })
            ->editColumn('status', function ($Province) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->province)[3];
                return $updateStatus == 1 ? ($Province->status ? '<span class="label label-success province_status cursor-pointer" data-id="'.$Province->id.'" data-status="'.$Province->status.'">Active</span>' : '<span class="label label-danger province_status cursor-pointer" data-id="'.$Province->id.'" data-status="'.$Province->status.'">Inactive</span>') : ($Province->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function GetDivisionData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->divisions)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        // $Divisions = Division::select('*')->get();
        $Divisions = Division::select('division.*', 'province.name as province_name')
        ->join('province', 'province.id', '=', 'division.province_id');
        if ($request->has('province') && $request->province != '' && $request->province != 'Loading...') {
            $Divisions->where('division.province_id', $request->province);
        }
        // ->get();
        // return DataTables::of($Divisions)
        return DataTables::eloquent($Divisions)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('division.id', 'like', "%{$search}%")
                            ->orWhere('division.name', 'like', "%{$search}%")
                            ->orWhere('division.status', 'like', "%{$search}%")
                            ->orWhere('division.effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('division.timestamp', 'like', "%{$search}%")
                            ->orWhere('division.last_updated', 'like', "%{$search}%")
                            ->orWhere('province.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Division) {
                return $Division->id;  // Raw ID value
            })
            ->editColumn('id', function ($Division) {
                $session = auth()->user();
                $sessionName = $session->name;
                $DivisionName = $Division->name;
                $idStr = str_pad($Division->id, 4, "0", STR_PAD_LEFT); // Pad the id with leading zeros
                $effectiveDate = Carbon::createFromTimestamp($Division->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Division->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Division->last_updated)->format('l d F Y - h:i A');
                $ModuleCode = 'DIV';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $DivisionName))));
                $DivisionCode = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $createdByName = getUserNameById($Division->user_id);
                $createdInfo = "<b>Created By:</b> " . ucwords($createdByName) . " <br> <b>Effective Date&amp;Time:</b> "
                    . $effectiveDate . " <br><b>RecordedAt:</b> " . $timestamp ." <br><b>LastUpdated:</b>
                    " . $lastUpdated;

                return $DivisionCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($Division) {
                    $divisionId = $Division->id;
                    $logId = $Division->logid;

                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->divisions)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-division" data-division-id="'.$divisionId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $Division->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                   
            })
            ->editColumn('status', function ($Division) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->divisions)[3];
                return $updateStatus == 1 ? ($Division->status ? '<span class="label label-success division_status cursor-pointer" data-id="'.$Division->id.'" data-status="'.$Division->status.'">Active</span>' : '<span class="label label-danger division_status cursor-pointer" data-id="'.$Division->id.'" data-status="'.$Division->status.'">Inactive</span>') : ($Division->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function GetDistrictData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->districts)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Districts = District::select('district.*', 'division.name as division_name',
        'province.name as province_name')
        ->join('province', 'province.id', '=', 'district.province_id')
        ->join('division', 'division.id', '=', 'district.division_id');

        if ($request->has('province') && $request->province != '' && $request->province != 'Loading...') {
            $Districts->where('district.province_id', $request->province);
        }
        if ($request->has('division') && $request->division != '' && $request->division != 'Loading...') {
            $Districts->where('district.division_id', $request->division);
        }
        // ->get();
        // return DataTables::of($Districts)
        return DataTables::eloquent($Districts)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('district.id', 'like', "%{$search}%")
                            ->orWhere('district.name', 'like', "%{$search}%")
                            ->orWhere('district.status', 'like', "%{$search}%")
                            ->orWhere('district.effective_timestamp', 'like', "%{$search}%")
                            ->orWhere('district.timestamp', 'like', "%{$search}%")
                            ->orWhere('district.last_updated', 'like', "%{$search}%")
                            ->orWhere('province.name', 'like', "%{$search}%")
                            ->orWhere('division.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($District) {
                return $District->id;  // Raw ID value
            })
            ->editColumn('id', function ($District) {
                $session = auth()->user();
                $sessionName = $session->name;
                $DistrictName = $District->name;
                $idStr = str_pad($District->id, 4, "0", STR_PAD_LEFT); // Pad the id with leading zeros
                $effectiveDate = Carbon::createFromTimestamp($District->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($District->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($District->last_updated)->format('l d F Y - h:i A');

                $ModuleCode = 'DIS';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $DistrictName))));
                $DistrictCode = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $createdByName = getUserNameById($District->user_id);
                $createdInfo = "<b>Created By:</b> " . ucwords($createdByName) . " <br> <b>Effective Date&amp;Time:</b> "
                    . $effectiveDate . " <br><b>RecordedAt:</b> " . $timestamp ." <br><b>LastUpdated:</b>
                    " . $lastUpdated;

                return $DistrictCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($District) {
                    $districtId = $District->id;
                    $logId = $District->logid;

                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->districts)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-district" data-district-id="'.$districtId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }

                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                    return $District->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($District) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->districts)[3];
                return $updateStatus == 1 ? ($District->status ? '<span class="label label-success district_status cursor-pointer" data-id="'.$District->id.'" data-status="'.$District->status.'">Active</span>' : '<span class="label label-danger district_status cursor-pointer" data-id="'.$District->id.'" data-status="'.$District->status.'">Inactive</span>') : ($District->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateProvinceStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->province)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ProvinceID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $province = Province::find($ProvinceID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $province->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
        }
        // Find the role by ID
        $province->status = $UpdateStatus;
        $province->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'province',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ProvinceLog = Province::where('id', $ProvinceID)->first();
        $logIds = $ProvinceLog->logid ? explode(',', $ProvinceLog->logid) : [];
        $logIds[] = $logs->id;
        $ProvinceLog->logid = implode(',', $logIds);
        $ProvinceLog->save();

        $province->save();
        return response()->json(['success' => true, 200]);
    }
    public function UpdateDivisionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->divisions)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $DivisionID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $division = Division::find($DivisionID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';

            $division->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        // Find the role by ID
        $division->status = $UpdateStatus;
        $division->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'division',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $DivisionLog = Division::where('id', $DivisionID)->first();
        $logIds = $DivisionLog->logid ? explode(',', $DivisionLog->logid) : [];
        $logIds[] = $logs->id;
        $DivisionLog->logid = implode(',', $logIds);
        $DivisionLog->save();

        $division->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateDistrictStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->districts)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $DistrictID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $district = District::find($DistrictID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $district->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';

        }
        // Find the role by ID
        $district->status = $UpdateStatus;
        $district->last_updated = $CurrentTimestamp;


        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'district',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $DistrictLog = District::where('id', $DistrictID)->first();
        $logIds = $DistrictLog->logid ? explode(',', $DistrictLog->logid) : [];
        $logIds[] = $logs->id;
        $DistrictLog->logid = implode(',', $logIds);
        $DistrictLog->save();

        $district->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateProvinceModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->province)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $province = Province::find($id);
        $province_name = $province->name;
        $effective_timestamp = $province->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');


        $data = [
            'id' => $id,
            'name' => $province_name,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateDivisionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->divisions)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Division = Division::select('division.*', 'province.name as province_name', 'province.id as province_id')
        ->join('province', 'province.id', '=', 'division.province_id')
        ->find($id);
        $division_name = $Division->name;
        $province_name = $Division->province_name;
        $province_id = $Division->province_id;
        $effective_timestamp = $Division->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $division_name,
            'province_id' => $province_id,
            'province_name' => $province_name,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateDistrictModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->districts)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $District = District::select('district.*', 'division.name as division_name',
        'province.name as province_name')
        ->join('province', 'province.id', '=', 'district.province_id')
        ->join('division', 'division.id', '=', 'district.division_id')
        ->where('district.id', $id)
        ->first();
        $district_name = $District->name;
        $province_name = $District->province_name;
        $province_id = $District->province_id;
        $division_name = $District->division_name;
        $division_id = $District->division_id;
        $effective_timestamp = $District->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'name' => $district_name,
            'province_name' => $province_name,
            'province_id' => $province_id,
            'division_name' => $division_name,
            'division_id' => $division_id,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateSelectedProvince(Request $request)
    {
        $provinceId = $request->input('provinceId');
        $provinces = Province::whereNotIn('id', [$provinceId])
                     ->where('status', 1)
                     ->get();

        return response()->json($provinces);
    }

    public function UpdateSelectedDivisions(Request $request)
    {
        $provinceId = $request->input('provinceId');
        $divisions = Division::where('province_id', $provinceId)
                     ->where('status', 1);

        if ($request->has('divisionId')) {
            $divisionId = $request->input('divisionId');
            $divisions = $divisions->where('id', '!=', $divisionId);
        }
        $divisions = $divisions->get();
        return response()->json($divisions);
    }

    public function UpdateSelectedDistrict(Request $request)
    {
        // $divisionid = $request->input('divisionId');
        // $district = District::where('division_id', $divisionid)
        //             ->where('status', 1)
        //             ->get();
        $district = District::where('status', 1);

        if ($request->has('divisionId')) {
            $divisionid = $request->input('divisionId');
            $district = $district->where('division_id', $divisionid);
        }
        if ($request->has('districtId')) {
            $districtId = $request->input('districtId');
            $district = $district->where('id', '!=', $districtId);
        }
        $district = $district->get();


        return response()->json($district);
    }

    public function UpdateProvince(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->province)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $provinvce = Province::findOrFail($id);
        // Update the role with the new values
        $provinvce->name = $request->input('u_province');
        $effective_date = $request->input('u_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
            $statusLog = 'Active';

        } else {
             $status = 0; //Inactive
             $statusLog = 'Inactive';

        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $provinvce->effective_timestamp = $effective_date;
        $provinvce->last_updated = $this->currentDatetime;
        $provinvce->status = $status;

        $provinvce->save();

        if (empty($provinvce->id)) {
            return response()->json(['error' => 'Failed to update provinvce. Please try again']);
        }


        $logs = Logs::create([
            'module' => 'province',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ProvinceLog = Province::where('id', $provinvce->id)->first();
        $logIds = $ProvinceLog->logid ? explode(',', $ProvinceLog->logid) : [];
        $logIds[] = $logs->id;
        $ProvinceLog->logid = implode(',', $logIds);
        $ProvinceLog->save();

        return response()->json(['success' => 'Province updated successfully']);
    }


    public function UpdateDivision(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->divisions)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $division = Division::findOrFail($id);
        // Update the role with the new values
        $division->name = $request->input('u_division');
        $division->province_id = $request->input('ud_province');
        $effective_date = $request->input('u_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active

        } else {
             $status = 0; //Inactive
        }

        $division->effective_timestamp = $effective_date;
        $division->last_updated = $this->currentDatetime;
        $division->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $division->save();

        if (empty($division->id)) {
            return response()->json(['error' => 'Failed to update division. Please try again']);
        }

        $logs = Logs::create([
            'module' => 'division',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $DivisionLog = Division::where('id', $division->id)->first();
        $logIds = $DivisionLog->logid ? explode(',', $DivisionLog->logid) : [];
        $logIds[] = $logs->id;
        $DivisionLog->logid = implode(',', $logIds);
        $DivisionLog->save();

        return response()->json(['success' => 'Division updated successfully']);
    }

    public function UpdateDistrict(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->districts)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $district = District::findOrFail($id);

        // Update the role with the new values
        $district->name = $request->input('u_district');
        $district->province_id = $request->input('u_province');
        $district->division_id = $request->input('u_division');
        $effective_date = $request->input('u_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $district->effective_timestamp = $effective_date;
        $district->last_updated = $this->currentDatetime;
        $district->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $district->save();

        if (empty($district->id)) {
            return response()->json(['error' => 'Failed to update district. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'district',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $DistrictLog = District::where('id', $district->id)->first();
        $logIds = $DistrictLog->logid ? explode(',', $DistrictLog->logid) : [];
        $logIds[] = $logs->id;
        $DistrictLog->logid = implode(',', $logIds);
        $DistrictLog->save();

        return response()->json(['success' => 'District updated successfully']);
    }

}
