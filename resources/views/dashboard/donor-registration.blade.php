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
                <li class="breadcrumb-item active">Donors Registration</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Registered Donors</h4>
                </div>
                @php
                $DonorRegistration = explode(',', $rights->donors_registration);
                $add = $DonorRegistration[0];
                $view = $DonorRegistration[1];
                $edit = $DonorRegistration[2];
                $updateStatus = $DonorRegistration[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 donor-registration">
                        <i class="mdi mdi-bank"></i> Register a Donor
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="donor-registration" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Donor Registration</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="register_donor" method="post">
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
                                                        <select class="form-contro selecter p-0" id="donor_org" name="donor_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="donor_org" id="donor_org" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="donor_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Donor Type</label>
                                                                    <select class="form-control selecter p-0"  name="donor_type" id="donor_type" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Donor Type</option>
                                                                        <option value="corporate">Corporate</option>
                                                                        <option value="individual">Individual</option>
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="donor_type_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 donor_corporate">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Corporate Name</label>
                                                                    <input type="text" placeholder="Enter Corporate Name" class="form-control input-sm" name="donor_corporate" id="input01">
                                                                </div>
                                                                <span class="text-danger" id="donor_corporate_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input02">Enter Focal Person Name</label>
                                                                    <input type="text" placeholder="Enter Focal Person Name" class="form-control input-sm" name="donor_name" id="input02">
                                                                </div>
                                                                <span class="text-danger" id="donor_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Focal Person Email</label>
                                                                    <input type="email" placeholder="Enter Focal Person Email" class="form-control input-sm" name="donor_email" id="input03">
                                                                </div>
                                                                <span class="text-danger" id="donor_email_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input04">Enter Focal Person Cell#</label>
                                                                    <input type="text" placeholder="Enter Focal Person Cell#" class="form-control input-sm" name="donor_cell" id="input04">
                                                                </div>
                                                                <span class="text-danger" id="donor_cell_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input05">Enter Focal Person Landline# <small class="text-danger" style="font-size:11px;">(Optional)</small></label> 
                                                                    <input type="text" placeholder="Enter Focal Person Landline#" class="form-control input-sm" name="donor_landline" id="input05">
                                                                </div>
                                                                <span class="text-danger" id="donor_landline_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective Date&Time</label>
                                                                    <input type="text" id="date-format" name="donor_edt" class="form-control input06" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="donor_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-10">
                                                                    <label for="input06">Enter Address</label>
                                                                    <textarea class="form-control" placeholder="Enter Address" rows="2" name="donor_address"  id="input06" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="donor_address_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-10">
                                                                    <label for="input07">Enter Remarks</label>
                                                                    <textarea class="form-control" placeholder="Enter Remarks" rows="2" name="donor_remarks"  id="input07" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="donor_remarks_error"></span>
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
                <table id="view-donors" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Donor Details</th>
                            <th>Address</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-donor" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Financial Payroll Addition Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_register_donor" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control u_donor-id" name="u_donor-id">
                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0 u_donor_org" required name="u_donor_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Donor Type</label>
                                                    <select class="form-control selecter p-0 u_donor_type" required name="u_donor_type" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 ucorporateName">
                                                <div class="form-group">
                                                    <label class="control-label">Update Corporate Name</label>
                                                    <input type="text" name="u_donor_corporate" class="form-control u_donor_corporate">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Focal Person Name</label>
                                                    <input type="text" name="u_donor_name" required class="form-control u_donor_name">
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Focal Person Email</label>
                                                    <input type="email" name="u_donor_email" required class="form-control u_donor_email">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Focal Person Cell#</label>
                                                    <input type="text" name="u_donor_cell" required class="form-control u_donor_cell">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Focal Person Landline# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <input type="text" name="u_donor_landline" class="form-control u_donor_landline">
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" name="u_donor_edt" id="date-format1" required class="form-control input06 dt edt">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Address</label>
                                                            <textarea class="form-control u_donor_address" rows="3" required name="u_donor_address" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Remarks</label>
                                                            <textarea class="form-control u_donor_remarks" rows="3" required name="u_donor_remarks" spellcheck="false"></textarea>
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
        $('#date-format1').bootstrapMaterialDatePicker({ format : 'dddd DD MMMM YYYY - hh:mm:ss A' });
    </script>
    <script src="{{ asset('assets/custom/donor_registration.js') }}"></script>