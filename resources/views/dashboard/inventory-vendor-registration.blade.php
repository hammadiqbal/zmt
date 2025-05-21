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
                <li class="breadcrumb-item active">Vendor Registration</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Registered Vendors</h4>
                </div>
                @php
                $ItemVendorRegistration = explode(',', $rights->vendor_registration);
                $add = $ItemVendorRegistration[0];
                $view = $ItemVendorRegistration[1];
                $edit = $ItemVendorRegistration[2];
                $updateStatus = $ItemVendorRegistration[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-vendorregistration">
                        <i class="mdi mdi-database"></i> Register Vendor
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-vendorregistration" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Register Vendor</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_vendorregistration" method="post">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input01">Enter Vendor Description</label>
                                                                <input type="text" placeholder="Enter Vendor Description" class="form-control input-sm" name="vendor_desc" id="input01">
                                                            </div>
                                                            <span class="text-danger" id="vendor_desc_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($user->org_id != 0)
                                                <div class="userOrganization">
                                                    <select class="form-contro selecter p-0" id="vendor_org" name="vendor_org">
                                                        <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                    </select>
                                                </div>
                                                @else
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Organization</label>
                                                                <select class="form-control selecter p-0" name="vendor_org" id="vendor_org" style="color:#222d32">
                                                                </select>
                                                            </div>
                                                            <span class="text-danger" id="vendor_org_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-10">
                                                                <label for="input03">Enter Vendor Address</label>
                                                                <textarea class="form-control" placeholder="Enter Vendor Address" rows="1" name="vendor_address"  id="input03" spellcheck="false"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="vendor_address_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input02">Enter Focal Person Name</label>
                                                                <input type="text" placeholder="Enter Focal Person Name" class="form-control input-sm" name="vendor_name" id="input02">
                                                            </div>
                                                            <span class="text-danger" id="vendor_name_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input04">Enter Focal Person Email</label>
                                                                <input type="text" placeholder="Enter Focal Person Email" class="form-control input-sm" name="vendor_email" id="input04">
                                                            </div>
                                                            <span class="text-danger" id="vendor_email_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input05">Enter Cell #</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Cell #" name="vendor_cell" id="input05">
                                                            </div>
                                                            <span class="text-danger" id="vendor_cell_error"></span>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label for="input06">Enter Landline # <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                <input type="text" class="form-control input-sm" placeholder="Enter Landline #" name="vendor_landline" id="input06">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-10">
                                                                <label for="input07">Enter Remarks</label>
                                                                <textarea class="form-control" placeholder="Enter Remarks" rows="1" name="vendor_remarks"  id="input07" spellcheck="false"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="vendor_remarks_error"></span>
                                                        </div>
                                                    </div>
                                                </div>



                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Effective Date&Time</label>
                                                                <input type="text" id="date-format" name="vendor_edt" class="form-control input08 dt" placeholder="Select Effective Date & Time">
                                                            </div>
                                                            <span class="text-danger" id="vendor_edt_error"></span>
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
                <table id="view-vendorregistration" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Description</th>
                            <th>Address</th>
                            <th>Contact Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-vendorregistration" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Inventory Vendor Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="update_vendorregistration">
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

                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" name="u_vendor_org" required id="u_vendor_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

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
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });
    </script>

