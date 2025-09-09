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
<style>.select2-container--default .select2-selection--single{background-color: white;}.select2-container--default .select2-selection--single .select2-selection__arrow b{margin-left: -15px;}.form-control{height:30px;min-height:0px;}.table td, .table th {border-color: black;}.table td, .table th{text-align: center;padding: 5px;font-weight: 600;color: black;font-size: 13px;}</style>

<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles" style="margin:0px;">
        <div class="col-md-8 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Patient Medical Record</li>
                <li class="breadcrumb-item active">Vital Signs</li>
            </ol>
        </div>
    </div>
    @php
    $VitalSign = explode(',', $rights->vital_signs);
    $add = $VitalSign[0];
    $view = $VitalSign[1];
    $edit = $VitalSign[2];
    $updateStatus = $VitalSign[3];
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
                <h4 class="text-white">Vital Signs</h4>
            </div>
        </div>

        <div class="main_row">
            <div class="col-lg-12">
                <div class="form-body">
                    <div class="main_row">
                        <form id="add_vitalsign" method="post">
                            @csrf

                            @if ($add == 1 || $view == 1)
                            <div class="col-md-12 p-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="main_custom">
                                            <!-- @if(empty($orgCode))
                                            <span class="main_label">ZMTP - </span>
                                            @else
                                            <span class="main_label">{{ $orgCode }} - </span>
                                            @endif -->
                                            <select class="form-control selecter p-0" name="vs_mr" id="vs_mr" style="color:#222d32">
                                                <option selected disabled >Select MR #</option>
                                                @foreach ($Patients as $Patient)
                                                    <option value="{{ $Patient['mr_code'] }}"> {{ $Patient['mr_code'] }} - {{ ucwords($Patient['name']) }}</option>
                                                @endforeach
                                            </select>
                                            <!-- <input type="text" class="form-control input-sm" id="vs_mr" style="width: 60%;" placeholder="MR #" name="vs_mr"> -->
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="main_custom">
                                            <label class="main_label">Name</label>
                                            <input type="text" class="form-control input-sm color_red" id="vs_pname" readonly placeholder="Name" >
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="main_custom">
                                            <label class="main_label">Gender</label>
                                            <input type="text" class="form-control input-sm color_red" id="vs_gender" readonly placeholder="Gender" >
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="main_custom">
                                            <label class="main_label">Age</label>
                                            <input type="text" class="form-control input-sm color_red vs_age" name="vs_age" readonly placeholder="Age" >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row p-2 patientArrivedvs" style="position: relative;">
                                <div class="col-md-7">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">Speciality (Billing Cost Center)</label>
                                                <input type="hidden" name="billingcc_id" class="billingcc_id">
                                                <input type="text" class="form-control input-sm color_red" id="vs_bcc" readonly placeholder="Speciality (Billing Cost Center)" >
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">Responsible Physician</label>
                                                <input type="text" class="form-control input-sm color_red" id="vs_emp" readonly placeholder="Responsible Physician" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">Speciality (Performing Cost Center)</label>
                                                <input type="text" class="form-control input-sm color_red"  id="vs_pcc" readonly placeholder="Speciality (Performing Cost Center)">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div>
                                                <label class="main_label">User (Entry Made By)</label>
                                                <input type="text" class="form-control input-sm color_red" id="vs_user" value="{{ ucwords($user->name) }}" readonly placeholder="User (Entry Made By)" >
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-5" id="vital_details" style="position: absolute; bottom: 5%; right: 0;">
                                    <div class="col-md-12 text-right p-0">
                                        <span class="main_label col-auto" id="vital_site"></span>
                                    </div>
                                    <div class="col-md-12 text-right p-0">
                                        <input type="hidden" name="servicemode_id" class="servicemode_id">
                                        <span class="main_label col-auto" id="vital_smt"></span>
                                    </div>
                                    <div class="col-md-12 text-right p-0">
                                        <span class="main_label col-auto" id="vital_sgb" ></span>
                                    </div>
                                    <div class="col-md-12 text-right p-0 mt-auto">
                                        <input type="hidden" name="sevice_id" class="sevice_id">
                                        <span class="main_label col-auto" id="vital_service"></span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                    @if ($add == 1)
                            <div class="row p-2 patientArrivedvs">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12 head">
                                            <h4 class="main_label text-center main_head text-white p-2">Vital Signs</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <span class="main_label">Date & Time</span>
                                            <input type="text" id="date-format"  name="vs_edt" class="form-control input-sm" placeholder="Select Date & Time">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">SBP (mmhg)</span>
                                            <input type="number" class="form-control input-sm" min="0" max="300" name="vs_sbp" placeholder="SBP">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">DBP (mmhg)</span>
                                            <input type="number" class="form-control input-sm" min="0" max="200" name="vs_dbp" placeholder="DBP">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">Pulse (Per min)</span>
                                            <input type="number" class="form-control input-sm" min="0" max="350" name="vs_pulse" placeholder="Pulse">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">Temperature (F)</span>
                                            <input type="number" class="form-control input-sm" min="90" max="110" name="vs_temp" placeholder="Temperature">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">Resp Rate (Per min)</span>
                                            <input type="number" class="form-control input-sm" min="0" max="50" name="vs_rrate" placeholder="Respiratory Rate">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">Weight (Kg)</span>
                                            <input type="number" class="form-control input-sm" min="0.1" max="250" step="0.1" name="vs_weight" placeholder="Weight">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">Height (cm)</span>
                                            <input type="text" class="form-control input-sm" min="20" max="200" name="vs_height" placeholder="Height">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <span class="main_label">Pain Score (1 -10) </span>
                                            <input type="number" min="1" max="10" class="form-control input-sm" name="vs_score" placeholder="Pain Score">
                                            <span class="text-danger" id="vs_score_error"></span>
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <span class="main_label">O₂ Saturation </span>
                                            <input type="number" min="0" max="100" class="form-control input-sm" name="vs_o2saturation" placeholder="91">
                                            <span class="text-danger" id="vs_o2saturation_error"></span>
                                        </div>


                                      
                                        <div class="col-md-10 mb-2">
                                            <span class="main_label">Nursing Notes <small class="text-danger" style="font-size:11px;">(Optional)</small></span>
                                            <textarea class="form-control" style="height:100px" name="vs_nursingnotes" rows="3"></textarea>
                                        </div>

                                        <div class="col-md-2 text-right" style="position: absolute;bottom: 15px;right: 15px;">
                                            <br>
                                            <button type="submit" class="btn btn-danger">Save</button>
                                        </div>
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
                        </form>

                        <div class="row p-2 vs_history">
                            <div class="col-md-12">
                                {{-- <div class="row mb-2 pr-2"> --}}
                                    <div class="row mt-2 mb-2 p-2">

                                        <div class="col-md-6 mb-3 p-4 border border-primary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="main_heading">Allergies History</h6>
                                                <i class="fa fa-plus add_allergieshistory patientArrivedvs" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
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

                                        <div class="col-md-6 mb-3 p-4 border border-primary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="main_heading">Past History</h6>
                                                <i class="fa fa-plus add_pasthistory patientArrivedvs" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
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

                                        <div class="col-md-6 mb-3 p-4 border border-primary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="main_heading">Immunization History</h6>
                                                <i class="fa fa-plus add_immunizationhistory patientArrivedvs" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
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

                                        <div class="col-md-6 mb-3 p-4 border border-primary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="main_heading">Menstrual / Obstetric History</h6>
                                                <i class="fa fa-plus add_obsterichistory patientArrivedvs" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
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

                                        <div class="col-md-6 mb-3 p-4 border border-primary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="main_heading">Drug History</h6>
                                                <i class="fa fa-plus add_drughistory patientArrivedvs" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
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

                                        <div class="col-md-6 mb-3 p-4 border border-primary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="main_heading">Socio-Economic History</h6>
                                                <i class="fa fa-plus add_socialhistory patientArrivedvs" style="cursor:pointer;font-size:20px;color:#0f0f66;"></i>
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

                                {{-- </div> --}}
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
                                        <input type="hidden" name="patient_age" class="vs_age">
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
                                        <input type="hidden" name="patient_age" class="vs_age">
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
                                        <input type="hidden" name="patient_age" class="vs_age">
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
                                        <input type="hidden" name="patient_age" class="vs_age">
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
                                        <input type="hidden" name="patient_age" class="vs_age">
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
                                        <input type="hidden" name="patient_age" class="vs_age">
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

                    @endif


                        @if ($view == 1)
                        <div class="row pb-1 mt-5 vs_history">
                            <div class="col-md-12 head">
                                <h4 class="main_label text-center main_head text-white p-2">Vital Sign History</span>
                            </div>
                            <div class="col-md-12">
                                <table class="tablesaw table-bordered table-hover table" id="view-vitalsign" style="border:none;" data-tablesaw-mode="swipe">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Date Time</th>
                                            <th>Service Mode & Group</th>
                                            <th>SBP</th>
                                            <th>DBP</th>
                                            <th>Pulse</th>
                                            <th>Temp</th>
                                            <th>R.Rate</th>
                                            <th>Weight</th>
                                            <th>Height</th>
                                            <th>PainScore</th>
                                            <th>O₂ Saturation</th>
                                            <th>BMI</th>
                                            <th>BSA</th>
                                            <th>Age</th>
                                            <th>Details</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                            <th>Log</th>
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


    @if ($edit == 1)
    <div class="modal fade bs-example-modal-lg" id="edit-vitalsign" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Vital Sign Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_vitalsign">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control" name="uvs-id" id="uvs-id">

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Date Time</label>
                                                        <input type="text" style="height:40px" required id="uvs_edt" name="uvs_edt" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update SBP</label>
                                                        <input type="number" style="height:40px" min="0" max="300" id="uvs_sbp" name="uvs_sbp" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update DBP</label>
                                                        <input type="number" style="height:40px" min="0" max="200" id="uvs_dbp" name="uvs_dbp" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Pulse</label>
                                                        <input type="number" style="height:40px" min="0" max="350" required id="uvs_pulse" name="uvs_pulse" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Temperature (F)</label>
                                                        <input type="number" style="height:40px" min="90" max="110" required id="uvs_temp" name="uvs_temp" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Respiratory Rate</label>
                                                        <input type="number" style="height:40px" min="0" max="50" required id="uvs_rrate" name="uvs_rrate" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Weight (Kg)</label>
                                                        <input type="number" style="height:40px" min="0.1" max="250" step="0.1" required id="uvs_weight" name="uvs_weight" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>
                                        

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Height (cm)</label>
                                                        <input type="text" style="height:40px" required id="uvs_height" name="uvs_height" class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Pain Score (1 -10)</label>
                                                        <input type="number" style="height:40px" min="1" max="10" id="uvs_score" name="uvs_score"  class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                           

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update O₂ Saturation (0 -100)</label>
                                                        <input type="number" style="height:40px" min="0" max="100" required id="uvs_o2saturation" name="uvs_o2saturation"  class="form-control input-sm">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Nursing Notes</label>
                                                        <textarea class="form-control" style="height:80px" id="uvs_nursingnotes" name="uvs_nursingnotes" rows="3"></textarea>
                                                    </select>
                                                </div>
                                            </div>
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Row -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
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
        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        $('#uvs_edt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A'
        });
    </script>
    <script src="{{ asset('assets/custom/vital_sign.js') }}"></script>