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
                <li class="breadcrumb-item">Material Management</li>
                <li class="breadcrumb-item active">Other Transactions</li>
            </ol>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Other Transactions</h4>
                </div>
                @php
                $IssueAndDispense = explode(',', $rights->issue_and_dispense);
                $add = $IssueAndDispense[0];
                $view = $IssueAndDispense[1];
                $respond = $IssueAndDispense[2];
                // $updateStatus = $IssueAndDispense[3];
                @endphp
                @if ($RequisitionNonMandatory)
                    @if ($add == 1)
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary p-2 add-othertransaction">
                            <i class="mdi mdi-clipboard-account"></i> New Transaction
                        </button>
                    </div>
                    @endif
                @endif
            </div>
            
            @if ($add == 1 || $respond == 1)
            <div class="modal fade bs-example-modal-lg" id="add-othertransaction" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Other Transactions Details</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="row" id="transaction-info-row" style="width:98%;display:none;font-size:13px;border:1px solid black;margin: 0 auto;"></div>

                        <form id="add_othertransaction" method="post" enctype="multipart/form-data">
                            <input type="hidden" id="source_applicable" name="source_applicable" value="1">
                            <input type="hidden" id="destination_applicable" name="destination_applicable" value="1">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body pt-0">
                                            <div class="form-body">
                                                <div class="row">
                                                    @if($user->org_id != 0)
                                                    <div class="userOrganization">
                                                        <select class="form-contro selecter p-0" id="ot_org" name="ot_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="ot_org" id="ot_org" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ot_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Site</label>
                                                                    <select class="form-control selecter p-0" name="ot_site" id="ot_site" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ot_site_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    

                                                    <div class="col-md-6" >
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Requested Transaction</label>
                                                                    <select class="form-control selecter p-0" name="ot_transactiontype" id="ot_transactiontype" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ot_transactiontype_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    

                                                    <div class="col-md-6" id="od_sl">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Inventory Source</label>
                                                                    <select class="form-control selecter p-0" name="ot_source" id="ot_source" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ot_source_error"></span>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="od_dl">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Inventory Destination</label>
                                                                    <select class="form-control selecter p-0" name="ot_destination" id="ot_destination" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="ot_destination_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    @if($user->org_id != 0)
                                                        @if($costcenters->count() > 1)
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">Performing CC</label>
                                                                            <select class="form-control selecter p-0" id="ot_performing_cc" name="ot_performing_cc" style="color:#222d32">
                                                                                <option selected disabled value="">Select Performing CC</option>
                                                                                @foreach($costcenters as $cc)
                                                                                    <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <span class="text-danger" id="ot_performing_cc_error"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @elseif($costcenters->count() == 1)
                                                            <input type="text" class="form-control input-sm" readonly name="ot_performing_cc" value="{{ $costcenters->first()->id }}">
                                                            @endif
                                                        @else
                                                            <input type="hidden" name="ot_performing_cc" value="0">
                                                    @endif


                                                    <div class="col-md-6" >
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Reference Document# <small class="text-danger" style="font-size:11px;">(Optional)</small> </label>
                                                                    <input type="text" placeholder="Enter Reference Document#"  name="ot_reference_document" class="form-control input-sm">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-10">
                                                                    <label class="control-label">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Enter Remarks" rows="2" name="ot_remarks" spellcheck="false"></textarea>
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
                                                                        <select class="form-control selecter p-0 ot_generic" name="ot_generic[]" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger ot_generic_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Select Brand</label>
                                                                        <select class="form-control selecter p-0 ot_brand" name="ot_brand[]" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger ot_brand_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 ">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Demand Qty</label>
                                                                        <input type="number" class="form-control input-sm ot_demand_qty" placeholder="Demand Qty..." name="ot_demand_qty[]">
                                                                    </div>
                                                                    <span class="text-danger ot_demand_qty_error" ></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
    
                                                        <div class="col-md-6 brand_details">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Batch #</label>
                                                                        <input type="text" class="form-control input-sm ot_batch" placeholder="Batch #.." name="ot_batch[]">
                                                                    </div>
                                                                    <span class="text-danger ot_batch_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 brand_details">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Expiry Date</label>
    
                                                                        <input type="text" name="ot_expiry[]" class="form-control input06 qd ot_expiry" placeholder="Select Expiry Date">
                                                                    </div>
                                                                    <span class="text-danger ot_expiry_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
    
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Enter Transaction Qty</label>
                                                                        <input type="number" class="form-control input-sm ot_qty" placeholder="Transaction Qty..." name="ot_qty[]">
                                                                    </div>
                                                                    <span class="text-danger ot_qty_error" ></span>
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
                <table id="view-othertransaction" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Transaction Details</th>
                            <th>Item Details</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endif
        </div>
    </div>



    {{-- @if ($edit == 1)
    <div class="modal fade bs-example-modal-lg" id="edit-externaltransactions" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Inventory Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_externaltransactions" method="post" enctype="multipart/form-data">
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
                                                            <select class="form-control selecter p-0" name="u_et_transactiontype" required id="u_et_transactiontype" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($user->org_id != 0)
                                                <div class="userOrganization">
                                                    <select class="form-contro selecter p-0" id="u_et_org" name="u_et_org">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                            @else
                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Organization</label>
                                                            <select class="form-control selecter p-0" name="u_et_org" required id="u_et_org" style="color:#222d32">
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
                                                            <select class="form-control selecter p-0" name="u_et_site" required id="u_et_site" style="color:#222d32">
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
                                                            <input type="text" id="u_et_expirydate" class="form-control input06" required name="u_et_expirydate">
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
    @endif --}}



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
    <script src="{{ asset('assets/custom/other_transactions.js') }}"></script>
