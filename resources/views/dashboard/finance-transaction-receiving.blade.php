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
            <div class="col-md-12 d-flex justify-content-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Financial Receiving</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h4 class="card-title">All Financial Receivings</h4>
                    </div>
                    @php
                    $FinanceReceiving = explode(',', $rights->finance_receiving);
                    $add = $FinanceReceiving[0];
                    $view = $FinanceReceiving[1];
                    $edit = $FinanceReceiving[2];
                    $updateStatus = $FinanceReceiving[3];
                    @endphp
                    @if ($add == 1)
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary p-2 add-financereceiving">
                            <i class="mdi mdi-bank"></i> Add Financial Receiving
                        </button>
                    </div>
                    @endif
                </div>

                @if ($add == 1)
                <div class="modal fade bs-example-modal-lg" id="add-financereceiving" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Add Financial Receiving</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form id="add_financereceiving" method="post">
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
                                                            <select class="form-contro selecter p-0" id="fr_org" name="fr_org">
                                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                            </select>
                                                        </div>
                                                        @else
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Organization</label>
                                                                        <select class="form-control selecter p-0" name="fr_org" id="fr_org" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fr_org_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Site</label>
                                                                        <select class="form-control selecter p-0" name="fr_site" id="fr_site" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fr_site_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Transaction Type</label>
                                                                        <select class="form-control selecter p-0" name="fr_transactiontype" id="fr_transactiontype" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fr_transactiontype_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Payment Options</label>
                                                                        <select class="form-control selecter p-0" name="fr_paymentoption" id="fr_paymentoption" style="color:#222d32">
                                                                            <option selected disabled value="">Select Payment Option</option>
                                                                            <option>Cash</option>
                                                                            <option>Credit</option>
                                                                            <option>Transfer</option>
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fr_paymentoption_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Payment Option Details <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                        <input type="text" class="form-control input-sm" placeholder="Cheque No etc." name="fr_paymentoptiondetails" id="input01">
                                                                    </div>
                                                                    <span class="text-danger" id="fr_paymentoptiondetails_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Amount</label>
                                                                        <input type="number" class="form-control input-sm" placeholder="Enter Amount" name="fr_amount" id="fr_amount">
                                                                    </div>
                                                                    <span class="text-danger" id="fr_amount_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" id="ft_discount">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Discount</label>
                                                                        <input type="number" class="form-control input-sm" placeholder="Enter Discount" name="fr_discount" id="fr_discount">
                                                                    </div>
                                                                    <span class="text-danger" id="fr_discount_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="ftt_desc">Enter Remarks</label>
                                                                        <textarea class="form-control" rows="1" id="fr_remarks" name="fr_remarks" spellcheck="false" placeholder="Remarks.."></textarea>
                                                                    </div>
                                                                    <span class="text-danger" id="fr_remarks_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Effective Date&Time</label>
                                                                        <input type="text" id="date-format" name="fr_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
                                                                    </div>
                                                                    <span id="fr_edt_error" class="text-danger"></span>
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
                    <table id="view-financereceiving" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Transaction Details</th>
                                <th>Debit Amount</th>
                                <th>Debit Account Balance</th>
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
        <div class="modal fade bs-example-modal-lg" id="edit-financereceiving" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Financial Receiving</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="update_financereceiving">
                        @csrf
                        <div class="modal-body">
                            <!-- Row -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <input type="hidden" name="ft-id" id="ft-id">
                                                @if($user->org_id == 0)
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Organization</label>
                                                                <select class="form-control selecter p-0" required name="u_fr_org" id="u_fr_org" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Site</label>
                                                                <select class="form-control selecter p-0" required name="u_fr_site" id="u_fr_site" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Transaction Type</label>
                                                                <select class="form-control selecter p-0" required name="u_fr_transactiontype" id="u_fr_transactiontype" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Payment Options</label>
                                                                <select class="form-control selecter p-0" required name="u_fr_paymentoption" id="u_fr_paymentoption" style="color:#222d32">
                                                                    <option value="Cash">Cash</option>
                                                                    <option value="Credit">Credit</option>
                                                                    <option value="Transfer">Transfer</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Payment Option Details <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                <input type="text" class="form-control input-sm" placeholder="Cheque No etc." name="u_fr_paymentoptiondetails" id="u_fr_paymentoptiondetails">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Amount</label>
                                                                <input type="number" required class="form-control input-sm" name="u_fr_amount" id="u_fr_amount">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6" id="uft_discount">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Allowed Discount</label>
                                                                <input type="number" class="form-control input-sm" name="u_fr_discount" id="u_fr_discount">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="ftt_desc">Update  Remarks</label>
                                                                <textarea class="form-control" required rows="1" id="u_fr_remarks" name="u_fr_remarks" spellcheck="false"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Effective Date&Time</label>
                                                                <input type="text" id="u_fr_edt" required name="u_fr_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
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
            $('#u_fr_edt').bootstrapMaterialDatePicker({
                format: 'dddd DD MMMM YYYY - hh:mm A',
                minDate: new Date() 
            });
        </script>
    <script src="{{ asset('assets/custom/finance_receiving.js') }}"></script>


