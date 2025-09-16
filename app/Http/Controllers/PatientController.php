<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PatientRegistrationRequest;
use App\Http\Requests\PatientArrivalDepartureRequest;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Models\EmployeeGender;
use App\Models\Organization;
use App\Models\Province;
use App\Models\ServiceBooking;
use App\Models\Employee;
use App\Models\Division;
use App\Models\District;
use App\Models\Site;
use App\Models\PatientRegistration;
use App\Models\PatientArrivalDeparture;
use App\Models\ServiceLocationScheduling;
use App\Models\FinancialTransactions;
use App\Models\ActivatedServiceRate;
use App\Models\ServiceMode;
use App\Models\RequisitionForEPI;
use App\Models\Service;

class PatientController extends Controller
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
    
    // Function to parse custom age format for backend
    private function parseCustomAgeBackend($ageInput)
    {
        $ageInput = trim($ageInput);
        
        // Handle 0.1 to 0.12 format (months)
        if (preg_match('/^0\.(1[0-2]|[1-9])$/', $ageInput)) {
            $decimalPart = substr($ageInput, 2);
            $months = (int)$decimalPart;
            return ['years' => 0, 'months' => $months];
        }
        
        // Handle 1.1 to 1.12, 2.1 to 2.12, etc. format (years + months)
        if (preg_match('/^([1-9]\d*)\.(1[0-2]|[1-9])$/', $ageInput, $matches)) {
            $years = (int)$matches[1];
            $months = (int)$matches[2];
            return ['years' => $years, 'months' => $months];
        }
        
        // Handle whole numbers (1, 2, 3, etc.)
        if (preg_match('/^[1-9]\d*$/', $ageInput)) {
            return ['years' => (int)$ageInput, 'months' => 0];
        }
        
        return null; // Invalid format
    }

    public function PatientRegistration()
    {
        $colName = 'patient_registration';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $Genders = EmployeeGender::where('status', 1)->get();
        $Organizations = Organization::where('status', 1)->get();
        $Provinces = Province::where('status', 1)->get();

        return view('dashboard.patient-registration', compact('user','Genders','Organizations','Provinces'));
    }

    public function PatientMRNo(Request $request)
    {
        $siteId = $request->input('siteId');
        $condition = $request->input('condition');
        // dd($siteId, $condition);

        $baseQuery = PatientRegistration::select('patient.mr_code')
        ->where('patient.status', 1)
        ->where('patient.site_id', $siteId);

        if ($condition == 'materialConsumption') {
            $PatientMRCode = $baseQuery
                ->join('patient_inout', 'patient_inout.mr_code', '=', 'patient.mr_code')
                ->where('patient_inout.status', 1)
                ->distinct('patient.mr_code');
        }
        elseif ($condition == 'serviceBooking') {
            $PatientMRCode = $baseQuery
                ->leftJoin('service_booking', function($join) {
                    $join->on('patient.mr_code', '=', DB::raw('service_booking.mr_code'))
                        ->where('service_booking.status', 1);
                })
                ->whereNull('service_booking.mr_code');
        }
        $PatientMRCode = $baseQuery->get();

        return response()->json($PatientMRCode);
    }

    public function OrganizationPatient(Request $request)
    {
        $orgId = $request->input('orgId');


        $baseQuery = PatientRegistration::select('mr_code', 'name', 'cell_no')
        ->where('status', 1)
        ->where('org_id', $orgId);
        // $PatientMRCode = $baseQuery
        // ->leftJoin('service_booking', function($join) {
        //     $join->on('patient.mr_code', '=', DB::raw('service_booking.mr_code'))
        //         ->where('service_booking.status', 1);
        // })
        // ->whereNull('service_booking.mr_code');
        $PatientMRCode = $baseQuery->get();
        return response()->json($PatientMRCode);
    }

    public function FetchpatientRecord(Request $request)
    {
        $MR = $request->input('MRno');

        // $patientQuery = PatientRegistration::select('gender_id', 'name', 'dob')
        // ->where('status', 1)
        // ->where('mr_code', $MR);

        $PatientDetails = PatientRegistration::select(
            'patient.name',
            'g.name as gender',
            'patient.dob as dob'
        )
        ->join('gender as g', 'g.id', '=', 'patient.gender_id')
        ->where('patient.status', 1)
        ->where('patient.mr_code', $MR)
        ->first();

        $DOB = Carbon::createFromTimestamp($PatientDetails->dob);
        $currentDate = Carbon::now();

        $years = $currentDate->diffInYears($DOB);
        $months = $currentDate->diffInMonths($DOB->copy()->addYears($years));
        $days = $currentDate->diffInDays($DOB->copy()->addYears($years)->addMonths($months));
        $hours = $currentDate->diffInHours($DOB->copy()->addYears($years)->addMonths($months)->addDays($days));

        $ageParts = [];
        if ($years > 0) {
            $ageParts[] = $years === 1 ? "{$years} year" : "{$years} years";
        }

        if ($months > 0) {
            $ageParts[] = $months === 1 ? "{$months} month" : "{$months} months";
        }

        if ($days > 0) {
            $ageParts[] = $days === 1 ? "{$days} day" : "{$days} days";
        }

        if ($years <= 1 &&$hours > 0) {
            $ageParts[] = $hours === 1 ? "{$hours} hour" : "{$hours} hours";
        }


        $age = implode(', ', $ageParts);
        $PatientDetails->Age =  $age;

        // $PatientDetails = $patientQuery->get();
        return response()->json($PatientDetails);
    }

    public function AddPatient(PatientRegistrationRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->patient_registration)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Name = trim($request->input('patient_name'));
        $GuardianName = trim($request->input('guardian_name'));
        $GuardianRelation = strtolower($request->input('guardian_relation'));
        $NextOfKin = trim($request->input('next_of_kin'));
        $Relation = ($request->input('relation'));
        $Relation = (!empty($Relation) && strtolower($Relation) !== 'null') ? strtolower($Relation) : '';
        if (((!empty($NextOfKin) && $NextOfKin !== 'null') && (empty($Relation) || $Relation === 'null')) || ((empty($NextOfKin) || $NextOfKin === 'null') && (!empty($Relation) && $Relation !== 'null')))
        {
            return response()->json(['error' => 'Both Next of Kin and Relation must be filled, or both must be blank.']);
        }
        $Language = trim($request->input('language'));
        $Religion = strtolower($request->input('religion'));
        $MariatlStatus = strtolower($request->input('marital_status'));
        $oldMRCode = trim($request->input('old_mrcode'));
        $Gender = $request->input('patient_gender');
        $Organization = ($request->input('patient_org'));
        $Site = ($request->input('patient_site'));
        $Province = ($request->input('patient_province'));
        $Division = ($request->input('patient_division'));
        $District = ($request->input('patient_district'));
        $CNIC = ($request->input('patient_cnic'));
        $FamilyNo = ($request->input('familyno'));
        $Cell = ($request->input('patient_cell'));
        $AdditionalCell = ($request->input('patient_additionalcell'));
        $Landline = ($request->input('patient_landline'));
        $Email = trim($request->input('patient_email'));
        $HouseNo = trim($request->input('patient_houseno'));
        $Address = trim($request->input('patient_address'));
        $Image = $request->file('patient_img');

        $ageInput = $request->input('patient_age');
        
        // Parse custom age format: 0.1-0.12, 1, 1.1-1.12, etc.
        $ageData = $this->parseCustomAgeBackend($ageInput);
        
        if ($ageData === null) {
            return response()->json(['error' => 'Invalid age format. Please enter a valid age.']);
        }
        
        $years = $ageData['years'];
        $months = $ageData['months'];
        
        $patientDOB = Carbon::now()->subYears($years)->subMonths($months)->startOfDay()->timestamp;

        // $patientDOB = Carbon::now()->subYears($Age)->startOfDay()->timestamp;


        // $DOB = $request->input('patient_dob');
        // $patientDOB = Carbon::createFromFormat('Y-m-d', $DOB)->timestamp;

        $empEdt = $request->input('patient_edt');
        $empEdt = Carbon::createFromFormat('l d F Y - h:i A', $empEdt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($empEdt)->setTimezone('Asia/Karachi');

        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive
        }

        if ($Image) {
            $ImgFileName = $Image->getClientOriginalName();
        } else {
            $ImgFileName = '';
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $PatientExists = PatientRegistration::where('guardian', $GuardianName)
        ->Where('name', $Name)
        ->Where('cnic', $CNIC)
        ->exists();
        if ($PatientExists) {
            return response()->json(['info' => 'Patient already exists.']);
        }
        else
        {
            $Patient = new PatientRegistration();
            $Patient->name = $Name;
            $Patient->guardian = $GuardianName;
            $Patient->guardian_relation = $GuardianRelation;
            $Patient->next_of_kin = $NextOfKin;
            $Patient->relation = $Relation;
            $Patient->language = $Language;
            $Patient->religion = $Religion;
            $Patient->marital_status = $MariatlStatus;
            $Patient->old_mrcode = $oldMRCode;
            $Patient->gender_id = $Gender;
            $Patient->dob = $patientDOB;
            $Patient->org_id = $Organization;
            $Patient->site_id = $Site;
            $Patient->house_no = $HouseNo;
            $Patient->address = $Address;
            $Patient->province_id = $Province;
            $Patient->division_id = $Division;
            $Patient->district_id = $District;
            $Patient->cnic = $CNIC;
            $Patient->family_no = $FamilyNo;
            $Patient->cell_no = $Cell;
            $Patient->additional_cellno = $AdditionalCell;
            $Patient->landline = $Landline;
            $Patient->email = $Email;
            $Patient->img = $ImgFileName;
            $Patient->status = $status;
            $Patient->user_id = $sessionId;
            $Patient->last_updated = $last_updated;
            $Patient->timestamp = $timestamp;
            $Patient->effective_timestamp = $empEdt;

            $Patient->save();
            if ($Image) {
                $ImgFileName = $Patient->id . '_' . $ImgFileName;
                $Image->move(public_path('assets/patient'), $ImgFileName);
            }
            $orgCode = Organization::find($Organization)->code;
            $orgName = Organization::find($Organization)->organization;
            $siteName = Site::find($Site)->name;
            $MRCode = $orgCode.'-000000'.$Patient->id;
            $Patient->mr_code = $MRCode;

            if (empty($Patient->id)) {
                return response()->json(['error' => 'Failed to create Patient.']);
            }
            $logs = Logs::create([
                'module' => 'patient',
                'content' => "'{$Name}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $Patient->logid = $logs->id;
            $Patient->save();
            return response()->json([
                'success' => 'Please select an appropriate action from the options below:',
                'org_id' => $Patient->org_id,
                'site_id' => $Patient->site_id,
                'orgName' => $orgName,
                'siteName' => $siteName,
                'mr_code' => $Patient->mr_code
            ]);
        }
    }

    public function GetPatientData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->patient_registration)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Patients = PatientRegistration::query()
        ->leftJoin('organization', 'organization.id', '=', 'patient.org_id')
        ->join('org_site', 'org_site.id', '=', 'patient.site_id')
        ->join('province', 'province.id', '=', 'patient.province_id')
        ->join('division', 'division.id', '=', 'patient.division_id')
        ->join('district', 'district.id', '=', 'patient.district_id')
        ->join('gender', 'gender.id', '=', 'patient.gender_id')
        ->select(
            'patient.*',
            'organization.organization as orgName',
            'org_site.name as siteName',
            'gender.name as genderName',
            'province.name as provinceName',
            'division.name as divisionName',
            'district.name as districtName'
        )
        ->orderBy('patient.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $Patients->where('patient.org_id', '=', $sessionOrg);
        }
        $Patients = $Patients;
        // ->get()
        // return DataTables::of($Patients)
        return DataTables::eloquent($Patients)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('patient.name', 'like', "%{$search}%")
                            ->orWhere('patient.mr_code', 'like', "%{$search}%")
                            ->orWhere('patient.cnic', 'like', "%{$search}%")
                            ->orWhere('patient.cell_no', 'like', "%{$search}%")
                            ->orWhere('patient.address', 'like', "%{$search}%")
                            ->orWhere('organization.organization', 'like', "%{$search}%")
                            ->orWhere('province.name', 'like', "%{$search}%")
                            ->orWhere('division.name', 'like', "%{$search}%")
                            ->orWhere('district.name', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($Patient) {
                return $Patient->id;
            })
            ->editColumn('patient_detail', function ($Patient) {
                $session = auth()->user();
                $sessionName = $session->name;
                $sessionId = $session->id;
                $MrCode = $Patient->mr_code;
                $effectiveDate = Carbon::createFromTimestamp($Patient->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($Patient->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($Patient->last_updated)->format('l d F Y - h:i A');

                $DOB = Carbon::createFromTimestamp($Patient->dob);
                $currentDate = Carbon::now();

                $years = $currentDate->diffInYears($DOB);
                $months = $currentDate->diffInMonths($DOB->copy()->addYears($years));
                $days = $currentDate->diffInDays($DOB->copy()->addYears($years)->addMonths($months));
                $hours = $currentDate->diffInHours($DOB->copy()->addYears($years)->addMonths($months)->addDays($days));

                $ageParts = [];
                if ($years > 0) {
                    $ageParts[] = $years === 1 ? "{$years} year" : "{$years} years";
                }

                if ($months > 0) {
                    $ageParts[] = $months === 1 ? "{$months} month" : "{$months} months";
                }

                if ($days > 0) {
                    $ageParts[] = $days === 1 ? "{$days} day" : "{$days} days";
                }

                if ($years <= 1 &&$hours > 0) {
                    $ageParts[] = $hours === 1 ? "{$hours} hour" : "{$hours} hours";
                }


                $age = implode(', ', $ageParts);

                $createdInfo = "
                        <b>Created By:</b> " . ucwords($sessionName) . "  <br>
                        <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                        <b>RecordedAt:</b> " . $timestamp ." <br>
                        <b>LastUpdated:</b> " . $lastUpdated;

                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($Patient->orgName);
                }

                return '<b>MR Code: </b>'.ucwords($MrCode)
                        .'<hr class="mt-1 mb-2">
                        '.ucwords($Patient->name)
                        .'<br>'.ucwords($Patient->genderName)
                        .'<br><b>Age: </b>'.$age
                        . '<hr class="mt-1 mb-2">'
                        . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                        . '<i class="fa fa-toggle-right"></i> View Details'
                        . '</span>';
            })
            ->addColumn('identity', function ($Patient) {
                $cnic = empty($Patient->cnic) ? 'N/A' : ($Patient->cnic);
                $familyNumber = empty($Patient->family_no) ? 'N/A' : ($Patient->family_no);

                return '<b>CNIC: </b>' . $cnic
                        . '<hr class="mt-1 mb-2">'
                        . '<b>Family No: </b>' . $familyNumber;
            })
            ->addColumn('contact', function ($Patient) {
                $Email = empty($Patient->email) ? 'N/A' : ($Patient->email);
                $CellNo = empty($Patient->cell_no) ? 'N/A' : ($Patient->cell_no);
                return '<b>Cell No: </b>'.$CellNo
                        .'<hr class="mt-1 mb-2">
                        <b>Email: </b>'.$Email
                        .'<br><b>Address: </b>'.ucwords($Patient->address)
                        .'<br><b>District: </b>'.ucwords($Patient->districtName)
                        .'<br><b>Division: </b> '.ucwords($Patient->divisionName)
                        .'<br><b>Province: </b> '.ucwords($Patient->provinceName);

            })
            // ->addColumn('service_booking', function ($Patient) {
            //     if ($Patient->service_starttime && $Patient->servicebookingStatus == 1) {
            //         $StartTime = Carbon::createFromTimestamp($Patient->service_starttime)->format('l d F Y - h:i A');
            //         $EndTime = Carbon::createFromTimestamp($Patient->service_endtime)->format('l d F Y - h:i A');
            //         $DayTime = '<b>'.ucwords($Patient->LocationSchedule).'</b><br><b>Start Time: </b> '.$StartTime . ' <br> <b>End Time: </b> ' .$EndTime;

            //         return '<b>Remarks: </b>'.ucwords($Patient->remarks)
            //                 .'<hr class="mt-1 mb-2">
            //                  <b>Patient Status:</b>'.ucwords($Patient->patient_status)
            //                 .'<br><b>Patient Priority:</b>'.ucwords($Patient->patient_priority)
            //                 .'<br><b>Service Location: </b> '.ucwords($Patient->locationName)
            //                 .'<hr class="mt-1 mb-2">'.$DayTime;
            //     }
            //     else {
            //         return 'N/A';
            //     }
            // })
            // ->addColumn('patient_inout', function ($Patient) {
            //     if ($Patient->service_start_time && $Patient->patientinoutStatus == 1) {
            //         $StartTime = Carbon::createFromTimestamp($Patient->service_start_time)->format('d F Y - h:i A');
            //         if ($Patient->service_end_time) {
            //             $EndTime = Carbon::createFromTimestamp($Patient->service_end_time)->format('d F Y - h:i A');
            //         } else {
            //             $EndTime = '<span class="badge badge-primary">In Processing</span>';
            //         }
            //         return '<b>Service:</b>'.ucwords($Patient->serviceName)
            //                 .'<hr class="mt-1 mb-2">
            //                 <b>Service Mode:</b>'.ucwords($Patient->serviceModeName)
            //                 .'<br><b>Billing Cost Center:</b>'.ucwords($Patient->CCName)
            //                 .'<br><b>Payment Mode: </b> '.ucwords($Patient->payment_mode)
            //                 .'<br><b>Amount: </b> '.number_format($Patient->amount,2)
            //                 .'<br><b>Service Start Time: </b> '.($StartTime)
            //                 .'<br><b>Service End Time: </b> '.($EndTime);
            //     }
            //     else {
            //         return 'N/A';
            //     }
            // })
            ->addColumn('action', function ($Patient) {
                    $PatientId = $Patient->id;
                    $logId = $Patient->logid;
                    $Rights = $this->rights;
                    $edit = explode(',', $Rights->patient_registration)[2];
                    $actionButtons = '';
                    if ($edit == 1) {
                        $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-patient" data-patient-id="'.$PatientId.'">'
                        . '<i class="fa fa-edit"></i> Edit'
                        . '</button>';
                    }
                    $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';
                    $actionButtons .= '<button type="button" class="btn btn-outline-secondary mt-2 patient-detail" data-patient-id="'.$PatientId.'">'
                    . '<i class="fa fa-plus-circle"></i> View All Details'
                    . '</button>';

                    // $actionButtons .= '<button type="button" class="btn btn-outline-secondary mt-2 patient-detail" data-patient-id="'.$PatientId.'">'
                    // . '<i class="fa fa-plus-circle"></i> View All Details'
                    // . '</button>';
                    $actionButtons .= '<br><a href="/patient/print-card/' . $Patient->id . '" target="_blank" class="btn btn-primary mt-2 print-card-link blinking" data-emp-id="' . $Patient->id . '">
                        <i class="fa fa-print"></i> Print Card
                    </a>';
                    return $Patient->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';

            })
            ->editColumn('status', function ($Patient) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->patient_registration)[3];
                return $updateStatus == 1 ? ($Patient->status ? '<span class="label label-success patient_status cursor-pointer" data-id="'.$Patient->id.'" data-status="'.$Patient->status.'">Active</span>' : '<span class="label label-danger patient_status cursor-pointer" data-id="'.$Patient->id.'" data-status="'.$Patient->status.'">Inactive</span>') : ($Patient->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');

            })
            ->rawColumns(['action', 'status','identity','contact',
            'patient_detail'])
            ->make(true);
    }

    public function printPatientCard($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->patient_registration)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $patient = PatientRegistration::select('patient.*', 'gender.name as genderName')
            ->leftJoin('gender', 'gender.id', '=', 'patient.gender_id')
            ->findOrFail($id);

        return view('dashboard.patient_card', compact('patient'));
    }
    public function UpdatePatientStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->patient_registration)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $PatientID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $Patient = PatientRegistration::find($PatientID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $Patient->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $Patient->effective_timestamp = 0;
        }
        $Patient->status = $UpdateStatus;
        $Patient->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'patient',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PatientRegistrationLog = PatientRegistration::where('id', $PatientID)->first();
        $logIds = $PatientRegistrationLog->logid ? explode(',', $PatientRegistrationLog->logid) : [];
        $logIds[] = $logs->id;
        $PatientRegistrationLog->logid = implode(',', $logIds);
        $PatientRegistrationLog->save();

        $Patient->save();
        return response()->json(['success' => true, 200]);
    }

    public function PatientDetailModal($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->patient_registration)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $Patients = PatientRegistration::select('patient.*',
        'organization.organization as orgName',
        'gender.name as genderName','org_site.name as siteName',
        'province.name as provinceName','division.name as divisionName','district.name as districtName')
        ->join('organization', 'organization.id', '=', 'patient.org_id')
        ->join('org_site', 'org_site.id', '=', 'patient.site_id')
        ->join('gender', 'gender.id', '=', 'patient.gender_id')
        ->join('province', 'province.id', '=', 'patient.province_id')
        ->join('division', 'division.id', '=', 'patient.division_id')
        ->join('district', 'district.id', '=', 'patient.district_id')
        ->find($id);

        $patientName = $Patients->name;
        $patientAddress = $Patients->address;
        if (!empty($Patients->house_no)) {
            $patientAddress .= '<br>House # ' . $Patients->house_no;
        }
        $MR = $Patients->mr_code;
        $patientGuardianName = $Patients->guardian;
        $patientGuardianRelation = $Patients->guardian_relation;
        $patientNextOfKin = $Patients->next_of_kin;
        $patientNextOfKinRelation = $Patients->relation;
        $Language = $Patients->language;
        $Religion = $Patients->religion;
        $MaritalStatus = $Patients->marital_status;
        $oldMR = $Patients->old_mrcode;
        $Gender = $Patients->genderName;
        $DateOfBirth = $Patients->dob;
        $DateOfBirth = date("j-M-Y", $DateOfBirth);
        $Org = $Patients->orgName;
        $Site = $Patients->siteName;
        $Province = $Patients->provinceName;
        $Division = $Patients->divisionName;
        $District = $Patients->districtName;
        $CNIC = $Patients->cnic;
        $familyNo = $Patients->family_no;
        $CellNo = $Patients->cell_no;
        if (empty($CellNo)) {
            $CellNo = 'N/A';
        }
        $AdditionalCellNo = $Patients->additional_cellno;
        $Landline = $Patients->landline;
        $Email = $Patients->email;
        $Image = $Patients->img;

        if($Image == '')
        {
            $Image = ($Gender == 'Male') ? 'assets/usericon/man.png' : 'assets/usericon/woman.png';
        }
        else {
            $Image = $id.'_'.$Image;
            $ImgPath = 'assets/patient/' . $Image;
            $Image = asset($ImgPath);
        }

        if($oldMR == '')
        {
            $oldMR = 'N/A';
        }


        $AdditionalCellNo = empty($AdditionalCellNo) ? 'N/A' : $AdditionalCellNo;
        $Landline = empty($Landline) ? 'N/A' : $Landline;
        $Email = empty($Email) ? 'N/A' : $Email;
        $CNIC = empty($CNIC) ? 'N/A' : $CNIC;
        $familyNo = empty($familyNo) ? 'N/A' : $familyNo;

        $data = [
            'patientName' => ucwords($patientName),
            'patientAddress' => ucwords($patientAddress),
            'MR' => ucwords($MR),
            'patientGuardianName' => ucwords($patientGuardianName),
            'patientGuardianRelation' => ucwords($patientGuardianRelation),
            'patientNextOfKin' => ucwords($patientNextOfKin),
            'patientNextOfKinRelation' => ucwords($patientNextOfKinRelation),
            'Language' => ucwords($Language),
            'Religion' => ucwords($Religion),
            'MaritalStatus' => ucwords($MaritalStatus),
            'oldMR' => ucwords($oldMR),
            'Gender' => ucwords($Gender),
            'DateOfBirth' => ucwords($DateOfBirth),
            'Org' => ucwords($Org),
            'Site' => ucwords($Site),
            'Province' => ucwords($Province),
            'Division' => ucwords($Division),
            'District' => ucwords($District),
            'CNIC' => $CNIC,
            'familyNo' => ucwords($familyNo),
            'CellNo' => $CellNo,
            'AdditionalCellNo' => $AdditionalCellNo,
            'Landline' => $Landline,
            'Email' => $Email,
            'Image' => $Image,
        ];
        return response()->json($data);
    }

    public function UpdatePatientModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->patient_registration)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $Patients = PatientRegistration::select('patient.*',
        'organization.organization as orgName',
        'gender.name as genderName','org_site.name as siteName',
        'province.name as provinceName','division.name as divisionName','district.name as districtName')
        ->join('organization', 'organization.id', '=', 'patient.org_id')
        ->join('org_site', 'org_site.id', '=', 'patient.site_id')
        ->join('gender', 'gender.id', '=', 'patient.gender_id')
        ->join('province', 'province.id', '=', 'patient.province_id')
        ->join('division', 'division.id', '=', 'patient.division_id')
        ->join('district', 'district.id', '=', 'patient.district_id')
        ->find($id);

        // $Patients = DB::table('patient')
        // ->leftJoin('service_booking', 'patient.mr_code', '=', 'service_booking.mr_code')
        // ->leftJoin('service_booking', function ($join) {
        //     $join->on('patient.mr_code', '=', 'service_booking.mr_code')
        //          ->on('patient.service_id', '=', 'service_booking.service_id')
        //          ->on('patient.service_mode_id', '=', 'service_booking.service_mode_id')
        //          ->on('patient.billing_cc', '=', 'service_booking.billing_cc')
        //          ->on('patient.emp_id', '=', 'service_booking.emp_id');
        // })
        // ->leftJoin('patient_inout', function ($join) {
        //     $join->on('service_booking.mr_code', '=', 'patient_inout.mr_code')
        //          ->on('service_booking.service_id', '=', 'patient_inout.service_id')
        //          ->on('service_booking.service_mode_id', '=', 'patient_inout.service_mode_id')
        //          ->on('service_booking.billing_cc', '=', 'patient_inout.billing_cc')
        //          ->on('service_booking.emp_id', '=', 'patient_inout.emp_id');
        // })
        // ->join('organization', 'organization.id', '=', 'patient.org_id')
        // ->join('org_site', 'org_site.id', '=', 'patient.site_id')
        // ->leftJoin('service_location', 'service_location.id', '=', 'service_booking.service_location_id')
        // ->leftJoin('service_location_scheduling', 'service_location_scheduling.id', '=', 'service_booking.schedule_id')
        // ->leftJoin('services', 'services.id', '=', 'patient_inout.service_id') // Use LEFT JOIN here
        // ->leftJoin('service_mode', 'service_mode.id', '=', 'patient_inout.service_mode_id')
        // ->leftJoin('costcenter', 'costcenter.id', '=', 'patient_inout.billing_cc')
        // ->join('gender', 'gender.id', '=', 'patient.gender_id')
        // ->join('province', 'province.id', '=', 'patient.province_id')
        // ->join('division', 'division.id', '=', 'patient.division_id')
        // ->join('district', 'district.id', '=', 'patient.district_id')
        // ->select('patient.*', 'service_booking.service_location_id', 'service_booking.schedule_id',
        // 'service_booking.service_starttime','service_booking.service_endtime','service_booking.emp_id',
        // 'service_booking.patient_status','service_booking.patient_priority','service_booking.remarks',
        // 'service_booking.status as servicebookingStatus',
        // 'patient_inout.service_id','patient_inout.service_mode_id','patient_inout.billing_cc',
        // 'patient_inout.amount','patient_inout.payment_mode','patient_inout.service_start_time',
        // 'patient_inout.service_end_time','patient_inout.status as patientinoutStatus',
        // 'organization.organization as orgName','org_site.name as siteName',
        // 'service_location.name as locationName','service_location_scheduling.name as locationSchedule',
        // 'services.name as serviceName','service_mode.name as servicemodeName',
        // 'costcenter.name as CCName','gender.name as genderName','province.name as provinceName','division.name as divisionName',
        // 'district.name as districtName')
        // ->where('patient.id', '=', $id)
        // ->first();


        $effective_timestamp = $Patients->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $DOB = $Patients->dob;
        $DOB = Carbon::createFromTimestamp($DOB);

        $Image = $Patients->img;
        if($Image != '')
        {
            $Image = $id.'_'.$Image;
            $ImgPath = 'assets/patient/' . $Image;
            $Image = asset($ImgPath);
        }
        else {
            $Image = '';
        }

        $data = [
            'id' => $id,
            'patientName' => ucwords($Patients->name),
            'guardianName' =>  ucwords($Patients->guardian),
            'guardianRelation' => ucwords($Patients->guardian_relation),
            'noxtofKin' => ucwords($Patients->next_of_kin),
            'noxtofKinRelation' => ucwords($Patients->relation),
            'oldMRNo' => ($Patients->old_mrcode),
            'genderName' => ucwords($Patients->genderName),
            'genderId' => ($Patients->gender_id),
            'language' => ucwords($Patients->language),
            'Religion' => ucwords($Patients->religion),
            'MaritalStatus' => ucwords($Patients->marital_status),
            'DOB' => $DOB,
            'orgName' => ucwords($Patients->orgName),
            'orgId' => ($Patients->org_id),
            'siteName' => ucwords($Patients->siteName),
            'siteId' => ($Patients->site_id),
            'provinceName' => ucwords($Patients->provinceName),
            'provinceId' => ($Patients->province_id),
            'divisionName' => ucwords($Patients->divisionName),
            'divisionId' => ($Patients->division_id),
            'districtName' => ucwords($Patients->districtName),
            'districtId' => ($Patients->district_id),
            'cnic' => ($Patients->cnic),
            'familyNo' => ($Patients->family_no),
            'cellNo' => ($Patients->cell_no),
            'additionalCell' => ($Patients->additional_cellno),
            'Landline' => ($Patients->landline),
            'Email' => ($Patients->email),
            'HouseNo' => ucwords($Patients->house_no),
            'Address' => ucwords($Patients->address),
            'Image' => $Image,
            'effective_timestamp' => $effective_timestamp,
        ];
        return response()->json($data);
    }

    public function UpdatePatient(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->patient_registration)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $NextOfKin = $request->input('up_nextofkin');
        $Relation = $request->input('up_nextofkinRelation');
        if (((!empty($NextOfKin) && $NextOfKin !== 'null') && (empty($Relation) || $Relation === 'null')) || ((empty($NextOfKin) || $NextOfKin === 'null') && (!empty($Relation) && $Relation !== 'null')))
        {
            return response()->json(['error' => 'Both Next of Kin and Relation must be filled, or both must be blank.']);
        }
        $Patient = PatientRegistration::findOrFail($id);
        $Patient->name = $request->input('up_name');
        $Patient->guardian = $request->input('up_guardianName');
        $Patient->guardian_relation = strtolower($request->input('up_guardianRelation'));
        $Patient->next_of_kin = $NextOfKin;
        $Patient->relation = $Relation;
        $Patient->old_mrcode = $request->input('up_oldmr');
        $Patient->gender_id = $request->input('up_gender');
        $Patient->language = strtolower($request->input('up_language'));
        $Patient->religion = $request->input('up_religion');
        $Patient->marital_status = $request->input('up_maritalStatus');
        $orgID = $request->input('up_org');
        if (isset($orgID)) {
            $Patient->org_id = $orgID;
        }
        $Patient->site_id = $request->input('up_site');
        $Patient->province_id = $request->input('up_province');
        $Patient->division_id = $request->input('up_division');
        $Patient->district_id = $request->input('up_district');
        $Patient->cnic = $request->input('up_cnic');
        $Patient->family_no = $request->input('up_familyno');
        $Patient->cell_no = $request->input('up_cell');
        $Patient->additional_cellno = $request->input('up_additionalCell');
        $Patient->landline = $request->input('up_landline');
        $Patient->email = $request->input('up_email');
        $Patient->house_no = $request->input('up_houseno');
        $Patient->address = $request->input('up_address');

        $patientImg = $request->file('u_patientImg');
        if (isset($patientImg)) {
            $oldImagePath = public_path('assets/patient/' . $id . '_' .$Patient->img);
            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }
            $ImgName = $patientImg->getClientOriginalName();
            $Patient->img = $ImgName;
            $ImgName = $id . '_' . $ImgName;
            $patientImg->move(public_path('assets/patient'), $ImgName);
        }

        $DOB = $request->input('up_dob');
        $DOB = Carbon::createFromFormat('Y-m-d', $DOB)->timestamp;
        $Patient->dob = $DOB;

        $effective_date = $request->input('up_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $Patient->last_updated = $this->currentDatetime;
        $Patient->effective_timestamp = $effective_date;
        $Patient->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $Patient->save();

        if (empty($Patient->id)) {
            return response()->json(['error' => 'Failed to update Patient Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'patient',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PatientLog = PatientRegistration::where('id', $Patient->id)->first();
        $logIds = $PatientLog->logid ? explode(',', $PatientLog->logid) : [];
        $logIds[] = $logs->id;
        $PatientLog->logid = implode(',', $logIds);
        $PatientLog->save();
        return response()->json(['success' => ' Patient Details updated successfully']);
    }

    public function PatientArrivalDeparture()
    {
        $colName = 'patient_arrival_and_departure';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        // $Provinces = Province::where('status', 1)->get();
        // $ServiceBookings = ServiceBooking::select('remarks','id')->where('status', 1)->get();
        // $UserorgId = $user->org_id;
        // $orgCode = Organization::where('id', $UserorgId)->value('code');
        $Organizations = Organization::select('id', 'organization')->where('status', 1)->get();
        $Patients = PatientRegistration::select('mr_code','name','cell_no')->where('status', 1)->orderBy('id', 'desc')->get();


        return view('dashboard.patient-inout', compact('user','Organizations','Patients'));
    }

    public function AddPatientArrival(PatientArrivalDepartureRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->patient_arrival_and_departure)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        // $BookingId = $request->input('booking_id');
        $MRno = $request->input('pio_mr');
        $Org = $request->input('pio_org');
        $Site = $request->input('pio_site');
        $PatientStaus = $request->input('pio_status');
        $PatientPriority = $request->input('pio_priority');
        $Remarks = $request->input('pio_remarks');
        $ServiceLocation = $request->input('pio_serviceLocation');
        $ServiceLocationSchedule = $request->input('pio_serviceSchedule');
        $Physician = $request->input('pio_emp');
        $Service = $request->input('pio_service');
        $ServiceMode = $request->input('pio_serviceMode');
        $BillingCC = $request->input('pio_billingCC');
        // $Amount = $request->input('amount_received');
        $PaymentMode = $request->input('pio_payMode');

        $CurrentTimestamp = $this->currentDatetime;
        $ServiceStartTime = $this->currentDatetime;
        $status = 1; //Active

        // $ServiceStartTime = $request->input('pio_serviceStart');
        // $ServiceStartTime = Carbon::createFromFormat('l d F Y - h:i A', $ServiceStartTime)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($ServiceStartTime)->setTimezone('Asia/Karachi');
        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive
        // }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        // $common = [
        //     ['emp_id',         $Physician],
        //     ['mr_code',        $MRno],
        //     ['service_id',     $Service],
        //     ['service_mode_id',$ServiceMode],
        //     ['billing_cc',     $BillingCC],
        // ];

        // $existsInBooking = ServiceBooking::where($common)
        // ->where('status', 1)
        // ->exists();

        // $existsInArrival = PatientArrivalDeparture::where($common)
        // ->where('status', 1)
        // ->exists();

        // $existsInRequisition = RequisitionForEPI::where($common)
        // ->where('status', 1)
        // ->select('action')
        // ->first();

        // if ($existsInBooking || $existsInArrival || $existsInRequisition) {
        //     if ($existsInBooking) {
        //         $msg = 'Service Booking already exists for this MR#, service, service mode, billing CC & physician';
        //     }
        //     elseif ($existsInArrival) {
        //         $msg = 'Arrival already recorded for this MR#, service, service mode, billing CC & physician';
        //     }
        //     else {
        //         $map = [
        //             'i' => 'Investigation',
        //             'e' => 'Encounter',
        //             'p' => 'Procedure',
        //         ];
        //         $type = $map[strtolower($existsInRequisition->action)] ?? 'Request';
        //         $msg  = "{$type} already requested for MR#, service, service mode, billing CC & physician.";
        //         $msg = 'Requisition for EPI';
        //     }

        //     return response()->json([
        //         'info' => $msg
        //     ]);
        // }

        $PatientArrived = PatientArrivalDeparture::where('emp_id', $Physician)
        ->where('mr_code', $MRno)
        ->where('service_id', $Service)
        ->where('service_mode_id', $ServiceMode)
        ->where('billing_cc', $BillingCC)
        ->where('status', 1)
        ->exists();

        if ($PatientArrived) {
            return response()->json(['info' => 'Arrival already recorded for this MR#, service, service mode, billing CC & physician']);
        }
        else{
            $PatientArrivalDeparture = new PatientArrivalDeparture();
            $PatientArrivalDeparture->org_id = $Org;
            $PatientArrivalDeparture->site_id = $Site;
            $PatientArrivalDeparture->service_location_id = $ServiceLocation;
            $PatientArrivalDeparture->schedule_id = $ServiceLocationSchedule;
            $PatientArrivalDeparture->mr_code = $MRno;
            $PatientArrivalDeparture->service_id = $Service;
            $PatientArrivalDeparture->service_mode_id = $ServiceMode;
            $PatientArrivalDeparture->billing_cc = $BillingCC;
            $PatientArrivalDeparture->emp_id = $Physician;
            $PatientArrivalDeparture->patient_status = $PatientStaus;
            $PatientArrivalDeparture->patient_priority = $PatientPriority;
            $PatientArrivalDeparture->remarks = $Remarks;
            // $PatientArrivalDeparture->amount = $Amount;
            // $PatientArrivalDeparture->payment_mode = $PaymentMode;
            $PatientArrivalDeparture->status = $status;
            $PatientArrivalDeparture->user_id = $sessionId;
            $PatientArrivalDeparture->last_updated = $last_updated;
            $PatientArrivalDeparture->timestamp = $CurrentTimestamp;
            $PatientArrivalDeparture->service_start_time = $ServiceStartTime;
            $PatientArrivalDeparture->save();

            if (empty($PatientArrivalDeparture->id)) {
                return response()->json(['error' => 'Unable to Add Details! Please Try Again.']);
            }

            $ServiceRates = ActivatedServiceRate::select('activated_service_rate.sell_price')
            ->where('activated_service_rate.service_mode_id', $ServiceMode)
            ->first();

            $Services = Service::select('services.name')
            ->where('services.id', $Service)
            ->first();

            $Remarks = $MRno.'-'.$Services->name;
            $billedAmount = $ServiceRates->sell_price ?? 0;

            $Transaction = new FinancialTransactions();
            $Transaction->org_id = $Org;
            $Transaction->site_id = $Site;
            $Transaction->transaction_type_id = 1;
            $Transaction->payment_option = $PaymentMode;
            $Transaction->amount = $billedAmount;
            $Transaction->discount = 0;
            $Transaction->debit = 1;
            $Transaction->credit = 0;
            $Transaction->remarks = $Remarks;
            $Transaction->status = $status;
            $Transaction->user_id = $sessionId;
            $Transaction->effective_timestamp = $ServiceStartTime;
            $Transaction->timestamp = $CurrentTimestamp;
            $Transaction->last_updated = $last_updated;
            $Transaction->save();


            $logs = Logs::create([
                'module' => 'patient',
                'content' => "Patient Arrival Details added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $PatientArrivalDeparture->logid = $logs->id;
            $PatientArrivalDeparture->save();
            return response()->json(['success' => 'Patient Arrival Details added successfully']);
        }
    }

    // public function GetPatientArrivalDepartureDetails(Request $request)
    // {
    //     $rights = $this->rights;
    //     $view = explode(',', $rights->patient_arrival_and_departure)[1];
    //     if($view == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    
    //     // Get session user for organization filtering
    //     $session = auth()->user();
    //     $sessionOrg = $session->org_id;
        
    //     // // Cache key for this specific query
    //     // $cacheKey = "patient_arrival_departure_{$sessionOrg}_" . 
    //     //            ($request->site_id ?? 'all') . "_" . 
    //     //            ($request->mr_no ?? 'all') . "_" . 
    //     //            ($request->date_filter ?? 'today');
        
    //     // // Try to get from cache first (cache for 5 minutes)
    //     // if (Cache::has($cacheKey)) {
    //     //     return Cache::get($cacheKey);
    //     // }
        
    //         // Revert to original working query structure
    //     $query = DB::table(DB::raw("(
    //         SELECT mr_code, emp_id, billing_cc, service_id, service_mode_id
    //         FROM (
    //             SELECT mr_code, emp_id, billing_cc, service_id, service_mode_id FROM service_booking
    //             UNION ALL
    //             SELECT mr_code, emp_id, billing_cc, service_id, service_mode_id FROM patient_inout
    //             UNION ALL
    //             SELECT mr_code, COALESCE(emp_id, 0) as emp_id, billing_cc, service_id, service_mode_id FROM req_epi
    //         ) all_records
    //     ) combined"))
    //     ->select([
    //         'combined.mr_code',
    //         'combined.emp_id',
    //         'combined.billing_cc',
    //         'combined.service_id',
    //         'combined.service_mode_id',
    //         'p.name as patientName',
    //         'g.name as genderName',
    //         'p.dob as patientDOB',
    //         'p.cnic as patientCNIC',
    //         'p.cell_no as patientCellNo',
    //         'p.email as patientEmail',
    //         'p.address as patientAddress',
    //         'prov.name as provinceName',
    //         'div_table.name as divisionName',
    //         'dist.name as districtName',
    //         'sb.id as serviceBookingId',
    //         'sb.service_starttime',
    //         'sb.service_endtime',
    //         'sb.patient_status as BookingPatientStatus',
    //         'sb.patient_priority as BookingPatientPriority',
    //         'sb.remarks as sb_remarks',
    //         'pi.id',
    //         'pi.service_start_time as patientArrivalTime',
    //         'pi.service_end_time as patientEndTime',
    //         'pi.timestamp',
    //         'pi.last_updated',
    //         'pi.logid',
    //         'pi.user_id',
    //         'pi.status',
    //         'pi.remarks as pi_remarks',
    //         's.name as ServiceName',
    //         's.id as ServiceId',
    //         'sm.name as serviceModeName',
    //         'sm.id as serviceModeId',
    //         'cc.name as billingCC',
    //         'cc.id as billingCCId',
    //         'sl.name as locationName',
    //         'sl.id as locationId',
    //         'sls.name as LocationSchedule',
    //         'sls.id as LocationScheduleId',
    //         'sls.start_timestamp',
    //         'sls.end_timestamp',
    //         'sls.schedule_pattern',
    //         'e.name as empName',
    //         'e.id as empId',
    //         'o.organization as orgName',
    //         'o.id as orgId',
    //         'os.name as siteName',
    //         'os.id as siteId',
    //         'pcc.name as performingCC',
    //         'asr.sell_price as sellPrice',
    //         'req.effective_timestamp as reqEffectiveTimestamp',
    //         DB::raw('COALESCE(pi.remarks, sb.remarks, req.remarks) as Remarks')
    //     ])
    //     ->distinct() // Add DISTINCT to eliminate duplicates from ASR JOIN
    //     ->join('patient as p', 'p.mr_code', '=', 'combined.mr_code')
    //     ->join('gender as g', 'g.id', '=', 'p.gender_id')
    //     ->leftJoin('province as prov', 'prov.id', '=', 'p.province_id')
    //     ->join('division as div_table', 'div_table.id', '=', 'p.division_id')
    //     ->join('district as dist', 'dist.id', '=', 'p.district_id')
    //     ->leftJoin('service_booking as sb', function($join) {
    //         $join->on('sb.mr_code', '=', 'combined.mr_code')
    //             ->on('sb.service_id', '=', 'combined.service_id')
    //             ->on('sb.service_mode_id', '=', 'combined.service_mode_id')
    //             ->on('sb.billing_cc', '=', 'combined.billing_cc')
    //             ->on('sb.emp_id', '=', 'combined.emp_id');
    //     })
    //     ->leftJoin('patient_inout as pi', function($join) {
    //         $join->on('pi.mr_code', '=', 'combined.mr_code')
    //             ->on('pi.service_id', '=', 'combined.service_id')
    //             ->on('pi.service_mode_id', '=', 'combined.service_mode_id')
    //             ->on('pi.billing_cc', '=', 'combined.billing_cc')
    //             ->on('pi.emp_id', '=', 'combined.emp_id');
    //     })
    //     ->leftJoin('req_epi as req', function($join) {
    //         $join->on('req.mr_code', '=', 'combined.mr_code')
    //             ->on('req.service_id', '=', 'combined.service_id')
    //             ->on('req.service_mode_id', '=', 'combined.service_mode_id')
    //             ->on('req.billing_cc', '=', 'combined.billing_cc')
    //             ->whereRaw('(req.emp_id = combined.emp_id OR (req.emp_id IS NULL AND combined.emp_id = 0))');
    //     })
    //     ->join('services as s', 's.id', '=', 'combined.service_id')
    //     ->join('service_mode as sm', 'sm.id', '=', 'combined.service_mode_id')
    //     ->join('costcenter as cc', 'cc.id', '=', 'combined.billing_cc')
    //     ->leftJoin('employee as e', function($join) {
    //         $join->on('e.id', '=', 'combined.emp_id')
    //             ->where('combined.emp_id', '!=', 0);
    //     })
    //     ->leftJoin('organization as o', 'o.id', '=', DB::raw('COALESCE(sb.org_id, pi.org_id, req.org_id)'))
    //     ->leftJoin('org_site as os', 'os.id', '=', DB::raw('COALESCE(sb.site_id, pi.site_id, req.site_id)'))
    //     ->leftJoin('service_location as sl', 'sl.id', '=', 'sb.service_location_id')
    //     ->leftJoin('service_location_scheduling as sls', 'sls.id', '=', 'sb.schedule_id')
    //     ->leftJoin('costcenter as pcc', 'pcc.id', '=', 'e.cc_id')
    //     ->leftJoin('activated_service as as1', function($join) {
    //         $join->on('as1.service_id', '=', 'combined.service_id')
    //             ->on('as1.site_id', '=', DB::raw('COALESCE(sb.site_id, pi.site_id, req.site_id)'))
    //             ->where('as1.status', '=', 1);
    //     })
    //     ->leftJoin('activated_service_rate as asr', function($join) {
    //         $join->on('asr.activated_service_id', '=', 'as1.id')
    //             ->on('asr.service_mode_id', '=', 'combined.service_mode_id');
    //     })
    //     ->where('p.status', 1)
    //     ->orderBy('combined.mr_code', 'desc');
    
    //     // Apply additional filters
    //     if($sessionOrg != '0') {
    //         $query->where('p.org_id', $sessionOrg);
    //     }
    //     if ($request->has('site_id') && $request->site_id != '' && $request->site_id != 'Loading...') {
    //         $query->where('p.site_id', $request->site_id);
    //     }
    //     if ($request->has('mr_no') && $request->mr_no != '' && $request->mr_no != 'Loading...') {
    //         $query->where('p.mr_code', $request->mr_no);
    //     }

    //             // Check if site or MR is selected
    //     $siteSelected = $request->has('site_id') && $request->site_id != '' && $request->site_id != 'Loading...';
    //     $mrSelected = $request->has('mr_no') && $request->mr_no != '' && $request->mr_no != 'Loading...';
        
    //     // Apply date filtering if a specific date filter is chosen (regardless of site/MR selection)
    //     if ($request->has('date_filter') && $request->date_filter != '') {
    //         $dateFilter = $request->date_filter;
    //         $today = Carbon::today()->setTimezone('Asia/Karachi');
            
            
    //         switch ($dateFilter) {
    //             case 'today':
    //                 // Use current date in Asia/Karachi timezone (date only, no time)
    //                 $today = Carbon::now()->setTimezone('Asia/Karachi');
    //                 $startDate = $today->copy()->startOfDay()->timestamp;
    //                 $endDate = $today->copy()->endOfDay()->timestamp;
    //                 break;
    //             case 'yesterday':
    //                 // Use yesterday's date in Asia/Karachi timezone (date only, no time)
    //                 $yesterday = Carbon::now()->setTimezone('Asia/Karachi')->subDay();
    //                 $startDate = $yesterday->copy()->startOfDay()->timestamp;
    //                 $endDate = $yesterday->copy()->endOfDay()->timestamp;
    //                 break;
    //             case 'this_week':
    //                 // Use this week's start and end dates in Asia/Karachi timezone
    //                 $thisWeekStart = Carbon::now()->setTimezone('Asia/Karachi')->startOfWeek();
    //                 $thisWeekEnd = Carbon::now()->setTimezone('Asia/Karachi')->endOfWeek();
    //                 $startDate = $thisWeekStart->copy()->startOfDay()->timestamp;
    //                 $endDate = $thisWeekEnd->copy()->endOfDay()->timestamp;
    //                 break;
    //             case 'last_week':
    //                 // Use last week's start date in Asia/Karachi timezone (date only, no time)
    //                 $lastWeekStart = Carbon::now()->setTimezone('Asia/Karachi')->subWeek()->startOfWeek();
    //                 $startDate = $lastWeekStart->copy()->startOfDay()->timestamp;
    //                 break;
    //             case 'this_month':
    //                 // Use this month's start date in Asia/Karachi timezone (date only, no time)
    //                 $thisMonthStart = Carbon::now()->setTimezone('Asia/Karachi')->startOfMonth();
    //                 $startDate = $thisMonthStart->copy()->startOfDay()->timestamp;
    //                 break;
    //             case 'last_month':
    //                 // Use last month's start date in Asia/Karachi timezone (date only, no time)
    //                 $currentYear = Carbon::now()->setTimezone('Asia/Karachi')->year;
    //                 $currentMonth = Carbon::now()->setTimezone('Asia/Karachi')->month;
    //                 $lastMonthYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
    //                 $lastMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
                    
    //                 $lastMonthStart = Carbon::create($lastMonthYear, $lastMonth, 1, 0, 0, 0, 'Asia/Karachi');
    //                 $startDate = $lastMonthStart->copy()->startOfDay()->timestamp;

    //                 break;
    //             case 'this_year':
    //                 // Use this year's start date in Asia/Karachi timezone (date only, no time)
    //                 $currentYear = Carbon::now()->setTimezone('Asia/Karachi')->year;
    //                 $thisYearStart = Carbon::create($currentYear, 1, 1, 0, 0, 0, 'Asia/Karachi');
    //                 $startDate = $thisYearStart->copy()->startOfDay()->timestamp;
           
    //                 break;
    //             case 'last_year':
    //                 // Use last year's start date in Asia/Karachi timezone (date only, no time)
    //                 $currentYear = Carbon::now()->setTimezone('Asia/Karachi')->year;
    //                 $lastYear = $currentYear - 1;
    //                 $lastYearStart = Carbon::create($lastYear, 1, 1, 0, 0, 0, 'Asia/Karachi');
    //                 $startDate = $lastYearStart->copy()->startOfDay()->timestamp;
               
    //                 break;
    //             default:
    //                 // Default to today - Use current date in Asia/Karachi timezone (date only, no time)
    //                 $today = Carbon::now()->setTimezone('Asia/Karachi');
    //                 $startDate = $today->copy()->startOfDay()->timestamp;
        
    //                 break;
    //         }
            
    //         // Apply date conditions - check each table's timestamp field (date only)
    //         $query->where(function($q) use ($startDate, $endDate) {
    //             $q->where(function($subQ) use ($startDate, $endDate) {
    //                 // Filter by patient_inout.service_start_time (date only)
    //                 $subQ->whereNotNull('pi.id')
    //                      ->whereRaw('DATE(FROM_UNIXTIME(pi.service_start_time)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
    //             })->orWhere(function($subQ) use ($startDate, $endDate) {
    //                 // Filter by service_booking.service_starttime (date only) - ONLY if no patient_inout record exists
    //                 $subQ->whereNotNull('sb.id')
    //                      ->whereNull('pi.id')
    //                      ->whereRaw('DATE(FROM_UNIXTIME(sb.service_starttime)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
    //             })->orWhere(function($subQ) use ($startDate, $endDate) {
    //                 // Filter by req_epi.effective_timestamp (date only) - ONLY if no patient_inout or service_booking record exists
    //                 $subQ->whereNotNull('req.id')
    //                      ->whereNull('pi.id')
    //                      ->whereNull('sb.id')
    //                      ->whereRaw('DATE(FROM_UNIXTIME(req.effective_timestamp)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
    //             });
    //         });
            
    //     } elseif (!($siteSelected || $mrSelected)) {
    //         // If neither site nor MR is selected, apply today's filter by default
    //         $today = Carbon::now()->setTimezone('Asia/Karachi');
    //         $startDate = $today->copy()->startOfDay()->timestamp;
    //         $endDate = $today->copy()->endOfDay()->timestamp;
    //         // Apply date conditions - check each table's timestamp field (date only)
    //         $query->where(function($q) use ($startDate, $endDate) {
    //             $q->where(function($subQ) use ($startDate, $endDate) {
    //                 $subQ->whereNotNull('pi.id')
    //                      ->whereRaw('DATE(FROM_UNIXTIME(pi.service_start_time)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
    //             })->orWhere(function($subQ) use ($startDate, $endDate) {
    //                 $subQ->whereNotNull('sb.id')
    //                      ->whereNull('pi.id')
    //                      ->whereRaw('DATE(FROM_UNIXTIME(sb.service_starttime)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
    //             })->orWhere(function($subQ) use ($startDate, $endDate) {
    //                 $subQ->whereNotNull('req.id')
    //                      ->whereNull('pi.id')
    //                      ->whereNull('sb.id')
    //                      ->whereRaw('DATE(FROM_UNIXTIME(req.effective_timestamp)) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?))', [$startDate, $endDate]);
    //             });
    //         });
    //     } 
    
    //     return DataTables::of($query)
    //         ->addColumn('id_raw', function ($PatientInOutDetail) {
    //             return $PatientInOutDetail->id;
    //         })
    //         ->editColumn('id', function ($PatientInOutDetail) {
    //             // Debug: Log the actual timestamps from the database and identify source table
    //             $sourceTable = 'Unknown';
    //             $sourceTimestamp = 'N/A';
    //             $rawTimestamp = 'N/A';
                
    //             if ($PatientInOutDetail->patientArrivalTime) {
    //                 $sourceTable = 'patient_inout';
    //                 $rawTimestamp = $PatientInOutDetail->patientArrivalTime;
    //                 $sourceTimestamp = Carbon::createFromTimestamp($PatientInOutDetail->patientArrivalTime)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s');
    //             } elseif ($PatientInOutDetail->service_starttime) {
    //                 $sourceTable = 'service_booking';
    //                 $rawTimestamp = $PatientInOutDetail->service_starttime;
    //                 $sourceTimestamp = Carbon::createFromTimestamp($PatientInOutDetail->service_starttime)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s');
    //             } elseif ($PatientInOutDetail->reqEffectiveTimestamp) {
    //                 $sourceTable = 'req_epi';
    //                 $rawTimestamp = $PatientInOutDetail->reqEffectiveTimestamp;
    //                 $sourceTimestamp = Carbon::createFromTimestamp($PatientInOutDetail->reqEffectiveTimestamp)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s');
    //             }
                
                
    //             $effectiveDate = $PatientInOutDetail->patientArrivalTime
    //                 ? Carbon::createFromTimestamp($PatientInOutDetail->patientArrivalTime)->format('l d F Y - h:i A')
    //                 : 'N/A';
    
    //             $timestamp = $PatientInOutDetail->timestamp
    //                 ? Carbon::createFromTimestamp($PatientInOutDetail->timestamp)->format('l d F Y - h:i A')
    //                 : 'N/A';
    
    //             $lastUpdated = $PatientInOutDetail->last_updated
    //                 ? Carbon::createFromTimestamp($PatientInOutDetail->last_updated)->format('l d F Y - h:i A')
    //                 : 'N/A';
    
    //             $createdByName = getUserNameById($PatientInOutDetail->user_id ?? null);
    //             $createdInfo = "
    //                 <b>Created By:</b> " . ucwords((string)$createdByName) . "<br>
    //                 <b>Effective Date&amp;Time:</b> " . $effectiveDate . "<br>
    //                 <b>RecordedAt:</b> " . $timestamp . "<br>
    //                 <b>LastUpdated:</b> " . $lastUpdated;
    
    //             $mrCode = $PatientInOutDetail->mr_code ?? 'N/A';
    //             $PatientName = ucwords((string)($PatientInOutDetail->patientName ?? ''));
    //             $Gender = ucwords((string)($PatientInOutDetail->genderName ?? ''));
    //             $DOB = $PatientInOutDetail->patientDOB
    //                 ? Carbon::createFromTimestamp($PatientInOutDetail->patientDOB)->format('d F Y')
    //                 : 'N/A';
    
    //             $cnic = !empty($PatientInOutDetail->patientCNIC) ? $PatientInOutDetail->patientCNIC : 'N/A';
    //             $Email = !empty($PatientInOutDetail->patientEmail) ? $PatientInOutDetail->patientEmail : 'N/A';
    //             $CellNo = $PatientInOutDetail->patientCellNo ?? 'N/A';
    //             $Address = ucwords((string)($PatientInOutDetail->patientAddress ?? ''));
    //             $Province = ucwords((string)($PatientInOutDetail->provinceName ?? ''));
    //             $District = ucwords((string)($PatientInOutDetail->districtName ?? ''));
    //             $Division = ucwords((string)($PatientInOutDetail->divisionName ?? ''));
    
    //             return $mrCode
    //                 . '<hr class="mt-1 mb-2">'
    //                 . $PatientName
    //                 . '<br>' . $Gender
    //                 . '<br><b>DOB: </b>' . $DOB
    //                 . '<br><b>CNIC: </b>' . $cnic
    //                 . '<br><b>Cell No: </b>' . $CellNo
    //                 . '<br><b>Email: </b>' . $Email
    //                 . '<br><b>Address: </b>' . $Address
    //                 . '<hr class="mt-1 mb-2">'
    //                 . '<b>District: </b>' . $District
    //                 . '<br><b>Division: </b>' . $Division
    //                 . '<br><b>Province: </b>' . $Province
    //                 . '<hr class="mt-1 mb-2">'
    //                 . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body" data-toggle="popover" data-placement="right" data-html="true" data-content="' . $createdInfo . '">'
    //                 . '<i class="fa fa-toggle-right"></i> View Details'
    //                 . '</span>';
    //         })
    //         ->editColumn('serviceBooking', function ($PatientInOutDetail) {
    //             $session = auth()->user();
    //             $BookingId = $PatientInOutDetail->serviceBookingId ?? null;
    
    //             if ($BookingId) {
    //                 $StartTime = $PatientInOutDetail->service_starttime
    //                     ? Carbon::createFromTimestamp($PatientInOutDetail->service_starttime)->format('l d F Y - h:i A')
    //                     : 'N/A';
    
    //                 $EndTime = $PatientInOutDetail->service_endtime
    //                     ? Carbon::createFromTimestamp($PatientInOutDetail->service_endtime)->format('l d F Y - h:i A')
    //                     : 'N/A';
    
    //                 $DayTime = '<b>Start Time: </b> ' . $StartTime . ' <br> <b>End Time: </b> ' . $EndTime;
    
    //                 $empName = ucwords((string)($PatientInOutDetail->empName ?? ''));
    //                 $Remarks = !empty($PatientInOutDetail->sb_remarks) ? ucwords((string)$PatientInOutDetail->sb_remarks) : 'N/A';
    
    //                 $Location = ucwords((string)($PatientInOutDetail->locationName ?? ''));
    //                 $Pattern = ucwords((string)($PatientInOutDetail->schedule_pattern ?? ''));
    //                 $PatientStatus = ucwords((string)($PatientInOutDetail->BookingPatientStatus ?? ''));
    //                 $PatientPriority = ucwords((string)($PatientInOutDetail->BookingPatientPriority ?? ''));
    //                 $ServiceName = ucwords((string)($PatientInOutDetail->ServiceName ?? ''));
    
    //                 $orgName = '';
    //                 if (($session->org_id ?? '0') == '0') {
    //                     $orgName = ' / ' . ucwords((string)($PatientInOutDetail->orgName ?? ''));
    //                 }
    
    //                 $siteOrg = ucwords((string)($PatientInOutDetail->siteName ?? '')) . $orgName;
    
    //                 return $ServiceName
    //                     . '<hr class="mt-1 mb-2">'
    //                     . $empName
    //                     . '<br>' . $siteOrg
    //                     . '<hr class="mt-1 mb-2">'
    //                     . $DayTime
    //                     . '<br><b>Pattern & Location: </b>' . $Pattern . ' & ' . $Location
    //                     . '<br><b>Patient Status: </b>' . $PatientStatus
    //                     . '<br><b>Patient Priority: </b>' . $PatientPriority
    //                     . '<br><b>Remarks: </b>' . $Remarks;
    //             } else {
    //                 return '<h5><b>Unbooked</b></h5><hr class="mt-1 mb-2">';
    //             }
    //         })
    //         ->editColumn('serviceDetails', function ($PatientInOutDetail) {
    //             $ArrivalId = $PatientInOutDetail->id ?? null;
    //             // return $ArrivalId;

    //             $sellPrice = number_format((float)($PatientInOutDetail->sellPrice ?? 0), 2);
    
    //             $orgId = $PatientInOutDetail->orgId ?? '';
    //             $orgName = ucwords((string)($PatientInOutDetail->orgName ?? ''));
                
    //             $siteId = $PatientInOutDetail->siteId ?? '';
    //             $siteName = ucwords((string)($PatientInOutDetail->siteName ?? ''));
    
    //             $ServiceMode = ucwords((string)($PatientInOutDetail->serviceModeName ?? ''));
    //             $ServiceModeId = $PatientInOutDetail->serviceModeId ?? '';
    
    //             $locationName = ucwords((string)($PatientInOutDetail->locationName ?? ''));
    //             $locationId = $PatientInOutDetail->locationId ?? '';
    
    //             $LocationSchedule = ucwords((string)($PatientInOutDetail->LocationSchedule ?? ''));
    //             $LocationScheduleId = $PatientInOutDetail->LocationScheduleId ?? '';
    
    //             $ScheduleStartTime = $PatientInOutDetail->start_timestamp
    //                 ? Carbon::createFromTimestamp($PatientInOutDetail->start_timestamp)->format('l d F Y - h:i A')
    //                 : 'N/A';
    //             $ScheduleEndTime = $PatientInOutDetail->end_timestamp
    //                 ? Carbon::createFromTimestamp($PatientInOutDetail->end_timestamp)->format('l d F Y - h:i A')
    //                 : 'N/A';
    
    //             $Pattern = ucwords(($PatientInOutDetail->schedule_pattern ?? 'N/A'));
    
    //             $empName = ucwords((string)($PatientInOutDetail->empName ?? 'N/A'));
    //             $empId = $PatientInOutDetail->empId ?? '';
    
    //             $Service = ucwords((string)($PatientInOutDetail->ServiceName ?? ''));
    //             $ServiceId = $PatientInOutDetail->ServiceId ?? '';
    
    //             $BillingCC = ucwords((string)($PatientInOutDetail->billingCC ?? ''));
    //             $BillingCCId = $PatientInOutDetail->billingCCId ?? '';
    
    //             $ShowServiceDetails = $ServiceMode . '<br>' . $Service . '<br> <b>Specialty: </b>' . $BillingCC . '<br><b>Responsible Person:</b> ' . $empName;
    
    //             if ($ArrivalId) {
    //                 $patientArrivalTime = $PatientInOutDetail->patientArrivalTime
    //                     ? Carbon::createFromTimestamp($PatientInOutDetail->patientArrivalTime)->format('l d F Y - h:i A')
    //                     : 'N/A';
    
    //                 $patientEndTimeRaw = $PatientInOutDetail->patientEndTime ?? null;
    //                 $Status = $PatientInOutDetail->status ?? null;
    
    //                 if ($Status == 0) {
    //                     $serviceEndTime = $patientEndTimeRaw
    //                         ? Carbon::createFromTimestamp($patientEndTimeRaw)->format('l d F Y - h:i A')
    //                         : 'N/A';
    //                     $patientEndTime = '<hr class="mt-2 mb-1"><h6><b>Service Performed By:</b></h6>'
    //                         . ucwords((string)($PatientInOutDetail->performingCC ?? ''))
    //                         . '<br>' . $empName
    //                         . '<br><b>Service End Time: </b>' . $serviceEndTime;
    //                 } else {
    //                     $patientEndTime = '<hr class="mt-2 mb-2"><h6><b>Service not yet completed</b></h6>';
    //                     $rights = $this->rights;
    //                     $endService = explode(',', $rights->patient_arrival_and_departure)[4] ?? 0;
    
    //                     if ($endService == 1) {
    //                         $patientEndTime .= $patientEndTimeRaw
    //                             ? Carbon::createFromTimestamp($patientEndTimeRaw)->format('d F Y - h:i A')
    //                             : '<span id="endService" class="text-underline" data-id="' . $ArrivalId . '"
    //                             data-servicemode-id="' . $ServiceModeId . '"
    //                             data-billingcc-id="' . $BillingCCId . '"  data-service-id="' . $ServiceId . '"
    //                             data-emp-id="' . $empId . '"  data-mr="' . $PatientInOutDetail->mr_code . '"
    //                             style="cursor:pointer; color: #fb3a3a;font-weight: 500;">Click here to end service</span>';
    //                     }
    //                 }
    
    //                 return $ShowServiceDetails
    //                     . '<hr class="mt-1 mb-1">'
    //                     . '<b>Patient Arrived At: </b>' . $patientArrivalTime
    //                     . $patientEndTime;
    //             }
    //             else {
    //                 $mrCode = $PatientInOutDetail->mr_code ?? '';
    //                 $Remarks = $PatientInOutDetail->Remarks ?? '';
    //                 $otherArrivals = PatientArrivalDeparture::where('mr_code', $mrCode)
    //                     ->where('service_mode_id', '!=', $ServiceModeId)
    //                     ->where('status', 1)
    //                     ->get();
    
    //                 if ($otherArrivals->isNotEmpty()) {
    //                     $otherServiceModeid = $otherArrivals->pluck('service_mode_id');
    //                     $ServiceModes = ServiceMode::whereIn('id', $otherServiceModeid)
    //                     ->pluck('name')
    //                     ->toArray();
    
    //                     $ServiceModeCount = count($ServiceModes);
    //                     if ($ServiceModeCount > 1) {
    //                         $last = array_pop($ServiceModes);
    //                         $modeList = implode(', ', $ServiceModes) . ' and ' . $last;
    //                     } else {
    //                         $modeList = $ServiceModes[0] ?? 'Unknown';
    //                     }
    //                     $serviceWord  = $ServiceModeCount > 1 ? 'services' : 'service';
    //                     $demonstrative = $ServiceModeCount > 1 ? 'those'   : 'that';
    
    //                     $message = " Please end {$demonstrative} {$serviceWord} first.";
    
    //                     return $ShowServiceDetails
    //                     . '<hr class="mt-1 mb-2">'
    //                     . '<h6>Patient already arrived in "<b>'
    //                     . e($modeList)
    //                     . '</b>'.$message;
    //                 } else {
    //                     $PatientStatusVal = $PatientInOutDetail->BookingPatientStatus ?? '';
    //                     $PatientStatus = ucwords((string)$PatientStatusVal);
    //                     $PatientPriorityVal = $PatientInOutDetail->BookingPatientPriority ?? '';
    //                     $PatientPriority = ucwords((string)$PatientPriorityVal);
    
    //                     return $ShowServiceDetails . '<hr class="mt-1 mb-2"><h6><b>Patient Not Yet Arrived</b></h6><hr class="mt-1 mb-2">
    //                         <a href="' . route('patient-inout', [
    //                             'mr' => encrypt($mrCode),
    //                             'billedamount' => encrypt($sellPrice),
    //                             'orgname' => encrypt($orgName),
    //                             'orgid' => encrypt($orgId),
    //                             'sitename' => encrypt($siteName),
    //                             'siteid' => encrypt($siteId),
    //                             'servicemode' => encrypt($ServiceMode),
    //                             'smid' => encrypt($ServiceModeId),
    //                             'empname' => encrypt($empName),
    //                             'eid' => encrypt($empId),
    //                             'service' => encrypt($Service),
    //                             'sid' => encrypt($ServiceId),
    //                             'billingcc' => encrypt($BillingCC),
    //                             'bcid' => encrypt($BillingCCId),
    //                             'patientstatusval' => encrypt($PatientStatusVal),
    //                             'patientstatus' => encrypt($PatientStatus),
    //                             'patientpriorityval' => encrypt($PatientPriorityVal),
    //                             'patientpriority' => encrypt($PatientPriority),
    //                             'locationname' => encrypt($locationName),
    //                             'locationid' => encrypt($locationId),
    //                             'schedulename' => encrypt($LocationSchedule),
    //                             'scheduleid' => encrypt($LocationScheduleId),
    //                             'scheduleStartTime' => encrypt($ScheduleStartTime),
    //                             'scheduleEndTime' => encrypt($ScheduleEndTime),
    //                             'pattern' => encrypt($Pattern),
    //                             'remarks' => encrypt($Remarks)
    //                         ]) . '">
    //                         <span class="text-underline" style="cursor:pointer; color: #fb3a3a;font-weight: 500;">
    //                             Confirm Patient Arrival
    //                         </a>';
    //                 }
    //             }
    //         })
    //         ->addColumn('action', function ($PatientInOutDetail) {
    //             $PatientInOutDetailId = $PatientInOutDetail->id ?? null;
    //             $logId = $PatientInOutDetail->logid ?? null;
    //             $status = $PatientInOutDetail->status ?? null;
    
    //             $Rights = $this->rights;
    //             $permissionArray = explode(',', $Rights->patient_arrival_and_departure ?? '');
    //             $edit = $permissionArray[2] ?? 0;
    
    //             $actionButtons = '';
    
    //             if ((int)$edit === 1 && $PatientInOutDetailId) {
    //                 $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-patientinout" data-patientinout-id="' . $PatientInOutDetailId . '">'
    //                     . '<i class="fa fa-edit"></i> Edit'
    //                     . '</button>';
    //             }
    
    //             if ($logId) {
    //                 $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="' . $logId . '">'
    //                     . '<i class="fa fa-eye"></i> View Logs'
    //                     . '</button>';
    //             }
    
    //             return ($status === 1 || $status === '1')
    //                 ? $actionButtons
    //                 : '<span class="font-weight-bold">Status must be Active or Patient must have arrived to perform any action.</span>';
    //         })
    //         ->editColumn('status', function ($PatientInOutDetail) {
    //             $rights = $this->rights;
    //             $updateStatus = explode(',', $rights->patient_arrival_and_departure)[3];
    //             return $updateStatus == 1
    //             ? ($PatientInOutDetail->status == '1'
    //                 ? '<span class="label label-success pio_status cursor-pointer" data-id="'.$PatientInOutDetail->id.'" data-status="'.$PatientInOutDetail->status.'">Active</span>'
    //                 : ($PatientInOutDetail->status == '0'
    //                     ? '<span class="label label-danger pio_status cursor-pointer" data-id="'.$PatientInOutDetail->id.'" data-status="'.$PatientInOutDetail->status.'">Inactive</span>'
    //                     : '<span class="label label-primary">N/A</span>'
    //                   )
    //               )
    //             : ($PatientInOutDetail->status == '1'
    //                 ? '<span class="label label-success">Active</span>'
    //                 : ($PatientInOutDetail->status == '0'
    //                     ? '<span class="label label-danger">Inactive</span>'
    //                     : '<span class="label label-primary">N/A</span>'
    //                   )
    //               );
    //         })->rawColumns(['action', 'status','serviceBooking','serviceDetails','id'])
    //         ->make(true);
        
    //     // Cache the result for 5 minutes
    //     // Cache::put($cacheKey, $result, 300);
        
    //     return $result;
    // }

    public function GetPatientArrivalDepartureDetails(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->patient_arrival_and_departure)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
    
        // Get session user for organization filtering
        $session = auth()->user();
        $sessionOrg = $session->org_id;
        
        // // Cache key for this specific query
        $cacheKey = "patient_arrival_departure_{$sessionOrg}_" . 
                   ($request->site_id ?? 'all') . "_" . 
                   ($request->mr_no ?? 'all') . "_" . 
                   ($request->date_filter ?? 'today');
        
        // Try to get from cache first (cache for 5 minutes)
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
            // Revert to original working query structure
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
            'pi.id',
            'pi.service_start_time as patientArrivalTime',
            'pi.service_end_time as patientEndTime',
            'pi.timestamp',
            'pi.last_updated',
            'pi.logid',
            'pi.user_id',
            'pi.status',
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

                // Check if site or MR is selected
        $siteSelected = $request->has('site_id') && $request->site_id != '' && $request->site_id != 'Loading...';
        $mrSelected = $request->has('mr_no') && $request->mr_no != '' && $request->mr_no != 'Loading...';
        
        // Apply date filtering if a specific date filter is chosen (regardless of site/MR selection)
        if ($request->has('date_filter') && $request->date_filter != '') {
            $dateFilter = $request->date_filter;
            $today = Carbon::today()->setTimezone('Asia/Karachi');
            
            
            switch ($dateFilter) {
                case 'today':
                    // Use current date in Asia/Karachi timezone (date only, no time)
                    $today = Carbon::now()->setTimezone('Asia/Karachi');
                    $startDate = $today->copy()->startOfDay()->timestamp;
                    $endDate = $today->copy()->endOfDay()->timestamp;
                    break;
                case 'yesterday':
                    // Use yesterday's date in Asia/Karachi timezone (date only, no time)
                    $yesterday = Carbon::now()->setTimezone('Asia/Karachi')->subDay();
                    $startDate = $yesterday->copy()->startOfDay()->timestamp;
                    $endDate = $yesterday->copy()->endOfDay()->timestamp;
                    break;
                case 'this_week':
                    // Use this week's start and end dates in Asia/Karachi timezone
                    $thisWeekStart = Carbon::now()->setTimezone('Asia/Karachi')->startOfWeek();
                    $thisWeekEnd = Carbon::now()->setTimezone('Asia/Karachi')->endOfWeek();
                    $startDate = $thisWeekStart->copy()->startOfDay()->timestamp;
                    $endDate = $thisWeekEnd->copy()->endOfDay()->timestamp;
                    break;
                case 'last_week':
                    // Use last week's start date in Asia/Karachi timezone (date only, no time)
                    $lastWeekStart = Carbon::now()->setTimezone('Asia/Karachi')->subWeek()->startOfWeek();
                    $startDate = $lastWeekStart->copy()->startOfDay()->timestamp;
                    break;
                case 'this_month':
                    // Use this month's start date in Asia/Karachi timezone (date only, no time)
                    $thisMonthStart = Carbon::now()->setTimezone('Asia/Karachi')->startOfMonth();
                    $startDate = $thisMonthStart->copy()->startOfDay()->timestamp;
                    break;
                case 'last_month':
                    // Use last month's start date in Asia/Karachi timezone (date only, no time)
                    $currentYear = Carbon::now()->setTimezone('Asia/Karachi')->year;
                    $currentMonth = Carbon::now()->setTimezone('Asia/Karachi')->month;
                    $lastMonthYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
                    $lastMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
                    
                    $lastMonthStart = Carbon::create($lastMonthYear, $lastMonth, 1, 0, 0, 0, 'Asia/Karachi');
                    $startDate = $lastMonthStart->copy()->startOfDay()->timestamp;

                    break;
                case 'this_year':
                    // Use this year's start date in Asia/Karachi timezone (date only, no time)
                    $currentYear = Carbon::now()->setTimezone('Asia/Karachi')->year;
                    $thisYearStart = Carbon::create($currentYear, 1, 1, 0, 0, 0, 'Asia/Karachi');
                    $startDate = $thisYearStart->copy()->startOfDay()->timestamp;
           
                    break;
                case 'last_year':
                    // Use last year's start date in Asia/Karachi timezone (date only, no time)
                    $currentYear = Carbon::now()->setTimezone('Asia/Karachi')->year;
                    $lastYear = $currentYear - 1;
                    $lastYearStart = Carbon::create($lastYear, 1, 1, 0, 0, 0, 'Asia/Karachi');
                    $startDate = $lastYearStart->copy()->startOfDay()->timestamp;
               
                    break;
                default:
                    // Default to today - Use current date in Asia/Karachi timezone (date only, no time)
                    $today = Carbon::now()->setTimezone('Asia/Karachi');
                    $startDate = $today->copy()->startOfDay()->timestamp;
        
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
            $today = Carbon::now()->setTimezone('Asia/Karachi');
            $startDate = $today->copy()->startOfDay()->timestamp;
            $endDate = $today->copy()->endOfDay()->timestamp;
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
    
        return DataTables::of($query)
            ->addColumn('id_raw', function ($PatientInOutDetail) {
                return $PatientInOutDetail->id;
            })
            ->editColumn('id', function ($PatientInOutDetail) {
                // Debug: Log the actual timestamps from the database and identify source table
                $sourceTable = 'Unknown';
                $sourceTimestamp = 'N/A';
                $rawTimestamp = 'N/A';
                
                if ($PatientInOutDetail->patientArrivalTime) {
                    $sourceTable = 'patient_inout';
                    $rawTimestamp = $PatientInOutDetail->patientArrivalTime;
                    $sourceTimestamp = Carbon::createFromTimestamp($PatientInOutDetail->patientArrivalTime)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s');
                } elseif ($PatientInOutDetail->service_starttime) {
                    $sourceTable = 'service_booking';
                    $rawTimestamp = $PatientInOutDetail->service_starttime;
                    $sourceTimestamp = Carbon::createFromTimestamp($PatientInOutDetail->service_starttime)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s');
                } elseif ($PatientInOutDetail->reqEffectiveTimestamp) {
                    $sourceTable = 'req_epi';
                    $rawTimestamp = $PatientInOutDetail->reqEffectiveTimestamp;
                    $sourceTimestamp = Carbon::createFromTimestamp($PatientInOutDetail->reqEffectiveTimestamp)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s');
                }
                
                
                $effectiveDate = $PatientInOutDetail->patientArrivalTime
                    ? Carbon::createFromTimestamp($PatientInOutDetail->patientArrivalTime)->format('l d F Y - h:i A')
                    : 'N/A';
    
                $timestamp = $PatientInOutDetail->timestamp
                    ? Carbon::createFromTimestamp($PatientInOutDetail->timestamp)->format('l d F Y - h:i A')
                    : 'N/A';
    
                $lastUpdated = $PatientInOutDetail->last_updated
                    ? Carbon::createFromTimestamp($PatientInOutDetail->last_updated)->format('l d F Y - h:i A')
                    : 'N/A';
    
                $createdByName = getUserNameById($PatientInOutDetail->user_id ?? null);
                $createdInfo = "
                    <b>Created By:</b> " . ucwords((string)$createdByName) . "<br>
                    <b>Effective Date&amp;Time:</b> " . $effectiveDate . "<br>
                    <b>RecordedAt:</b> " . $timestamp . "<br>
                    <b>LastUpdated:</b> " . $lastUpdated;
    
                $mrCode = $PatientInOutDetail->mr_code ?? 'N/A';
                $PatientName = ucwords((string)($PatientInOutDetail->patientName ?? ''));
                $Gender = ucwords((string)($PatientInOutDetail->genderName ?? ''));
                $DOB = $PatientInOutDetail->patientDOB
                    ? Carbon::createFromTimestamp($PatientInOutDetail->patientDOB)->format('d F Y')
                    : 'N/A';
    
                $cnic = !empty($PatientInOutDetail->patientCNIC) ? $PatientInOutDetail->patientCNIC : 'N/A';
                $Email = !empty($PatientInOutDetail->patientEmail) ? $PatientInOutDetail->patientEmail : 'N/A';
                $CellNo = $PatientInOutDetail->patientCellNo ?? 'N/A';
                $Address = ucwords((string)($PatientInOutDetail->patientAddress ?? ''));
                $Province = ucwords((string)($PatientInOutDetail->provinceName ?? ''));
                $District = ucwords((string)($PatientInOutDetail->districtName ?? ''));
                $Division = ucwords((string)($PatientInOutDetail->divisionName ?? ''));
    
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
            ->editColumn('serviceBooking', function ($PatientInOutDetail) {
                $session = auth()->user();
                $BookingId = $PatientInOutDetail->serviceBookingId ?? null;
    
                if ($BookingId) {
                    $StartTime = $PatientInOutDetail->service_starttime
                        ? Carbon::createFromTimestamp($PatientInOutDetail->service_starttime)->format('l d F Y - h:i A')
                        : 'N/A';
    
                    $EndTime = $PatientInOutDetail->service_endtime
                        ? Carbon::createFromTimestamp($PatientInOutDetail->service_endtime)->format('l d F Y - h:i A')
                        : 'N/A';
    
                    $DayTime = '<b>Start Time: </b> ' . $StartTime . ' <br> <b>End Time: </b> ' . $EndTime;
    
                    $empName = ucwords((string)($PatientInOutDetail->empName ?? ''));
                    $Remarks = !empty($PatientInOutDetail->sb_remarks) ? ucwords((string)$PatientInOutDetail->sb_remarks) : 'N/A';
    
                    $Location = ucwords((string)($PatientInOutDetail->locationName ?? ''));
                    $Pattern = ucwords((string)($PatientInOutDetail->schedule_pattern ?? ''));
                    $PatientStatus = ucwords((string)($PatientInOutDetail->BookingPatientStatus ?? ''));
                    $PatientPriority = ucwords((string)($PatientInOutDetail->BookingPatientPriority ?? ''));
                    $ServiceName = ucwords((string)($PatientInOutDetail->ServiceName ?? ''));
    
                    $orgName = '';
                    if (($session->org_id ?? '0') == '0') {
                        $orgName = ' / ' . ucwords((string)($PatientInOutDetail->orgName ?? ''));
                    }
    
                    $siteOrg = ucwords((string)($PatientInOutDetail->siteName ?? '')) . $orgName;
    
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
                    return '<h5><b>Unbooked</b></h5><hr class="mt-1 mb-2">';
                }
            })
            ->editColumn('serviceDetails', function ($PatientInOutDetail) {
                $ArrivalId = $PatientInOutDetail->id ?? null;
                // return $ArrivalId;

                $sellPrice = number_format((float)($PatientInOutDetail->sellPrice ?? 0), 2);
    
                $orgId = $PatientInOutDetail->orgId ?? '';
                $orgName = ucwords((string)($PatientInOutDetail->orgName ?? ''));
                
                $siteId = $PatientInOutDetail->siteId ?? '';
                $siteName = ucwords((string)($PatientInOutDetail->siteName ?? ''));
    
                $ServiceMode = ucwords((string)($PatientInOutDetail->serviceModeName ?? ''));
                $ServiceModeId = $PatientInOutDetail->serviceModeId ?? '';
    
                $locationName = ucwords((string)($PatientInOutDetail->locationName ?? ''));
                $locationId = $PatientInOutDetail->locationId ?? '';
    
                $LocationSchedule = ucwords((string)($PatientInOutDetail->LocationSchedule ?? ''));
                $LocationScheduleId = $PatientInOutDetail->LocationScheduleId ?? '';
    
                $ScheduleStartTime = $PatientInOutDetail->start_timestamp
                    ? Carbon::createFromTimestamp($PatientInOutDetail->start_timestamp)->format('l d F Y - h:i A')
                    : 'N/A';
                $ScheduleEndTime = $PatientInOutDetail->end_timestamp
                    ? Carbon::createFromTimestamp($PatientInOutDetail->end_timestamp)->format('l d F Y - h:i A')
                    : 'N/A';
    
                $Pattern = ucwords(($PatientInOutDetail->schedule_pattern ?? 'N/A'));
    
                $empName = ucwords((string)($PatientInOutDetail->empName ?? 'N/A'));
                $empId = $PatientInOutDetail->empId ?? '';
    
                $Service = ucwords((string)($PatientInOutDetail->ServiceName ?? ''));
                $ServiceId = $PatientInOutDetail->ServiceId ?? '';
    
                $BillingCC = ucwords((string)($PatientInOutDetail->billingCC ?? ''));
                $BillingCCId = $PatientInOutDetail->billingCCId ?? '';
    
                $ShowServiceDetails = $ServiceMode . '<br>' . $Service . '<br> <b>Specialty: </b>' . $BillingCC . '<br><b>Responsible Person:</b> ' . $empName;
    
                if ($ArrivalId) {
                    $patientArrivalTime = $PatientInOutDetail->patientArrivalTime
                        ? Carbon::createFromTimestamp($PatientInOutDetail->patientArrivalTime)->format('l d F Y - h:i A')
                        : 'N/A';
    
                    $patientEndTimeRaw = $PatientInOutDetail->patientEndTime ?? null;
                    $Status = $PatientInOutDetail->status ?? null;
    
                    if ($Status == 0) {
                        $serviceEndTime = $patientEndTimeRaw
                            ? Carbon::createFromTimestamp($patientEndTimeRaw)->format('l d F Y - h:i A')
                            : 'N/A';
                        $patientEndTime = '<hr class="mt-2 mb-1"><h6><b>Service Performed By:</b></h6>'
                            . ucwords((string)($PatientInOutDetail->performingCC ?? ''))
                            . '<br>' . $empName
                            . '<br><b>Service End Time: </b>' . $serviceEndTime;
                    } else {
                        $patientEndTime = '<hr class="mt-2 mb-2"><h6><b>Service not yet completed</b></h6>';
                        $rights = $this->rights;
                        $endService = explode(',', $rights->patient_arrival_and_departure)[4] ?? 0;
    
                        if ($endService == 1) {
                            $patientEndTime .= $patientEndTimeRaw
                                ? Carbon::createFromTimestamp($patientEndTimeRaw)->format('d F Y - h:i A')
                                : '<span id="endService" class="text-underline" data-id="' . $ArrivalId . '"
                                data-servicemode-id="' . $ServiceModeId . '"
                                data-billingcc-id="' . $BillingCCId . '"  data-service-id="' . $ServiceId . '"
                                data-emp-id="' . $empId . '"  data-mr="' . $PatientInOutDetail->mr_code . '"
                                style="cursor:pointer; color: #fb3a3a;font-weight: 500;">Click here to end service</span>';
                        }
                    }
    
                    return $ShowServiceDetails
                        . '<hr class="mt-1 mb-1">'
                        . '<b>Patient Arrived At: </b>' . $patientArrivalTime
                        . $patientEndTime;
                }
                else {
                    $mrCode = $PatientInOutDetail->mr_code ?? '';
                    $Remarks = $PatientInOutDetail->Remarks ?? '';
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
                            $last = array_pop($ServiceModes);
                            $modeList = implode(', ', $ServiceModes) . ' and ' . $last;
                        } else {
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
                        $PatientStatusVal = $PatientInOutDetail->BookingPatientStatus ?? '';
                        $PatientStatus = ucwords((string)$PatientStatusVal);
                        $PatientPriorityVal = $PatientInOutDetail->BookingPatientPriority ?? '';
                        $PatientPriority = ucwords((string)$PatientPriorityVal);
    
                        return $ShowServiceDetails . '<hr class="mt-1 mb-2"><h6><b>Patient Not Yet Arrived</b></h6><hr class="mt-1 mb-2">
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
                            ]) . '">
                            <span class="text-underline" style="cursor:pointer; color: #fb3a3a;font-weight: 500;">
                                Confirm Patient Arrival
                            </a>';
                    }
                }
            })
            ->addColumn('action', function ($PatientInOutDetail) {
                $PatientInOutDetailId = $PatientInOutDetail->id ?? null;
                $logId = $PatientInOutDetail->logid ?? null;
                $status = $PatientInOutDetail->status ?? null;
    
                $Rights = $this->rights;
                $permissionArray = explode(',', $Rights->patient_arrival_and_departure ?? '');
                $edit = $permissionArray[2] ?? 0;
    
                $actionButtons = '';
    
                if ((int)$edit === 1 && $PatientInOutDetailId) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-patientinout" data-patientinout-id="' . $PatientInOutDetailId . '">'
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
                    : '<span class="font-weight-bold">Status must be Active or Patient must have arrived to perform any action.</span>';
            })
            ->editColumn('status', function ($PatientInOutDetail) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->patient_arrival_and_departure)[3];
                return $updateStatus == 1
                ? ($PatientInOutDetail->status == '1'
                    ? '<span class="label label-success pio_status cursor-pointer" data-id="'.$PatientInOutDetail->id.'" data-status="'.$PatientInOutDetail->status.'">Active</span>'
                    : ($PatientInOutDetail->status == '0'
                        ? '<span class="label label-danger pio_status cursor-pointer" data-id="'.$PatientInOutDetail->id.'" data-status="'.$PatientInOutDetail->status.'">Inactive</span>'
                        : '<span class="label label-primary">N/A</span>'
                      )
                  )
                : ($PatientInOutDetail->status == '1'
                    ? '<span class="label label-success">Active</span>'
                    : ($PatientInOutDetail->status == '0'
                        ? '<span class="label label-danger">Inactive</span>'
                        : '<span class="label label-primary">N/A</span>'
                      )
                  );
            })->rawColumns(['action', 'status','serviceBooking','serviceDetails','id'])
            ->make(true);
        
        
        
        // Cache the result for 5 minutes
        Cache::put($cacheKey, $result, 300);
        
        return $result;
    }

    public function UpdatePatientArrivalDepartureStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->patient_arrival_and_departure)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $PatientArrivalDepartureID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $PatientArrivalDeparture = PatientArrivalDeparture::find($PatientArrivalDepartureID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $PatientArrivalDeparture->service_start_time = $CurrentTimestamp;
            $PatientArrivalDeparture->service_end_time = null;
        }
        else{
            $UpdateStatus = 0;
            $PatientArrivalDeparture->service_end_time = $CurrentTimestamp;
            $statusLog = 'Inactive';

        }
        $PatientArrivalDeparture->status = $UpdateStatus;
        $PatientArrivalDeparture->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'patient',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PatientArrivalDepartureLog = PatientArrivalDeparture::where('id', $PatientArrivalDepartureID)->first();
        $logIds = $PatientArrivalDepartureLog->logid ? explode(',', $PatientArrivalDepartureLog->logid) : [];
        $logIds[] = $logs->id;
        $PatientArrivalDepartureLog->logid = implode(',', $logIds);
        $PatientArrivalDepartureLog->save();

        $PatientArrivalDeparture->save();
        return response()->json(['success' => true, 200]);
    }

    public function EndService(Request $request)
    {
        $rights = $this->rights;
        $endService = explode(',', $rights->patient_arrival_and_departure)[4];
        if($endService == 0)
        {
            abort(403, 'Forbidden');
        }
        $validator = Validator::make($request->all(), [
            'pio_serviceEnd' => 'required',
            'pio_id' => 'required',
            // 'pio_servicemode_id' => 'required',
            // 'pio_billingcc_id' => 'required',
            // 'pio_emp_id' => 'required',
            // 'pio_mr_code' => 'required',
            // 'pio_service_id' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => 'This Field is Required.']);
        }

        $ID = $request->input('pio_id');
        // $serviceModeID = $request->input('pio_servicemode_id');
        $ServiceMode = $request->input('pio_servicemode_id');
        $BillingCC = $request->input('pio_billingcc_id');
        $Physician = $request->input('pio_emp_id');
        $MR = $request->input('pio_mr_code');
        $Service = $request->input('pio_service_id');

        $ServiceEnd = $request->input('pio_serviceEnd');
        $EndDateTime = $request->input('pio_serviceEnd');
        $EndDateTime = Carbon::createFromFormat('l d F Y - h:i A', $EndDateTime)->timestamp;

        $CheckStatus = Carbon::createFromTimestamp($EndDateTime)->setTimezone('Asia/Karachi');
        $CheckStatus->subMinute(1);
        if ($CheckStatus->isPast()) {
            $status = 0;
        } else {
            $status = 1;
        }
        $CurrentTimestamp = $this->currentDatetime;
        $PatientArrivalDeparture = PatientArrivalDeparture::find($ID);

        $PatientArrivalDeparture->service_end_time = $EndDateTime;
        $PatientArrivalDeparture->status = $status;
        $PatientArrivalDeparture->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;


        $sameArrivals = PatientArrivalDeparture::where('service_mode_id', $PatientArrivalDeparture->service_mode_id)
        ->where('mr_code', $PatientArrivalDeparture->mr_code)
        ->where('status', 1)
        ->get();

        if ($sameArrivals->count() > 1) {
            foreach ($sameArrivals as $arrival) {
                $arrival->service_end_time = $EndDateTime;
                $arrival->status = $status;
                $arrival->last_updated = $CurrentTimestamp;
                // $arrival->save();
                if($arrival->save())
                {
                    $reqEpiQuery = RequisitionForEPI::where('mr_code', $MR)
                    ->where('service_id', $Service)
                    ->where('service_mode_id', $ServiceMode)
                    ->where('billing_cc', $BillingCC)
                    ->where('emp_id', $Physician)
                    ->where('status', 1);

                    $matchingReqs = $reqEpiQuery->get();

                    if ($matchingReqs->isNotEmpty()) {
                        $log = Logs::create([
                            'module' => 'patient_medical_record',
                            'content' => "Status set to Inactive by '{$sessionName}'",
                            'event' => 'update',
                            'timestamp' => $CurrentTimestamp,
                        ]);

                        foreach ($matchingReqs as $req) {
                            $existingLogIds = $req->logid ? explode(',', $req->logid) : [];
                            $existingLogIds[] = $log->id;
                            $req->status = 0;
                            $req->logid = implode(',', $existingLogIds);
                            $req->save();
                        }
                    }
                }
                $logs = Logs::create([
                    'module' => 'patient',
                    'content' => "End Date&Time ('{$ServiceEnd}') added by '{$sessionName}'",
                    'event' => 'update',
                    'timestamp' => $CurrentTimestamp,
                ]);

                $logIds = $arrival->logid ? explode(',', $arrival->logid) : [];
                $logIds[] = $logs->id;
                $arrival->logid = implode(',', $logIds);
                $arrival->save();
            }
        }

        else {
            $PatientArrivalDeparture->service_end_time = $EndDateTime;
            $PatientArrivalDeparture->status = $status;
            $PatientArrivalDeparture->last_updated = $CurrentTimestamp;
            $PatientArrivalDeparture->save();

            $logs = Logs::create([
                'module' => 'patient',
                'content' => "End Date&Time ('{$ServiceEnd}') added by '{$sessionName}'",
                'event' => 'update',
                'timestamp' => $CurrentTimestamp,
            ]);

            $logIds = $PatientArrivalDeparture->logid ? explode(',', $PatientArrivalDeparture->logid) : [];
            $logIds[] = $logs->id;
            $PatientArrivalDeparture->logid = implode(',', $logIds);
            if($PatientArrivalDeparture->save())
            {
                $reqEpiQuery = RequisitionForEPI::where('mr_code', $MR)
                ->where('service_id', $Service)
                ->where('service_mode_id', $ServiceMode)
                ->where('billing_cc', $BillingCC)
                ->where('emp_id', $Physician)
                ->where('status', 1);

                $matchingReqs = $reqEpiQuery->get();

                if ($matchingReqs->isNotEmpty()) {
                    $log = Logs::create([
                        'module' => 'patient_medical_record',
                        'content' => "Status set to Inactive by '{$sessionName}'",
                        'event' => 'update',
                        'timestamp' => $CurrentTimestamp,
                    ]);

                    foreach ($matchingReqs as $req) {
                        $existingLogIds = $req->logid ? explode(',', $req->logid) : [];
                        $existingLogIds[] = $log->id;
                        $req->status = 0;
                        $req->logid = implode(',', $existingLogIds);
                        $req->save();
                    }
                }
            }
        }
        return response()->json(['success' => 'Service End Successfully.']);

        // $logs = Logs::create([
        //     'module' => 'patient',
        //     'content' => "End Date&Time ('{$ServiceEnd}') added  by '{$sessionName}'",
        //     'event' => 'update',
        //     'timestamp' => $CurrentTimestamp,
        // ]);
        // $PatientArrivalDepartureLog = PatientArrivalDeparture::where('id', $ID)->first();
        // $logIds = $PatientArrivalDepartureLog->logid ? explode(',', $PatientArrivalDepartureLog->logid) : [];
        // $logIds[] = $logs->id;
        // $PatientArrivalDepartureLog->logid = implode(',', $logIds);
        // $PatientArrivalDepartureLog->save();

        // $PatientArrivalDeparture->save();
        // return response()->json(['success' => 'Service End Succesfully.']);
    }

    public function UpdatePatientInOutModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->patient_arrival_and_departure)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PatientArrivalDeparture = PatientArrivalDeparture::select(
            'patient_inout.*',
            // 'sb.org_id',
            // 'sb.site_id',
            // 'sb.service_location_id',
            // 'sb.schedule_id',
            // 'sb.emp_id',
            // 'sb.mr_code',
            // 'sb.patient_status',
            // 'sb.patient_priority',
            // 'sb.remarks',
            'sl.name as locationName',
            'sls.name as locationSchedule',
            'sls.start_timestamp as schedulestartTime',
            'sls.end_timestamp as scheduleendTime',
            'sls.schedule_pattern as schedulePattern',
            'e.name as empName',
            's.name as serviceName',
            'sm.name as servicemodeName',
            'cc.name as CCName',
            'o.organization as orgName',
            'os.name as siteName',
            'asr.sell_price as billedAmount'
        )
        ->join('organization as o', 'o.id', '=', 'patient_inout.org_id')
        ->join('org_site as os', 'os.id', '=', 'patient_inout.site_id')
        ->join('service_location as sl', 'sl.id', '=', 'patient_inout.service_location_id')
        ->join('service_location_scheduling as sls', 'sls.id', '=', 'patient_inout.schedule_id')
        ->join('employee as e', 'e.id', '=', 'patient_inout.emp_id')
        ->join('services as s', 's.id', '=', 'patient_inout.service_id')
        ->join('service_mode as sm', 'sm.id', '=', 'patient_inout.service_mode_id')
        ->join('costcenter as cc', 'cc.id', '=', 'patient_inout.billing_cc')
        ->leftJoin('activated_service_rate as asr', 'asr.service_mode_id', '=', 'sm.id')
        ->where('patient_inout.id', '=', $id)
        ->first();

        $service_start_time = $PatientArrivalDeparture->service_start_time;
        $service_start_time = Carbon::createFromTimestamp($service_start_time);
        $service_start_time = $service_start_time->format('l d F Y - h:i A');

        $service_end_time = $PatientArrivalDeparture->service_end_time;
        if($service_end_time != null)
        {
            $service_end_time = Carbon::createFromTimestamp($service_end_time);
            $service_end_time = $service_end_time->format('l d F Y - h:i A');
        }
        else
        {
            $service_end_time = null;
        }
        $billedAmount = $ServiceRates->billedAmount ?? 0;

        $data = [
            'id' => $id,
            'serviceName' => ucwords($PatientArrivalDeparture->serviceName),
            'serviceID' => $PatientArrivalDeparture->service_id,
            'billedAmount' => $billedAmount,
            'service_modeName' => $PatientArrivalDeparture->servicemodeName,
            'serviceModeID' => $PatientArrivalDeparture->service_mode_id,
            'CCName' => ucwords($PatientArrivalDeparture->CCName),
            'CCID' => $PatientArrivalDeparture->billing_cc,
            // 'Amount' => ucwords($PatientArrivalDeparture->amount),
            // 'paymentMode' => ucwords($PatientArrivalDeparture->payment_mode),
            'StartTime' => $service_start_time,
            'EndTime' => $service_end_time,
            'siteId' => $PatientArrivalDeparture->site_id,
            'siteName' => $PatientArrivalDeparture->siteName,
            'orgId' => $PatientArrivalDeparture->org_id,
            'orgName' => $PatientArrivalDeparture->orgName,
            'patientPriority' => ucwords($PatientArrivalDeparture->patient_priority),
            'patientStatus' => ucwords($PatientArrivalDeparture->patient_status),
            'locationName' => ucwords($PatientArrivalDeparture->locationName),
            'locationSchedule' => ucwords($PatientArrivalDeparture->locationSchedule),
            'empName' => ucwords($PatientArrivalDeparture->empName),
            'start_timestamp' => ($PatientArrivalDeparture->schedulestartTime),
            'end_timestamp' => ($PatientArrivalDeparture->scheduleendTime),
            'schedulePattern' => ($PatientArrivalDeparture->schedulePattern),
            'mrNo' => ($PatientArrivalDeparture->mr_code),
        ];
        return response()->json($data);
    }

    public function UpdatePatientInOut(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->patient_arrival_and_departure)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $PatientArrivalDeparture = PatientArrivalDeparture::findOrFail($id);
        $PatientArrivalDeparture->service_id = $request->input('u_pio_service');
        $PatientArrivalDeparture->service_mode_id = $request->input('u_pio_serviceMode');
        $PatientArrivalDeparture->billing_cc = $request->input('u_pio_billingCC');
        // $PatientArrivalDeparture->amount = $request->input('u_pio_amount');
        // $PatientArrivalDeparture->payment_mode = $request->input('u_pio_payMode');

        $PatientArrivalDeparture->service_start_time = $request->input('u_pio_serviceStart');
        $PatientArrivalDeparture->service_end_time = $request->input('u_pio_serviceEnd');

        $startTime = $request->input('u_pio_serviceStart');
        $startTime = Carbon::createFromFormat('l d F Y - h:i A', $startTime)->timestamp;

        $endTime = $request->input('u_pio_serviceEnd');
        if($endTime == '')
        {
            $endTime = null;
        }
        else
        {
            $endTime = Carbon::createFromFormat('l d F Y - h:i A', $endTime)->timestamp;
            $EndTime = Carbon::createFromTimestamp($endTime)->setTimezone('Asia/Karachi');
            // $EndTime->subMinute(1);
            if ($EndTime->isPast()) {
                $status = 1; //Active
            } else {
                 $status = 0; //Inactive
            }
            $PatientArrivalDeparture->status = $status;

        }


        $PatientArrivalDeparture->service_start_time = $startTime;
        $PatientArrivalDeparture->service_end_time = $endTime;

        $PatientArrivalDeparture->last_updated = $this->currentDatetime;
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $PatientArrivalDeparture->save();

        if (empty($PatientArrivalDeparture->id)) {
            return response()->json(['error' => 'Failed to update Patient Arrival & Departure Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'patient',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $PatientArrivalDepartureLog = PatientArrivalDeparture::where('id', $PatientArrivalDeparture->id)->first();
        $logIds = $PatientArrivalDepartureLog->logid ? explode(',', $PatientArrivalDepartureLog->logid) : [];
        $logIds[] = $logs->id;
        $PatientArrivalDepartureLog->logid = implode(',', $logIds);
        $PatientArrivalDepartureLog->save();
        return response()->json(['success' => ' Patient Arrival & Departure Details updated successfully']);
    }

    public function OutsourcedServices()
    {
        $colName = 'outsourced_services';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        // $Provinces = Province::where('status', 1)->get();
        // $ServiceBookings = ServiceBooking::select('remarks','id')->where('status', 1)->get();
        // $UserorgId = $user->org_id;
        // $orgCode = Organization::where('id', $UserorgId)->value('code');
        $Organizations = Organization::select('id', 'organization')->where('status', 1)->get();
        $Patients = PatientRegistration::select('mr_code','name','cell_no')->where('status', 1)->orderBy('id', 'desc')->get();

        return view('dashboard.outsourced-services', compact('user','Organizations','Patients'));
    }

}
