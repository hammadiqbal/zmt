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
</style>


<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Patients</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Patients</h4>
                </div>
                @php
                $patientRegistration = explode(',', $rights->patient_registration);
                $serviceBooking = explode(',', $rights->services_booking_for_patients);
                $ConfirmArrival = explode(',', $rights->patient_arrival_and_departure);
                $add = $patientRegistration[0];
                $view = $patientRegistration[1];
                $edit = $patientRegistration[2];
                $updateStatus = $patientRegistration[3];


                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-patient">
                        <i class="mdi mdi-clipboard-account"></i> Register Patient
                    </button>
                </div>
                @endif
            </div>


            @if ($add == 1)
            <div class="modal fade bs-example-modal-xl" id="add-patient" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Register Patient</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_patient" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">
                                                    @if($user->org_id != 0)
                                                    <div class="userOrganization">
                                                        <select class="form-contro selecter p-0" id="patient_org" name="patient_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="patient_org" id="patient_org" style="color:#222d32">
                                                                        <option selected disabled >Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="patient_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" name="patient_site" id="patient_site" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="patient_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Patient Name</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Patient Name" name="patient_name" id="input01">
                                                                </div>
                                                                <span class="text-danger" id="patient_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input02">Enter Father/Husband Name</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Father/Husband Name" name="guardian_name" id="input02">
                                                                </div>
                                                                <span class="text-danger" id="guardian_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Relation</label>
                                                                    <select class="form-control selecter p-0" name="guardian_relation" id="guardian_relation" style="color:#222d32">
                                                                        <option selected disabled >Select Relation</option>
                                                                        <option>Father</option>
                                                                        <option>Husband</option>
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="guardian_relation_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Next Of Kin <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Next Of Kin" name="next_of_kin" id="input03">
                                                                </div>
                                                                <span class="text-danger" id="next_of_kin_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Next Of Kin Relation <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <select class="form-control selecter p-0" name="relation" id="relation" style="color:#222d32">
                                                                        <option selected disabled value="">Select Next Of Kin Relation</option>
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
                                                                <span class="text-danger" id="relation_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input05">Enter Old Mr Code <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Old MR Code" name="old_mrcode" id="input05">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Gender</label>
                                                                    <select class="form-control selecter p-0" name="patient_gender" id="patient_gender" style="color:#222d32">
                                                                        <option selected disabled >Select Gender</option>
                                                                        @foreach ($Genders as $Gender)
                                                                            <option value="{{ $Gender['id'] }}"> {{ $Gender['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="patient_gender_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input04">Enter Language</label>
                                                                    <select class="form-control selecter p-0" name="language" id="language" style="color:#222d32">
                                                                        <option selected disabled >Select Language</option>
                                                                        <option value="urdu">Urdu</option>
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
                                                                    {{-- <input type="text" class="form-control input-sm" placeholder="Patient Language" name="language" id="input04"> --}}
                                                                </div>
                                                                <span class="text-danger" id="language_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Religion</label>
                                                                    <select class="form-control selecter p-0" name="religion" id="religion" style="color:#222d32">
                                                                        <option selected disabled >Select Religion</option>
                                                                        <option>Islam</option>
                                                                        <option>Hindu</option>
                                                                        <option>Chiristian</option>
                                                                        <option>Sikh</option>
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="religion_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Marital Status</label>
                                                                    <select class="form-control selecter p-0" name="marital_status" id="marital_status" style="color:#222d32">
                                                                        <option selected disabled >Select Marital Status</option>
                                                                        <option>Single</option>
                                                                        <option>Married</option>
                                                                        <option>Divorced</option>
                                                                        <option>Widowed</option>
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="marital_status_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient DOB</label>
                                                                    <input type="text" id="dob" name="patient_dob" class="form-control input06 dt" placeholder="Select Patient DOB">
                                                                </div>
                                                                <span class="text-danger" id="patient_dob_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Age <code style="display:none;" id="age_display"></code></label>
                                                                    <input type="number" min="0.1" step="0.01" name="patient_age" id="patient_age" class="form-control input06" placeholder="Enter Patient Age">
                                                                </div>
                                                                <span class="text-danger" id="patient_age_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input06">Enter CNIC# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Patient CNIC" name="patient_cnic" id="input06">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input7">Enter Family# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Family no.." name="familyno" id="input7">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input8">Enter Cell# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Cell no" name="patient_cell" id="input8">
                                                                </div>
                                                                <span class="text-danger" id="patient_cell_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input9">Enter Additional Cell# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Additional Cell No" name="patient_additionalcell" id="input9">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input10">Enter Landline# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Landline #" name="patient_landline" id="input10">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input11">Enter Email <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Enter Email.." name="patient_email" id="input11">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input12">Enter House No <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="House #" name="patient_houseno" id="input12">
                                                                </div>
                                                                <span class="text-danger" id="patient_houseno_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                                                              

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Province</label>
                                                                    <select class="form-control selecter p-0" name="patient_province" id="patient_province" style="color:#222d32">
                                                                        {{-- <option selected disabled >Select Province</option> --}}
                                                                        {{-- @foreach ($Provinces as $Province)
                                                                            <option value="{{ $Province['id'] }}"> {{ $Province['name'] }}</option>
                                                                        @endforeach --}}
                                                                        @foreach ($Provinces as $Province)
                                                                            <option value="{{ $Province['id'] }}" {{ $Province['name'] == 'Sindh' ? 'selected' : '' }}>
                                                                                {{ $Province['name'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="patient_province_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Division</label>
                                                                    <select class="form-control selecter p-0" name="patient_division" id="patient_division" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="patient_division_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">District</label>
                                                                    <select class="form-control selecter p-0" name="patient_district" id="patient_district" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="patient_district_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input13">Enter Address</label>
                                                                    <textarea class="form-control" rows="3" placeholder="Address.." name="patient_address"  id="input13"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="patient_address_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="patient_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="patient_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <h4 class="card-title">Upload Image <small class="text-danger" style="font-size:11px;">(Optional)</small></h4>
                                                                <input type="file" name="patient_img" id="patient_img" class="dropify m-b-5" />
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
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif


            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-servicebooking" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Book Appointment</h4>
                            <h5 class="modal-title"><strong>MR#:</strong> <span id="pMrno">N/A</span></h5>
                        </div>
                        <div class="modal-info" style="display: flex; justify-content: space-between;padding: 10px 20px 10px 20px;">
                            <h5><strong>Organization:</strong> <span id="pOrg">N/A</span></h5>
                            <h5><strong>Site:</strong> <span id="pSite">N/A</span></h5>
                        </div>
                        <form id="add_servicebooking" method="post">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" class="form-control input-sm pb_org" name="sb_org" id="sb_org">
                                <input type="hidden" class="form-control input-sm pb_site" name="sb_site" id="sb_site">
                                <input type="hidden" class="form-control input-sm pb_mr" name="sb_mr">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Location</label>
                                                                    <select class="form-control selecter p-0" id="sb_location" name="sb_location" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Location Schedule</label>
                                                                    <select class="form-control selecter p-0" name="sb_schedule" id="sb_schedule" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="sb_schedule_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Responsible Physician</label>
                                                                    <select class="form-control selecter p-0" name="sb_emp" id="sb_emp" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="sb_emp_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Services</label>
                                                                    <select class="form-control selecter p-0" id="sb_service" name="sb_service" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_service_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Service Modes</label>
                                                                    <select class="form-control selecter p-0" id="sb_serviceMode" name="sb_serviceMode" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="sb_serviceMode_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Billing Cost Center</label>
                                                                    <select class="form-control selecter p-0" id="sb_billingCC" name="sb_billingCC" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sb_billingCC_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient Status</label>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="sbp_status" name="sbp_status" style="color:#222d32">
                                                                        <option selected disabled >Select Patient Status</option>
                                                                        <option value="new">New</option>
                                                                        <option value="follow up">Follow Up</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sbp_status_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient Priority</label>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="sbp_priority" name="sbp_priority" style="color:#222d32">
                                                                        <option selected disabled>Select Patient Priority</option>
                                                                        <option value="routine">Routine</option>
                                                                        <option value="urgent">Urgent</option>
                                                                        <option value="emergency">Emergency</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="sbp_priority_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format2" class="form-control input06 dt edt" name="sb_edt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="sb_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" rows="1" placeholder="Enter Remarks" id="input03" name="sb_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="sb_remarks_error"></span>
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
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" style="overflow-x: hidden !important;overflow-y: auto !important;" id="add-patientinout" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Confirm Arrival</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_patientinout" method="post">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row mb-1">
                                            <div class="col-md-12 text-right">
                                                <code>UNBOOKED</code>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold">MR #:</h6>
                                                                    <select class="form-control selecter p-0" name="pio_mr" id="enterMR" style="color:#222d32">
                                                                    </select>
                                                                    {{-- <input type="text" class="form-control input-sm" name="pio_mr" id="enterMR"> --}}
                                                                </div>
                                                                <span class="text-danger" id="pio_mr_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold" id="scheduleOrg">Organization:</h6>
                                                                    <select class="form-control selecter p-0" name="pio_org" id="pio_org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="pio_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <h6 class="box-title font-weight-bold" id="scheduleSite">Site:</h6>
                                                                    <select class="form-control selecter p-0" id="pio_site" name="pio_site" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Patient Status</label>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="pio_status" name="pio_status" style="color:#222d32">
                                                                        <option selected disabled >Select Patient Status</option>
                                                                        <option value="new">New</option>
                                                                        <option value="follow up">Follow Up</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="pio_status_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient Priority</label>
                                                                    <select class="form-control selecter p-0 cursor-pointer" id="pio_priority" name="pio_priority" style="color:#222d32">
                                                                        <option selected disabled>Select Patient Priority</option>
                                                                        <option value="routine">Routine</option>
                                                                        <option value="urgent">Urgent</option>
                                                                        <option value="emergency">Emergency</option>
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="pio_priority_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Service Location</label>
                                                                    <select class="form-control selecter p-0" name="pio_serviceLocation" id="pio_serviceLocation" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceLocation_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Service Location Schedule</label>
                                                                    <select class="form-control selecter p-0" name="pio_serviceSchedule" id="pio_serviceSchedule" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceSchedule_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-0">
                                                                    <label for="input03">Employee</label>
                                                                    <select class="form-control selecter p-0" name="pio_emp" id="pio_emp" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_emp_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Services</label>
                                                                    <select class="form-control selecter p-0" id="pio_service" name="pio_service" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_service_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Service Modes</label>
                                                                    <select class="form-control selecter p-0" id="pio_serviceMode" name="pio_serviceMode" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceMode_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label for="input03">Billing Cost Center</label>
                                                                    <select class="form-control selecter p-0" id="pio_billingCC" name="pio_billingCC" style="color:#222d32">
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_billingCC_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    {{-- <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="amount_received">Amount Received</label>
                                                                    <input type="number" class="form-control input-sm" placeholder="200" name="amount_received" id="amount_received">
                                                                </div>
                                                                <span class="text-danger" id="amount_received_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="amount_received">Payment Mode</label>
                                                                    <select class="form-control selecter p-0" id="pio_payMode" name="pio_payMode" style="color:#222d32">
                                                                        <option>Cash</option>
                                                                        <option>Card</option>
                                                                    </select>
                                                                    
                                                                </div>
                                                                <span class="text-danger" id="pio_payMode_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="amount_received">Service Start DateTime</label>
                                                                    <input type="text" class="form-control input06" id="pio_serviceStart" name="pio_serviceStart" placeholder="Select Service Start Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="pio_serviceStart_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Enter Remarks.." rows="1" id="input03" name="pio_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="pio_remarks_error"></span>
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
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if ($view == 1)
            <div class="table-responsive m-t-40">
                <table id="view-patient" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Patient Details</th>
                            <th>Identity</th>
                            <th>Contact</th>
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
    <div class="modal fade bs-example-modal-xl" id="edit-patient" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Patient Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_patient" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            
                                            @if($user->org_id == 0)
                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Organization</label>
                                                            <select class="form-control selecter p-0" name="up_org" required id="up_org" style="color:#222d32">
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
                                                            <label class="control-label">Update Site</label>
                                                            <select class="form-control selecter p-0" name="up_site" required id="up_site" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Patient Name</label>
                                                            <input type="hidden" class="form-control" name="up-id" id="up-id">
                                                            <input type="text" class="form-control input-sm" id="up_name" required name="up_name">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Father/Husband Name</label>
                                                            <input type="text" class="form-control input-sm" required name="up_guardianName" id="up_guardianName">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Father/Husband Relation</label>
                                                            <select class="form-control selecter p-0" name="up_guardianRelation" required id="up_guardianRelation" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Next Of Kin <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm"  name="up_nextofkin" id="up_nextofkin">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Next Of Kin Relation <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <select class="form-control selecter p-0 u_emp_gender" name="up_nextofkinRelation" id="up_nextofkinRelation" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update old MR # <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm" name="up_oldmr" id="up_oldmr">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Gender</label>
                                                            <select class="form-control selecter p-0" name="up_gender" required id="up_gender" style="color:#222d32">
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
                                                            <select class="form-control selecter p-0" required name="up_language" id="up_language" style="color:#222d32">
                                                            </select>
                                                            {{-- <input type="text" class="form-control input-sm" required name="up_language" id="up_language"> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Religion</label>
                                                            <select class="form-control selecter p-0" name="up_religion" required id="up_religion" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Marital Status</label>
                                                            <select class="form-control selecter p-0" name="up_maritalStatus" required id="up_maritalStatus" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Patient DOB</label>
                                                            <input type="text" id="up_dob" class="form-control input06 dt" required name="up_dob">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update CNIC <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm"  name="up_cnic" id="up_cnic">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Family# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm"  name="up_familyno" id="up_familyno">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Cell# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm" name="up_cell" id="up_cell">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Additional Cell# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm" name="up_additionalCell" id="up_additionalCell">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Landline <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm" name="up_landline" id="up_landline">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Email <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="email" class="form-control input-sm" name="up_email" id="up_email">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Province</label>
                                                            <select class="form-control selecter p-0" name="up_province" required id="up_province" style="color:#222d32">
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
                                                            <select class="form-control selecter p-0" name="up_division" required id="up_division" style="color:#222d32">
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
                                                            <select class="form-control selecter p-0" name="up_district" required id="up_district" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update House # <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm" name="up_houseno" id="up_houseno">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Address</label>
                                                            <textarea class="form-control" rows="1" id="up_address" required name="up_address" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                            <input type="text" id="date-format1" class="form-control input06 dt up_edt" required name="up_edt" >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-12">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Image <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="file" name="u_patientImg" id="u_patientImg" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    <div class="modal fade bs-example-modal-xl" id="patient-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Patient Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row">
                        <div class="col-lg-4 col-xlg-3 col-md-5">
                            <div class="card">
                                <div class="card-body">
                                    <center>
                                        <img id="patientImg" alt="patient" class="img-square" width="150">
                                        <hr>
                                        <h5 class="card-title m-t-10" id="patientName"></h5>
                                        <p><b>Address:</b> <span id="patientAddress"></span></p>
                                        <p><b>MR #:</b> <span id="mr_no"></span></p>
                                    </center>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">Guardian Name</small>
                                    <h6 id="guardianName"></h6>
                                    <hr>
                                    <small class="text-muted">Guardian Relation</small>
                                    <h6 id="guardianRelation"></h6>
                                    <hr>
                                    <small class="text-muted">Next Of Kin</small>
                                    <h6 id="nextofKin"></h6>
                                    <hr>
                                    <small class="text-muted">Next Of Kin Relation</small>
                                    <h6 id="nextofkinRelation"></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-xlg-9 col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Language</strong>
                                            <br>
                                            <p class="text-muted" id="patientLanguage"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Religion</strong>
                                            <br>
                                            <p class="text-muted" id="patientReligion"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Marital Status</strong>
                                            <br>
                                            <p class="text-muted" id="patientMaritalStatus"></p>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Old MR #</strong>
                                            <br>
                                            <p class="text-muted" id="patientoldMR"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Gender</strong>
                                            <br>
                                            <p class="text-muted" id="patientGender"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Date Of Birth</strong>
                                            <br>
                                            <p class="text-muted" id="patientDOB"></p>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Organization</strong>
                                            <br>
                                            <p class="text-muted" id="patientOrg"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Site</strong>
                                            <br>
                                            <p class="text-muted" id="patientSite"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Cell #</strong>
                                            <br>
                                            <p class="text-muted" id="patientCell"></p>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Province</strong>
                                            <br>
                                            <p class="text-muted" id="patientProvince"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Division</strong>
                                            <br>
                                            <p class="text-muted" id="patientDivision"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"><strong>District</strong>
                                            <br>
                                            <p class="text-muted" id="patientDistrict"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>CNIC</strong>
                                            <br>
                                            <p class="text-muted" id="patientCNIC"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Family #</strong>
                                            <br>
                                            <p class="text-muted" id="patientFamilyNo"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Additional Cell #</strong>
                                            <br>
                                            <p class="text-muted" id="patientAdditionalCell"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Landline #</strong>
                                            <br>
                                            <p class="text-muted" id="patientLandline"></p>
                                        </div>
                                        <div class="col-md-4 col-xs-12 b-r"> <strong>Email</strong>
                                            <br>
                                            <p class="text-muted" id="patientEmail"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        $('#u_emp_dob').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
        $('#date-format2').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
        $('#up_dob').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('.selectpicker').selectpicker();

    </script>
    <script src="{{ asset('assets/custom/patient_registration.js') }}"></script>
