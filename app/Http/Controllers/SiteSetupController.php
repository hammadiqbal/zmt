<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SiteRequest;
use Carbon\Carbon;
use PHPUnit\Framework\Constraint\IsEmpty;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use App\Models\Logs;
use App\Models\Organization;
use App\Models\Site;
use App\Models\Province;
use App\Models\District;
use App\Models\Division;
use App\Mail\SiteRegistration;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SiteSetupController extends Controller
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
    public function SiteSetup()
    {
        $colName = 'site_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        $provinces = Province::where('status', 1)->get();
        $ProvinceData = [];
        foreach ($provinces as $province)
        {
            $province_id = $province->id;
            $province_name = $province->name;
            $ProvinceData[] = [
                'province_id' => $province_id,
                'province_name' => ucfirst($province_name),
            ];

        }
        // $Organizations = Organization::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        return view('dashboard.site-setup', compact('user','ProvinceData','Organizations'));

    }

    public function AddSite(SiteRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->site_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $siteName = trim($request->input('site_name'));
        $siteOrg = trim($request->input('site_org'));
        $oldCode = trim($request->input('old_siteCode'));
        $siteRemarks = trim($request->input('site_remarks'));
        $siteaddress = trim($request->input('site_address'));
        $siteprovince = trim($request->input('site_province'));
        $sitedivision = trim($request->input('site_division'));
        $sitedistrict = trim($request->input('site_district'));
        $sitepersonname = trim($request->input('site_person_name'));
        $sitepersonemail = trim($request->input('site_person_email'));
        $sitewebsite = trim($request->input('site_website'));
        $sitegps = $request->input('site_gps');
        $sitecell = trim($request->input('site_cell'));
        $sitelandline = trim($request->input('site_landline'));
        $siteEdt = $request->input('site_edt');
        $siteEdt = Carbon::createFromFormat('l d F Y - h:i A', $siteEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($siteEdt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
            $emailStatus = 'Acive';
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

        $siteExists = Site::where('name', $siteName)
        ->orWhere('email', $sitepersonemail)
        ->exists();
        if ($siteExists) {
            return response()->json(['info' => 'Site already exists.']);
        }
        else
        {
            $Site = new Site();
            $Site->name = $siteName;
            $Site->org_id = $siteOrg;
            $Site->old_sitecode = $oldCode;
            $Site->remarks = $siteRemarks;
            $Site->address = $siteaddress;
            $Site->province_id = $siteprovince;
            $Site->division_id = $sitedivision;
            $Site->district_id = $sitedistrict;
            $Site->focalperson_name = $sitepersonname;
            $Site->email = $sitepersonemail;
            $Site->website = $sitewebsite;
            $Site->gps = $sitegps;
            $Site->cell_no = $sitecell;
            $Site->landline_no = $sitelandline;
            $Site->status = $status;
            $Site->user_id = $sessionId;
            $Site->last_updated = $last_updated;
            $Site->timestamp = $timestamp;
            $Site->effective_timestamp = $siteEdt;

            $provinceName = Province::find($siteprovince)->name;
            $divisionName = Division::find($sitedivision)->name;
            $districtName = District::find($sitedistrict)->name;
            $organizationName = Organization::find($siteOrg)->organization;
            try {
                $emailTimestamp = Carbon::createFromTimestamp($timestamp);
                $emailTimestamp = $emailTimestamp->format('l d F Y - h:i A');
                $emailEdt = $request->input('site_edt');
                if($sitegps == '')
                {
                    $emailGps = 'N/A';
                }
                else{
                    $emailGps = $sitegps;
                }

                Mail::to($sitepersonemail)->send(new SiteRegistration($siteName, $organizationName, $siteRemarks,
                $siteaddress, $provinceName, $divisionName, $districtName, $sitepersonname, $sitepersonemail,
                $sitewebsite, $emailGps, $sitecell, $sitelandline, $emailStatus,
                $emailTimestamp, $emailEdt));

                $Site->save();
            }
            catch (TransportExceptionInterface $ex)
            {
                return response()->json(['info' => 'There is an issue with admin email. Please try again!.']);
            }

            if (empty($Site->id)) {
                return response()->json(['error' => 'Failed to create Site.']);
            }

            // New logging (insert)
            $newData = [
                'name' => $siteName,
                'org_id' => $siteOrg,
                'old_sitecode' => $oldCode,
                'remarks' => $siteRemarks,
                'address' => $siteaddress,
                'province_id' => $siteprovince,
                'division_id' => $sitedivision,
                'district_id' => $sitedistrict,
                'focalperson_name' => $sitepersonname,
                'email' => $sitepersonemail,
                'website' => $sitewebsite,
                'gps' => $sitegps,
                'cell_no' => $sitecell,
                'landline_no' => $sitelandline,
                'status' => $status,
                'effective_timestamp' => $siteEdt,
            ];
            $logId = createLog(
                'site',
                'insert',
                [
                    'message' => "'{$siteName}' has been added",
                    'created_by' => $sessionName
                ],
                $Site->id,
                null,
                $newData,
                $sessionId
            );

            $Site->logid = $logId;
            $Site->save();
            return response()->json(['success' => 'Site created successfully']);
        }

    }

    public function GetSiteData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->site_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Sites = Site::select('org_site.*',
        'district.name as district_name',
        'division.name as division_name',
        'province.name as province_name',
        'organization.organization as org_name')
        ->join('province', 'province.id', '=', 'org_site.province_id')
        ->join('division', 'division.id', '=', 'org_site.division_id')
        ->join('district', 'district.id', '=', 'org_site.district_id')
        ->leftJoin('organization', 'organization.id', '=', 'org_site.org_id');
        // ->get();
        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $Sites->where('org_site.org_id', '=', $sessionOrg);
        }
        $Sites = $Sites->orderByDesc('id');
        
        // ->get()
        // return DataTables::of($Sites)
        return DataTables::eloquent($Sites)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('org_site.id', 'like', "%{$search}%")
                        ->orWhere('org_site.name', 'like', "%{$search}%")
                        ->orWhere('district.name', 'like', "%{$search}%")
                        ->orWhere('division.name', 'like', "%{$search}%")
                        ->orWhere('province.name', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('org_site.timestamp', 'like', "%{$search}%")
                        ->orWhere('org_site.effective_timestamp', 'like', "%{$search}%")
                        ->orWhere('org_site.last_updated', 'like', "%{$search}%")
                        ->orWhere('org_site.status', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Site) {
                return $Site->id;  // Raw ID value
            })
            ->editColumn('code', function ($Site) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName = ucwords($Site->org_name).'<hr class="mt-1 mb-2">';
                }

                $SiteName = $Site->name;
                $idStr = str_pad($Site->id, 4, "0", STR_PAD_LEFT);
                $effectiveDate = Carbon::createFromTimestamp($Site->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Site->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Site->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($Site->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $ModuleCode = 'STE';
                $firstLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $SiteName))));
                $Code = $ModuleCode.'-'.$firstLetters.'-'.$idStr;

                return $Code
                    . '<hr class="mt-1 mb-2">'
                    .''.$orgName.''
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->addColumn('action', function ($Site) {
                    $SiteId = $Site->id;
                    $logId = $Site->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->site_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-site" data-site-id="'.$SiteId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    $actionButtons .= '<button type="button" class="btn btn-outline-secondary mt-2 site-detail" data-site-id="'.$SiteId.'">'
                    . '<i class="fa fa-plus-circle"></i> View More Details'
                    . '</button>';
                    return $Site->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
           
            })
            ->editColumn('status', function ($Site) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->site_setup)[3];
                return $updateStatus == 1 ? ($Site->status ? '<span class="label label-success site_status cursor-pointer" data-id="'.$Site->id.'" data-status="'.$Site->status.'">Active</span>' : '<span class="label label-danger site_status cursor-pointer" data-id="'.$Site->id.'" data-status="'.$Site->status.'">Inactive</span>') : ($Site->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'code'])
            ->make(true);
    }

    public function SiteDetailModal($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->site_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Site = Site::select('org_site.*',
        'district.name as district_name',
        'division.name as division_name',
        'province.name as province_name',
        'organization.organization as org_name',
        'organization.logo as orglogo')
        ->join('province', 'province.id', '=', 'org_site.province_id')
        ->join('division', 'division.id', '=', 'org_site.division_id')
        ->join('district', 'district.id', '=', 'org_site.district_id')
        ->join('organization', 'organization.id', '=', 'org_site.org_id')
        ->find($id);

        $name = $Site->name;
        $orgName = $Site->org_name;
        $oldCode = $Site->old_sitecode;
        $remarks = $Site->remarks;
        $address = $Site->address;
        $province_name = $Site->province_name;
        $division_name = $Site->division_name;
        $district_name = $Site->district_name;
        $person_name = $Site->focalperson_name;
        $email = $Site->email;
        $website = $Site->website;
        $cell_no = $Site->cell_no;
        $landline_no = $Site->landline_no;
        if($landline_no == '')
        {
            $landline_no = 'N/A';
        }
        $logo = $Site->orglogo;
        $logo = $Site->org_id.'_'.$logo;

        $logoPath = 'assets/org/' . $logo;
        $logo = asset($logoPath);

        if($oldCode == '')
        {
            $oldCode = 'N/A';
        }

        $data = [
            'id' => $id,
            'name' => $name,
            'orgName' => $orgName,
            'oldCode' => $oldCode,
            'remarks' => $remarks,
            'address' => $address,
            'province_name' => $province_name,
            'division_name' => $division_name,
            'district_name' => $district_name,
            'person_name' => $person_name,
            'email' => $email,
            'website' => $website,
            'cell_no' => $cell_no,
            'landline_no' => $landline_no,
            'logo' => $logo,

        ];
        return response()->json($data);
    }

    public function UpdateSiteStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->site_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $SiteID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Site = Site::find($SiteID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Site->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Site->effective_timestamp = 0;
        }
        // Find the role by ID
        $Site->status = $UpdateStatus;
        $Site->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // New logging (status change) - only status fields
        $oldData = [
            'status' => (int)$Status,
        ];
        $newData = [
            'status' => $UpdateStatus,
        ];
        $logId = createLog(
            'site',
            'status_change',
            [
                'message' => "Status updated to '{$statusLog}'",
                'updated_by' => $sessionName
            ],
            $SiteID,
            $oldData,
            $newData,
            $sessionId
        );
        $SiteLog = Site::where('id', $SiteID)->first();
        $logIds = $SiteLog->logid ? explode(',', $SiteLog->logid) : [];
        $logIds[] = $logId;
        $SiteLog->logid = implode(',', $logIds);
        $SiteLog->save();

        $Site->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateSiteModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->site_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Site = Site::select('org_site.*',
        'district.name as district_name',
        'division.name as division_name',
        'province.name as province_name',
        'organization.organization as org_name',
        'organization.logo as orglogo')
        ->join('province', 'province.id', '=', 'org_site.province_id')
        ->join('division', 'division.id', '=', 'org_site.division_id')
        ->join('district', 'district.id', '=', 'org_site.district_id')
        ->join('organization', 'organization.id', '=', 'org_site.org_id')
        ->find($id);

        $siteName = $Site->name;
        $orgName = $Site->org_name;
        $orgID = $Site->org_id;
        $remarks = $Site->remarks;
        $address = $Site->address;
        $province_name = $Site->province_name;
        $division_name = $Site->division_name;
        $district_name = $Site->district_name;
        $province_id = $Site->province_id;
        $division_id = $Site->division_id;
        $district_id = $Site->district_id;
        $person_name = $Site->focalperson_name;
        $email = $Site->email;
        $website = $Site->website;
        $cell_no = $Site->cell_no;
        $landline_no = $Site->landline_no;
        $gps = $Site->gps;
        $effective_timestamp = $Site->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'siteName' => $siteName,
            'orgName' => $orgName,
            'orgID' => $orgID,
            'remarks' => $remarks,
            'address' => $address,
            'province_name' => $province_name,
            'division_name' => $division_name,
            'district_name' => $district_name,
            'province_id' => $province_id,
            'division_id' => $division_id,
            'district_id' => $district_id,
            'person_name' => $person_name,
            'email' => $email,
            'website' => $website,
            'cell_no' => $cell_no,
            'landline_no' => $landline_no,
            'gps' => $gps,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdateSiteData(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->site_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Site = Site::findOrFail($id);
        // Capture old data
        $oldData = [
            'name' => $Site->name,
            'org_id' => $Site->org_id,
            'old_sitecode' => $Site->old_sitecode,
            'remarks' => $Site->remarks,
            'address' => $Site->address,
            'province_id' => $Site->province_id,
            'division_id' => $Site->division_id,
            'district_id' => $Site->district_id,
            'focalperson_name' => $Site->focalperson_name,
            'email' => $Site->email,
            'website' => $Site->website,
            'gps' => $Site->gps,
            'cell_no' => $Site->cell_no,
            'landline_no' => $Site->landline_no,
            'status' => $Site->status,
            'effective_timestamp' => $Site->effective_timestamp,
        ];
        $Site->name = trim($request->input('u_site_name'));
        $Site->org_id = $request->input('u_site_org');
        $Site->old_sitecode = $request->input('u_oldcode');
        $Site->remarks = trim($request->input('u_site_remarks'));

        $Site->address = trim($request->input('u_site_address'));
        $Site->province_id = $request->input('u_site_province');
        $Site->division_id = $request->input('u_site_division');

        $Site->district_id = $request->input('u_site_district');
        $Site->focalperson_name = trim($request->input('u_site_person_name'));
        $Site->email = $request->input('u_site_person_email');

        $Site->website = $request->input('u_site_website');
        $Site->gps = $request->input('u_site_gps');
        $Site->cell_no = $request->input('u_site_cell');

        $Site->landline_no = $request->input('u_site_landline');

        $effective_date = $request->input('u_site_edt');

        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $Site->effective_timestamp = $effective_date;
        $Site->last_updated = $this->currentDatetime;
        $Site->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Site->save();
        if (empty($Site->id)) {
            return response()->json(['error' => 'Failed to update Site. Please try again']);
        }
        // New logging (update)
        $newData = [
            'name' => $Site->name,
            'org_id' => $Site->org_id,
            'old_sitecode' => $Site->old_sitecode,
            'remarks' => $Site->remarks,
            'address' => $Site->address,
            'province_id' => $Site->province_id,
            'division_id' => $Site->division_id,
            'district_id' => $Site->district_id,
            'focalperson_name' => $Site->focalperson_name,
            'email' => $Site->email,
            'website' => $Site->website,
            'gps' => $Site->gps,
            'cell_no' => $Site->cell_no,
            'landline_no' => $Site->landline_no,
            'status' => $Site->status,
            'effective_timestamp' => $Site->effective_timestamp,
        ];
        $logId = createLog(
            'site',
            'update',
            [
                'message' => "Data has been updated",
                'updated_by' => $sessionName
            ],
            $Site->id,
            $oldData,
            $newData,
            $sessionId
        );
        $SiteLog = Site::where('id', $id)->first();
        $logIds = $SiteLog->logid ? explode(',', $SiteLog->logid) : [];
        $logIds[] = $logId;
        $SiteLog->logid = implode(',', $logIds);
        $SiteLog->save();

        return response()->json(['success' => 'Site updated successfully']);
    }

    public function GetSelectedSite(Request $request)
    {
        $siteId = $request->input('siteId');
        $organizationId = $request->input('organizationId');
        if (isset($siteId)) {
            $Site = Site::whereNotIn('id', [$siteId])
                     ->where('org_id', $organizationId)
                     ->where('status', 1);
        }
        else {
            $Site = Site::where('org_id', $organizationId)
                     ->where('status', 1);
        }

        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Site->whereIn('id', $sessionSiteIds);
            }
        }
        $Site = $Site->get();

        return response()->json($Site);
    }
    
    public function GetSelectedSites(Request $request)
    {
        $orgId = $request->input('orgId');
        $Sites = Site::where('org_id', $orgId)
                    ->where('status', 1);
         
        if($this->sessionUser->is_employee == 1 && $this->sessionUser->site_enabled == 0) {
            $sessionSiteIds = $this->assignedSites;
            if(!empty($sessionSiteIds)) {
                $Sites->whereIn('id', $sessionSiteIds);
            }
        }
        
        $Sites = $Sites->get();

        return response()->json($Sites);
    }
}
