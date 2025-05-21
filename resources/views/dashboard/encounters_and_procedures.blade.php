<!-- ============================================================== -->
<!-- Start Header  -->
<!-- ============================================================== -->
@include('partials/header')
<!-- ============================================================== -->
<!-- End Header  -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Start Top Bar  -->
<!-- ============================================================== -->
@include('partials/topbar')
<!-- ============================================================== -->
<!-- End Top Bar  -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Start Side Bar  -->
<!-- ============================================================== -->
@include('partials/sidebar')
<!-- ============================================================== -->
<!-- End Side Bar  -->
<!-- ============================================================== -->
<style>.select2-container--default .select2-selection--single{background-color: white;}.select2-container--default .select2-selection--single .select2-selection__arrow b{margin-left: -15px;}.dataTables_wrapper{padding-top:0px;}.form-control{height:30px;min-height:0px;}.table td, .table th {border-color: black;}.table td, .table th{padding: 3px;font-weight: 600;color: black;font-size: 13px;}</style>

<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper" >
    <div class="row page-titles" style="margin:0px;">
        <div class="col-md-8 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Patient Medical Record</li>
                <li class="breadcrumb-item active">Encounters & Procedures</li>
            </ol>
        </div>
    </div>
    @php
    $EncounterProcedures = explode(',', $rights->encounters_and_procedures);
    $add = $EncounterProcedures[0];
    $view = $EncounterProcedures[1];
    $edit = $EncounterProcedures[2];
    $updateStatus = $EncounterProcedures[3];

    $investigationTracking = explode(',', $rights->investigation_tracking);
    $viewinvestigationTracking = $investigationTracking[0];

    @endphp

    <div class="main_row">
        <div class="row main_head" >
            <div class="col">
                @if(empty($user->orgName))
                    <h4 class="text-white">Patient Medical Record</h2>
                @else
                    <h4 class="text-white">{{ ucwords($user->orgName) }}</h2>
                @endif
            </div>

            <div class="col-auto">
                <h4 class="text-white">Encounters & Procedures</h4>
            </div>
        </div>

        <div class="main_row">
            <div class="col-lg-12">
                <div class="form-body">
                    <div class="main_row">
                        {{-- <form id="add_ep" method="post">
                            @csrf --}}

                            @if ($add == 1 || $view == 1)
                            <div class="col-md-12 p-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="main_custom">
                                            <!-- @if(empty($orgCode))
                                            <span class="main_label">ZMTP - </span>
                                            @else
                                            <span class="main_label">{{ $orgCode }} - </span>
                                            @endif
                                            <input type="text" class="form-control input-sm" id="ep_mr" style="width: 60%;" placeholder="MR #" name="ep_mr"> -->
                                            <select class="form-control selecter p-0" name="ep_mr" id="ep_mr" style="color:#222d32">
                                                <option selected disabled >Select MR #</option>
                                                @foreach ($Patients as $Patient)
                                                    <option value="{{ $Patient['mr_code'] }}"> {{ $Patient['mr_code'] }} - {{ ucwords($Patient['name']) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">  
                                        <div class="main_custom">
                                            <label class="main_label">Name</label>
                                            <input type="text" class="form-control input-sm color_red" id="ep_pname" readonly placeholder="Name" >
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="main_custom">
                                            <label class="main_label">Gender</label>
                                            <input type="text" class="form-control input-sm color_red" id="ep_gender" readonly placeholder="Gender" >
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="main_custom">
                                            <label class="main_label">Age</label>
                                            <input type="text" class="form-control input-sm color_red ep_age" name="ep_age" readonly placeholder="Age" >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row p-2" id="billing_details_section" style="position: relative;">
                                <div class="col-md-7">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">Speciality (Billing Cost Center)</label>
                                                <input type="hidden" name="billingcc_id" class="billingcc_id">
                                                <input type="text" class="form-control input-sm color_red" id="ep_bcc" readonly placeholder="Speciality (Billing Cost Center)" >
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">Responsible Physician</label>
                                                <input type="text" class="form-control input-sm color_red" id="ep_emp" readonly placeholder="Responsible Physician" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">Speciality (Performing Cost Center)</label>
                                                <input type="text" class="form-control input-sm color_red"  id="ep_pcc" readonly placeholder="Speciality (Performing Cost Center)">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">User (Entry Made By)</label>
                                                <input type="text" class="form-control input-sm color_red" id="ep_user" value="{{ ucwords($user->name) }}" readonly placeholder="User (Entry Made By)" >
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-5" id="ep_details" style="position: absolute; bottom: 5%; right: 0;">
                                    <div class="col-md-12 text-right p-0">
                                        <span class="main_label col-auto" id="ep_site"></span>
                                    </div>
                                    <div class="col-md-12 text-right p-0">
                                        <input type="hidden" name="servicemode_id" class="servicemode_id">
                                        <span class="main_label col-auto" id="ep_smt"></span>
                                    </div>
                                    <div class="col-md-12 text-right p-0">
                                        <span class="main_label col-auto" id="ep_sgb"></span>
                                    </div>
                                    <div class="col-md-12 text-right p-0 mt-auto">
                                        <input type="hidden" name="sevice_id" class="sevice_id">
                                        <span class="main_label col-auto" id="ep_service"></span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            @if ($add == 1)
                            <form id="add_visitdetails" method="post">
                                <div class="row mt-3" id="ep_history" style="position: relative;">
                                    @csrf
                                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                                    <input type="hidden" name="sevice_id" class="sevice_id">
                                    <input type="hidden" name="patient_age" class="ep_age">
                                    <input type="hidden" name="patientmr" class="patientmr">
                                    <input type="hidden" name="empid" class="empid">

                                    <input type="hidden" id="icdIDs" name="Complaints[]">

                                    <div class="col-md-4 ">
                                        <div class="row mb-5">
                                            <div class="col-md-12">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="main_heading" id="sp_head"></h6>
                                                    <i class="fa fa-plus add_complain addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                </div>
                                                <table class="table-bordered table-hover table" id="view-complain" style="border:none;">
                                                    <thead>
                                                        <tr>
                                                            {{-- <th> --}}
                                                            <th>Code</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                            <div class="col-md-12 mt-1">
                                                <h6 class="main_heading">Clinical Notes</h6>
                                                <textarea class="form-control" required style="height: 100%;" name="clnical_notes" rows="5" maxlength="3000"></textarea>
                                            </div>

                                            <div class="col-md-12 mt-5">
                                                <h6 class="main_heading">Summary / Plan</h6>
                                                <textarea class="form-control" required style="height: 100%;" name="summary" rows="4" maxlength="500"></textarea>
                                            </div>
                                        </div>

                                        <div class="row mt-2 pr-2">
                                            <div class="col-md-4">
                                                <button type="button" data-act="e" class="waves-effect waves-light btn-blue btn-custom encounterModal">Encounter Request</button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" data-act="p" class="waves-effect waves-light btn-blue btn-custom procedureModal">Procedure Planning</button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" data-act="i" class="waves-effect waves-light btn-blue btn-custom investigationModal">Investigation Order</button>
                                            </div>
                                        </div>

                                        <div class="row mt-2 pr-2">
                                            @php
                                                $columnClass = ($viewinvestigationTracking == 1) ? 'col-md-4' : 'col-md-6';
                                            @endphp
                                            <div class="{{ $columnClass }}">
                                                <a href="#" id="order-medication-link" target="_blank">
                                                    <button type="button" class="waves-effect waves-light btn-blue btn-custom" >Medicine Requisition</button>
                                                </a>
                                            </div>
                                            <div class="{{ $columnClass }}">
                                                <button type="button" class="waves-effect waves-light btn-blue btn-custom p_attachmentModal">Attachments</button>
                                            </div>
                                            @if ($viewinvestigationTracking == 1)
                                            <div class="{{ $columnClass }}">
                                                <a href="#" id="investigation-tracking" target="_blank">
                                                    <button type="button" class="waves-effect waves-light btn-blue btn-custom">Investigation Tracking</button>
                                                </a>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="row mt-2 mb-2 pr-2">
                                           {{-- <div class="col-md-4"> --}}
                                                {{-- <a href="#" id="order-medication-link" target="_blank"> --}}
                                                    {{-- <button type="button" class="waves-effect waves-light btn-info btn-custom">Edit or Update</button> --}}
                                                {{-- </a> --}}
                                            {{-- </div> --}}

                                            {{-- <div class="col-md-4">
                                                <button type="button" class="waves-effect waves-light btn-warning btn-custom">View log</button>
                                            </div> --}}

                                            <div class="col-md-12">
                                                <button type="submit" class="addep waves-effect waves-light btn-danger btn-custom">Save</button>
                                            </div>


                                            {{-- <div class="col-md-4">
                                                <a href="{{ route('req-epi', ['act' => 'p']) }}" target="_blank">
                                                    <button type="button" class="waves-effect waves-light btn-info btn-custom">View Procedures</button>
                                                </a>
                                            </div>

                                            <div class="col-md-4">
                                                <a href="{{ route('req-epi', ['act' => 'i']) }}" target="_blank">
                                                    <button type="button" class="waves-effect waves-light btn-info btn-custom">View Investigations</button>
                                                </a>
                                            </div> --}}
                                        </div>

                                        {{-- <div class="row mt-2 pr-4">
                                            <div class="col-md-4">
                                                <button type="button" class="waves-effect waves-light btn-blue btn-custom">Consumables</button>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <button type="button" class="waves-effect waves-light btn-warning btn-custom">View log</button>
                                            </div>

                                            <div class="col-md-4">
                                                <button type="submit" class="waves-effect waves-light btn-danger btn-custom">Save</button>
                                            </div>
                                        </div> --}}
                                        
           
                                    </div>

                                    <div class="col-md-8">
                                        <div class="row mb-2 pr-2">
                                            <div class="col-md-12 p-0">
                                                <h6 class="main_heading">Latest Vital Signs</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-2 mb-1">
                                                    <span class="main_label">SBP</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_sbp" placeholder="SBP">
                                                </div>
        
                                                <div class="col-md-2 mb-1">
                                                    <span class="main_label">DBP</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_dbp"  placeholder="DBP">
                                                </div>
        
                                                <div class="col-md-2 mb-1">
                                                    <span class="main_label">Pulse</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_pulse" placeholder="Pulse">
                                                </div>

                                                <div class="col-md-2 mb-1">
                                                    <span class="main_label">Plain Scr</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_score"  placeholder="Plain Scr">
                                                </div>
        
                                                <div class="col-md-2 mb-1">
                                                    <span class="main_label">Temp</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_temp"  placeholder="Temp">
                                                </div>
        
                                                <div class="col-md-2 mb-1">
                                                    <span class="main_label">R.Rate</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_rrate"  placeholder="R.Rate">
                                                </div>

                                                <div class="col-md-3 mb-1">
                                                    <span class="main_label">Weight</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_weight"  placeholder="Weight">
                                                </div>

                                                <div class="col-md-3 mb-1">
                                                    <span class="main_label">Height</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_height"  placeholder="Height">
                                                </div>

                                                <div class="col-md-3 mb-1">
                                                    <span class="main_label">BMI</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_bmi" placeholder="BMI">
                                                </div>

                                                <div class="col-md-3 mb-1">
                                                    <span class="main_label">BSA</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_bsa" placeholder="BSA">
                                                </div>

                                                <div class="col-md-3 mb-1">
                                                    <span class="main_label">O₂ Saturation</span>
                                                    <input type="text" class="form-control input-sm color_red" readonly id="ep_o2saturation" placeholder="O₂ Saturation">
                                                </div>

                                                <div class="col-md-9 mb-1">
                                                    <span class="main_label">Nursing Notes</span>
                                                    <textarea class="form-control" style="height:80px" id="ep_nursingnotes" readonly rows="3"></textarea>

                                                </div>
                                               
                                            </div>

                                            <div class="row mt-2 mb-2">
                                                <div class="col-md-12 mb-1">
                                                    <div class="d-flex justify-content-between align-items-center pt-3">
                                                        <h6 class="main_heading">Medical Diagnosis</h6>
                                                        <i class="fa fa-plus add_diagnosehistory addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                    </div>
                                                    <table class="tablesaw table-bordered table-hover table" id="view-mdh" style="border:none;" data-tablesaw-mode="swipe">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th>Code</th>  
                                                                <th>Description</th>  
                                                                <th>Since Date</th>
                                                                <th>Till Date</th>
                                                            </tr>
                                                        </thead>
                                                        
                                                    </table>
                                                </div>

                                                <div class="col-md-6 mb-1">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="main_heading">Allergies History</h6>
                                                        <i class="fa fa-plus add_allergieshistory addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                    </div>
                                                    <table class="tablesaw table-bordered table-hover table" id="view-al" style="border:none;" data-tablesaw-mode="swipe">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th>History</th>
                                                                <th>Since Date</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>

                                                <div class="col-md-6 mb-1">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="main_heading">Past History</h6>
                                                        <i class="fa fa-plus add_pasthistory addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                    </div>
                                                    <table class="tablesaw table-bordered table-hover table" id="view-ph" style="border:none;" data-tablesaw-mode="swipe">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                <th>History</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
        
                                                <div class="col-md-6 mb-1">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="main_heading">Immunization History</h6>
                                                        <i class="fa fa-plus add_immunizationhistory addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                    </div>
                                                    <table class="tablesaw table-bordered table-hover table" id="view-ih" style="border:none;" data-tablesaw-mode="swipe">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th>History</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>

                                                <div class="col-md-6 mb-1">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="main_heading">Menstrual / Obstetric History</h6>
                                                        <i class="fa fa-plus add_obsterichistory addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                    </div>
                                                    <table class="tablesaw table-bordered table-hover table" id="view-oh" style="border:none;" data-tablesaw-mode="swipe">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th>History</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
        
                                                <div class="col-md-6 mb-1">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="main_heading">Drug History</h6>
                                                        <i class="fa fa-plus add_drughistory addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                    </div>
                                                    <table class="tablesaw table-bordered table-hover table" id="view-dh" style="border:none;" data-tablesaw-mode="swipe">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                <th>History</th>
                                                                <th>Dose</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>

                                                <div class="col-md-6 mb-1">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="main_heading">Socio-Economic History</h6>
                                                        <i class="fa fa-plus add_socialhistory addep" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
                                                    </div>
                                                    <table class="tablesaw table-bordered table-hover table" id="view-sh" style="border:none;" data-tablesaw-mode="swipe">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                <th>History</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
        
                                            </div>
                                        
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @endif
                        {{-- </form> --}}

                        @if ($view == 1)
                        <div class="row pb-1 mt-2" id="ep_table">
                            <div class="col-md-12 head">
                                <h4 class="main_label text-center main_head text-white p-2">Tracking Visits (Encounters & Procedures)</span>
                            </div>
                            <div class="col-md-12">
                                <table class="tablesaw table-bordered table-hover table" id="view-vbd" style="border:none;" data-tablesaw-mode="swipe">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Date</th>
                                            <th>Service Mode & Group</th>
                                            {{-- <th>Clinical Notes</th> --}}
                                            <th>Summary/Plan</th>
                                            <th>Speciality</th>
                                            <th>Responsible Physician</th>
                                            <th>Details</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>


    @if ($add == 1)
    <div class="modal fade bs-example-modal-lg" id="add-diagnosishistory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Medical Diagnosis</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_diagnosishistory">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" name="billingcc_id" class="billingcc_id">
                                            <input type="hidden" name="servicemode_id" class="servicemode_id">
                                            <input type="hidden" name="sevice_id" class="sevice_id">
                                            <input type="hidden" name="patient_age" class="ep_age">
                                            <input type="hidden" name="patientmr" class="patientmr">


                                            {{-- <div class="col-md-12" id="icd_desc">
                                                <div class="form-group">
                                                    <label class="control-label">ICD Description </label>
                                                        <textarea class="form-control" row="3" disabled style="height: 100%;" id="icdDesc"></textarea>
                                                    </select>
                                                </div>
                                            </div> --}}

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Medical Diagnosis</label>
                                                        <select class="form-control selecter p-0" required placeholder="Medical Diagnosis..." id="m_icddiagnose" name="m_icddiagnose">
                                                        </select>    
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Since Date</label>
                                                    <input type="text" id="m_sincedate" required name="m_sincedate" style="height:40px" class="form-control input-sm" placeholder="Select Date">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Till Date</label>
                                                    <input type="text" id="m_tilledate" required name="m_tilledate" style="height:40px" class="form-control input-sm" placeholder="Select Date">
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Effective DateTime</label>
                                                    <input type="text" name="m_edt" required class="form-control input06 dt" required style="height:40px" placeholder="Select Effective Date & Time">
                                                </div>
                                            </div> --}}
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-lg" id="add-allergieshistory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Allergies History</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_allergieshistory">
                    @csrf
                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                    <input type="hidden" name="sevice_id" class="sevice_id">
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">

                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Allergies History</label>
                                                    <textarea class="form-control" required placeholder="Allergies History..." maxlength="1000" style="height: 100%;" name="allergy_history" rows="3"></textarea>
                                                    
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Since Date</label>
                                                    <input type="text" id="al_sincedate" required name="al_sincedate" style="height:40px" class="form-control input-sm" placeholder="Select Date">
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Effective DateTime</label>
                                                    <input type="text" name="al_edt" class="form-control input06 dt" required style="height:40px" placeholder="Select Effective Date & Time">
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-lg" id="add-immunizationhistory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Immunization History</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_immunizationhistory">
                    @csrf
                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                    <input type="hidden" name="sevice_id" class="sevice_id">
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">

                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Immunization History</label>
                                                    <textarea class="form-control" required placeholder="Immunization History..." maxlength="1000" style="height: 100%;" name="immunizationhistory_history" rows="3"></textarea>
                                                    
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label"> Date</label>
                                                    <input type="text" id="ih_date" required name="ih_date" style="height:40px" class="form-control input-sm" placeholder="Select Date">
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Effective DateTime</label>
                                                    <input type="text" name="ih_edt" class="form-control input06 dt" required style="height:40px" placeholder="Select Effective Date & Time">
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-lg" id="add-drughistory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Drug History</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_drughistory">
                    @csrf
                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                    <input type="hidden" name="sevice_id" class="sevice_id">
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">

                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Drug History</label>
                                                    <textarea class="form-control" required placeholder="Drug History..." maxlength="1000" style="height: 100%;" name="drug_history" rows="3"></textarea>
                                                    
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Dose</label>
                                                    <textarea class="form-control" required placeholder="Dose..." maxlength="1000" style="height: 100%;" name="dh_dose" rows="3"></textarea>
                                                </div>
                                            </div>


                                            {{-- <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Effective DateTime</label>
                                                    <input type="text" name="dh_edt" class="form-control input06 dt" required style="height:40px" placeholder="Select Effective Date & Time">
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-lg" id="add-pasthistory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Past History</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_pasthistory">
                    @csrf
                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                    <input type="hidden" name="sevice_id" class="sevice_id">
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">

                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Past History</label>
                                                    <textarea class="form-control" required placeholder="Past History..." maxlength="1000" style="height: 100%;" name="past_history" rows="3"></textarea>
                                                    
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label"> Date</label>
                                                    <input type="text" id="ph_date" required name="ph_date" style="height:40px" class="form-control input-sm" placeholder="Select Date">
                                                </div>
                                            </div>


                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Effective DateTime</label>
                                                    <input type="text" name="ph_edt" class="form-control input06 dt" required style="height:40px" placeholder="Select Effective Date & Time">
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-lg" id="add-obsterichistory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Menstrual / Obstetric History</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_obsterichistory">
                    @csrf
                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                    <input type="hidden" name="sevice_id" class="sevice_id">
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">

                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Obstetric History</label>
                                                    <textarea class="form-control" required placeholder="Obstetric History..." maxlength="1000" style="height: 100%;" name="obsteric_history" rows="3"></textarea>
                                                    
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label"> Date</label>
                                                    <input type="text" id="oh_date" required name="oh_date" style="height:40px" class="form-control input-sm" placeholder="Select Date">
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Effective DateTime</label>
                                                    <input type="text" name="oh_edt" class="form-control input06 dt" required style="height:40px" placeholder="Select Effective Date & Time">
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
   
    <div class="modal fade bs-example-modal-lg" id="add-socialhistory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Social History</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_socialhistory">
                    @csrf
                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                    <input type="hidden" name="sevice_id" class="sevice_id">
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Social History</label>
                                                    <textarea class="form-control"  required placeholder="Social History..." maxlength="1000" style="height: 100%;" name="social_history" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label"> Date</label>
                                                    <input type="text" id="sh_date" required name="sh_date" style="height:40px" class="form-control input-sm" placeholder="Select Date">
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Effective DateTime</label>
                                                    <input type="text" name="sh_edt" class="form-control input06 dt" required style="height:40px" placeholder="Select Effective Date & Time">
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-complain" tabindex="-1" role="dialog" aria-labelledby="serviceModesModalLabel">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Symptoms</h4>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <!-- Search Input -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <input type="text" id="icd-search" class="form-control" placeholder="Search Medical Code or Description">
                            </div>
                        </div>

                        <div class="row" id="icd-codes-container">
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button class="btn btn-primary load-more" style="display: none;">Load More</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success done" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-xl" id="add-reqe" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Requisition For Encounter</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_reqe" class="addep">
                    @csrf
                    {{-- <input type="hidden" name="billingcc_id" class="billingcc_id"> --}}
                    {{-- <input type="hidden" name="servicemode_id" class="servicemode_id"> --}}
                    {{-- <input type="hidden" name="sevice_id" class="sevice_id"> --}}
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">
                    {{-- <input type="hidden" name="physician" class="physician"> --}}
                    <input type="hidden" name="action" value="e">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row">

                                        @if($user->org_id != 0)
                                        <div class="userOrganization">
                                            <select class="form-contro selecter p-0" id="reqe_org" name="repi_org">
                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                            </select>
                                        </div>
                                        @else
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <!-- <h6 class="box-title font-weight-bold" id="scheduleOrg">Organization:</h6> -->
                                                        <h6 class="box-title font-weight-bold">Organization</h6>
                                                      
                                                        <select class="form-control selecter p-0" id="reqe_org" name="repi_org"  style="color:#222d32">
                                                            <option selected disabled >Select Organization</option>
                                                            @foreach ($Organizations as $Organization)
                                                                <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <span class="text-danger repi_org_error"></span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Site</h6>
                                                        <select class="form-control selecter p-0" id="reqe_site" name="repi_site" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger repi_site_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Services</h6>
                                                        <select class="form-control selecter p-0" id="reqe_sevice" name="sevice_id" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger sevice_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Service Modes</h6>
                                                        <select class="form-control selecter p-0" id="reqe_servicemode" name="servicemode_id" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger servicemode_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Speciality (Billing Cost Center)<h6>
                                                        <select class="form-control selecter p-0" id="reqe_billingcc" name="billingcc_id" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                    <span class="text-danger billingcc_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-0">
                                                        <h6 class="box-title font-weight-bold">Designated Physician <small class="text-danger" style="font-size:11px;">(Optional)</small></h6>
                                                        <select class="form-control selecter p-0" id="reqe_physician" name="physician"  style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="control-label">Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                <textarea class="form-control" placeholder="Remarks..."  style="height: 100%;" name="repi_remarks"></textarea>
                                            </div>

                                        </div>

                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Effective DateTime</label>
                                                <input type="text" name="repi_edt" class="form-control input06 dt"  style="height:40px" placeholder="Select Effective Date & Time">
                                            </div>
                                            <span class="text-danger" id="repi_edt_error"></span>
                                        </div> --}}

                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" style="float:right;" class="btn btn-primary">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div> --}}
                </form>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="view-reqepi table table-bordered table-striped" id="view-reqe" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>MR #</th>
                                        <th>Remarks</th>
                                        <th>Service Details</th>
                                        <th>Billing CC</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-xl" id="add-reqp" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Requisition For Procedure</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_reqp" class="addep">
                    @csrf
                    {{-- <input type="hidden" name="billingcc_id" class="billingcc_id"> --}}
                    {{-- <input type="hidden" name="servicemode_id" class="servicemode_id"> --}}
                    {{-- <input type="hidden" name="sevice_id" class="sevice_id"> --}}
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">
                    {{-- <input type="hidden" name="physician" class="physician"> --}}
                    <input type="hidden" name="action" value="p">
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row">
                                        @if($user->org_id != 0)
                                        <div class="userOrganization">
                                            <select class="form-contro selecter p-0" id="reqp_org" name="repi_org">
                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                            </select>
                                        </div>
                                        @else
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <!-- <h6 class="box-title font-weight-bold" id="scheduleOrg">Organization:</h6> -->
                                                        <h6 class="box-title font-weight-bold">Organization</h6>
                                                      
                                                        <select class="form-control selecter p-0" id="reqp_org" name="repi_org"  style="color:#222d32">
                                                            <option selected disabled >Select Organization</option>
                                                            @foreach ($Organizations as $Organization)
                                                                <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <span class="text-danger repi_org_error"></span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Site</h6>
                                                        <select class="form-control selecter p-0" id="reqp_site" name="repi_site" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger repi_site_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Services</h6>
                                                        <select class="form-control selecter p-0" id="reqp_sevice" name="sevice_id" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger sevice_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Service Modes</h6>
                                                        <select class="form-control selecter p-0" id="reqp_servicemode" name="servicemode_id" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger servicemode_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                                                                
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Speciality (Billing Cost Center)<h6>
                                                        <select class="form-control selecter p-0" id="reqp_billingcc" name="billingcc_id" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                    <span class="text-danger billingcc_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-0">
                                                        <h6 class="box-title font-weight-bold">Designated Physician</h6>
                                                        <select class="form-control selecter p-0" id="reqp_physician" name="physician"  style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger physician_error" ></span>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="control-label">Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                <textarea class="form-control" placeholder="Remarks..."  style="height: 100%;" name="repi_remarks" rows="3"></textarea>
                                            </div>
                                        </div>

                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Effective DateTime</label>
                                                <input type="text" name="repi_edt" class="form-control input06 dt"  style="height:40px" placeholder="Select Effective Date & Time">
                                            </div>
                                            <span class="text-danger" id="repi_edt_error"></span>
                                        </div> --}}

                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" style="float:right;" class="btn btn-primary">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                </form>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="view-reqepi table table-bordered table-striped" id="view-reqp" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>MR #</th>
                                        <th>Remarks</th>
                                        <th>Service Details</th>
                                        <th>Billing CC</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-xl" id="add-reqi" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Requisition For Investigation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_reqi" class="addep">
                    @csrf
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">
                    <input type="hidden" name="action" value="i">
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row">

                                        @if($user->org_id != 0)
                                        <div class="userOrganization">
                                            <select class="form-contro selecter p-0" id="reqi_org" name="repi_org">
                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                            </select>
                                        </div>
                                        @else
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <!-- <h6 class="box-title font-weight-bold" id="scheduleOrg">Organization:</h6> -->
                                                        <h6 class="box-title font-weight-bold">Organization</h6>
                                                      
                                                        <select class="form-control selecter p-0" id="reqi_org" name="repi_org"  style="color:#222d32">
                                                            <option selected disabled >Select Organization</option>
                                                            @foreach ($Organizations as $Organization)
                                                                <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <span class="text-danger repi_org_error"></span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Site</h6>
                                                        <select class="form-control selecter p-0" id="reqi_site" name="repi_site" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger repi_site_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Services</h6>
                                                        <select class="form-control selecter p-0" id="reqi_sevice" name="sevice_id" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger sevice_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Service Modes</h6>
                                                        <select class="form-control selecter p-0" id="reqi_servicemode" name="servicemode_id" style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger servicemode_id_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-5">
                                                        <h6 class="box-title font-weight-bold">Speciality (Billing Cost Center)<h6>
                                                        <select class="form-control selecter p-0" id="reqi_billingcc" name="billingcc_id" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                    <span class="text-danger billingcc_id_error"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group has-custom m-b-0">
                                                        <h6 class="box-title font-weight-bold">Designated Physician</h6>
                                                        <select class="form-control selecter p-0" id="reqi_physician" name="physician"  style="color:#222d32">
                                                        </select>
                                                        
                                                    </div>
                                                    <span class="text-danger physician_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="control-label">Remarks  <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                <textarea class="form-control" placeholder="Remarks..."  style="height: 100%;" name="repi_remarks" rows="3"></textarea>
                                            </div>

                                        </div>

                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Effective DateTime</label>
                                                <input type="text" name="repi_edt" class="form-control input06 dt"  style="height:40px" placeholder="Select Effective Date & Time">
                                            </div>
                                            <span class="text-danger" id="repi_edt_error"></span>
                                        </div> --}}

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" style="float:right;" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="view-reqepi table table-bordered table-striped" id="view-reqi" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>MR #</th>
                                        <th>Remarks</th>
                                        <th>Service Details</th>
                                        <th>Billing CC</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-xl" id="add-pattachments" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Attachments</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="add_patientattachment" class="addep" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="billingcc_id" class="billingcc_id">
                    <input type="hidden" name="servicemode_id" class="servicemode_id">
                    <input type="hidden" name="sevice_id" class="sevice_id">
                    <input type="hidden" name="patient_age" class="ep_age">
                    <input type="hidden" name="patientmr" class="patientmr">
                    <input type="hidden" name="physician" class="physician">
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="control-label">Description</label>
                                                <textarea class="form-control" placeholder="Description..."  style="height: 100%;" name="pattachement_desc" rows="3"></textarea>
                                            </div>
                                            <span class="text-danger" id="pattachement_desc_error"></span>

                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Date</label>
                                                <input type="text" name="pattachement_date" class="form-control input06 do"  style="height:40px" placeholder="Select Effective Date & Time">
                                            </div>
                                            <span class="text-danger" id="pattachement_date_error"></span>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Attachments</label>
                                                <input type="file" id="attachments" name="patient_attachments[]" class="form-control dropify" 
                                                       data-height="100" multiple style="height:80px" />
                                            </div>
                                            <div id="file-names" style="margin-top: 10px; font-size:14px; color:#555;"></div>

                                            <span class="text-danger" id="patient_attachments_error"></span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" style="float:right;" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="view-pattachment" class="table table-bordered table-striped" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>MR #</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Physician</th>
                                        <th>Attachments</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade bs-example-modal-lg" id="view-visitDetails" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">View Tracking Visit Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h5>Symptoms</h5>
                    <ul id="complaints-list">
                    </ul>
                    
                    <h5>Clinical Notes</h5>
                    <p id="visit_clinical_notes">
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade bs-example-modal-lg" id="edit-reqi" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Requisition For EPI</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_reqi">
                    @csrf
                    <input type="hidden" name="req_epiID" id="req_epiID">
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-0">
                                                <label class="control-label">Remarks</label>
                                                <textarea class="form-control" required placeholder="Remarks..." id="u_repi_remarks"  style="height: 100%;" name="u_repi_remarks" rows="3"></textarea>
                                            </div>

                                        </div>

                                        {{-- <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label">Effective DateTime</label>
                                                <input type="text" required name="u_repi_edt" class="form-control input06 dt" id="u_repi_edt"  style="height:40px" placeholder="Select Effective Date & Time">
                                            </div>
                                        </div> --}}

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="service-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select a Service</h5>
                </div>
                <div class="modal-body">
                    <!-- Buttons for service selection will be added dynamically -->
                </div>
            </div>
        </div>
    </div>

    @endif


    <!-- ============================================================== -->
    <!-- Start Footer  -->
    <!-- ============================================================== -->
    @include('partials/footer')
    <!-- ============================================================== -->
    <!-- End Footer  -->
    <!-- ============================================================== -->

    <script>
       $(document).ready(function () {
            const fileNamesContainer = $('#file-names');
            const dropifyInstance = $('.dropify').dropify();
            $('#attachments').on('change', function () {
                fileNamesContainer.empty(); 
                const files = this.files;
                if (files.length > 0) {
                    Array.from(files).forEach(file => {
                        fileNamesContainer.append(`<p>${file.name}</p>`); 
                    });
                }
            });
            dropifyInstance.on('dropify.afterClear', function (event, element) {
                fileNamesContainer.empty();
            });
        });

        $('.dt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date(),
            maxDate: new Date()
        });
        
        $('.do').bootstrapMaterialDatePicker({ weekStart : 0,  currentDate: new Date(), maxDate: new Date(), time: false });
    </script>
    <script src="{{ asset('assets/custom/encounter_procedures.js') }}"></script>