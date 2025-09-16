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
                    <li class="breadcrumb-item active">Financial Payment</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h4 class="card-title">All Financial Payments</h4>
                    </div>
                    @php
                    $FinancePayment = explode(',', $rights->finance_payment);
                    $add = $FinancePayment[0];
                    $view = $FinancePayment[1];
                    $edit = $FinancePayment[2];
                    $updateStatus = $FinancePayment[3];
                    @endphp
                    @if ($add == 1)
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary p-2 add-financepayment">
                            <i class="mdi mdi-bank"></i> Add Financial Payment
                        </button>
                    </div>
                    @endif
                </div>

                @if ($add == 1)
                <div class="modal fade bs-example-modal-lg" id="add-financepayment" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Add Financial Payment</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form id="add_financepayment" method="post">
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
                                                            <select class="form-contro selecter p-0" id="fp_org" name="fp_org">
                                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                            </select>
                                                        </div>
                                                        @else
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Organization</label>
                                                                        <select class="form-control selecter p-0" name="fp_org" id="fp_org" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fp_org_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Site</label>
                                                                        <select class="form-control selecter p-0" name="fp_site" id="fp_site" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fp_site_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Transaction Type</label>
                                                                        <select class="form-control selecter p-0" name="fp_transactiontype" id="fp_transactiontype" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fp_transactiontype_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Payment Options</label>
                                                                        <select class="form-control selecter p-0" name="fp_paymentoption" id="fp_paymentoption" style="color:#222d32">
                                                                            <option selected disabled value="">Select Payment Option</option>
                                                                            <option>Cash</option>
                                                                            <option>Credit</option>
                                                                            <option>Transfer</option>
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="fp_paymentoption_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Payment Option Details <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                        <input type="text" class="form-control input-sm" placeholder="Cheque No etc." name="fp_paymentoptiondetails" id="input01">
                                                                    </div>
                                                                    <span class="text-danger" id="fp_paymentoptiondetails_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Amount</label>
                                                                        <input type="number" class="form-control input-sm" placeholder="Enter Amount" name="fp_amount" id="fp_amount">
                                                                    </div>
                                                                    <span class="text-danger" id="fp_amount_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" id="ft_discount">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Discount</label>
                                                                        <input type="number" class="form-control input-sm" placeholder="Enter Discount" name="fp_discount" id="fp_discount">
                                                                    </div>
                                                                    <span class="text-danger" id="fp_discount_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="ftt_desc">Enter Remarks</label>
                                                                        <textarea class="form-control" rows="1" id="fp_remarks" name="fp_remarks" spellcheck="false" placeholder="Remarks.."></textarea>
                                                                    </div>
                                                                    <span class="text-danger" id="fp_remarks_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Effective Date&Time</label>
                                                                        <input type="text" id="date-format" name="fp_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
                                                                    </div>
                                                                    <span id="fp_edt_error" class="text-danger"></span>
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
                    <table id="view-financepayment" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Transaction Details</th>
                                <th>Credit Amount</th>
                                <th>Credit Account Balance</th>
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
        <div class="modal fade bs-example-modal-lg" id="edit-financepayment" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Financial Payment Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="update_financepayment">
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
                                                                <select class="form-control selecter p-0" required name="u_fp_org" id="u_fp_org" style="color:#222d32">
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
                                                                <select class="form-control selecter p-0" required name="u_fp_site" id="u_fp_site" style="color:#222d32">
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
                                                                <select class="form-control selecter p-0" required name="u_fp_transactiontype" id="u_fp_transactiontype" style="color:#222d32">
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
                                                                <select class="form-control selecter p-0" required name="u_fp_paymentoption" id="u_fp_paymentoption" style="color:#222d32">
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
                                                                <input type="text" class="form-control input-sm" placeholder="Cheque No etc." name="u_fp_paymentoptiondetails" id="u_fp_paymentoptiondetails">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Amount</label>
                                                                <input type="number" required class="form-control input-sm" name="u_fp_amount" id="u_fp_amount">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6" id="uft_discount">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Allowed Discount</label>
                                                                <input type="number" class="form-control input-sm" name="u_fp_discount" id="u_fp_discount">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="ftt_desc">Update  Remarks</label>
                                                                <textarea class="form-control" required rows="1" id="u_fp_remarks" name="u_fp_remarks" spellcheck="false"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Effective Date&Time</label>
                                                                <input type="text" id="u_fp_edt" required name="u_fp_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
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
        $('#u_fp_edt').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            minDate: new Date() 
        });
        </script>
    <script src="{{ asset('assets/custom/finance_payments.js') }}"></script>


