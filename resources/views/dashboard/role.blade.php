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
                <li class="breadcrumb-item active">User Roles</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All User Roles</h4>
                </div>
                @php
                $roleSetup = explode(',', $rights->user_roles);
                $add = $roleSetup[0];
                $view = $roleSetup[1];
                $edit = $roleSetup[2];
                $updateStatus = $roleSetup[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-user">
                        <i class="fa fa-user-plus"></i> Add User Role
                    </button>
                </div>
                @endif

            </div>
            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-user" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add User Role</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_role" method="post">
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
                                                                        <label id="emailLabel" for="input02">Enter Role Name</label>
                                                                        <input type="text" class="form-control input-sm" placeholder="Super Admin..." name="role_name" id="input01"><span class="bar"></span>
                                                                    </div>
                                                                    <span class="text-danger" id="role_name_error"></span>

                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label id="emailLabel" for="input02">Effective DateTime</label>
                                                                        <input type="text" id="date-format" class="form-control input06 dt" name="role_edt" placeholder="Select Effective Date & Time">
                                                                    </div>
                                                                    <span class="text-danger" id="role_edt_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label id="emailLabel" for="input02">Enter Remarks</label>
                                                                        <textarea class="form-control" rows="1" placeholder="Your remarks..." id="input04" name="role_remarks" spellcheck="false"></textarea>
                                                                    </div>
                                                                    <span class="text-danger" id="role_remarks_error"></span>
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
                <table id="view-roles" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Role ID</th>
                            <th>Role</th>
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
        <div class="modal fade bs-example-modal-lg" id="edit-role" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <form id="update_role" method="post">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Update Role</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card-body">
                                        <form action="#" class="floating-labels form-horizontal">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label">Update Role</label>
                                                            <input type="hidden" class="form-control role-id" name="role-id">
                                                            <input type="text" class="form-control role-name" required name="u_role">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label">Update Effective Date&amp;Time</label>
                                                            <input type="text" id="date-format1" required name="u_edt" class="form-control input06 dt edt" >
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label">Update Remarks</label>
                                                            <textarea class="form-control update-remark" required   name="u_remarks" rows="3" id="input03" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
    $('#date-format1').bootstrapMaterialDatePicker({
        format: 'dddd DD MMMM YYYY - hh:mm A',
        minDate: new Date() 
    });
</script>

