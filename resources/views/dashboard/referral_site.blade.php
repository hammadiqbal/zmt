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
                <li class="breadcrumb-item">Organization</li>
                <li class="breadcrumb-item active">Referral Sites</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Referral Sites</h4>
                </div>
                @php
                $ReferralSetup = explode(',', $rights->referral_site);
                $add = $ReferralSetup[0];
                $view = $ReferralSetup[1];
                $edit = $ReferralSetup[2];
                $updateStatus = $ReferralSetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2 add-referralsite">
                        <i class="mdi mdi-bank"></i> Add Referral Site
                    </button>
                </div>
                @endif
            </div>

            @if ($add == 1)
            <div class="modal fade bs-example-modal-lg" id="add-referralsite" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Referral Site Details</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_referralsite" method="post">
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
                                                        <select class="form-contro selecter p-0" id="rf_org" name="rf_org">
                                                            <option selected value='{{ $user->org_id }}'>{{ $user->orgName }}</option>
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Organization</label>
                                                                    <select class="form-control selecter p-0" name="rf_org" id="rf_org" style="color:#222d32">
                                                                        <option selected disabled value=' '>Select Organization</option>
                                                                        @foreach ($Organizations as $Organization)
                                                                            <option value="{{ $Organization['id'] }}"> {{ $Organization['organization'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rf_org_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                     {{-- <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom">
                                                                    <label for="input07">Description </label>
                                                                    <textarea class="form-control" placeholder="Enter Description" rows="2" name="rf_desc"  spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="rf_desc_error"></span>
                                                            </div>
                                                        </div>
                                                    </div> --}}

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Referral Site</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Enter Name" name="rf_desc">
                                                                </div>
                                                                <span class="text-danger" id="rf_desc_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Provinces</label>
                                                                    <select class="form-control selecter p-0" name="rf_province" id="rf_province" style="color:#222d32">
                                                                        @foreach ($ProvinceData as $Province)
                                                                            <option value="{{ $Province['id'] }}" {{ $Province['name'] == 'Sindh' ? 'selected' : '' }}>
                                                                                {{ $Province['name'] }}
                                                                            </option>                                                                       
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rf_province_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Division</label>
                                                                    <select class="form-control selecter p-0" name="rf_division" id="rf_division" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rf_division_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Districts</label>
                                                                    <select class="form-control selecter p-0" name="rf_district" id="rf_district" style="color:#222d32">
                                                                    </select>
                                                                </div>
                                                                <span class="text-danger" id="rf_district_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter Cell # <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Cell #.." name="rf_cell">
                                                                </div>
                                                                <span class="text-danger" id="rf_cell_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label>Enter Landline # <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Landline #.." name="rf_landline">
                                                                </div>
                                                                <span class="text-danger" id="rf_landline_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective Date&Time</label>
                                                                    <input type="text" id="date-format" name="rf_edt" class="form-control input06" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="rf_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom">
                                                                    <label for="input07">Remarks  <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <textarea class="form-control" placeholder="Enter Remarks" rows="2" name="rf_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="rf_remarks_error"></span>
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
                <table id="view-referralsite" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Referral Site</th>
                            <th>Address Details</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-referralsite" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Referral Site Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="u_referralsite" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control u_rf-id" name="u_rf-id">

                                            @if($user->org_id == 0)
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Organization</label>
                                                            <select class="form-control selecter p-0" id="u_rf_org" required name="u_rf_org" style="color:#222d32">
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
                                                            <label>Referral Site</label>
                                                            <input type="text" class="form-control input-sm u_rf_desc" placeholder="Enter Name" name="u_rf_desc" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Provinces</label>
                                                            <select class="form-control selecter p-0" name="u_rf_province" id="u_rf_province" required style="color:#222d32">
                                                            </select>
                                                        </div>
                                                        <span class="text-danger" id="u_rf_province_error"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Division</label>
                                                            <select class="form-control selecter p-0" name="u_rf_division" id="u_rf_division" required style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Districts</label>
                                                            <select class="form-control selecter p-0" name="u_rf_district" id="u_rf_district" required style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label>Enter Cell # <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_rf_cell" placeholder="Cell #.." name="u_rf_cell">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label>Enter Landline # <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_rf_landline" placeholder="Landline #.." name="u_rf_landline">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label class="control-label">Effective Date&Time</label>
                                                            <input type="text" id="date-format1" name="u_rf_edt" class="form-control input06" placeholder="Select Effective Date & Time" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-group has-custom m-b-5">
                                                            <label for="input07">Remarks <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <textarea class="form-control u_rf_remarks" placeholder="Enter Remarks" rows="2" name="u_rf_remarks" spellcheck="false"></textarea>
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
    <script src="{{ asset('assets/custom/referral_setup.js') }}"></script>
