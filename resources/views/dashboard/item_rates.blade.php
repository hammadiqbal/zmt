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
                    <li class="breadcrumb-item active">Item Rate</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h4 class="card-title">All Item Rates</h4>
                    </div>
                    @php
                    $Itemrates = explode(',', $rights->item_rates);
                    $add = $Itemrates[0];
                    $view = $Itemrates[1];
                    $edit = $Itemrates[2];
                    $updateStatus = $Itemrates[3];
                    @endphp
                    @if ($add == 1)
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary p-2 add-itemrate">
                            <i class="mdi mdi-bank"></i> Add Item Rates
                        </button>
                    </div>
                    @endif
                </div>

                @if ($add == 1)
                <div class="modal fade bs-example-modal-lg" id="add-itemrate" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Add Item Rate</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <form id="add_itemrate" method="post">
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
                                                            <select class="form-contro selecter p-0" id="ir_org" name="ir_org">
                                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                            </select>
                                                        </div>
                                                        @else
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Organization</label>
                                                                        <select class="form-control selecter p-0" name="ir_org" id="ir_org" style="color:#222d32">
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
                                                                        <select class="form-control selecter p-0" name="ir_site" id="ir_site" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="ir_site_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Item Generic</label>
                                                                        <select class="form-control selecter p-0" name="ir_generic" id="ir_generic" style="color:#222d32">
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="ir_generic_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Item Brand</label>
                                                                        <select class="form-control selecter p-0" name="ir_brand" id="ir_brand" style="color:#222d32" disabled>
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="ir_brand_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Batch Number</label>
                                                                        <select class="form-control selecter p-0" name="ir_batch" id="ir_batch" style="color:#222d32" disabled>
                                                                            <option selected disabled value="">Select Batch Number</option>
                                                                        </select>
                                                                    </div>
                                                                    <span class="text-danger" id="ir_batch_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Pack Size</label>
                                                                        <input type="number" min="1" class="form-control input-sm" placeholder="Enter Pack Size" name="ir_packsize" id="ir_packsize">
                                                                    </div>
                                                                    <span class="text-danger" id="ir_packsize_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Unit Cost</label>
                                                                        <input type="number" step="0.01" class="form-control input-sm" placeholder="Enter Unit Cost" name="ir_unitcost" id="ir_unitcost">
                                                                    </div>
                                                                    <span class="text-danger" id="ir_unitcost_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" id="ft_discount">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label for="input01">Billed Amount</label>
                                                                        <input type="number" step="0.01" class="form-control input-sm" placeholder="Enter Billed Amount" name="ir_billedamount" id="ir_billedamount">
                                                                    </div>
                                                                    <span class="text-danger" id="ir_billedamount_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Effective Date&Time</label>
                                                                        <input type="text" id="date-format" name="ir_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
                                                                    </div>
                                                                    <span id="ir_edt_error" class="text-danger"></span>
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
                    <table id="view-itemrate" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Item Details</th>
                                <th>Unit Cost</th>
                                <th>Billed Amount</th>
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
        <div class="modal fade bs-example-modal-lg" id="edit-itemrate" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Item Rate</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="update_itemrate">
                        @csrf
                        <div class="modal-body">
                            <!-- Row -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <input type="hidden" name="ir-id" id="ir-id">
                                                @if($user->org_id == 0)
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Organization</label>
                                                                <select class="form-control selecter p-0" required name="u_ir_org" id="u_ir_org" style="color:#222d32">
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
                                                                <select class="form-control selecter p-0" required name="u_ir_site" id="u_ir_site" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Item Generic</label>
                                                                <select class="form-control selecter p-0" required name="u_ir_generic" id="u_ir_generic" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Item Brand</label>
                                                                <select class="form-control selecter p-0" required name="u_ir_brand" id="u_ir_brand" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Batch Number</label>
                                                                <select class="form-control selecter p-0" required name="u_ir_batch" id="u_ir_batch" style="color:#222d32">
                                                                    <option selected disabled value="">Select Batch Number</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                               
                                             

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Pack Size</label>
                                                                <input type="number" min="1" required class="form-control input-sm" name="u_ir_packsize" id="u_ir_packsize">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Cost Amount</label>
                                                                <input type="number" step="0.01" required class="form-control input-sm" name="u_ir_unitcost" id="u_ir_unitcost">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6" id="uft_discount">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Update Billed Amount</label>
                                                                <input type="number" class="form-control input-sm" required name="u_ir_billedamount" id="u_ir_billedamount">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Update Effective Date&Time</label>
                                                                <input type="text" id="u_ir_edt" required name="u_ir_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
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

    <script src="{{ asset('assets/custom/item_rates.js') }}"></script>


