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
                <li class="breadcrumb-item active">Financial Transaction Type</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Financial Transaction Types </h4>
                </div>
                @php
                $FinanceTransactionType = explode(',', $rights->finance_transaction_types);
                $add = $FinanceTransactionType[0];
                $view = $FinanceTransactionType[1];
                $edit = $FinanceTransactionType[2];
                $updateStatus = $FinanceTransactionType[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-financetransactiontype">
                        <i class="mdi mdi-bank"></i> Add Financial Transaction Type
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-financetransactiontype" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Financial Transaction Type</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_financetransactiontype" method="post" enctype="multipart/form-data" >
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-body">
                                            <div class="row">

                                                @if($user->org_id != 0)
                                                <div class="userOrganization">
                                                    <select class="form-contro selecter p-0" id="ftt_org" name="ftt_org">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                                @else
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Organization</label>
                                                                <select class="form-control selecter p-0" name="ftt_org" id="ftt_org" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_org_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif


                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="ftt_desc">Enter Transaction Type Description</label>
                                                                <textarea class="form-control" placeholder="Enter Transaction Type Description" rows="1" id="ftt_desc" name="ftt_desc" spellcheck="false"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="ftt_desc_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Activity Type</label>
                                                                <select class="form-control selecter p-0" name="ftt_activitytype" id="ftt_activitytype" style="color:#222d32">
                                                                    <option selected disabled value="">Select Activity Type</option>
                                                                    <option value="inward">Inward</option>
                                                                    <option value="outward">OutWard</option>
                                                                    <option value="reverse">Reverse</option>
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_activitytype_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Transaction Source</label>
                                                                <select class="form-control selecter p-0" name="ftt_source" id="ftt_source" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_source_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Transaction Destination</label>
                                                                <select class="form-control selecter p-0" name="ftt_destination" id="ftt_destination" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_destination_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Debit Account</label>
                                                                <select class="form-control selecter p-0" name="ftt_debit" id="ftt_debit" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_debit_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Credit Account</label>
                                                                <select class="form-control selecter p-0" name="ftt_credit" id="ftt_credit" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_credit_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Ledger</label>
                                                                <select class="form-control selecter p-0" name="ftt_ledger" id="ftt_ledger" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_ledger_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Amount Editing Status</label>
                                                                <select class="form-control selecter p-0" name="ftt_amounteditable" id="ftt_amounteditable" style="color:#222d32">\
                                                                    <option selected disabled value="">Is Amount Editable?</option>
                                                                    <option value="yes">Yes</option>
                                                                    <option value="no">No</option>
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="ftt_amounteditable_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6" id="ceilingamount">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Enter Cieling Amount</label>
                                                                <input type="number" placeholder="Enter Cieling Amount" class="form-control input-sm" name="ftt_amountceiling" id="ftt_amountceiling">
                                                            </div>
                                                            <span class="text-danger" id="ftt_amountceiling_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Discount Status</label>
                                                                <select class="form-control selecter p-0" name="ftt_discountallowed" id="ftt_discountallowed" style="color:#222d32">\
                                                                    <option selected disabled value="">Is Discount Allowed?</option>
                                                                    <option value="yes">Yes</option>
                                                                    <option value="no">No</option>
                                                                </select>
                                                                <span class="bar"></span>
                                                            </div>
                                                            <span class="text-danger" id="ftt_discountallowed_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Effective DateTime</label>
                                                                <input type="text" id="date-format" name="ftt_edt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                            </div>
                                                            <span class="text-danger" id="ftt_edt_error"></span>
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
                <table id="view-financetransactiontype" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Transaction Type Details</th>
                            <th>Debit/Credit Account</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-financetransactiontype" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Inventory Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_financetransactiontype" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" class="form-control u_ftt-id" name="u_ftt-id">
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
                                                            <select class="form-control selecter p-0" name="u_ftt_org" required id="u_ftt_org" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6" id="u_patientSelect">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Transaction Type Description</label>
                                                            <input type="text" class="form-control input-sm" required name="u_ftt_desc" id="u_ftt_desc">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Activity Type</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_activity" required id="u_ftt_activity" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Transaction Source</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_source" required id="u_ftt_source" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Transaction Destination</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_destination" required id="u_ftt_destination" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Debit Account</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_debit" required id="u_ftt_debit" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Credit Account</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_credit" required id="u_ftt_credit" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Ledger Type</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_ledger" required id="u_ftt_ledger" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Amount Editing Status</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_amounteditable" required id="u_ftt_amounteditable" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="u_ceilingamount">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Ceiling Amount</label>
                                                            <input type="number" class="form-control input-sm" name="u_ftt_amountceiling" id="u_ftt_amountceiling">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Discount Allowed Status</label>
                                                            <select class="form-control selecter p-0" name="u_ftt_discountallowed" required id="u_ftt_discountallowed" style="color:#222d32">
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
                                                            <input type="text" id="u_ftt_edt" class="form-control input06 dt uedt" required name="u_ftt_edt" >
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
        $('#u_ftt_edt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });       
        $('#u_im_expirydate').bootstrapMaterialDatePicker({ weekStart : 0, time: false });
        $('.selectpicker').selectpicker();
    </script>
    <script src="{{ asset('assets/custom/finance_transaction_type.js') }}"></script>


