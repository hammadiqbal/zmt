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


<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-9 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Patient Medical Record</li>
                <li class="breadcrumb-item active">Requisition For Medication Consumption</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Requisitions For Medication Consumptions</h4>
                </div>
                @php
                $EPISetup = explode(',', $rights->encounters_and_procedures);
                $add = $EPISetup[0];
                $view = $EPISetup[1];
                $edit = $EPISetup[2];
                $updateStatus = $EPISetup[3];
                @endphp

                {{-- @if ($add == 1) --}}
                @if ($add == 1 && $canAddRequisition)

                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-reqmc">
                        <i class="mdi mdi-medical-bag"></i> Add Requisition For Medication Consumption
                    </button>
                </div>
                @endif
            </div>
            <br>
           
            {{-- <div class="mt-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-12">
                        <div class="border p-4 rounded shadow-sm" style="font-weight:bolder;">
                            <div class="row mb-3">
                                @if(!empty($PatientDetails->orgName))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>Organization</b></label>
                                            <div class="border p-2 rounded">
                                                {{ ucfirst($PatientDetails->orgName) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
            
                                @if(!empty($PatientDetails->siteName))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>Site</b></label>
                                            <div class="border p-2 rounded">
                                                {{ ucfirst($PatientDetails->siteName) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
            
                                @if(!empty($PatientDetails->patientMR))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>MR#</b></label>
                                            <div class="border p-2 rounded">
                                                {{ $PatientDetails->patientMR }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
            
                                @if(!empty($PatientDetails->patientName))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>Patient Name</b></label>
                                            <div class="border p-2 rounded">
                                                {{ ucfirst($PatientDetails->patientName) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row mb-3">
                                @if(!empty($ageString))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>Age</b></label>
                                            <div class="border p-2 rounded">
                                                {{ $ageString }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
            
                                @if(!empty($PatientDetails->gender))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>Gender</b></label>
                                            <div class="border p-2 rounded">
                                                {{ ucfirst($PatientDetails->gender) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
            
                                @if(!empty($PatientDetails->responsiblePhysician))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>Responsible Physician</b></label>
                                            <div class="border p-2 rounded">
                                                {{ ucfirst($PatientDetails->responsiblePhysician) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
            
                                @if(!empty($PatientDetails->billingCCName))
                                    <div class="col">
                                        <div class="form-group">
                                            <label><b>Billing CostCenter</b></label>
                                            <div class="border p-2 rounded">
                                                {{ ucfirst($PatientDetails->billingCCName) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="container mt-4">
                <div class="card shadow-sm border rounded-3">
                    <div class="card-body px-4 py-3" style="font-size: 14px;">
                        {{-- First Row --}}
                        <div class="row text-center">
                            @if(!empty($PatientDetails->orgName))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Organization:</small>
                                    <span class="fw-bold text-primary">{{ ucfirst($PatientDetails->orgName) }}</span>
                                </div>
                            @endif
            
                            @if(!empty($PatientDetails->siteName))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Site:</small>
                                    <span class="fw-bold text-primary">{{ ucfirst($PatientDetails->siteName) }}</span>
                                </div>
                            @endif
            
                            @if(!empty($PatientDetails->patientMR))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">MR#:</small>
                                    <span class="fw-bold text-primary">{{ $PatientDetails->patientMR }}</span>
                                </div>
                            @endif
            
                            @if(!empty($PatientDetails->patientName))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Patient Name:</small>
                                    <span class="fw-bold text-primary">{{ ucfirst($PatientDetails->patientName) }}</span>
                                </div>
                            @endif
                        </div>
            
                        {{-- Second Row --}}
                        <div class="row text-center border-top pt-3">
                            @if(!empty($ageString))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Age:</small>
                                    <span class="fw-bold text-primary">{{ $ageString }}</span>
                                </div>
                            @endif
            
                            @if(!empty($PatientDetails->gender))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Gender:</small>
                                    <span class="fw-bold text-primary">{{ ucfirst($PatientDetails->gender) }}</span>
                                </div>
                            @endif
            
                            @if(!empty($PatientDetails->responsiblePhysician))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Responsible Physician:</small>
                                    <span class="fw-bold text-primary">{{ ucfirst($PatientDetails->responsiblePhysician) }}</span>
                                </div>
                            @endif
            
                            @if(!empty($PatientDetails->billingCCName))
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted d-block">Billing CostCenter:</small>
                                    <span class="fw-bold text-primary">{{ ucfirst($PatientDetails->billingCCName) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            
            

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-reqmc" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Requisition For Medication Consumption</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_reqmc" method="post">
                            <input type="hidden" id="rmc_orgid" value=" {{ $PatientDetails->orgId }}">
                            <input type="hidden" name="rmc_mr" value=" {{ $PatientDetails->patientMR }}">
                            <input type="hidden" id="rmc_siteid" value=" {{ $PatientDetails->siteId }}">
                            @csrf
                            <div class="modal-body">
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
                                                                    <label class="control-label">Select Transaction Type</label>
                                                                    <select class="form-control selecter p-0" name="rmc_transaction_type" id="rmc_transaction_type" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rmc_transaction_type_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Inventory Locations </label>
                                                                    <select class="form-control selecter p-0" name="rmc_inv_location" id="rmc_inv_location" style="color:#222d32">
                                                                        {{-- <option selected disabled >Select Inventory Location</option>
                                                                        @foreach ($ServiceLocations as $ServiceLocation)
                                                                            <option value="{{ $ServiceLocation['id'] }}">{{ $ServiceLocation['name'] }}</option>
                                                                        @endforeach --}}
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rmc_inv_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Remarks..." rows="2" name="rmc_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row pt-4 pb-1 duplicate" style="border: 1px solid #939393;">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Item Generic</label>
                                                                    <select class="form-control selecter p-0 rmc_inv_generic" name="rmc_inv_generic[]" style="color:#222d32">
                                                                        <option selected disabled >Select Item Generic</option>
                                                                        @foreach ($Generics as $Generic)
                                                                            <option value="{{ $Generic['id'] }}">{{ $Generic['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rmc_inv_generic_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Dose</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Dose.." name="rmc_dose[]">
                                                                </div>
                                                                <span class="text-danger" id="rmc_dose_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Route</label>
                                                                    <select class="form-control selecter p-0 rmc_route" name="rmc_route[]" style="color:#222d32">
                                                                        <option selected disabled >Select Route</option>
                                                                        @foreach ($MedicationRoutes as $MedicationRoute)
                                                                            <option value="{{ $MedicationRoute['id'] }}">{{ $MedicationRoute['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rmc_route_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Frequency</label>
                                                                    <select class="form-control selecter p-0 rmc_frequency" name="rmc_frequency[]" style="color:#222d32">
                                                                        <option selected disabled >Select Frequency</option>
                                                                        @foreach ($MedicationFrequencies as $MedicationFrequency)
                                                                            <option value="{{ $MedicationFrequency['id'] }}">{{ $MedicationFrequency['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rmc_frequency_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Days</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Days..." name="rmc_days[]">
                                                                </div>
                                                                <span class="text-danger" id="rmc_days_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 d-flex justify-content-center p-2">
                                                    <button type="button" id="addMoreBtn" class="btn btn-success mr-2"> <i class="mdi mdi-plus"></i> Add More</button>
                                                    <button type="button" id="removeBtn" class="btn btn-danger"> <i class="mdi mdi-minus"></i> Remove</button>
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
                <table id="view-reqmc" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Transaction Details</th>
                            <th>Patient Details</th>
                            <th>Item Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-reqmc" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Requisition For Medication Consumption Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_reqmc">
                @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" id="reqmc_id" name="reqmc_id">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Update Transaction Type</label>
                                                            <select class="form-control selecter p-0" required name="u_rmc_transaction_type" id="u_rmc_transaction_type" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Select Inventory Locations </label>
                                                            <select class="form-control selecter p-0" name="u_rmc_inv_location" id="u_rmc_inv_location" style="color:#222d32">
                                                                <option selected disabled >Select Inventory Location</option>
                                                                @foreach ($ServiceLocations as $ServiceLocation)
                                                                    <option value="{{ $ServiceLocation['id'] }}">{{ $ServiceLocation['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Update Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <textarea class="form-control" rows="3" name="u_rmc_remarks" id="u_rmc_remarks" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="uduplicate">
                                        </div>

                                        <div class="d-flex justify-content-center pt-3">
                                            <button type="button" id="urmc_addmore" class="btn btn-success mr-2">
                                            <i class="mdi mdi-plus"></i> Add More</button>

                                            <button type="button" id="urmc_remove" class="btn btn-danger mr-2"> 
                                            <i class="mdi mdi-minus"></i> Remove</button>
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
        $('#uicd_edt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/req-medication-consumption.js') }}"></script>
