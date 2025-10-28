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
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">User Setup</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Users</h4>
                </div>
                @php
                    $userSetup = explode(',', $rights->user_setup);
                    $add = $userSetup[0];
                    $view = $userSetup[1];
                    $edit = $userSetup[2];
                    $updateStatus = $userSetup[3];
                @endphp
                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 adduser">
                        <i class="fa fa-user"></i> Add User
                    </button>
                </div>
                @endif
            </div>
            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-user" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add User</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_user" method="post">
                            @csrf
                            <div class="modal-body p-1">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                                <div class="form-body">
                                                    <div class="row" id="enable_site">
                                                        <div class="col-md-8"></div>
                                                        <div class="col-md-4">
                                                            <div class="form-group text-right">
                                                                <h4>Enable All Sites</h4>
                                                                <div class="bt-switch">
                                                                    <input type="checkbox" id="siteEnabled" data-on-color="primary" data-off-color="info" data-on-text="Yes" data-off-text="No" data-size="small">
                                                                    <input type="hidden" id="siteStatus" name="siteStatus" value="off">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        @if($user->org_id != 0)
                                                        <div class="userOrganization">
                                                            <select class="form-contro selecter p-0" id="usereOrg" name="userOrg">
                                                                <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                            </select>
                                                        </div>
                                                        @else
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Organization</label>
                                                                        <select class="form-contro selecter l p-0" id="userOrg" name="userOrg" style="color:#222d32">
                                                                            <option selected disabled value=' '>Select Organization</option>
                                                                            @foreach ($organizations as $organization)
                                                                            <option value="{{ $organization['id'] }}"> {{ $organization['organization'] }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <span class="bar"></span>
                                                                    </div>
                                                                    <span class="text-danger" id="userOrg_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                            <label class="control-label">User Role</label>
                                                                            <select class="form-contro selecter l p-0" id="roleName" name="userRole" style="color:#222d32">
                                                                                @if ($allroles->isEmpty())
                                                                                    <option selected disabled>Not Available</option>
                                                                                @else
                                                                                    <option selected disabled value=' '>Select Role</option>
                                                                                    @foreach ($allroles as $roles)
                                                                                        <option value="{{ $roles['id'] }}"> {{ ucwords($roles['role']) }}</option>
                                                                                    @endforeach
                                                                                @endif
                                                                            </select>
                                                                        <span class="bar"></span>
                                                                    </div>
                                                                    <span class="text-danger" id="userRole_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label>Effective DateTime</label>
                                                                        <input type="text" id="date-format" name="userEdt" class="form-control input06 dt" placeholder="Select Effective Date & Time">
                                                                    </div>
                                                                    <span class="text-danger" id="userEdt_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="col-12 text-center">
                                                            <div class="form-group row">
                                                                <div class="col-lg-12 bt-switch">
                                                                    <h4>Is the user an employee?</h4>
                                                                    <div class="m-b-5">
                                                                        <input type="checkbox" id="isEmployee"  checked data-on-color="primary" data-off-color="info" data-on-text="Yes" data-off-text="No">
                                                                        <input type="hidden" id="empStatus" name="isEmployee" value="on">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12" id="userEmployee">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Employee</label>
                                                                        <select class="form-control selecter p-0" id="userEmp" name="userEmp" style="color:#222d32">

                                                                        </select>
                                                                        <span class="bar"></span>
                                                                    </div>
                                                                    <span class="text-danger" id="userEmp_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row" id="userDetails" style="display:none">
                                                        <div class="col-md-6" id="userName">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Enter Full Name</label>
                                                                        <input type="text" class="form-control input-sm" placeholder="Full Name..." name="username">
                                                                    </div>
                                                                    <span class="text-danger" id="username_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" id="userEmail">
                                                            <div class="form-group row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group has-custom m-b-5">
                                                                        <label class="control-label">Enter Email</label>
                                                                        <input type="email" class="form-control input-sm" placeholder="abcd@gmail.com" name="useremail">
                                                                    </div>
                                                                    <span class="text-danger" id="useremail_error"></span>
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
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            @if ($view == 1)
            <div class="table-responsive m-t-40">
                <table id="view-user" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>UserId</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            {{-- <th>Employee Name</th> --}}
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
    <div class="modal fade bs-example-modal-lg" id="edit-user" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form id="update_user" method="post">
            @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Update User Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-8"></div>
                                                <div class="col-md-4">
                                                    <div class="form-group text-right">
                                                         <h4>Enable All Sites</h4>
                                                        <div class="bt-switch">
                                                            <input type="checkbox" id="u_siteEnabled" data-on-color="primary" data-off-color="info" data-on-text="Yes" data-off-text="No" data-size="small">
                                                            <input type="hidden" id="u_siteStatus" name="u_siteStatus" value="off">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Enable All Sites Radio Buttons -->
                                            
                                            <div class="row">
                                                @if($user->org_id == 0)
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Organization</label>
                                                        <select class="form-control selecter custom-select u_user_org" required name="user_org" tabindex="1">
                                                        </select>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-6" id="u_employee">
                                                    <div class="form-group">
                                                        <label class="control-label1">Update Employee</label>
                                                        <select class="form-control selecter custom-select u_user_emp" name="user_emp" tabindex="1">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Full Name</label>
                                                        <input type="hidden" class="form-control user-id" name="user-id">
                                                        <input type="text" required class="form-control user-name" name="user_name">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Email</label>
                                                        <input type="email"  class="form-control user-email" required name="user_email">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Role</label>
                                                        <select class="form-control selecter custom-select u_user_role" required name="user_role" tabindex="1">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                        <input type="text" id="date-format1" class="form-control input06 dt edt" name="user_edt" >
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
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
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
        minDate: new Date(),
        currentDate: new Date()
    });
    $('#date-format1').bootstrapMaterialDatePicker({
        format: 'dddd DD MMMM YYYY - hh:mm A',
        minDate: new Date() 
    });
   $(".bt-switch input[type='checkbox']").bootstrapSwitch();
</script>
<script src="{{ asset('assets/custom/users.js') }}"></script>

