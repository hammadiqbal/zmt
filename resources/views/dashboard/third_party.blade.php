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
                <li class="breadcrumb-item">Material Management</li>
                <li class="breadcrumb-item active">Third Party Registration</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Third Party Registration</h4>
                </div>
                @php
                $ThirdPartyRegistration = explode(',', $rights->third_party_registration);
                $add = $ThirdPartyRegistration[0];
                $view = $ThirdPartyRegistration[1];
                $edit = $ThirdPartyRegistration[2];
                $updateStatus = $ThirdPartyRegistration[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 tp-registration">
                        <i class="mdi mdi-bank"></i> Register a Third Party
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="tp-registration" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Third Party Registration</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="register_tp" method="post">
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
                                                        <select class="form-contro selecter p-0" id="tp_org" name="tp_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="tp_org" id="tp_org" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="tp_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Registration Type</label>
                                                                    <select class="form-control selecter p-0"  name="registration_type" id="registration_type" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Registration Type</option>
                                                                        <option value="v">Vendor</option>
                                                                        <option value="d">Donor</option>
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="registration_type_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Category</label>
                                                                    <select class="form-control selecter p-0"  name="vendor_cat" id="vendor_cat" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Category</option>
                                                                        <option value="c">Corporate</option>
                                                                        <option value="i">Individual</option>
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="vendor_cat_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 tp_corporate">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input01">Enter Corporate Name</label>
                                                                    <input type="text" placeholder="Enter Corporate Name" class="form-control input-sm" name="tp_corporate_name">
                                                                </div>
                                                                <span class="text-danger" id="tp_corporate_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Select Prefix</label>
                                                                    <select class="form-control selecter p-0" name="tp_prefix" id="tp_prefix" style="color:#222d32">
                                                                        @foreach ($Prefixes as $Prefix)
                                                                            <option value="{{ $Prefix['id'] }}"> {{ $Prefix['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="tp_prefix_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input02">Enter Focal Person Name</label>
                                                                    <input type="text" placeholder="Enter Focal Person Name" class="form-control input-sm" name="tp_name">
                                                                </div>
                                                                <span class="text-danger" id="tp_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Focal Person Email</label>
                                                                    <input type="email" placeholder="Enter Focal Person Email" class="form-control input-sm" name="tp_email">
                                                                </div>
                                                                <span class="text-danger" id="tp_email_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input04">Enter Focal Person Cell#</label>
                                                                    <input type="text" placeholder="Enter Focal Person Cell#" class="form-control input-sm" name="tp_cell">
                                                                </div>
                                                                <span class="text-danger" id="tp_cell_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input05">Enter Landline# <small class="text-danger" style="font-size:11px;">(Optional)</small></label> 
                                                                    <input type="text" placeholder="Enter Landline#" class="form-control input-sm" name="tp_landline">
                                                                </div>
                                                                <span class="text-danger" id="tp_landline_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective Date&Time</label>
                                                                    <input type="text" id="date-format" name="tp_edt" class="form-control input06" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="tp_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-10">
                                                                    <label for="input06">Enter Address</label>
                                                                    <textarea class="form-control" placeholder="Enter Address" rows="2" name="tp_address"  spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="tp_address_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-10">
                                                                    <label for="input07">Enter Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Enter Remarks" rows="2" name="tp_remarks"  id="input07" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="tp_remarks_error"></span>
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
                <table id="view-tpregistration" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Donor/Vendor Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-tp" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Third Party Registration Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_register_tp" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control u_tp-id" name="u_tp-id">
                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Organization</label>
                                                    <select class="form-control selecter p-0" id="u_tp_org" required name="u_tp_org" style="color:#222d32">
                                                    </select>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Registration Type</label>
                                                    <select class="form-control selecter p-0 u_registration_type" required name="u_registration_type" style="color:#222d32">
                                                        <option value="v">Vendor</option>
                                                        <option value="d">Donor</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Vendor Category</label>
                                                    <select class="form-control selecter p-0 u_vendor_cat" required name="u_vendor_cat" style="color:#222d32">
                                                        <option value="c">Corporate</option>
                                                        <option value="i">Individual</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 ucorporateName">
                                                <div class="form-group">
                                                    <label class="control-label">Update Corporate Name</label>
                                                    <input type="text" name="u_tp_corporate_name" class="form-control u_tp_corporate_name">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Prefix</label>
                                                    <select class="form-control selecter p-0" name="u_tp_prefix" id="u_tp_prefix" style="color:#222d32">
                                                        @foreach ($Prefixes as $Prefix)
                                                            <option value="{{ $Prefix['id'] }}"> {{ $Prefix['name'] }}</option>
                                                        @endforeach
                                                    </select>                                               
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Focal Person Name</label>
                                                    <input type="text" name="u_tp_name" required class="form-control u_tp_name">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Focal Person Email</label>
                                                    <input type="email" name="u_tp_email" required class="form-control u_tp_email">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Focal Person Cell#</label>
                                                    <input type="text" name="u_tp_cell" required class="form-control u_tp_cell">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Landline# <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                    <input type="text" name="u_tp_landline" class="form-control u_tp_landline">
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Update Effective Date&Time</label>
                                                    <input type="text" name="u_tp_edt" id="date-format1" required class="form-control input06 dt uedt">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Address</label>
                                                            <textarea class="form-control u_tp_address" rows="3" required name="u_tp_address" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <textarea class="form-control u_tp_remarks" rows="3" name="u_tp_remarks" spellcheck="false"></textarea>
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

<script src="{{ asset('assets/custom/third_party_registration.js') }}"></script>
