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
                <li class="breadcrumb-item active">Organization</li>
            </ol>
        </div>
    </div>



    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h4 class="card-title">All Organizations</h4>
                </div>
                @php
                $orgSetup = explode(',', $rights->organization_setup);
                $add = $orgSetup[0];
                $view = $orgSetup[1];
                $edit = $orgSetup[2];
                $updateStatus = $orgSetup[3];
                @endphp

                @if ($add == 1)
                <div class="col-auto">
                    <button type="button" class="btn btn-primary p-2" data-toggle="modal" data-target="#add-organization">
                        <i class="mdi mdi-hospital-building"></i> Add Organization
                    </button>
                </div>
                @endif

            </div>


            @if ($add == 1)
            <div class="modal fade bs-example-modal-xl" id="add-organization" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Add Organization</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form id="add_organization" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <!-- Row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Org Name</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Organization name..." name="org_name" id="input01"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Org Code</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Organization code..." name="org_code" id="input02"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_code_error"></span>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Province</label>
                                                                    <select class="form-control selecter p-0" name="org_province" id="province_name" style="color:#222d32">
                                                                    <option value="" selected disabled> Select Province</option>
                                                                        @foreach ($ProvinceData as $Province)
                                                                        <option value="{{ $Province['province_id'] }}"> {{ $Province['province_name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_province_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Division</label>
                                                                    <select class="form-control selecter p-0" name="org_division" id="division_name" style="color:#222d32">
                                                                        <option selected value=''>Select Division</option>
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_division_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">District</label>
                                                                    <select class="form-control selecter p-0" name="org_district" id="district_name" style="color:#222d32">
                                                                        <option selected  value=''>Select District</option>
                                                                    </select>
                                                                    <span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_district_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Focal Peson Name</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Focal person name..." name="org_person_name" id="input08"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_person_name_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Focal Peson Email</label>
                                                                    <input type="email" class="form-control input-sm" placeholder="Focal person email..." name="org_person_email" id="input09"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_person_email_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter Website URL</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Web URL..." name="org_website" id="input10"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_website_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Enter GPS Coordinates <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                                    <input type="text" class="form-control input-sm" placeholder="GPS Coordinates..." name="org_gps" id="input11"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_gps_error"></span>

                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input12">Enter Cell #</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Cell #..." name="org_cell" id="input12"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_cell_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input13">Enter Landline #</label>
                                                                    <input type="text" class="form-control input-sm" placeholder="Landline #..." name="org_landline" id="input13"><span class="bar"></span>
                                                                </div>
                                                                <span class="text-danger" id="org_landline_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label class="control-label">Effective DateTime</label>
                                                                    <input type="text" id="date-format" class="form-control input06 dt" name="org_edt" placeholder="Select Effective Date & Time">
                                                                </div>
                                                                <span class="text-danger" id="org_edt_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Remarks</label>
                                                                    <textarea class="form-control" rows="1" id="input03" placeholder="Remarks..." name="org_remarks" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="org_remarks_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <div class="form-group has-custom m-b-5">
                                                                    <label for="input03">Enter Head Office Address</label>
                                                                    <textarea class="form-control" rows="1" id="input04" placeholder="Address..." name="org_address" spellcheck="false"></textarea>
                                                                </div>
                                                                <span class="text-danger" id="org_address_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h4 class="card-title">Upload Logo </h4>
                                                                    {{--<br> <span class="compulsory">Image size must be 500 * 250 px</span> --}}
                                                                <input type="file" name="org_logo" id="org_logo" class="dropify m-b-5" />
                                                                <span class="text-danger" id="org_logo_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h4 class="card-title">Upload Banner</h4>
                                                                    {{-- <br><span class="compulsory">Image size must be 1000 * 200 px</span> --}}
                                                                <input type="file" name="org_banner" id="org_banner" class="dropify" />
                                                                <span class="text-danger" id="org_banner_error"></span>
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
                <table id="view-organization" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Remarks</th>
                            <th>Address</th>
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
    <div class="modal fade bs-example-modal-lg" id="edit-organization" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Update Organization</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="edit_org" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Org Name</label>
                                                            <input type="hidden" class="form-control u-org-id" name="u-orgid">
                                                            <input type="text" class="form-control input-sm u_org_name" required name="u_org_name" id="input01">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Province</label>
                                                            <select class="form-control selecter p-0 u_org_province" name="u_org_province" required id="u_org_province" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Division</label>
                                                            <select class="form-control selecter p-0 u_org_division" name="u_org_division" required id="u_org_division" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update District</label>
                                                            <select class="form-control selecter p-0 u_org_district" name="u_org_district" required id="u_org_district" style="color:#222d32">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Focal Peson Name</label>
                                                            <input type="text" class="form-control input-sm u_org_person_name" required name="u_org_person_name" id="input08">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Focal Peson Email</label>
                                                            <input type="email" class="form-control input-sm u_org_person_email" required name="u_org_person_email" id="input09">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Website URL</label>
                                                            <input type="text" class="form-control input-sm u_org_website" required name="u_org_website" id="input10">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update GPS Coordinates <small class="text-danger" style="font-size:11px;">(Optional)</small></label>
                                                            <input type="text" class="form-control input-sm u_org_gps"  name="u_org_gps" id="input11">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Cell #</label>
                                                            <input type="text" class="form-control input-sm u_org_cell" required name="u_org_cell" id="input12">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Landline #</label>
                                                            <input type="text" class="form-control input-sm u_org_landline" required name="u_org_landline" id="input13">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group row  m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                        <label class="control-label">Update Effective Date&Time</label>
                                                            <input type="text" id="date-format1" class="form-control input06 dt u_org_edt" required name="u_org_edt" placeholder="Update Effective Date & Time">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Remarks</label>
                                                            <textarea class="form-control u_org_remarks"  rows="3" id="input03" required name="u_org_remarks" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group row m-b-5">
                                                    <div class="col-md-12">
                                                        <div class="form-group m-b-5">
                                                            <label class="control-label">Update Head Office Address</label>
                                                            <textarea class="form-control u_org_address" rows="3" id="input04" required name="u_org_address" spellcheck="false"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-lg-6 col-md-6">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <label class="control-label">Update Logo</h4></label>
                                                            {{-- <br><span class="compulsory">Image size must be 500 * 250 px</span> --}}
                                                        <input type="file" name="u_org_logo" id="u_org_logo"  />
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-lg-6 col-md-6">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <label class="control-label">Update Banner</h4></label>
                                                            {{-- <br><span class="compulsory">Image size must be 1000 * 200 px</span> --}}
                                                        <input type="file" name="u_org_banner" id="u_org_banner" />
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


    @if ($view == 1)
    <div class="modal fade bs-example-modal-lg" id="organization-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body pt-0">
                    <div class="row">
                        <div class="col-lg-12 col-xlg-12 col-md-12">
                            <div class="card">
                                <div class="row">
                                    <img src="" id="orgBanner" alt="Organization Banner" class="img-square" style="width: 100%;height: 150px;">
                                </div>
                            </div>
                        </div>
                        <!-- Column -->
                        <div class="col-lg-4 col-xlg-3 col-md-5">
                            <div class="card">
                                <div class="card-body">
                                    <center class="m-t-30">
                                        <img src="" id="orglogo" alt="Organization Name" class="img-square" width="150">
                                        <h5 class="card-title m-t-10" id="orgname"></h5>
                                        <h6 class="card-title m-t-10" id="orgcode"></h6>

                                    </center>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">Email address </small>
                                    <h6 id="orgemail"></h6>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->
                        <!-- Column -->
                        <div class="col-lg-8 col-xlg-9 col-md-7">
                            <div class="card">
                                <div class="tab-pane" id="profile" role="tabpanel">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 col-xs-12 b-r"> <strong>Province</strong>
                                                <br>
                                                <p class="text-muted" id="orgprovince"></p>
                                            </div>
                                            <div class="col-md-4 col-xs-12 b-r"> <strong>Division</strong>
                                                <br>
                                                <p class="text-muted" id="orgdivision"></p>
                                            </div>
                                            <div class="col-md-4 col-xs-12 b-r"> <strong>District</strong>
                                                <br>
                                                <p class="text-muted" id="orgdistrict"></p>
                                            </div>
                                        </div>

                                        <hr>
                                        <p><b>Focal Person Name:</b> <span id="orgpersonname"></span></p>
                                        <hr>
                                        <p><b>Website:</b> <span id="orgwebsite"><a></a></span></p>
                                        <hr>
                                        <p><b>Head Office Address:</b> <span id="orgaddress"></span></p>
                                        <hr>
                                        <p><b>Remarks:</b> <span  id="orgremarks"></span></p>
                                        <hr>
                                        <p><b>Contact #:</b> <span id="orgcontact"></span></p>
                                        <hr>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Column -->


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
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
        $(document).ready(function() {
            $('.dropify').dropify();
        });

        $('#date-format').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm A',
            currentDate: new Date()
        });
        $('#date-format1').bootstrapMaterialDatePicker({
            format: 'dddd DD MMMM YYYY - hh:mm:ss A',
            minDate: new Date() 
        });
    </script>
<script src="{{ asset('assets/custom/organization.js') }}"></script>

