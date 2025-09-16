
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
.smode button.btn.dropdown-toggle.btn-default {border: 1px solid rgba(0,0,0,.15);}
</style>


<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-8 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Requisition For Material Consumption</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Requisition For Material Consumptions</h4>
                </div>
                @php
                $MaterialConsumption = explode(',', $rights->requisition_for_material_consumption);
                $add = $MaterialConsumption[0];
                $view = $MaterialConsumption[1];
                $edit = $MaterialConsumption[2];
                $updateStatus = $MaterialConsumption[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-materialconsumption">
                        <i class="mdi mdi-clipboard-account"></i> Add Requisition For Material Consumption
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-materialconsumption" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Requisition For Material Consumption</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="row" id="transaction-info-row" style="width:98%;display:none;font-size:13px;border:1px solid black;margin: 0 auto;"></div>

                        <form id="add_materialconsumption" method="post" enctype="multipart/form-data">
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
                                                        <select class="form-contro selecter p-0" id="mc_org" name="mc_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="mc_org" id="mc_org" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="mc_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" name="mc_site" id="mc_site" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="mc_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Transaction Type</label>
                                                                    <select class="form-control selecter p-0" name="mc_transactiontype" id="mc_transactiontype" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="mc_transactiontype_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mc_source_location" style="display: none;">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Source Location</label>
                                                                    <select class="form-control selecter p-0" name="mc_source_location" id="mc_source_location" style="color:#222d32">
                                                                        <option selected disabled value="">Select Source Location</option>
                                                                    </select> 
                                                                </div>
                                                                <span class="text-danger" id="mc_source_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mc_destination_location" style="display: none;">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Destination Location</label>
                                                                    <select class="form-control selecter p-0" name="mc_destination_location" id="mc_destination_location" style="color:#222d32">
                                                                        <option selected disabled value="">Select Destination Location</option>
                                                                    </select> 
                                                                </div>
                                                                <span class="text-danger" id="mc_destination_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6" id="patientSelect">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Patient MR# <small class="text-danger mr_optional" style="font-size:11px;">(Optional)</small></label>
                                                                    <select class="form-control selecter p-0" name="mc_patient" id="mc_patient" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="serviceSelect">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Service</label>
                                                                    <select class="form-control selecter p-0" name="mc_service" id="mc_service" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="mc_remarks">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" rows="1" placeholder="Enter Remarks" name="mc_remarks" id="mc_remarks"></textarea>
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
                                                                    <select class="form-control selecter p-0 mc_itemgeneric" name="mc_itemgeneric[]" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger mc_itemgeneric_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Demand Qty</label>
                                                                    <input type="number" class="form-control input-sm" placeholder="Demand Qty.." name="mc_qty[]">
                                                                </div>
                                                                <span class="text-danger mc_qty_error"></span>
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
                <table id="view-materialconsumption" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Requisition Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-materialconsumption" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Patient Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_materialconsumption" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" class="form-control u_mc-id" name="u_mc-id">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            
                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Organization</label>
                                                            <select class="form-control selecter p-0" name="u_mc_org" required id="u_mc_org" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Site</label>
                                                            <select class="form-control selecter p-0" name="u_mc_site" required id="u_mc_site" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Transaction type</label>
                                                            <select class="form-control selecter p-0" name="u_mc_transactionType" required id="u_mc_transactionType" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 u_mc_source_location" style="display: none;">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Source Location</label>
                                                            <select class="form-control selecter p-0" name="u_mc_source_location" id="u_mc_source_location" style="color:#222d32">
                                                                <option selected disabled value="">Select Source Location</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 u_mc_destination_location" style="display: none;">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Destination Location</label>
                                                            <select class="form-control selecter p-0" name="u_mc_destination_location" id="u_mc_destination_location" style="color:#222d32">
                                                                <option selected disabled value="">Select Destination Location</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Patient MR# <small class="text-danger umr_optional" style="font-size:11px;">(Optional)</small></label>
                                                            <select class="form-control selecter p-0" name="u_mc_patient"  id="u_mc_patient" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_serviceSelect">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Service <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <select class="form-control selecter p-0" name="u_mc_service"  id="u_mc_service" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <textarea class="form-control" rows="1" id="u_mc_remarks" name="u_mc_remarks" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                          
                                        </div>
                                        <div class="uduplicate">
                                        </div>

                                        <div class="d-flex justify-content-center pt-3">
                                            <button type="button" id="umc_addmore" class="btn btn-success mr-2">
                                            <i class="mdi mdi-plus"></i> Add More</button>

                                            <button type="button" id="umc_remove" class="btn btn-danger mr-2"> 
                                            <i class="mdi mdi-minus"></i> Remove</button>
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

    <!-- ============================================================== -->
    <!-- Start Footer  -->
    <!-- ============================================================== -->
    @include('partials/footer')
    <!-- ============================================================== -->
    <!-- End Footer  -->
    <!-- ============================================================== -->
    <script>
        $('.selectpicker').selectpicker();
        $('#date-format').bootstrapMaterialDatePicker({
                format: 'dddd DD MMMM YYYY - hh:mm A',
                currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });
    </script>
    <script src="{{ asset('assets/custom/material-consumption.js') }}"></script>