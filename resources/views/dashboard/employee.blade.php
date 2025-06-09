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
<style>
.show-tick .btn{background: none !important;padding: 7px 20px 2px 0;border-bottom: 1px solid #222d32;height: calc(2.45rem + 2px);}
.smode button.btn.dropdown-toggle.btn-default {
border: 1px solid rgba(0,0,0,.15);
}
@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}

.blinking {
    animation: blink 1s infinite;
}
</style>


<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Human Resource</li>
                <li class="breadcrumb-item active">Employee</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Employees</h4>
                </div>
                @php
                $empSetup = explode(',', $rights->employee_setup);
                $add = $empSetup[0];
                $view = $empSetup[1];
                $edit = $empSetup[2];
                $updateStatus = $empSetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-employee">
                        <i class="mdi mdi-human"></i> Add Employee
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-xl" id="add-employee" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Employee</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_employee" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Select Prefix</label>
                                                                <select class="form-control selecter p-0" name="emp_prefix" id="emp_prefix" style="color:#222d32">
                                                                    @foreach ($Prefixes as $Prefix)
                                                                        <option value="{{ $Prefix['id'] }}"> {{ $Prefix['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="emp_prefix_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Enter Employee Name</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Employee Name" name="emp_name" id="input01">
                                                            </div>
                                                            <span class="text-danger" id="emp_name_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01222">Enter Father/Husband Name</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Father/Husband Name" name="emp_guardian_name" id="input01222">
                                                            </div>
                                                            <span class="text-danger" id="emp_guardian_name_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Relation</label>
                                                                <select class="form-control selecter p-0" name="emp_guardian_relation" id="emp_guardian_relation" style="color:#222d32">
                                                                    <option selected disabled >Select Relation</option>
                                                                    <option>Father</option>
                                                                    <option>Husband</option>
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="emp_guardian_relation_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0121">Enter Next Of Kin Name</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Next Of Kin Name" name="emp_next_of_kin" id="input0121">
                                                            </div>
                                                            <span class="text-danger" id="emp_next_of_kin_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0121">Next Of Kin Relation</label>
                                                                <select class="form-control selecter p-0" name="emp_nextofkin_relation" id="emp_nextofkin_relation" style="color:#222d32">
                                                                    <option selected disabled >Select Next Of Kin Relation</option>
                                                                    <option>Father</option>
                                                                    <option>Mother</option>
                                                                    <option>Brother</option>
                                                                    <option>Sister</option>
                                                                    <option>Spouse</option>
                                                                    <option>Child</option>
                                                                    <option>Grandparent</option>
                                                                    <option>Grandchild</option>
                                                                    <option>Uncle</option>
                                                                    <option>Aunt</option>
                                                                    <option>Niece</option>
                                                                    <option>Nephew</option>
                                                                    <option>Cousin</option>
                                                                    <option>Legal Guardian</option>
                                                                    <option>Friend</option>
                                                                    <option>Partner</option>
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_nextofkin_relation_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input011">Old Employee Code <small class="text-danger" style="font-size:11px;">(Optional)</small> </label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Old Employee Code" name="emp_oldcode" id="input011">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Gender</label>
                                                                <select class="form-control selecter p-0" name="emp_gender" id="emp_gender" style="color:#222d32">
                                                                    <option selected disabled >Select Gender</option>
                                                                    @foreach ($Genders as $Gender)
                                                                        <option value="{{ $Gender['id'] }}"> {{ $Gender['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="emp_gender_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Enter Language</label>
                                                                <select class="form-control selecter p-0" name="emp_language" id="emp_language" style="color:#222d32">
                                                                    <option selected disabled >Select Language</option>
                                                                    <option value="urdu">Urdu</option>
                                                                    <option value="english">English</option>
                                                                    <option value="sindhi">Sindhi</option>
                                                                    <option value="balochi">Balochi</option>
                                                                    <option value="punjabi">Punjabi</option>
                                                                    <option value="pashto">Pashto</option>
                                                                    <option value="hindko">Hindko</option>
                                                                    <option value="siraiki">Siraiki</option>
                                                                    <option value="memoni">Memoni</option>
                                                                    <option value="gujrati">Gujrati</option>
                                                                    <option value="brahui">Brahui</option>
                                                                    <option value="shina">Shina</option>
                                                                    <option value="burushaski">Burushaski</option>
                                                                    <option value="wakhi">Wakhi</option>
                                                                    <option value="balti">Balti</option>
                                                                    <option value="kashmiri">Kashmiri</option>
                                                                    <option value="khowar">Khowar</option>
                                                                </select>
                                                                {{-- <input type="text" class="form-control input-sm" placeholder="Enter Langage" name="emp_language" id="input0412"> --}}
                                                            </div>
                                                            <span class="text-danger" id="emp_language_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Religion</label>
                                                                <select class="form-control selecter p-0" name="emp_religion" id="emp_religion" style="color:#222d32">
                                                                    <option selected disabled >Select Religion</option>
                                                                    <option>Islam</option>
                                                                    <option>Hindu</option>
                                                                    <option>Chiristian</option>
                                                                    <option>Sikh</option>
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_religion_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Marital Status</label>
                                                                <select class="form-control selecter p-0" name="emp_marital_status" id="emp_marital_status" style="color:#222d32">
                                                                    <option selected disabled >Select Marital Status</option>
                                                                    <option>Single</option>
                                                                    <option>Married</option>
                                                                    <option>Divorced</option>
                                                                    <option>Widowed</option>
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_marital_status_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Date Of Birth</label>
                                                                <input type="text" id="dob" name="emp_dob" class="form-control input06 dt" placeholder="Select Employee DOB">
                                                            </div>
                                                            <span class="text-danger" id="emp_dob_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($user->org_id != 0)
                                                <div class="userOrganization">
                                                    <select class="form-contro selecter p-0" id="emp_org" name="emp_org">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                                @else
                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Organization</label>
                                                                <select class="form-control selecter p-0" name="emp_org" id="emp_org" style="color:#222d32">
                                                                    <option selected disabled >Select Organization</option>
                                                                    @foreach ($Organizations as $Organization)
                                                                        <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="emp_org_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Head Count Site</label>
                                                                <select class="form-control selecter p-0" name="emp_site" id="emp_site" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="emp_site_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Head Count Cost Center</label>
                                                                <select class="form-control selecter p-0" name="emp_cc" id="emp_cc" style="color:#222d32">
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_cc_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Cadre</label>
                                                                <select class="form-control selecter p-0" name="emp_cadre" id="emp_cadre" style="color:#222d32">
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_cadre_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0412">Position</label>
                                                                <select class="form-control selecter p-0" name="emp_position" id="emp_position" style="color:#222d32">
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_position_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                {{-- <div class="input-timerange input-group" id="time-range">

                                                                    <input type="text" id="start_time" name="start_time" class="form-control" placeholder="Start time" />
                                                                    <span class="input-group-addon bg-info b-0 text-white">to</span>
                                                                    <input type="text" id="end_time" name="end_time" class="form-control" placeholder="End time" style="padding-left: 8px;"/>

                                                                </div> --}}
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01111">Enter Week Hours</label>
                                                                    <input type="number" class="form-control input-sm" placeholder="Enter Week Hrs" name="emp_weekHrs" id="input01111">
                                                                </div>
                                                            </div>
                                                            <span class="text-danger" id="emp_weekHrs_error"></span>
                                                        </div>
                                                    </div>
                                                </div>



                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Manager/Supervisor</label>
                                                                <select class="form-control selecter p-0" name="emp_reportto" id="emp_reportto" style="color:#222d32">
                                                                    <option selected disabled >Select Manager/Supervisor </option>
                                                                    @if(!$Employees->isEmpty())
                                                                        <option value="0"> N/A </option>
                                                                        @foreach ($Employees as $Employee)
                                                                            <option value="{{ $Employee['id'] }}"> {{ $Employee['name'] }}</option>
                                                                        @endforeach
                                                                    @else
                                                                        <option value="0"> N/A </option>
                                                                    @endif
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_reportto_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Qualification level</label>
                                                                <select class="form-control selecter p-0" name="emp_qual_lvl" id="emp_qual_lvl" style="color:#222d32">
                                                                    <option selected disabled >Select Qualification level</option>
                                                                    @foreach ($QualificationLevels as $QualificationLevel)
                                                                        <option value="{{ $QualificationLevel['id'] }}"> {{ $QualificationLevel['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_qual_lvl_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Employee Status</label>
                                                                <select class="form-control selecter p-0" name="emp_status" id="emp_status" style="color:#222d32">
                                                                    <option selected disabled >Select Employee Status</option>
                                                                    @foreach ($EmpStatuses as $EmpStatus)
                                                                        <option value="{{ $EmpStatus['id'] }}"> {{ $EmpStatus['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_status_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Working Status</label>
                                                                <select class="form-control selecter p-0" name="emp_working_status" id="emp_working_status" style="color:#222d32">
                                                                    <option selected disabled >Select Working Status</option>
                                                                    @foreach ($EmpWorkingStatuses as $EmpWorkingStatus)
                                                                        <option value="{{ $EmpWorkingStatus['id'] }}"> {{ $EmpWorkingStatus['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_working_status_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Date Of Joining</label>
                                                                <input type="text" id="doj" name="emp_doj" class="form-control input06 dt" placeholder="Select Date Of Joining">
                                                            </div>
                                                            <span class="text-danger" id="emp_doj_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input016">Enter CNIC#</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter CNIC#" name="emp_cnic" id="input016">
                                                            </div>
                                                            <span class="text-danger" id="emp_cnic_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Select Expiry Of CNIC</label>
                                                                <input type="text" id="cnic_expiry" name="cnic_expiry" class="form-control input06 dt" placeholder="Select Expiry Of CNIC">
                                                            </div>
                                                            <span class="text-danger" id="cnic_expiry_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input014">Enter Cell#</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Cell#" name="emp_cell" id="input014">
                                                            </div>
                                                            <span class="text-danger" id="emp_cell_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input919">Enter Additional Cell# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Additional Cell#" name="emp_additionalcell" id="input919">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input0122">Enter Landline# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                <input type="text" class="form-control input-sm" name="emp_landline" placeholder="Enter Landline#" id="input0122">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input021">Enter Email</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter email" name="emp_email" id="input021">
                                                            </div>
                                                            <span class="text-danger" id="emp_email_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input021">Effective DateTime</label>
                                                                <input type="text" id="date-format" name="emp_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                            </div>
                                                            <span class="text-danger" id="emp_edt_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input03">Enter Present Address</label>
                                                                <textarea class="form-control" rows="3" name="emp_address" placeholder="Enter Address"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="emp_address_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input03">Enter Mailing Address</label>
                                                                <textarea class="form-control" rows="3" name="mailing_address" placeholder="Enter Mailing Address"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="mailing_address_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                
                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Province</label>
                                                                <select class="form-control selecter p-0" name="emp_province" id="province_name" style="color:#222d32">
                                                                    <option selected disabled >Select Province</option>
                                                                    @foreach ($Provinces as $Province)
                                                                        <option value="{{ $Province['id'] }}"> {{ $Province['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="emp_province_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Division</label>
                                                                <select class="form-control selecter p-0" name="emp_division" id="division_name" style="color:#222d32">
                                                                    <option selected disabled >Select Division</option>
                                                                </select>
                                                                
                                                            </div>
                                                            <span class="text-danger" id="emp_division_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">District</label>
                                                                <select class="form-control selecter p-0" name="emp_district" id="district_name" style="color:#222d32">
                                                                    <option selected disabled >Select District</option>
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="emp_district_error"></span>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <h4 class="card-title">Upload Image</h4>
                                                            <input type="file" name="emp_img" id="emp_img" class="dropify m-b-5" />
                                                            <span class="text-danger" id="emp_img_error"></span>
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
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if ($view == 1)
            <div class="table-responsive m-t-40">
                <table id="view-employee" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Employee Details</th>
                            <th>Placement</th>
                            <th>Work Status</th>
                            <th>Contact Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endif
        </div>
    </div>

    @if ($edit == 1)
    <div class="modal fade bs-example-modal-xl" id="edit-employee" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Employee Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="edit_employee" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Prefix </label>
                                                            <select class="form-control selecter p-0" name="u_emp_prefix" id="u_emp_prefix" style="color:#222d32">
                                                                @foreach ($Prefixes as $Prefix)
                                                                    <option value="{{ $Prefix['id'] }}"> {{ $Prefix['name'] }}</option>
                                                                @endforeach
                                                            </select>   
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Employee Name</label>
                                                            <input type="hidden" class="form-control u-emp-id" name="u-empid">
                                                            <input type="text" class="form-control input-sm u_emp_name" required name="u_emp_name" id="input051">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Employee Father/Husband Name</label>
                                                            <input type="text" class="form-control input-sm u_guardian_name" required name="u_guardian_name" id="input01116">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Relation</label>
                                                            <select class="form-control selecter p-0 u_guardian_relation" name="u_guardian_relation" required id="u_guardian_relation" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Employee Next Of Kin</label>
                                                            <input type="text" class="form-control input-sm u_emp_nextofkin" required name="u_emp_nextofkin" id="input01118">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Next Of Kin Relation</label>
                                                            <select class="form-control selecter p-0 u_nextofkin_relation" name="u_nextofkin_relation" required id="u_nextofkin_relation" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Old Employee Code <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_emp_code"  name="u_emp_code" id="input02">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Gender</label>
                                                            <select class="form-control selecter p-0 u_emp_gender" name="u_emp_gender" required id="u_emp_gender" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Language</label>
                                                            <select class="form-control selecter p-0" required name="u_emp_language" id="u_emp_language" style="color:#222d32">
                                                            </select>
                                                            {{-- <input type="text" class="form-control input-sm u_emp_language" required name="u_emp_language" id="input01115"> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Religion</label>
                                                            <select class="form-control selecter p-0 u_emp_religion" name="u_emp_religion" required id="u_emp_religion" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update marital Status</label>
                                                            <select class="form-control selecter p-0 u_emp_marital_status" name="u_emp_marital_status" required id="u_emp_marital_status" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Date Of Birth</label>
                                                            <input type="text" id="u_emp_dob" class="form-control input06 dt" required name="u_emp_dob">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @if($user->org_id == 0)
                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Organization</label>
                                                            <select class="form-control selecter p-0 u_emp_org" name="u_emp_org" required id="u_emp_org" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Head Count Site</label>
                                                            <select class="form-control selecter p-0 u_emp_site" name="u_emp_site" required id="u_emp_site" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Head Count Cost Center</label>
                                                            <select class="form-control selecter p-0 u_emp_cc" name="u_emp_cc" required id="u_emp_cc" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Cadre</label>
                                                            <select class="form-control selecter p-0 u_emp_cadre" name="u_emp_cadre" required id="u_emp_cadre" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Position</label>
                                                            <select class="form-control selecter p-0 u_emp_position" name="u_emp_position" required id="u_emp_position" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Update Week Hours</label>
                                                            <input type="number" required class="form-control input-sm"  name="u_emp_weekHrs" id="u_emp_weekHrs">

                                                            {{-- <div class="input-timerange input-group" id="time-range">

                                                                <input type="text" id="u_start_time" name="u_start_time" class="form-control" placeholder="Start time" />
                                                                <span class="input-group-addon bg-info b-0 text-white">to</span>
                                                                <input type="text" id="u_end_time" name="u_end_time" class="form-control" placeholder="End time" style="padding-left: 8px;"/>

                                                            </div> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Manager/Supervisor</label>
                                                            <select class="form-control selecter p-0 u_emp_reportto" name="u_emp_reportto" required id="u_emp_reportto" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Qualification Level</label>
                                                            <select class="form-control selecter p-0 u_qualification" name="u_qualification" required id="u_qualification" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Employee Status</label>
                                                            <select class="form-control selecter p-0 u_emp_status" name="u_emp_status" required id="u_emp_status" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Working Status</label>
                                                            <select class="form-control selecter p-0 u_working_status" name="u_working_status" required id="u_working_status" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4" id="date_of_leaving" style="display:none;">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Date Of Leaving</label>
                                                            <input type="text" id="u_emp_dol" class="form-control input06 dt" name="u_emp_dol">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Date Of Joining</label>
                                                            <input type="text" id="u_emp_doj" class="form-control input06 dt" required name="u_emp_doj">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update CNIC</label>
                                                            <input type="text" class="form-control input-sm u_emp_cnic" required name="u_emp_cnic">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update CNIC Expiry</label>
                                                            <input type="text" class="form-control input-sm u_cnic_expiry" required name="u_cnic_expiry" >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> --}}

                                            <div class="col-md-4">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update CNIC Expiry</label>
                                                            <input type="text" id="u_cnic_expiry" class="form-control input06 dt" required name="u_cnic_expiry">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Cell#</label>
                                                            <input type="text" class="form-control input-sm u_emp_cell" required name="u_emp_cell" id="input0829">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Additional Cell# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_emp_additional_cell" name="u_emp_additional_cell" id="input08292">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Landline <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_emp_landline" name="u_emp_landline" id="input0839">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Email </label>
                                                            <input type="email" required class="form-control input-sm u_emp_email" name="u_emp_email" id="input09">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                            <input type="text" id="date-format1" class="form-control input06 dt u_emp_edt" required name="u_emp_edt" >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Present Address</label>
                                                            <textarea class="form-control u_emp_address" rows="3"required name="u_emp_address"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Mailing Address</label>
                                                            <textarea class="form-control u_emp_mailingaddress" rows="3"required name="u_emp_mailingaddress"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Province</label>
                                                            <select class="form-control selecter p-0 u_emp_province" name="u_emp_province" required id="u_emp_province" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Division</label>
                                                            <select class="form-control selecter p-0 u_emp_division" name="u_emp_division" required id="u_emp_division" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update District</label>
                                                            <select class="form-control selecter p-0 u_emp_district" name="u_emp_district" required id="u_emp_district" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Image</label>
                                                            <input type="file" name="u_empImg" id="u_empImg"  />
                                                        </div>
                                                    </div>
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

    @if ($view == 1)
    <div class="modal fade bs-example-modal-xl" id="employee-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Employee Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row">
                        <!-- Column -->
                        <div class="col-lg-3 col-xlg-3 col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <center>
                                        <img id="empImg" alt="Employee" class="img-square" width="150">
                                        <h5 class="card-title m-t-10" id="empName"></h5>
                                        <hr>
                                        <p><b>Present Address:</b> <span id="empAddress"></span></p>
                                        <p><b>Mailing Address:</b> <span id="empMailingAddress"></span></p>
                                    </center>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">Old Code</small>
                                    <h6 id="empOldcode"></h6>
                                    <hr>
                                    <small class="text-muted">Email address</small>
                                    <h6 id="empEmail"></h6>
                                    <hr>
                                    <small class="text-muted">Contact #</small>
                                    <h6 id="empContact"></h6>
                                    <hr>
                                    <small class="text-muted">Additional Cell#</small>
                                    <h6 id="empAdditionalCell"></h6>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->
                        <!-- Column -->
                        <div class="col-lg-9 col-xlg-9 col-md-9">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-xs-12 b-r"> <strong>Father/Husband Name</strong>
                                            <br>
                                            <p class="text-muted" id="empGuardian"></p>
                                        </div>
                                        <div class="col-md-3 col-xs-12 b-r"> <strong>Relation</strong>
                                            <br>
                                            <p class="text-muted" id="empRelation"></p>
                                        </div>
                                        <div class="col-md-3 col-xs-12 b-r"> <strong>Next Of Kin</strong>
                                            <br>
                                            <p class="text-muted" id="empNextOfKin"></p>
                                        </div>
                                        <div class="col-md-3 col-xs-12 b-r"> <strong>Next Of Kin Relation</strong>
                                            <br>
                                            <p class="text-muted" id="empNextOfKinRelation"></p>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Province</strong>
                                            <br>
                                            <p class="text-muted" id="empProvince"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Division</strong>
                                            <br>
                                            <p class="text-muted" id="empDivision"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>District</strong>
                                            <br>
                                            <p class="text-muted" id="empDistrict"></p>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Organization</strong>
                                            <br>
                                            <p class="text-muted" id="empOrg"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Head Count Site</strong>
                                            <br>
                                            <p class="text-muted" id="empSite"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Gender</strong>
                                            <br>
                                            <p class="text-muted" id="empGender"></p>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Language</strong>
                                            <br>
                                            <p class="text-muted" id="empLanguage"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Religion</strong>
                                            <br>
                                            <p class="text-muted" id="empReligion"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Marital Status</strong>
                                            <br>
                                            <p class="text-muted" id="empMaritalStatus"></p>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Head Count Cost Center</strong>
                                            <br>
                                            <p class="text-muted" id="empCC"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Cadre</strong>
                                            <br>
                                            <p class="text-muted" id="empCadre"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Position</strong>
                                            <br>
                                            <p class="text-muted" id="empPosition"></p>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Qualification Level</strong>
                                            <br>
                                            <p class="text-muted" id="empQualification"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Employee Status</strong>
                                            <br>
                                            <p class="text-muted" id="empStatus"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Working Status</strong>
                                            <br>
                                            <p class="text-muted" id="workingStatus"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Week Hours</strong>
                                            <br>
                                            <p class="text-muted" id="weekHrs"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Manager/Supervisor</strong>
                                            <br>
                                            <p class="text-muted" id="empManager"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Joining Date</strong>
                                            <br>
                                            <p class="text-muted" id="empJD"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>CNIC</strong>
                                            <br>
                                            <p class="text-muted" id="empCnic"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>CNIC Expiry</strong>
                                            <br>
                                            <p class="text-muted" id="empCnicExpiry"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Date Of Birth</strong>
                                            <br>
                                            <p class="text-muted" id="empDOB"></p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
        $('.dropify').dropify();
        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        }); 
        $('#dob').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#doj').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#cnic_expiry').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#u_emp_dob').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#u_emp_doj').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#u_cnic_expiry').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#u_emp_dol').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        }); 
        $('#start_time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: false,
            format: 'hh:mm A'
        });
        $('#end_time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: false,
            format: 'hh:mm A'
        });
        $('#u_start_time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: false,
            format: 'hh:mm A'
        });
        $('#u_end_time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: false,
            format: 'hh:mm A'
        });
        $('.timepicker').one('focus', function(){
            $(this).bootstrapMaterialDatePicker(pickerOptions);
        });
        $('.selectpicker').selectpicker();

    </script>
    <script src="{{ asset('assets/custom/employee.js') }}"></script>

