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
                <li class="breadcrumb-item active">Investigation Tracking</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Investigation Trackings</h4>
                </div>
                @php
                $EPISetup = explode(',', $rights->encounters_and_procedures);
                $add = $EPISetup[0];
                $view = $EPISetup[1];
                $edit = $EPISetup[2];
                $updateStatus = $EPISetup[3];
                @endphp

                {{-- @if ($add == 1 && $canAdd) --}}
                {{-- @if ($add == 1 )

                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 confirm-sample">
                        <i class="mdi mdi-medical-bag"></i> Add Investigation Tracking
                    </button>
                </div>
                @endif --}}
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

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="confirm-sample" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Confirm Sample / Procedure Acknowledgement</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="confirm_sample" method="post" enctype="multipart/form-data">
                            {{-- <input type="hidden" name="it_mr" value=" {{ $PatientDetails->patientMR }}"> --}}
                            <input type="hidden" class="it_id" name="investigation_id">
                            <input type="hidden" class="it_age" name="it_age">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Investigation Confirmation Date&Time</label>
                                                                    <input type="text" id="date-format" name="it_confirmation" class="form-control input08" placeholder="Select Investigation Confirmation Date&Time">
                                                                </div>
                                                                <span id="it_confirmation_error" class="text-danger"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Remarks  <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Remarks..." rows="5" name="sample_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="sample_remarks_error"></span>
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

            <div class="modal fade bs-example-modal-lg" id="upload-report" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Upload Report</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="upload_report" method="post" enctype="multipart/form-data">
                            {{-- <input type="hidden" name="it_mr" value=" {{ $PatientDetails->patientMR }}"> --}}
                            <input type="hidden" class="it_id" name="investigation_id">
                            <input type="hidden" class="it_age" name="it_age">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="control-label">Upload Report</label>
                                                            <input type="file" id="reportattachments" name="it_report[]" class="form-control dropify" data-height="100" multiple style="height:80px" />
                                                        </div>
                                                        <div id="file-names" style="margin-top: 10px; font-size:14px; color:#555;"></div>
            
                                                        <span class="text-danger" id="it_report_error"></span>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Report / Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Remarks..." rows="2" name="report_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="report_remarks_error"></span>
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
            <div class="table-responsive">
                <table id="view-investigationTracking" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Patient Details</th>
                            <th>Service Details</th>
                            <th>Investigation Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endif
        </div>
    </div>

    @if ($edit == 1)
    {{-- <div class="modal fade bs-example-modal-lg" id="edit-reqmc" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
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
    </div> --}}
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
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            currentDate: new Date() 
        });
        // $('#uicd_edt').bootstrapMaterialDatePicker({
        //     format: 'dddd DD MMMM YYYY - hh:mm A',
        //     minDate: new Date() 
        // });
          
       $(document).ready(function () {
            const fileNamesContainer = $('#file-names');
            const dropifyInstance = $('.dropify').dropify();
            $('#reportattachments').on('change', function () {
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
    </script>
    <script src="{{ asset('assets/custom/investigation_tracking.js') }}"></script>
