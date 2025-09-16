<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\ChartOfAccountStrategy;
use App\Models\ChartOfAccountStrategySetup;
use App\Models\AccountLevelSetup;
use App\Models\TransactionSourcesDestinations;
use App\Models\FinancialLedgerTypes;
use App\Models\FinancialPayrollAddition;
use App\Models\FinancialPayrollDeduction;
use App\Models\DonorRegistration;
use App\Models\FinancialTransactionTypes;
use App\Models\FinancialTransactions;
use App\Models\ActivatedServiceRate;
use App\Models\Service;
use App\Models\ItemRates;
use App\Http\Requests\ChartOfAccountStrategyRequest;
use App\Http\Requests\ChartOfAccountStrategySetupRequest;
use App\Http\Requests\TransactionSourceDestinationRequest;
use App\Http\Requests\FinancialLedgerTypesRequest;
use App\Http\Requests\FinancialPayrollAdditionRequest;
use App\Http\Requests\FinancialPayrollDeductionRequest;
use App\Http\Requests\DonorRegistrationRequest;
use App\Http\Requests\FinanceTransactionTypeRequst;
use App\Http\Requests\FinancialTransactionRequest;
use App\Http\Requests\FinancialPaymentRequest;
use App\Http\Requests\ServiceRateRequest;
use App\Http\Requests\ItemRatesRequest;
use App\Mail\DonorRegistrationMail;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;


class FinanceController extends Controller
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

    public function ChartOfAccountStrategy()
    {
        $colName = 'chart_of_accounts_strategy';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.chart-of-account-strategy', compact('user','Organizations'));
    }

    public function AddChartOfAccountStrategy(ChartOfAccountStrategyRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->chart_of_accounts_strategy)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $StrategyDesc = trim($request->input('accountStrategy'));
        $Remarks = trim($request->input('as_remarks'));
        $Level = trim($request->input('as_level'));
        $Edt = $request->input('as_edt');
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

        $StrategyExists = ChartOfAccountStrategy::where('name', $StrategyDesc)
        ->orWhere('level', $Level)
        ->where('status', 1)
        ->exists();
        if ($StrategyExists) {
            return response()->json(['info' => 'This Chart Of Accounts Strategy Details already exists.']);
        }
        else
        {
            $AccountStrategy = new ChartOfAccountStrategy();
            $AccountStrategy->name = $StrategyDesc;
            $AccountStrategy->remarks = $Remarks;
            $AccountStrategy->level = $Level;
            $AccountStrategy->status = $status;
            $AccountStrategy->user_id = $sessionId;
            $AccountStrategy->last_updated = $last_updated;
            $AccountStrategy->timestamp = $timestamp;
            $AccountStrategy->effective_timestamp = $Edt;
            $AccountStrategy->save();

            if (empty($AccountStrategy->id)) {
                return response()->json(['error' => 'Failed to create Chart Of Accounts Strategy.']);
            }

            $logs = Logs::create([
                'module' => 'finance',
                'content' => "'{$StrategyDesc}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $AccountStrategy->logid = $logs->id;
            $AccountStrategy->save();
            return response()->json(['success' => 'Chart Of Accounts Strategy created successfully']);
        }

    }

    public function GetChartOfAccountStrategyData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->chart_of_accounts_strategy)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $AccountStrategies = ChartOfAccountStrategy::select('*')->orderBy('id', 'desc');
        // ->get()
        // return DataTables::of($AccountStrategies)
        return DataTables::eloquent($AccountStrategies)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%")
                            ->orWhere('level', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('logid', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($AccountStrategy) {
                return $AccountStrategy->id;  // Raw ID value
            })
            ->editColumn('id', function ($AccountStrategy) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $Description = ucwords($AccountStrategy->name);
                $HierarchyLevel = ($AccountStrategy->level);
                $HierarchyLevel = numberToWordOrdinal($HierarchyLevel).' Level';

                $effectiveDate = Carbon::createFromTimestamp($AccountStrategy->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($AccountStrategy->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($AccountStrategy->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($AccountStrategy->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                return $Description
                    . '<br><b>Account Hierarchy level: </b>'.$HierarchyLevel
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($AccountStrategy) {
                    $AccountStrategyId = $AccountStrategy->id;
                    $logId = $AccountStrategy->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->chart_of_accounts_strategy)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-accountStrategy" data-accountstrategy-id="'.$AccountStrategyId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $AccountStrategy->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
           
            })
            ->editColumn('status', function ($AccountStrategy) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->chart_of_accounts_strategy)[3];
                return $updateStatus == 1 ? ($AccountStrategy->status ? '<span class="label label-success accountstrategy_status cursor-pointer" data-id="'.$AccountStrategy->id.'" data-status="'.$AccountStrategy->status.'">Active</span>' : '<span class="label label-danger accountstrategy_status cursor-pointer" data-id="'.$AccountStrategy->id.'" data-status="'.$AccountStrategy->status.'">Inactive</span>') : ($AccountStrategy->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateChartOfAccountStrategyStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->chart_of_accounts_strategy)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ChartOfAccountStrategyID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ChartOfAccountStrategy = ChartOfAccountStrategy::find($ChartOfAccountStrategyID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Level = $ChartOfAccountStrategy->level;
            $StrategyExists = ChartOfAccountStrategy::where('level', $Level)
            ->where('status', 1)
            ->exists();

            if ($StrategyExists) {
                return response()->json(['info' => 'This Account Level Hierarchy is already assigned to another Account Strategy.']);
            }

            $ChartOfAccountStrategy->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ChartOfAccountStrategy->effective_timestamp = 0;

        }
        $ChartOfAccountStrategy->status = $UpdateStatus;
        $ChartOfAccountStrategy->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ChartOfAccountStrategyLog = ChartOfAccountStrategy::where('id', $ChartOfAccountStrategyID)->first();
        $logIds = $ChartOfAccountStrategyLog->logid ? explode(',', $ChartOfAccountStrategyLog->logid) : [];
        $logIds[] = $logs->id;
        $ChartOfAccountStrategyLog->logid = implode(',', $logIds);
        $ChartOfAccountStrategyLog->save();

        $ChartOfAccountStrategy->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateChartOfAccountStrategyModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->chart_of_accounts_strategy)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ChartOfAccountStrategy = ChartOfAccountStrategy::find($id);
        $Desc = ucwords($ChartOfAccountStrategy->name);
        $Remarks = ucwords($ChartOfAccountStrategy->remarks);
        $effective_timestamp = $ChartOfAccountStrategy->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');
        $HierarchyLevel = ($ChartOfAccountStrategy->level);
        $HierarchyLevel = numberToWordOrdinal($HierarchyLevel).' Level';

        $data = [
            'id' => $id,
            'desc' => $Desc,
            'remarks' => $Remarks,
            'Level' => $ChartOfAccountStrategy->level,
            'HierarchyLevel' => $HierarchyLevel,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateChartOfAccountStrategy(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->chart_of_accounts_strategy)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ChartOfAccountStrategy = ChartOfAccountStrategy::findOrFail($id);
        $ChartOfAccountStrategy->name = $request->input('u_accountStrategy');
        $ChartOfAccountStrategy->remarks = $request->input('u_as_remarks');
        $ChartOfAccountStrategy->level = $request->input('u_as_level');
        $Level = $request->input('u_as_level');
        $effective_date = $request->input('u_as_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ChartOfAccountStrategy->effective_timestamp = $effective_date;
        $ChartOfAccountStrategy->last_updated = $this->currentDatetime;
        $ChartOfAccountStrategy->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $StrategyExists = ChartOfAccountStrategy::where('level', $Level)
        ->where('status', 1)
        ->exists();

        if ($StrategyExists) {
            return response()->json(['info' => 'This Account Level Hierarchy is already assigned to another Account Strategy.']);
        }
        else
        {
            $ChartOfAccountStrategy->save();
            if (empty($ChartOfAccountStrategy->id)) {
                return response()->json(['error' => 'Failed to update Chart Of Account Strategy Details. Please try again']);
            }
            $logs = Logs::create([
                'module' => 'finance',
                'content' => "Data has been updated by '{$sessionName}'",
                'event' => 'update',
                'timestamp' => $this->currentDatetime,
            ]);
            $ChartOfAccountStrategyLog = ChartOfAccountStrategy::where('id', $ChartOfAccountStrategy->id)->first();
            $logIds = $ChartOfAccountStrategyLog->logid ? explode(',', $ChartOfAccountStrategyLog->logid) : [];
            $logIds[] = $logs->id;
            $ChartOfAccountStrategyLog->logid = implode(',', $logIds);
            $ChartOfAccountStrategyLog->save();
            return response()->json(['success' => 'Chart Of Account Strategy Details updated successfully']);
        }


    }
    public function FetchAccountStrategyOrganizations()
    {
        $user = auth()->user();
        $Organizations = Organization::leftJoin('account_strategy_setup', 'organization.id', '=', 'account_strategy_setup.org_id')
        ->select('organization.*')
        ->where(function ($query) {
            $query->whereNull('account_strategy_setup.org_id')
            ->orWhere('account_strategy_setup.status', 0);
        })
        ->get();

        $AccountStrategyLevels = ChartOfAccountStrategy::where('status', 1)->get();
        return view('dashboard.chart-of-account-strategy-setup', compact('user','Organizations','AccountStrategyLevels'));
    }

    public function ChartOfAccountStrategySetup()
    {
        $colName = 'chart_of_accounts_strategy_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::leftJoin('account_strategy_setup', 'organization.id', '=', 'account_strategy_setup.org_id')
        ->select('organization.*')
        ->where(function ($query) {
            $query->whereNull('account_strategy_setup.org_id')
            ->orWhere('account_strategy_setup.status', 0);
        })
        ->get();

        $AccountStrategyLevels = ChartOfAccountStrategy::where('status', 1)->get();
        return view('dashboard.chart-of-account-strategy-setup', compact('user','Organizations','AccountStrategyLevels'));
    }
    public function AddChartOfAccountStrategySetup(ChartOfAccountStrategySetupRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->chart_of_accounts_strategy_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = trim($request->input('ass_org'));
        $AccountLevel = trim($request->input('ass_level'));
        $Edt = $request->input('ass_edt');
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

        $StrategySetupExists = ChartOfAccountStrategySetup::where('org_id', $Organization)
        ->where('account_strategy_id', $AccountLevel)
        ->exists();

        if ($StrategySetupExists) {
            return response()->json(['info' => 'This Chart Of Accounts Strategy Setup Details already exists.']);
        }
        else
        {
            $AccountStrategySetup = new ChartOfAccountStrategySetup();
            $AccountStrategySetup->org_id = $Organization;
            $AccountStrategySetup->account_strategy_id = $AccountLevel;
            $AccountStrategySetup->status = $status;
            $AccountStrategySetup->user_id = $sessionId;
            $AccountStrategySetup->last_updated = $last_updated;
            $AccountStrategySetup->timestamp = $timestamp;
            $AccountStrategySetup->effective_timestamp = $Edt;
            $AccountStrategySetup->save();

            if (empty($AccountStrategySetup->id)) {
                return response()->json(['error' => 'Failed to create Chart Of Accounts Strategy Setup.']);
            }

            $AccountStrategyLevelName = ChartOfAccountStrategy::where('status', 1)->where('id', $AccountLevel)->pluck('name');
            $OrgName = Organization::where('status', 1)->where('id', $Organization)->pluck('organization');
            $logs = Logs::create([
                'module' => 'finance',
                'content' => "'{$AccountStrategyLevelName[0]}' has been assigned to '{$OrgName[0]}' by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);

            $logId = $logs->id;
            $AccountStrategySetup->logid = $logs->id;
            $AccountStrategySetup->save();
            return response()->json(['success' => 'Chart Of Accounts Strategy Setup added successfully']);
        }

    }
    public function GetChartOfAccountStrategySetupData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->chart_of_accounts_strategy_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $AccountStrategiesSetup = ChartOfAccountStrategySetup::select('account_strategy_setup.*', 'organization.organization as orgName',
        'account_strategy.name as accountLevel','account_strategy.remarks as accountLevelRemarks','account_strategy.level as Level')
        ->join('account_strategy', 'account_strategy.id', '=', 'account_strategy_setup.account_strategy_id')
        ->leftJoin('organization', 'organization.id', '=', 'account_strategy_setup.org_id')
        ->orderBy('account_strategy_setup.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $AccountStrategiesSetup->where('account_strategy_setup.org_id', '=', $sessionOrg);
        }
        $AccountStrategiesSetup = $AccountStrategiesSetup;
        // ->get()
        // return DataTables::of($AccountStrategiesSetup)
        return DataTables::eloquent($AccountStrategiesSetup)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('account_strategy_setup.id', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('account_strategy.name', 'like', "%{$search}%")
                            ->orWhere('account_strategy.remarks', 'like', "%{$search}%")
                            ->orWhere('account_strategy.level', 'like', "%{$search}%")
                            ->orWhere('account_strategy_setup.status', 'like', "%{$search}%")
                            ->orWhere('account_strategy_setup.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($AccountStrategySetup) {
                return $AccountStrategySetup->id;  // Raw ID value
            })
            ->editColumn('id', function ($AccountStrategySetup) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $accountLevel = ucwords($AccountStrategySetup->accountLevel);
                $accountLevelRemarks = ucwords($AccountStrategySetup->accountLevelRemarks);
                $effectiveDate = Carbon::createFromTimestamp($AccountStrategySetup->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($AccountStrategySetup->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($AccountStrategySetup->last_updated)->format('l d F Y - h:i A');
                $HierarchyLevel = ($AccountStrategySetup->Level);
                $HierarchyLevel = numberToWordOrdinal($HierarchyLevel).' Level';
                $createdByName = getUserNameById($AccountStrategySetup->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($AccountStrategySetup->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'ASS';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $HierarchyLevel))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($AccountStrategySetup->orgName).'<hr class="mt-1 mb-1">';
                }
                return $Code.'<hr class="mt-1 mb-1">'.$orgName.$accountLevel.'<br> <b>Remarks: </b>'.$accountLevelRemarks
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Account Hierarchy level: </b>'.$HierarchyLevel
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('account_level_setup', function ($AccountStrategySetup) {
                $AccountStrategySetupId = $AccountStrategySetup->id;
                $Level = $AccountStrategySetup->Level;
                $expectedLevels = range(1, $Level);

                $existingLevels = DB::table('account_level_setup')
                ->where('account_strategy_setup_id', $AccountStrategySetupId)
                ->whereIn('account_level', $expectedLevels)
                ->pluck('account_level')
                ->toArray();
                $allLevelsExist = count(array_intersect($expectedLevels, $existingLevels)) == $Level;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->chart_of_accounts_strategy_setup)[2];
                $add = explode(',', $Rights->chart_of_accounts_strategy_setup)[0];
                if ($allLevelsExist) {
                    if($edit == 1)
                    {
                        return $AccountStrategySetup->status ?
                        '<button type="button" style="color:black;font-size: 14px;" class="btn-sm btn btn-warning view_al" data-accountstrategysetup-id="'.$AccountStrategySetupId.'" data-level="'.$Level.'"><i class="fa fa-eye"></i> View Account levels</button><hr class="mt-2 mb-2">' .
                        '<button type="button" style="font-size: 14px" class="btn-sm btn btn-success setup_al" data-accountstrategysetup-id="'.$AccountStrategySetupId.'" data-level="'.$Level.'"><i class="fa fa-edit"></i> Update Account Levels</button>' :
                        '<span class="font-weight-bold">Status must be Active to perform this action.</span>';
                    }
                    else{
                        return '<code>Permission Denied</code>';
                    }
                } else {
                    if($add == 1)
                    {
                        $atLeastOneLevelExist = DB::table('account_level_setup')
                            ->where('account_strategy_setup_id', $AccountStrategySetupId)
                            ->exists();
                
                        if ($atLeastOneLevelExist) {
                            return $AccountStrategySetup->status ?
                            '<button type="button" class="btn-sm btn btn-danger setup_al" data-accountstrategysetup-id="'.$AccountStrategySetupId.'" data-level="'.$Level.'"><i class="fa fa-check-circle"></i> Complete Account level Setup</button>' :
                            '<span class="font-weight-bold">Status must be Active to perform this action.</span>';

                        } else {
                            return $AccountStrategySetup->status ?
                            '<button type="button" class="btn-sm btn btn-primary blink setup_al" data-accountstrategysetup-id="'.$AccountStrategySetupId.'" data-level="'.$Level.'"><i class="fa fa-gears"></i> Setup Account levels</button>' :
                            '<span class="font-weight-bold">Status must be Active to perform this action.</span>';
                        }
                    }
                    else{
                        return '<code>Permission Denied</code>';
                    }
                }

                // return $AccountStrategySetup->status ? '<button type="button" class="btn-sm btn btn-primary blink setup_al" data-accountstrategysetup-id="'.$AccountStrategySetupId.'" data-level="'.$AccountStrategySetup->Level.'"><i class="fa fa-gears"></i> Setup Account levels</button>' : '<span class="font-weight-bold">Status must be Active to perform this action.</span>';
            })
            ->addColumn('action', function ($AccountStrategySetup) {
                    $AccountStrategySetupId = $AccountStrategySetup->id;
                    $logId = $AccountStrategySetup->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->chart_of_accounts_strategy_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-accountStrategySetup" data-accountstrategysetup-id="'.$AccountStrategySetupId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $AccountStrategySetup->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
           
            })
            ->editColumn('status', function ($AccountStrategySetup) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->chart_of_accounts_strategy_setup)[3];
                return $updateStatus == 1 ? ($AccountStrategySetup->status ? '<span class="label label-success accountstrategysetup_status cursor-pointer" data-id="'.$AccountStrategySetup->id.'" data-status="'.$AccountStrategySetup->status.'">Active</span>' : '<span class="label label-danger accountstrategysetup_status cursor-pointer" data-id="'.$AccountStrategySetup->id.'" data-status="'.$AccountStrategySetup->status.'">Inactive</span>') : ($AccountStrategySetup->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id','account_level_setup'])
            ->make(true);
    }
    public function UpdateChartOfAccountStrategySetupStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->chart_of_accounts_strategy_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ChartOfAccountStrategySetupID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ChartOfAccountStrategySetup = ChartOfAccountStrategySetup::find($ChartOfAccountStrategySetupID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ChartOfAccountStrategySetup->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ChartOfAccountStrategySetup->effective_timestamp = 0;

        }
        $ChartOfAccountStrategySetup->status = $UpdateStatus;
        $ChartOfAccountStrategySetup->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ChartOfAccountStrategySetupLog = ChartOfAccountStrategySetup::where('id', $ChartOfAccountStrategySetupID)->first();
        $logIds = $ChartOfAccountStrategySetupLog->logid ? explode(',', $ChartOfAccountStrategySetupLog->logid) : [];
        $logIds[] = $logs->id;
        $ChartOfAccountStrategySetupLog->logid = implode(',', $logIds);
        $ChartOfAccountStrategySetupLog->save();

        $ChartOfAccountStrategySetup->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateChartOfAccountStrategySetupModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->chart_of_accounts_strategy_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ChartOfAccountStrategySetup = ChartOfAccountStrategySetup::select('account_strategy_setup.*',
        'organization.organization as orgName','account_strategy.name as accountLevel',
        'account_strategy.remarks as accountLevelRemarks')
        ->join('account_strategy', 'account_strategy.id', '=', 'account_strategy_setup.account_strategy_id')
        ->join('organization', 'organization.id', '=', 'account_strategy_setup.org_id')
        ->where('account_strategy_setup.id', $id)
        ->first();

        $Org = $ChartOfAccountStrategySetup->orgName;
        $OrgId = $ChartOfAccountStrategySetup->org_id;
        $accountLevelId = $ChartOfAccountStrategySetup->account_strategy_id;
        $accountLevel = $ChartOfAccountStrategySetup->accountLevel;
        $effective_timestamp = $ChartOfAccountStrategySetup->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'Org' => $Org,
            'OrgId' => $OrgId,
            'accountLevel' => $accountLevel,
            'accountLevelId' => $accountLevelId,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateChartOfAccountStrategySetup(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->chart_of_accounts_strategy_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ChartOfAccountStrategySetup = ChartOfAccountStrategySetup::findOrFail($id);
        $ChartOfAccountStrategySetup->org_id = $request->input('u_ass_org');
        $ChartOfAccountStrategySetup->account_strategy_id = $request->input('u_ass_level');
        $effective_date = $request->input('u_ass_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ChartOfAccountStrategySetup->effective_timestamp = $effective_date;
        $ChartOfAccountStrategySetup->last_updated = $this->currentDatetime;
        $ChartOfAccountStrategySetup->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ChartOfAccountStrategySetup->save();

        if (empty($ChartOfAccountStrategySetup->id)) {
            return response()->json(['error' => 'Failed to update Chart Of Account Strategy Setup Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ChartOfAccountStrategySetupLog = ChartOfAccountStrategySetup::where('id', $ChartOfAccountStrategySetup->id)->first();
        $logIds = $ChartOfAccountStrategySetupLog->logid ? explode(',', $ChartOfAccountStrategySetupLog->logid) : [];
        $logIds[] = $logs->id;
        $ChartOfAccountStrategySetupLog->logid = implode(',', $logIds);
        $ChartOfAccountStrategySetupLog->save();
        return response()->json(['success' => 'Chart Of Account Strategy Setup Details updated successfully']);
    }
    public function GetSelectedAccountStrategies(Request $request)
    {
        $accountLevelId = $request->input('accountLevelId');
        $query = ChartOfAccountStrategy::select('id', 'name');
        if ($accountLevelId !== null) {
            $query->whereNotIn('id', [$accountLevelId]);
        }
        $ChartOfAccountStrategy = $query->where('status', 1)->get();
        return response()->json($ChartOfAccountStrategy);
    }

    public function GetSelectedAccountStrategyOrg(Request $request)
    {
        $orgID = $request->input('orgID');
        $query = Organization::leftJoin('account_strategy_setup', 'organization.id', '=', 'account_strategy_setup.org_id')
            ->select('organization.*')
            ->where(function ($query) {
                $query->whereNull('account_strategy_setup.org_id')
                    ->orWhere('account_strategy_setup.status', 0);
            });

        if ($orgID !== null && $orgID !== '') {
            $query->whereNotIn('organization.id', [$orgID]);
        }

        $Organization = $query->get();
        return response()->json($Organization);

    }

    public function GetAccountNames(Request $request)
    {
        $Id = $request->input('id');
        $DebitCreditAccounts = ChartOfAccountStrategySetup::select('account_strategy_setup.account_strategy_id',
        'account_strategy.level as level',
        DB::raw("CONCAT(UCASE(LEFT(account_level_setup.name, 1)), SUBSTRING(account_level_setup.name, 2)) as accountNames"),
        'account_level_setup.id as id',
        'account_level_setup.account_level as accountLevel')
        ->join('account_strategy', 'account_strategy.id', '=', 'account_strategy_setup.account_strategy_id')
        ->join('account_level_setup', 'account_level_setup.account_strategy_setup_id', '=', 'account_strategy_setup.id')
        ->where('account_strategy_setup.org_id', $Id)
        ->where('account_level_setup.account_level', '=', DB::raw('account_strategy.level')) 
        ->where('account_level_setup.status', '1')
        ->orderBy('account_strategy_setup.id', 'desc')
        ->get();


        return response()->json($DebitCreditAccounts);
    }

    public function SetupAccountLevel(Request $request)
    {
        $descriptions = explode(',', implode(',', $request->input('account_desc')));
        $level = trim($request->input('account_level'));
        $strategyId = trim($request->input('strategyId'));
        $maxlevel = trim($request->input('maxlevel'));
        $isInitialSetup = $request->input('initial_setup');
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null; 
        $currentLevel = $level - 1;
        $parentCol = "parent_level" . $level . "_id";

        $Organization = ChartOfAccountStrategySetup::where('status', 1)->where('id', $strategyId)->pluck('org_id');
        $OrgName = Organization::where('status', 1)->where('id', $Organization)->pluck('organization');
        $logId = $this->createAccountSetupLog($level, $OrgName[0], $sessionName, $timestamp);
       
        foreach ($descriptions as $descIndex => $desc) {
            $Desc = strtolower(trim($desc));
            if($isInitialSetup == 1){
                if($level < $maxlevel) {
                    $btnText = "Click here to configure next level";
                }
                else
                {
                    $btnText = "Ok";
                }
                $AccountLevelSetupExists = AccountLevelSetup::where('name', $Desc)
                ->where('account_level', $level)
                ->where('account_strategy_setup_id', $strategyId)
                ->exists();
                if ($AccountLevelSetupExists) {
                    return response()->json(['info' => 'This Account Level Setup is already assigned to the same organization at the specified level.']);
                }
                else
                {
                    $AccountLevelSetup = new AccountLevelSetup();
                    $AccountLevelSetup->account_strategy_setup_id = $strategyId;
                    $AccountLevelSetup->name = $Desc;
                    $AccountLevelSetup->account_level = $level;
                    $AccountLevelSetup->status = 1;
                    $AccountLevelSetup->user_id = $sessionId;
                    $AccountLevelSetup->last_updated = $last_updated;
                    $AccountLevelSetup->timestamp = $timestamp;
                    $AccountLevelSetup->save();
                    if (empty($AccountLevelSetup->id)) {
                        return response()->json(['error' => 'Failed to Setup Account Level.']);
                    }
                    $AccountLevelSetup->logid = $logId;
                    $AccountLevelSetup->save();
                }
            }
            else{
                if($level < $maxlevel) {
                    $btnText = "Click here to update next level";
                }
                else
                {
                    $btnText = "Ok";
                }
                $existingRecords = AccountLevelSetup::where('account_level', $level)
                ->where('account_strategy_setup_id', $strategyId)
                ->where('status', 1)
                ->get();
                $existingNames = $existingRecords->pluck('name')->all();
                $submittedNames = array_map('strtolower', array_map('trim', $descriptions));
                $namesToDelete = array_diff($existingNames, $submittedNames);
                $namesToAdd = array_diff($submittedNames, $existingNames);
                foreach ($namesToDelete as $oldname) {
                    $oldRecord = AccountLevelSetup::where('name', $oldname)
                                               ->where('account_level', $level)
                                               ->where('account_strategy_setup_id', $strategyId)
                                               ->where('status', 1)
                                               ->first();
                    if ($oldRecord) {
                        $oldRecord->status = 0;
                        $oldRecord->save();
                        $recordId = $oldRecord->id;
                        AccountLevelSetup::where($parentCol, $recordId)->update(['status' => 0]);
                    }
                }

                foreach ($namesToAdd as $newName) {
                    $existingRecord = AccountLevelSetup::where('name', $newName)
                    ->where('account_level', $level)
                    ->where('account_strategy_setup_id', $strategyId)
                    ->where('status', 0)
                    ->first();

                    if ($existingRecord) {
                        $existingRecord->status = 1;
                        $recordId = $existingRecord->id;
                        $existingRecord->last_updated = $last_updated;
                        $existingRecord->save();
                        AccountLevelSetup::where($parentCol, $recordId)->update(['status' => 1]);
                    }
                    else
                    {
                        $newRecord = new AccountLevelSetup();
                        $newRecord->account_strategy_setup_id = $strategyId; 
                        $newRecord->name = $newName; 
                        $newRecord->account_level = $level; 
                        $newRecord->status = 1; 
                        $newRecord->user_id = $sessionId; 
                        $newRecord->last_updated = $last_updated; 
                        $newRecord->timestamp = $timestamp; 
                        $newRecord->save();
                    }
                }
            }
        }
        if($level > 1)
        {
            $prevParentLevelId = '';
            if($isInitialSetup == 1){
                $parentLevelIds = [];
                $parentLevels = [];
                $descriptionsss = $request->input('account_desc');
                for ($i = 1; $i < $level; $i++) {
                    $parentLevelIds[] = 'parent_level' . $i . '_id';
                    $parentLevels[] = explode(',', implode(',', $request->input("acnt_lvl" . ($i))));
                } 
                for ($j = 0; $j < count($parentLevels); $j++) 
                {
                    foreach($parentLevels[$j] as $parentLvlKey => $parentLevelArr){
                        if (isset($descriptionsss[$parentLvlKey])) {
                            $DescArr = $descriptionsss[$parentLvlKey];
                            $DescArr = explode(',', $DescArr); 
                            foreach($DescArr as $desc)
                            {
                                $currentParentLevelId = isset($parentLevelIds[$j]) ? $parentLevelIds[$j] : null;
                                if ($currentParentLevelId !== null) {
                                    $parentLevelId = $currentParentLevelId;
                                    $prevParentLevelId = $currentParentLevelId;
                                } else {
                                    $parentLevelId = $prevParentLevelId;
                                }
                                AccountLevelSetup::where('name', trim($desc))
                                ->where('account_strategy_setup_id', $strategyId)
                                ->update([
                                    $parentLevelId => $parentLevelArr,
                                ]);
                            }
                        }
                    }

                }
            }
            else
            {
                $parentLevelIds = [];
                $parentLevels = [];
                $submittedDescriptions = $request->input('account_desc');
                for ($i = 1; $i < $level; $i++) {
                    $parentLevelIds[] = 'parent_level' . $i . '_id';
                    $parentLevels[] = explode(',', implode(',', $request->input("acnt_lvl" . ($i))));
                }
                for ($j = 0; $j < count($parentLevels); $j++) {
                    foreach ($parentLevels[$j] as $parentLvlKey => $parentLevelArr) {
                        if (isset($submittedDescriptions[$parentLvlKey])) {
                            $DescArr = explode(',', $submittedDescriptions[$parentLvlKey]);
                            foreach ($DescArr as $desc) {
                                $currentParentLevelId = $parentLevelIds[$j] ?? null;
                                $descTrimmed = trim($desc);
                                $accountLevelSetup = AccountLevelSetup::where('name', $descTrimmed)
                                                                      ->where('account_strategy_setup_id', $strategyId)
                                                                      ->where('account_level', $level)
                                                                      ->first();
            
                                if ($accountLevelSetup) {
                                    if ($currentParentLevelId !== null) {
                                        $accountLevelSetup->{$currentParentLevelId} = $parentLevelArr;
                                    }
                                    $accountLevelSetup->save();
                                } else {
                                    $newRecord = new AccountLevelSetup();
                                    $newRecord->account_strategy_setup_id = $strategyId; 
                                    $newRecord->name = $descTrimmed; 
                                    $newRecord->account_level = $level;
                                    if ($currentParentLevelId !== null) {
                                        $newRecord->{$currentParentLevelId} = $parentLevelArr;
                                    }
                                    $newRecord->save();
                                }
                            }
                        }
                    }
                }
            }
        }
       
        if($level == $maxlevel)
        {
            $Edt = $request->input('account_edt');
            $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
            AccountLevelSetup::where('account_strategy_setup_id', $strategyId)
            ->update(['effective_timestamp' => $Edt]);

            $OrganizationName = ucwords($OrgName[0]);
            return response()->json([
                'finalize' => "Account Level Setup Successfully Completed For {$OrganizationName}",
                'btntext' => "{$btnText}",
            ]);
        }
        else{
            return response()->json([
                'success' => "Level {$level} Account Setup Successfully Completed",
                'btntext' => "{$btnText}",
            ]);
        }
        
    }

    private function createAccountSetupLog($level, $orgName, $sessionName, $timestamp)
    {
        $log = Logs::create([
            'module' => 'finance',
            'content' => "Account Level '{$level}' setup successfully completed for '{$orgName}' by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);

        return $log->id;
    }

    public function GetAccountLevelOption($level, $strategyId)
    {
        $options = [];
        for ($i = 1; $i < $level; $i++) {
            $optionsForLevel = AccountLevelSetup::where('account_level', $i)
                ->where('account_strategy_setup_id', $strategyId)
                ->where('status', 1)
                ->pluck('name', 'id')
                ->map(function ($name) {
                    return ucfirst($name);
                });
            
            $options["level{$i}"] = $optionsForLevel;
        }
        return response()->json($options);
    }

    public function ViewAccountLevels(Request $request)
    {
        $accountStrategySetupId = $request->input('accountStrategySetupId');
        $maxLevel = $request->input('maxLevel', 10); 
        $allData = AccountLevelSetup::where('account_strategy_setup_id', $accountStrategySetupId)
                                    ->where('status', 1)
                                    ->orderBy('account_level')
                                    ->get();
        $levelsData = [];
        foreach ($allData as $item) {
            $levelsData[$item->account_level][] = $item;
        }
        $hierarchy = $this->buildHierarchy($levelsData, $maxLevel);
        return response()->json($hierarchy);
    }
    
    private function buildHierarchy($levelsData, $maxLevel)
    {
        $items = [];
        $result = [];

        for ($level = 1; $level <= $maxLevel; $level++) {
            if (!isset($levelsData[$level])) {
                continue; // Skip levels with no data
            }
            foreach ($levelsData[$level] as $item) {
                $itemArray = $item->toArray();
                $ShowLevel = '';
                // $ShowLevel = numberToWordOrdinal($level).' Level';
                for ($i = 1; $i <= $level; $i++) {
                    $ShowLevel .= ($i > 1 ? '.' : '') . $i;
                }

                $itemArray['name'] = '<span style="font-weight: 400;">('.$ShowLevel.')</span>'.' <span style="font-weight: 700;">'.ucfirst($itemArray['name']).'</span>'; 
                $itemArray['children'] = [];
                $items[$item->id] = $itemArray;
            }
        }

        foreach ($items as $id => $item) {
            if ($item['account_level'] == 1) {
                $result[$id] = &$items[$id];
            } else {
                $parentIdField = 'parent_level' . ($item['account_level'] - 1) . '_id';
                $parentId = $item[$parentIdField];

                if (isset($items[$parentId])) {
                    $items[$parentId]['children'][] = &$items[$id];
                }
            }
        }
        return array_values($result);
    }

    public function GetAccountLevelData($strategyId, $currentLevel)
    {
        $query = AccountLevelSetup::where('account_level_setup.account_strategy_setup_id', $strategyId)
            ->where('account_level_setup.account_level', $currentLevel)
            ->where('account_level_setup.status', 1);

        for ($i = 1; $i < $currentLevel; $i++) {
            $parentAlias = "parent_level{$i}_setup";
            $parentCol = "parent_level{$i}_id";

            $query->leftJoin("account_level_setup as {$parentAlias}", "account_level_setup.{$parentCol}", '=', "{$parentAlias}.id");
            $query->addSelect("account_level_setup.{$parentCol}"); // Select the parent level ID
            $query->addSelect(DB::raw("MAX({$parentAlias}.name) as parent_level{$i}_name"));
            $query->addSelect("account_level_setup.effective_timestamp");
        }

        $query->addSelect(
            DB::raw("GROUP_CONCAT(account_level_setup.name SEPARATOR ', ') AS names")
        );

        for ($i = 1; $i < $currentLevel; $i++) {
            $parentCol = "parent_level{$i}_id";
            $query->groupBy("account_level_setup.{$parentCol}");
            $query->groupBy("account_level_setup.effective_timestamp");
        }

        $data = $query->get();

        foreach ($data as $item) {
            for ($i = 1; $i < $currentLevel; $i++) {
                $parentNameField = "parent_level{$i}_name";
                if (isset($item->$parentNameField)) {
                    $item->$parentNameField = ucfirst($item->$parentNameField);
                }
            }
            if (isset($item->effective_timestamp)) {
                if($item->effective_timestamp != 0 && $item->effective_timestamp != null)
                {
                    $effectiveTimestamp = Carbon::createFromTimestamp($item->effective_timestamp);
                    $item->effective_timestamp = $effectiveTimestamp->format('l d F Y - h:i A');
                }
                else
                {
                    $item->effective_timestamp = '';
                }
                
            }
        }

        return response()->json($data);
    }

    public function GetChildLevel($levelId,$colmn,$currentlevel)
    {
        $data = AccountLevelSetup::where($colmn, $levelId)
        ->where('account_level', $currentlevel)
        ->where('status', 1)
        ->select('id', 'name', 'account_level')
        ->get()
        ->map(function ($item) {
            $item->name = ucfirst($item->name);
            return $item;
        });
        return response()->json($data);
    }

    public function TransactionSourceDestinations()
    {
        $colName = 'transaction_sources_or_destinations';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.transaction-source-destination', compact('user','Organizations'));
    }

    
    public function GetTransactionSourceDestinations(Request $request)
    {
        $Id = $request->input('id');
        // dd($Id);
        $query = TransactionSourcesDestinations::select('id', 'name');
        if ($Id !== null) {
            $query->where('org_id', $Id);
        }
        $TransactionSourcesDestinationList = $query->where('status', 1)->get();
        return response()->json($TransactionSourcesDestinationList);
    }

    public function AddTransactionSourcesDestinations(TransactionSourceDestinationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->transaction_sources_or_destinations)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionSourceDestinationName = trim($request->input('transactionsd'));
        $Organization = trim($request->input('tsd_org'));
        $Edt = $request->input('tsd_edt');
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

        $TransactionSourceDestinationExists = TransactionSourcesDestinations::where('name', $TransactionSourceDestinationName)
        ->Where('org_id', $Organization)
        ->where('status', 1)
        ->exists();

        if ($TransactionSourceDestinationExists) {
            return response()->json(['info' => 'This Source/Destination already exists.']);
        }
        else
        {
            $TransactionSourceDestination = new TransactionSourcesDestinations();
            $TransactionSourceDestination->name = $TransactionSourceDestinationName;
            $TransactionSourceDestination->org_id = $Organization;
            $TransactionSourceDestination->status = $status;
            $TransactionSourceDestination->user_id = $sessionId;
            $TransactionSourceDestination->last_updated = $last_updated;
            $TransactionSourceDestination->timestamp = $timestamp;
            $TransactionSourceDestination->effective_timestamp = $Edt;
            $TransactionSourceDestination->save();


            if (empty($TransactionSourceDestination->id)) {
                return response()->json(['error' => 'Failed to create Source/Destination.']);
            }

            $logs = Logs::create([
                'module' => 'finance',
                'content' => "'{$TransactionSourceDestinationName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $TransactionSourceDestination->logid = $logs->id;
            $TransactionSourceDestination->save();
            return response()->json(['success' => 'Transaction Source/Destination created successfully']);
        }

    }

    public function GetTransactionSourcesDestinationsData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->transaction_sources_or_destinations)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionSourceDestinations = TransactionSourcesDestinations::select('transaction_source_destination.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'transaction_source_destination.org_id')
        ->orderBy('transaction_source_destination.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $TransactionSourceDestinations->where('transaction_source_destination.org_id', '=', $sessionOrg);
        }
        $TransactionSourceDestinations = $TransactionSourceDestinations;
        // ->get()

        // return DataTables::of($TransactionSourceDestinations)
        return DataTables::eloquent($TransactionSourceDestinations)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('transaction_source_destination.id', 'like', "%{$search}%")
                            ->orWhere('transaction_source_destination.name', 'like', "%{$search}%")
                            ->orWhere('transaction_source_destination.status', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('transaction_source_destination.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($TransactionSourceDestination) {
                return $TransactionSourceDestination->id;  // Raw ID value
            })
            ->editColumn('id', function ($TransactionSourceDestination) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $Name = ucwords($TransactionSourceDestination->name);

                $effectiveDate = Carbon::createFromTimestamp($TransactionSourceDestination->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($TransactionSourceDestination->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($TransactionSourceDestination->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($TransactionSourceDestination->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($TransactionSourceDestination->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'TSD';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Name))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-2"><b>Organization:</b> '.ucwords($TransactionSourceDestination->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($TransactionSourceDestination) {
                    $TransactionSourceDestinationId = $TransactionSourceDestination->id;
                    $logId = $TransactionSourceDestination->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->transaction_sources_or_destinations)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-transactionsd" data-transactionsd-id="'.$TransactionSourceDestinationId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $TransactionSourceDestination->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                
            })
            ->editColumn('status', function ($TransactionSourceDestination) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->transaction_sources_or_destinations)[3];
                return $updateStatus == 1 ? ($TransactionSourceDestination->status ? '<span class="label label-success transactionsd_status cursor-pointer" data-id="'.$TransactionSourceDestination->id.'" data-status="'.$TransactionSourceDestination->status.'">Active</span>' : '<span class="label label-danger transactionsd_status cursor-pointer" data-id="'.$TransactionSourceDestination->id.'" data-status="'.$TransactionSourceDestination->status.'">Inactive</span>') : ($TransactionSourceDestination->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateTransactionSourcesDestinationsStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->transaction_sources_or_destinations)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionSourceDestinationID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $TransactionSourceDestination = TransactionSourcesDestinations::find($TransactionSourceDestinationID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $TransactionSourceDestination->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $TransactionSourceDestination->effective_timestamp = 0;

        }
        
        $TransactionSourceDestination->status = $UpdateStatus;
        $TransactionSourceDestination->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $TransactionSourceDestinationLog = TransactionSourcesDestinations::where('id', $TransactionSourceDestinationID)->first();
        $logIds = $TransactionSourceDestinationLog->logid ? explode(',', $TransactionSourceDestinationLog->logid) : [];
        $logIds[] = $logs->id;
        $TransactionSourceDestinationLog->logid = implode(',', $logIds);
        $TransactionSourceDestinationLog->save();

        $TransactionSourceDestination->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateTransactionSourcesDestinationsModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->transaction_sources_or_destinations)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionSourceDestination = TransactionSourcesDestinations::select('transaction_source_destination.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'transaction_source_destination.org_id')
        ->where('transaction_source_destination.id', $id)
        ->first();
        $name = ucwords($TransactionSourceDestination->name);
        $orgName = ucwords($TransactionSourceDestination->orgName);
        $orgId = ($TransactionSourceDestination->org_id);
        $effective_timestamp = $TransactionSourceDestination->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $TransactionSourceDestination->id,
            'name' => $name,
            'orgName' => $orgName,
            'orgId' => $orgId,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateTransactionSourcesDestinations(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->transaction_sources_or_destinations)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionSourceDestination = TransactionSourcesDestinations::findOrFail($id);
        $TransactionSourceDestination->name = $request->input('u_transactionsd');
        $orgID = $request->input('u_tsd_org');
        if (isset($orgID)) {
            $TransactionSourceDestination->org_id = $orgID;
        }  
        $effective_date = $request->input('u_tsd_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $TransactionSourceDestination->effective_timestamp = $effective_date;
        $TransactionSourceDestination->last_updated = $this->currentDatetime;
        $TransactionSourceDestination->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $TransactionSourceDestination->save();
        if (empty($TransactionSourceDestination->id)) {
            return response()->json(['error' => 'Failed to update Transaction Source/Destination. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $TransactionSourceDestinationLog = TransactionSourcesDestinations::where('id', $TransactionSourceDestination->id)->first();
        $logIds = $TransactionSourceDestinationLog->logid ? explode(',', $TransactionSourceDestinationLog->logid) : [];
        $logIds[] = $logs->id;
        $TransactionSourceDestinationLog->logid = implode(',', $logIds);
        $TransactionSourceDestinationLog->save();
        return response()->json(['success' => 'Transaction Source/Destination Details updated successfully']);
    }

    public function FinancialLedgerTypes()
    {
        $colName = 'financial_ledger_types';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.ledger-types', compact('user','Organizations'));
    }

    public function GetFinancialLedgerTypes(Request $request)
    {
        $Id = $request->input('id');
        $query = FinancialLedgerTypes::select('id', 'name');
        if ($Id !== null) {
            $query->where('org_id', $Id);
        }
        $FinancialLedgerTypeList = $query->where('status', 1)->get();
        return response()->json($FinancialLedgerTypeList);
    }

    public function AddFinancialLedgerTypes(FinancialLedgerTypesRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->financial_ledger_types)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialLedgerType = trim($request->input('ledgertype'));
        $Organization = trim($request->input('flt_org'));
        $Edt = $request->input('flt_edt');
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

        $FinancialLedgerTypeExists = FinancialLedgerTypes::where('name', $FinancialLedgerType)
        ->Where('org_id', $Organization)
        ->where('status', 1)
        ->exists();

        if ($FinancialLedgerTypeExists) {
            return response()->json(['info' => 'This Financial Ledger Type already exists.']);
        }
        else
        {
            $LedgerType = new FinancialLedgerTypes();
            $LedgerType->name = $FinancialLedgerType;
            $LedgerType->org_id = $Organization;
            $LedgerType->status = $status;
            $LedgerType->user_id = $sessionId;
            $LedgerType->last_updated = $last_updated;
            $LedgerType->timestamp = $timestamp;
            $LedgerType->effective_timestamp = $Edt;
            $LedgerType->save();


            if (empty($LedgerType->id)) {
                return response()->json(['error' => 'Failed to create Source/Destination.']);
            }

            $logs = Logs::create([
                'module' => 'finance',
                'content' => "'{$FinancialLedgerType}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $LedgerType->logid = $logs->id;
            $LedgerType->save();
            return response()->json(['success' => 'Financial Ledger Type created successfully']);
        }

    }

    public function GetFinancialLedgerTypesData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->financial_ledger_types)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialLedgerTypeData = FinancialLedgerTypes::select('ledger_types.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'ledger_types.org_id')
        ->orderBy('ledger_types.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $FinancialLedgerTypeData->where('ledger_types.org_id', '=', $sessionOrg);
        }
        $FinancialLedgerTypeData = $FinancialLedgerTypeData;
        // ->get()
        // return DataTables::of($FinancialLedgerTypeData)
        return DataTables::eloquent($FinancialLedgerTypeData)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('ledger_types.id', 'like', "%{$search}%")
                            ->orWhere('ledger_types.name', 'like', "%{$search}%")
                            ->orWhere('ledger_types.status', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('ledger_types.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($FinancialLedgerType) {
                return $FinancialLedgerType->id;  // Raw ID value
            })
            ->editColumn('id', function ($FinancialLedgerType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $Name = ucwords($FinancialLedgerType->name);

                $effectiveDate = Carbon::createFromTimestamp($FinancialLedgerType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($FinancialLedgerType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($FinancialLedgerType->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($FinancialLedgerType->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($FinancialLedgerType->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'FLT';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Name))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-2"><b>Organization:</b> '.ucwords($FinancialLedgerType->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($FinancialLedgerType) {
                    $FinancialLedgerTypeId = $FinancialLedgerType->id;
                    $logId = $FinancialLedgerType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->financial_ledger_types)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-ledgertype" data-ledgertype-id="'.$FinancialLedgerTypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $FinancialLedgerType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                
            })
            ->editColumn('status', function ($FinancialLedgerType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->financial_ledger_types)[3];
                return $updateStatus == 1 ? ($FinancialLedgerType->status ? '<span class="label label-success ledgertype_status cursor-pointer" data-id="'.$FinancialLedgerType->id.'" data-status="'.$FinancialLedgerType->status.'">Active</span>' : '<span class="label label-danger ledgertype_status cursor-pointer" data-id="'.$FinancialLedgerType->id.'" data-status="'.$FinancialLedgerType->status.'">Inactive</span>') : ($FinancialLedgerType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateFinancialLedgerTypesStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->financial_ledger_types)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialLedgerTypeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $FinancialLedgerType = FinancialLedgerTypes::find($FinancialLedgerTypeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $FinancialLedgerType->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $FinancialLedgerType->effective_timestamp = 0;

        }
        
        $FinancialLedgerType->status = $UpdateStatus;
        $FinancialLedgerType->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $FinancialLedgerTypeLog = FinancialLedgerTypes::where('id', $FinancialLedgerTypeID)->first();
        $logIds = $FinancialLedgerTypeLog->logid ? explode(',', $FinancialLedgerTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $FinancialLedgerTypeLog->logid = implode(',', $logIds);
        $FinancialLedgerTypeLog->save();

        $FinancialLedgerType->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateFinancialLedgerTypesModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->financial_ledger_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialLedgerType = FinancialLedgerTypes::select('ledger_types.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'ledger_types.org_id')
        ->where('ledger_types.id', $id)
        ->first();

        $name = ucwords($FinancialLedgerType->name);
        $orgName = ucwords($FinancialLedgerType->orgName);
        $orgId = ($FinancialLedgerType->org_id);
        $effective_timestamp = $FinancialLedgerType->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $FinancialLedgerType->id,
            'name' => $name,
            'orgName' => $orgName,
            'orgId' => $orgId,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateFinancialLedgerTypes(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->financial_ledger_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialLedgerType = FinancialLedgerTypes::findOrFail($id);
        $FinancialLedgerType->name = $request->input('u_ledgertype');
        $orgID = $request->input('u_flt_org');
        if (isset($orgID)) {
            $FinancialLedgerType->org_id = $orgID;
        }  
        $effective_date = $request->input('u_flt_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $FinancialLedgerType->effective_timestamp = $effective_date;
        $FinancialLedgerType->last_updated = $this->currentDatetime;
        $FinancialLedgerType->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $FinancialLedgerType->save();
        if (empty($FinancialLedgerType->id)) {
            return response()->json(['error' => 'Failed to update Financial Ledger Type. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $FinancialLedgerTypeLog = FinancialLedgerTypes::where('id', $FinancialLedgerType->id)->first();
        $logIds = $FinancialLedgerTypeLog->logid ? explode(',', $FinancialLedgerTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $FinancialLedgerTypeLog->logid = implode(',', $logIds);
        $FinancialLedgerTypeLog->save();
        return response()->json(['success' => 'Financial Ledger Type Details updated successfully']);
    }

    public function PayrollAddition()
    {
        $colName = 'payroll_additions_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.finance-payroll-addition', compact('user','Organizations'));
    }

    public function AddPayrollAddition(FinancialPayrollAdditionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->payroll_additions_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollAddition = trim($request->input('payrolladdition'));
        $Organization = trim($request->input('pa_org'));
        $Edt = $request->input('pa_edt');
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

        $PayrollAdditionExists = FinancialPayrollAddition::where('name', $PayrollAddition)
        ->Where('org_id', $Organization)
        ->where('status', 1)
        ->exists();

        if ($PayrollAdditionExists) {
            return response()->json(['info' => 'This Payroll Addition already exists.']);
        }
        else
        {
            $Payroll = new FinancialPayrollAddition();
            $Payroll->name = $PayrollAddition;
            $Payroll->org_id = $Organization;
            $Payroll->status = $status;
            $Payroll->user_id = $sessionId;
            $Payroll->last_updated = $last_updated;
            $Payroll->timestamp = $timestamp;
            $Payroll->effective_timestamp = $Edt;
            $Payroll->save();


            if (empty($Payroll->id)) {
                return response()->json(['error' => 'Failed to create Payroll Addition.']);
            }

            $logs = Logs::create([
                'module' => 'finance',
                'content' => "'{$PayrollAddition}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Payroll->logid = $logs->id;
            $Payroll->save();
            return response()->json(['success' => 'Payroll Addition created successfully']);
        }

    }

    public function GetPayrollAdditionData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->payroll_additions_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialPayrollAdditionData = FinancialPayrollAddition::select('finance_payroll_addition.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'finance_payroll_addition.org_id')
        ->orderBy('finance_payroll_addition.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $FinancialPayrollAdditionData->where('finance_payroll_addition.org_id', '=', $sessionOrg);
        }
        $FinancialPayrollAdditionData = $FinancialPayrollAdditionData;
        // ->get()
        // return DataTables::of($FinancialPayrollAdditionData)
        return DataTables::eloquent($FinancialPayrollAdditionData)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('finance_payroll_addition.id', 'like', "%{$search}%")
                            ->orWhere('finance_payroll_addition.name', 'like', "%{$search}%")
                            ->orWhere('finance_payroll_addition.status', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('finance_payroll_addition.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($FinancialPayrollAddition) {
                return $FinancialPayrollAddition->id;  // Raw ID value
            })
            ->editColumn('id', function ($FinancialPayrollAddition) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $Name = ucwords($FinancialPayrollAddition->name);

                $effectiveDate = Carbon::createFromTimestamp($FinancialPayrollAddition->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($FinancialPayrollAddition->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($FinancialPayrollAddition->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($FinancialPayrollAddition->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($FinancialPayrollAddition->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'PAS';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Name))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-2"><b>Organization:</b> '.ucwords($FinancialPayrollAddition->orgName);
                }


                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($FinancialPayrollAddition) {
                    $FinancialPayrollAdditionId = $FinancialPayrollAddition->id;
                    $logId = $FinancialPayrollAddition->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->payroll_additions_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-payrolladdition" data-payrolladdition-id="'.$FinancialPayrollAdditionId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $FinancialPayrollAddition->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                
            })
            ->editColumn('status', function ($FinancialPayrollAddition) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->payroll_additions_setup)[3];
                // return $updateStatus == 1 ? ($FinancialPayrollAddition->status ? '<span class="label label-success payrolladdition_status cursor-pointer" data-id="'.$FinancialPayrollAddition->id.'" data-status="'.$FinancialPayrollAddition->status.'">Active</span>' : '<span class="label label-danger payrolladdition_status cursor-pointer" data-id="'.$FinancialPayrollAddition->id.'" data-status="'.$FinancialPayrollAddition->status.'">Inactive</span>') : ($FinancialPayrollAddition->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
                return $updateStatus == 1 ? ($FinancialPayrollAddition->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>') : ($FinancialPayrollAddition->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdatePayrollAdditionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->payroll_additions_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollAdditionID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $PayrollAddition = FinancialPayrollAddition::find($PayrollAdditionID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $PayrollAddition->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $PayrollAddition->effective_timestamp = 0;

        }
        
        $PayrollAddition->status = $UpdateStatus;
        $PayrollAddition->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PayrollAdditionLog = FinancialPayrollAddition::where('id', $PayrollAdditionID)->first();
        $logIds = $PayrollAdditionLog->logid ? explode(',', $PayrollAdditionLog->logid) : [];
        $logIds[] = $logs->id;
        $PayrollAdditionLog->logid = implode(',', $logIds);
        $PayrollAdditionLog->save();

        $PayrollAddition->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdatePayrollAdditionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->payroll_additions_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollAddition = FinancialPayrollAddition::select('finance_payroll_addition.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'finance_payroll_addition.org_id')
        ->where('finance_payroll_addition.id', $id)
        ->first();

        $name = ucwords($PayrollAddition->name);
        $orgName = ucwords($PayrollAddition->orgName);
        $orgId = ($PayrollAddition->org_id);
        $effective_timestamp = $PayrollAddition->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $PayrollAddition->id,
            'name' => $name,
            'orgName' => $orgName,
            'orgId' => $orgId,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdatePayrollAddition(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->payroll_additions_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollAddition = FinancialPayrollAddition::findOrFail($id);
        $PayrollAddition->name = $request->input('u_payrolladdition');
        $orgID = $request->input('u_pa_org');
        if (isset($orgID)) {
            $PayrollAddition->org_id = $orgID;
        }  
        $effective_date = $request->input('u_pa_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $PayrollAddition->effective_timestamp = $effective_date;
        $PayrollAddition->last_updated = $this->currentDatetime;
        $PayrollAddition->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $PayrollAddition->save();
        if (empty($PayrollAddition->id)) {
            return response()->json(['error' => 'Failed to update Payroll Addition. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PayrollAdditionLog = FinancialPayrollAddition::where('id', $PayrollAddition->id)->first();
        $logIds = $PayrollAdditionLog->logid ? explode(',', $PayrollAdditionLog->logid) : [];
        $logIds[] = $logs->id;
        $PayrollAdditionLog->logid = implode(',', $logIds);
        $PayrollAdditionLog
        
        ->save();
        return response()->json(['success' => 'Payroll Addition Details updated successfully']);
    }

    public function PayrollDeduction()
    {
        $colName = 'payroll_deduction_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.finance-payroll-deduction', compact('user','Organizations'));
    }

    public function AddPayrollDeduction(FinancialPayrollDeductionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->payroll_deduction_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollDeduction = trim($request->input('payrolldeduction'));
        $Organization = trim($request->input('pd_org'));
        $Edt = $request->input('pd_edt');
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

        $PayrollDeductionExists = FinancialPayrollDeduction::where('name', $PayrollDeduction)
        ->Where('org_id', $Organization)
        ->where('status', 1)
        ->exists();

        if ($PayrollDeductionExists) {
            return response()->json(['info' => 'This Payroll Deduction already exists.']);
        }
        else
        {
            $Payroll = new FinancialPayrollDeduction();
            $Payroll->name = $PayrollDeduction;
            $Payroll->org_id = $Organization;
            $Payroll->status = $status;
            $Payroll->user_id = $sessionId;
            $Payroll->last_updated = $last_updated;
            $Payroll->timestamp = $timestamp;
            $Payroll->effective_timestamp = $Edt;
            $Payroll->save();


            if (empty($Payroll->id)) {
                return response()->json(['error' => 'Failed to create Payroll Deduction.']);
            }

            $logs = Logs::create([
                'module' => 'finance',
                'content' => "'{$PayrollDeduction}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Payroll->logid = $logs->id;
            $Payroll->save();
            return response()->json(['success' => 'Payroll Deduction created successfully']);
        }

    }

    public function GetPayrollDeductionData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->payroll_deduction_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialPayrollDeductionData = FinancialPayrollDeduction::select('finance_payroll_deductions.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'finance_payroll_deductions.org_id')
        ->orderBy('finance_payroll_deductions.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $FinancialPayrollDeductionData->where('finance_payroll_deductions.org_id', '=', $sessionOrg);
        }
        $FinancialPayrollDeductionData = $FinancialPayrollDeductionData;
        // ->get()
        // return DataTables::of($FinancialPayrollDeductionData)
        return DataTables::eloquent($FinancialPayrollDeductionData)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('finance_payroll_deductions.id', 'like', "%{$search}%")
                            ->orWhere('finance_payroll_deductions.name', 'like', "%{$search}%")
                            ->orWhere('finance_payroll_deductions.status', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('finance_payroll_deductions.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($FinancialPayrollDeduction) {
                return $FinancialPayrollDeduction->id;  // Raw ID value
            })
            ->editColumn('id', function ($FinancialPayrollDeduction) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $Name = ucwords($FinancialPayrollDeduction->name);

                $effectiveDate = Carbon::createFromTimestamp($FinancialPayrollDeduction->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($FinancialPayrollDeduction->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($FinancialPayrollDeduction->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($FinancialPayrollDeduction->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($FinancialPayrollDeduction->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'PDS';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Name))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-2"><b>Organization:</b> '.ucwords($FinancialPayrollDeduction->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($FinancialPayrollDeduction) {
                    $FinancialPayrollDeductionId = $FinancialPayrollDeduction->id;
                    $logId = $FinancialPayrollDeduction->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->payroll_deduction_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-payrolldeduction" data-payrolldeduction-id="'.$FinancialPayrollDeductionId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $FinancialPayrollDeduction->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                
            })
            ->editColumn('status', function ($FinancialPayrollDeduction) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->payroll_deduction_setup)[3];
                // return $updateStatus == 1 ? ($FinancialPayrollDeduction->status ? '<span class="label label-success payrolldeduction_status cursor-pointer" data-id="'.$FinancialPayrollDeduction->id.'" data-status="'.$FinancialPayrollDeduction->status.'">Active</span>' : '<span class="label label-danger payrolldeduction_status cursor-pointer" data-id="'.$FinancialPayrollDeduction->id.'" data-status="'.$FinancialPayrollDeduction->status.'">Inactive</span>') : ($FinancialPayrollDeduction->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
                return $updateStatus == 1 ? ($FinancialPayrollDeduction->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>') : ($FinancialPayrollDeduction->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdatePayrollDeductionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->payroll_deduction_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollDeductionID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $PayrollDeduction = FinancialPayrollDeduction::find($PayrollDeductionID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $PayrollDeduction->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $PayrollDeduction->effective_timestamp = 0;
        }
        
        $PayrollDeduction->status = $UpdateStatus;
        $PayrollDeduction->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PayrollDeductionLog = FinancialPayrollAddition::where('id', $PayrollDeductionID)->first();
        $logIds = $PayrollDeductionLog->logid ? explode(',', $PayrollDeductionLog->logid) : [];
        $logIds[] = $logs->id;
        $PayrollDeductionLog->logid = implode(',', $logIds);
        $PayrollDeductionLog->save();

        $PayrollDeduction->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdatePayrollDeductionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->payroll_deduction_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollDeduction = FinancialPayrollDeduction::select('finance_payroll_deductions.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'finance_payroll_deductions.org_id')
        ->where('finance_payroll_deductions.id', $id)
        ->first();

        $name = ucwords($PayrollDeduction->name);
        $orgName = ucwords($PayrollDeduction->orgName);
        $orgId = ($PayrollDeduction->org_id);
        $effective_timestamp = $PayrollDeduction->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $PayrollDeduction->id,
            'name' => $name,
            'orgName' => $orgName,
            'orgId' => $orgId,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdatePayrollDeduction(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->payroll_deduction_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PayrollDeduction = FinancialPayrollDeduction::findOrFail($id);
        $PayrollDeduction->name = $request->input('u_payrolldeduction');
        $orgID = $request->input('u_pd_org');
        if (isset($orgID)) {
            $PayrollDeduction->org_id = $orgID;
        }  
        $effective_date = $request->input('u_pd_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $PayrollDeduction->effective_timestamp = $effective_date;
        $PayrollDeduction->last_updated = $this->currentDatetime;
        $PayrollDeduction->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $PayrollDeduction->save();
        if (empty($PayrollDeduction->id)) {
            return response()->json(['error' => 'Failed to update Payroll Deduction. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PayrollDeductionLog = FinancialPayrollDeduction::where('id', $PayrollDeduction->id)->first();
        $logIds = $PayrollDeductionLog->logid ? explode(',', $PayrollDeductionLog->logid) : [];
        $logIds[] = $logs->id;
        $PayrollDeductionLog->logid = implode(',', $logIds);
        $PayrollDeductionLog->save();
        return response()->json(['success' => 'Payroll Deduction Details updated successfully']);
    }

    public function RegisterDonor()
    {
        $colName = 'donors_registration';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.donor-registration', compact('user','Organizations'));
    }

    public function DonorRegistration(DonorRegistrationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->donors_registration)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Org = trim($request->input('donor_org'));
        $DonorType = trim($request->input('donor_type'));
        $CorporateName = trim($request->input('donor_corporate'));
        $FocalPersonName = trim($request->input('donor_name'));
        $FocalPersonEmail = trim($request->input('donor_email'));
        $FocalPersonCell = trim($request->input('donor_cell'));
        $FocalPersonLandline = trim($request->input('donor_landline'));
        $Address = trim($request->input('donor_address'));
        $Remarks = trim($request->input('donor_remarks'));
        $Edt = $request->input('donor_edt');
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

        $DonorExists = DonorRegistration::where('corporate_name', $CorporateName)
        ->Where('org_id', $Org)
        ->Where('person_name', $FocalPersonName)
        ->Where('person_email', $FocalPersonEmail)
        ->Where('person_cell', $FocalPersonCell)
        ->where('status', 1)
        ->exists();

        if ($DonorExists) {
            return response()->json(['info' => 'This Donor is already exists.']);
        }
        else
        {
            $RegisterDonor = new DonorRegistration();
            $RegisterDonor->org_id = $Org;
            $RegisterDonor->corporate_name = $CorporateName;
            $RegisterDonor->type = $DonorType;
            $RegisterDonor->person_name = $FocalPersonName;
            $RegisterDonor->person_email = $FocalPersonEmail;
            $RegisterDonor->person_cell = $FocalPersonCell;
            $RegisterDonor->person_landline = $FocalPersonLandline;
            $RegisterDonor->address = $Address;
            $RegisterDonor->remarks = $Remarks;
            $RegisterDonor->status = $status;
            $RegisterDonor->user_id = $sessionId;
            $RegisterDonor->last_updated = $last_updated;
            $RegisterDonor->timestamp = $timestamp;
            $RegisterDonor->effective_timestamp = $Edt;
            $RegisterDonor->save();


            if (empty($RegisterDonor->id)) {
                return response()->json(['error' => 'Failed to Register Donor. Please try again']);
            }

            $logs = Logs::create([
                'module' => 'finance',
                'content' => "Donor: '{$FocalPersonName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $RegisterDonor->logid = $logs->id;

            $orgName = Organization::find($Org)->organization;
            $emailEdt = $request->input('donor_edt');

            Mail::to($FocalPersonEmail)->send(new DonorRegistrationMail($orgName, $CorporateName,
            $DonorType, $FocalPersonName, $FocalPersonEmail, $FocalPersonCell, $FocalPersonLandline, $Address,
            $Remarks, $emailEdt));

            $RegisterDonor->save();
            return response()->json(['success' => 'Donor Registered successfully']);
        }

    }

    public function GetDonorsData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->donors_registration)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Donors = DonorRegistration::select('donors.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'donors.org_id')
        ->orderBy('donors.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $Donors->where('donors.org_id', '=', $sessionOrg);
        }
        $Donors = $Donors;
        // ->get();
        // return DataTables::of($Donors)
        return DataTables::eloquent($Donors)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('donors.id', 'like', "%{$search}%")
                            ->orWhere('donors.person_name', 'like', "%{$search}%")
                            ->orWhere('donors.person_email', 'like', "%{$search}%")
                            ->orWhere('donors.type', 'like', "%{$search}%")
                            ->orWhere('donors.corporate_name', 'like', "%{$search}%")
                            ->orWhere('donors.person_cell', 'like', "%{$search}%")
                            ->orWhere('donors.person_landline', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('donors.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Donor) {
                return $Donor->id;  
            })
            ->editColumn('id', function ($Donor) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $orgName = ucwords($Donor->orgName);
                $FocalPersonName = ucwords($Donor->person_name);
                $DonorType = ucwords($Donor->type);
                $FocalPersonEmail = ($Donor->person_email);
                $Cell = ($Donor->person_cell);
                $Landline = ($Donor->person_landline);
                if($Landline !="")
                {
                    $Landline = '<b>Landline#</b>: '.$Landline.'<br>';
                }
                $corporateName = ($Donor->corporate_name);
                if($corporateName !="")
                {
                    $corporateName = '<b>Corporate Name</b>: '.$corporateName.'<br>';
                }

                $effectiveDate = Carbon::createFromTimestamp($Donor->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Donor->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Donor->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($Donor->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($Donor->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'DNR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $FocalPersonName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-2"><b>Organization:</b> '.ucwords($Donor->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Person Name</b>: '.$FocalPersonName.'<br>'
                    . '<b>Person Email</b>: '.$FocalPersonEmail.'<br>'
                    . '<b>Donor Type</b>: '.$DonorType.'<br>'
                    . $corporateName
                    . '<b>Cell#</b>: '.$Cell.'<br>'
                    . $Landline
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($Donor) {
                    $DonorId = $Donor->id;
                    $logId = $Donor->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->donors_registration)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-donor" data-donor-id="'.$DonorId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $Donor->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                
            })
            ->editColumn('status', function ($Donor) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->donors_registration)[3];
                return $updateStatus == 1 ? ($Donor->status ? '<span class="label label-success donors_registration cursor-pointer" data-id="'.$Donor->id.'" data-status="'.$Donor->status.'">Active</span>' : '<span class="label label-danger donors_registration cursor-pointer" data-id="'.$Donor->id.'" data-status="'.$Donor->status.'">Inactive</span>') : ($Donor->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'id'])
            ->make(true);
    }

    public function UpdateDonorStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->donors_registration)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $DonorID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Donor = DonorRegistration::find($DonorID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Donor->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Donor->effective_timestamp = 0;
        }
        
        $Donor->status = $UpdateStatus;
        $Donor->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $DonorLog = DonorRegistration::where('id', $DonorID)->first();
        $logIds = $DonorLog->logid ? explode(',', $DonorLog->logid) : [];
        $logIds[] = $logs->id;
        $DonorLog->logid = implode(',', $logIds);
        $DonorLog->save();

        $Donor->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateDonorModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->donors_registration)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Donor = DonorRegistration::select('donors.*',
        'organization.organization as orgName')
        ->join('organization', 'organization.id', '=', 'donors.org_id')
        ->where('donors.id', $id)
        ->first();

        $CorporateName = ucwords($Donor->corporate_name);
        $Type = ucwords($Donor->type);
        $PersonName = ucwords($Donor->person_name);
        $PersonEmail = ($Donor->person_email);
        $Cell = ($Donor->person_cell);
        $landline = ($Donor->person_landline);
        $Address = ucwords($Donor->address);
        $Remarks = ucwords($Donor->remarks);
        $orgName = ucwords($Donor->orgName);
        $orgId = ($Donor->org_id);
        $effective_timestamp = $Donor->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $Donor->id,
            'CorporateName' => $CorporateName,
            'Type' => $Type,
            'PersonName' => $PersonName,
            'PersonEmail' => $PersonEmail,
            'Cell' => $Cell,
            'landline' => $landline,
            'Address' => $Address,
            'Remarks' => $Remarks,
            'orgName' => $orgName,
            'orgId' => $orgId,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateDonor(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->donors_registration)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Donor = DonorRegistration::findOrFail($id);
        $orgID = $request->input('u_donor_org');
        if (isset($orgID)) {
            $Donor->org_id = $orgID;
        }  
        $Donor->type	 = $request->input('u_donor_type');
        $Donor->corporate_name = $request->input('u_donor_corporate');
        $Donor->person_name = $request->input('u_donor_name');
        $Donor->person_email = $request->input('u_donor_email');
        $Donor->person_cell = $request->input('u_donor_cell');
        $Donor->person_landline = $request->input('u_donor_landline');
        $Donor->address = $request->input('u_donor_address');
        $Donor->remarks = $request->input('u_donor_remarks');
        $effective_date = $request->input('u_donor_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $Donor->effective_timestamp = $effective_date;
        $Donor->last_updated = $this->currentDatetime;
        $Donor->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Donor->save();
        if (empty($Donor->id)) {
            return response()->json(['error' => 'Failed to update Donor Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $DonorLog = FinancialPayrollDeduction::where('id', $Donor->id)->first();
        $logIds = $DonorLog->logid ? explode(',', $DonorLog->logid) : [];
        $logIds[] = $logs->id;
        $DonorLog->logid = implode(',', $logIds);
        $DonorLog->save();
        return response()->json(['success' => 'Donor Details updated successfully']);
    }

    public function FinanceTransactionType()
    {
        $colName = 'finance_transaction_types';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.finance-transaction-type', compact('user','Organizations'));
    }

    public function AddFinanceTransactionType(FinanceTransactionTypeRequst $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->finance_transaction_types)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = ($request->input('ftt_org'));
        $Desc = trim($request->input('ftt_desc'));
        $ActivityType = trim($request->input('ftt_activitytype'));
        $Source = ($request->input('ftt_source'));
        $Destination = ($request->input('ftt_destination'));
        $DebitAccount = ($request->input('ftt_debit'));
        $CeditAccount = ($request->input('ftt_credit'));
        $LedgerType = ($request->input('ftt_ledger'));
        $AmountEditable = ($request->input('ftt_amounteditable'));
        $AmountCeiling = ($request->input('ftt_amountceiling'));
        $DiscountAllowed = ($request->input('ftt_discountallowed'));
        $Edt = $request->input('ftt_edt');
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

        $FinancialTransactionTypeExists = FinancialTransactionTypes::where('name', $Desc)
        ->Where('org_id', $Organization)
        ->Where('transaction_source_id', $Source)
        ->Where('transaction_destination_id', $Destination)
        ->Where('debit_account', $DebitAccount)
        ->Where('credit_account', $CeditAccount)
        ->where('status', 1)
        ->exists();

        if ($FinancialTransactionTypeExists) {
            return response()->json(['info' => 'This Transaction Type already exists.']);
        }
        else
        {
            $FinancialTransactionType = new FinancialTransactionTypes();
            $FinancialTransactionType->name = $Desc;
            $FinancialTransactionType->org_id = $Organization;
            $FinancialTransactionType->activity = $ActivityType;
            $FinancialTransactionType->transaction_source_id = $Source;
            $FinancialTransactionType->transaction_destination_id = $Destination;
            $FinancialTransactionType->debit_account = $DebitAccount;
            $FinancialTransactionType->credit_account = $CeditAccount;
            $FinancialTransactionType->ledger_id = $LedgerType;
            $FinancialTransactionType->amount_editable = $AmountEditable;
            $FinancialTransactionType->amount_ceiling = $AmountCeiling;
            $FinancialTransactionType->discount_allowed = $DiscountAllowed;
            $FinancialTransactionType->status = $status;
            $FinancialTransactionType->user_id = $sessionId;
            $FinancialTransactionType->last_updated = $last_updated;
            $FinancialTransactionType->timestamp = $timestamp;
            $FinancialTransactionType->effective_timestamp = $Edt;
            $FinancialTransactionType->save();


            if (empty($FinancialTransactionType->id)) {
                return response()->json(['error' => 'Failed to create Financial Transaction Type.']);
            }

            $logs = Logs::create([
                'module' => 'finance',
                'content' => "'{$Desc}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $FinancialTransactionType->logid = $logs->id;
            $FinancialTransactionType->save();
            return response()->json(['success' => 'Financial Transaction Type created successfully']);
        }
    }

    public function FinanceTransactionTypeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->finance_transaction_types)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionTypes = FinancialTransactionTypes::select('finance_transaction_type.*',
        'organization.organization as orgName','organization.code as orgCode','source.name as TransactionSourceName',
        'destination.name as TransactionDestinationName',
        'debit.name as DebitAccount',
        'credit.name as CreditAccount',
        'ledger_types.name as Ledger'
        )
        ->join('organization', 'organization.id', '=', 'finance_transaction_type.org_id')
        ->join('transaction_source_destination as source', 'source.id', '=', 'finance_transaction_type.transaction_source_id')
        ->join('transaction_source_destination as destination', 'destination.id', '=', 'finance_transaction_type.transaction_destination_id')
        ->join('account_level_setup as debit', 'debit.id', '=', 'finance_transaction_type.debit_account')
        ->join('account_level_setup as credit', 'credit.id', '=', 'finance_transaction_type.credit_account')
        ->join('ledger_types', 'ledger_types.id', '=', 'finance_transaction_type.ledger_id')
        ->orderBy('finance_transaction_type.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $TransactionTypes->where('finance_transaction_type.org_id', '=', $sessionOrg);
        }
        $TransactionTypes = $TransactionTypes;
        // ->get()
        // return DataTables::of($TransactionTypes)
        return DataTables::eloquent($TransactionTypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('finance_transaction_type.id', 'like', "%{$search}%")
                            ->orWhere('finance_transaction_type.name', 'like', "%{$search}%")
                            ->orWhere('finance_transaction_type.activity', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('organization.code', 'like', "%{$search}%")
                            ->orWhere('source.name', 'like', "%{$search}%")
                            ->orWhere('destination.name', 'like', "%{$search}%")
                            ->orWhere('ledger_types.name', 'like', "%{$search}%")
                            ->orWhere('debit.name', 'like', "%{$search}%")
                            ->orWhere('credit.name', 'like', "%{$search}%")
                            ->orWhere('finance_transaction_type.amount_ceiling', 'like', "%{$search}%")
                            ->orWhere('finance_transaction_type.discount_allowed', 'like', "%{$search}%")
                            ->orWhere('finance_transaction_type.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($TansactionType) {
                return $TansactionType->id;  
            })
            ->editColumn('details', function ($TansactionType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $TansactionTypeDesc = ucwords($TansactionType->name);
                $Activity = ucwords($TansactionType->activity);
                $orgName = ucwords($TansactionType->orgName);
                $orgCode = ucwords($TansactionType->orgCode);
                $Source = ucwords($TansactionType->TransactionSourceName);
                $Desination = ucwords($TansactionType->TransactionDestinationName);
                $effectiveDate = Carbon::createFromTimestamp($TansactionType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($TansactionType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($TansactionType->last_updated)->format('l d F Y - h:i A');
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($sessionName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $idStr = str_pad($TansactionType->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'TTY';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $orgCode))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-2"><b>Organization:</b> '.ucwords($TansactionType->orgName);
                }

                return $Code.$orgName
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Description</b>: '.$TansactionTypeDesc.'<br>'
                    . '<b>Activity</b>: '.$Activity.'<br>'
                    . '<b>Source Type</b>: '.$Source.'<br>'
                    . '<b>Destination Type</b>: '.$Desination.'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('account', function ($TansactionType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $DebitAccount = ucwords($TansactionType->DebitAccount);
                $CreditAccount = ucwords($TansactionType->CreditAccount);
                $AmountEditable = ucwords($TansactionType->amount_editable);
                $AmountCeiling = ucwords($TansactionType->amount_ceiling);
                $DiscountAllowed = ucwords($TansactionType->discount_allowed);
                $Ledger = ucwords($TansactionType->Ledger);
                if($AmountCeiling !="")
                {
                    $AmountCeiling = '<b>Amount Ceiling#</b>: Rs '.number_format($AmountCeiling,2).'<br>';
                }

                return $Ledger
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Debit Account</b>: '.$DebitAccount.'<br>'
                    . '<b>Credit Account</b>: '.$CreditAccount.'<br>'
                    . '<b>Amount Editable</b>: '.$AmountEditable.'<br>'
                    . $AmountCeiling
                    . '<b>Discount Allowed</b>: '.$DiscountAllowed.'<br>'
                   ;
            })
            ->addColumn('action', function ($TansactionType) {
                    $TansactionTypeId = $TansactionType->id;
                    $logId = $TansactionType->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->finance_transaction_types)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-ftt" data-ftt-id="'.$TansactionTypeId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                   
                    return $TansactionType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
                
            })
            ->editColumn('status', function ($TansactionType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->finance_transaction_types)[3];
                return $updateStatus == 1 ? ($TansactionType->status ? '<span class="label label-success ftt_status cursor-pointer" data-id="'.$TansactionType->id.'" data-status="'.$TansactionType->status.'">Active</span>' : '<span class="label label-danger ftt_status cursor-pointer" data-id="'.$TansactionType->id.'" data-status="'.$TansactionType->status.'">Inactive</span>') : ($TansactionType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'details','account'])
            ->make(true);
    }

    public function UpdateTransactionTypeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->finance_transaction_types)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $TransactionType = FinancialTransactionTypes::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $TransactionType->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $TransactionType->effective_timestamp = 0;
        }
        
        $TransactionType->status = $UpdateStatus;
        $TransactionType->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $TransactionTypeLog = FinancialTransactionTypes::where('id', $ID)->first();
        $logIds = $TransactionTypeLog->logid ? explode(',', $TransactionTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $TransactionTypeLog->logid = implode(',', $logIds);
        $TransactionTypeLog->save();

        $TransactionType->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateTransactionTypeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->finance_transaction_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionTypes = FinancialTransactionTypes::select('finance_transaction_type.*',
        'organization.organization as orgName','source.name as TransactionSourceName',
        'destination.name as TransactionDestinationName',
        'debit.name as DebitAccount',
        'credit.name as CreditAccount',
        'ledger_types.name as Ledger'
        )
        ->join('organization', 'organization.id', '=', 'finance_transaction_type.org_id')
        ->join('transaction_source_destination as source', 'source.id', '=', 'finance_transaction_type.transaction_source_id')
        ->join('transaction_source_destination as destination', 'destination.id', '=', 'finance_transaction_type.transaction_destination_id')
        ->join('account_level_setup as debit', 'debit.id', '=', 'finance_transaction_type.debit_account')
        ->join('account_level_setup as credit', 'credit.id', '=', 'finance_transaction_type.credit_account')
        ->join('ledger_types', 'ledger_types.id', '=', 'finance_transaction_type.ledger_id')
        ->where('finance_transaction_type.id', $id)
        ->first();

        $orgName = ucwords($TransactionTypes->orgName);
        $TransactionSourceName = ucwords($TransactionTypes->TransactionSourceName);
        $TransactionDestinationName = ucwords($TransactionTypes->TransactionDestinationName);
        $DebitAccount = ucwords($TransactionTypes->DebitAccount);
        $CreditAccount = ucwords($TransactionTypes->CreditAccount);
        $Ledger = ucwords($TransactionTypes->Ledger);
        $effective_timestamp = $TransactionTypes->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $TransactionTypes->id,
            'orgName' => $orgName,
            'orgId' => $TransactionTypes->org_id,
            'Description' => ucwords($TransactionTypes->name),
            'Activity' => ($TransactionTypes->activity),
            'dispActivity' => ucwords($TransactionTypes->activity),
            'TransactionSourceName' => $TransactionSourceName,
            'TransactionSourceId' => $TransactionTypes->transaction_source_id,
            'TransactionDestinationName' => $TransactionDestinationName,
            'TransactionDestinationId' => $TransactionTypes->transaction_destination_id,
            'DebitAccount' => $DebitAccount,
            'DebitAccountId' => $TransactionTypes->debit_account,
            'CreditAccount' => $CreditAccount,
            'CreditAccountId' => $TransactionTypes->credit_account,
            'Ledger' => $Ledger,
            'LedgerId' => $TransactionTypes->ledger_id,
            'AmountEditable' => $TransactionTypes->amount_editable,
            'DisplayAmountEditable' => ucwords($TransactionTypes->amount_editable),
            'AmountCeiling' => $TransactionTypes->amount_ceiling,
            'DiscountAllowed' => $TransactionTypes->discount_allowed,
            'DisplayDiscountAllowed' => ucwords($TransactionTypes->discount_allowed),
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateFinanceTransactiontype(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->finance_transaction_types)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $TransactionType = FinancialTransactionTypes::findOrFail($id);
        $orgID = $request->input('u_ftt_org');
        if (isset($orgID)) {
            $TransactionType->org_id = $orgID;
        }  
        
        $TransactionType->name	 = $request->input('u_ftt_desc');
        $TransactionType->activity = $request->input('u_ftt_activity');
        $TransactionType->transaction_source_id = $request->input('u_ftt_source');
        $TransactionType->transaction_destination_id = $request->input('u_ftt_destination');
        $TransactionType->debit_account = $request->input('u_ftt_debit');
        $TransactionType->debit_account = $request->input('u_ftt_credit');
        $TransactionType->ledger_id = $request->input('u_ftt_ledger');
        $TransactionType->amount_editable = $request->input('u_ftt_amounteditable');
        $TransactionType->amount_ceiling = $request->input('u_ftt_amountceiling');
        $TransactionType->discount_allowed = $request->input('u_ftt_discountallowed');
        $effective_date = $request->input('u_ftt_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $TransactionType->effective_timestamp = $effective_date;
        $TransactionType->last_updated = $this->currentDatetime;
        $TransactionType->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $TransactionType->save();
        if (empty($TransactionType->id)) {
            return response()->json(['error' => 'Failed to update Transaction Type Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $TransactionTypeLog = FinancialTransactionTypes::where('id', $TransactionType->id)->first();
        $logIds = $TransactionTypeLog->logid ? explode(',', $TransactionTypeLog->logid) : [];
        $logIds[] = $logs->id;
        $TransactionTypeLog->logid = implode(',', $logIds);
        $TransactionTypeLog->save();
        return response()->json(['success' => 'Transaction Type Details updated successfully']);
    }

    public function FinanceReceiving()
    {
        $colName = 'finance_receiving';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.finance-transaction-receiving', compact('user','Organizations'));
    }

    public function GetFinanceTransactionTypes(Request $request)
    {
        $Id = $request->input('id');
        $query = FinancialTransactionTypes::select('id', 'name', 'discount_allowed');
        if ($Id !== null) {
            $query->where('org_id', $Id);
        }
        $TransactionTypeList = $query->where('status', 1)->get();
        return response()->json($TransactionTypeList);
    }


    public function AddFinanceReceiving(FinancialTransactionRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->finance_receiving)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = ($request->input('fr_org'));
        $Site = trim($request->input('fr_site'));
        $TransactionType = trim($request->input('fr_transactiontype'));
        $PaymentOption = ($request->input('fr_paymentoption'));
        $PaymentOptionDetails = ($request->input('fr_paymentoptiondetails'));
        $Amount = ($request->input('fr_amount'));
        $Discount = ($request->input('fr_discount'));
        $Remarks = ($request->input('fr_remarks'));
        $Edt = $request->input('fr_edt');
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

        $FinancialTransaction = new FinancialTransactions();
        $FinancialTransaction->org_id = $Organization;
        $FinancialTransaction->site_id = $Site;
        $FinancialTransaction->transaction_type_id = $TransactionType;
        $FinancialTransaction->payment_option = $PaymentOption;
        $FinancialTransaction->payment_option_detail = $PaymentOptionDetails;
        $FinancialTransaction->amount = $Amount;
        $FinancialTransaction->discount = $Discount;
        $FinancialTransaction->debit = 1;
        $FinancialTransaction->credit = 0;
        $FinancialTransaction->remarks = $Remarks;
        $FinancialTransaction->status = $status;
        $FinancialTransaction->user_id = $sessionId;
        $FinancialTransaction->last_updated = $last_updated;
        $FinancialTransaction->timestamp = $timestamp;
        $FinancialTransaction->effective_timestamp = $Edt;
        $FinancialTransaction->save();


        if (empty($FinancialTransaction->id)) {
            return response()->json(['error' => 'Failed To Create This Financial Payments.']);
        }

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Receiving Transaction '{$Remarks}' has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $FinancialTransaction->logid = $logs->id;
        $FinancialTransaction->save();
        return response()->json(['success' => 'Financial Receiving created successfully']);
    }

    public function FinanceReceivingData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->finance_receiving)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinanceTransactionTypes = FinancialTransactions::select('finance_transactions.*',
            'organization.organization as orgName','org_site.name as siteName',
            'finance_transaction_type.name as TransactionTypeName',
            'ledger_types.name as ledgertypeName',
            'account_level_setup.name as AccountName')
            ->join('organization', 'organization.id', '=', 'finance_transactions.org_id')
            ->join('org_site', 'org_site.id', '=', 'finance_transactions.site_id')
            ->leftJoin('finance_transaction_type', 'finance_transaction_type.id', '=', 'finance_transactions.transaction_type_id')
            ->leftJoin('ledger_types', 'ledger_types.id', '=', 'finance_transaction_type.ledger_id')
            ->leftJoin('account_level_setup', 'account_level_setup.id', '=', 'finance_transaction_type.debit_account')
            ->orderBy('finance_transactions.id', 'desc')
            ->where('debit', '1');

            $session = auth()->user();
            $sessionOrg = $session->org_id;
            if($sessionOrg != '0')
            {
                $FinanceTransactionTypes->where('finance_transactions.org_id', '=', $sessionOrg);
            }
            $FinanceTransactionTypes = $FinanceTransactionTypes;
    
            // ->get()
        // return DataTables::of($FinanceTransactionTypes)
        return DataTables::eloquent($FinanceTransactionTypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('finance_transactions.id', 'like', "%{$search}%")
                            ->orWhere('finance_transaction_type.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('ledger_types.name', 'like', "%{$search}%")
                            ->orWhere('account_level_setup.name', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.payment_option', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.payment_option_detail', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.remarks', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($FinanceTransactionType) {
                return $FinanceTransactionType->id;  
            })
            ->editColumn('details', function ($FinanceTransactionType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionID = $session->id;
                $TransactionType = ucwords($FinanceTransactionType->TransactionTypeName);
                $LedgerType = ucwords($FinanceTransactionType->ledgertypeName);
                $orgName = ucwords($FinanceTransactionType->orgName);
                $siteName = ucwords($FinanceTransactionType->siteName);
                $PaymentOption = ucwords($FinanceTransactionType->payment_option);
                $PaymentOptionDetail = ucwords($FinanceTransactionType->payment_option_detail);
                $Remarks = ucwords($FinanceTransactionType->remarks);
                if($PaymentOptionDetail != null)
                {
                    $PaymentOptionDetail = '<b>Payment Option Detail</b>: '.$PaymentOptionDetail.'<br>';
                }
    
                $effectiveDate = Carbon::createFromTimestamp($FinanceTransactionType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($FinanceTransactionType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($FinanceTransactionType->last_updated)->format('l d F Y - h:i A');
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($sessionName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;
    
                        
                $idStr = str_pad($FinanceTransactionType->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'REC';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Remarks))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($FinanceTransactionType->orgName).'<br>';
                }

                return $Code.'<hr class="mt-1 mb-2">'.$Remarks
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Transaction Type</b>: '.$TransactionType.'<br>'
                    . '<b>Ledger</b>: '.$LedgerType.'<br>'
                    . $orgName
                    . '<b>Site</b>: '.$siteName.'<br>'
                    . '<b>Payment Option</b>: '.$PaymentOption.'<br>'
                    . $PaymentOptionDetail
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('debit', function ($FinanceTransactionType) {
                $DebitAccount = ucwords($FinanceTransactionType->AccountName);
                $DebitAmount = $FinanceTransactionType->amount;
                $DiscountAmount =$FinanceTransactionType->discount;
                $TotalDebitAmount = number_format($DebitAmount - $DiscountAmount,2);
    
                return '<b>Debit Amount</b>: Rs '.number_format($DebitAmount,2).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Discount</b>: Rs '.number_format($DiscountAmount,2).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Total Amount</b>: Rs '.$TotalDebitAmount.'<br>'
                    . '<hr class="mt-1 mb-2">';

            })
            ->editColumn('account_balance', function ($FinanceTransactionType) {
                $DebitAmount = number_format($FinanceTransactionType->amount,2);
                $DebitAccount = ucwords($FinanceTransactionType->AccountName);
                $TransactionTypeId = ($FinanceTransactionType->transaction_type_id);
                $OrgId = ($FinanceTransactionType->org_id);

                $balanceQuery = DB::table('finance_transactions')
                ->selectRaw('SUM(CASE WHEN debit = 1 THEN amount - IFNULL(discount, 0) ELSE -(amount - IFNULL(discount, 0)) END) AS balance')
                ->where('transaction_type_id', $TransactionTypeId)
                ->where('org_id', $OrgId)
                ->first();
                $balance = $balanceQuery->balance ?? 0;

                return $DebitAccount
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Balance</b>: Rs '.number_format($balance,2).'<br>'
                    . '<hr class="mt-1 mb-2">';
            })
            ->addColumn('action', function ($FinanceTransactionType) {
                $FinanceTransactionTypeId = $FinanceTransactionType->id;
                $logId = $FinanceTransactionType->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->finance_receiving)[2];
                $actionButtons = '';
                if ($edit == 1) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-ft" data-ft-id="'.$FinanceTransactionTypeId.'">'
                    . '<i class="fa fa-edit"></i> Edit'
                    . '</button>';
                }
                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';
                
                return $FinanceTransactionType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($FinanceTransactionType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->finance_receiving)[3];
                return $updateStatus == 1 ? ($FinanceTransactionType->status ? '<span class="label label-success ft_status cursor-pointer" data-id="'.$FinanceTransactionType->id.'" data-status="'.$FinanceTransactionType->status.'">Active</span>' : '<span class="label label-danger ft_status cursor-pointer" data-id="'.$FinanceTransactionType->id.'" data-status="'.$FinanceTransactionType->status.'">Inactive</span>') : ($FinanceTransactionType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'details','account_balance','debit'])
            ->make(true);
    }

    public function UpdateFinanceReceivingStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->finance_receiving)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $FinanceTransaction = FinancialTransactions::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $FinanceTransaction->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $FinanceTransaction->effective_timestamp = 0;
        }
        
        $FinanceTransaction->status = $UpdateStatus;
        $FinanceTransaction->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $FinanceTransactionLog = FinancialTransactions::where('id', $ID)->first();
        $logIds = $FinanceTransactionLog->logid ? explode(',', $FinanceTransactionLog->logid) : [];
        $logIds[] = $logs->id;
        $FinanceTransactionLog->logid = implode(',', $logIds);
        $FinanceTransactionLog->save();

        $FinanceTransaction->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateFinanceReceivingModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->finance_receiving)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinanceTransaction = FinancialTransactions::select('finance_transactions.*',
        'organization.organization as orgName','org_site.name as siteName',
        'finance_transaction_type.name as TransactionTypeName',
        'finance_transaction_type.discount_allowed as DiscountAllowed',
        'finance_transaction_type.amount_editable as AmountEditable',
        'finance_transaction_type.amount_ceiling as CeilingAmount',
        'ledger_types.name as ledgertypeName',
        'account_level_setup.name as AccountName')
        ->join('organization', 'organization.id', '=', 'finance_transactions.org_id')
        ->join('org_site', 'org_site.id', '=', 'finance_transactions.site_id')
        ->leftJoin('finance_transaction_type', 'finance_transaction_type.id', '=', 'finance_transactions.transaction_type_id')
        ->join('ledger_types', 'ledger_types.id', '=', 'finance_transaction_type.ledger_id')
        ->join('account_level_setup', 'account_level_setup.id', '=', 'finance_transaction_type.debit_account')
        ->where('finance_transactions.id', $id)
        ->first();

        $orgName = ucwords($FinanceTransaction->orgName);
        $siteName = ucwords($FinanceTransaction->siteName);
        $TransactionTypeName = ucwords($FinanceTransaction->TransactionTypeName);
        $PaymentOption = ucwords($FinanceTransaction->payment_option);
        $PaymentOptionDetail = ucwords($FinanceTransaction->payment_option_detail);
        $Amount = ($FinanceTransaction->amount);
        $Discount = ($FinanceTransaction->discount);
        $Remarks = ucwords($FinanceTransaction->remarks);
        $effective_timestamp = $FinanceTransaction->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $FinanceTransaction->id,
            'orgName' => $orgName,
            'orgId' => $FinanceTransaction->org_id,
            'siteName' => $siteName,
            'siteId' => $FinanceTransaction->site_id,
            'TransactionTypeName' => $TransactionTypeName,
            'TransactionTypeId' => $FinanceTransaction->transaction_type_id,
            'DiscountAllowed' => $FinanceTransaction->DiscountAllowed,
            'AmountEditable' => $FinanceTransaction->AmountEditable,
            'CeilingAmount' => $FinanceTransaction->CeilingAmount,
            'PaymentOption' => $FinanceTransaction->payment_option,
            'PaymentOptionDetail' => $FinanceTransaction->payment_option_detail,
            'Amount' => $FinanceTransaction->amount,
            'Discount' => $FinanceTransaction->discount,
            'Remarks' => $FinanceTransaction->remarks,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateFinanceReceiving(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->finance_receiving)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialTransaction = FinancialTransactions::findOrFail($id);
        $orgID = $request->input('u_fr_org');
        if (isset($orgID)) {
            $FinancialTransaction->org_id = $orgID;
        }  
        $FinancialTransaction->site_id	 = $request->input('u_fr_site');
        $FinancialTransaction->transaction_type_id = $request->input('u_fr_transactiontype');
        $FinancialTransaction->payment_option = $request->input('u_fr_paymentoption');
        $FinancialTransaction->payment_option_detail = $request->input('u_fr_paymentoptiondetails');
        $FinancialTransaction->amount = $request->input('u_fr_amount');
        $FinancialTransaction->discount = $request->input('u_fr_discount');
        $FinancialTransaction->remarks = $request->input('u_fr_remarks');
        $effective_date = $request->input('u_fr_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $FinancialTransaction->effective_timestamp = $effective_date;
        $FinancialTransaction->last_updated = $this->currentDatetime;
        $FinancialTransaction->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $FinancialTransaction->save();
        if (empty($FinancialTransaction->id)) {
            return response()->json(['error' => 'Failed to update Financial Receiving Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $FinancialTransactionLog = FinancialTransactions::where('id', $FinancialTransaction->id)->first();
        $logIds = $FinancialTransactionLog->logid ? explode(',', $FinancialTransactionLog->logid) : [];
        $logIds[] = $logs->id;
        $FinancialTransactionLog->logid = implode(',', $logIds);
        $FinancialTransactionLog->save();
        return response()->json(['success' => 'Financial Receiving Details updated successfully']);
    }

    public function FinancePayments()
    {
        $colName = 'finance_payment';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.finance-transaction-payments', compact('user','Organizations'));
    }

    public function AddFinancePayment(FinancialPaymentRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->finance_payment)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = ($request->input('fp_org'));
        $Site = trim($request->input('fp_site'));
        $TransactionType = trim($request->input('fp_transactiontype'));
        $PaymentOption = ($request->input('fp_paymentoption'));
        $PaymentOptionDetails = ($request->input('fp_paymentoptiondetails'));
        $Amount = ($request->input('fp_amount'));
        $Discount = ($request->input('fp_discount'));
        $Remarks = ($request->input('fp_remarks'));
        $Edt = $request->input('fp_edt');
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

        $FinancialTransaction = new FinancialTransactions();
        $FinancialTransaction->org_id = $Organization;
        $FinancialTransaction->site_id = $Site;
        $FinancialTransaction->transaction_type_id = $TransactionType;
        $FinancialTransaction->payment_option = $PaymentOption;
        $FinancialTransaction->payment_option_detail = $PaymentOptionDetails;
        $FinancialTransaction->amount = $Amount;
        $FinancialTransaction->discount = $Discount;
        $FinancialTransaction->debit = 0;
        $FinancialTransaction->credit = 1;
        $FinancialTransaction->remarks = $Remarks;
        $FinancialTransaction->status = $status;
        $FinancialTransaction->user_id = $sessionId;
        $FinancialTransaction->last_updated = $last_updated;
        $FinancialTransaction->timestamp = $timestamp;
        $FinancialTransaction->effective_timestamp = $Edt;
        $FinancialTransaction->save();


        if (empty($FinancialTransaction->id)) {
            return response()->json(['error' => 'Failed To Create This Financial Payment.']);
        }

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Financial Payment Transaction '{$Remarks}' has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $FinancialTransaction->logid = $logs->id;
        $FinancialTransaction->save();
        return response()->json(['success' => 'Financial Payment created successfully']);
    }

    public function FinancePaymentData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->finance_payment)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinanceTransactionTypes = FinancialTransactions::select('finance_transactions.*',
            'organization.organization as orgName','org_site.name as siteName',
            'finance_transaction_type.name as TransactionTypeName',
            'ledger_types.name as ledgertypeName',
            'account_level_setup.name as AccountName')
            ->join('organization', 'organization.id', '=', 'finance_transactions.org_id')
            ->join('org_site', 'org_site.id', '=', 'finance_transactions.site_id')
            ->join('finance_transaction_type', 'finance_transaction_type.id', '=', 'finance_transactions.transaction_type_id')
            ->join('ledger_types', 'ledger_types.id', '=', 'finance_transaction_type.ledger_id')
            ->join('account_level_setup', 'account_level_setup.id', '=', 'finance_transaction_type.debit_account')
            ->orderBy('finance_transactions.id', 'desc')
            ->where('credit', '1');
            // ->get();
    
        // return DataTables::of($FinanceTransactionTypes)
        return DataTables::eloquent($FinanceTransactionTypes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('finance_transactions.id', 'like', "%{$search}%")
                            ->orWhere('finance_transaction_type.name', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('org_site.name', 'like', "%{$search}%")
                            ->orWhere('ledger_types.name', 'like', "%{$search}%")
                            ->orWhere('account_level_setup.name', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.payment_option', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.payment_option_detail', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.remarks', 'like', "%{$search}%")
                            ->orWhere('finance_transactions.timestamp', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($FinanceTransactionType) {
                return $FinanceTransactionType->id;  
            })
            ->editColumn('details', function ($FinanceTransactionType) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionID = $session->id;
                $TransactionType = ucwords($FinanceTransactionType->TransactionTypeName);
                $LedgerType = ucwords($FinanceTransactionType->ledgertypeName);
                $orgName = ucwords($FinanceTransactionType->orgName);
                $siteName = ucwords($FinanceTransactionType->siteName);
                $PaymentOption = ucwords($FinanceTransactionType->payment_option);
                $PaymentOptionDetail = ucwords($FinanceTransactionType->payment_option_detail);
                $Remarks = ucwords($FinanceTransactionType->remarks);
                if($PaymentOptionDetail != null)
                {
                    $PaymentOptionDetail = '<b>Payment Option Detail</b>: '.$PaymentOptionDetail.'<br>';
                }
    
                $effectiveDate = Carbon::createFromTimestamp($FinanceTransactionType->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($FinanceTransactionType->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($FinanceTransactionType->last_updated)->format('l d F Y - h:i A');
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($sessionName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;
                            
                $idStr = str_pad($FinanceTransactionType->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'PAY';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $Remarks))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($FinanceTransactionType->orgName).'<br>';
                }

                return $Code.'<hr class="mt-1 mb-2">'.$Remarks
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Transaction Type</b>: '.$TransactionType.'<br>'
                    . '<b>Ledger</b>: '.$LedgerType.'<br>'
                    .  $orgName
                    . '<b>Site</b>: '.$siteName.'<br>'
                    . '<b>Payment Option</b>: '.$PaymentOption.'<br>'
                    . $PaymentOptionDetail
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('debit', function ($FinanceTransactionType) {
                $CreditAmount = $FinanceTransactionType->amount;
                $DiscountAmount =$FinanceTransactionType->discount;
                $TotalCreditAmount = number_format($CreditAmount - $DiscountAmount,2);
    
                return '<b>Credit Amount</b>: Rs '.number_format($CreditAmount,2).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Discount</b>: Rs '.number_format($DiscountAmount,2).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<b>Total Amount</b>: Rs '.$TotalCreditAmount.'<br>'
                    . '<hr class="mt-1 mb-2">';

            })
            ->editColumn('account_balance', function ($FinanceTransactionType) {
                $CreditAmount = number_format($FinanceTransactionType->amount,2);
                $CreditAccount = ucwords($FinanceTransactionType->AccountName);
                $TransactionTypeId = ($FinanceTransactionType->transaction_type_id);
                $OrgId = ($FinanceTransactionType->org_id);

                $balanceQuery = DB::table('finance_transactions')
                ->selectRaw('SUM(CASE WHEN debit = 1 THEN amount - IFNULL(discount, 0) ELSE -(amount - IFNULL(discount, 0)) END) AS balance')
                ->where('transaction_type_id', $TransactionTypeId)
                ->where('org_id', $OrgId)
                ->first();
                $balance = $balanceQuery->balance ?? 0;

                return $CreditAccount
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Balance</b>: Rs '.number_format($balance,2).'<br>'
                    . '<hr class="mt-1 mb-2">';
            })
            ->addColumn('action', function ($FinanceTransactionType) {
                $FinanceTransactionTypeId = $FinanceTransactionType->id;
                $logId = $FinanceTransactionType->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->finance_payment)[2];
                $actionButtons = '';
                if ($edit == 1) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-ft" data-ft-id="'.$FinanceTransactionTypeId.'">'
                    . '<i class="fa fa-edit"></i> Edit'
                    . '</button>';
                }
                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';
                
                return $FinanceTransactionType->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($FinanceTransactionType) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->finance_payment)[3];
                return $updateStatus == 1 ? ($FinanceTransactionType->status ? '<span class="label label-success ft_status cursor-pointer" data-id="'.$FinanceTransactionType->id.'" data-status="'.$FinanceTransactionType->status.'">Active</span>' : '<span class="label label-danger ft_status cursor-pointer" data-id="'.$FinanceTransactionType->id.'" data-status="'.$FinanceTransactionType->status.'">Inactive</span>') : ($FinanceTransactionType->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'details','account_balance','debit'])
            ->make(true);
    }

    public function UpdateFinancePaymentStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->finance_payment)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $FinanceTransaction = FinancialTransactions::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $FinanceTransaction->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $FinanceTransaction->effective_timestamp = 0;
        }
        
        $FinanceTransaction->status = $UpdateStatus;
        $FinanceTransaction->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $FinanceTransactionLog = FinancialTransactions::where('id', $ID)->first();
        $logIds = $FinanceTransactionLog->logid ? explode(',', $FinanceTransactionLog->logid) : [];
        $logIds[] = $logs->id;
        $FinanceTransactionLog->logid = implode(',', $logIds);
        $FinanceTransactionLog->save();

        $FinanceTransaction->save();
        return response()->json(['success' => true, 200]);
    }


    public function UpdateFinancePaymentModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->finance_payment)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinanceTransaction = FinancialTransactions::select('finance_transactions.*',
        'organization.organization as orgName','org_site.name as siteName',
        'finance_transaction_type.name as TransactionTypeName',
        'finance_transaction_type.discount_allowed as DiscountAllowed',
        'finance_transaction_type.amount_editable as AmountEditable',
        'finance_transaction_type.amount_ceiling as CeilingAmount',
        'ledger_types.name as ledgertypeName',
        'account_level_setup.name as AccountName')
        ->join('organization', 'organization.id', '=', 'finance_transactions.org_id')
        ->join('org_site', 'org_site.id', '=', 'finance_transactions.site_id')
        ->join('finance_transaction_type', 'finance_transaction_type.id', '=', 'finance_transactions.transaction_type_id')
        ->join('ledger_types', 'ledger_types.id', '=', 'finance_transaction_type.ledger_id')
        ->join('account_level_setup', 'account_level_setup.id', '=', 'finance_transaction_type.debit_account')
        ->where('finance_transactions.id', $id)
        ->first();

        $orgName = ucwords($FinanceTransaction->orgName);
        $siteName = ucwords($FinanceTransaction->siteName);
        $TransactionTypeName = ucwords($FinanceTransaction->TransactionTypeName);
        $PaymentOption = ucwords($FinanceTransaction->payment_option);
        $PaymentOptionDetail = ucwords($FinanceTransaction->payment_option_detail);
        $Amount = ($FinanceTransaction->amount);
        $Discount = ($FinanceTransaction->discount);
        $Remarks = ucwords($FinanceTransaction->remarks);
        $effective_timestamp = $FinanceTransaction->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $FinanceTransaction->id,
            'orgName' => $orgName,
            'orgId' => $FinanceTransaction->org_id,
            'siteName' => $siteName,
            'siteId' => $FinanceTransaction->site_id,
            'TransactionTypeName' => $TransactionTypeName,
            'TransactionTypeId' => $FinanceTransaction->transaction_type_id,
            'DiscountAllowed' => $FinanceTransaction->DiscountAllowed,
            'AmountEditable' => $FinanceTransaction->AmountEditable,
            'CeilingAmount' => $FinanceTransaction->CeilingAmount,
            'PaymentOption' => $FinanceTransaction->payment_option,
            'PaymentOptionDetail' => $FinanceTransaction->payment_option_detail,
            'Amount' => $FinanceTransaction->amount,
            'Discount' => $FinanceTransaction->discount,
            'Remarks' => $FinanceTransaction->remarks,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateFinancePayment(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->finance_payment)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $FinancialTransaction = FinancialTransactions::findOrFail($id);
        $orgID = $request->input('u_fp_org');
        if (isset($orgID)) {
            $FinancialTransaction->org_id = $orgID;
        }  
        $FinancialTransaction->site_id	 = $request->input('u_fp_site');
        $FinancialTransaction->transaction_type_id = $request->input('u_fp_transactiontype');
        $FinancialTransaction->payment_option = $request->input('u_fp_paymentoption');
        $FinancialTransaction->payment_option_detail = $request->input('u_fp_paymentoptiondetails');
        $FinancialTransaction->amount = $request->input('u_fp_amount');
        $FinancialTransaction->discount = $request->input('u_fp_discount');
        $FinancialTransaction->remarks = $request->input('u_fp_remarks');
        $effective_date = $request->input('u_fp_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $FinancialTransaction->effective_timestamp = $effective_date;
        $FinancialTransaction->last_updated = $this->currentDatetime;
        $FinancialTransaction->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $FinancialTransaction->save();
        if (empty($FinancialTransaction->id)) {
            return response()->json(['error' => 'Failed to update Financial Payment Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $FinancialTransactionLog = FinancialTransactions::where('id', $FinancialTransaction->id)->first();
        $logIds = $FinancialTransactionLog->logid ? explode(',', $FinancialTransactionLog->logid) : [];
        $logIds[] = $logs->id;
        $FinancialTransactionLog->logid = implode(',', $logIds);
        $FinancialTransactionLog->save();
        return response()->json(['success' => 'Financial Payment Details updated successfully']);
    }


    public function ItemRates()
    {
        $colName = 'item_rates';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.item_rates', compact('user','Organizations'));
    }

    public function AddItemRates(ItemRatesRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->item_rates)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = ($request->input('ir_org'));
        $Site = trim($request->input('ir_site'));
        $GenericId = trim($request->input('ir_generic'));
        $BrandId = trim($request->input('ir_brand'));
        $Batch = trim($request->input('ir_batch'));
        $PackSize = trim($request->input('ir_packsize'));
        $UnitCost = ($request->input('ir_unitcost'));
        $Cost = number_format($request->input('ir_unitcost'),2);
        $BilledAmount = ($request->input('ir_billedamount'));
        $Billed = number_format($request->input('ir_billedamount'),2);
        $Edt = $request->input('ir_edt');
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

        $RateExists = ItemRates::where('org_id', $Organization)
        ->Where('site_id', $Site)
        ->Where('generic_id', $GenericId)
        ->Where('brand_id', $BrandId)
        ->Where('batch_no', $Batch)
        ->where('status', 1)
        ->exists();
        
        if ($RateExists) {
            return response()->json(['info' => 'Item rates already exist for this combination.']);
        }

        $ItemRate = new ItemRates();
        $ItemRate->org_id = $Organization;
        $ItemRate->site_id = $Site;
        $ItemRate->generic_id = $GenericId;
        $ItemRate->brand_id = $BrandId;
        $ItemRate->batch_no = $Batch;
        $ItemRate->pack_size = $PackSize;
        $ItemRate->unit_cost = $UnitCost;
        $ItemRate->billed_amount = $BilledAmount;
        $ItemRate->status = $status;
        $ItemRate->user_id = $sessionId;
        $ItemRate->last_updated = $last_updated;
        $ItemRate->timestamp = $timestamp;
        $ItemRate->effective_timestamp = $Edt;
        $ItemRate->save();


        if (empty($ItemRate->id)) {
            return response()->json(['error' => 'Failed To add Item Rates.']);
        }

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Cost '{$Cost}' and Billed '{$Billed}' Amount has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $ItemRate->logid = $logs->id;
        $ItemRate->save();
        return response()->json(['success' => 'Item Rates created successfully']);
    }

    public function ItemRatesData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->item_rates)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ItemRates = ItemRates::select('item_rates.*',
        'organization.organization as orgName','org_site.name as siteName',
        'inventory_generic.name as GenericName',
        'inventory_brand.name as BrandName')
        ->join('organization', 'organization.id', '=', 'item_rates.org_id')
        ->join('org_site', 'org_site.id', '=', 'item_rates.site_id')
        ->join('inventory_generic', 'inventory_generic.id', '=', 'item_rates.generic_id')
        ->join('inventory_brand', 'inventory_brand.id', '=', 'item_rates.brand_id')
        ->orderBy('item_rates.id', 'desc');
        // ->get();
    
        // return DataTables::of($FinanceTransactionTypes)
        return DataTables::eloquent($ItemRates)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('item_rates.id', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('org_site.name', 'like', "%{$search}%")
                        ->orWhere('inventory_brand.name', 'like', "%{$search}%")
                        ->orWhere('item_rates.unit_cost', 'like', "%{$search}%")
                        ->orWhere('item_rates.billed_amount', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ItemRate) {
                return $ItemRate->id;  
            })
            ->editColumn('details', function ($ItemRate) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionID = $session->id;
                $GenericName = ucwords($ItemRate->GenericName);
                $BrandName = ucwords($ItemRate->BrandName);
                $orgName = ucwords($ItemRate->orgName);
                $siteName = ucwords($ItemRate->siteName);
                $effectiveDate = Carbon::createFromTimestamp($ItemRate->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ItemRate->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ItemRate->last_updated)->format('l d F Y - h:i A');
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($sessionName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;
                            
                $idStr = str_pad($ItemRate->id, 5, "0", STR_PAD_LEFT);
                $ModuleCode = 'IR';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $BrandName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($ItemRate->orgName).'<br>';
                }

                return $Code.'<hr class="mt-1 mb-2">'
                    .'<b>Generic:</b> '.$GenericName.'<br>'
                    .'<b>Brand:</b> '.$BrandName.'<br>'
                    .'<b>Batch #:</b> '.$ItemRate->batch_no.'<br>'
                    . '<b>Pack Size:</b> '.$ItemRate->pack_size.'<br>'
                    . '<hr class="mt-1 mb-2">'
                    .  $orgName
                    . '<b>Site</b>: '.$siteName.'<br>'
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('unit_cost', function ($ItemRate) {
                $totalCost = $ItemRate->unit_cost * $ItemRate->pack_size;
                return '<b>Unit Cost:</b> Rs '.number_format($ItemRate->unit_cost,2).'<br>'.
                       '<b>Total Unit Cost:</b> Rs '.number_format($totalCost,2).'<br>';
            })
            ->editColumn('billed_amount', function ($ItemRate) {
                $totalBilled = $ItemRate->billed_amount * $ItemRate->pack_size;
                return '<b>Unit Billed:</b> Rs '.number_format($ItemRate->billed_amount,2).'<br>'.
                       '<b>Total Billed:</b> Rs '.number_format($totalBilled,2).'<br>';
            })
            ->addColumn('action', function ($ItemRate) {
                $ItemRateId = $ItemRate->id;
                $logId = $ItemRate->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->item_rates)[2];
                $actionButtons = '';
                if ($edit == 1) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-ir" data-ir-id="'.$ItemRateId.'">'
                    . '<i class="fa fa-edit"></i> Edit'
                    . '</button>';
                }
                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';
                
                return $ItemRate->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ItemRate) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->item_rates)[3];
                return $updateStatus == 1 ? ($ItemRate->status ? '<span class="label label-success ir_status cursor-pointer" data-id="'.$ItemRate->id.'" data-status="'.$ItemRate->status.'">Active</span>' : '<span class="label label-danger ir_status cursor-pointer" data-id="'.$ItemRate->id.'" data-status="'.$ItemRate->status.'">Inactive</span>') : ($ItemRate->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->rawColumns(['action', 'status',
            'unit_cost','billed_amount','details'])
            ->make(true);
    }

    public function UpdateItemRatesStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->item_rates)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ItemRate = ItemRates::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ItemRate->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ItemRate->effective_timestamp = 0;
        }
        
        $ItemRate->status = $UpdateStatus;
        $ItemRate->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ItemRateLog = ItemRates::where('id', $ID)->first();
        $logIds = $ItemRateLog->logid ? explode(',', $ItemRateLog->logid) : [];
        $logIds[] = $logs->id;
        $ItemRateLog->logid = implode(',', $logIds);
        $ItemRateLog->save();

        $ItemRate->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateItemRateModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_rates)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
       
        $ItemRates = ItemRates::select('item_rates.*',
        'organization.organization as orgName','org_site.name as siteName',
        'inventory_generic.name as GenericName', 'inventory_generic.id as GenericId',
        'inventory_brand.name as BrandName')
        ->join('organization', 'organization.id', '=', 'item_rates.org_id')
        ->join('org_site', 'org_site.id', '=', 'item_rates.site_id')
        ->join('inventory_generic', 'inventory_generic.id', '=', 'item_rates.generic_id')
        ->join('inventory_brand', 'inventory_brand.id', '=', 'item_rates.brand_id')
        ->where('item_rates.id', $id)
        ->first();

        $orgName = ucwords($ItemRates->orgName);
        $siteName = ucwords($ItemRates->siteName);
        $GenericName = ucwords($ItemRates->GenericName);
        $BrandName = ucwords($ItemRates->BrandName);
        $UnitCost = ($ItemRates->unit_cost);
        $BilledAmount = ($ItemRates->billed_amount);
        $effective_timestamp = $ItemRates->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $ItemRates->id,
            'orgName' => $orgName,
            'orgId' => $ItemRates->org_id,
            'siteName' => $siteName,
            'siteId' => $ItemRates->site_id,
            'GenericName' => $GenericName,
            'GenericId' => $ItemRates->GenericId,
            'BrandName' => $BrandName,
            'batch' => $ItemRates->batch_no,
            'BrandId' => $ItemRates->brand_id,
            'packSize' => $ItemRates->pack_size,
            'BilledAmount' => $BilledAmount,
            'UnitCost' => $UnitCost,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateItemRate(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->item_rates)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ItemRate = ItemRates::findOrFail($id);
        $orgID = $request->input('u_ir_org');
        if (isset($orgID)) {
            $ItemRate->org_id = $orgID;
        }  
        $ItemRate->site_id	 = $request->input('u_ir_site');
        $ItemRate->generic_id = $request->input('u_ir_generic');
        $ItemRate->brand_id = $request->input('u_ir_brand');
        $ItemRate->batch_no = $request->input('u_ir_batch');
        $ItemRate->pack_size = $request->input('u_ir_packsize');
        $ItemRate->unit_cost = $request->input('u_ir_unitcost');
        $ItemRate->billed_amount = $request->input('u_ir_billedamount');
        $effective_date = $request->input('u_ir_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ItemRate->effective_timestamp = $effective_date;
        $ItemRate->last_updated = $this->currentDatetime;
        $ItemRate->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ItemRate->save();
        if (empty($ItemRate->id)) {
            return response()->json(['error' => 'Failed to update Item Rate Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ItemRateLog = ItemRates::where('id', $ItemRate->id)->first();
        $logIds = $ItemRateLog->logid ? explode(',', $ItemRateLog->logid) : [];
        $logIds[] = $logs->id;
        $ItemRateLog->logid = implode(',', $logIds);
        $ItemRateLog->save();
        return response()->json(['success' => 'Item Rate Details updated successfully']);
    }

    public function CheckFinanceTransactionType(Request $request)
    {
        $transactionTypeID = $request->input('transactionTypeID');
        $TransactionTypes = FinancialTransactionTypes::where('status', 1)->where('id', $transactionTypeID);
        $TransactionTypes = $TransactionTypes->get(['discount_allowed', 'amount_editable', 'amount_ceiling']);
        return response()->json($TransactionTypes);
    }

    public function ServiceRates()
    {
        $colName = 'service_rates';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        // $sessionID = $session->id;
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.service-rate', compact('user','Organizations'));
    }

    public function FetchServiceRates(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->service_rates)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $siteID = $request->input('srate_site');

        $Services = Service::select(
            'services.id', 
            'services.user_id', 
            'services.status', 
            'services.name as serviceName', 
            'activated_service.status as activated_status', 
            'activated_service.id as activated_service_id', 
            'activated_service.site_id',
            DB::raw('GROUP_CONCAT(DISTINCT billing_cc.name ORDER BY billing_cc.name ASC SEPARATOR ", ") as BillingCCNames'),
            DB::raw('GROUP_CONCAT(DISTINCT performing_cc.name ORDER BY performing_cc.name ASC SEPARATOR ", ") as PerformingCCNames'),
            'service_type.name as ServiceTypeName', 
            'service_group.name as ServiceGroupName',
            'service_mode.name as ServiceModeName',
            'service_mode.id as ServiceModeId',
            'service_unit.name as ServiceUnit' 
        )
        ->join('activated_service', 'activated_service.service_id', '=', 'services.id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->join('service_unit', 'service_unit.id', '=', 'services.unit_id')
        ->join('costcenter as billing_cc', function($join) {
            $join->on(DB::raw('FIND_IN_SET(billing_cc.id, activated_service.ordering_cc_ids)'), '>', DB::raw('0'));
        })
        ->join('costcenter as performing_cc', function($join) {
            $join->on(DB::raw('FIND_IN_SET(performing_cc.id, activated_service.performing_cc_ids)'), '>', DB::raw('0'));
        })
        ->leftJoin('service_mode', function($join) {
            $join->on(DB::raw('FIND_IN_SET(service_mode.id, activated_service.servicemode_ids)'), '>', DB::raw('0')); // Check for each servicemode_id
        })
        ->where('services.status', 1)
        ->where('activated_service.status', 1)
        ->where('activated_service.site_id', $siteID)
        ->groupBy(
            'services.id', 
            'services.name', 
            'services.status', 
            'services.user_id', 
            'activated_service.status', 
            'activated_service.site_id',
            'activated_service.id',
            'service_mode.id',
            'service_mode.name',
            'service_type.name', 
            'service_type.code', 
            'service_unit.name', 
            'service_group.name'
        );
        // ->get();
        // return DataTables::of($Services)
        return DataTables::eloquent($Services)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('services.name', 'like', "%{$search}%")
                            ->orWhere('service_type.name', 'like', "%{$search}%")
                            ->orWhere('service_group.name', 'like', "%{$search}%")
                            ->orWhere('service_mode.name', 'like', "%{$search}%")
                            ->orWhere('service_unit.name', 'like', "%{$search}%")
                            ->orWhere('billing_cc.name', 'like', "%{$search}%")
                            ->orWhere('performing_cc.name', 'like', "%{$search}%")
                            ->orWhere('activated_service.site_id', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Service) {
                return $Service->id;  
            })
            ->editColumn('service_details', function ($Service) {
                $createdByName = getUserNameById($Service->user_id);
                $createdInfo = "<b>Created By:</b> " . ucwords($createdByName) . "  <br>";
              
                $serviceName = $Service->serviceName;
                $serviceGroupName = $Service->ServiceGroupName;
                $serviceTypeName = $Service->ServiceTypeName;

                return $serviceTypeName.'<hr class="mt-1 mb-1">'.$serviceGroupName.'<hr class="mt-1 mb-1">'.$serviceName.'<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('billingCC', function ($Service) {
                $BillingCCName = $Service->BillingCCNames; 
                $Array = explode(',', $BillingCCName);
                
                $costCenterNames = implode('<hr class="mt-1 mb-1">', $Array);
                return $costCenterNames;
            })
            ->editColumn('performingCC', function ($Service) {
                $PerformingCCName = $Service->PerformingCCNames; 
                $Array = explode(',', $PerformingCCName);
                
                $costCenterNames = implode('<hr class="mt-1 mb-1">', $Array);
                return $costCenterNames;
            })
            ->editColumn('ServiceModes', function ($Service) {
                $ServiceModeName = $Service->ServiceModeName; 
                return $ServiceModeName;
            })
            ->editColumn('ServiceUnit', function ($Service) {
                $ServiceUnit = $Service->ServiceUnit; 
                return $ServiceUnit;
            })
            ->addColumn('action', function ($Service) {
                    $ServiceId = $Service->id;
                    $rights = $this->rights;
                    $add = explode(',', $rights->service_rates)[0];
                    $edit = explode(',', $rights->service_rates)[2];
                    $actionButtons = '';
                    $ServiceModeId = $Service->ServiceModeId;
                    $siteId = $Service->site_id;

                    $ActivatedServiceId = $Service->activated_service_id;
                    $ActivatedServiceAvailable = ActivatedServiceRate::select('id')
                    ->where('activated_service_id', $ActivatedServiceId)
                    ->where('service_mode_id', $ServiceModeId)
                    ->exists();
                    if (!$ActivatedServiceAvailable) {
                        if ($add == 1) {
                        
                            $actionButtons .= '<button type="button" class="btn btn-success add-servicerate" data-site-id="'.$siteId.'" data-servicemode-id="'.$ServiceModeId.'" data-activatedservice-id="'.$ActivatedServiceId.'">'
                            . '<i class="fa fa-edit"></i> Add Service Rate'
                            . '</button>';
                        }
                    }
                    else{
                        // $activatedServiceDetails = ActivatedServiceRate::where('activated_service_id', $ActivatedServiceId)->first();
                        $activatedServiceDetails = ActivatedServiceRate::where('activated_service_id', $ActivatedServiceId)
                        ->where('service_mode_id', $ServiceModeId)
                        ->first();
                        // if ($activatedServiceDetails) {
                        $logId = $activatedServiceDetails->logid;
                        $id = $activatedServiceDetails->id;
                        $service_mode_id = $activatedServiceDetails->service_mode_id;
                        $costPrice = number_format($activatedServiceDetails->cost_price,2);
                        $sellPrice = number_format($activatedServiceDetails->sell_price,2);

                        if ($edit == 1) {
                            $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-servicerates" data-servicerate-id="'.$id.'">'
                            . '<i class="fa fa-edit"></i> Edit'
                            . '</button>';
                        }
                        $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                        . '<i class="fa fa-eye"></i> View Logs'
                        . '</button>';
                        
                        // if ($ServiceModeId == $service_mode_id) {
                            $actionButtons .= '<br><br><span style="background-color:#4b4d4e;padding:6px;color:white;" class="mt-2"> <b>Unit Cost</b>: Rs '.$costPrice.'</span>';
                            $actionButtons .= '<br><br><span style="background-color:#7d868b;padding:6px;color:white;"> <b>Billed Amount</b>: Rs '.$sellPrice.'</span>';
                        // }
                    }
                    return $actionButtons;

            })
            ->rawColumns(['action','service_details','ServiceUnit','billingCC','performingCC','ServiceModes',
            'id'])
            ->make(true);
    }

    public function AddServiceRates(ServiceRateRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->service_rates)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $ActivatedId = ($request->input('activated_id'));
        $ModeId = trim($request->input('mode_id'));
        $UnitCost = trim($request->input('rate_unitCost'));
        $billedAmount = ($request->input('rate_billedAmount'));

        if ($UnitCost > $billedAmount) {
            return response()->json(['info' => 'Billed amount must be greater than cost amount.']);
        }
        $ActivatedServiceRateExists = ActivatedServiceRate::where('activated_service_id', $ActivatedId)
        ->where('service_mode_id', $ModeId)
        ->exists();

        if ($ActivatedServiceRateExists) {
            return response()->json(['info' => 'Service rate is already assigned for this mode.']);
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $ServiceRates = new ActivatedServiceRate();
        $ServiceRates->activated_service_id = $ActivatedId;
        $ServiceRates->service_mode_id = $ModeId;
        $ServiceRates->cost_price = $UnitCost;
        $ServiceRates->sell_price = $billedAmount;
        $ServiceRates->user_id = $sessionId;
        $ServiceRates->last_updated = $last_updated;
        $ServiceRates->timestamp = $timestamp;
        $ServiceRates->save();


        if (empty($ServiceRates->id)) {
            return response()->json(['error' => 'Failed To Add Service Rates.']);
        }

        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Service Rates Unit Cost: '{$UnitCost}' and Billed Amount: '{$billedAmount}' added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $ServiceRates->logid = $logs->id;
        $ServiceRates->save();
        return response()->json(['success' => 'Service Rates added successfully']);
    }

    public function UpdateServiceRatesModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_rates)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        // $ServiceRates = ActivatedServiceRate::find($id);
        $ServiceRates = ActivatedServiceRate::select('activated_service_rate.*', 'activated_service.site_id')
        ->join('activated_service', 'activated_service_rate.activated_service_id', '=', 'activated_service.id')
        ->where('activated_service_rate.id', $id)
        ->first();


        $unitCost = ($ServiceRates->cost_price);
        $billedAmount = ($ServiceRates->sell_price);

        $data = [
            'id' => $ServiceRates->id,
            'siteId' => $ServiceRates->site_id,
            'unitCost' => $unitCost,
            'billedAmount' => $billedAmount,
        ];

        return response()->json($data);
    }

    public function UpdateServiceRates(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->service_rates)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ServiceRates = ActivatedServiceRate::findOrFail($id);
        $unitCost = $request->input('u_rate_unitCost');
        $billedAmount = $request->input('u_rate_billedAmount');
        if ($unitCost > $billedAmount) {
            return response()->json(['info' => 'Billed amount must be greater than cost amount.']);
        }
        $ServiceRates->cost_price	 = $unitCost;
        $ServiceRates->sell_price = $billedAmount;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ServiceRates->save();
        if (empty($ServiceRates->id)) {
            return response()->json(['error' => 'Failed to update Service Rates. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'finance',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ServiceRatesLog = ActivatedServiceRate::where('id', $ServiceRates->id)->first();
        $logIds = $ServiceRatesLog->logid ? explode(',', $ServiceRatesLog->logid) : [];
        $logIds[] = $logs->id;
        $ServiceRatesLog->logid = implode(',', $logIds);
        $ServiceRatesLog->save();
        return response()->json(['success' => 'Service Rates updated successfully']);
    }
}