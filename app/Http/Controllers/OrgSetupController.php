<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\ReferralSiteRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use App\Models\Logs;
use App\Models\Organization;
use App\Models\Province;
use App\Models\District;
use App\Models\Division;
use App\Models\ReferralSite;
use App\Mail\OrganizationRegistration;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Illuminate\Support\Facades\Auth;

class OrgSetupController extends Controller
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
            // if (Auth::check() && Auth::user()->role_id == 1) {
            if (Auth::check()) {
                return $next($request);
            } else {
                return redirect('/');
            }
        });
    }

    public function viewOrganization()
    {
        $colName = 'organization_setup';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        // $roles = OrganizationRole::all();
        // return view('dashboard.organization', compact('roles'));
        $currentDate = $this->currentDatetime;
        $provinces = Province::where('effective_timestamp', '<=', $currentDate)
        ->where('status', 1)->get();
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
        return view('dashboard.organization', compact('ProvinceData','user'));

    }

    public function AddOrganization(OrganizationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->organization_setup)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        // $data = $request->all();
        $orgName = trim($request->input('org_name'));
        $orgCode = trim($request->input('org_code'));
        $orgRemarks = trim($request->input('org_remarks'));
        $orgaddress = trim($request->input('org_address'));
        $orgprovince = trim($request->input('org_province'));
        $orgdivision = trim($request->input('org_division'));
        $orgdistrict = trim($request->input('org_district'));
        $orgpersonname = trim($request->input('org_person_name'));
        $orgpersonemail = trim($request->input('org_person_email'));
        $orgwebsite = trim($request->input('org_website'));
        $orggps = $request->input('org_gps');
        $orgcell = trim($request->input('org_cell'));
        $orglandline = trim($request->input('org_landline'));
        $orglogo = $request->file('org_logo');
        $orgbanner = $request->file('org_banner');
        $orgEdt = $request->input('org_edt');
        $orgEdt = Carbon::createFromFormat('l d F Y - h:i A', $orgEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($orgEdt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
            $emailStatus = 'Acive';
        } else {
            $status = 0; //Inactive
            $emailStatus = 'Inactive';

        }

        $logoFileName = $orglogo->getClientOriginalName();
        $bannerFileName = $orgbanner->getClientOriginalName();

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $organizationExists = Organization::where('organization', $orgName)
        ->Where('email', $orgpersonemail)
        ->Where('code', $orgCode)
        ->exists();
        if ($organizationExists) {
            return response()->json(['info' => 'Organization already exists.']);
        }
        else
        {
            $Organization = new Organization();
            $Organization->code = $orgCode;
            $Organization->organization = $orgName;
            $Organization->remarks = $orgRemarks;
            $Organization->headoffice_address = $orgaddress;
            $Organization->province_id = $orgprovince;
            $Organization->division_id = $orgdivision;
            $Organization->district_id = $orgdistrict;
            $Organization->focalperson_name = $orgpersonname;
            $Organization->email = $orgpersonemail;
            $Organization->website = $orgwebsite;
            $Organization->gps = $orggps;
            $Organization->cell_no = $orgcell;
            $Organization->landline_no = $orglandline;
            $Organization->logo = $logoFileName;
            $Organization->banner = $bannerFileName;
            $Organization->status = $status;
            $Organization->user_id = $sessionId;
            $Organization->last_updated = $last_updated;
            $Organization->timestamp = $timestamp;
            $Organization->effective_timestamp = $orgEdt;

            $provinceName = Province::find($orgprovince)->name;
            $divisionName = Division::find($orgdivision)->name;
            $districtName = District::find($orgdistrict)->name;
            try {
                $emailTimestamp = Carbon::createFromTimestamp($timestamp);
                $emailTimestamp = $emailTimestamp->format('l d F Y - h:i A');
                $emailEdt = $request->input('org_edt');
                if($orggps == '')
                {
                    $emailGps = 'N/A';
                }
                else{
                    $emailGps = $orggps;
                }

                Mail::to($orgpersonemail)->send(new OrganizationRegistration($orgName, $orgCode, $orgRemarks,
                $orgaddress, $provinceName, $divisionName, $districtName, $orgpersonname, $orgpersonemail,
                $orgwebsite, $emailGps, $orgcell, $orglandline, $emailStatus,
                $emailTimestamp, $emailEdt));

                $Organization->save();
                $logoFileName = $Organization->id . '_' . $logoFileName;
                $bannerFileName = $Organization->id . '_' . $bannerFileName;
                $orglogo->move(public_path('assets/org'), $logoFileName);
                $orgbanner->move(public_path('assets/org'), $bannerFileName);
            }
            catch (TransportExceptionInterface $ex)
            {
                return response()->json(['info' => 'There is an issue with email. Please try again!.']);
            }

            if (empty($Organization->id)) {
                return response()->json(['error' => 'Failed to create Organization.']);
            }

            $logs = Logs::create([
                'module' => 'organization',
                'content' => "'{$orgName}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Organization->logid = $logs->id;
            $Organization->save();
            return response()->json(['success' => 'Organization created successfully']);


        }

    }

    public function GetSelectedDistrict(Request $request)
    {
        $divisionId = $request->input('divisionId');
        $divisions = District::where('division_id', $divisionId)
        ->where('status', 1)
        ->get();

        return response()->json($divisions);
    }

    public function GetOrganizationData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->organization_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = Organization::select('organization.*',
        'district.name as district_name',
        'division.name as division_name',
        'province.name as province_name')
        ->join('province', 'province.id', '=', 'organization.province_id')
        ->join('division', 'division.id', '=', 'organization.division_id')
        ->join('district', 'district.id', '=', 'organization.district_id');
        // ->get();
        // return DataTables::of($Organization)
        return DataTables::eloquent($Organization)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('organization.organization', 'like', "%{$search}%")
                            ->orWhere('organization.remarks', 'like', "%{$search}%")
                            ->orWhere('organization.headoffice_address', 'like', "%{$search}%")
                            ->orWhere('organization.code', 'like', "%{$search}%")
                            ->orWhere('district.name', 'like', "%{$search}%")
                            ->orWhere('division.name', 'like', "%{$search}%")
                            ->orWhere('province.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Organization) {
                return $Organization->id;  // Raw ID value
            })
            ->editColumn('code', function ($Organization) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $OrganizationCode = $Organization->code;
                $effectiveDate = Carbon::createFromTimestamp($Organization->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Organization->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Organization->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($Organization->user_id);

                $createdInfo = "<b>Created By:</b> " . ucwords($createdByName) . " <br> <b>Effective Date&amp;Time:</b> "
                    . $effectiveDate . " <br><b>RecordedAt:</b> " . $timestamp ." <br><b>LastUpdated:</b>
                    " . $lastUpdated;

                return $OrganizationCode
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('name', function ($Organization) {
                $Organizationname = $Organization->organization;
                return $Organizationname;
            })
            ->editColumn('remarks', function ($Organization) {
                $Organizationremarks = $Organization->remarks;
                return $Organizationremarks;
            })
            ->editColumn('address', function ($Organization) {
                $Organizationaddress = $Organization->headoffice_address;
                return $Organizationaddress;
            })
            ->addColumn('action', function ($Organization) {
                    $OrganizationId = $Organization->id;
                    $logId = $Organization->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->organization_setup)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-organization" data-organization-id="'.$OrganizationId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    $actionButtons .= '<button type="button" class="btn btn-outline-secondary mt-2 organization-detail" data-org-id="'.$OrganizationId.'">'
                    . '<i class="fa fa-plus-circle"></i> View More Details'
                    . '</button>';
                    return $Organization->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($Organization) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->organization_setup)[3];
                return $updateStatus == 1 ? ($Organization->status ? '<span class="label label-success organization_status cursor-pointer" data-id="'.$Organization->id.'" data-status="'.$Organization->status.'">Active</span>' : '<span class="label label-danger organization_status cursor-pointer" data-id="'.$Organization->id.'" data-status="'.$Organization->status.'">Inactive</span>') : ($Organization->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status',
            'code'])
            ->make(true);
    }

    public function UpdateOrganizationStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->organization_setup)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $OrganizationID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Organization = Organization::find($OrganizationID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Organization->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Organization->effective_timestamp = 0;
        }
        // Find the role by ID
        $Organization->status = $UpdateStatus;
        $Organization->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'organization',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $OrganizationLog = Organization::where('id', $OrganizationID)->first();
        $logIds = $OrganizationLog->logid ? explode(',', $OrganizationLog->logid) : [];
        $logIds[] = $logs->id;
        $OrganizationLog->logid = implode(',', $logIds);
        $OrganizationLog->save();

        $Organization->save();
        return response()->json(['success' => true, 200]);
    }

    public function OrganizationDetailModal($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->organization_setup)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        } 
        $organization = Organization::select('organization.*',
        'district.name as district_name',
        'division.name as division_name',
        'province.name as province_name')
        ->join('province', 'province.id', '=', 'organization.province_id')
        ->join('division', 'division.id', '=', 'organization.division_id')
        ->join('district', 'district.id', '=', 'organization.district_id')
        ->find($id);

        $code = $organization->code;
        $name = $organization->organization;
        $remarks = $organization->remarks;
        $address = $organization->headoffice_address;
        $province_name = $organization->province_name;
        $division_name = $organization->division_name;
        $district_name = $organization->district_name;
        $person_name = $organization->focalperson_name;
        $email = $organization->email;
        $website = $organization->website;
        $cell_no = $organization->cell_no;
        $landline_no = $organization->landline_no;
        $logo = $organization->logo;
        $banner = $organization->banner;
        $logo = $id.'_'.$logo;
        $banner = $id.'_'.$banner;

        $logoPath = 'assets/org/' . $logo;
        $bannerPath = 'assets/org/' . $banner;
        $logo = asset($logoPath);
        $banner = asset($bannerPath);


        $data = [
            'id' => $id,
            'code' => $code,
            'name' => $name,
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
            'banner' => $banner,
        ];
        return response()->json($data);
    }

    public function UpdateOrganizationModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->organization_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = Organization::select('organization.*',
        'district.name as district_name',
        'division.name as division_name',
        'province.name as province_name',
        'province.id as province_id',
        'division.id as division_id',
        'district.id as district_id',)
        ->join('province', 'province.id', '=', 'organization.province_id')
        ->join('division', 'division.id', '=', 'organization.division_id')
        ->join('district', 'district.id', '=', 'organization.district_id')
        ->find($id);

        $org_name = $Organization->organization;
        $org_code = $Organization->code;
        $org_remarks = $Organization->remarks;
        $org_address = $Organization->headoffice_address;
        $province_name = $Organization->province_name;
        $division_name = $Organization->division_name;
        $district_name = $Organization->district_name;
        $province_id = $Organization->province_id;
        $division_id = $Organization->division_id;
        $district_id = $Organization->district_id;
        $org_personname = $Organization->focalperson_name;
        $org_email = $Organization->email;
        $org_website = $Organization->website;
        $org_gps = $Organization->gps;
        $org_cell_no = $Organization->cell_no;
        $org_landline_no = $Organization->landline_no;
        $org_logo = $id.'_'.$Organization->logo;
        $org_banner = $id.'_'.$Organization->banner;
        $effective_timestamp = $Organization->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $org_logo = 'assets/org/' . $org_logo;
        $org_banner = 'assets/org/' . $org_banner;
        $org_logo = asset($org_logo);
        $org_banner = asset($org_banner);

        $data = [
            'id' => $id,
            'org_name' => $org_name,
            'org_code' => $org_code,
            'org_remarks' => $org_remarks,
            'org_address' => $org_address,
            'province_name' => $province_name,
            'division_name' => $division_name,
            'district_name' => $district_name,
            'province_id' => $province_id,
            'division_id' => $division_id,
            'district_id' => $district_id,
            'org_personname' => $org_personname,
            'org_email' => $org_email,
            'org_website' => $org_website,
            'org_gps' => $org_gps,
            'org_cell_no' => $org_cell_no,
            'org_landline_no' => $org_landline_no,
            'org_logo' => $org_logo,
            'org_banner' => $org_banner,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateOrganization(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->organization_setup)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $Organization = Organization::findOrFail($id);
        $Organization->organization = trim($request->input('u_org_name'));
        $Organization->remarks = trim($request->input('u_org_remarks'));

        $Organization->headoffice_address = trim($request->input('u_org_address'));
        $Organization->province_id = $request->input('u_org_province');
        $Organization->division_id = $request->input('u_org_division');

        $Organization->district_id = $request->input('u_org_district');
        $Organization->focalperson_name = trim($request->input('u_org_person_name'));
        $Organization->email = $request->input('u_org_person_email');

        $Organization->website = $request->input('u_org_website');
        $Organization->gps = $request->input('u_org_gps');
        $Organization->cell_no = $request->input('u_org_cell');

        $Organization->landline_no = $request->input('u_org_landline');

        $orglogo = $request->file('u_org_logo');
        $orgbanner = $request->file('u_org_banner');

        if (isset($orglogo)) {
            $logoFileName = $orglogo->getClientOriginalName();
            $Organization->logo = $logoFileName;

            $logoFileName = $id . '_' . $logoFileName;
            // $orglogo->storeAs('public/assets/organization', $logoFileName);
            $orglogo->move(public_path('assets/org'), $logoFileName);
        }

        if (isset($orgbanner)) {
            $Organization->banner = $request->file('u_org_banner');
            $bannerFileName = $orgbanner->getClientOriginalName();
            $Organization->banner = $bannerFileName;

            $bannerFileName = $id . '_' . $bannerFileName;
            // $orgbanner->storeAs('public/assets/organization', $bannerFileName);
            $orgbanner->move(public_path('assets/org'), $bannerFileName);
        }

        $effective_date = $request->input('u_org_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $Organization->effective_timestamp = $effective_date;
        $Organization->last_updated = $this->currentDatetime;
        $Organization->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Organization->save();
        if (empty($Organization->id)) {
            return response()->json(['error' => 'Failed to update Organization. Please try again']);
        }
        // $data = "Data has been updated by '{$sessionName}'";
        $logs = Logs::create([
            'module' => 'organization',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $OrganizationLog = Organization::where('id', $id)->first();
        $logIds = $OrganizationLog->logid ? explode(',', $OrganizationLog->logid) : [];
        $logIds[] = $logs->id;
        $OrganizationLog->logid = implode(',', $logIds);
        $OrganizationLog->save();

        return response()->json(['success' => 'Organization updated successfully']);
    }

    public function GetSelectedOrganization(Request $request)
    {
        $orgID = $request->input('orgID');
        $query = Organization::select('id', 'organization');
        if ($orgID !== null) {
            $query->whereNotIn('id', [$orgID]);
        }
        $Organization = $query->where('status', 1)->get();
        return response()->json($Organization);
    }

    public function GetSelectedTransactionTypeOrganization(Request $request)
    {
        $transactiontypeID = $request->input('transactiontypeID');
        $Organization = Organization::select('organization.id','organization.organization')
        ->join('inventory_transaction_type', 'inventory_transaction_type.org_id', '=', 'organization.id')
        ->where('organization.status', 1)
        ->where('inventory_transaction_type.id', $transactiontypeID)
        ->get();
        return response()->json($Organization);
    }

    public function ShowReferralSite()
    {
        $colName = 'referral_site';
        if (PermissionDenied($colName)) {
            abort(403); 
        }
        $user = auth()->user();
        // $roles = OrganizationRole::all();
        // return view('dashboard.organization', compact('roles'));
        $ProvinceData = Province::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        
        return view('dashboard.referral_site', compact('ProvinceData','user','Organizations'));
    }

    public function AddReferralSite(ReferralSiteRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->referral_site)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Description = $request->input('rf_desc');
        $Organization = $request->input('rf_org');
        $Province = $request->input('rf_province');
        $Division = $request->input('rf_division');
        $District = $request->input('rf_district');
        $Cell = $request->input('rf_cell');
        $landline = $request->input('rf_landline');
        $Remarks = $request->input('rf_remarks');
        $Edt = $request->input('rf_edt');
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

        $CGExists = ReferralSite::where('org_id', $Organization)
        ->where('name', $Description)
        ->where('province_id', $Province)
        ->where('division_id', $Division)
        ->where('district_id', $District)
        ->exists();

        if ($CGExists) {
            return response()->json(['info' => 'Referral Site already exists.']);
        }
        else
        {
            $ReferralSite = new ReferralSite();
            $ReferralSite->org_id = $Organization;
            $ReferralSite->name = $Description;
            $ReferralSite->province_id = $Province;
            $ReferralSite->division_id = $Division;
            $ReferralSite->district_id = $District;
            $ReferralSite->cell = $Cell;
            $ReferralSite->landline = $landline;
            $ReferralSite->remarks = $Remarks;
            $ReferralSite->status = $status;
            $ReferralSite->user_id = $sessionId;
            $ReferralSite->last_updated = $last_updated;
            $ReferralSite->timestamp = $timestamp;
            $ReferralSite->effective_timestamp = $Edt;

            $ReferralSite->save();

            if (empty($ReferralSite->id)) {
                return response()->json(['error' => 'Failed to create Referral Site.']);
            }

            $logs = Logs::create([
                'module' => 'site',
                'content' => "'{$Description}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ReferralSite->logid = $logs->id;
            $ReferralSite->save();
            return response()->json(['success' => 'Referral Site Added successfully']);
        }
    }

    public function ShowReferralSiteDate(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->referral_site)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ReferralSites = ReferralSite::select('referral_site.*',
        'organization.organization as orgName','province.name as provinceName',
        'division.name as divisionName','district.name as districtName')
        ->join('organization', 'organization.id', '=', 'referral_site.org_id')
        ->join('province', 'province.id', '=', 'referral_site.province_id')
        ->join('division', 'division.id', '=', 'referral_site.division_id')
        ->join('district', 'district.id', '=', 'referral_site.district_id')
        ->orderBy('referral_site.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ReferralSites->where('referral_site.org_id', '=', $sessionOrg);
        }
        $ReferralSites = $ReferralSites;
        // ->get()
        // return DataTables::of($Vendors)
        return DataTables::eloquent($ReferralSites)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('referral_site.name', 'like', "%{$search}%")
                        ->orWhere('referral_site.cell', 'like', "%{$search}%")
                        ->orWhere('referral_site.landline', 'like', "%{$search}%")
                        ->orWhere('referral_site.remarks', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('province.name', 'like', "%{$search}%")
                        ->orWhere('division.name', 'like', "%{$search}%")
                        ->orWhere('district.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ReferralSite) {
                return $ReferralSite->id; 
            })
            ->editColumn('id', function ($ReferralSite) {
                $session = auth()->user();
                $effectiveDate = Carbon::createFromTimestamp($ReferralSite->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ReferralSite->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ReferralSite->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ReferralSite->user_id);
                $createdInfo = "
                        <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $Name = $ReferralSite->name;
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<b>Organization:</b> '.ucwords($ReferralSite->orgName).'<hr class="mt-1 mb-1">';
                }
                return ucwords($Name)
                    . '<hr class="mt-1 mb-1">'
                    . $orgName
                    .'<b>Remarks:</b> '.($ReferralSite->remarks ? ucwords($ReferralSite->remarks) : 'N/A').'<hr class="mt-1 mb-1">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('address', function ($ReferralSite) {
                $Province = $ReferralSite->provinceName;
                $Division = $ReferralSite->divisionName;
                $District = $ReferralSite->districtName;
                return '<b>Province:</b> '.ucwords($Province).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Division:</b> '.ucwords($Division).'<br>'
                    . '<hr class="mt-1 mb-2">'
                    .'<b>District:</b> '.ucwords($District).'<br>';
            })
            ->editColumn('contact', function ($ReferralSite) {
                return '<b>Cell #:</b> '.($ReferralSite->cell ? ucwords($ReferralSite->cell) : 'N/A').'<br>'
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Landline #:</b> '.($ReferralSite->landline ? ucwords($ReferralSite->landline) : 'N/A').'<br>';
            })
            ->addColumn('action', function ($ReferralSite) {
                    $ReferralSiteId = $ReferralSite->id;
                    $logId = $ReferralSite->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->referral_site)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-rs" data-cg-id="'.$ReferralSiteId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .='<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    return $ReferralSite->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($ReferralSite) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->referral_site)[3];
                return $updateStatus == 1 ? ($ReferralSite->status ? '<span class="label label-success rs_status cursor-pointer" data-id="'.$ReferralSite->id.'" data-status="'.$ReferralSite->status.'">Active</span>' : '<span class="label label-danger rs_status cursor-pointer" data-id="'.$ReferralSite->id.'" data-status="'.$ReferralSite->status.'">Inactive</span>') : ($ReferralSite->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','address','contact',
            'id'])
            ->make(true);
    }

    public function UpdateReferralSiteStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->referral_site)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ReferralSiteID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ReferralSite = ReferralSite::find($ReferralSiteID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ReferralSite->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ReferralSite->effective_timestamp = 0;
        }
        $ReferralSite->status = $UpdateStatus;
        $ReferralSite->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'referral_site',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ReferralSiteLog = ReferralSite::where('id', $ReferralSiteID)->first();
        $logIds = $ReferralSiteLog->logid ? explode(',', $ReferralSiteLog->logid) : [];
        $logIds[] = $logs->id;
        $ReferralSiteLog->logid = implode(',', $logIds);
        $ReferralSiteLog->save();

        $ReferralSite->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateReferralSiteModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->referral_site)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ReferralSite = ReferralSite::select('referral_site.*',
        'organization.organization as org_name',
        'province.name as province_name',
        'division.name as division_name',
        'district.name as district_name')
        ->join('organization', 'organization.id', '=', 'referral_site.org_id')
        ->join('province', 'province.id', '=', 'referral_site.province_id')
        ->join('division', 'division.id', '=', 'referral_site.division_id')
        ->join('district', 'district.id', '=', 'referral_site.district_id')
        ->find($id);

        $data = [
            'id' => $ReferralSite->id,
            'org_id' => $ReferralSite->org_id,
            'org_name' => $ReferralSite->org_name,
            'name' => $ReferralSite->name,
            'province_id' => $ReferralSite->province_id,
            'province_name' => $ReferralSite->province_name,
            'division_id' => $ReferralSite->division_id,
            'division_name' => $ReferralSite->division_name,
            'district_id' => $ReferralSite->district_id,
            'district_name' => $ReferralSite->district_name,
            'cell' => $ReferralSite->cell,
            'landline' => $ReferralSite->landline,
            'remarks' => $ReferralSite->remarks,
            'effective_timestamp' => Carbon::createFromTimestamp($ReferralSite->effective_timestamp)->format('l d F Y - h:i A')
        ];

        return response()->json($data);
    }

    public function UpdateReferralSite(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->referral_site)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ReferralSite = ReferralSite::findOrFail($id);
        
        $ReferralSite->org_id = $request->input('u_rf_org');
        $ReferralSite->name = trim($request->input('u_rf_desc'));
        $ReferralSite->province_id = $request->input('u_rf_province');
        $ReferralSite->division_id = $request->input('u_rf_division');
        $ReferralSite->district_id = $request->input('u_rf_district');
        $ReferralSite->cell = $request->input('u_rf_cell');
        $ReferralSite->landline = $request->input('u_rf_landline');
        $ReferralSite->remarks = trim($request->input('u_rf_remarks'));

        $effective_date = $request->input('u_rf_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive
        }

        $ReferralSite->effective_timestamp = $effective_date;
        $ReferralSite->last_updated = $this->currentDatetime;
        $ReferralSite->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;

        $ReferralSite->save();

        if (empty($ReferralSite->id)) {
            return response()->json(['error' => 'Failed to update Referral Site. Please try again']);
        }

        $logs = Logs::create([
            'module' => 'referral_site',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);

        $ReferralSiteLog = ReferralSite::where('id', $id)->first();
        $logIds = $ReferralSiteLog->logid ? explode(',', $ReferralSiteLog->logid) : [];
        $logIds[] = $logs->id;
        $ReferralSiteLog->logid = implode(',', $logIds);
        $ReferralSiteLog->save();

        return response()->json(['success' => 'Referral Site updated successfully']);
    }

    
    public function GetSelectedReferralSites(Request $request)
    {
        $orgId = $request->input('orgId');
        $ReferralSites = ReferralSite::where('org_id', $orgId)
                    ->where('status', 1)
                      ->orderBy('id', 'ASC')
                    ->get();

        return response()->json($ReferralSites);
    }
}
