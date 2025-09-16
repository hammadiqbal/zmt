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
                    <li class="breadcrumb-item active">Purchase Order</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h4 class="card-title">All Purchase Orders For Material Procurement</h4>
                    </div>
                    @php
                    $PurchaseOrder = explode(',', $rights->purchase_order);
                    $add = $PurchaseOrder[0];
                    $view = $PurchaseOrder[1];
                    $edit = $PurchaseOrder[2];
                    $updateStatus = $PurchaseOrder[3];
                    @endphp
                    @if ($add == 1)
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary p-2 add-purchaseorder">
                            <i class="mdi mdi-database"></i> Add Purchase Order
                        </button>
                    </div>
                    @endif
                </div>

                @if ($add == 1)
                <div class="modal fade bs-example-modal-lg" id="add-purchaseorder" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Add Purchase Order</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form id="add_purchaseorder" method="post">
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
                                                            <select class="form-contro selecter p-0" id="po_org" name="po_org">
                                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                            </select>
                                                        </div>
                                                        @else
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Organization</label>
                                                                        <select class="form-control selecter p-0" name="po_org" id="po_org" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger po_org_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Site</label>
                                                                        <select class="form-control selecter p-0" name="po_site" id="po_site" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger po_site_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Vendor</label>
                                                                        <select class="form-control selecter p-0" name="po_vendor" id="po_vendor" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger po_vendor_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Effective Date&Time</label>
                                                                        <input type="text" id="date-format" name="po_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
                                                                    </div>
                                                                    <span class="text-danger po_edt_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row pt-4 pb-1 duplicate" style="border: 1px solid #939393;">

                                                        <div class="col-md-12 payable_amount text-center fw-bold" style="display:none;">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="netPayableAmount">Net Payable Amount: </label>
                                                                <span id="netPayableAmount" class="net_payable_amount" style="font-weight:bold;"></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Item Brand</label>
                                                                        <select class="form-control selecter p-0 po_brand" name="po_brand[]"  style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger po_brand_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input05">Demand Qty</label>
                                                                        <input type="number" placeholder="Qty.." class="form-control input-sm" name="po_qty[]" id="input05">
                                                                    </div>
                                                                    <span class="text-danger po_qty_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input0565">Amount</label>
                                                                        <input type="number" placeholder="Amount.." class="form-control input-sm po_amount" name="po_amount[]" id="input0565">
                                                                    </div>
                                                                    <span class="text-danger po_amount_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input0575">Discount Received</label>
                                                                        <input type="number" placeholder="Discount..." class="form-control input-sm po_discount" name="po_discount[]" id="input0575">
                                                                    </div>
                                                                    <span class="text-danger po_discount_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-10">
                                                                        <label for="input07">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                        <textarea class="form-control" placeholder="Remarks.." rows="1" name="po_remarks[]"  id="input07" spellcheck="false"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 d-flex justify-content-center p-2">
                                                        <button type="button" id="addMoreBtn" class="btn btn-success mr-2"> <i class="mdi mdi-plus"></i>  Add More</button>
                                                        <button type="button" id="removeBtn" class="btn btn-danger"> <i class="mdi mdi-minus"></i>  Remove</button>
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
                    <table id="view-purchaseorder" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>PO Details</th>
                                <th>Item Details</th>
                                {{-- <th>Other Details</th> --}}
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
        <div class="modal fade bs-example-modal-lg" id="edit-purchaseorder" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Purchase Order Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="update_purchaseorder">
                        @csrf
                        <div class="modal-body">
                            <!-- Row -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <input type="hidden" class="form-control" name="po-id" id="po-id">

                                                @if($user->org_id == 0)
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Organization</label>
                                                        <select class="form-control selecter p-0" name="u_po_org" required id="u_po_org" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Site</label>
                                                        <select class="form-control selecter p-0" name="u_po_site" required id="u_po_site" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Vendor</label>
                                                        <select class="form-control selecter p-0" name="u_po_vendor" required id="u_po_vendor" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="u_po_edt" name="u_po_edt" required class="form-control input06 uedt">
                                                    </div>
                                                </div>


                                            </div>
  
                                            <div class="uduplicate">
                                            </div>

                                            <div class="d-flex justify-content-center pt-3 upo_buttons">
                                                <button type="button" id="upo_addmore" class="btn btn-success mr-2">
                                                <i class="mdi mdi-plus"></i> Add More</button>

                                                <button type="button" id="upo_remove" class="btn btn-danger mr-2"> 
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
            $('#u_po_edt').bootstrapMaterialDatePicker({
                format: 'dddd DD MMMM YYYY - hh:mm A',
                minDate: new Date() 
            });
        </script>
        <script src="{{ asset('assets/custom/purchase_order.js') }}"></script>


