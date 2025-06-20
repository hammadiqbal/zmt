
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
                <li class="breadcrumb-item active">Requisition For Other Transactions</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Requisition For Other Transactions</h4>
                </div>
                @php
                $Requisition_for_other_transaction = explode(',', $rights->requisition_for_other_transaction);
                $add = $Requisition_for_other_transaction[0];
                $view = $Requisition_for_other_transaction[1];
                $edit = $Requisition_for_other_transaction[2];
                $updateStatus = $Requisition_for_other_transaction[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-othertransaction">
                        <i class="mdi mdi-clipboard-account"></i> Add Requisition For Other Transactions
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-othertransaction" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Requisition For Other Transactions</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_othertransaction" method="post" enctype="multipart/form-data">
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
                                                        <select class="form-contro selecter p-0" id="rot_org" name="rot_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="rot_org" id="rot_org" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="rot_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" name="rot_site" id="rot_site" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="rot_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Transaction Type</label>
                                                                    <select class="form-control selecter p-0" name="rot_transactiontype" id="rot_transactiontype" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="rot_transactiontype_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Inventory Locations </label>
                                                                    <select class="form-control selecter p-0" name="rot_inv_location" id="rot_inv_location" style="color:#222d32">
                                                                        <option selected disabled >Select Inventory Location</option>
                                                                        @foreach ($ServiceLocations as $ServiceLocation)
                                                                            <option value="{{ $ServiceLocation['id'] }}">{{ $ServiceLocation['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rot_inv_location_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="rot_remarks">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" rows="1" placeholder="Enter Remarks" name="rot_remarks" id="rot_remarks"></textarea>
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
                                                                    <select class="form-control selecter p-0 rot_itemgeneric" name="rot_itemgeneric[]" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger rot_itemgeneric_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Demand Qty</label>
                                                                    <input type="number" class="form-control input-sm" placeholder="Demand Qty.." name="rot_qty[]">
                                                                </div>
                                                                <span class="text-danger rot_qty_error"></span>
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
                <table id="view-othertransaction" class="table table-bordered table-striped">
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
    <div class="modal fade bs-example-modal-lg" id="edit-othertransaction" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Patient Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_othertransaction" method="post" enctype="multipart/form-data">
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
                                                            <select class="form-control selecter p-0" name="u_rot_org" required id="u_rot_org" style="color:#222d32">
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
                                                            <select class="form-control selecter p-0" name="u_rot_site" required id="u_rot_site" style="color:#222d32">
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
                                                            <select class="form-control selecter p-0" name="u_rot_transactiontype" required id="u_rot_transactiontype" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Inventory Location</label>
                                                            <select class="form-control selecter p-0" name="u_rot_inv_location" id="u_rot_inv_location" style="color:#222d32">
                                                                @foreach ($ServiceLocations as $ServiceLocation)
                                                                    <option value="{{ $ServiceLocation['id'] }}">{{ $ServiceLocation['name'] }}</option>
                                                                @endforeach
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
                                                            <textarea class="form-control" rows="1" id="u_rot_remarks" name="u_rot_remarks" spellcheck="false"></textarea>
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
    <script src="{{ asset('assets/custom/req_other_transaction.js') }}"></script>