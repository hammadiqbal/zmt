<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use PHPUnit\Framework\Constraint\IsEmpty;
use Yajra\DataTables\Facades\DataTables;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use App\Models\Service;
use App\Models\ICDCoding;
use App\Models\PatientRegistration;
use App\Models\VitalSign;
use App\Models\Organization;
use App\Models\MedicalDiagnosis;
use App\Models\AllergiesHistory;
use App\Models\ImmunizationHistory;
use App\Models\DrugHistory;
use App\Models\PastHistory;
use App\Models\ObstericHistory;
use App\Models\SocialHistory;
use App\Models\VisitBasedDetails;
use App\Models\RequisitionForEPI;
use App\Models\MedicationRoutes;
use App\Models\MedicationFrequency;
use App\Models\InventoryGeneric;
use App\Models\RequisitionForMedicationConsumption;
use App\Models\ProcedureCoding;
use App\Models\PatientArrivalDeparture;
use App\Models\PatientAttachments;
use App\Models\ServiceLocation;
use App\Models\Site;
use App\Models\ServiceActivation;
use App\Models\InvestigationTracking;
use App\Models\ServiceBooking;
use App\Http\Requests\ICDCodingRequest;
use App\Http\Requests\VitalSignRequest;
use App\Http\Requests\RequisitionEPIRequest;
use App\Http\Requests\RequisitionMedicationConsumptionRequest;
use App\Http\Requests\PatientAttachmentRequest;
use App\Http\Requests\SampleTrackingRequest;
use App\Http\Requests\UploadReportRequest;

class PatientMedicalRecord extends Controller
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

    public function ICDCoding()
    {
        $colName = 'medical_coding';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        return view('dashboard.icd_code', compact('user'));
    }

    public function GetDiagnosisICDCodes(Request $request)
    {
        $query = ICDCoding::where('icd_code.status', 1)
            ->where('icd_code.type', 'd');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('icd_code.code', 'like', "%$search%")
                ->orWhere('icd_code.description', 'like', "%$search%");
            });
        }
        $ICDCodes = $query->paginate(50);
        return response()->json($ICDCodes);
    }
    // public function GetProcedureICDCodes(Request $request)
    // {
    //     $query = ICDCoding::where('icd_code.status', 1)
    //     ->where('icd_code.type', 's');
    //     if ($request->has('id')) {
    //         $query->where('icd_code.id', $request->id);
    //     }

    //     if ($request->has('search')) {
    //         $query->where(function ($q) use ($request) {
    //             $q->where('icd_code.type', 's')
    //                 ->where(function ($subQuery) use ($request) {
    //                     $subQuery->where('icd_code.code', 'like', '%' . $request->search . '%')
    //                         ->orWhere('icd_code.description', 'like', '%' . $request->search . '%');
    //                 });
    //         });
    //     }
    //     $ICDCodes = $query->paginate(20);

    //     return response()->json($ICDCodes);
    // }

    public function GetProcedureICDCodes(Request $request)
    {
        $service = DB::table('services')
        ->join('service_group', 'services.group_id', '=', 'service_group.id')
        ->join('service_type', 'service_group.type_id', '=', 'service_type.id')
        ->where('services.id', $request->serviceId)
        ->select('service_type.code as serviceTypeCode')
        ->first();
        $icdTypeCondition = null;

        // if ($service) {
        //     if ($service->serviceTypeCode === 'e') {
        //         $icdTypeCondition = 's'; // Only Symptoms
        //     } elseif ($service->serviceTypeCode === 'p') {
        //         $icdTypeCondition = 'p'; // Only Procedures
        //     }
        // }

        if ($service) {
            if ($service->serviceTypeCode === 'e') {
                $icdTypeCondition = 's'; // Only Symptoms
            } elseif ($service->serviceTypeCode === 'p') {
                $icdTypeCondition = 'p'; // Only Procedures

                $mappedICDIds = DB::table('procedure_coding')
                    ->where('service_id', $request->serviceId)
                    ->pluck('icd_id')
                    ->first();

                if ($mappedICDIds) {
                    $mappedICDIds = explode(',', $mappedICDIds);
                } else {
                    $mappedICDIds = [];
                }
            }
        }

        $query = ICDCoding::where('icd_code.status', 1);

        if ($icdTypeCondition) {
            $query->where('icd_code.type', $icdTypeCondition);
        }

        if ($service->serviceTypeCode === 'p' && !empty($mappedICDIds)) {
            $query->whereIn('icd_code.id', $mappedICDIds);
        }

        if ($request->has('id')) {
            $query->where('icd_code.id', $request->id);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('icd_code.code', 'like', '%' . $request->search . '%')
                    ->orWhere('icd_code.description', 'like', '%' . $request->search . '%');
            });
        }

        $ICDCodes = $query->paginate(20);
        return response()->json($ICDCodes);
    }


    public function AddICDCoding(ICDCodingRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->medical_coding)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $Desc = trim($request->input('icd_desc'));
        $Code = trim($request->input('icd_code'));
        $CodeType = trim($request->input('icd_codetype'));
        $Edt = $request->input('icd_edt');
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

        $ICDExists = ICDCoding::where('code', $Code)
        ->exists();
        if ($ICDExists) {
            return response()->json(['info' => 'Medical Code already exists.']);
        }
        else
        {
            $ICDCode = new ICDCoding();
            $ICDCode->description = $Desc;
            $ICDCode->code = $Code;
            $ICDCode->type = $CodeType;
            $ICDCode->status = $status;
            $ICDCode->user_id = $sessionId;
            $ICDCode->last_updated = $last_updated;
            $ICDCode->timestamp = $timestamp;
            $ICDCode->effective_timestamp = $Edt;
            $ICDCode->save();

            if (empty($ICDCode->id)) {
                return response()->json(['error' => 'Failed to create Medical Code.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Medical Code '{$Code}' has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ICDCode->logid = $logs->id;
            $ICDCode->save();
            return response()->json(['success' => 'Medical Code created successfully']);
        }

    }

    public function GetICDCodeData(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->medical_coding)[1];
        if ($view == 0) {
            abort(403, 'Forbidden');
        }

        $ICDCodes = ICDCoding::select('icd_code.*')
        ->orderBy('icd_code.id', 'desc');
        // ->get();

        // return DataTables::of($ICDCodes)
        return DataTables::eloquent($ICDCodes)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('icd_code.code', 'like', "%{$search}%")
                          ->orWhere('icd_code.description', 'like', "%{$search}%")
                          ->orWhere(function ($sub) use ($search) {
                              $search = strtolower($search);
                              $mappedType = match(true) {
                                  str_contains($search, 'procedure') => 'p',
                                  str_contains($search, 'symptom') => 's',
                                  str_contains($search, 'diagnosis') => 'd',
                                  default => null,
                              };
                              if ($mappedType) {
                                  $sub->orWhere('icd_code.type', '=', $mappedType);
                              }
                          });
                    });

                }
            })
            ->addColumn('id_raw', function ($ICDCode) {
                return $ICDCode->id;
            })
            ->editColumn('id', function ($ICDCode) {
                $effectiveDate = Carbon::createFromTimestamp($ICDCode->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($ICDCode->timestamp)->format('l d F Y - h:i A');
                $lastUpdated = Carbon::createFromTimestamp($ICDCode->last_updated)->format('l d F Y - h:i A');
                $createdByName = getUserNameById($ICDCode->user_id);

                $createdInfo = "
                    <b>Created By:</b> " . ucwords($createdByName) . "  <br>
                    <b>Effective Date&amp;Time:</b> " . $effectiveDate . " <br>
                    <b>RecordedAt:</b> " . $timestamp ." <br>
                    <b>LastUpdated:</b> " . $lastUpdated;

                return $ICDCode->code
                    . '<hr class="mt-1 mb-2">'
                    . '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body" data-toggle="popover" data-placement="right" data-html="true" data-content="'. $createdInfo .'">'
                    . '<i class="fa fa-toggle-right"></i> View Details'
                    . '</span>';
            })
            ->editColumn('desc', function ($ICDCode) {
                return ucwords($ICDCode->description ?? '');
            })
            ->editColumn('codetype', function ($ICDCode) {
                $Type = $ICDCode->type;
                $codeType = match($Type) {
                    'p' => 'Procedure',
                    's' => 'Symptom',
                    'd' => 'Diagnosis',
                    default => 'Unknown',
                };
                return $codeType;
            })
            ->addColumn('action', function ($ICDCode) {
                $ICDCodeId = $ICDCode->id;
                $logId = $ICDCode->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->medical_coding)[2];
                $actionButtons = '';

                if ($edit == 1) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-icdcode" data-icd-code="'.$ICDCodeId.'">'
                    . '<i class="fa fa-edit"></i> Edit'
                    . '</button>';
                }

                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';

                return $ICDCode->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($ICDCode) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->medical_coding)[3];
                return $updateStatus == 1
                    ? ($ICDCode->status
                        ? '<span class="label label-success icdcode_status cursor-pointer" data-id="'.$ICDCode->id.'" data-status="'.$ICDCode->status.'">Active</span>'
                        : '<span class="label label-danger icdcode_status cursor-pointer" data-id="'.$ICDCode->id.'" data-status="'.$ICDCode->status.'">Inactive</span>'
                    )
                    : ($ICDCode->status
                        ? '<span class="label label-success">Active</span>'
                        : '<span class="label label-danger">Inactive</span>'
                    );
            })
            ->rawColumns(['id', 'action', 'status'])
            ->make(true);
    }


    public function UpdateICDCodeStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->medical_coding)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ICDCodeID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ICDCode = ICDCoding::find($ICDCodeID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ICDCode->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ICDCode->effective_timestamp = 0;

        }
        $ICDCode->status = $UpdateStatus;
        $ICDCode->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ICDCodeLog = ICDCoding::where('id', $ICDCodeID)->first();
        $logIds = $ICDCodeLog->logid ? explode(',', $ICDCodeLog->logid) : [];
        $logIds[] = $logs->id;
        $ICDCodeLog->logid = implode(',', $logIds);
        $ICDCodeLog->save();

        $ICDCode->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateICDCodeModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->medical_coding)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ICDCodes = ICDCoding::select('icd_code.*')
        ->where('icd_code.id', $id)
        ->first();

        $Description = ucwords($ICDCodes->description);
        $Code = ucwords($ICDCodes->code);
        $Type = ($ICDCodes->type);
        $effective_timestamp = $ICDCodes->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'description' => $Description,
            'code' => $Code,
            'type' => $Type,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateICDCode(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->medical_coding)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ICDCodes = ICDCoding::findOrFail($id);

        $ICDCodes->description = $request->input('uicd_desc');
        $ICDCodes->code = $request->input('uicd_code');
        $codeType = $request->input('uicd_codetype');
        $ICDCodes->type = $request->input('uicd_codetype');

        $effective_date = $request->input('uicd_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $ICDCodes->effective_timestamp = $effective_date;
        $ICDCodes->last_updated = $this->currentDatetime;
        $ICDCodes->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ICDCodes->save();

        if (empty($ICDCodes->id)) {
            return response()->json(['error' => 'Failed to update ICD Code. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ICDCodesLog = ICDCoding::where('id', $ICDCodes->id)->first();
        $logIds = $ICDCodesLog->logid ? explode(',', $ICDCodesLog->logid) : [];
        $logIds[] = $logs->id;
        $ICDCodesLog->logid = implode(',', $logIds);
        $ICDCodesLog->save();
        return response()->json(['success' => 'Medical Code updated successfully']);
    }

    public function VitalSigns()
    {
        $colName = 'vital_signs';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        $Patients = PatientRegistration::select('mr_code','name','cell_no')->where('status', 1)->orderBy('id', 'desc')->get();

        return view('dashboard.vital_sign', compact('user','orgCode','Patients'));
    }

    public function PatientRecords($mr, Request $request)
    {
        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $EmployeeStatus = $user->is_employee;
        $UserempId = $user->emp_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        $mr = strpos($mr, '-') === false ? ($orgCode ? $orgCode : 'ZMTP') . '-' . $mr : $mr;

        $selectedService = $request->input('serviceId');
        // dd($selectedService);

        $services = PatientArrivalDeparture::select(
            'services.id as serviceId',
            'services.name as serviceName'
        )
        ->join('services', 'services.id', '=', 'patient_inout.service_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->where('patient_inout.mr_code', $mr)
        ->where('patient_inout.status', 1)
        ->where('service_type.code', '!=' , 'i')
        ->groupBy('services.id', 'services.name')
        ->get();
        if ($services->count() > 1 && !$selectedService) {
            return response()->json(['info' => 200,'services' => $services]);
        }
        // if ($services->count() > 1 && !is_null($selectedService)) {
        //     return response()->json(['info' => 200, 'services' => $services]);
        // }


        // dd($services->count());

        $EncounterProcedurePatientDetails = PatientRegistration::select(
            'patient.name as patientName',
            'gender.name as genderName',
            'patient.dob as patientDOB',
            'billingCC.name as billingCCName',
            'billingCC.id as billingCCId',
            'employee.name as empName',
            'employee.id as empID',
            'service_mode.name as serviceMode',
            'service_type.name as serviceType',
            'service_type.code as serviceTypeCode',
            'service_group.name as serviceGroup',
            'services.name as serviceName',
            'services.id as serviceId',
            'service_mode.id as serviceModeId',
            'org_site.name as siteName',
            'org_site.id as siteId',
            'patient_inout.status as patientInOutStatus',
            'patient_inout.remarks as patientInOutRemarks',
        )
        ->leftJoin('patient_inout', 'patient_inout.mr_code', '=', 'patient.mr_code')
        ->join('gender', 'gender.id', '=', 'patient.gender_id')
        ->leftJoin('costcenter as billingCC', 'billingCC.id', '=', 'patient_inout.billing_cc')
        ->leftJoin('employee', 'employee.id', '=', 'patient_inout.emp_id')
        ->leftJoin('org_site', 'org_site.id', '=', 'patient_inout.site_id')
        ->leftJoin('service_mode', 'service_mode.id', '=', 'patient_inout.service_mode_id')
        ->leftJoin('services', 'services.id', '=', 'patient_inout.service_id')
        ->leftJoin('service_group', 'service_group.id', '=', 'services.group_id')
        ->leftJoin('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->where('patient.status', 1)
        ->when(
            PatientArrivalDeparture::where([
                ['mr_code', '=', $mr],
                ['status', '=', 1]
            ])->exists(),
            function ($query) {
                $query->where('patient_inout.status', 1);
            }
        )
        ->where('patient.mr_code', $mr);

        if ($selectedService) {
            $EncounterProcedurePatientDetails = $EncounterProcedurePatientDetails->where('patient_inout.service_id', $selectedService);
        }
        $EncounterProcedurePatientDetails = $EncounterProcedurePatientDetails->first();
        // ->first();

        if ($EncounterProcedurePatientDetails) {

            $dob = Carbon::createFromTimestamp($EncounterProcedurePatientDetails->patientDOB);
            $now = Carbon::now();
            $diff = $dob->diff($now);

            $years = $diff->y;
            $months = $diff->m;
            $days = $diff->d;

            // Format the age string
            $ageString = "";
            if ($years > 0) {
                $ageString .= $years . " " . ($years == 1 ? "year" : "years");
            }
            if ($months > 0) {
                $ageString .= " " . $months . " " . ($months == 1 ? "month" : "months");
            }
            if ($days > 0) {
                $ageString .= " " . $days . " " . ($days == 1 ? "day" : "days");
            }
            $EncounterProcedurePatientDetails->patientDOB = $ageString;


            // if ($EmployeeStatus != 0 && $empId != 0) {
            //     $employeeData = DB::table('employee')
            //         ->select('id as empID', 'name as empName')
            //         ->where('id', $empId)
            //         ->first();
            //         if ($employeeData) {
            //             $EncounterProcedurePatientDetails->physicianId = $employeeData->empID;
            //             $EncounterProcedurePatientDetails->physicianName = $employeeData->empName;
            //         }

            // }
            // dd($EncounterProcedurePatientDetails);

            // Get performing cost centers for the logged-in user from emp_cc table
            $siteId = $EncounterProcedurePatientDetails->siteId;
            $performingCostCenters = DB::table('emp_cc')
                ->join('costcenter', function($join) {
                    $join->on(DB::raw('FIND_IN_SET(costcenter.id, emp_cc.cc_id)'), '>', DB::raw('0'));
                })
                ->join('cc_type', 'cc_type.id', '=', 'costcenter.cc_type')
                ->select('costcenter.id', 'costcenter.name')
                ->where('emp_cc.emp_id', $UserempId)
                ->where('emp_cc.status', 1)
                ->where('costcenter.status', 1)
                ->where('cc_type.performing', 1)
                ->whereRaw('FIND_IN_SET(?, emp_cc.site_id) > 0', [$siteId])
                ->orderBy('costcenter.name')
                ->get();

            // Filter cost centers based on sequential mapping of site_id and cc_id
            if ($performingCostCenters->isNotEmpty()) {
                $filteredCostCenters = collect();
                
                foreach ($performingCostCenters as $costCenter) {
                    // Get the emp_cc record for this cost center
                    $empCCRecord = DB::table('emp_cc')
                        ->where('emp_id', $UserempId)
                        ->where('status', 1)
                        ->whereRaw('FIND_IN_SET(?, cc_id) > 0', [$costCenter->id])
                        ->first();
                    
                    if ($empCCRecord) {
                        $siteIds = explode(',', $empCCRecord->site_id);
                        $ccIds = explode(',', $empCCRecord->cc_id);
                        
                        // Check if the current site_id matches the corresponding cc_id position
                        foreach ($siteIds as $index => $siteIdFromEmp) {
                            $siteIdFromEmp = trim($siteIdFromEmp);
                            if ($siteIdFromEmp == $siteId && isset($ccIds[$index]) && $ccIds[$index] == $costCenter->id) {
                                $filteredCostCenters->push($costCenter);
                                break;
                            }
                        }
                    }
                }
                
                $performingCostCenters = $filteredCostCenters;
            }

            // Add performing cost centers to the response
            $EncounterProcedurePatientDetails->performingCostCenters = $performingCostCenters;

            // Get service-based performing cost centers from activated_service table
            $serviceId = $EncounterProcedurePatientDetails->serviceId;
            $servicePerformingCCs = DB::table('activated_service')
                ->select('performing_cc_ids')
                ->where('service_id', $serviceId)
                ->where('site_id', $siteId) // Add site_id condition
                ->where('status', 1)
                ->first();

            if ($servicePerformingCCs && $servicePerformingCCs->performing_cc_ids) {
                $EncounterProcedurePatientDetails->servicePerformingCCs = $servicePerformingCCs->performing_cc_ids;
            } else {
                $EncounterProcedurePatientDetails->servicePerformingCCs = '';
            }

            return response()->json($EncounterProcedurePatientDetails);
            // return response()->json([
            //     'selectService' => false,
            //     'patientDetails' => $EncounterProcedurePatientDetails
            // ]);
        } else {
            return response()->json(['error' => 404]);
        }
    }

    public function AddVitalSign(VitalSignRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->vital_signs)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }
        $MR = trim($request->input('vs_mr'));

        // $MR = trim($orgCode.'-'.$request->input('vs_mr'));
        $PatientAge = trim($request->input('vs_age'));
        $SBP = trim($request->input('vs_sbp'));
        $DBP = trim($request->input('vs_dbp'));
        $Pulse = trim($request->input('vs_pulse'));
        $Temperature = trim($request->input('vs_temp'));
        $RespiratoryRate= trim($request->input('vs_rrate'));
        $Weight = trim($request->input('vs_weight'));
        $Height = trim($request->input('vs_height'));
        $Score = trim($request->input('vs_score'));
        $o2Saturation = ($request->input('vs_o2saturation'));
        $NursingNotes = trim($request->input('vs_nursingnotes'));
        $BillingCCID = trim($request->input('billingcc_id'));
        $ServiceModeId = trim($request->input('servicemode_id'));
        $ServiceId = trim($request->input('sevice_id'));

        // Handle null values for patients under 16
        $isUnder16 = $PatientAge && $PatientAge < 16;
        if ($isUnder16) {
            $SBP = $SBP ?: null;
            $DBP = $DBP ?: null;
            $Score = $Score ?: null;
        }

        $HeightInMeters = $Height / 100;
        $BMI = null;
        if ($HeightInMeters > 0) {
            $BMI = $Weight / ($HeightInMeters * $HeightInMeters);
            $BMI = round($BMI, 2);
        }

        $Weight = floatval($request->input('vs_weight'));
        $Height = floatval($request->input('vs_height'));

        $BSA = null;
        if ($Weight > 0 && $Height > 0) {
            $BSA = pow($Weight, 0.425) * pow($Height, 0.725) * 0.007184 ;
            $BSA = round($BSA, 2);
        }


        $Edt = $request->input('vs_edt');
        $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);
        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
            $status = 0; //Inactive

        }

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $VitalSign = new VitalSign();
        $VitalSign->mr_code = $MR;
        $VitalSign->service_id = $ServiceId;
        $VitalSign->service_mode_id = $ServiceModeId;
        $VitalSign->billing_cc = $BillingCCID;
        $VitalSign->patient_age = $PatientAge;
        $VitalSign->sbp = $SBP;
        $VitalSign->dbp = $DBP;
        $VitalSign->pulse = $Pulse;
        $VitalSign->temp = $Temperature;
        $VitalSign->r_rate = $RespiratoryRate;
        $VitalSign->weight = $Weight;
        $VitalSign->height = $Height;
        $VitalSign->score = $Score;
        $VitalSign->o2_saturation = $o2Saturation;
        $VitalSign->bmi = $BMI;
        $VitalSign->bsa = $BSA;
        $VitalSign->nursing_notes = $NursingNotes;
        $VitalSign->status = $status;
        $VitalSign->user_id = $sessionId;
        $VitalSign->last_updated = $last_updated;
        $VitalSign->timestamp = $timestamp;
        $VitalSign->effective_timestamp = $Edt;
        $VitalSign->save();

        if (empty($VitalSign->id)) {
            return response()->json(['error' => 'Failed to add Vital Sign.']);
        }

        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Vital Sign for '{$MR}' were recorded by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $VitalSign->logid = $logs->id;
        $VitalSign->save();
        return response()->json(['success' => 'Vital Sign added successfully']);

    }

    public function GetVitalSignData($mr)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->vital_signs)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }

        $VitalSigns = VitalSign::select('vital_sign.*',
        'service_mode.name as serviceMode','service_group.name as serviceGroup')
        // ->join('service_booking', 'service_booking.mr_code', '=', 'vital_sign.mr_code')
        ->join('services', 'services.id', '=', 'vital_sign.service_id')
        ->join('service_mode', 'service_mode.id', '=', 'vital_sign.service_mode_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        // ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        // ->join('patient_inout', 'patient_inout.booking_id', '=', 'service_booking.id')
        ->where('vital_sign.mr_code', $mr)
        ->orderBy('vital_sign.id', 'desc');
        // ->get();

        // return DataTables::of($VitalSigns)
        return DataTables::eloquent($VitalSigns)

            ->addColumn('id_raw', function ($VitalSign) {
                return $VitalSign->id;
            })
            ->addColumn('dateTime', function ($VitalSign) {
                $EffectiveTimeStamp = $VitalSign->effective_timestamp;
                $EffectiveTimeStamp = Carbon::createFromTimestamp($EffectiveTimeStamp)->format('d-F-y h:i A');
                return '<span class="label label-primary popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $EffectiveTimeStamp .'">'
                . 'View'
                . '</span>';
                // return $EffectiveTimeStamp;
            })
            ->addColumn('mode_group', function ($VitalSign) {
                $Sevicemode = $VitalSign->serviceMode;
                $serviceGroup = $VitalSign->serviceGroup;
                return $Sevicemode.' & '.$serviceGroup;
            })
            ->editColumn('status', function ($VitalSign) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->vital_signs)[3];
                return $updateStatus == 1 ? ($VitalSign->status ? '<span class="label label-success vs_status cursor-pointer" data-id="'.$VitalSign->id.'" data-status="'.$VitalSign->status.'">Active</span>' : '<span class="label label-danger vs_status cursor-pointer" data-id="'.$VitalSign->id.'" data-status="'.$VitalSign->status.'">Inactive</span>') : ($VitalSign->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->editColumn('update', function ($VitalSign) {
                $VitalSignId = $VitalSign->id;
                $logId = $VitalSign->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->vital_signs)[2];
                $actionButtons = '';
                if ($edit == 1) {
                    $actionButtons .= '<button type="submit" class="btn btn-warning edit-vs" data-vs-id="'.$VitalSignId.'" style="padding: 2px 5px 2px 6px;font-size:13px;">Update</button>';
                }
                else
                {
                  $actionButtons.= 'N/A';
                }
                return $VitalSign->status ? $actionButtons : '<span class="font-weight-bold">N/A</span>';
            })
            ->editColumn('log', function ($VitalSign) {
                $VitalSignId = $VitalSign->id;
                $logId = $VitalSign->logid;
                return '<button type="submit" class="btn btn-warning logs-modal" data-log-id="'.$logId.'" style="padding: 2px 5px 2px 6px;font-size:13px;">Log</button>';
            })
            ->editColumn('sbp', function ($VitalSign) {
                $SBP = $VitalSign->sbp;
                return !empty($SBP) ? $SBP.' (mmhg)' : 'N/A';
            })
            ->editColumn('dbp', function ($VitalSign) {
                $DBP = $VitalSign->dbp;
                return !empty($DBP) ? $DBP.' (mmhg)' : 'N/A';
            })
            ->editColumn('pulse', function ($VitalSign) {
                $pulse = $VitalSign->pulse;
                return $pulse.' / min';
            })
            ->editColumn('r_rate', function ($VitalSign) {
                $r_rate = $VitalSign->r_rate;
                return $r_rate.' / min';
            })
            ->editColumn('weight', function ($VitalSign) {
                $Weight = $VitalSign->weight;
                return $Weight.' kg';
            })
            ->editColumn('height', function ($VitalSign) {
                $height = $VitalSign->height;
                return $height.' cm';
            })
            ->editColumn('score', function ($VitalSign) {
                $score = $VitalSign->score;
                return !empty($score) ? $score : 'N/A';
            })
            ->editColumn('o2_saturation', function ($VitalSign) {
                $o2Saturation = $VitalSign->o2_saturation;
                return $o2Saturation.' %';
            })
            ->editColumn('temp', function ($VitalSign) {
                $temp = $VitalSign->temp;
                return $temp.' (F)';
            })
            ->editColumn('bsa', function ($VitalSign) {
                $BSA = $VitalSign->bsa;
                return $BSA . ' m<sup>2</sup>';
            })
            ->editColumn('age', function ($VitalSign) {

                $Age = ($VitalSign->patient_age);

                return '<span class="label label-primary popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $Age .'">'
                . 'View'
                . '</span>';
            })
            ->editColumn('details', function ($VitalSign) {
                $Details = ucwords($VitalSign->nursing_notes);
                if(!empty($Details))
                {
                    return '<span class="label label-info popoverTrigger" style="cursor: pointer;" data-container="body"  data-toggle="popover" data-placement="right" data-html="true" data-content="'. $Details .'">'
                    . 'Details'
                    . '</span>';
                }
                else{
                    return 'N/A';
                }
            })
            ->rawColumns(['mode_group','dateTime','sbp','dbp','pulse','r_rate','status','update','log','details','age','bsa'])
            ->make(true);
    }

    public function UpdateVitalSignStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->vital_signs)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $VitalSignID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $VitalSign = VitalSign::find($VitalSignID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $VitalSign->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $VitalSign->effective_timestamp = 0;

        }
        $VitalSign->status = $UpdateStatus;
        $VitalSign->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $VitalSignLog = VitalSign::where('id', $VitalSignID)->first();
        $logIds = $VitalSignLog->logid ? explode(',', $VitalSignLog->logid) : [];
        $logIds[] = $logs->id;
        $VitalSignLog->logid = implode(',', $logIds);
        $VitalSignLog->save();

        $VitalSign->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateVitalSignModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->vital_signs)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $VitalSigns = VitalSign::select('vital_sign.*')
        ->where('vital_sign.id', $id)
        ->first();

        $patientAge = ($VitalSigns->patient_age);
        $SBP = ($VitalSigns->sbp);
        $DBP = ($VitalSigns->dbp);
        $Pulse = ($VitalSigns->pulse);
        $Temp = ($VitalSigns->temp);
        $RespiratoryRate = ($VitalSigns->r_rate);
        $Weight = ($VitalSigns->weight);
        $Height = ($VitalSigns->height);
        $Score = ($VitalSigns->score);
        $o2_Saturation = ($VitalSigns->o2_saturation);
        $Details = ucwords($VitalSigns->nursing_notes);
        $effective_timestamp = $VitalSigns->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'patientAge' => $patientAge,
            'SBP' => $SBP,
            'DBP' => $DBP,
            'Pulse' => $Pulse,
            'Temp' => $Temp,
            'RespiratoryRate' => $RespiratoryRate,
            'Weight' => $Weight,
            'Height' => $Height,
            'Score' => $Score,
            'o2_Saturation' => $o2_Saturation,
            'Details' => $Details,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateVitalSign(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->vital_signs)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $VitalSigns = VitalSign::findOrFail($id);

        // Handle null values for patients under 16
        $patientAge = $VitalSigns->patient_age;
        $isUnder16 = $patientAge && $patientAge < 16;
        
        $VitalSigns->sbp = $request->input('uvs_sbp');
        $VitalSigns->dbp = $request->input('uvs_dbp');
        $VitalSigns->pulse = $request->input('uvs_pulse');
        $VitalSigns->temp = $request->input('uvs_temp');
        $VitalSigns->r_rate = $request->input('uvs_rrate');
        $VitalSigns->weight = $request->input('uvs_weight');
        $VitalSigns->height = $request->input('uvs_height');
        $VitalSigns->score = $request->input('uvs_score');
        $VitalSigns->nursing_notes = $request->input('uvs_nursingnotes');
        $VitalSigns->o2_saturation = $request->input('uvs_o2saturation');
        
        // Set null values for patients under 16 if fields are empty
        if ($isUnder16) {
            $VitalSigns->sbp = $VitalSigns->sbp ?: null;
            $VitalSigns->dbp = $VitalSigns->dbp ?: null;
            $VitalSigns->score = $VitalSigns->score ?: null;
        }

        $Weight = floatval($request->input('uvs_weight'));
        $Height = floatval($request->input('uvs_height'));

        $HeightInMeters = $Height / 100;
        $BMI = null;
        if ($HeightInMeters > 0) {
            $BMI = $Weight / ($HeightInMeters * $HeightInMeters);
            $BMI = round($BMI, 2);
        }

        $BSA = null;
        if ($Weight > 0 && $Height > 0) {
            $BSA = pow($Weight, 0.425) * pow($Height, 0.725) * 0.007184 ;
            $BSA = round($BSA, 2);
        }
        $VitalSigns->bmi = $BMI;
        $VitalSigns->bsa = $BSA;


        $effective_date = $request->input('uvs_edt');
        $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        $EffectDateTime->subMinute(1);

        if ($EffectDateTime->isPast()) {
            $status = 1; //Active
        } else {
             $status = 0; //Inactive
        }

        $VitalSigns->effective_timestamp = $effective_date;
        $VitalSigns->last_updated = $this->currentDatetime;
        $VitalSigns->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $VitalSigns->save();

        if (empty($VitalSigns->id)) {
            return response()->json(['error' => 'Failed to update Vital Sign Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $VitalSignsLog = VitalSign::where('id', $VitalSigns->id)->first();
        $logIds = $VitalSignsLog->logid ? explode(',', $VitalSignsLog->logid) : [];
        $logIds[] = $logs->id;
        $VitalSignsLog->logid = implode(',', $logIds);
        $VitalSignsLog->save();
        return response()->json(['success' => 'Vital Sign updated successfully']);
    }

    public function EncountersProcedures()
    {
        $colName = 'encounters_and_procedures';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $UserorgId = $user->org_id;
        // $orgCode = Organization::where('id', $UserorgId)->value('code');
        // $icdCodes = ICDCoding::where('status', 1)->get();
        $Patients = PatientRegistration::select('mr_code','name','cell_no')->where('status', 1)->orderBy('id', 'desc')->get();
        $Organizations = Organization::select('id', 'organization')->where('status', 1)->get();

        return view('dashboard.encounters_and_procedures', compact('user','Organizations','Patients'));
    }

    public function GetLatestVitalSignData($mr)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->encounters_and_procedures)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if (strpos($mr, '-') == false) {
            if($orgCode)
            {
                $mr = $orgCode.'-'.$mr;
            }
            else{
                $mr = 'ZMTP-'.$mr;
            }
        }
        $LatestVitalSigns = VitalSign::select('vital_sign.*')
        ->where('vital_sign.mr_code', $mr)
        ->orderBy('vital_sign.id', 'desc')
        ->first();
        return response()->json($LatestVitalSigns);

    }

    public function AddDiagnosisHistory(Request $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->encounters_and_procedures)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR = trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));

        $ICD = trim($request->input('m_icddiagnose'));
        if(empty($ICD))
        {
            $ICD = null;
        }

        $SinceData = $request->input('m_sincedate');
        if(!empty($SinceData))
        {
            $SinceData = Carbon::createFromFormat('Y-m-d', $SinceData)->timestamp;
        }

        $TillData = $request->input('m_tilledate');
        if(!empty($TillData))
        {
            $TillData = Carbon::createFromFormat('Y-m-d', $TillData)->timestamp;
        }

        // $Edt = $request->input('m_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');


        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }
        $status = 1;
        $Edt = $this->currentDatetime;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;
        if (empty($ICD) && empty($SinceData) && empty($TillData)) {
            return response()->json(['info' => 'All fields are empty. There is nothing to save.']);
        }

        $MedicalDiagnosisExists = MedicalDiagnosis::where('icd_id', $ICD)
        ->where('since_date', $SinceData)
        ->where('till_date', $TillData)
        ->exists();
        if ($MedicalDiagnosisExists) {
            return response()->json(['info' => 'Medical Diagnosis History already exists.']);
        }
        else
        {
            $MedicalDiagnose = new MedicalDiagnosis();
            $MedicalDiagnose->mr_code = $MR;
            $MedicalDiagnose->service_id = $SeviceId;
            $MedicalDiagnose->service_mode_id = $ServiceModeID;
            $MedicalDiagnose->billing_cc = $billingCC;
            $MedicalDiagnose->patient_age = $Age;
            $MedicalDiagnose->icd_id = $ICD;
            $MedicalDiagnose->since_date = $SinceData;
            $MedicalDiagnose->till_date = $TillData;
            $MedicalDiagnose->status = $status;
            $MedicalDiagnose->user_id = $sessionId;
            $MedicalDiagnose->last_updated = $last_updated;
            $MedicalDiagnose->timestamp = $timestamp;
            $MedicalDiagnose->effective_timestamp = $Edt;
            $MedicalDiagnose->save();

            if (empty($MedicalDiagnose->id)) {
                return response()->json(['error' => 'Failed to add Medical Diagnosis History.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Medical Diagnosis History has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $MedicalDiagnose->logid = $logs->id;
            $MedicalDiagnose->save();
            return response()->json(['success' => 'Medical Diagnosis History added successfully']);
        }

    }

    public function GetMedicalDiagnosisData($mr)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->encounters_and_procedures)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if (strpos($mr, '-') == false) {
            if($orgCode)
            {
                $mr = $orgCode.'-'.$mr;
            }
            else{
                $mr = 'ZMTP-'.$mr;
            }
        }
        $MedicalDiagnosis = MedicalDiagnosis::select('medical_diagnosis.*','icd_code.code as ICDCode',
        'icd_code.description as ICDDesc')
        ->leftJoin('icd_code', 'icd_code.id', '=', 'medical_diagnosis.icd_id')
        ->where('medical_diagnosis.mr_code', $mr)
        ->orderBy('medical_diagnosis.id', 'desc');
        // ->get();

        // return DataTables::of($MedicalDiagnosis)
        return DataTables::eloquent($MedicalDiagnosis)
            ->addColumn('id_raw', function ($MedicalDiagnose) {
                return $MedicalDiagnose->id;
            })
            ->addColumn('ICDCode', function ($MedicalDiagnose) {
                $ICDCode = $MedicalDiagnose->ICDCode;
                if(empty($ICDCode))
                {
                    $ICDCode = 'N/A';
                }
                return $ICDCode;
            })
            ->addColumn('ICDDesc', function ($MedicalDiagnose) {
                $ICDDesc = $MedicalDiagnose->ICDDesc;
                if(empty($ICDDesc))
                {
                    $ICDDesc = 'N/A';
                }
                return $ICDDesc;
            })
            ->addColumn('since_date', function ($MedicalDiagnose) {
                $SinceDate = $MedicalDiagnose->since_date;
                if(!empty($SinceDate))
                {
                    $SinceDate = Carbon::createFromTimestamp($SinceDate)->format('d-F-y');
                }
                else
                {
                   $SinceDate = 'N/A';
                }
                return $SinceDate;
            })
            ->addColumn('till_date', function ($MedicalDiagnose) {
                $TillDate = $MedicalDiagnose->till_date;
                if(!empty($TillDate))
                {
                    $TillDate = Carbon::createFromTimestamp($TillDate)->format('d-F-y');
                }
                else
                {
                   $TillDate = 'N/A';
                }
                return $TillDate;
            })

            ->rawColumns(['id_raw','ICDCode','since_date','till_date'])
            ->make(true);
    }

    public function AddAllergiesHistory(Request $request)
    {
        $rights = $this->rights;
        // $add = explode(',', $rights->encounters_and_procedures)[0];
        // $encountersView = explode(',', $rights->encounters_and_procedures)[0];
        // $vitalSignsView = explode(',', $rights->vital_signs)[0];
        // if($add == 0)
        // {
        //     abort(403, 'Forbidden');
        // }
        $encountersAdd = explode(',', $rights->encounters_and_procedures)[0];
        $vitalSignsAdd = explode(',', $rights->vital_signs)[0];
        if ($encountersAdd == 0 && $vitalSignsAdd == 0) 
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR =trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));

        $AllergyHistory = trim($request->input('allergy_history'));
        if(empty($AllergyHistory))
        {
            $AllergyHistory = null;
        }

        $SinceData = $request->input('al_sincedate');
        if(!empty($SinceData))
        {
            $SinceData = Carbon::createFromFormat('Y-m-d', $SinceData)->timestamp;
        }

        // $Edt = $request->input('al_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');


        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }

        $status = 1;
        $Edt = $this->currentDatetime;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;
        if (empty($AllergyHistory) && empty($SinceData)) {
            return response()->json(['info' => 'All fields are empty. There is nothing to save.']);
        }

        $AllergiesHistoryExists = AllergiesHistory::where('since_date', $SinceData)
        ->where('history', $AllergyHistory)
        ->exists();

        if ($AllergiesHistoryExists) {
            return response()->json(['info' => 'Allergies History already exists.']);
        }
        else
        {
            $AllergiesHistory = new AllergiesHistory();
            $AllergiesHistory->mr_code = $MR;
            $AllergiesHistory->service_id = $SeviceId;
            $AllergiesHistory->service_mode_id = $ServiceModeID;
            $AllergiesHistory->billing_cc = $billingCC;
            $AllergiesHistory->patient_age = $Age;
            $AllergiesHistory->history = $AllergyHistory;
            $AllergiesHistory->since_date = $SinceData;
            $AllergiesHistory->status = $status;
            $AllergiesHistory->user_id = $sessionId;
            $AllergiesHistory->last_updated = $last_updated;
            $AllergiesHistory->timestamp = $timestamp;
            $AllergiesHistory->effective_timestamp = $Edt;
            $AllergiesHistory->save();

            if (empty($AllergiesHistory->id)) {
                return response()->json(['error' => 'Failed to add Allergies History.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Allergies History has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $AllergiesHistory->logid = $logs->id;
            $AllergiesHistory->save();
            return response()->json(['success' => 'Allergies History added successfully']);
        }

    }

    public function GetAllergiesHistoryData($mr)
    {
        $rights = $this->rights;
        // $view = explode(',', $rights->encounters_and_procedures)[1];
        // $view = explode(',', $rights->vital_signs)[1];
        $encountersView = explode(',', $rights->encounters_and_procedures)[1];
        $vitalSignsView = explode(',', $rights->vital_signs)[1];
        // dd($encountersView,$vitalSignsView);
        // if($view == 0)
        if ($encountersView == 0 && $vitalSignsView == 0) 
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }
        $AllergiesHistories = AllergiesHistory::select('allergies_history.*')
        ->where('allergies_history.mr_code', $mr)
        ->orderBy('allergies_history.id', 'desc');
        // ->get();

        // return DataTables::of($AllergiesHistories)
        return DataTables::eloquent($AllergiesHistories)
            ->addColumn('id_raw', function ($AllergyHistory) {
                return $AllergyHistory->id;
            })
            ->addColumn('AllergyHistory', function ($AllergyHistory) {
                $AllergyHistory = $AllergyHistory->history;
                if(empty($AllergyHistory))
                {
                    $AllergyHistory = 'N/A';
                }
                return $AllergyHistory;
            })
            ->addColumn('since_date', function ($AllergyHistory) {
                $SinceDate = $AllergyHistory->since_date;
                if(!empty($SinceDate))
                {
                    $SinceDate = Carbon::createFromTimestamp($SinceDate)->format('d-F-y');
                }
                else
                {
                   $SinceDate = 'N/A';
                }
                return $SinceDate;
            })
            ->rawColumns(['id_raw','AllergyHistory','since_date'])
            ->make(true);
    }

    public function AddImmunizationHistory(Request $request)
    {
        $rights = $this->rights;
        // $add = explode(',', $rights->encounters_and_procedures)[0];
        // if($add == 0)
        // {
        //     abort(403, 'Forbidden');
        // }
        $encountersAdd = explode(',', $rights->encounters_and_procedures)[0];
        $vitalSignsAdd = explode(',', $rights->vital_signs)[0];
        if ($encountersAdd == 0 && $vitalSignsAdd == 0) 
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR =trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));

        $ImmunizationHistories = trim($request->input('immunizationhistory_history'));
        if(empty($ImmunizationHistories))
        {
            $ImmunizationHistories = null;
        }

        $Date = $request->input('ih_date');
        if(!empty($Date))
        {
            $Date = Carbon::createFromFormat('Y-m-d', $Date)->timestamp;
        }

        // $Edt = $request->input('ih_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');


        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }
        $status = 1;
        $Edt = $this->currentDatetime;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;
        if (empty($ImmunizationHistory) && empty($Date)) {
            return response()->json(['info' => 'All fields are empty. There is nothing to save.']);
        }

        $ImmunizationHistoryExists = ImmunizationHistory::where('date', $Date)
        ->where('history', $ImmunizationHistories)
        ->exists();

        if ($ImmunizationHistoryExists) {
            return response()->json(['info' => 'Immunization History already exists.']);
        }
        else
        {
            $ImmunizationHistory = new ImmunizationHistory();
            $ImmunizationHistory->mr_code = $MR;
            $ImmunizationHistory->service_id = $SeviceId;
            $ImmunizationHistory->service_mode_id = $ServiceModeID;
            $ImmunizationHistory->billing_cc = $billingCC;
            $ImmunizationHistory->patient_age = $Age;
            $ImmunizationHistory->history = $ImmunizationHistories;
            $ImmunizationHistory->date = $Date;
            $ImmunizationHistory->status = $status;
            $ImmunizationHistory->user_id = $sessionId;
            $ImmunizationHistory->last_updated = $last_updated;
            $ImmunizationHistory->timestamp = $timestamp;
            $ImmunizationHistory->effective_timestamp = $Edt;
            $ImmunizationHistory->save();

            if (empty($ImmunizationHistory->id)) {
                return response()->json(['error' => 'Failed to add Immunization History.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Immunization History has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ImmunizationHistory->logid = $logs->id;
            $ImmunizationHistory->save();
            return response()->json(['success' => 'Immunization History added successfully']);
        }

    }

    public function GetImmunizationHistoryData($mr)
    {
        $rights = $this->rights;
        // $view = explode(',', $rights->encounters_and_procedures)[1];
        // if($view == 0)
        // {
        //     abort(403, 'Forbidden');
        // }

        $encountersView = explode(',', $rights->encounters_and_procedures)[1];
        $vitalSignsView = explode(',', $rights->vital_signs)[1];
        if ($encountersView == 0 && $vitalSignsView == 0) 
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }
        $ImmunizationHistories = ImmunizationHistory::select('immunization_history.*')
        ->where('immunization_history.mr_code', $mr)
        ->orderBy('immunization_history.id', 'desc');
        // ->get();

        // return DataTables::of($ImmunizationHistories)
        return DataTables::eloquent($ImmunizationHistories)
            ->addColumn('id_raw', function ($ImmunizationHistory) {
                return $ImmunizationHistory->id;
            })
            ->addColumn('ImmunizationHistory', function ($ImmunizationHistory) {
                $ImmunizationHistory = $ImmunizationHistory->history;
                if(empty($ImmunizationHistory))
                {
                    $ImmunizationHistory = 'N/A';
                }
                return $ImmunizationHistory;
            })
            ->addColumn('date', function ($ImmunizationHistory) {
                $Date = $ImmunizationHistory->date;
                if(!empty($Date))
                {
                    $Date = Carbon::createFromTimestamp($Date)->format('d-F-y');
                }
                else
                {
                   $Date = 'N/A';
                }
                return $Date;
            })
            ->rawColumns(['id_raw','ImmunizationHistory','date'])
            ->make(true);
    }

    public function AddDrugHistory(Request $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->encounters_and_procedures)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR =trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));

        $DrugHistories = trim($request->input('drug_history'));
        if(empty($DrugHistories))
        {
            $DrugHistories = null;
        }

        $Dose = trim($request->input('dh_dose'));
        if(empty($Dose))
        {
            $Dose = null;
        }


        // $Edt = $request->input('dh_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');


        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }
        $status = 1;
        $Edt = $this->currentDatetime;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;
        if (empty($DrugHistories) && empty($Dose)) {
            return response()->json(['info' => 'All fields are empty. There is nothing to save.']);
        }

        $DrugHistoryExists = DrugHistory::where('dose', $Dose)
        ->where('history', $DrugHistories)
        ->exists();

        if ($DrugHistoryExists) {
            return response()->json(['info' => 'Immunization History already exists.']);
        }
        else
        {
            $DrugHistory = new DrugHistory();
            $DrugHistory->mr_code = $MR;
            $DrugHistory->service_id = $SeviceId;
            $DrugHistory->service_mode_id = $ServiceModeID;
            $DrugHistory->billing_cc = $billingCC;
            $DrugHistory->patient_age = $Age;
            $DrugHistory->history = $DrugHistories;
            $DrugHistory->dose = $Dose;
            $DrugHistory->status = $status;
            $DrugHistory->user_id = $sessionId;
            $DrugHistory->last_updated = $last_updated;
            $DrugHistory->timestamp = $timestamp;
            $DrugHistory->effective_timestamp = $Edt;
            $DrugHistory->save();

            if (empty($DrugHistory->id)) {
                return response()->json(['error' => 'Failed to add Drug History.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Drug History has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $DrugHistory->logid = $logs->id;
            $DrugHistory->save();
            return response()->json(['success' => 'Drug History added successfully']);
        }

    }

    public function GetDrugHistoryData($mr)
    {
        $rights = $this->rights;
        // $view = explode(',', $rights->encounters_and_procedures)[1];
        // if($view == 0)
        // {
        //     abort(403, 'Forbidden');
        // }
        // $encountersView = explode(',', $rights->encounters_and_procedures)[1];
        // $vitalSignsView = explode(',', $rights->vital_signs)[1];
        // if ($encountersView == 0 && $vitalSignsView == 0) 
        // {
        //     abort(403, 'Forbidden');
        // }
        $encountersAdd = explode(',', $rights->encounters_and_procedures)[0];
        $vitalSignsAdd = explode(',', $rights->vital_signs)[0];
        if ($encountersAdd == 0 && $vitalSignsAdd == 0) 
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }
        $DrugHistories = DrugHistory::select('drug_history.*')
        ->where('drug_history.mr_code', $mr)
        ->orderBy('drug_history.id', 'desc');
        // ->get();

        // return DataTables::of($DrugHistories)
        return DataTables::eloquent($DrugHistories)
            ->addColumn('id_raw', function ($DrugHistory) {
                return $DrugHistory->id;
            })
            ->addColumn('drugHistory', function ($DrugHistory) {
                $DrugHistory = $DrugHistory->history;
                if(empty($DrugHistory))
                {
                    $DrugHistory = 'N/A';
                }
                return $DrugHistory;
            })
            ->addColumn('dose', function ($DrugHistory) {
                $Dose = $DrugHistory->dose;
                if(empty($Dose))
                {
                    $Dose = 'N/A';
                }
                return $Dose;
            })
            ->rawColumns(['id_raw','drugHistory','dose'])
            ->make(true);
    }

    public function AddPastHistory(Request $request)
    {
        $rights = $this->rights;
        // $add = explode(',', $rights->encounters_and_procedures)[0];
        // if($add == 0)
        // {
        //     abort(403, 'Forbidden');
        // }

        $encountersAdd = explode(',', $rights->encounters_and_procedures)[0];
        $vitalSignsAdd = explode(',', $rights->vital_signs)[0];
        if ($encountersAdd == 0 && $vitalSignsAdd == 0) 
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR =trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));

        $PastHistories = trim($request->input('past_history'));
        if(empty($PastHistories))
        {
            $PastHistories = null;
        }

        $Date = $request->input('ph_date');
        if(!empty($Date))
        {
            $Date = Carbon::createFromFormat('Y-m-d', $Date)->timestamp;
        }

        // $Edt = $request->input('ph_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');


        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }

        $status = 1;
        $Edt = $this->currentDatetime;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;
        if (empty($PastHistories) && empty($Date)) {
            return response()->json(['info' => 'All fields are empty. There is nothing to save.']);
        }

        $PastHistoryExists = PastHistory::where('date', $Date)
        ->where('history', $PastHistories)
        ->exists();

        if ($PastHistoryExists) {
            return response()->json(['info' => 'Past History already exists.']);
        }
        else
        {
            $PastHistory = new PastHistory();
            $PastHistory->mr_code = $MR;
            $PastHistory->service_id = $SeviceId;
            $PastHistory->service_mode_id = $ServiceModeID;
            $PastHistory->billing_cc = $billingCC;
            $PastHistory->patient_age = $Age;
            $PastHistory->history = $PastHistories;
            $PastHistory->date = $Date;
            $PastHistory->status = $status;
            $PastHistory->user_id = $sessionId;
            $PastHistory->last_updated = $last_updated;
            $PastHistory->timestamp = $timestamp;
            $PastHistory->effective_timestamp = $Edt;
            $PastHistory->save();

            if (empty($PastHistory->id)) {
                return response()->json(['error' => 'Failed to add Past History.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Past History has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $PastHistory->logid = $logs->id;
            $PastHistory->save();
            return response()->json(['success' => 'Past History added successfully']);
        }

    }

    public function GetPastHistoryData($mr)
    {
        $rights = $this->rights;
        // $view = explode(',', $rights->encounters_and_procedures)[1];
        // if($view == 0)
        // {
        //     abort(403, 'Forbidden');
        // }
        $encountersView = explode(',', $rights->encounters_and_procedures)[1];
        $vitalSignsView = explode(',', $rights->vital_signs)[1];
        if ($encountersView == 0 && $vitalSignsView == 0) 
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }
        $PastHistories = PastHistory::select('past_history.*')
        ->where('past_history.mr_code', $mr)
        ->orderBy('past_history.id', 'desc');
        // ->get();

        // return DataTables::of($PastHistories)
        return DataTables::eloquent($PastHistories)
            ->addColumn('id_raw', function ($PastHistory) {
                return $PastHistory->id;
            })
            ->addColumn('pastHistory', function ($PastHistory) {
                $PastHistory = $PastHistory->history;
                if(empty($PastHistory))
                {
                    $PastHistory = 'N/A';
                }
                return $PastHistory;
            })
            ->addColumn('date', function ($PastHistory) {
                $Date = $PastHistory->date;
                if(!empty($Date))
                {
                    $Date = Carbon::createFromTimestamp($Date)->format('d-F-y');
                }
                else
                {
                   $Date = 'N/A';
                }
                return $Date;
            })
            ->rawColumns(['id_raw','drugHistory','date'])
            ->make(true);
    }

    public function AddObstericHistory(Request $request)
    {
        $rights = $this->rights;
        // $add = explode(',', $rights->encounters_and_procedures)[0];
        // if($add == 0)
        // {
        //     abort(403, 'Forbidden');
        // }
        $encountersAdd = explode(',', $rights->encounters_and_procedures)[0];
        $vitalSignsAdd = explode(',', $rights->vital_signs)[0];
        if ($encountersAdd == 0 && $vitalSignsAdd == 0) 
        {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR =trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));

        $ObstericHistories = trim($request->input('obsteric_history'));
        if(empty($ObstericHistories))
        {
            $ObstericHistories = null;
        }

        $Date = $request->input('oh_date');
        if(!empty($Date))
        {
            $Date = Carbon::createFromFormat('Y-m-d', $Date)->timestamp;
        }

        // $Edt = $request->input('oh_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');


        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }

        $status = 1;
        $Edt = $this->currentDatetime;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;
        if (empty($ObstericHistories) && empty($Date)) {
            return response()->json(['info' => 'All fields are empty. There is nothing to save.']);
        }

        $ObstericHistoryExists = ObstericHistory::where('date', $Date)
        ->where('history', $ObstericHistories)
        ->exists();

        if ($ObstericHistoryExists) {
            return response()->json(['info' => 'Obsteric History already exists.']);
        }
        else
        {
            $ObstericHistory = new ObstericHistory();
            $ObstericHistory->mr_code = $MR;
            $ObstericHistory->service_id = $SeviceId;
            $ObstericHistory->service_mode_id = $ServiceModeID;
            $ObstericHistory->billing_cc = $billingCC;
            $ObstericHistory->patient_age = $Age;
            $ObstericHistory->history = $ObstericHistories;
            $ObstericHistory->date = $Date;
            $ObstericHistory->status = $status;
            $ObstericHistory->user_id = $sessionId;
            $ObstericHistory->last_updated = $last_updated;
            $ObstericHistory->timestamp = $timestamp;
            $ObstericHistory->effective_timestamp = $Edt;
            $ObstericHistory->save();

            if (empty($ObstericHistory->id)) {
                return response()->json(['error' => 'Failed to add Obsteric History.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Obsteric History has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $ObstericHistory->logid = $logs->id;
            $ObstericHistory->save();
            return response()->json(['success' => 'Obsteric History added successfully']);
        }
    }

    public function GetObstericHistoryData($mr)
    {
        $rights = $this->rights;
        // $view = explode(',', $rights->encounters_and_procedures)[1];
        // if($view == 0)
        // {
        //     abort(403, 'Forbidden');
        // }

        $encountersView = explode(',', $rights->encounters_and_procedures)[1];
        $vitalSignsView = explode(',', $rights->vital_signs)[1];
        if ($encountersView == 0 && $vitalSignsView == 0) 
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }
        $ObstericHistories = ObstericHistory::select('obsteric_history.*')
        ->where('obsteric_history.mr_code', $mr)
        ->orderBy('obsteric_history.id', 'desc');
        // ->get();

        // return DataTables::of($ObstericHistories)
        return DataTables::eloquent($ObstericHistories)
            ->addColumn('id_raw', function ($ObstericHistory) {
                return $ObstericHistory->id;
            })
            ->addColumn('obstericHistory', function ($ObstericHistory) {
                $ObstericHistory = $ObstericHistory->history;
                if(empty($ObstericHistory))
                {
                    $ObstericHistory = 'N/A';
                }
                return $ObstericHistory;
            })
            ->addColumn('date', function ($ObstericHistory) {
                $Date = $ObstericHistory->date;
                if(!empty($Date))
                {
                    $Date = Carbon::createFromTimestamp($Date)->format('d-F-y');
                }
                else
                {
                   $Date = 'N/A';
                }
                return $Date;
            })
            ->rawColumns(['id_raw','obstericHistory','date'])
            ->make(true);
    }

    public function AddSocialHistory(Request $request)
    {
        $rights = $this->rights;
        // $add = explode(',', $rights->encounters_and_procedures)[0];
        // if($add == 0)
        // {
        //     abort(403, 'Forbidden');
        // }
        $encountersAdd = explode(',', $rights->encounters_and_procedures)[0];
        $vitalSignsAdd = explode(',', $rights->vital_signs)[0];
        if ($encountersAdd == 0 && $vitalSignsAdd == 0) 
        {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR =trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));

        $SocialHistories = trim($request->input('social_history'));
        if(empty($SocialHistories))
        {
            $SocialHistories = null;
        }

        $Date = $request->input('sh_date');
        if(!empty($Date))
        {
            $Date = Carbon::createFromFormat('Y-m-d', $Date)->timestamp;
        }

        // $Edt = $request->input('sh_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');


        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }
        $status = 1;
        $Edt = $this->currentDatetime;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;
        if (empty($SocialHistories) && empty($Date)) {
            return response()->json(['info' => 'All fields are empty. There is nothing to save.']);
        }

        $SocialHistoryExists = SocialHistory::where('date', $Date)
        ->where('history', $SocialHistories)
        ->exists();

        if ($SocialHistoryExists) {
            return response()->json(['info' => 'Social History already exists.']);
        }
        else
        {
            $SocialHistory = new SocialHistory();
            $SocialHistory->mr_code = $MR;
            $SocialHistory->service_id = $SeviceId;
            $SocialHistory->service_mode_id = $ServiceModeID;
            $SocialHistory->billing_cc = $billingCC;
            $SocialHistory->patient_age = $Age;
            $SocialHistory->history = $SocialHistories;
            $SocialHistory->date = $Date;
            $SocialHistory->status = $status;
            $SocialHistory->user_id = $sessionId;
            $SocialHistory->last_updated = $last_updated;
            $SocialHistory->timestamp = $timestamp;
            $SocialHistory->effective_timestamp = $Edt;
            $SocialHistory->save();

            if (empty($SocialHistory->id)) {
                return response()->json(['error' => 'Failed to add Social History.']);
            }

            $logs = Logs::create([
                'module' => 'patient_medical_record',
                'content' => "Social History has been added by '{$sessionName}'",
                'event' => 'add',
                'timestamp' => $timestamp,
            ]);
            $logId = $logs->id;
            $SocialHistory->logid = $logs->id;
            $SocialHistory->save();
            return response()->json(['success' => 'Social History added successfully']);
        }
    }

    public function GetSocialHistoryData($mr)
    {
        $rights = $this->rights;
        // $view = explode(',', $rights->encounters_and_procedures)[1];
        // if($view == 0)
        // {
        //     abort(403, 'Forbidden');
        // }

        $encountersView = explode(',', $rights->encounters_and_procedures)[1];
        $vitalSignsView = explode(',', $rights->vital_signs)[1];
        if ($encountersView == 0 && $vitalSignsView == 0) 
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }
        $SocialHistories = SocialHistory::select('social_history.*')
        ->where('social_history.mr_code', $mr)
        ->orderBy('social_history.id', 'desc');
        // ->get();

        // return DataTables::of($SocialHistories)
        return DataTables::eloquent($SocialHistories)
            ->addColumn('id_raw', function ($SocialHistory) {
                return $SocialHistory->id;
            })
            ->addColumn('socialHistory', function ($SocialHistory) {
                $SocialHistory = $SocialHistory->history;
                if(empty($socialHistory))
                {
                    $SocialHistory = 'N/A';
                }
                return $SocialHistory;
            })
            ->addColumn('date', function ($SocialHistory) {
                $Date = $SocialHistory->date;
                if(!empty($Date))
                {
                    $Date = Carbon::createFromTimestamp($Date)->format('d-F-y');
                }
                else
                {
                   $Date = 'N/A';
                }
                return $Date;
            })
            ->rawColumns(['id_raw','socialHistory','date'])
            ->make(true);
    }

    public function AddVisitBasedDetails(Request $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->encounters_and_procedures)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $UserorgId = $session->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        if(empty($orgCode))
        {
            $orgCode = 'ZMTP';
        }

        $MR =trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));
        $empid = trim($request->input('empid'));

        $Complaints = ($request->input('Complaints'));
        $Complaints = is_array($Complaints) ? implode(',', $Complaints) : '';

        $ClinicalNotes = trim($request->input('clnical_notes'));
        $Summary = trim($request->input('summary'));


        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        $VisitBasedDetail = new VisitBasedDetails();
        $VisitBasedDetail->mr_code = $MR;
        $VisitBasedDetail->service_id = $SeviceId;
        $VisitBasedDetail->service_mode_id = $ServiceModeID;
        $VisitBasedDetail->billing_cc = $billingCC;
        $VisitBasedDetail->emp_id = $empid;
        $VisitBasedDetail->patient_age = $Age;
        $VisitBasedDetail->complaints = $Complaints;
        $VisitBasedDetail->clinical_notes = $ClinicalNotes;
        $VisitBasedDetail->summary = $Summary;
        $VisitBasedDetail->user_id = $sessionId;
        $VisitBasedDetail->last_updated = $last_updated;
        $VisitBasedDetail->timestamp = $timestamp;

        $PatientArrivalDeparture = PatientArrivalDeparture::where('mr_code', $MR)
        ->where('billing_cc', $billingCC)
        ->where('service_mode_id', $ServiceModeID)
        ->where('service_id', $SeviceId)
        ->where('emp_id', $empid)
        ->where('status', 1)
        ->whereNull('service_end_time')
        ->first();

        if (!$PatientArrivalDeparture) {
            return response()->json(['error' => "Failed to add visit-based details due to an issue with the patient's arrival end time."]);
        }

        $VisitBasedDetail->save();

        if (empty($VisitBasedDetail->id)) {
            return response()->json(['error' => 'Failed to add Visit Based Details.']);
        }

        $PatientArrivalDeparture->service_end_time = $timestamp;
        $PatientArrivalDeparture->status = 0;
        if($PatientArrivalDeparture->save())
        {
            // RequisitionForEPI::where('mr_code', $MR)
            // ->where('service_id', $SeviceId)
            // ->where('service_mode_id', $ServiceModeID)
            // ->where('billing_cc', $billingCC)
            // ->where('emp_id', $empid)
            // ->where('status', 1)
            // ->update(['status' => 0]);

            $reqEpiQuery = RequisitionForEPI::where('mr_code', $MR)
            ->where('service_id', $SeviceId)
            ->where('service_mode_id', $ServiceModeID)
            ->where('billing_cc', $billingCC)
            ->where('emp_id', $empid)
            ->where('status', 1);

            $matchingReqs = $reqEpiQuery->get();

            if ($matchingReqs->isNotEmpty()) {
                $log = Logs::create([
                    'module' => 'patient_medical_record',
                    'content' => "Status set to Inactive by '{$sessionName}'",
                    'event' => 'update',
                    'timestamp' => $timestamp,
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
            'module' => 'patient_medical_record',
            'content' => "Visit Based Details has been added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;
        $VisitBasedDetail->logid = $logs->id;
        $VisitBasedDetail->save();
        return response()->json(['success' => 'Visit Based Details added successfully']);
    }

    public function GetVisitBasedDetails($mr)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->encounters_and_procedures)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if (strpos($mr, '-') == false) {
        //     if($orgCode)
        //     {
        //         $mr = $orgCode.'-'.$mr;
        //     }
        //     else{
        //         $mr = 'ZMTP-'.$mr;
        //     }
        // }
        $VisitBasedDetails = VisitBasedDetails::select('visit_based_details.*',
        'costcenter.name as Speciality','employee.name as Physician',
        'service_mode.name as serviceMode','service_group.name as serviceGroup')
        ->join('costcenter', 'costcenter.id', '=', 'visit_based_details.billing_cc')
        ->join('service_mode', 'service_mode.id', '=', 'visit_based_details.service_mode_id')
        ->join('services', 'services.id', '=', 'visit_based_details.service_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('employee', 'employee.id', '=', 'visit_based_details.emp_id')
        ->where('visit_based_details.mr_code', $mr)
        ->orderBy('visit_based_details.id', 'desc');
        // ->get();

        // return DataTables::of($VisitBasedDetails)
        return DataTables::eloquent($VisitBasedDetails)
            ->addColumn('id_raw', function ($VisitBasedDetail) {
                return $VisitBasedDetail->id;
            })
            ->addColumn('date', function ($SocialHistory) {
                $Date = $SocialHistory->timestamp;
                $Date = Carbon::createFromTimestamp($Date)->format('d-F-y');
                return $Date;
            })
            ->addColumn('ServiceModeGroup', function ($VisitBasedDetail) {
                $serviceMode = $VisitBasedDetail->serviceMode;
                $serviceGroup = $VisitBasedDetail->serviceGroup;
                $ServiceModeGroup = $serviceMode .' / '. $serviceGroup;
                return $ServiceModeGroup;
            })
            // ->addColumn('clinical_notes', function ($VisitBasedDetail) {
            //     $clinicalNotes = $VisitBasedDetail->clinical_notes;
            //     return $clinicalNotes;
            // })
            ->addColumn('summary', function ($VisitBasedDetail) {
                $summary = $VisitBasedDetail->summary;
                return $summary;
            })
            ->addColumn('speciality', function ($VisitBasedDetail) {
                $Speciality = $VisitBasedDetail->Speciality;
                return $Speciality;
            })
            ->addColumn('physician', function ($VisitBasedDetail) {
                $Physician = $VisitBasedDetail->Physician;
                return $Physician;
            })
            ->editColumn('action', function ($VisitBasedDetail) {
                $id = $VisitBasedDetail->id;

                $actionButtons = '<button type="button" data-id="'.$id.'" class="btn waves-effect waves-light btn-sm btn-primary viewVisitDetails">'
                . '<i class="fa fa-eye"></i> View Details'
                . '</button>';
                return $actionButtons;
            })
            ->addColumn('logs', function ($VisitBasedDetail) {
                $logId = $VisitBasedDetail->logid;
                $actionButtons = '<button type="button" class="btn waves-effect waves-light btn-sm btn-warning logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';
                return $actionButtons;
            })
            ->rawColumns(['id_raw','speciality','physician',
            'summary','date','action','logs'])
            ->make(true);
    }

    public function AddRequisitionEPI(RequisitionEPIRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->encounters_and_procedures)[0];
        if($add == 0)
        {
            abort(403, 'Forbidden');
        }
        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        // $UserorgId = $session->org_id;
        // $orgCode = Organization::where('id', $UserorgId)->value('code');
        // if(empty($orgCode))
        // {
        //     $orgCode = 'ZMTP';
        // }

        // $MR = $orgCode.'-'.trim($request->input('patientmr'));
        $Org = trim($request->input('repi_org'));
        $Site = trim($request->input('repi_site'));
        $MR = trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeIDs = $request->input('servicemode_id', []);
        $ServiceIds = $request->input('sevice_id', []);
        $Age = trim($request->input('patient_age'));
        $Physician = trim($request->input('physician'));
        $Physician = $Physician !== '' ? $Physician : null;
        $Action = trim($request->input('action'));
        $Remarks = trim($request->input('repi_remarks'));

        // Ensure arrays are properly formatted
        if (!is_array($ServiceModeIDs)) {
            $ServiceModeIDs = [$ServiceModeIDs];
        }
        if (!is_array($ServiceIds)) {
            $ServiceIds = [$ServiceIds];
        }

        // Validate that we have the same number of services and service modes
        if (count($ServiceIds) !== count($ServiceModeIDs)) {
            return response()->json(['error' => 'Number of services and service modes must match.']);
        }

        // Validate that all arrays have values
        if (empty($ServiceIds) || empty($ServiceModeIDs)) {
            return response()->json(['error' => 'At least one service and service mode must be selected.']);
        }

        // $Edt = $request->input('repi_edt');
        // $Edt = Carbon::createFromFormat('l d F Y - h:i A', $Edt)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($Edt)->setTimezone('Asia/Karachi');
        // $EffectDateTime->subMinute(1);
        // if ($EffectDateTime->isPast()) {
        //     $status = 1; //Active
        // } else {
        //     $status = 0; //Inactive

        // }
        $status = 1;
        $Edt = $this->currentDatetime;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;
        $logId = null;

        if($Action == 'e')
        {
            $act = 'Encounter';
        }
        else if($Action == 'p')
        {
            $act = 'Procedure';
        }
        else if($Action == 'i')
        {
            $act = 'Investigation';
        }

        // Check for existing requisitions for each service/service mode combination
        foreach ($ServiceIds as $index => $serviceId) {
            $serviceModeId = $ServiceModeIDs[$index];
            
            $common = [
                ['site_id',        $Site],
                ['emp_id',         $Physician],
                ['mr_code',        $MR],
                ['service_id',     $serviceId],
                ['service_mode_id',$serviceModeId],
                ['billing_cc',     $billingCC],
            ];

            $existsInArrival = PatientArrivalDeparture::where($common)
            ->where('status', 1)
            ->exists();

            $existsInBooking = ServiceBooking::where($common)
            ->where('status', 1)
            ->exists();

            $existsInRequisition = RequisitionForEPI::where($common)
            ->where('status', 1)
            ->where('action', $Action)
            ->exists();

            if ($existsInBooking || $existsInArrival || $existsInRequisition) {
                if ($existsInArrival) {
                    $msg = 'Arrival already recorded for this MR#, service, service mode, billing CC & physician';
                }
                elseif ($existsInBooking) {
                    $msg = 'Service Booking already exists for this MR#, service, service mode, billing CC & physician';
                }
                else {
                    $msg  = "Requisition for $act already exist with these details.";
                }

                return response()->json([
                    'info' => $msg
                ]);
            }
        }
        // $ReqisitionExist = RequisitionForEPI::where('emp_id', $Physician)
        // ->where('mr_code', $MR)
        // ->where('service_id', $SeviceId)
        // ->where('service_mode_id', $ServiceModeID)
        // ->where('billing_cc', $billingCC)
        // ->where('action', $Action)
        // ->where('status', 1)
        // ->exists();

        // if ($ReqisitionExist) {
        //     return response()->json(['info' => 'Requisition for ' . $act . ' already exist with these details.']);
        // }
        // else{
            // Insert multiple requisitions
            $successCount = 0;
            $totalCount = count($ServiceIds);
            
            foreach ($ServiceIds as $index => $serviceId) {
                $serviceModeId = $ServiceModeIDs[$index];
                $RquEPI = new RequisitionForEPI();
                $RquEPI->org_id = $Org;
                $RquEPI->site_id = $Site;
                $RquEPI->mr_code = $MR;
                $RquEPI->service_id = $serviceId;
                $RquEPI->service_mode_id = $serviceModeId;
                $RquEPI->billing_cc = $billingCC;
                $RquEPI->patient_age = $Age;
                $RquEPI->emp_id = $Physician;
                $RquEPI->action = $Action;
                $RquEPI->remarks = $Remarks;
                $RquEPI->status = $status;
                $RquEPI->user_id = $sessionId;
                $RquEPI->last_updated = $last_updated;
                $RquEPI->timestamp = $timestamp;
                $RquEPI->effective_timestamp = $Edt;

                $RquEPI->save();

                if (!empty($RquEPI->id)) {
                    $successCount++;
                    
                    $logs = Logs::create([
                        'module' => 'patient_medical_record',
                        'content' => "Requisition For $act has been added by '{$sessionName}'",
                        'event' => 'add',
                        'timestamp' => $timestamp,
                    ]);
                    $logId = $logs->id;
                    $RquEPI->logid = $logs->id;
                    $RquEPI->save();
                }
            }

            if($Action == 'e')
            {
                return response()->json(['success' => "Requisition for Encounter added successfully"]);
            }
            else if($Action == 'p')
            {
                return response()->json(['success' => "Requisition for Procedure added successfully"]);
            }
            else if($Action == 'i')
            {
                if ($successCount === $totalCount) {
                    return response()->json(['success' => "All $totalCount requisitions for $act added successfully"]);
                } 
                else {
                    return response()->json(['error' => "Failed to add some requisitions for $act. Only $successCount out of $totalCount were added."]);
                }
            }
    }

    public function GetRequisitionEPI(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->encounters_and_procedures)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        // $referrer = $request->headers->get('referer');
        // $act = last(explode('/', $referrer));

        $act = $request->query('act', 'default_act_value');
        $mr = $request->query('mr');

        $RequisitionForEPIDetails = RequisitionForEPI::select('req_epi.*',
        'services.name as serviceName','service_mode.name as serviceModeName',
        'costcenter.name as billingCC','employee.name as empName')
        ->join('services', 'services.id', '=', 'req_epi.service_id')
        ->join('costcenter', 'costcenter.id', '=', 'req_epi.billing_cc')
        ->join('service_mode', 'service_mode.id', '=', 'req_epi.service_mode_id')
        ->leftjoin('employee', 'employee.id', '=', 'req_epi.emp_id')
        ->where('req_epi.action', $act)
        ->where('req_epi.mr_code', $mr)
        ->orderBy('req_epi.id', 'desc');
        // ->get();

        // return DataTables::of($RequisitionForEPIDetails)
        return DataTables::eloquent($RequisitionForEPIDetails)
            ->addColumn('id_raw', function ($RequisitionForEPIDetail) {
                return $RequisitionForEPIDetail->id;
            })
            ->addColumn('mr', function ($RequisitionForEPIDetail) {
                $mrCode = $RequisitionForEPIDetail->mr_code;
                $Age = $RequisitionForEPIDetail->patient_age;
                $empName = $RequisitionForEPIDetail->empName;
                $empName = !empty($RequisitionForEPIDetail->empName) ? $RequisitionForEPIDetail->empName : 'N/A';
                $effectiveDate = Carbon::createFromTimestamp($RequisitionForEPIDetail->effective_timestamp)->format('l d F Y - h:i A');
                return $mrCode.'<hr class="mt-1 mb-2">'
                .'<b>Age:</b> '.ucwords($Age)
                .'<hr class="mt-1 mb-2"><b>Physician:</b> '.ucwords($empName)
                .'<hr class="mt-1 mb-2"><b>Request Date&Time:</b> '.ucwords($effectiveDate);
            })
            ->addColumn('service', function ($RequisitionForEPIDetail) {
                $serviceName = $RequisitionForEPIDetail->serviceName;
                $serviceModeName = $RequisitionForEPIDetail->serviceModeName;
                // return ucwords($serviceName);
                return '<b>Service:</b> '.ucwords($serviceName)
                .'<hr class="mt-1 mb-2"><b>Service Mode:</b> '.ucwords($serviceModeName);
            })
            // ->addColumn('serviceModeName', function ($RequisitionForEPIDetail) {
            //     $serviceModeName = $RequisitionForEPIDetail->serviceModeName;
            //     return ucwords($serviceModeName);
            // })
            ->addColumn('billingCC', function ($RequisitionForEPIDetail) {
                $billingCC = $RequisitionForEPIDetail->billingCC;
                return ucwords($billingCC);
            })
            ->addColumn('remarks', function ($RequisitionForEPIDetail) {
                $Remarks = !empty($RequisitionForEPIDetail->remarks) ? $RequisitionForEPIDetail->remarks : 'N/A';
                return ucwords($Remarks);
            })
            ->addColumn('action', function ($RequisitionForEPIDetail) {
                $RequisitionForEPIId = $RequisitionForEPIDetail->id;
                $logId = $RequisitionForEPIDetail->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->encounters_and_procedures)[2];
                $actionButtons = '';
                if ($edit == 1) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-reqepi" data-reqepiid="'.$RequisitionForEPIId.'">'
                    . '<i class="fa fa-edit"></i> Edit'
                    . '</button>';
                }
                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';

                return $RequisitionForEPIDetail->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($RequisitionForEPIDetail) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->encounters_and_procedures)[3];
                // return $updateStatus == 1 ? ($RequisitionForEPIDetail->status ? '<span class="label label-success reqepi cursor-pointer" data-id="'.$RequisitionForEPIDetail->id.'" data-status="'.$RequisitionForEPIDetail->status.'">Active</span>' : '<span class="label label-danger reqepi cursor-pointer" data-id="'.$RequisitionForEPIDetail->id.'" data-status="'.$RequisitionForEPIDetail->status.'">Inactive</span>') : ($RequisitionForEPIDetail->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
                return $updateStatus == 1 ? ($RequisitionForEPIDetail->status ? '<span class="label label-success" >Active</span>' : '<span class="label label-danger">Inactive</span>') : ($RequisitionForEPIDetail->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->rawColumns(['id_raw','mr','service','action','status'])
            ->make(true);
    }

    public function UpdateRequisitionEPIStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->encounters_and_procedures)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $RequisitionEPIID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $RequisitionForEPI = RequisitionForEPI::find($RequisitionEPIID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $RequisitionForEPI->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $RequisitionForEPI->effective_timestamp = 0;

        }
        $RequisitionForEPI->status = $UpdateStatus;
        $RequisitionForEPI->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $RequisitionEPILog = RequisitionForEPI::where('id', $RequisitionEPIID)->first();
        $logIds = $RequisitionEPILog->logid ? explode(',', $RequisitionEPILog->logid) : [];
        $logIds[] = $logs->id;
        $RequisitionEPILog->logid = implode(',', $logIds);
        $RequisitionEPILog->save();

        $RequisitionForEPI->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateReqEPIModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->encounters_and_procedures)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $RequisitionForEPI = RequisitionForEPI::select('req_epi.*')
        ->where('req_epi.id', $id)
        ->first();

        $Remarks = ucwords($RequisitionForEPI->remarks);
        $effective_timestamp = $RequisitionForEPI->effective_timestamp;
        $effective_timestamp = Carbon::createFromTimestamp($effective_timestamp);
        $effective_timestamp = $effective_timestamp->format('l d F Y - h:i A');

        $data = [
            'id' => $id,
            'Remarks' => $Remarks,
            'effective_timestamp' => $effective_timestamp,
        ];

        return response()->json($data);
    }

    public function UpdateReqEPI(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->encounters_and_procedures)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        // dd($request->all());
        $RequisitionForEPI = RequisitionForEPI::findOrFail($id);

        $RequisitionForEPI->remarks = $request->input('u_repi_remarks');
        // $effective_date = $request->input('u_repi_edt');
        // $effective_date = Carbon::createFromFormat('l d F Y - h:i A', $effective_date)->timestamp;
        // $EffectDateTime = Carbon::createFromTimestamp($effective_date)->setTimezone('Asia/Karachi');
        // $EffectDateTime->subMinute(1);

        // if ($EffectDateTime->isPast()) {
        //     $status = 1;
        // } else {
        //      $status = 0;
        // }

        // $RequisitionForEPI->effective_timestamp = $effective_date;
        $RequisitionForEPI->last_updated = $this->currentDatetime;
        // $RequisitionForEPI->status = $status;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $RequisitionForEPI->save();

        if (empty($RequisitionForEPI->id)) {
            return response()->json(['error' => 'Failed to update Requisition For EPI Details. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $RequisitionForEPILog = RequisitionForEPI::where('id', $RequisitionForEPI->id)->first();
        $logIds = $RequisitionForEPILog->logid ? explode(',', $RequisitionForEPILog->logid) : [];
        $logIds[] = $logs->id;
        $RequisitionForEPILog->logid = implode(',', $logIds);
        $RequisitionForEPILog->save();
        return response()->json(['success' => 'Requisition For EPI updated successfully']);
    }

    public function GetTrackingVisits($id)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->encounters_and_procedures)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $id = trim($id);

        $VisitBasedDetails = VisitBasedDetails::select('visit_based_details.clinical_notes')
        ->selectRaw("GROUP_CONCAT(icd_code.code) as ICDCodes, GROUP_CONCAT(icd_code.description) as ICDDescriptions")
        ->join('icd_code', function($join) {
            $join->whereRaw('FIND_IN_SET(icd_code.id, visit_based_details.complaints)');
        })
        ->where('visit_based_details.id', $id)
        ->groupBy('visit_based_details.clinical_notes')
        ->get();

        return $VisitBasedDetails;
    }

    public function RequisitionMedicationConsumption($mr)
    {
        $colName = 'encounters_and_procedures';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        $mr = strpos($mr, '-') === false ? ($orgCode ? $orgCode : 'ZMTP') . '-' . $mr : $mr;

        $MedicationRoutes = MedicationRoutes::select('id', 'name')->where('status', 1)->get();
        $MedicationFrequencies = MedicationFrequency::select('id', 'name')->where('status', 1)->get();

        $Generics = InventoryGeneric::select('inventory_generic.id', 'inventory_generic.name')
        ->join('inventory_category', 'inventory_category.id', '=', 'inventory_generic.cat_id')
        ->where('inventory_generic.status', 1)
        ->where('inventory_category.name', 'like', 'Medicine%')
        ->get();

        $ServiceLocations = ServiceLocation::select('id', 'name')->where('status', 1)->get();
        $PatientDetails = PatientRegistration::select(
            'patient.name as patientName', 'gender.name as gender','organization.organization as orgName',
            'organization.id as orgId','org_site.id as siteId',
            'org_site.name as siteName','patient.dob as patientDOB','patient.mr_code as patientMR',
            'employee.name as responsiblePhysician','billingCC.name as billingCCName',
            'patient_inout.status as patientInOutStatus'
        )
        ->join('gender', 'gender.id', '=', 'patient.gender_id')
        ->join('organization', 'organization.id', '=', 'patient.org_id')
        ->join('org_site', 'org_site.id', '=', 'patient.site_id')
        ->leftjoin('patient_inout', 'patient_inout.mr_code', '=', 'patient.mr_code')
        ->leftjoin('employee', 'employee.id', '=', 'patient_inout.emp_id')
        ->leftjoin('costcenter as billingCC', 'billingCC.id', '=', 'patient_inout.billing_cc')
        ->where('patient.status', 1)
        ->when(
            PatientArrivalDeparture::where([
                ['mr_code', '=', $mr],
                ['status', '=', 1]
            ])->exists(),
            function ($query) {
                $query->where('patient_inout.status', 1);
            }
        )
        // ->orWhere(function($query) use ($mr) {
        //     $query->where('patient_inout.status', 0)
        //           ->where('patient_inout.mr_code', $mr)
        //           ->orderBy('patient_inout.created_at', 'desc'); // Get the latest entry with status 0
        // })
        // ->where('patient_inout.status', 1)
        ->where('patient.mr_code', $mr)
        ->first();
        $canAddRequisition = $PatientDetails && $PatientDetails->patientInOutStatus == 1;

        $dob = Carbon::createFromTimestamp($PatientDetails->patientDOB);
        $now = Carbon::now();
        $diff = $dob->diff($now);

        $years = $diff->y;
        $months = $diff->m;
        $days = $diff->d;

        $ageString = "";
        if ($years > 0) {
            $ageString .= $years . " " . ($years == 1 ? "year" : "years");
        }
        if ($months > 0) {
            $ageString .= " " . $months . " " . ($months == 1 ? "month" : "months");
        }
        if ($days > 0) {
            $ageString .= " " . $days . " " . ($days == 1 ? "day" : "days");
        }

        return view('dashboard.req-medication-consumption', compact('user','MedicationRoutes','MedicationFrequencies','Generics','PatientDetails','ageString','ServiceLocations','canAddRequisition'));
    }

    public function AddRequisitionMedicationConsumption(RequisitionMedicationConsumptionRequest $request)
    {
        if (explode(',', $this->rights->encounters_and_procedures)[0] == 0) {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionId = $session->id;
        $sessionName = $session->name;
        $MR = trim($request->input('rmc_mr'));

        $PatientDetails = PatientRegistration::select(
            'patient.name as patientName', 'patient.mr_code as patientMR', 'gender.id as genderId', 'patient.dob as patientDOB',
            'employee.id as empID',
            'patient_inout.org_id as OrgId', 'patient_inout.site_id as SiteId', 'services.id as serviceId',
            'service_mode.id as serviceModeId', 'billingCC.id as billingCCId', 'service_group.id as serviceGroupId',
            'service_type.id as serviceTypeId'
        )
        ->join('patient_inout', 'patient_inout.mr_code', '=', 'patient.mr_code')
        ->join('gender', 'gender.id', '=', 'patient.gender_id')
        ->join('costcenter as billingCC', 'billingCC.id', '=', 'patient_inout.billing_cc')
        ->join('employee', 'employee.id', '=', 'patient_inout.emp_id')
        ->join('service_mode', 'service_mode.id', '=', 'patient_inout.service_mode_id')
        ->join('services', 'services.id', '=', 'patient_inout.service_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->where('patient.status', 1)
        ->where('patient_inout.status', 1)
        ->where('patient.mr_code', $MR)
        ->first();

        if ($PatientDetails) {
            $MR = $PatientDetails->patientMR;
            $Gender = $PatientDetails->genderId;
            $dob = Carbon::createFromTimestamp($PatientDetails->patientDOB);
            $now = Carbon::now();
            $diff = $dob->diff($now);

            $years = $diff->y;
            $months = $diff->m;
            $days = $diff->d;

            $ageString = "";
            if ($years > 0) {
                $ageString .= $years . " " . ($years == 1 ? "year" : "years");
            }
            if ($months > 0) {
                $ageString .= " " . $months . " " . ($months == 1 ? "month" : "months");
            }
            if ($days > 0) {
                $ageString .= " " . $days . " " . ($days == 1 ? "day" : "days");
            }
            $Age = $ageString;

            $Service = $PatientDetails->serviceId;
            $Org = $PatientDetails->OrgId;
            $Site = $PatientDetails->SiteId;
            $ServiceModeId = $PatientDetails->serviceModeId;
            $ServiceTypeId = $PatientDetails->serviceTypeId;
            $ServiceGroupId = $PatientDetails->serviceGroupId;
            $ResponsiblePhysician = $PatientDetails->empID;
            $BillingCC = $PatientDetails->billingCCId;
        }


        $InvTransactionType = trim($request->input('rmc_transaction_type'));
        $SourceLocation = ($request->input('rmc_source_location'));
        $DestinationLocation = ($request->input('rmc_destination_location'));

        $InvGeneric =  implode(',',($request->input('rmc_inv_generic')));
        $Dose =  implode(',',($request->input('rmc_dose')));
        $Route =  implode(',',($request->input('rmc_route')));
        $Frequency =  implode(',',($request->input('rmc_frequency')));
        $Days =  implode(',',($request->input('rmc_days')));

        $Remarks = trim($request->input('rmc_remarks'));


        $Edt = $this->currentDatetime;
        $status = 1;

        $timestamp = $this->currentDatetime;

        $ReqMedicationConsumption = RequisitionForMedicationConsumption::create([
            'transaction_type_id' => $InvTransactionType, 'source_location_id' => $SourceLocation, 'destination_location_id' => $DestinationLocation, 'mr_code' => $MR,
            'gender_id' => $Gender, 'age' => $Age, 'service_id' => $Service, 'org_id' => $Org, 'site_id' => $Site,
            'service_mode_id' => $ServiceModeId, 'service_type_id' => $ServiceTypeId, 'service_group_id' => $ServiceGroupId,
            'responsible_physician' => $ResponsiblePhysician, 'billing_cc' => $BillingCC,
            'inv_generic_ids' => $InvGeneric, 'dose' => $Dose,'route_ids' => $Route, 'frequency_ids' => $Frequency,
            'days' => $Days, 'remarks' => $Remarks, 'user_id' => $sessionId,
            'status' => $status, 'last_updated' => $timestamp, 'timestamp' => $timestamp, 'effective_timestamp' => $Edt
        ]);

        if (!$ReqMedicationConsumption->id) {
            return response()->json(['error' => "Failed to add Requisition for Medication Consumption."]);
        }

        $SiteName = Site::find($Site); // $Site is the site_id
        $SiteName = $SiteName ? $SiteName->name : '';
        $idStr = str_pad($ReqMedicationConsumption->id, 5, "0", STR_PAD_LEFT);
        $firstSiteNameLetters = strtoupper(implode('', array_map(function($word) { return substr($word, 0, 1); }, explode(' ', $SiteName))));
        $RequisitionCode = $firstSiteNameLetters.'-MDC-'.$idStr;
        $ReqMedicationConsumption->code = $RequisitionCode;

        $log = Logs::create([
            'module' => 'patient_medical_record', 'content' => "Requisition For Medication Consumption has been added by '{$sessionName}'",
            'event' => 'add', 'timestamp' => $timestamp,
        ]);
        $ReqMedicationConsumption->logid = $log->id;
        $ReqMedicationConsumption->save();

        return response()->json(['success' => "Requisition for Medication Consumption added successfully"]);
    }

    public function GetRequisitionMedicationConsumption(Request $request, $mr)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->encounters_and_procedures)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        $mr = strpos($mr, '-') === false ? ($orgCode ? $orgCode : 'ZMTP') . '-' . $mr : $mr;

        $RMCDetails = RequisitionForMedicationConsumption::select(
            'req_medication_consumption.*', 'gender.name as gender','patient.name as patientName',
            'employee.name as Physician',
            'organization.organization as OrgName', 'org_site.name as SiteName', 'services.name as serviceName',
            'service_mode.name as serviceMode', 'billingCC.name as billingCC', 'service_group.name as serviceGroup',
            'service_type.name as serviceType','inventory_transaction_type.name as TransactionType',
            'source_location.name as SourceLocationName','destination_location.name as DestinationLocationName'
        )
        ->join('gender', 'gender.id', '=', 'req_medication_consumption.gender_id')
        ->join('costcenter as billingCC', 'billingCC.id', '=', 'req_medication_consumption.billing_cc')
        ->join('employee', 'employee.id', '=', 'req_medication_consumption.responsible_physician')
        ->join('service_mode', 'service_mode.id', '=', 'req_medication_consumption.service_mode_id')
        ->join('services', 'services.id', '=', 'req_medication_consumption.service_id')
        ->join('organization', 'organization.id', '=', 'req_medication_consumption.org_id')
        ->join('org_site', 'org_site.id', '=', 'req_medication_consumption.site_id')
        ->join('service_group', 'service_group.id', '=', 'req_medication_consumption.service_group_id')
        ->join('service_type', 'service_type.id', '=', 'req_medication_consumption.service_type_id')
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'req_medication_consumption.transaction_type_id')
        ->leftJoin('service_location as source_location', 'source_location.id', '=', 'req_medication_consumption.source_location_id')
        ->leftJoin('service_location as destination_location', 'destination_location.id', '=', 'req_medication_consumption.destination_location_id')
        ->join('patient', 'patient.mr_code', '=', 'req_medication_consumption.mr_code')
        ->where('req_medication_consumption.mr_code', $mr)
        ->get();

        return DataTables::of($RMCDetails)
        // return DataTables::eloquent($RMCDetails)
            // ->filter(function ($query) use ($request) {
            //     if ($request->has('search') && $request->search['value']) {
            //         $search = $request->search['value'];
            //         $sanitizedSearch = preg_replace('/[^a-zA-Z0-9]/', '', $search); // remove dashes/spaces

            //         $query->where(function ($q) use ($search, $sanitizedSearch) {
            //             $q->where('req_medication_consumption.id', 'like', "%{$search}%")
            //                 ->orWhere('gender.name', 'like', "%{$search}%")
            //                 ->orWhere('employee.name', 'like', "%{$search}%")
            //                 ->orWhere('organization.organization', 'like', "%{$search}%")
            //                 ->orWhere('org_site.name', 'like', "%{$search}%")
            //                 ->orWhere('services.name', 'like', "%{$search}%")
            //                 ->orWhere('service_mode.name', 'like', "%{$search}%")
            //                 ->orWhere('billingCC.name', 'like', "%{$search}%")
            //                 ->orWhere('service_group.name', 'like', "%{$search}%")
            //                 ->orWhere('service_type.name', 'like', "%{$search}%")
            //                 ->orWhere('inventory_transaction_type.name', 'like', "%{$search}%")
            //                 ->orWhere('req_medication_consumption.dose', 'like', "%{$search}%")
            //                 ->orWhere('req_medication_consumption.days', 'like', "%{$search}%")
            //                 ->orWhere('req_medication_consumption.remarks', 'like', "%{$search}%")
            //                 ->orWhere('req_medication_consumption.effective_timestamp', 'like', "%{$search}%")
            //                 ->orWhere('req_medication_consumption.timestamp', 'like', "%{$search}%")
            //                 ->orWhere('req_medication_consumption.last_updated', 'like', "%{$search}%")
            //                 ->orWhere('req_medication_consumption.mr_code', 'like', "%{$search}%")
            //                 ->orWhereRaw("REPLACE(REPLACE(REPLACE(
            //                 CONCAT(
            //                     UPPER(LEFT(org_site.name, 1)), '-',
            //                     UPPER(LEFT(service_type.name, 1)), '-',
            //                     LPAD(req_medication_consumption.id, 4, '0')
            //                 ), '-', ''), '_', ''), ' ', '') LIKE ?", ["%{$sanitizedSearch}%"]);
            //                 });
            //     }
            // })
            ->addColumn('id_raw', function ($RMCDetail) {
                return $RMCDetail->id;
            })
            ->editColumn('id', function ($RMCDetail) {
                $session = auth()->user();
                $sessionName = $session->name;
                $TransactionType = $RMCDetail->TransactionType;
                $SiteName = $RMCDetail->SiteName;
                $serviceType = $RMCDetail->serviceType;
                $Remarks = !empty($RMCDetail->remarks) ? $RMCDetail->remarks : 'N/A';

                $effectiveDate = Carbon::createFromTimestamp($RMCDetail->effective_timestamp)->format('l d F Y - h:i A');
                $timestamp = Carbon::createFromTimestamp($RMCDetail->timestamp)->format('l d F Y - h:i A');

                // Build location information
                $locationInfo = '';
                if (!empty($RMCDetail->SourceLocationName)) {
                    $locationInfo .= '<br><b>Source Location: </b>' . ucwords($RMCDetail->SourceLocationName);
                }
                if (!empty($RMCDetail->DestinationLocationName)) {
                    $locationInfo .= '<br><b>Destination Location: </b>' . ucwords($RMCDetail->DestinationLocationName);
                }

                $RequisitionCode = $RMCDetail->code;
                return $RequisitionCode
                    . '<hr class="mt-1 mb-2">'
                    .'<b>Request For</b>: '.$TransactionType
                    .$locationInfo
                    .'<br><b>Site</b>: '.$SiteName.'<br>'
                    .'<b>Request Date: </b>: '.$timestamp.'<br>'
                    .'<b>Effective Date: </b>: '.$effectiveDate.'<br>'
                    .'<b>Remarks</b>: '.$Remarks;
            })
            ->editColumn('patientDetails', function ($RMCDetail) {
                // return;
                $MR = $RMCDetail->mr_code;
                $patientName = ucwords($RMCDetail->patientName);
                $Physician = ucwords($RMCDetail->Physician);
                $serviceMode = ucwords($RMCDetail->serviceMode);
                $serviceName = ucwords($RMCDetail->serviceName);
                $serviceGroup = ucwords($RMCDetail->serviceGroup);
                $Physician = ucwords($RMCDetail->Physician);
                $billingCC = ucwords($RMCDetail->billingCC);
                return $MR.'<br>'
                    .$patientName
                    . '<hr class="mt-1 mb-2">'
                    .$serviceMode.'<br>'
                    .$serviceGroup.'<br>'
                    .$serviceName.'<br>'
                    .$Physician.'<br>'
                    .$billingCC.'<br>'
                    ;
            })
            ->editColumn('InventoryDetails', function ($RMCDetail) {
                // Get the comma-separated IDs and values
                $genericIds = explode(',', $RMCDetail->inv_generic_ids);
                $routeIds = explode(',', $RMCDetail->route_ids);
                $frequencyIds = explode(',', $RMCDetail->frequency_ids);
                $dose = explode(',', $RMCDetail->dose);
                $days = explode(',', $RMCDetail->days);

                // Fetch the corresponding names for the IDs (including duplicates)
                $genericNames = InventoryGeneric::whereIn('id', $genericIds)->pluck('name', 'id')->toArray();
                $routeNames = MedicationRoutes::whereIn('id', $routeIds)->pluck('name', 'id')->toArray();
                $frequencyNames = MedicationFrequency::whereIn('id', $frequencyIds)->pluck('name', 'id')->toArray();

                $tableRows = '';
                $maxRows = max(count($genericIds), count($routeIds), count($frequencyIds), count($dose), count($days));

                // Loop through the ids to handle each corresponding name and value
                for ($i = 0; $i < $maxRows; $i++) {
                    // Ensure no index out of range error and default to 'N/A' if not available
                    $genericName = isset($genericIds[$i]) && isset($genericNames[$genericIds[$i]]) ? $genericNames[$genericIds[$i]] : 'N/A';
                    $routeName = isset($routeIds[$i]) && isset($routeNames[$routeIds[$i]]) ? $routeNames[$routeIds[$i]] : 'N/A';
                    $frequencyName = isset($frequencyIds[$i]) && isset($frequencyNames[$frequencyIds[$i]]) ? $frequencyNames[$frequencyIds[$i]] : 'N/A';
                    $doseName = isset($dose[$i]) ? $dose[$i] : 'N/A';  // Get dose from the exploded array
                    $daysValue = isset($days[$i]) ? $days[$i] : 'N/A';  // Get days from the exploded array

                    // Add row for each item
                    $tableRows .= '
                        <tr>
                            <td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . $genericName . '</td>
                            <td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . $doseName . '</td>
                            <td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . $routeName . '</td>
                            <td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . $frequencyName . '</td>
                            <td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . $daysValue . '</td>
                        </tr>';
                }

                // Return the table structure with dynamic rows
                return '
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Generic Name</th>
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Dose</th>
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Route</th>
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Frequency</th>
                                <th style="padding: 5px 15px 5px 5px;border: 1px solid grey;">Duration (Days)</th>
                            </tr>
                        </thead>
                        <tbody>' . $tableRows . '</tbody>
                    </table>';
            })
            ->addColumn('action', function ($RMCDetail) {
                $RMCId = $RMCDetail->id;
                $logId = $RMCDetail->logid;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->encounters_and_procedures)[2];
                $actionButtons = '';
                if ($edit == 1) {
                    $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-reqmc mb-2" data-reqmc-id="'.$RMCId.'">'
                    . '<i class="fa fa-edit"></i> Edit'
                    . '</button>';
                }

                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';

                return $RMCDetail->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->editColumn('status', function ($RMCDetail) {
                $rights = $this->rights;
                $updateStatus = explode(',', $rights->encounters_and_procedures)[3];
                return $updateStatus == 1 ? ($RMCDetail->status ? '<span class="label label-success rmc_status cursor-pointer" data-id="'.$RMCDetail->id.'" data-status="'.$RMCDetail->status.'">Active</span>' : '<span class="label label-danger rmc_status cursor-pointer" data-id="'.$RMCDetail->id.'" data-status="'.$RMCDetail->status.'">Inactive</span>') : ($RMCDetail->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>');
            })
            ->rawColumns(['id_raw','id','patientDetails','InventoryDetails','action','status'])
            ->make(true);
    }

    public function UpdateRequisitionMedicationConsumptionStatus(Request $request)
    {
        $rights = $this->rights;
        $UpdateStatus = explode(',', $rights->encounters_and_procedures)[3];
        if($UpdateStatus == 0)
        {
            abort(403, 'Forbidden');
        }
        $ID = $request->input('id');
        $Status = $request->input('status');
        $CurrentTimestamp = $this->currentDatetime;
        $ReqMC = RequisitionForMedicationConsumption::find($ID);

        if($Status == 0)
        {
            $UpdateStatus = 1;
            $statusLog = 'Active';
            $ReqMC->effective_timestamp = $CurrentTimestamp;
        }
        else{
            $UpdateStatus = 0;
            $statusLog = 'Inactive';
            $ReqMC->effective_timestamp = 0;

        }
        $ReqMC->status = $UpdateStatus;
        $ReqMC->last_updated = $CurrentTimestamp;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);
        $ReqMCLog = RequisitionForMedicationConsumption::where('id', $ID)->first();
        $logIds = $ReqMCLog->logid ? explode(',', $ReqMCLog->logid) : [];
        $logIds[] = $logs->id;
        $ReqMCLog->logid = implode(',', $logIds);
        $ReqMCLog->save();

        $ReqMC->save();
        return response()->json(['success' => true, 200]);
    }

    public function UpdateReqMedicationConsumptionModal($id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->encounters_and_procedures)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }
        $ReqMC = RequisitionForMedicationConsumption::select('req_medication_consumption.*',
        'inventory_transaction_type.name as TransactionTypeName',
        'source_location.name as SourceLocationName','destination_location.name as DestinationLocationName'
        // 'inventory_generic.name as Generic',
        // 'medication_routes.name as RouteName','medication_frequency.name as FrequencyName',
        )
        ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'req_medication_consumption.transaction_type_id')
        ->leftJoin('service_location as source_location', 'source_location.id', '=', 'req_medication_consumption.source_location_id')
        ->leftJoin('service_location as destination_location', 'destination_location.id', '=', 'req_medication_consumption.destination_location_id')
        // ->join('inventory_generic', 'inventory_generic.id', '=', 'req_medication_consumption.inv_generic_id')
        // ->join('medication_routes', 'medication_routes.id', '=', 'req_medication_consumption.route')
        // ->join('medication_frequency', 'medication_frequency.id', '=', 'req_medication_consumption.frequency')
        ->where('req_medication_consumption.id', $id)
        ->first();
        // dd($ReqMC);
        $InvGenericIds = explode(',', $ReqMC->inv_generic_ids);
        $genericNames = [];
        foreach ($InvGenericIds as $genericId) {
            $generic = InventoryGeneric::find($genericId);
            if ($generic) {
                $genericNames[] = $generic->name;
            }
        }
        $combinedGenericNames = implode(',', $genericNames);
        $ReqMC->genericNames = $combinedGenericNames;

        $RouteIds = explode(',', $ReqMC->route_ids);
        $routeNames = [];
        foreach ($RouteIds as $RouteId) {
            $route = MedicationRoutes::find($RouteId);
            if ($route) {
                $routeNames[] = $route->name;
            }
        }
        $combinedRouteNames = implode(',', $routeNames);
        $ReqMC->routeNames = $combinedRouteNames;

        $frequencyIds = explode(',', $ReqMC->frequency_ids);
        $frequencyNames = [];
        foreach ($frequencyIds as $frequencyId) {
            $frequency = MedicationFrequency::find($frequencyId);
            if ($frequency) {
                $frequencyNames[] = $frequency->name;
            }
        }
        $combinedFrequencyNames = implode(',', $frequencyNames);
        $ReqMC->frequencyNames = $combinedFrequencyNames;

        $data = [
            'id' => $ReqMC->id,
            'siteId' => $ReqMC->site_id,
            'orgId' => $ReqMC->org_id,
            'Dose' => $ReqMC->dose,
            'Days' => $ReqMC->days,
            'Remarks' => ucwords($ReqMC->remarks),
            'TransactionTypeId' => $ReqMC->transaction_type_id,
            'TransactionType' => ucwords($ReqMC->TransactionTypeName),
            'SourceLocationId' => $ReqMC->source_location_id,
            'SourceLocationName' => ucwords($ReqMC->SourceLocationName),
            'DestinationLocationId' => $ReqMC->destination_location_id,
            'DestinationLocationName' => ucwords($ReqMC->DestinationLocationName),
            'genericIds' => $ReqMC->inv_generic_ids,
            'genericNames' => ucwords($ReqMC->genericNames),
            'routeIds' => $ReqMC->route_ids,
            'routeNames' => ucwords($ReqMC->routeNames),
            'frequencyIds' => $ReqMC->frequency_ids,
            'frequencyNames' => ucwords($ReqMC->frequencyNames),
        ];

        return response()->json($data);
    }

    public function UpdateReqMedicationConsumption(Request $request, $id)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->encounters_and_procedures)[2];
        if($edit == 0)
        {
            abort(403, 'Forbidden');
        }

        $ReqMC = RequisitionForMedicationConsumption::findOrFail($id);

        $ReqMC->transaction_type_id = $request->input('u_rmc_transaction_type');
        $ReqMC->source_location_id = $request->input('u_rmc_source_location');
        $ReqMC->destination_location_id = $request->input('u_rmc_destination_location');
        $ReqMC->remarks = $request->input('u_rmc_remarks');

        $ReqMC->inv_generic_ids = implode(',',($request->input('u_rmc_inv_generic')));
        $ReqMC->dose = implode(',',($request->input('u_rmc_dose')));
        $ReqMC->route_ids = implode(',',($request->input('u_rmc_route')));
        $ReqMC->frequency_ids = implode(',',($request->input('u_rmc_frequency')));
        $ReqMC->days = implode(',',($request->input('u_rmc_days')));

        $ReqMC->last_updated = $this->currentDatetime;

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $ReqMC->save();

        if (empty($ReqMC->id)) {
            return response()->json(['error' => 'Failed to update Requisition For Medication Consumption. Please try again']);
        }
        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Data has been updated by '{$sessionName}'",
            'event' => 'update',
            'timestamp' => $this->currentDatetime,
        ]);

        $ReqMCLog = RequisitionForMedicationConsumption::where('id', $ReqMC->id)->first();
        $logIds = $ReqMCLog->logid ? explode(',', $ReqMCLog->logid) : [];
        $logIds[] = $logs->id;
        $ReqMCLog->logid = implode(',', $logIds);
        $ReqMCLog->save();
        return response()->json(['success' => 'Requisition For Medication Consumption updated successfully']);
    }


    public function ShowInvestigationTracking($mr)
    {
        $colName = 'investigation_tracking';
        if (PermissionDenied($colName)) {
            abort(403);
        }
        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        $mr = strpos($mr, '-') === false ? ($orgCode ? $orgCode : 'ZMTP') . '-' . $mr : $mr;

        $ServiceLocations = ServiceLocation::select('id', 'name')->where('status', 1)->get();
        $PatientDetails = PatientRegistration::select(
            'patient.name as patientName', 'gender.name as gender','organization.organization as orgName',
            'organization.id as orgId',
            'org_site.name as siteName','patient.dob as patientDOB','patient.mr_code as patientMR',
            'employee.name as responsiblePhysician','billingCC.name as billingCCName',
            'patient_inout.status as patientInOutStatus'
        )
        ->join('gender', 'gender.id', '=', 'patient.gender_id')
        ->join('organization', 'organization.id', '=', 'patient.org_id')
        ->join('org_site', 'org_site.id', '=', 'patient.site_id')
        ->leftjoin('patient_inout', 'patient_inout.mr_code', '=', 'patient.mr_code')
        ->leftjoin('employee', 'employee.id', '=', 'patient_inout.emp_id')
        ->leftjoin('costcenter as billingCC', 'billingCC.id', '=', 'patient_inout.billing_cc')
        ->where('patient.status', 1)
        ->when(
            PatientArrivalDeparture::where([
                ['mr_code', '=', $mr],
                ['status', '=', 1]
            ])->exists(),
            function ($query) {
                $query->where('patient_inout.status', 1);
            }
        )
        ->where('patient.mr_code', $mr)
        ->first();
        $canAdd = $PatientDetails && $PatientDetails->patientInOutStatus == 1;

        $dob = Carbon::createFromTimestamp($PatientDetails->patientDOB);
        $now = Carbon::now();
        $diff = $dob->diff($now);

        $years = $diff->y;
        $months = $diff->m;
        $days = $diff->d;

        $ageString = "";
        if ($years > 0) {
            $ageString .= $years . " " . ($years == 1 ? "year" : "years");
        }
        if ($months > 0) {
            $ageString .= " " . $months . " " . ($months == 1 ? "month" : "months");
        }
        if ($days > 0) {
            $ageString .= " " . $days . " " . ($days == 1 ? "day" : "days");
        }

        return view('dashboard.investigation-tracking', compact('user','PatientDetails','ageString','ServiceLocations','canAdd'));
        // return view('dashboard.investigation-tracking', compact('user','ServiceLocations'));
    }

    public function ConfirmSampleReport(SampleTrackingRequest $request)
    {
        if (explode(',', $this->rights->investigation_tracking)[1] == 0) {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionId = $session->id;
        $sessionName = $session->name;

        $investigationId = decrypt($request->input('investigation_id'));
        $Age = decrypt($request->input('it_age'));
        $confirmDateTime = $request->input('it_confirmation');
        $confirmDateTime = Carbon::createFromFormat('l d F Y - h:i A', $confirmDateTime)->timestamp;
        $Remarks = trim($request->input('sample_remarks'));

        $reqEpi = RequisitionForEPI::find($investigationId);
        if (! $reqEpi) {
            return response()->json(['error' => 'Invalid investigation.']);
        }

        $inOut = PatientArrivalDeparture::where('mr_code',$reqEpi->mr_code)
        ->where('service_id',$reqEpi->service_id)
        ->where('service_mode_id',$reqEpi->service_mode_id)
        ->where('billing_cc',$reqEpi->billing_cc)
        ->where('emp_id',$reqEpi->emp_id)
        ->first();

        if (! $inOut) {
            return response()->json(['error' => "{$reqEpi->mr_code} - Patient not arrived yet"]);
        }

        $inOut->service_end_time = $confirmDateTime;
        $confirmationDateTime = Carbon::createFromTimestamp($confirmDateTime)->setTimezone('Asia/Karachi');
        $confirmationDateTime->subMinute(1);
        if ($confirmationDateTime->isPast()) {
            $status = 0;
        } else {
            $status = 1;
        }

        $inOut->status = $status;
        $timestamp = $this->currentDatetime;

        if($inOut->save()){

            $reqEpiQuery = RequisitionForEPI::where('mr_code', $reqEpi->mr_code)
            ->where('service_id', $reqEpi->service_id)
            ->where('service_mode_id', $reqEpi->service_mode_id)
            ->where('billing_cc', $reqEpi->billing_cc)
            ->where('emp_id', $reqEpi->emp_id)
            ->where('status', 1);

            $matchingReqs = $reqEpiQuery->get();

            if ($matchingReqs->isNotEmpty()) {
                $log = Logs::create([
                    'module' => 'patient_medical_record',
                    'content' => "Status set to Inactive by '{$sessionName}'",
                    'event' => 'update',
                    'timestamp' => $timestamp,
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

        $SampleConfirmatation = InvestigationTracking::create([
            'investigation_id' => $investigationId,  'age' => $Age,  'investigation_confirmation_datetime' => $confirmDateTime,'confirmation_remarks' => $Remarks,
            'user_id' => $sessionId,'last_updated' => $timestamp, 'timestamp' => $timestamp
        ]);

        // $InvestigationTracking = InvestigationTracking::create([
        //     'mr_code' => $MR,'gender_id' => $Gender, 'age' => $Age, 'org_id' => $Org, 'site_id' => $Site, 'service_id' => $Service,
        //     'service_mode_id' => $ServiceModeId, 'service_type_id' => $ServiceTypeId, 'service_group_id' => $ServiceGroupId,
        //     'responsible_physician' => $ResponsiblePhysician, 'billing_cc' => $BillingCC,
        //     'performing_cc' => $performingCC,'report' => null, 'investigation_confirmation_datetime' => $confirmationDateTime,'reporting_datetime' => $timestamp,
        //     'remarks' => $Remarks, 'user_id' => $sessionId,'last_updated' => $timestamp, 'timestamp' => $timestamp, 'effective_timestamp' => $Edt
        // ]);

        if (!$SampleConfirmatation->id) {
            return response()->json(['error' => "Failed to add Investigation Confirmation Details."]);
        }

        $log = Logs::create([
            'module' => 'patient_medical_record', 'content' => "Investigation Confirmed by '{$sessionName}'",
            'event' => 'add', 'timestamp' => $timestamp,
        ]);
        $SampleConfirmatation->logid = $log->id;
        $SampleConfirmatation->save();

        // $filePaths = [];
        // if ($request->hasFile('it_report')) {
        //     $uploadedFiles = $request->file('it_report');
        //     $lastId = $SampleConfirmatation->id;

        //     foreach ($uploadedFiles as $file) {
        //         $insertFileName = time() . '_' . $file->getClientOriginalName();
        //         $uniqueFileName = $lastId . '_' .time() . '_' . $file->getClientOriginalName();
        //         $file->move(public_path('assets/investigationtracking'), $uniqueFileName);
        //         $filePaths[] = $insertFileName;
        //     }
        // }

        // $fileAttachments = implode(',', $filePaths);
        // $SampleConfirmatation->report = $fileAttachments;
        $SampleConfirmatation->save();

        return response()->json(['success' => "Investigation Confirmation details added successfully"]);
    }

    public function UploadReport(UploadReportRequest $request)
    {
        if (explode(',', $this->rights->investigation_tracking)[2] == 0) {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionId = $session->id;
        $sessionName = $session->name;

        $investigationId = decrypt($request->input('investigation_id'));
        $remarks = trim($request->input('report_remarks'));
        $timestamp = $this->currentDatetime;

        // Check if investigation exists
        $investigation = InvestigationTracking::where('investigation_id', $investigationId)->first();

        if (!$investigation) {
            return response()->json(['error' => "Investigation record not found."]);
        }

        // Create a log entry
        $log = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Report added by '{$sessionName}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);

        // Handle file uploads
        $filePaths = [];
        if ($request->hasFile('it_report')) {
            $uploadedFiles = $request->file('it_report');

            foreach ($uploadedFiles as $file) {
                $uniqueFileName = $investigation->id . '_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/investigationtracking'), $uniqueFileName);
                $filePaths[] = $uniqueFileName;
            }
        }

        $fileAttachments = implode(',', $filePaths);

        // Update the existing investigation record
        $existingLogIds = $investigation->logid;

        $newLogId = $log->id;
        if ($existingLogIds) {
            $logidUpdated = $existingLogIds . ',' . $newLogId;
        } else {
            $logidUpdated = $newLogId;
        }

        // Update the existing investigation record
        $investigation->update([
            'report_remarks' => $remarks,
            'reporting_datetime' => $timestamp,
            'report' => $fileAttachments,
            'logid' => $logidUpdated,
            'last_updated' => $timestamp,
        ]);

        return response()->json(['success' => "Report details updated successfully"]);
    }


    public function GetInvestigationTrackingData(Request $request, $mr)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->investigation_tracking)[0];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $user = auth()->user();
        $UserorgId = $user->org_id;
        $orgCode = Organization::where('id', $UserorgId)->value('code');
        $mr = strpos($mr, '-') === false ? ($orgCode ? $orgCode : 'ZMTP') . '-' . $mr : $mr;
        $RequisitionForEPIDetails = RequisitionForEPI::select('req_epi.*',
        'services.name as serviceName','service_mode.name as serviceModeName',
        'costcenter.name as billingCC','employee.name as empName',
        'org_site.name as siteName', 'investigation_tracking.logid as investigationTrackingLogID')
        ->join('org_site', 'org_site.id', '=', 'req_epi.site_id')
        ->join('services', 'services.id', '=', 'req_epi.service_id')
        ->join('costcenter', 'costcenter.id', '=', 'req_epi.billing_cc')
        ->join('service_mode', 'service_mode.id', '=', 'req_epi.service_mode_id')
        ->leftjoin('employee', 'employee.id', '=', 'req_epi.emp_id')
        ->leftjoin('investigation_tracking', 'investigation_tracking.investigation_id', '=', 'req_epi.id')
        ->where('req_epi.action', 'i')
        ->where('req_epi.mr_code', $mr)
        ->orderBy('req_epi.id', 'desc');
        // ->get();


        // return DataTables::of($RequisitionForEPIDetails)
        return DataTables::eloquent($RequisitionForEPIDetails)
            ->addColumn('id_raw', function ($RequisitionForEPIDetail) {
                return $RequisitionForEPIDetail->id;
            })
            ->addColumn('id', function ($RequisitionForEPIDetail) {
                $mrCode = $RequisitionForEPIDetail->mr_code;
                $Age = $RequisitionForEPIDetail->patient_age;
                $empName = $RequisitionForEPIDetail->empName;
                $empName = !empty($RequisitionForEPIDetail->empName) ? $RequisitionForEPIDetail->empName : 'N/A';
                $billingCC = $RequisitionForEPIDetail->billingCC;
                $Remarks = !empty($RequisitionForEPIDetail->remarks) ? $RequisitionForEPIDetail->remarks : 'N/A';
                $siteName = !empty($RequisitionForEPIDetail->siteName) ? $RequisitionForEPIDetail->siteName : 'N/A';
                return $mrCode.'<hr class="mt-1 mb-2">'
                .'<b>Age:</b> '.ucwords($Age)
                .'<hr class="mt-1 mb-2"><b>Speciality BillingCC :</b> '.ucwords($billingCC)
                .'<hr class="mt-1 mb-2"><b>Physician:</b> '.ucwords($empName)
                .'<hr class="mt-1 mb-2"><b>Site :</b> '.ucwords($siteName)
                .'<hr class="mt-1 mb-2"><b>Remarks :</b> '.ucwords($Remarks);
            })
            ->addColumn('serviceDetails', function ($RequisitionForEPIDetail) {

                $serviceName = $RequisitionForEPIDetail->serviceName;
                $serviceModeName = $RequisitionForEPIDetail->serviceModeName;
                // return ucwords($serviceName);
                return '<b>Service:</b> '.ucwords($serviceName)
                .'<hr class="mt-1 mb-2"><b>Service Mode:</b> '.ucwords($serviceModeName);
            })
            ->addColumn('report', function ($RequisitionForEPIDetail) {
                $actionButtons = '';
                $patientArrived = PatientArrivalDeparture::where('mr_code', $RequisitionForEPIDetail->mr_code)
                ->where('service_id', $RequisitionForEPIDetail->service_id)
                ->where('service_mode_id', $RequisitionForEPIDetail->service_mode_id)
                ->where('billing_cc', $RequisitionForEPIDetail->billing_cc)
                ->where('emp_id', $RequisitionForEPIDetail->emp_id)
                ->exists();
                if (! $patientArrived) {
                    return '<i class="mdi mdi-account-clock" style="font-size:18px"></i> <span class="text-danger" style="font-weight:900;">Patient not arrived yet</span>';
                }
                $investigationRecord = InvestigationTracking::where('investigation_id', $RequisitionForEPIDetail->id)
                ->whereNotNull('investigation_confirmation_datetime')
                ->first();

                $Rights = $this->rights;
                $confirmSample = explode(',', $Rights->investigation_tracking)[1];
                $uploadReport = explode(',', $Rights->investigation_tracking)[2];

                if ($investigationRecord) {

                    $confirmationDateTime = Carbon::createFromTimestamp($investigationRecord->investigation_confirmation_datetime)->format('l d F Y - h:i A');

                    $sampleRemarks = !empty($investigationRecord->confirmation_remarks)
                        ? ucfirst($investigationRecord->confirmation_remarks)
                        : 'N/A';

                    $actionButtons .= '<b>Investigation Confirmed at: ' . $confirmationDateTime . '</b><br><br>
                        Sample Remarks: <b>' . $sampleRemarks . '</b><br><hr class="mt-1 mb-2">';

                    if (!empty($investigationRecord->report)) {
                        $reportPath = asset('assets/investigationtracking/' . $investigationRecord->report);

                        $reportingDateTime = Carbon::createFromTimestamp($investigationRecord->reporting_datetime)->format('l d F Y - h:i A');

                        $reportRemarks = !empty($investigationRecord->report_remarks)
                        ? ucfirst($investigationRecord->report_remarks)
                        : 'N/A';

                        $actionButtons .= '<b>Reporting Date&Time: ' . $reportingDateTime . '</b><br><br>
                        Report Remarks: <b>' . $reportRemarks . '</b><br><hr class="mt-1 mb-2">';

                        $actionButtons .= '<a href="' . $reportPath . '" download class="btn btn-info p-2">
                            <i class="fa fa-download"></i> Download Report
                        </a><br><br>';
                    }
                    else{
                        if($uploadReport == 1)
                        {
                            $actionButtons .= '<button type="button" class="btn btn-success p-2 upload-report"
                            data-id="' . encrypt($RequisitionForEPIDetail->id) . '">
                            <i class="mdi mdi-upload"></i> Upload Report
                            </button><br><br>';
                        }
                        else
                        {
                            $actionButtons .= '<i class="mdi mdi-security" style="font-size:18px"></i><span class="text-danger" style="font-weight:900;">Access Restricted</span>';
                        }
                    }

                }
                else {
                    if($confirmSample == 1)
                    {
                        $actionButtons .= '<button type="button" class="btn btn-primary p-2 confirm-sample"
                            data-id="' . encrypt($RequisitionForEPIDetail->id) . '"
                            data-age="' . encrypt($RequisitionForEPIDetail->patient_age) . '">
                            <i class="mdi mdi-medical-bag"></i> Investigation Confirmation
                        </button>';
                    }
                    else
                    {
                        $actionButtons .= '<i class="mdi mdi-security" style="font-size:18px"></i><span class="text-danger" style="font-weight:900;">Access Restricted</span>';
                    }

                }

                return $actionButtons;
                // return $RequisitionForEPIDetail->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->addColumn('action', function ($RequisitionForEPIDetail) {
                $logId = $RequisitionForEPIDetail->investigationTrackingLogID;
                $actionButtons = '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
                . '<i class="fa fa-eye"></i> View Logs'
                . '</button>';

                return $actionButtons;
                // return $RequisitionForEPIDetail->status ? $actionButtons : '<span class="font-weight-bold">Status must be Active to perform any action.</span>';
            })
            ->rawColumns(['id_raw','id','serviceDetails','action','report'])
            ->make(true);

        // $InvestigationTrackingDetails = InvestigationTracking::select(
        //     'investigation_tracking.*', 'patient.name as patientName',
        //     'employee.name as Physician',
        //     'organization.organization as OrgName', 'org_site.name as SiteName', 'services.name as serviceName',
        //     'service_mode.name as serviceMode', 'billingCC.name as billingCC', 'performingCC.name as performingCC', 'service_group.name as serviceGroup',
        //     'service_type.name as serviceType'
        // )
        // ->join('costcenter as billingCC', 'billingCC.id', '=', 'investigation_tracking.billing_cc')
        // ->join('costcenter as performingCC', 'performingCC.id', '=', 'investigation_tracking.performing_cc')
        // ->join('employee', 'employee.id', '=', 'investigation_tracking.responsible_physician')
        // ->join('service_mode', 'service_mode.id', '=', 'investigation_tracking.service_mode_id')
        // ->join('services', 'services.id', '=', 'investigation_tracking.service_id')
        // ->join('organization', 'organization.id', '=', 'investigation_tracking.org_id')
        // ->join('org_site', 'org_site.id', '=', 'investigation_tracking.site_id')
        // ->join('service_group', 'service_group.id', '=', 'investigation_tracking.service_group_id')
        // ->join('service_type', 'service_type.id', '=', 'investigation_tracking.service_type_id')
        // ->join('patient', 'patient.mr_code', '=', 'investigation_tracking.mr_code')
        // ->where('investigation_tracking.mr_code', $mr);

        // $InvestigationTrackingDetails = RequisitionForEPI::select('req_epi.*',
        // 'services.name as serviceName','service_mode.name as serviceModeName',
        // 'costcenter.name as billingCC','employee.name as empName')
        // ->join('services', 'services.id', '=', 'req_epi.service_id')
        // ->join('costcenter', 'costcenter.id', '=', 'req_epi.billing_cc')
        // ->join('service_mode', 'service_mode.id', '=', 'req_epi.service_mode_id')
        // ->leftjoin('employee', 'employee.id', '=', 'req_epi.emp_id')
        // ->where('req_epi.action', 'i')
        // ->orderBy('req_epi.id', 'desc');

        // // return DataTables::of($RMCDetails)
        // return DataTables::eloquent($InvestigationTrackingDetails)
        //     ->filter(function ($query) use ($request) {
        //         if ($request->has('search') && $request->search['value']) {
        //             $search = $request->search['value'];
        //             $query->where(function ($q) use ($search) {
        //                 $q->where('investigation_tracking.id', 'like', "%{$search}%")
        //                 ->orWhere('employee.name', 'like', "%{$search}%")
        //                 ->orWhere('organization.organization', 'like', "%{$search}%")
        //                 ->orWhere('org_site.name', 'like', "%{$search}%")
        //                 ->orWhere('services.name', 'like', "%{$search}%")
        //                 ->orWhere('service_mode.name', 'like', "%{$search}%")
        //                 ->orWhere('billingCC.name', 'like', "%{$search}%")
        //                 ->orWhere('performingCC.name', 'like', "%{$search}%")
        //                 ->orWhere('service_group.name', 'like', "%{$search}%")
        //                 ->orWhere('service_type.name', 'like', "%{$search}%")
        //                 ->orWhere('investigation_tracking.remarks', 'like', "%{$search}%")
        //                 ->orWhere('investigation_tracking.effective_timestamp', 'like', "%{$search}%")
        //                 ->orWhere('investigation_tracking.timestamp', 'like', "%{$search}%")
        //                 ->orWhere('investigation_tracking.last_updated', 'like', "%{$search}%")
        //                 ->orWhere('investigation_tracking.mr_code', 'like', "%{$search}%");
        //             });
        //         }
        //     })
        //     ->addColumn('id_raw', function ($InvestigationTrackingDetail) {
        //         return $InvestigationTrackingDetail->id;
        //     })
        //     ->editColumn('id', function ($InvestigationTrackingDetail) {
        //         $session = auth()->user();
        //         $sessionOrg = $session->org_id;
        //         $sessionName = $session->name;
        //         $MRCode = $InvestigationTrackingDetail->mr_code;
        //         $Age = $InvestigationTrackingDetail->age;
        //         $SiteName = $InvestigationTrackingDetail->SiteName;
        //         $confirmationDateTime = Carbon::createFromTimestamp($InvestigationTrackingDetail->investigation_confirmation_datetime)->format('l d F Y - h:i A');
        //         $ReportingDateTime = Carbon::createFromTimestamp($InvestigationTrackingDetail->reporting_datetime)->format('l d F Y - h:i A');
        //         $Remarks = !empty($InvestigationTrackingDetail->remarks) ? $InvestigationTrackingDetail->remarks : 'N/A';
        //         $OrgName = '';
        //         if($sessionOrg == 0)
        //         {
        //             $OrgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($InvestigationTrackingDetail->OrgName);
        //         }
        //         return $MRCode
        //             . '<hr class="mt-1 mb-2">'
        //             .'<b>Patient Age</b>: '.$Age.'<br>'
        //             .'<b>Organization</b>: '.$OrgName.'<br>'
        //             .'<b>Site</b>: '.$SiteName.'<br>'
        //             .'<b>Reporting DateTime: </b>: '.$ReportingDateTime.'<br>'
        //             .'<b>Confirmation DateTime: </b>: '.$confirmationDateTime
        //             . '<hr class="mt-1 mb-2">'
        //             .'<b>Remarks</b>: '.$Remarks;
        //     })
        //     ->editColumn('serviceDetails', function ($InvestigationTrackingDetail) {
        //         $serviceName = $InvestigationTrackingDetail->serviceName;
        //         $serviceMode = ucwords($InvestigationTrackingDetail->serviceMode);
        //         $serviceType = ucwords($InvestigationTrackingDetail->serviceType);
        //         $billingCC = ucwords($InvestigationTrackingDetail->billingCC);
        //         $performingCC = ucwords($InvestigationTrackingDetail->performingCC);
        //         $serviceGroup = ucwords($InvestigationTrackingDetail->serviceGroup);
        //         $serviceType = ucwords($InvestigationTrackingDetail->serviceType);
        //         $Physician = ucwords($InvestigationTrackingDetail->Physician);

        //         return '<b>Service</b>: '.$serviceName.'<br>'
        //             . '<hr class="mt-1 mb-2">'
        //             .'<b>Service Mode</b>: '.$serviceMode.'<br>'
        //             .'<b>Service Group</b>: '.$serviceGroup.'<br>'
        //             .'<b>Service Type</b>: '.$serviceType.'<br>'
        //             .'<b>Responsible Physician</b>: '.$Physician.'<br>'
        //             .'<b>Billing CC</b>: '.$billingCC.'<br>'
        //             .'<b>Performing CC</b>: '.$performingCC.'<br>'
        //             ;
        //     })
        //     ->editColumn('report', function ($InvestigationTrackingDetail) {
        //         // return;
        //         $id = $InvestigationTrackingDetail->id;
        //         $attachmentPath = $InvestigationTrackingDetail->report;

        //         $actionButtons = '<button type="button" data-id="' . $id . '" data-path="' . $attachmentPath . '" class="btn waves-effect waves-light btn-sm btn-primary downloaditReport">'
        //             . '<i class="fa fa-download"></i> Download Reports'
        //             . '</button>';
        //         return $actionButtons;

        //     })
        //     ->addColumn('action', function ($InvestigationTrackingDetail) {
        //         $InvestigationTrackingId = $InvestigationTrackingDetail->id;
        //         $logId = $InvestigationTrackingDetail->logid;
        //         // $Rights = $this->rights;
        //         // $edit = explode(',', $Rights->encounters_and_procedures)[2];
        //         // $actionButtons = '';
        //         // if ($edit == 1) {
        //         //     $actionButtons .= '<button type="button" class="btn btn-outline-danger mr-2 edit-reqmc" data-reqmc-id="'.$RMCId.'">'
        //         //     . '<i class="fa fa-edit"></i> Edit'
        //         //     . '</button>';
        //         // }

        //         $actionButtons = '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
        //         . '<i class="fa fa-eye"></i> View Logs'
        //         . '</button>';

        //         return $actionButtons;
        //     })
        //     ->rawColumns(['id_raw','id','serviceDetails','report','action'])
        //     ->make(true);
    }

    // public function UpdateRequisitionMedicationConsumptionStatus(Request $request)
    // {
    //     $rights = $this->rights;
    //     $UpdateStatus = explode(',', $rights->encounters_and_procedures)[3];
    //     if($UpdateStatus == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $ID = $request->input('id');
    //     $Status = $request->input('status');
    //     $CurrentTimestamp = $this->currentDatetime;
    //     $ReqMC = RequisitionForMedicationConsumption::find($ID);

    //     if($Status == 0)
    //     {
    //         $UpdateStatus = 1;
    //         $statusLog = 'Active';
    //         $ReqMC->effective_timestamp = $CurrentTimestamp;
    //     }
    //     else{
    //         $UpdateStatus = 0;
    //         $statusLog = 'Inactive';

    //     }
    //     $ReqMC->status = $UpdateStatus;
    //     $ReqMC->last_updated = $CurrentTimestamp;

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $logs = Logs::create([
    //         'module' => 'patient_medical_record',
    //         'content' => "Status updated to '{$statusLog}' by '{$sessionName}'",
    //         'event' => 'update',
    //         'timestamp' => $this->currentDatetime,
    //     ]);
    //     $ReqMCLog = RequisitionForMedicationConsumption::where('id', $ID)->first();
    //     $logIds = $ReqMCLog->logid ? explode(',', $ReqMCLog->logid) : [];
    //     $logIds[] = $logs->id;
    //     $ReqMCLog->logid = implode(',', $logIds);
    //     $ReqMCLog->save();

    //     $ReqMC->save();
    //     return response()->json(['success' => true, 200]);
    // }

    // public function UpdateReqMedicationConsumptionModal($id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->encounters_and_procedures)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }
    //     $ReqMC = RequisitionForMedicationConsumption::select('req_medication_consumption.*',
    //     'inventory_transaction_type.name as TransactionTypeName','service_location.name as ServiceLocationName'
    //     // 'inventory_generic.name as Generic',
    //     // 'medication_routes.name as RouteName','medication_frequency.name as FrequencyName',
    //     )
    //     ->join('inventory_transaction_type', 'inventory_transaction_type.id', '=', 'req_medication_consumption.transaction_type_id')
    //     ->join('service_location', 'service_location.id', '=', 'req_medication_consumption.inv_location_id')
    //     // ->join('inventory_generic', 'inventory_generic.id', '=', 'req_medication_consumption.inv_generic_id')
    //     // ->join('medication_routes', 'medication_routes.id', '=', 'req_medication_consumption.route')
    //     // ->join('medication_frequency', 'medication_frequency.id', '=', 'req_medication_consumption.frequency')
    //     ->where('req_medication_consumption.id', $id)
    //     ->first();
    //     // dd($ReqMC);
    //     $InvGenericIds = explode(',', $ReqMC->inv_generic_ids);
    //     $genericNames = [];
    //     foreach ($InvGenericIds as $genericId) {
    //         $generic = InventoryGeneric::find($genericId);
    //         if ($generic) {
    //             $genericNames[] = $generic->name;
    //         }
    //     }
    //     $combinedGenericNames = implode(',', $genericNames);
    //     $ReqMC->genericNames = $combinedGenericNames;

    //     $RouteIds = explode(',', $ReqMC->route_ids);
    //     $routeNames = [];
    //     foreach ($RouteIds as $RouteId) {
    //         $route = MedicationRoutes::find($RouteId);
    //         if ($route) {
    //             $routeNames[] = $route->name;
    //         }
    //     }
    //     $combinedRouteNames = implode(',', $routeNames);
    //     $ReqMC->routeNames = $combinedRouteNames;

    //     $frequencyIds = explode(',', $ReqMC->frequency_ids);
    //     $frequencyNames = [];
    //     foreach ($frequencyIds as $frequencyId) {
    //         $frequency = MedicationFrequency::find($frequencyId);
    //         if ($frequency) {
    //             $frequencyNames[] = $frequency->name;
    //         }
    //     }
    //     $combinedFrequencyNames = implode(',', $frequencyNames);
    //     $ReqMC->frequencyNames = $combinedFrequencyNames;

    //     $data = [
    //         'id' => $ReqMC->id,
    //         'siteId' => $ReqMC->site_id,
    //         'orgId' => $ReqMC->org_id,
    //         'Dose' => $ReqMC->dose,
    //         'Days' => $ReqMC->days,
    //         'Remarks' => ucwords($ReqMC->remarks),
    //         'TransactionTypeId' => $ReqMC->transaction_type_id,
    //         'TransactionType' => ucwords($ReqMC->TransactionTypeName),
    //         'ServiceLocationId' => $ReqMC->inv_location_id,
    //         'ServiceLocation' => ucwords($ReqMC->ServiceLocationName),
    //         'genericIds' => $ReqMC->inv_generic_ids,
    //         'genericNames' => ucwords($ReqMC->genericNames),
    //         'routeIds' => $ReqMC->route_ids,
    //         'routeNames' => ucwords($ReqMC->routeNames),
    //         'frequencyIds' => $ReqMC->frequency_ids,
    //         'frequencyNames' => ucwords($ReqMC->frequencyNames),
    //     ];

    //     return response()->json($data);
    // }

    // public function UpdateReqMedicationConsumption(Request $request, $id)
    // {
    //     $rights = $this->rights;
    //     $edit = explode(',', $rights->encounters_and_procedures)[2];
    //     if($edit == 0)
    //     {
    //         abort(403, 'Forbidden');
    //     }

    //     $ReqMC = RequisitionForMedicationConsumption::findOrFail($id);

    //     $ReqMC->transaction_type_id = $request->input('u_rmc_transaction_type');
    //     $ReqMC->inv_location_id = $request->input('u_rmc_inv_location');
    //     $ReqMC->remarks = $request->input('u_rmc_remarks');

    //     $ReqMC->inv_generic_ids = implode(',',($request->input('u_rmc_inv_generic')));
    //     $ReqMC->dose = implode(',',($request->input('u_rmc_dose')));
    //     $ReqMC->route_ids = implode(',',($request->input('u_rmc_route')));
    //     $ReqMC->frequency_ids = implode(',',($request->input('u_rmc_frequency')));
    //     $ReqMC->days = implode(',',($request->input('u_rmc_days')));

    //     $ReqMC->last_updated = $this->currentDatetime;

    //     $session = auth()->user();
    //     $sessionName = $session->name;
    //     $sessionId = $session->id;

    //     $ReqMC->save();

    //     if (empty($ReqMC->id)) {
    //         return response()->json(['error' => 'Failed to update Requisition For Medication Consumption. Please try again']);
    //     }
    //     $logs = Logs::create([
    //         'module' => 'patient_medical_record',
    //         'content' => "Data has been updated by '{$sessionName}'",
    //         'event' => 'update',
    //         'timestamp' => $this->currentDatetime,
    //     ]);

    //     $ReqMCLog = RequisitionForMedicationConsumption::where('id', $ReqMC->id)->first();
    //     $logIds = $ReqMCLog->logid ? explode(',', $ReqMCLog->logid) : [];
    //     $logIds[] = $logs->id;
    //     $ReqMCLog->logid = implode(',', $logIds);
    //     $ReqMCLog->save();
    //     return response()->json(['success' => 'Requisition For Medication Consumption updated successfully']);
    // }


    public function ShowProcedureCoding()
    {
        $colName = 'procedure_coding';
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

        return view('dashboard.procedure_coding', compact('user', 'Services', 'Organizations'));
    }

    public function ViewProcedureCoding(Request $request)
    {
        $rights = $this->rights;
        $view = explode(',', $rights->procedure_coding)[0];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }
        $ProceduresData = ServiceActivation::select('procedure_coding.logid as logID',
        'organization.organization as orgName', 'organization.id as orgID',
        'services.name as serviceName', 'services.id as serviceId','procedure_coding.icd_id as icd_id')
        ->join('organization', 'organization.id', '=', 'activated_service.org_id')
        ->join('services', 'services.id', '=', 'activated_service.service_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('service_type', 'service_type.id', '=', 'service_group.type_id')
        ->leftJoin('procedure_coding', 'procedure_coding.service_id', '=', 'services.id')
        // ->leftJoin('icd_code', 'icd_code.id', '=', 'procedure_coding.icd_id')
        ->distinct('services.name')
        ->where('service_type.code', 'p')
        ->orderBy('activated_service.id', 'desc');

        $session = auth()->user();
        $sessionOrg = $session->org_id;
        if($sessionOrg != '0')
        {
            $ProceduresData->where('activated_service.org_id', '=', $sessionOrg);
        }
        $ProceduresData = $ProceduresData;
        // ->get()
        // return DataTables::of($ServiceRequisitions)
        return DataTables::eloquent($ProceduresData)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('activated_service.id', 'like', "%{$search}%")
                        ->orWhere('organization.organization', 'like', "%{$search}%")
                        ->orWhere('services.name', 'like', "%{$search}%")
                        ->orWhere('activated_service.timestamp', 'like', "%{$search}%")
                        ->orWhere('activated_service.effective_timestamp', 'like', "%{$search}%")
                        ->orWhere('activated_service.last_updated', 'like', "%{$search}%")
                        ->orWhere('activated_service.status', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('id_raw', function ($ProcedureData) {
                return $ProcedureData->id;
            })
            ->editColumn('id', function ($ProcedureData) {
                $session = auth()->user();
                $sessionName = $session->name;
                $ServiceName = $ProcedureData->serviceName;
                $sessionOrg = $session->org_id;
                $orgName = '';
                if($sessionOrg == 0)
                {
                    $orgName ='<hr class="mt-1 mb-1"><b>Organization:</b> '.ucwords($ProcedureData->orgName);
                }

                return $ServiceName.$orgName;
            })
            ->editColumn('medical_codes', function ($ProcedureData) {
                $icdIds = $ProcedureData->icd_id;
                if ($icdIds) {
                    $icdIdsArray = explode(',', $icdIds);
                    $ProceduresMedicalCoding = ICDCoding::select('icd_code.code','icd_code.description')
                        ->whereIn('icd_code.id', $icdIdsArray)
                        ->get();
                    if ($ProceduresMedicalCoding->count() > 0) {
                        $table = '<table class="table-condensed"><tbody>';
                        foreach ($ProceduresMedicalCoding as $coding) {
                            $table .= '<tr ><td style="padding: 5px 15px 5px 5px;border: 1px solid grey;">' . $coding->code . ' (' . $coding->description . ')</td></tr>';
                        }
                        $table .= '</tbody></table>';
                    }
                } else {
                    $table = 'N/A';
                }
                return $table;
            })
            ->addColumn('action', function ($ProcedureData) {
                $serviceID = $ProcedureData->serviceId;
                $orgID = $ProcedureData->orgID;
                $logId = $ProcedureData->logID;
                $Rights = $this->rights;
                $edit = explode(',', $Rights->procedure_coding)[1];
                $actionButtons = '';
                $icdIds = $ProcedureData->icd_id;
                if ($icdIds) {
                    $actionButtons .= '<button type="button" class="mb-1 btn btn-success mr-2 assign-medicalcodes" data-org-id="' . $orgID . '" data-service-id="' . $serviceID . '">'
                    . '<i class="fa fa-edit"></i> Update Medical Codes'
                    . '</button>';
                } else {
                    $actionButtons .= '<button type="button" class="mb-1 btn btn-warning mr-2 assign-medicalcodes" data-org-id="' . $orgID . '" data-service-id="' . $serviceID . '">'
                    . '<i class="fa fa-edit"></i> Assign Medical Codes'
                    . '</button>';
                }

                $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="' . $logId . '">'
                    . '<i class="fa fa-eye"></i> View Logs'
                    . '</button>';

                return $actionButtons;
            })
            // ->addColumn('action', function ($ProcedureData) {
            //         $serviceID = $ProcedureData->serviceId;
            //         $orgID = $ProcedureData->orgID;
            //         $logId = $ProcedureData->logID;
            //         $Rights = $this->rights;
            //         $edit = explode(',', $Rights->procedure_coding)[1];
            //         $actionButtons = '';
            //         if ($edit == 1) {
            //             $actionButtons .= '<button type="button" class="btn btn-outline-success mr-2 assign-medicalcodes" data-org-id="'.$orgID.'" data-service-id="'.$serviceID.'">'
            //             . '<i class="fa fa-edit"></i> Mapped Medical Codes'
            //             . '</button>';
            //         }
            //         $actionButtons .= '<button type="button" class="btn btn-outline-info logs-modal" data-log-id="'.$logId.'">'
            //         . '<i class="fa fa-eye"></i> View Logs'
            //         . '</button>';

            //         return $actionButtons;
            // })
            ->rawColumns(['id','action','medical_codes'])
            ->make(true);
    }

    public function GetProcedureMedicalCoding(Request $request)
    {
        $colName = 'procedure_coding';
        if (PermissionDenied($colName)) {
            abort(403);
        }

        $serviceId = $request->input('service_id');
        $orgId = $request->input('org_id');

        $ProceduresMedicalCoding = ICDCoding::select('icd_code.*')
            ->where('icd_code.type', 'p')
            ->where('icd_code.status', 1)
            ->orderBy('icd_code.id', 'desc')
            ->get();

        $mappedICDs = ProcedureCoding::where('service_id', $serviceId)
            ->where('org_id', $orgId)
            ->pluck('icd_id')
            ->first();

        $mappedICDArray = [];
        if ($mappedICDs) {
            $mappedICDArray = explode(',', $mappedICDs);
        }

        return response()->json([
            'ProceduresMedicalCoding' => $ProceduresMedicalCoding,
            'mappedICDArray' => $mappedICDArray
        ]);
    }

    public function InsertUpdateProcedureMedicalCoding(Request $request)
    {
        $rights = $this->rights;
        $edit = explode(',', $rights->procedure_coding)[2];
        if ($edit == 0) {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;
        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;

        $OrgId = trim($request->input('org_id'));
        $ServiceId = trim($request->input('service_id'));
        $MedicalCodingIds = $request->input('mc_value');

        if (is_array($MedicalCodingIds)) {
            $MedicalCodingIds = implode(',', $MedicalCodingIds);
        }

        $existingRecord = ProcedureCoding::where('org_id', $OrgId)
                                        ->where('service_id', $ServiceId)
                                        ->first();

        if ($existingRecord) {
            $existingRecord->icd_id = $MedicalCodingIds;
            $existingRecord->user_id = $sessionId;
            $existingRecord->last_updated = $last_updated;
            $existingRecord->save();

            $logMessage = "Medical Coding updated by '{$sessionName}'";
        } else {
            $ProcedureCodings = new ProcedureCoding();
            $ProcedureCodings->org_id = $OrgId;
            $ProcedureCodings->service_id = $ServiceId;
            $ProcedureCodings->icd_id = $MedicalCodingIds;
            $ProcedureCodings->user_id = $sessionId;
            $ProcedureCodings->last_updated = $last_updated;
            $ProcedureCodings->timestamp = $timestamp;
            $ProcedureCodings->save();

            $logMessage = "Medical Coding mapped by '{$sessionName}'";
        }

        // Create a new log entry
        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => $logMessage,
            'event' => 'activate',
            'timestamp' => $timestamp,
        ]);
        $logId = $logs->id;

        if (!$existingRecord) {
            // New record, directly set the log ID
            $ProcedureCodings->logid = $logId;
            $ProcedureCodings->save();
        } else {
            // Append new log ID to the existing comma-separated log IDs
            $existingLogIds = $existingRecord->logid;
            if (!empty($existingLogIds)) {
                $existingRecord->logid = $existingLogIds . ',' . $logId;
            } else {
                $existingRecord->logid = $logId;
            }
            $existingRecord->save();
        }

        return response()->json(['success' => 'Medical Coding processed successfully']);
    }

    public function AddPatientAttachement(PatientAttachmentRequest $request)
    {
        $rights = $this->rights;
        $add = explode(',', $rights->encounters_and_procedures)[0];
        if ($add == 0) {
            abort(403, 'Forbidden');
        }

        $session = auth()->user();
        $sessionName = $session->name;
        $sessionId = $session->id;

        $MR = trim($request->input('patientmr'));
        $billingCC = trim($request->input('billingcc_id'));
        $ServiceModeID = trim($request->input('servicemode_id'));
        $SeviceId = trim($request->input('sevice_id'));
        $Age = trim($request->input('patient_age'));
        $Physician = trim($request->input('physician'));
        $Description = trim($request->input('pattachement_desc'));
        $attachmentDate = $request->input('pattachement_date');
        $attachmentDate = Carbon::createFromFormat('Y-m-d', $attachmentDate)->timestamp;

        $last_updated = $this->currentDatetime;
        $timestamp = $this->currentDatetime;

        $PatientAttachments = new PatientAttachments();
        $PatientAttachments->mr_code = $MR;
        $PatientAttachments->service_id = $SeviceId;
        $PatientAttachments->service_mode_id = $ServiceModeID;
        $PatientAttachments->billing_cc = $billingCC;
        $PatientAttachments->patient_age = $Age;
        $PatientAttachments->emp_id = $Physician;
        $PatientAttachments->description = $Description;
        $PatientAttachments->date = $attachmentDate;
        $PatientAttachments->attachments = null;
        $PatientAttachments->user_id = $sessionId;
        $PatientAttachments->last_updated = $last_updated;
        $PatientAttachments->timestamp = $timestamp;

        $PatientAttachments->save();

        if (empty($PatientAttachments->id)) {
            return response()->json(['error' => "Failed to add Patient Attachment."]);
        }

        $filePaths = [];
        if ($request->hasFile('patient_attachments')) {
            $uploadedFiles = $request->file('patient_attachments');
            $lastId = $PatientAttachments->id; // Get the last inserted ID

            foreach ($uploadedFiles as $file) {
                $insertFileName = time() . '_' . $file->getClientOriginalName();
                $uniqueFileName = $lastId . '_' .time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/patientattachment'), $uniqueFileName);
                $filePaths[] = $insertFileName;
            }
        }

        $fileAttachments = implode(',', $filePaths);
        $PatientAttachments->attachments = $fileAttachments;
        $PatientAttachments->save();

        $logs = Logs::create([
            'module' => 'patient_medical_record',
            'content' => "Patient Attachment has been added for '{$MR}'",
            'event' => 'add',
            'timestamp' => $timestamp,
        ]);

        $PatientAttachments->logid = $logs->id;
        $PatientAttachments->save();

        return response()->json(['success' => "Patient Attachment added successfully."]);
    }

    public function GetPatientAttachments($mr)
    {

        $rights = $this->rights;
        $view = explode(',', $rights->encounters_and_procedures)[1];
        if($view == 0)
        {
            abort(403, 'Forbidden');
        }

        $mr = trim($mr);

        $user = auth()->user();

        $PatientAttachments = PatientAttachments::select('patient_attachments.*',
        'costcenter.name as Speciality','employee.name as Physician',
        'service_mode.name as serviceMode','service_group.name as serviceGroup')
        ->join('costcenter', 'costcenter.id', '=', 'patient_attachments.billing_cc')
        ->join('service_mode', 'service_mode.id', '=', 'patient_attachments.service_mode_id')
        ->join('services', 'services.id', '=', 'patient_attachments.service_id')
        ->join('service_group', 'service_group.id', '=', 'services.group_id')
        ->join('employee', 'employee.id', '=', 'patient_attachments.emp_id')
        ->where('patient_attachments.mr_code', $mr)
        ->orderBy('patient_attachments.id', 'desc');
        // ->get();

        // return DataTables::of($VisitBasedDetails)
        return DataTables::eloquent($PatientAttachments)
            ->addColumn('id_raw', function ($PatientAttachment) {
                return $PatientAttachment->id;
            })
            ->addColumn('mr', function ($PatientAttachment) {
                $MR = $PatientAttachment->mr_code;
                return $MR;
            })
            ->addColumn('description', function ($PatientAttachment) {
                $Desc = ucwords($PatientAttachment->description);
                return $Desc;
            })
            ->addColumn('date', function ($PatientAttachment) {
                $Date = $PatientAttachment->date;
                $Date = Carbon::createFromTimestamp($Date)->format('d-F-y');
                return $Date;
            })
            ->addColumn('physician', function ($PatientAttachment) {
                $Physician = $PatientAttachment->Physician;
                return $Physician;
            })
            ->editColumn('attachments', function ($PatientAttachment) {
                $id = $PatientAttachment->id;
                $attachmentPath = $PatientAttachment->attachments;

                $actionButtons = '<button type="button" data-id="' . $id . '" data-path="' . $attachmentPath . '" class="btn waves-effect waves-light btn-sm btn-primary downloadattachements">'
                    . '<i class="fa fa-download"></i> Download Attachments'
                    . '</button>';
                return $actionButtons;
            })

            ->rawColumns(['id_raw','mr','description','physician','date','attachments'])
            ->make(true);
    }

    /**
     * Get patients for sidebar investigation tracking
     */
    public function GetPatientsForSidebar()
    {
        try {
            $patients = PatientRegistration::select('mr_code', 'name', 'cell_no')
                ->where('status', 1)
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'patients' => $patients
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
