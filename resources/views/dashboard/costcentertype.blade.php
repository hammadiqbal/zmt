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
                <li class="breadcrumb-item active">Cost Center Type</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Cost Center Types</h4>
                </div>
                @php
                $ccTypeSetup = explode(',', $rights->cost_center_types);
                $add = $ccTypeSetup[0];
                $view = $ccTypeSetup[1];
                $edit = $ccTypeSetup[2];
                $updateStatus = $ccTypeSetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2" data-toggle="modal" data-target="#add-ccType">
                        <i class="mdi mdi-tune-vertical"></i> Add Cost Center Type
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-ccType" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Cost Center Type</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_ccType" method="post">
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
                                                            <div class="form-group has-custom m-b-10">
                                                                <label class="control-label">Enter Cost Center Type</label>
                                                                <input type="text" class="form-control input-sm" placeholder="Cost Center Type.." name="cc_type" id="input01"><span class="bar"></span>
                                                            </div>
                                                            <span class="text-danger" id="cc_type_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-10">
                                                                <label class="control-label">Effective DateTime</label>
                                                                <input type="text" id="date-format" name="cct_edt"  class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                            </div>
                                                            <span class="text-danger" id="cct_edt_error"></span>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Ordering CC Status</label>
                                                                <select class="form-control selecter p-0" id="orderingCC" name="ordering_cc" style="color:#222d32">
                                                                    <option selected disabled value=' '>Select Ordering CC Status</option>
                                                                        <option value="1">Enable</option>
                                                                        <option value="0">Disable</option>
                                                                    </select>
                                                                <span class="bar"></span>
                                                            </div>
                                                            <span class="text-danger" id="ordering_cc_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-5">
                                                                <label class="control-label">Performing CC Status</label>
                                                                <select class="form-control selecter p-0" id="performingCC" name="performing_cc" style="color:#222d32">
                                                                    <option selected disabled value=' '>Select Performing CC Status</option>
                                                                        <option value="1">Enable</option>
                                                                        <option value="0">Disable</option>
                                                                    </select>
                                                                <span class="bar"></span>
                                                            </div>
                                                            <span class="text-danger" id="performing_cc_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">
                                                            <div class="form-group has-custom m-b-10">
                                                                <label class="control-label">Enter Remarks</label>
                                                                <textarea class="form-control" rows="2" placeholder="Remarks.." name="cc_remarks"  id="input03" spellcheck="false"></textarea>
                                                            </div>
                                                            <span class="text-danger" id="cc_remarks_error"></span>
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
                <table id="view-ccType" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>CC Type</th>
                            <th>Remarks</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-cctype" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_ccType" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Cost Center Type Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Cost Center Type</label>
                                                        <input type="hidden" class="form-control ccType-id" name="ccType-id">
                                                        <input type="text" class="form-control cctype-name" required name="u_ccType">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update OrderingCC Status</label>
                                                        <select class="form-control selecter p-0 u_ordering" required name="u_ordering" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update PerformingCC Status</label>
                                                        <select class="form-control selecter p-0 u_performing" required name="u_performing" style="color:#222d32">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="date-format1" name="cct_edt" required class="form-control input06 dt edt">
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Remarks</label>
                                                        <textarea type="text" class="form-control cc-remarks" required name="u_ccremarks"></textarea>
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
                </div>
            </form>
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
        $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });

    </script>
<script src="{{ asset('assets/custom/costcenter_type.js') }}"></script>

