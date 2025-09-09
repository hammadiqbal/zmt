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
                <li class="breadcrumb-item active">Consumption </li>
            </ol>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Consumption Records </h4>
                </div>
                @php
                $Consumption = explode(',', $rights->consumption);
                $add = $Consumption[0];
                $view = $Consumption[1];
                // $updateStatus = $IssueAndDispense[3];
                @endphp
              
            </div>
                @if ($add == 1)
                <div class="modal fade bs-example-modal-lg" id="add-consumption" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Add Consumption Details</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="row" id="transaction-info-row" style="width:98%;display:none;font-size:13px;border:1px solid black;margin: 0 auto;"></div>

                            <form id="add_consumption" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="source_type" id="source_type">
                                <input type="hidden" id="source_applicable" name="source_applicable" value="1">
                                <input type="hidden" id="destination_applicable" name="destination_applicable" value="1">
                                <div class="modal-body">
                                    <!-- Row -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card-body pt-0">
                                                <div class="form-body">
                                                    <div class="row">
                                                        @if($user->org_id != 0)
                                                        <div class="userOrganization">
                                                            <select class="form-contro selecter p-0" id="consumption_org" name="consumption_org">
                                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                            </select>
                                                        </div>
                                                        @else
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                        <select class="form-control selecter p-0" name="consumption_org" id="consumption_org" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="consumption_org_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                        <select class="form-control selecter p-0" name="consumption_site" id="consumption_site" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="consumption_site_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                      

                                                        <div class="col-md-6" >
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">MR # <small class="text-danger" id="mr-optional" style="font-size:11px;">(Optional)</small></label>
                                                                        <select class="form-control selecter p-0" name="consumption_mr" id="consumption_mr" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" >
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Requested Transaction</label>
                                                                        <select class="form-control selecter p-0" name="consumption_transactiontype" id="consumption_transactiontype" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="consumption_transactiontype_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        

                                                        <div class="col-md-6" id="consumption_sl">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Inventory Source</label>
                                                                        <select class="form-control selecter p-0" name="consumption_source" id="consumption_source" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="consumption_source_error"></span>

                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" id="consumption_dl">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Inventory Destination</label>
                                                                        <select class="form-control selecter p-0" name="consumption_destination" id="consumption_destination" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="consumption_destination_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" id="mrService">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service </label>
                                                                        <select class="form-control selecter p-0" name="consumption_service" id="consumption_service" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 serviceDetails">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Mode</label>
                                                                        <select class="form-control selecter p-0" name="consumption_servicemode" id="consumption_servicemode" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 serviceDetails">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Type</label>
                                                                        <input type="text" id="consumption_servicetype" name="consumption_servicetype" class="form-control input-sm">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 serviceDetails">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Service Group</label>
                                                                        <input type="text"  id="consumption_servicegroup" name="consumption_servicegroup" class="form-control input-sm">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 serviceDetails">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Physician </label>
                                                                        <select class="form-control selecter p-0" name="consumption_physician" id="consumption_physician" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 serviceDetails">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Billing Speciality </label>
                                                                        <select class="form-control selecter p-0" name="consumption_billingcc" id="consumption_billingcc" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if ($user->org_id != 0)
                                                            @if ($costcenters->count() > 1)
                                                                <div class="col-md-6">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group has-custom m-b-5">
                                                                                <label class="control-label">Performing CC</label>
                                                                                <select class="form-control selecter p-0" id="consumption_performing_cc" name="consumption_performing_cc" style="color:#222d32">
                                                                                    <option selected disabled value="">Select Performing CC</option>
                                                                                    @foreach($costcenters as $cc)
                                                                                        <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <span class="text-danger" id="consumption_performing_cc_error"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @elseif ($costcenters->count() == 1)
                                                                <input type="hidden" name="consumption_performing_cc" value="{{ $costcenters->first()->id }}">
                                                            @else
                                                                <input type="hidden" name="consumption_performing_cc" value="0">
                                                            @endif
                                                        @else
                                                            <input type="hidden" name="consumption_performing_cc" value="0">
                                                        @endif


                                                        <div class="col-md-6" >
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Enter Reference Document# <small class="text-danger" style="font-size:11px;">(Optional)</small> </label>
                                                                        <input type="text" placeholder="Enter Reference Document#"  name="consumption_reference_document" class="form-control input-sm">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-10">
                                                                        <label class="control-label">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                        <textarea class="form-control" placeholder="Enter Remarks" rows="2" name="consumption_remarks" spellcheck="false"></textarea>
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
                                                                            <select class="form-control selecter p-0 consumption_generic" name="consumption_generic[]" style="color:#222d32">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger consumption_generic_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Select Brand</label>
                                                                            <select class="form-control selecter p-0 consumption_brand" name="consumption_brand[]" style="color:#222d32">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger consumption_brand_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 mr-nt-dependent">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Demand Qty</label>
                                                                            <input type="number" class="form-control input-sm consumption_demand_qty" placeholder="Demand Qty..." name="consumption_demand_qty[]">
                                                                        </div>
                                                                        <span class="text-danger consumption_demand_qty_error" ></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            
                                                            <div class="col-md-6 mr-dependent">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Enter Dose</label>
                                                                            <input type="text" class="form-control input-sm consumption_dose" placeholder="Dose.." name="consumption_dose[]">
                                                                        </div>
                                                                        <span class="text-danger consumption_dose_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 mr-dependent">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Select Route</label>
                                                                            <select class="form-control selecter p-0 consumption_route" name="consumption_route[]" style="color:#222d32">
                                                                                @foreach ($MedicationRoutes as $MedicationRoute)
                                                                                    <option value="{{ $MedicationRoute['id'] }}">{{ $MedicationRoute['name'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger consumption_route_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 mr-dependent">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Select Frequency</label>
                                                                            <select class="form-control selecter p-0 consumption_frequency" name="consumption_frequency[]" style="color:#222d32">
                                                                                @foreach ($MedicationFrequencies as $MedicationFrequency)
                                                                                    <option value="{{ $MedicationFrequency['id'] }}">{{ $MedicationFrequency['name'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger consumption_frequency_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 mr-dependent">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Enter Duration</label>
                                                                            <input type="text" class="form-control input-sm consumption_duration" placeholder="Duration..." name="consumption_duration[]">
                                                                        </div>
                                                                        <span class="text-danger consumption_duration_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

        
                                                            <div class="col-md-6 brand_details">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Batch #</label>
                                                                            <input type="text" class="form-control input-sm consumption_batch" placeholder="Batch #.." name="consumption_batch[]">
                                                                        </div>
                                                                        <span class="text-danger consumption_batch_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

        
                                                            <div class="col-md-6 brand_details">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Expiry Date</label>
        
                                                                            <input type="text" name="consumption_expiry[]" class="form-control input06 qd consumption_expiry" placeholder="Select Expiry Date">
                                                                        </div>
                                                                        <span class="text-danger consumption_expiry_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                             <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Issued Qty</label>
                                                                            <input type="number" class="form-control input-sm issue_qty" placeholder="Transaction Qty..." name="issue_qty[]">
                                                                        </div>
                                                                        <span class="text-danger issue_qty_error" ></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
        
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Enter Consumed Qty</label>
                                                                            <input type="number" min="0" class="form-control input-sm consumption_qty" name="consumption_qty[]">
                                                                        </div>
                                                                        <span class="text-danger consumption_qty_error" ></span>
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
                <table id="view-consumption" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Transaction Details</th>
                            <th>Patient Details</th>
                            <th>Item Details</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endif
        </div>
    </div>




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
        $('#u_im_edt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });       
        // $('.edt').bootstrapMaterialDatePicker({
        //     weekStart : 0, time: false,
        //     minDate: new Date() 
        // });
        $('.qd').bootstrapMaterialDatePicker({ weekStart : 0, time: false });


        $('#u_et_expirydate').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('.selectpicker').selectpicker();
    </script>
    <script src="{{ asset('assets/custom/consumption.js') }}"></script>
