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
                <li class="breadcrumb-item active">Inventory Management</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Managed Inventories </h4>
                </div>
                @php
                $ItemManagement = explode(',', $rights->inventory_management);
                $add = $ItemManagement[0];
                $view = $ItemManagement[1];
                $edit = $ItemManagement[2];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-manageinventory">
                        <i class="mdi mdi-clipboard-account"></i> Manage Inventory
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-manageinventory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Manage Inventory</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_manageinventory" method="post" enctype="multipart/form-data">
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
                                                                   <label class="control-label">Transaction Type</label>
                                                                    <select class="form-control selecter p-0" name="im_transactiontype" id="im_transactiontype" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="im_transactiontype_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" id="inv-management-section">

                                                    @if($user->org_id != 0)
                                                        <div class="userOrganization">
                                                            <select class="form-contro selecter p-0" id="im_org" name="im_org">
                                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                            </select>
                                                        </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                   <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="im_org" id="im_org" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="im_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                   <label class="control-label">Site</label>

                                                                    <select class="form-control selecter p-0" name="im_site" id="im_site" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="im_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                   <label class="control-label">Brand</label>

                                                                    <select class="form-control selecter p-0" name="im_brand" id="im_brand" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="im_brand_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6" >
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5" id="enter_batch">
                                                                    <label for="im_reference_document">Enter Batch #</label>
                                                                    <input type="text" placeholder="Enter Batch #" class="form-control input-sm">
                                                                </div>
                                                                <div class="form-group has-custom m-b-5" id="select_batch">
                                                                    <label class="control-label">Select Batch  #</label>
                                                                    <select class="form-control selecter p-0" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="im_batch_no_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6" id="itemexpiry">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Expiry Date</label>

                                                                    <input type="text" id="expiry-date" name="im_expiry" class="form-control input06" placeholder="Select Expiry Date">
                                                                </div>
                                                                <span class="text-danger" id="im_expiry_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="itemrate">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="im_rate">Enter Item Rate</label>
                                                                    <input type="number" class="form-control input-sm"  placeholder="Enter Item Rate" name="im_rate" id="im_rate">
                                                                </div>
                                                                <span class="text-danger" id="im_rate_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="itemQty">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="im_qty">Enter Transaction Qty</label>
                                                                    <input type="number" class="form-control input-sm" placeholder="Enter Transaction Qty" name="im_qty" id="im_qty">
                                                                </div>
                                                                <span class="text-danger" id="im_qty_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="reference_document_section">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5" id="opentext">
                                                                    <label for="im_reference_document">Enter Reference Document #</label>
                                                                    <input type="text" placeholder="Enter Reference Document #" class="form-control input-sm">
                                                                </div>
                                                                <div class="form-group has-custom m-b-5" id="selectoption">
                                                                    <label class="control-label">Reference Document #</label>
                                                                    <select class="form-control selecter p-0" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="im_reference_document_error"></span> 
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="from_section">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Origin</label>
                                                                    <select class="form-control selecter p-0" name="im_origin" id="im_origin" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="im_origin_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="to_section">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Destination</label>
                                                                    <select class="form-control selecter p-0" name="im_destination" id="im_destination" style="color:#222d32">
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="im_destination_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" name="im_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="im_edt_error"></span>
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
                <table id="view-manageinventory" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Transaction Details</th>
                            <th>Item Details</th>
                            <th>Inventory Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-manageinventory" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Inventory Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_manageinventory" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" class="form-control u_im-id" name="u_im-id">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Transaction Type</label>
                                                            <select class="form-control selecter p-0" name="u_im_transactiontype" required id="u_im_transactiontype" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($user->org_id != 0)
                                                <div class="userOrganization">
                                                    <select class="form-contro selecter p-0" id="u_im_org" name="u_im_org">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                            @else
                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Organization</label>
                                                            <select class="form-control selecter p-0" name="u_im_org" required id="u_im_org" style="color:#222d32">
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
                                                            <select class="form-control selecter p-0" name="u_im_site" required id="u_im_site" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Item Brand</label>
                                                            <select class="form-control selecter p-0" name="u_im_brand" required id="u_im_brand" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6" id="u_patientSelect">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        {{-- <div class="form-group m-b-5">
                                                            <label class="control-label">Update Item Batch#</label>
                                                            <input type="text" class="form-control input-sm" required name="u_im_batch_no" id="u_im_batch_no">
                                                        </div> --}}

                                                        <div class="form-group m-b-5" id="u_enter_batch">
                                                            <label for="im_reference_document">Enter Batch #</label>
                                                            <input type="text" placeholder="Enter Batch #" class="form-control input-sm">
                                                        </div>
                                                        <div class="form-group m-b-5" id="u_select_batch">
                                                            <label class="control-label">Select Batch  #</label>
                                                            <select class="form-control selecter p-0" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_itemexpiry">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Expiry Date</label>
                                                            <input type="text" id="u_im_expirydate" class="form-control input06" required name="u_im_expirydate">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_itemrate">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Item Rate</label>
                                                            <input type="number" class="form-control input-sm" required name="u_im_rate" id="u_im_rate">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_itemQty">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Transaction Qty</label>
                                                            <input type="number" class="form-control input-sm" required name="u_im_qty" id="u_im_qty">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_reference_document_section">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5" id="u_opentext">
                                                            <label class="control-label">Update Reference Document #</label>
                                                            <input type="text" class="form-control input-sm">
                                                        </div>
                                                        <div class="form-group has-custom m-b-5" id="u_selectoption">
                                                            <label class="control-label">Reference Document #</label>
                                                            <select class="form-control selecter p-0" style="color:#222d32">
                                                            </select>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_from_section">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Update Origin</label>
                                                            <select class="form-control selecter p-0" name="u_im_origin" id="u_im_origin" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_to_section">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Update Destination</label>
                                                            <select class="form-control selecter p-0" name="u_im_destination" id="u_im_destination" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Effective Date&Time</label>
                                                            <input type="text" id="u_im_edt" class="form-control input06 dt uedt" required name="u_im_edt" >
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
        $('#expiry-date').bootstrapMaterialDatePicker({
            weekStart : 0, time: false,
            minDate: new Date() 
        });
        $('#u_im_expirydate').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('.selectpicker').selectpicker();
    </script>

