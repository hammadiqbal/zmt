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
    {{-- @include('partials/topbar') --}}
    <!-- ============================================================== -->
    <!-- End Top Bar  -->
    <!-- ============================================================== -->


    <!-- ============================================================== -->
    <!-- Start Side Bar  -->
    <!-- ============================================================== -->
    {{-- @include('partials/sidebar') --}}
    <!-- ============================================================== -->
    <!-- End Side Bar  -->
    <!-- ============================================================== -->


    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Payment Order</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h4 class="card-title">All Payment Orders For Material Procurement</h4>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary p-2 add-paymentorder">
                            <i class="mdi mdi-database"></i> Add Payment Order
                        </button>
                    </div>
                </div>

                <div class="modal fade bs-example-modal-lg" id="add-paymentorder" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Add Payment Order</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form id="add_paymentorder" method="post" class="floating-labels form-horizontal">
                                @csrf
                                <div class="modal-body">
                                    <!-- Row -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card-body">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <select class="form-control selecter p-0" name="po_org" id="po_org" style="color:#222d32">
                                                                        </select>
                                                                        <span class="bar"></span>
                                                                    </div>
                                                                    <span class="text-danger" id="po_org_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <select class="form-control selecter p-0" name="po_site" id="po_site" style="color:#222d32">
                                                                        </select>
                                                                        <span class="bar"></span>
                                                                    </div>
                                                                    <span class="text-danger" id="po_site_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <select class="form-control selecter p-0" name="po_vendor" id="po_vendor" style="color:#222d32">
                                                                        </select>
                                                                        <span class="bar"></span>
                                                                    </div>
                                                                    <span class="text-danger" id="po_vendor_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <input type="text" id="date-format" name="po_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
                                                                    </div>
                                                                    <span class="text-danger" id="po_edt_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row pt-4 pb-1 duplicate" style="border: 1px solid #939393;">
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <select class="form-control selecter p-0 po_brand" name="po_brand[]" style="color:#222d32"></select>
                                                                    </div>
                                                                    <span class="text-danger po_brand_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="control-label">Demand Qty</label>
                                                                <input type="number" class="form-control" rows="1" name="po_qty[]">
                                                                <span class="text-danger po_qty_error"></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="control-label">Amount</label>
                                                                <input type="number" class="form-control" rows="1"  name="po_amount[]">
                                                                <span class="text-danger po_amount_error"></span>

                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="control-label">Discount Received</label>
                                                                <input type="number" class="form-control" rows="1"  name="po_discount[]">
                                                                <span class="text-danger po_discount_error"></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="control-label">Enter Remarks</label>
                                                                <input type="number" class="form-control" rows="1"  name="po_remarks[]">
                                                                <span class="text-danger po_remarks_error"></span>
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

                {{-- <div class="table-responsive m-t-40">
                    <table id="view-paymentorder" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Description</th>
                                <th>Address</th>
                                <th>Cell #</th>
                                <th>Landline #</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div> --}}
            </div>
        </div>

        <div class="modal fade bs-example-modal-lg" id="edit-paymentorder" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Inventory Vendor Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="update_paymentorder">
                        @csrf
                        <div class="modal-body">
                            <!-- Row -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="hidden" class="form-control u_vendor-id" name="u_vendor-id">
                                                        <label class="control-label">Update Vendor Description</label>
                                                        <input type="text" name="u_vendor_desc" required class="form-control u_vendor_desc">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Organization</label>
                                                        <select class="form-control p-0" name="u_vendor_org" required id="u_vendor_org" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Vendor Address</label>
                                                        <textarea class="form-control u_vendor_address" rows="1" id="input04" required name="u_vendor_address" spellcheck="false"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Focal Person Name</label>
                                                        <input type="text" name="u_vendor_name" required class="form-control u_vendor_name">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Focal Person Email</label>
                                                        <input type="email" name="u_vendor_email" required class="form-control u_vendor_email">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Cell #</label>
                                                        <input type="text" name="u_vendor_cell" required class="form-control u_vendor_cell">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Landline <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                        <input type="text" name="u_vendor_landline" class="form-control u_vendor_landline">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Remarks</label>
                                                        <textarea class="form-control u_vendor_remarks" rows="1" id="input04" required name="u_vendor_remarks" spellcheck="false"></textarea>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="date-format1" name="u_vendor_edt" required class="form-control input06 uedt">
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


        <!-- ============================================================== -->
        <!-- Start Footer  -->
        <!-- ============================================================== -->
        @include('partials/footer')
        <!-- ============================================================== -->
        <!-- End Footer  -->
        <!-- ============================================================== -->

        <script>
            $('#date-format').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });
            $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });
        </script>

